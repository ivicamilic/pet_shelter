<?php
require_once 'includes/config.php'; // Load configuration // Učitaj konfiguraciju
require_once 'includes/auth.php';   // Load authentication // Učitaj autentifikaciju
require_once 'includes/functions.php'; // Load helper functions // Učitaj pomoćne funkcije

$lang = $_SESSION['lang'] ?? 'sr'; // Get language from session or default to English // Uzmi jezik iz sesije ili podesi na engleski
$L = require __DIR__ . '/lang/' . $lang . '.php'; // Load language file // Učitaj fajl sa prevodom

redirectIfNotLoggedIn(); // Redirect if user is not logged in // Preusmeri ako korisnik nije prijavljen

// Allow access for all logged in users // Dozvoli pristup za sve prijavljene korisnike

// Check if request is POST and required GET params exist // Proveri da li je zahtev POST i da postoje potrebni GET parametri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['pet_id'], $_GET['health_check_id'])) {
    $pet_id = (int)$_GET['pet_id']; // Get pet_id as integer // Uzmi pet_id kao ceo broj
    $health_check_id = (int)$_GET['health_check_id']; // Get health_check_id as integer // Uzmi health_check_id kao ceo broj

    // Check if user is volunteer role - prevent save // Proveri da li je korisnik "volunteer" uloge - spreči čuvanje
    if ($_SESSION['role'] === 'volunteer') {
        $_SESSION['error'] = $L['save_not_allowed'] ?? 'Save not allowed for this role'; // Set error message // Postavi poruku o grešci
        header("Location: view-pet.php?id=$pet_id"); // Redirect back // Preusmeri nazad
        exit();
    }

    // Get and sanitize input // Uzmi i očisti ulaz
    $check_date = $_POST['check_date'] ?? null;
    $veterinarian = trim($_POST['veterinarian'] ?? '');
    $health_status = $_POST['health_status'] ?? '';
    $diagnosis = trim($_POST['diagnosis'] ?? '');
    $treatment_plan = trim($_POST['treatment_plan'] ?? '');
    $clinical_exam = trim($_POST['clinical_exam'] ?? '');
    $animal_statement = trim($_POST['animal_statement'] ?? '');
    $health_notes = trim($_POST['health_notes'] ?? '');

    // Update health check using prepared statement // Ažuriraj zdravstveni pregled koristeći pripremljeni upit
    $db->query(
        "UPDATE health_checks SET 
            check_date = ?, 
            veterinarian = ?, 
            health_status = ?, 
            diagnosis = ?, 
            treatment_plan = ?, 
            clinical_exam = ?, 
            animal_statement = ?, 
            health_notes = ?
         WHERE id = ? AND pet_id = ?",
        [
            $check_date,
            $veterinarian,
            $health_status,
            $diagnosis,
            $treatment_plan,
            $clinical_exam,
            $animal_statement,
            $health_notes,
            $health_check_id,
            $pet_id
        ]
    );

    $_SESSION['message'] = $L['health_check_updated'] ??  "Health check updated successfully!"; // Set success message // Postavi poruku o uspehu
    header("Location: view-pet.php?id=$pet_id"); // Redirect to pet view // Preusmeri na prikaz ljubimca
    exit();
} else {
    header("Location: pets.php"); // Redirect if not valid request // Preusmeri ako zahtev nije validan
    exit();
}
