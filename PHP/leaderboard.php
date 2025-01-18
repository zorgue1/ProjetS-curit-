<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ctf_challenge";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer les données des utilisateurs classées par leurs scores totaux
$sql = "SELECT CONCAT(users.prenom, ' ', users.nom) AS full_name, SUM(user_scores.score) AS total_score
        FROM users
        JOIN user_scores ON users.id = user_scores.user_id
        GROUP BY users.id
        ORDER BY total_score DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <link rel="stylesheet" href="../CSS/leaderboard.css">
</head>
<body>
    <header>
        <div class="navbar">
            <div class="nav-buttons">
                <button onclick="window.location.href='category&challenge.php'">Catégories</button>
                <button onclick="window.location.href='leaderboard.php'">Leaderboard</button>
                <button onclick="window.location.href='premièrePage.php'">Mon espace</button>
            </div>
        </div>
    </header>

    <main>
    <h1 class="leaderboard-title">
        Découvrez les meilleurs, <br> êtes-vous prêt à relever le défi ? 🏆
    </h1>
        <div class="leaderboard-container">
            <?php if ($result->num_rows > 0): ?>
                <table class="leaderboard-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Full Name</th>
                            <th>Total Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $rank = 1;
                        while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $rank++; ?></td>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo $row['total_score']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No users found.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

<?php
$conn->close();
?>
