<?php
require_once 'includes/config.php'; // Load configuration // Učitaj konfiguraciju
require_once 'includes/db.php';     // Load database connection // Učitaj konekciju sa bazom

// Handle language switching // Obrada promene jezika
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'sr'])) {
    $_SESSION['lang'] = $_GET['lang'];
    $queryParams = $_GET;
    unset($queryParams['lang']);
    $url = strtok($_SERVER['REQUEST_URI'], '?');
    $newQueryString = http_build_query($queryParams);
    $redirectUrl = $url . ($newQueryString ? '?' . $newQueryString : '');
    header("Location: " . $redirectUrl); // Redirect after language change // Preusmeri nakon promene jezika
    exit();
}

$lang = $_SESSION['lang'] ?? 'en'; // Get language from session or default to English // Uzmi jezik iz sesije ili podesi na engleski
$L = [];
if (file_exists(__DIR__ . '/lang/' . $lang . '.php')) {
    $L = require __DIR__ . '/lang/' . $lang . '.php'; // Load language file // Učitaj fajl sa prevodom
}

// Check if user is logged in // Proveri da li je korisnik prijavljen
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login // Preusmeri na prijavu
    exit();
}

// Only allow non-volunteers // Dozvoli samo korisnicima koji nisu volonteri
if ($_SESSION['role'] === 'Volunteer') {
    header('Location: pets.php'); // Redirect to pets // Preusmeri na ljubimce
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle image upload // Obrada slanja slike
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/pets/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true); // Create upload directory if not exists // Kreiraj direktorijum za slike ako ne postoji
        }
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION)); // Get file extension // Uzmi ekstenziju fajla
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp']; // Allowed extensions // Dozvoljene ekstenzije
        if (!in_array($file_ext, $allowed_ext)) {
            die('Invalid image format'); // Stop if format is not allowed // Zaustavi ako format nije dozvoljen
        }
        $file_name = uniqid() . '.' . $file_ext; // Generate unique file name // Generiši jedinstveno ime fajla
        $target_path = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_path = $target_path; // Save image path // Sačuvaj putanju slike
        }
    }

    // Sanitize and validate input // Očisti i validiraj ulaz
    $name = trim($_POST['name']);
    $species = trim($_POST['species']);
    $breed = trim($_POST['breed']);
    $sex = $_POST['sex'];
    $birth_date = $_POST['birth_date'] ?: null;
    $coat_color = trim($_POST['coat_color']);
    $coat_type = trim($_POST['coat_type']);
    $microchip_number = trim($_POST['microchip_number']);
    $microchip_date = $_POST['microchip_date'] ?: null;
    $microchip_location = trim($_POST['microchip_location']);
    $status = $_POST['status'];
    $in_shelter = (int)$_POST['in_shelter'];
    $incoming_date = $_POST['incoming_date'] ?: null;
    $capture_location = trim($_POST['capture_location']) ?: null;
    $cage_number = trim($_POST['cage_number']) ?: null;
    $return_location = trim($_POST['return_location']) ?: null;
    $return_date = $_POST['return_date'] ?: null;
    $created_by = $_SESSION['user_id'];

    // Insert pet data using prepared statement // Unesi podatke o ljubimcu koristeći pripremljeni upit
    $db->query(
        "INSERT INTO pets (name, species, breed, sex, birth_date, coat_color, coat_type, microchip_number, microchip_date, microchip_location, image_path, status, in_shelter, incoming_date, capture_location, cage_number, return_location, return_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [
            $name,
            $species,
            $breed,
            $sex,
            $birth_date,
            $coat_color,
            $coat_type,
            $microchip_number,
            $microchip_date,
            $microchip_location,
            $image_path,
            $status,
            $in_shelter,
            $incoming_date,
            $capture_location,
            $cage_number,
            $return_location,
            $return_date,
            $created_by
        ]
    );

    $pet_id = $db->getConnection()->insert_id; // Get inserted pet ID // Uzmi ID unetog ljubimca

    // Insert vaccination if provided // Unesi vakcinaciju ako je prosleđena
    if (!empty($_POST['vaccine_type']['new']) && !empty($_POST['vaccine_name']['new'])) {
        $db->query(
            "INSERT INTO vaccinations (pet_id, vaccine_type, vaccine_name, batch_number, vaccination_date, expiry_date, veterinarian, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $pet_id,
                $_POST['vaccine_type']['new'],
                $_POST['vaccine_name']['new'],
                $_POST['batch_number']['new'],
                $_POST['vaccination_date']['new'] ?: null,
                $_POST['expiry_date']['new'] ?: null,
                $_POST['veterinarian']['new'],
                $_POST['notes']['new']
            ]
        );
    }

    // Insert health check if provided // Unesi zdravstveni pregled ako je prosleđen
    if (!empty($_POST['check_date']['new'])) {
        $db->query(
            "INSERT INTO health_checks (pet_id, check_date, veterinarian, health_status, diagnosis, treatment_plan, clinical_exam, animal_statement, health_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $pet_id,
                $_POST['check_date']['new'] ?: null,
                $_POST['health_veterinarian']['new'],
                $_POST['health_status']['new'],
                $_POST['diagnosis']['new'],
                $_POST['treatment_plan']['new'],
                $_POST['clinical_exam']['new'],
                $_POST['animal_statement']['new'],
                $_POST['health_notes']['new']
            ]
        );
    }
    
    $_SESSION['message'] = $L['pet_added'] ?? 'Pet added successfully!'; // Set success message // Postavi poruku o uspehu
    header("Location: view-pet.php?id=$pet_id"); // Redirect to pet view // Preusmeri na prikaz ljubimca
    exit();
}
include 'includes/header.php'; // Include header // Uključi zaglavlje
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add Pet</title> <!-- Page title // Naslov stranice -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<!-- Navigation bar is commented out // Navigacija je zakomentarisana -->
<div class="container mt-4">
    <h2><?php echo $L['add_pet'] ?? 'Add Pet'; // Heading // Naslov ?></h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6">
                <!-- Pet basic info // Osnovne informacije o ljubimcu -->
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['name'] ?? 'Name'; ?></label>
                    <input type="text" class="form-control" name="name">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['species'] ?? 'Species'; ?></label>
                    <select class="form-select" name="species">
                        <option value=""><?php echo $L['select_species'] ?? 'Select species'; ?></option>
                        <option value="dog"><?php echo $L['dog'] ?? 'Dog'; ?></option>
                        <option value="cat"><?php echo $L['cat'] ?? 'Cat'; ?></option>
                        <option value="rabbit"><?php echo $L['rabbit'] ?? 'Rabbit'; ?></option>
                        <option value="bird"><?php echo $L['bird'] ?? 'Bird'; ?></option>
                        <option value="other"><?php echo $L['other'] ?? 'Other'; ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['breed'] ?? 'Breed'; ?></label>
                    <input type="text" class="form-control" name="breed">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['sex'] ?? 'Sex'; ?></label>
                    <select class="form-select" name="sex">
                        <option value="male"><?php echo $L['male'] ?? 'Male'; ?></option>
                        <option value="female"><?php echo $L['female'] ?? 'Female'; ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['date_of_birth'] ?? 'Date of Birth'; ?></label>
                    <input type="date" class="form-control" name="birth_date">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['coat_color'] ?? 'Coat Color'; ?></label>
                    <input type="text" class="form-control" name="coat_color">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['coat_type'] ?? 'Coat Type'; ?></label>
                    <input type="text" class="form-control" name="coat_type">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['microchip_number'] ?? 'Microchip Number'; ?></label>
                    <input type="text" class="form-control" name="microchip_number">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['microchip_date'] ?? 'Microchip Date'; ?></label>
                    <input type="date" class="form-control" name="microchip_date">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['microchip_location'] ?? 'Microchip Location'; ?></label>
                    <input type="text" class="form-control" name="microchip_location">
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label"><?php echo $L['status'] ?? 'Status'; ?></label>
                    <select class="form-select" id="status" name="status">
                        <option value="fostered"><?php echo $L['fostered'] ?? 'Fostered'; ?></option>
                        <option value="available"><?php echo $L['available_for_adoption'] ?? 'Available for adoption'; ?></option>
                        <option value="adopted"><?php echo $L['adopted'] ?? 'Adopted'; ?></option>
                        <option value="medical"><?php echo $L['under_medical_care'] ?? 'Under Medical care'; ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="capture_location" class="form-label"><?php echo $L['capture_location'] ?? 'Capture Location'; ?></label>
                    <input type="text" class="form-control" id="capture_location" name="capture_location">
                </div>
                <div class="mb-3">
                    <label for="cage_number" class="form-label"><?php echo $L['cage_number'] ?? 'Cage Number'; ?></label>
                    <input type="text" class="form-control" id="cage_number" name="cage_number">
                </div>
                <div class="mb-3">
                    <label for="return_location" class="form-label"><?php echo $L['return_location'] ?? 'Return Location'; ?></label>
                    <input type="text" class="form-control" id="return_location" name="return_location">
                </div>
                <div class="mb-3">
                    <label for="return_date" class="form-label"><?php echo $L['return_date'] ?? 'Return Date'; ?></label>
                    <input type="date" class="form-control" id="return_date" name="return_date">
                </div>

                <div class="mb-3">
                    <label class="form-label"><?php echo $L['presence_in_shelter'] ?? 'Presence In Shelter'; ?></label>
                    <select class="form-select" name="in_shelter">
                        <option value="1"><?php echo $L['yes'] ?? 'Yes'; ?></option>
                        <option value="0"><?php echo $L['no'] ?? 'No'; ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['incoming_date'] ?? 'Incoming Date'; ?></label>
                    <input type="date" class="form-control" name="incoming_date">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['image'] ?? 'Image'; ?></label>
                    <div class="input-group">
                        <input type="file" class="form-control d-none" id="image-upload" name="image" accept="image/*">
                        <button class="btn btn-outline-secondary" type="button" id="file-upload-button">
                            <?php echo $L['choose_file'] ?? 'Choose File'; ?>
                        </button>
                        <input type="text" class="form-control" id="file-name" readonly placeholder="<?php echo $L['no_file_chosen'] ?? 'No file chosen'; ?>">
                    </div>
                </div>
            </div>
        </div>
        
        <hr>
        <h4><?php echo $L['vaccinations'] ?? 'Vaccinations'; // Vaccinations section // Sekcija za vakcinacije ?></h4>
        <div class="border p-3 mb-3">
            <div class="mb-3">
                <label class="form-label"><?php echo $L['vaccine_type'] ?? 'Vaccine Type'; ?></label>
                <select class="form-select" name="vaccine_type[new]">
                    <option value=""><?php echo $L['choose_type'] ?? 'Choose type'; ?></option>
                    <option value="rabies"><?php echo $L['rabies'] ?? 'Rabies'; ?></option>
                    <option value="distemper"><?php echo $L['distemper'] ?? 'Distemper'; ?></option>
                    <option value="parvovirus"><?php echo $L['parvovirus'] ?? 'Parvovirus'; ?></option>
                    <option value="other"><?php echo $L['other'] ?? 'Other'; ?></option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo $L['vaccine_name'] ?? 'Vaccine Name'; ?></label>
                <input type="text" class="form-control" name="vaccine_name[new]">
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo $L['batch_number'] ?? 'Batch Number'; ?></label>
                <input type="text" class="form-control" name="batch_number[new]">
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo $L['veterinarian'] ?? 'Veterinarian'; ?></label>
                <input type="text" class="form-control" name="veterinarian[new]">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?php echo $L['vaccination_date'] ?? 'Vaccination Date'; ?></label>
                    <input type="date" class="form-control" name="vaccination_date[new]">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?php echo $L['expiry_date'] ?? 'Expiry Date'; ?></label>
                    <input type="date" class="form-control" name="expiry_date[new]">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo $L['notes'] ?? 'Notes'; ?></label>
                <textarea class="form-control" name="notes[new]" rows="2"></textarea>
            </div>
        </div>
        
        <hr>
        <h4><?php echo $L['health_checks'] ?? 'Health Checks'; // Health checks section // Sekcija za zdravstvene preglede ?></h4>
        <div class="border p-3 mb-3">
            <div class="mb-3">
                <label class="form-label"><?php echo $L['check_date'] ?? 'Check Date'; ?></label>
                <input type="date" class="form-control" name="check_date[new]">
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo $L['veterinarian'] ?? 'Veterinarian'; ?></label>
                <input type="text" class="form-control" name="health_veterinarian[new]">
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo $L['health_status'] ?? 'Health Status'; ?></label>
                <select class="form-select" name="health_status[new]">
                    <option value=""><?php echo $L['select_status'] ?? 'Select status'; ?></option>
                    <option value="excellent"><?php echo $L['excellent'] ?? 'Excellent'; ?></option>
                    <option value="good"><?php echo $L['good'] ?? 'Good'; ?></option>
                    <option value="fair"><?php echo $L['fair'] ?? 'Fair'; ?></option>
                    <option value="poor"><?php echo $L['poor'] ?? 'Poor'; ?></option>
                    <option value="critical"><?php echo $L['critical'] ?? 'Critical'; ?></option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo $L['diagnosis'] ?? 'Diagnosis'; ?></label>
                <textarea class="form-control" name="diagnosis[new]"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo $L['treatment_plan'] ?? 'Treatment Plan'; ?></label>
                <textarea class="form-control" name="treatment_plan[new]"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo $L['clinical_exam'] ?? 'Clinical Exam'; ?></label>
                <textarea class="form-control" name="clinical_exam[new]"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo $L['animal_statement'] ?? 'Animal Statement'; ?></label>
                <textarea class="form-control" name="animal_statement[new]"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo $L['health_notes'] ?? 'Additional Notes'; ?></label>
                <textarea class="form-control" name="health_notes[new]"></textarea>
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><?php echo $L['save'] ?? 'Save'; // Save button // Dugme za čuvanje ?></button>
        <a href="pets.php" class="btn btn-secondary"><?php echo $L['cancel'] ?? 'Cancel'; // Cancel button // Dugme za otkazivanje ?></a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// File upload button logic // Logika za dugme za slanje fajla
document.getElementById('file-upload-button').addEventListener('click', function() {
    document.getElementById('image-upload').click();
});
document.getElementById('image-upload').addEventListener('change', function() {
    const fileName = this.files[0] ? this.files[0].name : '';
    document.getElementById('file-name').value = fileName;
});
</script>
</body>
</html>