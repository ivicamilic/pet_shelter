<?php
// filepath: c:\xampp\htdocs\pet-shelter\edit-vaccination.php
require_once 'includes/config.php'; // Load configuration // Učitaj konfiguraciju
require_once 'includes/auth.php';   // Load authentication // Učitaj autentifikaciju
require_once 'includes/functions.php'; // Load helper functions // Učitaj pomoćne funkcije

$lang = $_SESSION['lang'] ?? 'en'; // Get language from session or default to English // Uzmi jezik iz sesije ili podesi na engleski
$L = require __DIR__ . '/lang/' . $lang . '.php'; // Load language file // Učitaj fajl sa prevodom

redirectIfNotLoggedIn(); // Redirect if user is not logged in // Preusmeri ako korisnik nije prijavljen

if ($_SESSION['role'] === 'Volunteer') {
    $_SESSION['error'] = 'Access denied'; // Set error message // Postavi poruku o grešci
    header('Location: pets.php'); // Redirect to pets // Preusmeri na ljubimce
    exit();
}

// Check if request is POST and required GET params exist // Proveri da li je zahtev POST i da postoje potrebni GET parametri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['pet_id'], $_GET['vaccination_id'])) {
    $pet_id = (int)$_GET['pet_id']; // Get pet_id as integer // Uzmi pet_id kao ceo broj
    $vaccination_id = (int)$_GET['vaccination_id']; // Get vaccination_id as integer // Uzmi vaccination_id kao ceo broj

    // Get and sanitize input // Uzmi i očisti ulaz
    $vaccine_type = $_POST['vaccine_type'] ?? '';
    $vaccine_name = trim($_POST['vaccine_name'] ?? '');
    $batch_number = trim($_POST['batch_number'] ?? '');
    $veterinarian = trim($_POST['veterinarian'] ?? '');
    $vaccination_date = $_POST['vaccination_date'] ?? null;
    $expiry_date = $_POST['expiry_date'] ?? null;
    $notes = trim($_POST['notes'] ?? '');

    // Update vaccination using prepared statement // Ažuriraj vakcinaciju koristeći pripremljeni upit
    $db->query(
        "UPDATE vaccinations SET 
            vaccine_type = ?, 
            vaccine_name = ?, 
            batch_number = ?, 
            veterinarian = ?, 
            vaccination_date = ?, 
            expiry_date = ?,
            notes = ?
         WHERE id = ? AND pet_id = ?",
        [
            $vaccine_type,
            $vaccine_name,
            $batch_number,
            $veterinarian,
            $vaccination_date,
            $expiry_date,
            $notes,
            $vaccination_id,
            $pet_id
        ]
    );

    $_SESSION['message'] = $L['vaccination_updated'] ??  "Vaccination updated successfully!"; // Set success message // Postavi poruku o uspehu
    header("Location: view-pet.php?id=$pet_id"); // Redirect to pet view // Preusmeri na prikaz ljubimca
    exit();
} else {
    header("Location: pets.php"); // Redirect if not valid request // Preusmeri ako zahtev nije validan
    exit();
}