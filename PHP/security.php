<?php
require_once '../security/config.php';
require_once '../security/DosProtection.php';

// Initialiser la protection
$dosProtection = new DosProtection();

// Vérifier la requête
if (!$dosProtection->isRequestAllowed($_SERVER['REMOTE_ADDR'])) {
    header('HTTP/1.1 429 Too Many Requests');
    die('Too many requests. Please try again later.');
}
?>