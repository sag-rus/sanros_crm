<?php
// Legacy entry point: use the validated CLI worker, never an unauthenticated browser loop.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Use the scheduled classification worker.'); }
require __DIR__.'/tools/fsa-refresh.php';
