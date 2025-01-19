<?php
// Inclure le fichier de connexion à la base de données
include 'db_connect.php';

// Vérifier si l'utilisateur est connecté
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../HTML/identification.html');
    exit();
}

// Initialiser les filtres
$category_filter = $_GET['category'] ?? '';
$sort_column = $_GET['sort_column'] ?? '';
$sort_order = $_GET['sort_order'] ?? '';
$search_query = $_GET['search_query'] ?? '';

// Construire la requête SQL
$sql = "SELECT * FROM challenges WHERE 1=1";

// Filtrer par catégorie
if (!empty($category_filter)) {
    $sql .= " AND category = :category";
}

// Rechercher par nom ou description
if (!empty($search_query)) {
    $sql .= " AND (name LIKE :search_query OR description LIKE :search_query)";
}

// Trier par colonne et ordre
if (!empty($sort_column) && !empty($sort_order)) {
    $sql .= " ORDER BY $sort_column $sort_order";
} else {
    $sql .= " ORDER BY created_at DESC"; // Par défaut
}

// Préparer et exécuter la requête
$stmt = $conn->prepare($sql);

// Lier les paramètres
if (!empty($category_filter)) {
    $stmt->bindParam(':category', $category_filter);
}
if (!empty($search_query)) {
    $search_param = "%$search_query%";
    $stmt->bindParam(':search_query', $search_param);
}

$stmt->execute();
$challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gérer les actions (ajouter, modifier, supprimer un défi)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        // Supprimer un défi
        $challenge_id = $_POST['challenge_id'] ?? '';
        $stmt = $conn->prepare("DELETE FROM challenges WHERE id = :id");
        $stmt->bindParam(':id', $challenge_id);
        $stmt->execute();
        header("Location: challenge_management.php");
        exit();
    } elseif ($action === 'update') {
        // Modifier un défi
        $challenge_id = $_POST['challenge_id'];
        $name = htmlspecialchars($_POST['name']);
        $category = htmlspecialchars($_POST['category']);
        $level = htmlspecialchars($_POST['level']);
        $description = htmlspecialchars($_POST['description']);
        $flag = htmlspecialchars($_POST['flag']);

        $stmt = $conn->prepare("UPDATE challenges SET name = :name, category = :category, level = :level, description = :description, flag = :flag WHERE id = :id");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':level', $level);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':flag', $flag);
        $stmt->bindParam(':id', $challenge_id);
        $stmt->execute();

        header("Location: challenge_management.php");
        exit();
    } elseif ($action === 'add') {
        // Ajouter un nouveau défi
        $name = htmlspecialchars($_POST['name']);
        $category = htmlspecialchars($_POST['category']);
        $level = htmlspecialchars($_POST['level']);
        $description = htmlspecialchars($_POST['description']);
        $flag = htmlspecialchars($_POST['flag']);
        $target_dir = "uploads/";
        $file_path = '';
        $description_file = '';

        if (!empty($_FILES["file"]["name"])) {
            $file_path = basename($_FILES["file"]["name"]);
            move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir . $file_path);
        }

        if (!empty($_FILES["description_file"]["name"])) {
            $description_file = basename($_FILES["description_file"]["name"]);
            move_uploaded_file($_FILES["description_file"]["tmp_name"], $target_dir . $description_file);
        }

        $stmt = $conn->prepare("INSERT INTO challenges (name, category, level, description, file_path, description_file, flag, created_at) VALUES (:name, :category, :level, :description, :file_path, :description_file, :flag, NOW())");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':level', $level);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':file_path', $file_path);
        $stmt->bindParam(':description_file', $description_file);
        $stmt->bindParam(':flag', $flag);
        $stmt->execute();

        header("Location: challenge_management.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des défis</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: white;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header {
            padding: 10px 20px;
            background-color: #1E1E1E;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header .btn-dashboard {
            background-color: #32CD32;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }

        header .btn-dashboard:hover {
            background-color: #228B22;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #1E1E1E;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            flex: 1;
        }

        .filters {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .filters form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .filters form select, .filters form input {
            padding: 10px;
            border-radius: 5px;
            background-color: #2E2E2E;
            color: white;
            border: 1px solid #444;
        }

        .filters .btn-apply, .filters .btn-reset{
            background-color: #32CD32;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .filters .btn-add {
            background-color: White;
            color: Black;
            padding: 10px 15px;
            border: none;
            margin-left: 5px; 
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .filters .btn-apply:hover, .filters .btn-reset:hover, .filters .btn-add:hover {
            background-color: #228B22;
        }

        .challenge-table {
            width: 100%;
            border-collapse: collapse;
        }

        .challenge-table th, .challenge-table td {
            padding: 15px;
            text-align: left;
            border: 1px solid #444;
        }

        .challenge-table th {
            background-color: #2E2E2E;
            cursor: pointer;
        }

        .challenge-table tr:nth-child(even) {
            background-color: #2E2E2E;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-delete, .btn-edit {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-delete {
            background-color: #FF4C4C;
            color: white;
        }

        .btn-delete:hover {
            background-color: #FF0000;
        }

        .btn-edit {
            background-color: #007BFF;
            color: white;
        }

        .btn-edit:hover {
            background-color: #0056b3;
        }

        .form-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            background-color: #1E1E1E;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
        }

        .form-popup input, .form-popup select, .form-popup textarea {
            width: calc(100% - 20px);
            margin-bottom: 10px;
            padding: 10px;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 5px;
        }

        .form-popup button {
            background: linear-gradient(90deg, #32CD32, #228B22);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .form-popup button:hover {
            background: linear-gradient(90deg, #228B22, #32CD32);
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        footer {
            text-align: center;
            padding: 10px;
            background-color: #1E1E1E;
            color: white;
        }
    </style>
</head>
<body>
<header>
    <a href="admin_dashboard.php" class="btn-dashboard">Retour au dashboard</a>
</header>

<h1>Gestion des challenges</h1>

<div class="container">
    <div class="filters">
        <form method="GET">
            <span>Filtres :</span>
            <select name="category">
                <option value="">Toutes les catégories</option>
                <option value="Cryptology">Cryptology</option>
                <option value="Web">Web</option>
                <option value="Forensic">Forensic</option>
                <option value="Network">Network</option>
            </select>
            <select name="sort_column">
                <option value="">Filtrer par</option>
                <option value="name">Nom</option>
                <option value="created_at">Date de création</option>
            </select>
            <select name="sort_order">
                <option value="">Ordre</option>
                <option value="ASC">Croissant</option>
                <option value="DESC">Décroissant</option>
            </select>
            <input type="text" name="search_query" placeholder="Rechercher un challenge">
            <button type="submit" class="btn-apply">Appliquer</button>
            <a href="challenge_management.php" class="btn-reset">Réinitialiser</a>
        </form>
        <button class="btn-add" onclick="openAddForm()">Ajouter un challenge</button>
    </div>

    <table class="challenge-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Catégorie</th>
                <th>Niveau</th>
                <th>Date de création</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($challenges as $challenge): ?>
                <tr>
                    <td><?= $challenge['id'] ?></td>
                    <td><?= htmlspecialchars($challenge['name']) ?></td>
                    <td><?= htmlspecialchars($challenge['category']) ?></td>
                    <td><?= htmlspecialchars($challenge['level']) ?></td>
                    <td><?= $challenge['created_at'] ?></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-edit" onclick="openEditForm(<?= htmlspecialchars(json_encode($challenge)) ?>)">Modifier</button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="challenge_id" value="<?= $challenge['id'] ?>">
                                <button class="btn-delete" onclick="return confirm('Êtes-vous sûr ?')">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="overlay" id="overlay" onclick="closeForm()"></div>

<!-- Formulaire de modification -->
<div class="form-popup" id="editForm">
    <form method="POST">
        <input type="hidden" name="action" value="update">
        <input type="hidden" id="challenge_id" name="challenge_id">
        <label for="name">Nom</label>
        <input type="text" id="name" name="name" required>
        <label for="category">Catégorie</label>
        <select id="category" name="category" required>
            <option value="Cryptology">Cryptology</option>
            <option value="Web">Web</option>
            <option value="Forensic">Forensic</option>
            <option value="Network">Network</option>
        </select>
        <label for="level">Niveau</label>
        <select id="level" name="level" required>
            <option value="Beginner">Beginner</option>
            <option value="Intermediate">Intermediate</option>
            <option value="Advanced">Advanced</option>
        </select>
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4" required></textarea>
        <label for="flag">Flag</label>
        <input type="text" id="flag" name="flag" required>
        <button type="submit">Enregistrer</button>
    </form>
</div>

<!-- Formulaire d'ajout -->
<div class="form-popup" id="addForm">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <label for="name">Nom</label>
        <input type="text" id="name" name="name" required>
        <label for="category">Catégorie</label>
        <select id="category" name="category" required>
            <option value="Cryptology">Cryptology</option>
            <option value="Web">Web</option>
            <option value="Forensic">Forensic</option>
            <option value="Network">Network</option>
        </select>
        <label for="level">Niveau</label>
        <select id="level" name="level" required>
            <option value="Beginner">Beginner</option>
            <option value="Intermediate">Intermediate</option>
            <option value="Advanced">Advanced</option>
        </select>
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4" required></textarea>
        <label for="description_file">Fichier de description</label>
        <input type="file" id="description_file" name="description_file">
        <label for="file">Fichier</label>
        <input type="file" id="file" name="file">
        <label for="flag">Flag</label>
        <input type="text" id="flag" name="flag" required>
        <button type="submit">Ajouter</button>
    </form>
</div>

<script>
    function openEditForm(data) {
        document.getElementById('editForm').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
        document.getElementById('challenge_id').value = data.id;
        document.getElementById('name').value = data.name;
        document.getElementById('category').value = data.category;
        document.getElementById('level').value = data.level;
        document.getElementById('description').value = data.description;
        document.getElementById('flag').value = data.flag;
    }

    function closeForm() {
        document.getElementById('editForm').style.display = 'none';
        document.getElementById('addForm').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
    }

    function openAddForm() {
        document.getElementById('addForm').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
    }
</script>

<footer>
    <p>&copy; 2024 CTF Challenge - Tous droits réservés.</p>
</footer>
</body>
</html>