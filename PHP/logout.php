<?php

require_once 'security.php';

session_start();

// Vérifie si une session existe
if (isset($_SESSION['user_id'])) {
    session_unset();  // Supprime les variables de session
    session_destroy();  // Détruit la session
    http_response_code(200);  // Succès
} else {
    http_response_code(401);  // Non autorisé
}
?>