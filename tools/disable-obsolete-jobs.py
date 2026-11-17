#!/usr/bin/env python3
"""Retire only audited jobs, from a published master checkout. Python >= 3.4."""
import argparse
import datetime
import glob
import hashlib
import json
import os
import re
import subprocess
import sys
import tempfile


def digest(data):
    return hashlib.sha256(data).hexdigest()


def read(path):
    with open(path, 'rb') as stream:
        return stream.read()


def command(args, data=None, allowed=(0,)):
    proc = subprocess.Popen(args, stdin=subprocess.PIPE, stdout=subprocess.PIPE,
                            stderr=subprocess.PIPE)
    out, err = proc.communicate(data)
    if proc.returncode not in allowed:
        raise RuntimeError('{} failed ({}): {}'.format(args[0], proc.returncode,
                                                      err.decode('utf8', 'replace')[:300]))
    return out


def normalized(line):
    return ' '.join(line.split())


def transform_crontab(data, manifest):
    """Preserve all unrelated bytes, including comments and environment settings."""
    expected = set(normalized(line) for line in manifest['remove_cron'])
    result, removed = [], []
    for line in data.decode('utf8').splitlines(True):
        value = normalized(line)
        if value in expected:
            removed.append(value)
        else:
            if value and not value.startswith('#'):
                if any(target in value for target in manifest['guard_commands']):
                    raise RuntimeError('Unexpected schedule for a retired command; review required')
            result.append(line)
    return ''.join(result).encode('utf8'), removed


def supervisor_groups(status):
    groups = set()
    for line in status.decode('utf8').splitlines():
        if not line.strip():
            continue
        match = re.match(r'^([A-Za-z0-9_-]+)(?::[A-Za-z0-9_-]+)?\s+(RUNNING|STOPPED|FATAL|EXITED|STARTING|STOPPING|BACKOFF|UNKNOWN)\b', line)
        if not match:
            raise RuntimeError('Unexpected Supervisor status response')
        groups.add(match.group(1))
    return groups


def supervisor_plan(root, settings):
    path = settings['path']
    if os.path.islink(path) or not os.path.isfile(path):
        raise RuntimeError('Supervisor configuration must be a regular file')
    before = read(path)
    after = read(os.path.join(root, settings['replacement']))
    if before != after and digest(before) != settings['before_sha256']:
        raise RuntimeError('Supervisor configuration changed since audit')
    if digest(read('/etc/supervisor/supervisord.conf')) != settings['main_sha256']:
        raise RuntimeError('Supervisor main configuration changed since audit')
    expected = set(settings['groups'])
    # Removing the daily daemon restart is valid only while no other workers exist.
    for config in glob.glob('/etc/supervisor/conf.d/*.conf'):
        text = read(config).decode('utf8')
        configured = set(re.findall(r'^\[program:([^]]+)\]', text, re.M))
        if configured - expected or (config != path and configured):
            raise RuntimeError('Additional Supervisor programs require review')
    active = supervisor_groups(command(['supervisorctl', 'status'], allowed=(0, 3)))
    if active - expected:
        raise RuntimeError('Additional running Supervisor groups require review')
    return before, after, sorted(active)


def replace_file(path, data):
    fd, temporary = tempfile.mkstemp(prefix='.obsolete-jobs-', dir=os.path.dirname(path))
    try:
        with os.fdopen(fd, 'wb') as stream:
            stream.write(data)
            stream.flush()
            os.fsync(stream.fileno())
        os.chmod(temporary, 0o644)
        os.chown(temporary, 0, 0)
        os.replace(temporary, path)
    finally:
        if os.path.exists(temporary):
            os.unlink(temporary)


def save_private(directory, name, data):
    path = os.path.join(directory, name)
    with open(path, 'wb') as stream:
        stream.write(data)
    os.chmod(path, 0o600)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--apply', action='store_true', help='Apply the printed retirement plan')
    args = parser.parse_args()
    if os.geteuid() != 0:
        raise RuntimeError('Run as root on the deployment host')
    root = os.path.realpath(os.path.join(os.path.dirname(__file__), '..'))
    manifest = json.loads(read(os.path.join(root, 'deploy/obsolete-jobs.json')).decode('utf8'))
    if root != manifest['server_root']:
        raise RuntimeError('Unexpected deployment directory')
    def git(*values):
        return command(['git', '-C', root] + list(values)).decode('utf8').strip()
    if git('symbolic-ref', '--short', 'HEAD') != 'master':
        raise RuntimeError('Deployment must be on master')
    if git('diff', '--name-only') or git('diff', '--cached', '--name-only'):
        raise RuntimeError('Tracked source changes must be reconciled before installation')
    head = git('rev-parse', 'HEAD')
    if head != git('rev-parse', 'origin/master'):
        raise RuntimeError('Deploy the fetched origin/master commit first')
    for path in manifest.get('required_absent', []):
        if os.path.lexists(path):
            raise RuntimeError('Retired application path exists again: ' + path)
    for relative, expected in manifest['source_sha256'].items():
        if digest(read(os.path.join(root, relative))) != expected:
            raise RuntimeError('Audited source changed: ' + relative)
    user = manifest['cron_user']
    before = command(['crontab', '-u', user, '-l'])
    after, removed = transform_crontab(before, manifest)
    settings = manifest.get('supervisor')
    sup_before, sup_after, groups = (None, None, [])
    if settings:
        sup_before, sup_after, groups = supervisor_plan(root, settings)
    result = {'migration': manifest['id'], 'commit': head, 'apply': args.apply,
              'cron_user': user, 'remove_cron': removed, 'supervisor_groups': groups,
              'replace_supervisor_config': settings is not None and sup_before != sup_after}
    print(json.dumps(result, indent=2))
    if not args.apply:
        return
    if before == after and sup_before == sup_after and not groups:
        print('Already applied; no changes.')
        return
    parent = '/var/backups/sanata-job-retirement'
    if not os.path.isdir(parent):
        os.makedirs(parent, 0o700)
    os.chmod(parent, 0o700)
    backup = tempfile.mkdtemp(prefix=manifest['id'] + '-', dir=parent)
    save_private(backup, 'crontab.before', before)
    save_private(backup, 'crontab.after', after)
    if settings:
        save_private(backup, 'supervisor.before', sup_before)
        save_private(backup, 'supervisor.after', sup_after)
    save_private(backup, 'manifest.json', json.dumps(dict(result, backup=backup,
                 utc=datetime.datetime.utcnow().isoformat()+'Z'), indent=2).encode('utf8'))
    print('Backup: ' + backup)
    # Recheck immediately before changing either scheduler.
    if command(['crontab', '-u', user, '-l']) != before:
        raise RuntimeError('Crontab changed during planning; nothing installed')
    if settings and read(settings['path']) != sup_before:
        raise RuntimeError('Supervisor configuration changed during planning; nothing installed')
    try:
        if before != after:
            command(['crontab', '-u', user, '-'], after)
        if settings:
            if sup_before != sup_after:
                replace_file(settings['path'], sup_after)
            command(['supervisorctl', 'reread'])
            if groups:
                # Scope update to the retired groups; do not restart the daemon or other jobs.
                command(['supervisorctl', 'update'] + groups)
            remaining = supervisor_groups(command(['supervisorctl', 'status'], allowed=(0, 3)))
            if remaining & set(settings['groups']):
                raise RuntimeError('Retired Supervisor groups are still registered')
        if command(['crontab', '-u', user, '-l']) != after:
            raise RuntimeError('Crontab verification failed')
        if settings and read(settings['path']) != sup_after:
            raise RuntimeError('Supervisor configuration verification failed')
    except Exception:
        # Keep the private before/after snapshots; never overwrite concurrent admin changes.
        print('Installation incomplete; inspect backup before recovery: ' + backup, file=sys.stderr)
        raise
    save_private(backup, 'completed.json', json.dumps({'commit': head,
                 'removed_cron_count': len(removed), 'removed_supervisor_groups': groups}).encode('utf8'))
    print('Verified: {} cron entries removed; {} Supervisor groups removed.'.format(len(removed), len(groups)))


if __name__ == '__main__':
    try:
        main()
    except Exception as error:
        print('ERROR: ' + str(error), file=sys.stderr)
        sys.exit(1)
