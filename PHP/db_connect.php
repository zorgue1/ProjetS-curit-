<?php

require_once 'security.php';

$host = "localhost";
$username = "root";
$password = ""; // Par défaut, pas de mot de passe sous XAMPP
$dbname = "ctf_challenge";

try {
    // Connexion à la base de données avec PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Échec de la connexion : " . $e->getMessage());
}
?>
