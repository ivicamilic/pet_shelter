<?php
require_once 'includes/config.php'; // Load configuration // Učitaj konfiguraciju
require_once 'includes/auth.php';   // Load authentication // Učitaj autentifikaciju
require_once 'includes/db.php';     // Load database connection // Učitaj konekciju sa bazom

$lang = $_SESSION['lang'] ?? 'en'; // Get language from session or default to English // Uzmi jezik iz sesije ili podesi na engleski
$L = require __DIR__ . '/lang/' . $lang . '.php'; // Load language file // Učitaj fajl sa prevodom

redirectIfNotLoggedIn(); // Redirect if user is not logged in // Preusmeri ako korisnik nije prijavljen

// Only allow non-volunteers // Dozvoli samo korisnicima koji nisu volonteri
if ($_SESSION['role'] === 'Volunteer') {
    header('Location: pets.php'); // Redirect to pets // Preusmeri na ljubimce
    exit();
}

// Check if pet id is provided // Proveri da li je prosleđen id ljubimca
if (!isset($_GET['id'])) {
    header('Location: pets.php'); // Redirect if no id // Preusmeri ako nema id
    exit();
}

$pet_id = (int)$_GET['id']; // Get pet id as integer // Uzmi id ljubimca kao ceo broj

// Get pet details // Uzmi podatke o ljubimcu
$pet = $db->fetchOne("SELECT * FROM pets WHERE id = ?", [$pet_id]);
if (!$pet) {
    $_SESSION['error'] = $L['pet_not_found'] ?? 'Pet not found.'; // Set error message // Postavi poruku o grešci
    header('Location: pets.php'); // Redirect to pets // Preusmeri na ljubimce
    exit();
}

// Check permissions // Proveri dozvole
if ($_SESSION['role'] === 'staff' && $pet['created_by'] != $_SESSION['user_id']) {
    $_SESSION['error'] = $L['no_permission'] ?? 'You do not have permission to delete this pet.'; // Set error message // Postavi poruku o grešci
    header('Location: pets.php'); // Redirect to pets // Preusmeri na ljubimce
    exit();
}

// JavaScript confirmation should be handled on the calling page // Potvrda brisanja se radi na klijent strani

// Delete related records first // Prvo obriši povezane podatke
try {
    // Delete vaccinations // Obriši vakcinacije
    $db->query("DELETE FROM vaccinations WHERE pet_id = ?", [$pet_id]);
    
    // Delete health checks // Obriši zdravstvene preglede
    $db->query("DELETE FROM health_checks WHERE pet_id = ?", [$pet_id]);
    
    // Delete pet image if exists // Obriši sliku ljubimca ako postoji
    if (!empty($pet['image_path']) && file_exists($pet['image_path'])) {
        unlink($pet['image_path']); // Delete image file // Obriši fajl slike
    }
    
    // Delete the pet // Obriši ljubimca
    $db->query("DELETE FROM pets WHERE id = ?", [$pet_id]);
    
    $_SESSION['message'] = $L['pet_deleted'] ?? 'Pet deleted successfully.'; // Set success message // Postavi poruku o uspehu
} catch (Exception $e) {
    $_SESSION['error'] = $L['error_deleting_pet'] ?? 'Error deleting pet.'; // Set error message // Postavi poruku o grešci
}

header('Location: pets.php'); // Redirect to pets // Preusmeri na ljubimce
exit();
?>