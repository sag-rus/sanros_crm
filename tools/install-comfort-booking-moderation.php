<?php
if(PHP_SAPI!=='cli' || posix_geteuid()!==0) exit(1);
$secret=trim(stream_get_contents(STDIN));
if(!preg_match('/^[a-f0-9]{64}$/D',$secret)) throw new RuntimeException('Invalid credential');
$dir='/etc/sanata';if(!is_dir($dir))mkdir($dir,0750,true);chgrp($dir,'www-data');
$path=$dir.'/comfort-booking-moderation-token';
if(file_exists($path) && !hash_equals(trim(file_get_contents($path)),$secret)) throw new RuntimeException('Credential already exists and differs');
if(file_put_contents($path,$secret."\n",LOCK_EX)===false)throw new RuntimeException('Cannot store credential');
chmod($path,0640);chgrp($path,'www-data');echo "Private moderation credential installed.\n";
