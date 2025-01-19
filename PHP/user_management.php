<?php
// Inclure le fichier de connexion à la base de données
include 'db_connect.php';
session_start();

// Vérifier si l'utilisateur est connecté
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../HTML/identification.html');
    exit();
}



// Définir les valeurs par défaut des filtres
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$$sort_column = $_GET['sort_column'] ?? '';
$sort_order = $_GET['sort_order'] ?? '';
$search_query = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';

// Construire la requête SQL avec les filtres
$sql = "SELECT id, prenom, nom, email, role, created_at FROM users WHERE 1=1";

// Appliquer le filtre par rôle
if (!empty($role_filter)) {
    $sql .= " AND role = :role";
}

// Appliquer le filtre de recherche
if (!empty($search_query)) {
    $sql .= " AND (prenom LIKE :search OR nom LIKE :search)";
}

// Appliquer le tri par colonne
$allowed_columns = ['prenom', 'nom', 'created_at'];
if (in_array($sort_column, $allowed_columns)) {
    $sql .= " ORDER BY $sort_column $sort_order";
}

try {
    $stmt = $conn->prepare($sql);

    // Lier le paramètre du rôle si un filtre est appliqué
    if (!empty($role_filter)) {
        $stmt->bindParam(':role', $role_filter);
    }

    // Lier le paramètre de recherche si une recherche est effectuée
    if (!empty($search_query)) {
        $search_term = '%' . $search_query . '%';
        $stmt->bindParam(':search', $search_term);
    }

    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des utilisateurs : " . $e->getMessage());
}

// Gérer les actions d'administrateur (supprimer ou changer le rôle)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $user_id = intval($_POST['user_id']);

        // Supprimer un utilisateur
        if ($_POST['action'] === 'delete') {
            try {
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                header("Location: user_management.php?result=deleted");
                exit();
            } catch (PDOException $e) {
                die("Erreur lors de la suppression de l'utilisateur : " . $e->getMessage());
            }
        }

        // Changer le rôle d'un utilisateur
        if ($_POST['action'] === 'change_role') {
            $new_role = htmlspecialchars($_POST['role']);
            try {
                $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$new_role, $user_id]);
                header("Location: user_management.php?result=role_updated");
                exit();
            } catch (PDOException $e) {
                die("Erreur lors de la mise à jour du rôle : " . $e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs</title>
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

        .filters .btn-apply, .filters .btn-reset {
            background-color: #32CD32;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .filters .btn-apply:hover, .filters .btn-reset:hover {
            background-color: #228B22;
        }

        .user-table {
            width: 100%;
            border-collapse: collapse;
        }

        .user-table th, .user-table td {
            padding: 15px;
            text-align: left;
            border: 1px solid #444;
        }

        .user-table th {
            background-color: #2E2E2E;
            cursor: pointer;
        }

        .user-table tr:nth-child(even) {
            background-color: #2E2E2E;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-delete {
            background-color: #FF4C4C;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-delete:hover {
            background-color: #FF0000;
        }

        .btn-change-role {
            background-color: #32CD32;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-change-role:hover {
            background-color: #228B22;
        }

        footer {
            text-align: center;
            padding: 10px;
            background-color: #1E1E1E;
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
    <a href="admin_dashboard.php" class="btn-dashboard">Retour au dashboard</a>
</header>

<h1>Gestion des utilisateurs</h1>

<div class="container">
    <div class="filters">
        <form method="GET">
            <span>Filtres :</span>
            <select name="role">
                <option value="">Tous les rôles</option>
                <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>Utilisateur</option>
                <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
            </select>

            <select name="sort_column">
                <option value="" disabled selected>Filtrer par</option>
                <option value="prenom" <?php echo $sort_column === 'prenom' ? 'selected' : ''; ?>>Prénom</option>
                <option value="nom" <?php echo $sort_column === 'nom' ? 'selected' : ''; ?>>Nom</option>
                <option value="created_at" <?php echo $sort_column === 'created_at' ? 'selected' : ''; ?>>Date d'inscription</option>
            </select>

            <select name="sort_order">
                <option value="" disabled selected>Ordre</option>
                <option value="ASC" <?php echo $sort_order === 'ASC' ? 'selected' : ''; ?>>Croissant</option>
                <option value="DESC" <?php echo $sort_order === 'DESC' ? 'selected' : ''; ?>>Décroissant</option>
            </select>

            <input type="text" name="search_query" placeholder="Rechercher prénom ou nom" value="<?php echo htmlspecialchars($search_query); ?>">

            <button type="submit" class="btn-apply">Appliquer</button>
            <a href="user_management.php" class="btn-reset">Réinitialiser</a>
        </form>
    </div>

    <table class="user-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Prénom</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Date de création</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['prenom']); ?></td>
                    <td><?php echo htmlspecialchars($user['nom']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td><?php echo $user['created_at']; ?></td>
                    <td>
                        <div class="action-buttons">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="change_role">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <select name="role" required>
                                    <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Utilisateur</option>
                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                                <button type="submit" class="btn-change-role">Changer</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<footer>
    <p>&copy; 2024 CTF Challenge - Tous droits réservés.</p>
</footer>
</body>
</html>