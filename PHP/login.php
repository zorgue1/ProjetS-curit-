<?php
// Inclure le fichier de connexion à la base de données
include 'db_connect.php';

// Vérifiez que la méthode HTTP est POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Préparer une requête SQL pour vérifier l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Vérifiez si l'utilisateur existe et que le mot de passe est correct
    if ($user && password_verify($password, $user['password'])) {
        // Démarrer une session pour gérer l'utilisateur connecté (optionnel)
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['prenom'];

        // Redirection vers la page 'premièrePage.html'
        header("Location: ../HTML/premièrePage.html");
        exit();
    } else {
        // Si les identifiants sont incorrects, redirigez vers la page de connexion avec un message d'erreur
        echo "Identifiants incorrects.";
    }
} else {
    // Si la méthode n'est pas POST, affichez un message d'erreur
    echo "Méthode non autorisée.";
}
?>
