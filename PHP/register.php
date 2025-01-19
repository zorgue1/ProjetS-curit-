<?php

require_once 'security.php';

// Inclure le fichier de connexion à la base de données
include 'db_connect.php';

// Vérifier que les données sont envoyées en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Méthode non autorisée. Méthode utilisée : " . $_SERVER['REQUEST_METHOD']);
}

// Récupérer les données du formulaire
$prenom = trim($_POST['firstname']);
$nom = trim($_POST['lastname']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirmPassword = $_POST['confirmPassword'];

// Vérification des mots de passe
if ($password !== $confirmPassword) {
    die("Les mots de passe ne correspondent pas.");
}

// Hasher le mot de passe
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

try {
    // Préparer la requête SQL
    $stmt = $conn->prepare("INSERT INTO users (prenom, nom, email, password) VALUES (:prenom, :nom, :email, :password)");
    $stmt->bindParam(':prenom', $prenom);
    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashedPassword);

    // Exécuter la requête
    $stmt->execute();

    // Redirection après succès
    header("Location: premièrePage.php");
    exit();
} catch (PDOException $e) {
    die("Erreur lors de la création du compte : " . $e->getMessage());
}
?>
