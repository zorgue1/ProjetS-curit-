<?php
// Détails de connexion à la base de données PostgreSQL
$host = "localhost";
$dbname = "Project";
$username = "postgres";
$password = "password";

try {
    // Connexion à la base de données PostgreSQL
    $pdo = new PDO("pgsql:host=$host;port=5432;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Vérification si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validation des champs (côté serveur)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("<p>Email non conforme.</p>");
    }

    if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/', $password)) {
        die("<p>Mot de passe non conforme. Minimum : 1 majuscule, 1 chiffre, 1 caractère spécial, 8 caractères.</p>");
    }

    if ($password !== $confirmPassword) {
        die("<p>Mot de passe différent.</p>");
    }

    // Vérification si l'email existe déjà
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        die("<p>Erreur : Un compte avec cet email existe déjà.</p>");
    }

    // Hashage du mot de passe
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insertion des données dans la base de données
    $sql = "INSERT INTO users (firstname, lastname, email, password) VALUES (:firstname, :lastname, :email, :password)";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':firstname', $firstname);
    $stmt->bindParam(':lastname', $lastname);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashedPassword);

    if ($stmt->execute()) {
        echo "<p>Compte créé avec succès ! <a href='identification.html'>Connectez-vous</a></p>";
    } else {
        echo "<p>Erreur lors de la création du compte.</p>";
    }
}
?>
