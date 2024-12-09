<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "Méthode POST détectée. Les données reçues sont :";
    print_r($_POST);
} else {
    echo "Méthode non autorisée.";
}
?>
