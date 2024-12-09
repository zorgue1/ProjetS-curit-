<?php
session_start();
include 'database_connection.php'; // Modifiez selon votre configuration

// Récupération des données de l'utilisateur depuis la base
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, prenom, nom FROM users WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_prenom'] = $user['prenom'];
        $_SESSION['user_nom'] = $user['nom'];

        // Redirection vers la page principale
        header("Location: premièrePage.php");
        exit();
    } else {
        echo "Identifiants invalides.";
    }
}
?>
