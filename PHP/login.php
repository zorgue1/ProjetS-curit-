<?php
// Inclure le fichier de connexion à la base de données
include 'db_connect.php';
session_start();

// Vérifier que les données sont envoyées en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Méthode non autorisée.");
}

// Récupérer les données du formulaire
$email = trim($_POST['email']);
$password = $_POST['password'];

try {
    // Préparer la requête pour récupérer les informations de l'utilisateur
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    // Vérifier si l'utilisateur existe
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        die("Adresse e-mail ou mot de passe incorrect.");
    }

    // Vérifier le mot de passe
    if (!password_verify($password, $user['password'])) {
        die("Adresse e-mail ou mot de passe incorrect.");
    }

    // Connexion réussie, redirection vers la page de succès
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['prenom'] . " " . $user['nom'];
    header("Location: ../PHP/premièrePage.php");
    exit();
} catch (PDOException $e) {
    die("Erreur lors de la connexion : " . $e->getMessage());
}
