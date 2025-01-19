<?php
// Vérifier si l'utilisateur est connecté
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../HTML/identification.html');
    exit();
}

// Récupérer le nom de l'administrateur
$admin_name = htmlspecialchars($_SESSION['user_name']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: white;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header {
    background: #1e1e1e;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between; /* Aligne les éléments sur les bords opposés */
    align-items: center;
    z-index: 1000; 
}

        header h1 {
            font-size: 2rem;
            margin: 0;
        }

        .welcome-message {
    font-size: 1.2rem; /* Taille de la police */
    color: #28a745; /* Un vert plus moderne et agréable */
    text-align: left; /* Aligne le texte à gauche */
    margin-left: 20px; /* Ajoute un petit espacement à gauche */
    font-weight: bold; /* Met le texte en gras pour plus de lisibilité */
}


        .header-buttons {
            display: flex;
            gap: 15px;
        }

        .header-buttons button {
            background-color: #2E2E2E;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }

        .header-buttons button:hover {
            background-color: #444;
            transform: scale(1.05);
        }

        .menu-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px; /* Espace entre les encadrés */
            flex-wrap: wrap; /* Permet de s'adapter sur petits écrans */
            flex-grow: 1; /* Remplit tout l'espace disponible */
            margin: 40px auto; /* Espace autour des encadrés */
        }

        .menu-item {
            width: 300px;
            height: 150px;
            background-color: #1E1E1E;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .menu-item:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 15px rgba(0, 255, 0, 0.4);
        }

        .menu-item a {
            text-decoration: none;
            color: white;
            font-size: 1.2rem;
            font-weight: bold;
        }

        footer {
            margin-top: auto;
            padding: 10px 20px;
            background-color: #1E1E1E;
            width: 100%;
            text-align: center;
            color: white;
        }

        footer a {
            color: #32CD32;
            text-decoration: none;
        }
    </style>
</head>
<body>
<header>
    <div>
        <p class="welcome-message">Bienvenue sur la page administrateur !</p>
    </div>
    <div class="header-buttons">
        <button onclick="window.location.href='admin_dashboard.php'">Espace</button>
        <button onclick="window.location.href='leaderboard.php'">Leaderboard</button>
        <button onclick="window.location.href='category&challenge.php'">Catégories</button>
    </div>
</header>

<div class="menu-container">
    <div class="menu-item">
        <a href="forum_management.php">Forum</a>
    </div>
    <div class="menu-item">
        <a href="user_management.php">Gestion des utilisateurs</a>
    </div>
    <div class="menu-item">
        <a href="challenge_management.php">Gestion des challenges</a>
    </div>
</div>

<footer>
    <p>&copy; 2024 CTF Challenge - Tous droits réservés. | <a id="logoutBt" href="logout.php">Se déconnecter</a></p>
</footer>
</body>
</html>

<script>
    document.getElementById('logoutBt').addEventListener('click', (event) => {
        // Empêcher tout comportement par défaut
        event.preventDefault();

        // Envoyer une requête POST pour déconnexion
        fetch('../PHP/logout.php', {
            method: 'POST'
        })
        .then(response => {
            if (response.ok) {
                // Redirection vers index.html après succès
                window.location.href = '../index.html';
            } else {
                alert('Erreur lors de la déconnexion.');
            }
        })
        .catch(error => {
            console.error('Erreur réseau :', error);
            alert('Impossible de se déconnecter.');
        });
    });
  </script>