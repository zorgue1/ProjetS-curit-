<?php

require_once 'security.php';

// Inclure le fichier de connexion à la base de données
include 'db_connect.php';
session_start();

// Initialiser les variables pour la gestion des erreurs et des tentatives
$error_message = '';
$max_attempts = 5; // Nombre maximum de tentatives
$lockout_time = 30; // Temps de verrouillage en secondes

// Vérifier si le formulaire de connexion a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Vérifier si l'utilisateur est actuellement bloqué
    if (isset($_SESSION['lockout']) && time() < $_SESSION['lockout']) {
        $remaining_time = $_SESSION['lockout'] - time();
        header('Location: ../HTML/identification.html?error=locked&time=' . $remaining_time);
        exit();
    } else {
        // Réinitialiser les tentatives après la fin de la période de verrouillage
        if (isset($_SESSION['lockout']) && time() >= $_SESSION['lockout']) {
            unset($_SESSION['attempts']);
            unset($_SESSION['lockout']);
        }

        // Initialiser le compteur de tentatives s'il n'existe pas
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
                $_SESSION['attempts']++; // Incrémenter les tentatives en cas d'échec
                if ($_SESSION['attempts'] >= $max_attempts) {
                    // Bloquer l'utilisateur pour un temps défini
                    $_SESSION['lockout'] = time() + $lockout_time;
                    header('Location: ../HTML/identification.html?error=locked&time=' . $lockout_time);
                    exit();
                } else {
                    // Calculer le nombre de tentatives restantes
                    $remaining_attempts = $max_attempts - $_SESSION['attempts'];
                    header('Location: ../HTML/identification.html?error=auth&attempts=' . $remaining_attempts);
                    exit();
                }
            } else {
                // Réinitialiser les tentatives en cas de succès
                unset($_SESSION['attempts']);
                unset($_SESSION['lockout']);

                // Stocker les informations de l'utilisateur dans la session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['prenom'] . " " . $user['nom'];
                $_SESSION['user_role'] = $user['role']; // Stocker le rôle (admin ou user)

                // Rediriger en fonction du rôle de l'utilisateur
                if ($user['role'] === 'admin') {
                    header("Location: ../PHP/admin_dashboard.php"); // Exemple : une page dédiée pour les admins
                } else {
                    header("Location: ../PHP/premièrePage.php"); // Page utilisateur classique
                }
                exit();
            }
        } catch (PDOException $e) {
            // En cas d'erreur système
            header('Location: ../HTML/identification.html?error=system');
            exit();
        }
    }
}
?>