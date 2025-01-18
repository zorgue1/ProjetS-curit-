<?php

require_once 'security.php';

// Inclure le fichier de connexion à la base de données
include 'db_connect.php';
session_start();

// Initialiser les messages d'erreur
$error_message = '';
$max_attempts = 5;
$lockout_time = 30; // Changé à 30 secondes comme demandé

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Vérifier si l'utilisateur est bloqué
    if (isset($_SESSION['lockout']) && time() < $_SESSION['lockout']) {
        $remaining_time = $_SESSION['lockout'] - time();
        header('Location: ../HTML/identification.html?error=locked&time=' . $remaining_time);
        exit();
    } else {
        // Réinitialiser les tentatives après le temps d'attente
        if (isset($_SESSION['lockout']) && time() >= $_SESSION['lockout']) {
            unset($_SESSION['attempts']);
            unset($_SESSION['lockout']);
        }

        // Vérifier les tentatives précédentes
        if (!isset($_SESSION['attempts'])) {
            $_SESSION['attempts'] = 0;
        }

        try {
            // Préparer la requête pour récupérer les informations de l'utilisateur
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            // Vérifier si l'utilisateur existe
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user || !password_verify($password, $user['password'])) {
                $_SESSION['attempts']++;
                if ($_SESSION['attempts'] >= $max_attempts) {
                    $_SESSION['lockout'] = time() + $lockout_time;
                    header('Location: ../HTML/identification.html?error=locked&time=' . $lockout_time);
                    exit();
                } else {
                    $remaining_attempts = $max_attempts - $_SESSION['attempts'];
                    header('Location: ../HTML/identification.html?error=auth&attempts=' . $remaining_attempts);
                    exit();
                }
            } else {
                // Réinitialiser les tentatives en cas de succès
                unset($_SESSION['attempts']);
                unset($_SESSION['lockout']);

                // Connexion réussie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['prenom'] . " " . $user['nom'];
                header("Location: ../PHP/premièrePage.php");
                exit();
            }
        } catch (PDOException $e) {
            header('Location: ../HTML/identification.html?error=system');
            exit();
        }
    }
}
?>