<?php
// Détails de connexion à la base de données PostgreSQL
$host = "localhost";       // Adresse du serveur PostgreSQL
$dbname = "Project";       // Nom de la base de données PostgreSQL
$username = "postgres";    // Nom d'utilisateur PostgreSQL
$password = "password";    // Mot de passe PostgreSQL

try {
    // Connexion à la base de données PostgreSQL
    $pdo = new PDO("pgsql:host=$host;port=5432;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Vérification si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Requête préparée pour éviter les injections SQL
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    // Vérification des résultats
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérification du mot de passe (utilisation de password_verify)
        if (password_verify($password, $user['password'])) {
            echo "<p>Connexion réussie !</p>";
        } else {
            echo "<p>Erreur : Mot de passe incorrect.</p>";
        }
    } else {
        echo "<p>Erreur : Adresse e-mail incorrecte.</p>";
    }
}

?>