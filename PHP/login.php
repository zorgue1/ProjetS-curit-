<?php
// Inclure la connexion à la base de données
include 'db_connect.php';

// Activez les erreurs pour déboguer (optionnel)
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Vérifier l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Démarrer la session
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['nom'] = $user['nom'];

        // Rediriger vers la première page
        header("Location: ../HTML/premièrePage.html");
        exit();
    } else {
        echo "Identifiants incorrects.";
    }
} else {
    echo "Méthode non autorisée.";
}
?>
