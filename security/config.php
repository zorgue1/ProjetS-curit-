<?php
define('SECURITY_LOG_PATH', '../logs/security.log');
define('WHITELIST_IPS', [
    '127.0.0.1',
    // autres IPs
]);
define('MAX_REQUESTS_PER_WINDOW', 100);
define('WINDOW_SIZE', 60);
define('BLACKLIST_DURATION', 300);
?>