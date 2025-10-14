<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

if (!isset($_GET['id'])) {
    echo "No ID provided";
    exit;
}

$pet = $db->fetchOne("SELECT * FROM pets WHERE id = ?", [$_GET['id']]);
if (!$pet) {
    echo "Pet not found";
    exit;
}

echo "<h1>Pet: " . $pet['name'] . "</h1>";
echo "<p>Breed: " . $pet['breed'] . "</p>";
echo "<p>Species: " . $pet['species'] . "</p>";
?>