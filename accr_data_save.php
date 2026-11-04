<?php
// The old fuzzy-match importer could overwrite a property with its last building.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
fwrite(STDERR, "Legacy matching disabled. Use tools/fsa-refresh.php (preview), then --apply.\n");
exit(1);
