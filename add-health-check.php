<?php
require_once 'includes/config.php'; // Load configuration // Učitaj konfiguraciju
require_once 'includes/db.php';     // Load database connection // Učitaj konekciju sa bazom

$lang = $_SESSION['lang'] ?? 'en'; // Get language from session or default to English // Uzmi jezik iz sesije ili podesi na engleski
$L = [];
if (file_exists(__DIR__ . '/lang/' . $lang . '.php')) {
    $L = require __DIR__ . '/lang/' . $lang . '.php'; // Load language file // Učitaj fajl sa prevodom
}

// Check if user is logged in and not a Volunteer // Proveri da li je korisnik prijavljen i da nije volonter
if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'Volunteer') {
    header('Location: pets.php'); // Redirect to pets page // Preusmeri na stranicu sa ljubimcima
    exit();
}

// Check if pet_id is provided and is a valid integer // Proveri da li je prosleđen pet_id i da je validan broj
if (!isset($_GET['pet_id']) || !ctype_digit($_GET['pet_id'])) {
    header('Location: pets.php'); // Redirect if no pet_id // Preusmeri ako nema pet_id
    exit();
}

$pet_id = (int)$_GET['pet_id']; // Get pet_id as integer // Uzmi pet_id kao ceo broj

// Handle form submission // Obradi slanje forme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // No required field validation needed // Nema potrebe za validacijom obaveznih polja

    // Prepare and sanitize input // Pripremi i očisti ulaz
    $check_date      = $_POST['check_date'] ?: '';
    $health_status   = htmlspecialchars($_POST['health_status']);
    $clinical_exam   = htmlspecialchars($_POST['clinical_exam']);
    $animal_statement= htmlspecialchars($_POST['animal_statement']);
    $diagnosis       = htmlspecialchars($_POST['diagnosis']);
    $treatment_plan  = htmlspecialchars($_POST['treatment_plan']);
    $health_notes    = htmlspecialchars($_POST['health_notes']);
    $veterinarian    = htmlspecialchars($_POST['veterinarian']);

    // Use prepared statements for security // Koristi pripremljene upite radi sigurnosti
    $db->query(
        "INSERT INTO health_checks (pet_id, check_date, health_status, clinical_exam, animal_statement, diagnosis, treatment_plan, health_notes, veterinarian) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [
            $pet_id, 
            $check_date,
            $health_status,
            $clinical_exam,
            $animal_statement,
            $diagnosis,
            $treatment_plan,
            $health_notes,
            $veterinarian
        ]
    );
    $_SESSION['message'] = $L['health_check_added'] ?? 'Health check added successfully!';
    header("Location: view-pet.php?id=$pet_id"); // Redirect to pet view // Preusmeri na prikaz ljubimca
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($L['add_health_check'] ?? 'Add Health Check'); // Page title // Naslov stranice ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2><?php echo htmlspecialchars($L['add_health_check'] ?? 'Add Health Check'); // Heading // Naslov ?></h2>
    <form method="POST">
        <!-- Check Date // Datum pregleda -->
        <div class="mb-3">
            <label class="form-label"><?php echo htmlspecialchars($L['check_date'] ?? 'Check Date'); ?></label>
            <input type="date" class="form-control" name="check_date">
        </div>
        <!-- Veterinarian // Veterinar -->
        <div class="mb-3">
            <label class="form-label"><?php echo htmlspecialchars($L['veterinarian'] ?? 'Veterinarian'); ?></label>
            <input type="text" class="form-control" name="veterinarian" required>
        </div>
        <!-- Health Status // Zdravstveno stanje -->
        <div class="mb-3">
            <label class="form-label"><?php echo htmlspecialchars($L['health_status'] ?? 'Health Status'); ?></label>
            <select class="form-select" name="health_status" required>
                <option value=""><?php echo htmlspecialchars($L['select_health_status'] ?? 'Select Health Status'); ?></option>
                <option value="excellent"><?php echo htmlspecialchars($L['excellent'] ?? 'Excellent'); ?></option>
                <option value="good"><?php echo htmlspecialchars($L['good'] ?? 'Good'); ?></option>
                <option value="fair"><?php echo htmlspecialchars($L['fair'] ?? 'Fair'); ?></option>
                <option value="poor"><?php echo htmlspecialchars($L['poor'] ?? 'Poor'); ?></option>
                <option value="critical"><?php echo htmlspecialchars($L['critical'] ?? 'Critical'); ?></option>
            </select>
        </div>
        <!-- Diagnosis // Dijagnoza -->
        <div class="mb-3">
            <label class="form-label"><?php echo htmlspecialchars($L['diagnosis'] ?? 'Diagnosis'); ?></label>
            <textarea class="form-control" name="diagnosis" required></textarea>
        </div>
        <!-- Treatment Plan // Plan lečenja -->
        <div class="mb-3">
            <label class="form-label"><?php echo htmlspecialchars($L['treatment_plan'] ?? 'Treatment Plan'); ?></label>
            <textarea class="form-control" name="treatment_plan" required></textarea>
        </div>
        <!-- Clinical Exam // Klinički pregled -->
        <div class="mb-3">
            <label class="form-label"><?php echo htmlspecialchars($L['clinical_exam'] ?? 'Clinical Exam'); ?></label>
            <textarea class="form-control" name="clinical_exam" required></textarea>
        </div>
        <!-- Animal Statement // Izjava o životinji -->
        <div class="mb-3">
            <label class="form-label"><?php echo htmlspecialchars($L['animal_statement'] ?? 'Animal Statement'); ?></label>
            <textarea class="form-control" name="animal_statement" required></textarea>
        </div>
        <!-- Health Notes // Napomene -->
        <div class="mb-3">
            <label class="form-label"><?php echo htmlspecialchars($L['health_notes'] ?? 'Health Notes'); ?></label>
            <textarea class="form-control" name="health_notes" required></textarea>
        </div>
        <!-- Save and Cancel buttons // Dugmad za čuvanje i otkazivanje -->
        <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars($L['save'] ?? 'Save'); ?></button>
        <a href="view-pet.php?id=<?php echo $pet_id; ?>" class="btn btn-secondary"><?php echo htmlspecialchars($L['cancel'] ?? 'Cancel'); ?></a>
    </form>
</div>
</body>
</html>