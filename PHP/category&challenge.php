

<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ctf_challenge";

$conn = new mysqli($servername, $username, $password, $dbname);


$created_at = date('Y-m-d H:i:s');
$stmt = $conn->prepare("INSERT INTO challenges (name, category, level, description, file_path, flag, description_file, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssss", $name, $category, $level, $description, $file_name, $flag, $description_file_name, $created_at);




// Fetch categories
$sql_categories = "SELECT category FROM challenges GROUP BY category";
$result_categories = $conn->query($sql_categories);

// Fetch challenges
$sql_challenges = "SELECT * FROM challenges ORDER BY created_at DESC";
$result_challenges = $conn->query($sql_challenges);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Vérifiez si 'action' est défini
    if (!isset($_POST['action'])) {
        die("Erreur : aucune action spécifiée !");
    }

    // Routeur d'actions
    switch ($_POST['action']) {
        case 'create_challenge':
            // Création d'un nouveau challenge
            $name = htmlspecialchars($_POST['name']);
            $category = htmlspecialchars($_POST['category']);
            $level = htmlspecialchars($_POST['level']);
            $description = htmlspecialchars($_POST['description']);
            $flag = htmlspecialchars($_POST['flag']);
            $target_dir = "../uploads/";
            $file_name = "";
            $description_file_name = "";

            // Gestion des fichiers
            
            if (!empty($_FILES["file"]["name"])) {
                $file_name = basename($_FILES["file"]["name"]);
                $target_file = $target_dir . $file_name;

                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                if (!move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                    die("Erreur lors du téléchargement du fichier.");
                }
            }

            if (!empty($_FILES["description_file"]["name"])) {
                $description_file_name = basename($_FILES["description_file"]["name"]);
                $description_target_file = $target_dir . $description_file_name;

                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                if (!move_uploaded_file($_FILES["description_file"]["tmp_name"], $description_target_file)) {
                    die("Erreur lors du téléchargement du fichier de description.");
                }
            }

            // Insertion dans la base de données
            $created_at = date('Y-m-d H:i:s');
$stmt = $conn->prepare("INSERT INTO challenges (name, category, level, description, file_path, flag, description_file, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssss", $name, $category, $level, $description, $file_name, $flag, $description_file_name, $created_at);

            if ($stmt->execute()) {
                // Redirigez vers la même page avec un message de succès
                header("Location: " . $_SERVER['PHP_SELF'] . "?result=challenge_created");
                exit();
            } else {
                die("Erreur d'insertion : " . $stmt->error);
            }

            $stmt->close();
            break;

            case 'submit_flag':
                if (!isset($_POST['challenge_id']) || !isset($_POST['flag'])) {
                    die("Erreur : données manquantes pour la soumission du flag !");
                }
            
                $challenge_id = intval($_POST['challenge_id']);
                $submitted_flag = trim($_POST['flag']);
                $user_id = 1; // Remplacez par l'ID réel de l'utilisateur connecté (par exemple via $_SESSION)
            
                // Étape 1 : Vérification du flag correct
                $stmt = $conn->prepare("SELECT flag, level FROM challenges WHERE id = ?");
                $stmt->bind_param("i", $challenge_id);
                $stmt->execute();
                $stmt->bind_result($correct_flag, $challenge_level);
                $stmt->fetch();
                $stmt->close();
            
                if ($correct_flag && $submitted_flag === $correct_flag) {
                    // Étape 2 : Vérifier si l'utilisateur a déjà complété ce challenge
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM user_scores WHERE user_id = ? AND challenge_id = ?");
                    $stmt->bind_param("ii", $user_id, $challenge_id);
                    $stmt->execute();
                    $stmt->bind_result($count);
                    $stmt->fetch();
                    $stmt->close();
            
                    if ($count > 0) {
                        // Rediriger avec un message indiquant que le challenge est déjà complété
                        header("Location: " . $_SERVER['PHP_SELF'] . "?challenge_id=$challenge_id&result=already_completed");
                        exit();
                    }
            
                    // Étape 3 : Déterminer les points à attribuer en fonction de la difficulté
                    $points = 0;
                    if ($challenge_level === 'Beginner') {
                        $points = 1;
                    } elseif ($challenge_level === 'Intermediate') {
                        $points = 3;
                    } elseif ($challenge_level === 'Advanced') {
                        $points = 5;
                    }
            
                    // Étape 4 : Ajouter les points dans la table user_scores
                    $completed_at = date('Y-m-d H:i:s');
                    $stmt = $conn->prepare("INSERT INTO user_scores (user_id, challenge_id, score, completed_at) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iiis", $user_id, $challenge_id, $points, $completed_at);
            
                    if ($stmt->execute()) {
                        // Rediriger avec un message de succès
                        header("Location: " . $_SERVER['PHP_SELF'] . "?challenge_id=$challenge_id&result=flag_success");
                        exit();
                    } else {
                        die("Erreur d'insertion dans user_scores : " . $stmt->error);
                    }
                } else {
                    // Si le flag est incorrect
                    header("Location: " . $_SERVER['PHP_SELF'] . "?challenge_id=$challenge_id&result=flag_fail");
                    exit();
                }
                break;
            
            
        default:
            die("Erreur : action non reconnue.");
    }
}




// Fetch categories
$sql_categories = "SELECT category FROM challenges GROUP BY category";
$result_categories = $conn->query($sql_categories);

// Fetch challenges
$sql_challenges = "SELECT * FROM challenges ORDER BY created_at DESC";
$result_challenges = $conn->query($sql_challenges);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integrated Form, Categories, and Challenges</title>
    <link rel="stylesheet" href="../CSS/category&challenge.css?v=1.0">

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

<button class="back-button" onclick="showCategories()">Back to Categories</button>


<h1 class="page-title">
        <span>Tentez votre chance,</span>
        <span>choisissez un challenge 🏁</span>
    </h1>
<!-- Overlay -->
<div class="overlay" id="overlay" onclick="hidePopup()"></div>

<button class="button-icon" onclick="showPopup()">+</button>


<!-- Form Section -->
<div class="popup-form" id="popupForm">
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="create_challenge">

    <label for="name">Name</label>
    <input type="text" id="name" name="name" placeholder="Challenge Name" required autocomplete="off">

    <label for="category">Category</label>
    <select id="category" name="category" required>
        <option value="">Select Category</option>
        <option value="Cryptology">Cryptology</option>
        <option value="Web">Web</option>
        <option value="Forensic">Forensic</option>
        <option value="Network">Network</option>
    </select>

    <label for="level">Level</label>
    <select id="level" name="level" required>
        <option value="">Select Level</option>
        <option value="Beginner">Beginner</option>
        <option value="Intermediate">Intermediate</option>
        <option value="Advanced">Advanced</option>
    </select>

    <label for="description">Description</label>
    <textarea id="description" name="description" rows="4" placeholder="Enter a description" required autocomplete="off"></textarea>


    <label for="description_file">Description File</label>
    <input type="file" id="description_file" name="description_file">

    <label for="file">File</label>
    <input type="file" id="file" name="file">

    <label for="flag">Flag</label>
    <input type="text" id="flag" name="flag" placeholder="Enter the flag here" required autocomplete="off">



    <button class="form-submit" type="submit">Submit</button>
</form>




</div>

<!-- Categories -->
<div class="card-container" id="categoryContainer">
    <?php if ($result_categories->num_rows > 0): ?>
        <?php while ($row = $result_categories->fetch_assoc()): ?>
            <div class="card" onclick="showChallenges('<?php echo $row['category']; ?>')">
                <h3><?php echo htmlspecialchars($row['category']); ?></h3>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No categories found.</p>
    <?php endif; ?>
</div>



<!-- Challenges -->
<div class="card-container hidden" id="challengeContainer">
    <div id="challengeCards" class="challenge-cards-container"></div>
   
</div>


<!-- Message displayed below categories -->
<div id="globalMessage" class="global-message"></div>

<script>

function hidePopup() {
    document.getElementById('popupForm').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}


function showPopup() {
    document.getElementById('popupForm').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}


function showCategories() {
    document.getElementById('challengeContainer').classList.add('hidden'); // Masque le conteneur des challenges
    document.getElementById('categoryContainer').classList.remove('hidden'); // Affiche le conteneur des catégories
}


document.addEventListener("DOMContentLoaded", () => {
    const params = new URLSearchParams(window.location.search);

    if (params.has("result")) {
        const result = params.get("result");
        const globalMessage = document.getElementById("globalMessage");

        if (result === "success") {
            globalMessage.textContent = "🎉 Bravo, vous avez réussi le challenge et gagné des points !";
            globalMessage.className = "global-message success show";
        } else if (result === "fail") {
            globalMessage.textContent = "❌ Dommage, votre flag est incorrect. Réessayez !";
            globalMessage.className = "global-message error show";
        } else if (result === "already_completed") {
            globalMessage.textContent = "⚠️ Vous avez déjà complété ce challenge !";
            globalMessage.className = "global-message error show";
        }

        // Afficher le message pendant 5 secondes
        setTimeout(() => {
            globalMessage.classList.remove("show");
            globalMessage.style.opacity = "0";
        }, 5000);
    }
});






 






function showChallenges(category) {
    document.getElementById('categoryContainer').classList.add('hidden');
    document.getElementById('challengeContainer').classList.remove('hidden');

    const challengeCards = document.getElementById('challengeCards');
    challengeCards.innerHTML = '';

    const challenges = <?php
        $challenges_array = [];
        if ($result_challenges->num_rows > 0) {
            while ($row = $result_challenges->fetch_assoc()) {
                // Ajout d'un feedback dynamique basé sur les paramètres GET
                $row['feedback'] = ""; // Feedback par défaut
                if (isset($_GET['challenge_id']) && $_GET['challenge_id'] == $row['id']) {
                    if (isset($_GET['result'])) {
                        $row['feedback'] = $_GET['result']; // Passe "success", "fail", etc.
                    }
                }
                $challenges_array[] = $row;
            }
        }
        
        echo json_encode($challenges_array);
    ?>;

    const filteredChallenges = challenges.filter(challenge => challenge.category === category);

    if (filteredChallenges.length > 0) {
        filteredChallenges.forEach(challenge => {
            const card = document.createElement('div');
            card.className = 'card';

            const description = challenge.description.length > 100
                ? challenge.description.substring(0, 100) + '...'
                : challenge.description;

                card.innerHTML = `
    <h3>${challenge.name}</h3>
    <p><b>Category:</b> <em>${challenge.category}</em></p>
    <p><b>Level:</b> <em>${challenge.level}</em></p>
    <p>${description}</p>
    ${
        challenge.file_path
        ? `<a href="../uploads/${challenge.file_path}" download target="_blank" class="btn">Download File</a>`
        : ''
    }
    ${
        challenge.description_file
        ? `<a href="../uploads/${challenge.description_file}" target="_blank" class="btn">View Description File</a>`
        : ''
    }
    <form method="POST" action="">
    <input type="hidden" name="action" value="submit_flag">
    <input type="hidden" name="challenge_id" value="${challenge.id}">
    <label for="flag_${challenge.id}">Submit your flag:</label>
    <input type="text" id="flag_${challenge.id}" name="flag" placeholder="Enter the flag here" required autocomplete="off">
    <button type="submit" class="submit-flag">Submit</button>
</form>




    <div id="feedback_${challenge.id}" class="feedback-container">
    ${
        challenge.feedback === "success"
            ? "🎉 Challenge réussi !"
            : challenge.feedback === "fail"
            ? "❌ Dommage, essayez encore !"
            : challenge.feedback === "file_not_found"
            ? "⚠️ Fichier de solution non trouvé."
            : challenge.feedback === "upload_error"
            ? "❌ Erreur lors de l'envoi du fichier."
            : "No feedback yet."
    }
    </div>
`;







            challengeCards.appendChild(card);
        });
    } else {
        challengeCards.innerHTML = '<p>No challenges found.</p>';
    }
}
</script>

</body>
</html>

<?php $conn->close(); ?>


