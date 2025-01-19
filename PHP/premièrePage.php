<?php
session_start();

// Si l'utilisateur n'est pas connecté, rediriger vers index.html
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.html');
    exit();
}

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ctf_challenge";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer le score total de l'utilisateur
$user_id = $_SESSION['user_id'];
$sql = "SELECT SUM(score) as total_score FROM user_scores WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_score = $row['total_score'] ?? 0; // Si pas de score, on met 0 par défaut

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Première Page</title>
  <link rel="stylesheet" href="../CSS/premièrePage.css">
</head>
<body>
  <div class="deconnexion-button">
    <button id="logoutBtn">Déconnexion</button>
  </div>

  <div class="container">
    <!-- Informations de l'utilisateur -->
    <div class="user-info">
    <h1>Bienvenue, <span id="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur'); ?></span></h1>
      <div class="score-section">
        <div class="score-label">Score total</div>
        <div class="score-value">
            <?php echo $total_score; ?>
            <span class="score-unit">pts</span>
        </div>
    </div>
  </div>

    <!-- Boutons centraux -->
    <div class="challenge-button">
      <a href="../PHP/category&challenge.php" class="btn">Challenge TOI !</a>
      <a href="../PHP/leaderboard.php" class="btn">Leaderboard</a>
    </div>
  </div>

  <script>
    document.getElementById('logoutBtn').addEventListener('click', (event) => {
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
</body>
</html>