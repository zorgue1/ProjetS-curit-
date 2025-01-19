<?php
class DosProtection {
    private $db;
    private $cache;
    private $window_size = 60; // Fenêtre de temps en secondes
    private $max_requests = 50; // Nombre maximum de requêtes
    private $blacklist_duration = 300; // Durée du blacklist en secondes

    public function __construct() {
        // Initialisation (si besoin)
    }

    public function isRequestAllowed($ip) {
        if ($this->isWhitelisted($ip)) {
            return true;
        }

        if ($this->isBlacklisted($ip)) {
            $this->logAttempt($ip, 'Blocked - Blacklisted IP');
            return false;
        }

        $requests = $this->countRecentRequests($ip);

        if ($requests > $this->max_requests) {
            $this->blacklistIP($ip);
            $this->logAttempt($ip, 'Blocked - Too many requests');
            return false;
        }

        $this->recordRequest($ip);
        return true;
    }

    private function isWhitelisted($ip) {
        $whitelistedIps = ['127.0.0.1', '::1']; // Liste blanche
        return in_array($ip, $whitelistedIps);
    }

    private function isBlacklisted($ip) {
        $blacklist = json_decode(file_get_contents('../logs/blacklist.json'), true) ?? [];
        if (isset($blacklist[$ip]) && $blacklist[$ip] > time()) {
            return true;
        }
        return false;
    }

    private function blacklistIP($ip) {
        $blacklist = json_decode(file_get_contents('../logs/blacklist.json'), true) ?? [];
        $blacklist[$ip] = time() + $this->blacklist_duration;
        file_put_contents('../logs/blacklist.json', json_encode($blacklist));
    }

    private function countRecentRequests($ip) {
        $logFile = '../logs/requests.log';
        $timeThreshold = time() - $this->window_size;
        $count = 0;

        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                list($logIp, $timestamp) = explode(',', $line);
                if ($logIp === $ip && $timestamp >= $timeThreshold) {
                    $count++;
                }
            }
        }
        return $count;
    }

    private function recordRequest($ip) {
        $logFile = '../logs/requests.log';
        $entry = "$ip," . time() . "\n";
        file_put_contents($logFile, $entry, FILE_APPEND);
    }

    private function logAttempt($ip, $message) {
        $date = date('Y-m-d H:i:s');
        error_log("[$date] $message - IP: $ip\n", 3, "../logs/security.log");
    }
}
?>
