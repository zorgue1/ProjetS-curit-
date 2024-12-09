<?php
// Inclure le fichier de connexion à la base de données
include 'db_connect.php';

// Vérifiez la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Méthode non autorisée. Méthode utilisée : " . $_SERVER['REQUEST_METHOD']);
}

// Récupérer les données du formulaire
$prenom = $_POST['prenom'];
$nom = $_POST['nom'];
$email = $_POST['email'];
$password = $_POST['password'];
$confirmPassword = $_POST['confirm-password'];

// Vérifier les mots de passe
if ($password !== $confirmPassword) {
    die("Les mots de passe ne correspondent pas.");
}

// Hash du mot de passe
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Insérer dans la base de données
$stmt = $pdo->prepare("INSERT INTO users (prenom, nom, email, password) VALUES (?, ?, ?, ?)");
try {
    $stmt->execute([$prenom, $nom, $email, $hashedPassword]);
    header("Location: ../HTML/premièrePage.html");
    exit();
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
