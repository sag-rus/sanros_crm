<?php
// Run only from a published Git revision. No application source is written by this installer.
if (PHP_SAPI !== 'cli' || !function_exists('posix_geteuid') || posix_geteuid() !== 0) exit(1);
$root = dirname(__DIR__);
$path = '/etc/sanata/fsa-api-key';
if (!is_dir(dirname($path)) && !mkdir(dirname($path),0750,true)) throw new RuntimeException('Credential directory unavailable');
chgrp(dirname($path),'www-data'); chmod(dirname($path),0750);
if (!file_exists($path)) {
    $old = shell_exec('git -C '.escapeshellarg($root).' show HEAD^:accr_data_load.php');
    if (!preg_match('/Api-Key: ([^\x27\x22\r\n]+)/', (string)$old, $match)) throw new RuntimeException('Set FSA credential before installation');
    if (file_put_contents($path,trim($match[1])."\n",LOCK_EX) === false) throw new RuntimeException('Credential write failed');
}
chgrp($path,'www-data'); chmod($path,0640);
$state = '/var/lib/sanata-fsa';
foreach ([$state, $state.'/backups'] as $dir) {
    if (!is_dir($dir) && !mkdir($dir,0750,true)) throw new RuntimeException('State directory unavailable');
    chgrp($dir,'www-data'); chmod($dir,$dir===$state ? 0750 : 0770);
}
foreach (['deploy/fsa-refresh.cron'=>'/etc/cron.d/sanata-fsa','deploy/fsa-refresh.logrotate'=>'/etc/logrotate.d/sanata-fsa'] as $src=>$dst) {
    if (!copy($root.'/'.$src,$dst)) throw new RuntimeException('Schedule installation failed');
    chown($dst,'root'); chgrp($dst,'root'); chmod($dst,0644);
}
echo "FSA credential stored privately; schedule installed.\n";
