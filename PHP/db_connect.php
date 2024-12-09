<?php
$host = 'localhost';
$dbname = 'ctf_challenge'; // Nom de votre base de données
$username = 'root'; // Nom d'utilisateur par défaut pour XAMPP
$password = ''; // Mot de passe par défaut pour XAMPP (vide)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
