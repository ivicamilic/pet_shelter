<?php
require_once 'includes/config.php'; // Load configuration // Učitaj konfiguraciju
require_once 'includes/db.php';     // Load database connection // Učitaj konekciju sa bazom

ini_set('display_errors', 1); // Show errors (for development) // Prikaži greške (za razvoj)
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$lang = $_SESSION['lang'] ?? 'en'; // Get language from session or default to English // Uzmi jezik iz sesije ili podesi na engleski
$L = [];
if (file_exists(__DIR__ . '/lang/' . $lang . '.php')) {
    $L = require __DIR__ . '/lang/' . $lang . '.php'; // Load language file // Učitaj fajl sa prevodom
}

// Check if user is logged in and not a Volunteer // Proveri da li je korisnik prijavljen i da nije volonter
if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'Volunteer') {
    header('Location: pets.php'); // Redirect to pets // Preusmeri na ljubimce
    exit();
}

// Check if pet_id is provided // Proveri da li je prosleđen pet_id
if (!isset($_GET['pet_id']) || !ctype_digit($_GET['pet_id'])) {
    header('Location: pets.php'); // Redirect if no pet_id // Preusmeri ako nema pet_id
    exit();
}

$pet_id = (int)$_GET['pet_id']; // Get pet_id as integer // Uzmi pet_id kao ceo broj

// Handle form submission // Obradi slanje forme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // No required field validation needed // Nema potrebe za validacijom obaveznih polja

    // Sanitize input // Očisti ulaz
    $vaccine_type = htmlspecialchars($_POST['vaccine_type']);
    $vaccine_name = htmlspecialchars($_POST['vaccine_name']);
    $vaccination_date = $_POST['vaccination_date'] ?: '';
    $batch_number = htmlspecialchars($_POST['batch_number']);
    $veterinarian = htmlspecialchars($_POST['veterinarian']);
    $expiry_date = $_POST['expiry_date'] ?: '';

    // Insert vaccination using prepared statement // Unesi vakcinaciju koristeći pripremljeni upit
    $db->query(
        "INSERT INTO vaccinations (pet_id, vaccine_type, vaccine_name, vaccination_date, batch_number, veterinarian, expiry_date) VALUES (?, ?, ?, ?, ?, ?, ?)",
        [$pet_id, $vaccine_type, $vaccine_name, $vaccination_date, $batch_number, $veterinarian, $expiry_date]
    );
    $_SESSION['message'] = $L['vaccination_added'] ?? 'Vaccination added successfully!';
    header("Location: view-pet.php?id=$pet_id"); // Redirect to pet view // Preusmeri na prikaz ljubimca
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($L['add_vaccination'] ?? 'Add Vaccination'); // Page title // Naslov stranice ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2><?php echo htmlspecialchars($L['add_vaccination'] ?? 'Add Vaccination'); // Heading // Naslov ?></h2>
    <form method="POST">
        <!-- Vaccine Type // Tip vakcine -->
        <div class="mb-3">
            <label class="form-label"><?php echo htmlspecialchars($L['vaccine_type'] ?? 'Vaccine Type'); ?></label>
            <select class="form-select" name="vaccine_type" required>
                <option value=""><?php echo htmlspecialchars($L['select_vaccine_type'] ?? 'Select Vaccine Type'); ?></option>
                <option value="rabies"><?php echo htmlspecialchars($L['rabies'] ?? 'Rabies'); ?></option>
                <option value="distemper"><?php echo htmlspecialchars($L['distemper'] ?? 'Distemper'); ?></option>
                <option value="parvovirus"><?php echo htmlspecialchars($L['parvovirus'] ?? 'Parvovirus'); ?></option>
                <option value="other"><?php echo htmlspecialchars($L['other'] ?? 'Other'); ?></option>
            </select>
        </div>
        <!-- Vaccine Name // Naziv vakcine -->
        <div class="mb-3">
            <label class="form-label"><?php echo htmlspecialchars($L['vaccine_name'] ?? 'Vaccine Name'); ?></label>
            <input type="text" class="form-control" name="vaccine_name" required>
        </div>
        <div class="row">
            <!-- Batch Number // Broj serije -->
            <div class="col-md-6 mb-3">
                <label class="form-label"><?php echo htmlspecialchars($L['batch_number'] ?? 'Batch Number'); ?></label>
                <input type="text" class="form-control" name="batch_number" required>
            </div>
            <!-- Veterinarian // Veterinar -->
            <div class="col-md-6 mb-3">
                <label class="form-label"><?php echo htmlspecialchars($L['veterinarian'] ?? 'Veterinarian'); ?></label>
                <input type="text" class="form-control" name="veterinarian" required>
            </div>
        </div>
        <div class="row">
            <!-- Vaccination Date // Datum vakcinacije -->
            <div class="col-md-6 mb-3">
                <label class="form-label"><?php echo htmlspecialchars($L['vaccination_date'] ?? 'Vaccination Date'); ?></label>
                <input type="date" class="form-control" name="vaccination_date" required>
            </div>
            <!-- Expiry Date // Datum isteka -->
            <div class="col-md-6 mb-3">
                <label class="form-label"><?php echo htmlspecialchars($L['expiry_date'] ?? 'Expiry Date'); ?></label>
                <input type="date" class="form-control" name="expiry_date">
            </div>
        </div>
        <!-- Save and Cancel buttons // Dugmad za čuvanje i otkazivanje -->
        <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars($L['save'] ?? 'Save'); ?></button>
        <a href="view-pet.php?id=<?php echo $pet_id; ?>" class="btn btn-secondary"><?php echo htmlspecialchars($L['cancel'] ?? 'Cancel'); ?></a>
    </form>
</div>
</body>
</html>