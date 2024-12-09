<?php
$host = "localhost";
$username = "root";
$password = ""; // Par défaut, pas de mot de passe pour XAMPP
$dbname = "ctf_challenge";

// Connexion à la base de données
$conn = new mysqli($host, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}
?>
