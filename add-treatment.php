<?php
require_once 'includes/config.php'; // Load configuration // Učitaj konfiguraciju
require_once 'includes/auth.php';   // Load authentication // Učitaj autentifikaciju
require_once 'includes/db.php';     // Load database connection // Učitaj konekciju sa bazom
require_once 'includes/functions.php'; // Load helper functions // Učitaj pomoćne funkcije

redirectIfNotLoggedIn(); // Redirect if user is not logged in // Preusmeri ako korisnik nije prijavljen

// Check request method and pet_id // Proveri metod zahteva i pet_id
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['pet_id'])) {
    $_SESSION['error'] = 'Invalid request'; // Set error message // Postavi poruku o grešci
    header('Location: pets.php'); // Redirect to pets // Preusmeri na ljubimce
    exit();
}

// Get and sanitize input // Uzmi i očisti ulaz
$pet_id = (int)$_POST['pet_id'];
$treatment_type = trim($_POST['treatment_type']);
$product_name = trim($_POST['product_name']);
$treatment_date = $_POST['treatment_date'];
$next_treatment_date = $_POST['next_treatment_date'] ?? null;
$veterinarian = isset($_POST['veterinarian']) ? trim($_POST['veterinarian']) : null;
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;

// Validate required fields // Validiraj obavezna polja
if (empty($treatment_type) || empty($product_name) || empty($treatment_date)) {
    $_SESSION['error'] = 'Treatment type, product name and date are required'; // Set error message // Postavi poruku o grešci
    header("Location: view-pet.php?id=$pet_id"); // Redirect to pet view // Preusmeri na prikaz ljubimca
    exit();
}

// Check if pet exists and user has permission // Proveri da li ljubimac postoji i da li korisnik ima prava
$pet = $db->fetchOne("SELECT * FROM pets WHERE id = ?", [$pet_id]);
if (!$pet || ($pet['created_by'] != $_SESSION['user_id'] && !isAdmin())) {
    $_SESSION['error'] = 'Pet not found or you don\'t have permission'; // Set error message // Postavi poruku o grešci
    header('Location: pets.php'); // Redirect to pets // Preusmeri na ljubimce
    exit();
}

// Insert treatment record using prepared statement // Unesi tretman koristeći pripremljeni upit
$db->query(
    "INSERT INTO treatments (pet_id, treatment_type, product_name, treatment_date, next_treatment_date, veterinarian, notes) 
    VALUES (?, ?, ?, ?, ?, ?, ?)",
    [$pet_id, $treatment_type, $product_name, $treatment_date, $next_treatment_date, $veterinarian, $notes]
);

$_SESSION['message'] = 'Treatment record added successfully'; // Set success message // Postavi poruku o uspehu
header("Location: view-pet.php?id=$pet_id"); // Redirect to pet view // Preusmeri na prikaz ljubimca
exit();
?>