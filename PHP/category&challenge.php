<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ctf_challenge";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



// Fetch categories
$sql_categories = "SELECT category FROM challenges GROUP BY category";
$result_categories = $conn->query($sql_categories);

// Fetch challenges
$sql_challenges = "SELECT * FROM challenges ORDER BY created_at DESC";
$result_challenges = $conn->query($sql_challenges);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = htmlspecialchars($_POST['name'] ?? '');
    $category = htmlspecialchars($_POST['category'] ?? '');
    $level = htmlspecialchars($_POST['level'] ?? '');
    $description = htmlspecialchars($_POST['description'] ?? '');
    $target_dir = "../uploads/";
    $file_name = "";

    // Check if file is uploaded
    if (!empty($_FILES["file"]["name"])) {
        $file_name = basename($_FILES["file"]["name"]);
        $target_file = $target_dir . $file_name;

        // Ensure the uploads directory exists
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                die("Failed to create the 'uploads' directory. Check permissions.");
            }
        }

        // Move the uploaded file
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            echo "File uploaded successfully: $file_name<br>";
        } else {
            die("Error uploading file. Error code: " . $_FILES["file"]["error"]);
        }
    }

    // Prepare and execute the database insertion
    $stmt = $conn->prepare("INSERT INTO challenges (name, category, level, description, file_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $category, $level, $description, $file_name);

    if ($stmt->execute()) {
        echo "Challenge added successfully!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error inserting data: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integrated Form, Categories, and Challenges</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: white;
            margin: 0;
            padding: 0;
        }
 
        h1 {
            text-align: center;
            margin-top: 20px;
        }
 
        /* Popup and Form Styles */
        .button-icon {
            padding: 10px;
            background: linear-gradient(90deg, #32CD32, #228B22);
            color: white;
            font-size: 18px;
            border: none;
            cursor: pointer;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            transition: transform 0.2s, background 0.3s;
            position: fixed;
            bottom: 5%;
            left: 5%;
        }
 
        .button-icon:hover {
            background: linear-gradient(90deg, #228B22, #32CD32);
            transform: scale(1.1);
        }
 
        .popup-form {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            background-color: #1E1E1E;
            padding: 25px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            border-radius: 15px;
            width: 400px;
        }
 
        .popup-form label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
        }
 
        .popup-form input,
        .popup-form select,
        .popup-form textarea,
        .popup-form button {
            width: calc(100% - 20px);
            margin-bottom: 15px;
            padding: 12px;
            border: 1px solid #444;
            border-radius: 10px;
            background-color: #2E2E2E;
            color: white;
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
 
        .card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px;
            width: 90%;
            margin: 80px auto;
        }
 
       
 
        .card {
            background-color: #1E1E1E;
            padding: 20px;
            border-radius: 10px;
            width: 200px;
            text-align: center;
        }
 
        
 
        .challenge-cards-container {
            display: flex;
            flex-wrap: wrap; /* Wrap cards to the next line when space is insufficient */
            justify-content: center; /* Center align all cards */
            gap: 20px; /* Maintain equal spacing */
            padding: 20px;
        }
 
        /* Headings inside Cards */
        .card h3 {
            margin-top: 0;
            font-size: 18px;
        }
 
        /* Paragraphs inside Cards */
        .card p {
            margin: 10px 0;
            font-size: 14px;
        }
 
        /* Styling for Buttons */
        .card .button-group {
            display: flex; /* Align buttons horizontally */
            justify-content: center; /* Center buttons inside the card */
            gap: 10px; /* Add spacing between buttons */
            margin-top: 10px; /* Space between buttons and card content */
        }
 
        /* View File Button */
        .card .file-link {
            background-color: #444;
            color: white;
            padding: 10px 15px;
            font-size: 14px;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }
 
        /* View File Button Hover Effect */
        .card .file-link:hover {
            background-color: #555;
            color: white;
        }
 
        /* Get Started Button */
        .card .get-started {
            background: linear-gradient(90deg, #32CD32, #228B22);
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            cursor: pointer;
        }
 
        /* Get Started Button Hover Effect */
        .card .get-started:hover {
            background: linear-gradient(90deg, #228B22, #32CD32);
        }
 
 
 
        /* Fixed "Back to Categories" button */
        .back-button {
            display: block;
            margin: 20px auto 0 auto;
            padding: 10px 20px;
            background: linear-gradient(90deg, #32CD32, #228B22);
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            width: 200px;
            height: 50px;
            position: absolute;
            bottom: 20px;
        }
 
        /* Hyperlink style for "Show More" */
        .card .show-more-link {
            color: #888;
            text-decoration: underline;
            cursor: pointer;
            font-size: 14px;
        }
 
        .card .show-more-link:hover {
            color: #fff;
        }
 
 
        .back-button:hover {
            background: linear-gradient(90deg, #228B22, #32CD32);
        }
 
        .hidden {
            display: none;
        }
 
 
        /* Header */
        header {
        width: 100%;
        background-color: rgba(0, 0, 0, 1);
        color: #fff;
        padding: 10px 20px;
        position: sticky;
        top: 0;
        z-index: 1000;
        display: flex;
        justify-content: space-between; /* Espace entre le logo et les boutons */
        align-items: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); /* Ombre subtile */
        }
 
        .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        }
 
        .logo {
        font-size: 1.8rem;
        font-weight: bold;
        letter-spacing: 2px;
        margin: 0;
        }
 
        .nav-buttons {
        display: flex;
        gap: 15px;
        }
 
        .nav-buttons button {
        background-color: transparent;
        border: 2px solid #fff;
        color: #fff;
        padding: 8px 15px;
        border-radius: 5px;
        font-size: 1rem;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
        }
 
        .nav-buttons button:hover {
        background-color: #fff;
        color: #000;
        }
 
        /* Titre principal */
        .title-container {
        text-align: center;
        margin-bottom: 30px;
        }
 
        .title-container h1 {
        font-size: 3rem;
        background: linear-gradient(135deg, #00ff00, #007f00);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-transform: uppercase;
        font-family: 'Poppins', sans-serif;
        }
 
        /* Boîte semi-transparente */
        .text-box {
        background-color: rgba(0, 0, 0, 0.7); /* Fond semi-transparent */
        border-radius: 10px;
        padding: 20px;
        max-width: 600px;
        margin: 0 auto; /* Centrage horizontal */
        color: white;
        font-family: 'Courier New', Courier, monospace; /* Style machine à écrire */
        white-space: pre-wrap; /* Respect des sauts de ligne */
        line-height: 1.5; /* Espacement entre les lignes */
        overflow: hidden;
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
        }
 
        /* Conteneur principal */
        .container {
        display: flex;
        width: 80%;
        max-width: 1200px;
        justify-content: space-between;
        align-items: flex-start;
        padding: 20px 0;
        margin: 0 auto; /* Centrage */
        }
 
 
 
        .category-btn {
        width: 80%;
        padding: 15px;
        background: linear-gradient(135deg, #006400, #00ff00);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1.2rem;
        font-weight: bold;
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s;
        }
 
        .category-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 5px 15px rgba(0, 255, 0, 0.4);
        }
 
        /* Section du texte */
        .text-section {
        width: 55%;
        font-size: 1.5rem;
        line-height: 2rem;
        text-align: justify;
        position: relative;
        }
 
        #animated-text {
        white-space: normal; /* Permet le retour à la ligne automatique */
        word-wrap: break-word; /* Gère les mots longs */
        overflow-wrap: break-word; /* Compatibilité supplémentaire */
        color: white;
        font-family: 'Courier New', Courier, monospace;
        font-size: 1rem;
        line-height: 1.5;
        }
 
        /* Animations */
        @keyframes typing {
        from { width: 0; }
        to { width: 100%; }
        }
 
        @keyframes blink {
        from, to { border-color: transparent; }
        50% { border-color: white; }
        }
 
        .text-box {
        background-color: rgba(0, 0, 0, 0.6); /* Boîte semi-transparente */
        border-radius: 10px;
        padding: 20px;
        max-width: 600px;
        margin: 0 auto;
        font-family: 'Courier New', Courier, monospace;
        white-space: pre-wrap;
        overflow: hidden;
        }
 
        .form-submit{
            display: block;
            margin: 20px auto 0 auto;
            padding: 10px 20px;
            background: linear-gradient(90deg, #32CD32, #228B22);
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            width: 100px;
            height: 50px;
            position: relative;
 
        }
 
        .form-submit:hover {
            background: linear-gradient(90deg, #228B22, #32CD32);
        }
 
    </style>
</head>
<body>
<header>
    <div class="navbar">
      <h1 class="logo">Challenge</h1>
      <div class="nav-buttons">
        <button onclick="window.location.href='challenges.html'">Catégories</button>
        <button onclick="window.location.href=''">Leaderboard</button>
        <button onclick="window.location.href='premièrePage.html'">Mon espace</button>
      </div>
    </div>
  </header>
  
 
    <h1>Category</h1>
 
    <!-- Overlay -->
    <div class="overlay" id="overlay" onclick="hidePopup()"></div>
 
    <!-- Form Section -->
    <div class="popup-form" id="popupForm">
        <form method="POST" enctype="multipart/form-data">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" placeholder="Challenge Name" required>
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
            <textarea id="description" name="description" rows="4" placeholder="Enter a description" required></textarea>
            <label for="file">File</label>
            <input type="file" id="file" name="file">
            
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
        <button class="back-button" onclick="showCategories()">Back to Categories</button>
    </div>
 
    <button class="button-icon" onclick="showPopup()"><i class="fas fa-plus"></i></button>
 
 
    <main>
    <div class="container">
 
  
      <div class="text-box">
          <p id="animated-text"></p>
      </div>
        
    </div>
  
    <script src="../JS/challenges.js"> </script>
 
 
 
 
 
  </main>
 
    <script>
        function showPopup() {
            document.getElementById('popupForm').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }
 
        function hidePopup() {
            document.getElementById('popupForm').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
 
        function showChallenges(category) {
        document.getElementById('categoryContainer').classList.add('hidden');
        document.getElementById('challengeContainer').classList.remove('hidden');
 
        const challengeCards = document.getElementById('challengeCards');
        challengeCards.innerHTML = '';
 
        const challenges = <?php
            $challenges_array = [];
            if ($result_challenges->num_rows > 0) {
                while ($row = $result_challenges->fetch_assoc()) {
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
 
                // Truncate long descriptions
                const description = challenge.description.length > 100
                    ? challenge.description.substring(0, 100) + '...'
                    : challenge.description;
 
                card.innerHTML = `
                    <h3>${challenge.name}</h3>
                    <p><b>Category:</b> <em>${challenge.category}</em></p>
                    <p><b>Level:</b> <em>${challenge.level}</em></p>
                    <p>${description}
                        ${
                            challenge.description.length > 100
                            ? `<span class="show-more-link" onclick="showFullDescription(this, '${challenge.description.replace(/'/g, "\\'")}')">Show More</span>`
                            : ''
                        }
                    </p>
                    ${
                        challenge.file_path
                        ? `<a href="uploads/${challenge.file_path}" class="file-link" target="_blank">View File</a>`
                        : ''
                    }
                    <button class="get-started">Get Started</button>
                `;
                challengeCards.appendChild(card);
            });
        } else {
            challengeCards.innerHTML = '<p>No challenges found.</p>';
        }
    }
 
    function showCategories() {
        document.getElementById('challengeContainer').classList.add('hidden');
        document.getElementById('categoryContainer').classList.remove('hidden');
    }
 
    function showFullDescription(link, fullDescription) {
        const descriptionElement = link.parentElement;
        descriptionElement.innerHTML = fullDescription;
    }
    </script>
</body>
</html>
 
<?php $conn->close(); ?>