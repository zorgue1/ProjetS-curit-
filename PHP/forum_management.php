<?php
session_start();

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../HTML/identification.html");
    exit();
}

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ctf_challenge";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Ajouter un nouveau sujet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_topic') {
        $title = htmlspecialchars($_POST['title']);
        $content = htmlspecialchars($_POST['content']);
        $user_id = $_SESSION['user_id'];

        $stmt = $conn->prepare("INSERT INTO forum_topics (title, content, user_id, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("ssi", $title, $content, $user_id);
        $stmt->execute();
    } elseif ($_POST['action'] === 'add_reply' && isset($_POST['topic_id'])) {
        $topic_id = intval($_POST['topic_id']);
        $content = htmlspecialchars($_POST['content']);
        $user_id = $_SESSION['user_id'];

        $stmt = $conn->prepare("INSERT INTO forum_replies (topic_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $topic_id, $user_id, $content);
        $stmt->execute();
    } elseif ($_POST['action'] === 'delete_topic' && isset($_POST['topic_id'])) {
        $topic_id = intval($_POST['topic_id']);
        $stmt = $conn->prepare("DELETE FROM forum_topics WHERE id = ?");
        $stmt->bind_param("i", $topic_id);
        $stmt->execute();
        header("Location: forum_management.php");
        exit();
    } elseif ($_POST['action'] === 'delete_reply' && isset($_POST['reply_id'])) {
        $reply_id = intval($_POST['reply_id']);
        $stmt = $conn->prepare("DELETE FROM forum_replies WHERE id = ?");
        $stmt->bind_param("i", $reply_id);
        $stmt->execute();
    }
}

// Récupérer tous les sujets
$topics_query = "SELECT forum_topics.id, forum_topics.title, forum_topics.created_at, users.prenom AS username
                 FROM forum_topics 
                 JOIN users ON forum_topics.user_id = users.id
                 ORDER BY forum_topics.created_at DESC";
$topics_result = $conn->query($topics_query);

// Récupérer les réponses pour un sujet sélectionné
$selected_topic = null;
$replies = [];
if (isset($_GET['topic_id'])) {
    $topic_id = intval($_GET['topic_id']);

    // Sujet sélectionné
    $selected_topic_query = $conn->prepare("SELECT forum_topics.title, forum_topics.content, forum_topics.created_at, users.prenom AS username
                                             FROM forum_topics
                                             JOIN users ON forum_topics.user_id = users.id
                                             WHERE forum_topics.id = ?");
    $selected_topic_query->bind_param("i", $topic_id);
    $selected_topic_query->execute();
    $selected_topic = $selected_topic_query->get_result()->fetch_assoc();

    // Réponses associées au sujet
    $replies_query = $conn->prepare("SELECT forum_replies.id AS reply_id, forum_replies.content, forum_replies.created_at, users.prenom AS username, users.role
                                      FROM forum_replies
                                      JOIN users ON forum_replies.user_id = users.id
                                      WHERE forum_replies.topic_id = ?
                                      ORDER BY forum_replies.created_at ASC");
    $replies_query->bind_param("i", $topic_id);
    $replies_query->execute();
    $replies = $replies_query->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: white;
            margin: 0;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            width: 25%;
            background-color: #1E1E1E;
            overflow-y: auto;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.5);
        }

        .sidebar h2 {
            text-align: center;
        }

        .topic {
            padding: 10px;
            margin-bottom: 10px;
            background-color: #2E2E2E;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .topic:hover {
            background-color: #444;
        }

        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 20px;
            overflow-y: auto;
        }

        .topic-details {
            flex: 1;
            margin-bottom: 20px;
        }

        .chat-box {
            background-color: #1E1E1E;
            padding: 20px;
            border-radius: 10px;
            max-height: 300px;
            overflow-y: auto;
        }

        .message {
            margin-bottom: 10px;
        }

        .message strong.admin {
            color: red;
        }

        .message strong.user {
            color: green;
        }

        .form {
            margin-top: 10px;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #2E2E2E;
            border: 1px solid #444;
            border-radius: 5px;
            color: white;
        }

        button {
            background-color: #32CD32;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #228B22;
        }

        .delete-button {
            background-color: red;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .delete-button:hover {
            background-color: darkred;
        }

        .add-topic {
            margin-bottom: 20px;
        }

        .back-button {
            margin-top: 20px;
            display: block;
            text-align: center;
            padding: 10px 20px;
            background: linear-gradient(90deg, #32CD32, #228B22);
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
        }

        .back-button:hover {
            background: linear-gradient(90deg, #228B22, #32CD32);
        }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>Sujets</h2>
    <?php while ($topic = $topics_result->fetch_assoc()): ?>
        <div class="topic" onclick="window.location.href='?topic_id=<?= $topic['id'] ?>'">
            <strong><?= htmlspecialchars($topic['title']) ?></strong>
            <p>Par <?= htmlspecialchars($topic['username']) ?>, <?= $topic['created_at'] ?></p>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="delete_topic">
                    <input type="hidden" name="topic_id" value="<?= $topic['id'] ?>">
                    <button class="delete-button" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce sujet ?')">Supprimer</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>

    <!-- Formulaire pour ajouter un sujet -->
    <div class="add-topic">
        <h3>Ajouter un sujet</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add_topic">
            <input type="text" name="title" placeholder="Titre" required>
            <textarea name="content" rows="4" placeholder="Contenu" required></textarea>
            <button type="submit">Ajouter</button>
        </form>
    </div>

    <a class="back-button" href="admin_dashboard.php">Retour au dashboard</a>
</div>

<div class="content">
    <?php if ($selected_topic): ?>
        <div class="topic-details">
            <h1><?= htmlspecialchars($selected_topic['title']) ?></h1>
            <p><strong>Par <?= htmlspecialchars($selected_topic['username']) ?></strong> - <?= $selected_topic['created_at'] ?></p>
            <p><?= nl2br(htmlspecialchars($selected_topic['content'])) ?></p>
        </div>

        <div class="chat-box">
            <h2>Discussion</h2>
            <?php foreach ($replies as $reply): ?>
                <div class="message">
                    <strong class="<?= $reply['role'] === 'admin' ? 'admin' : 'user' ?>">
                        <?= htmlspecialchars($reply['username']) ?>
                    </strong>: <?= nl2br(htmlspecialchars($reply['content'])) ?>
                    <small>(<?= $reply['created_at'] ?>)</small>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete_reply">
                            <input type="hidden" name="reply_id" value="<?= $reply['reply_id'] ?>">
                            <button class="delete-button" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?')">Supprimer</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Formulaire pour envoyer un message -->
        <form class="form" method="POST">
            <input type="hidden" name="action" value="add_reply">
            <input type="hidden" name="topic_id" value="<?= $topic_id ?>">
            <textarea name="content" rows="3" placeholder="Votre message..." required></textarea>
            <button type="submit">Envoyer</button>
        </form>
    <?php else: ?>
        <p>Sélectionnez un sujet pour afficher les détails et participer à la discussion.</p>
    <?php endif; ?>
</div>
</body>
</html>
<?php $conn->close(); ?>