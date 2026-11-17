#!/usr/bin/env python3
"""Exercise scheduler preservation and fail-closed retirement guards without system writes."""
import importlib.machinery
import json
import os
import unittest
from unittest import mock

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
retirement = importlib.machinery.SourceFileLoader('retirement', os.path.join(ROOT, 'tools/disable-obsolete-jobs.py')).load_module()
with open(os.path.join(ROOT, 'deploy/obsolete-jobs.json')) as stream:
    MANIFEST = json.load(stream)


class RetirementTests(unittest.TestCase):
    def test_preserves_other_jobs_and_comments_exactly(self):
        retained = b'SHELL=/bin/sh\n# Keep this operator note\n7 4 * * * /usr/local/bin/nightly-backup\n'
        commented = ('#' + MANIFEST['remove_cron'][0] + '\n').encode()
        source = retained + ('\n'.join(MANIFEST['remove_cron']) + '\n').encode() + commented
        changed, removed = retirement.transform_crontab(source, MANIFEST)
        self.assertEqual(changed, retained + commented)
        self.assertEqual(len(removed), len(MANIFEST['remove_cron']))
        self.assertEqual(retirement.transform_crontab(changed, MANIFEST), (changed, []))

    def test_changed_schedule_is_rejected_not_silently_left_running(self):
        altered = MANIFEST['remove_cron'][0].split()
        altered[0] = '13'
        with self.assertRaises(RuntimeError):
            retirement.transform_crontab((' '.join(altered) + '\n').encode(), MANIFEST)

    def test_added_arguments_are_rejected(self):
        with self.assertRaises(RuntimeError):
            retirement.transform_crontab((MANIFEST['remove_cron'][0] + ' --extra\n').encode(), MANIFEST)

    def test_all_duplicate_occurrences_are_removed(self):
        line = (MANIFEST['remove_cron'][0] + '\n').encode()
        self.assertEqual(retirement.transform_crontab(line + line, MANIFEST), (b'', [MANIFEST['remove_cron'][0]] * 2))

    def test_unrelated_crm_and_price_jobs_survive(self):
        data = b'*/10 * * * * /usr/bin/php /var/www/html/CRM/core/cron/sync-objects-api.php\n* * * * * /usr/bin/php /var/www/html/CRM/core/price-tonia-live-worker.php\n*/10 * * * * /usr/bin/php /var/www/html/CRM/tools/live-catalogue-sync.php\n* * * * * /usr/bin/python3 /home/rustem/price-tonia-auto-sync-v1/worker.py\n'
        self.assertEqual(retirement.transform_crontab(data, MANIFEST), (data, []))

    def test_supervisor_status_handles_fatal_and_empty(self):
        self.assertEqual(retirement.supervisor_groups(b'crm-worker-0:crm-worker-0_00 FATAL Exited too quickly\n'), {'crm-worker-0'})
        self.assertEqual(retirement.supervisor_groups(b''), set())
        with self.assertRaises(RuntimeError):
            retirement.supervisor_groups(b'error: refused connection\n')

    def test_supervisor_drift_and_other_workers_block_plan(self):
        before = b'[program:crm-worker-0]\ncommand=/old-disabled-worker\n'
        replacement = b'; retired\n'
        settings = {'path':'/etc/supervisor/conf.d/crm.conf', 'before_sha256':retirement.digest(before), 'replacement':'deploy/retired.conf', 'main_sha256':retirement.digest(b'main'), 'groups':['crm-worker-0']}
        files = {settings['path']:before, '/repo/deploy/retired.conf':replacement, '/etc/supervisor/supervisord.conf':b'main'}
        with mock.patch.object(retirement.os.path, 'islink', return_value=False), mock.patch.object(retirement.os.path, 'isfile', return_value=True), mock.patch.object(retirement, 'read', side_effect=lambda p:files[p]), mock.patch.object(retirement.glob, 'glob', return_value=[settings['path']]), mock.patch.object(retirement, 'command', return_value=b'crm-worker-0:crm-worker-0_00 FATAL exited\n'):
            self.assertEqual(retirement.supervisor_plan('/repo', settings), (before, replacement, ['crm-worker-0']))
            files[settings['path']] = before + b'# changed by an administrator\n'
            with self.assertRaises(RuntimeError):
                retirement.supervisor_plan('/repo', settings)
            files[settings['path']] = before
            with mock.patch.object(retirement, 'command', return_value=b'another-worker RUNNING pid 123\n'):
                with self.assertRaises(RuntimeError):
                    retirement.supervisor_plan('/repo', settings)
            files['/etc/supervisor/conf.d/another.conf'] = b'[program:another-worker]\n'
            with mock.patch.object(retirement.glob, 'glob', return_value=[settings['path'], '/etc/supervisor/conf.d/another.conf']):
                with self.assertRaises(RuntimeError):
                    retirement.supervisor_plan('/repo', settings)


if __name__ == '__main__':
    unittest.main()
