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

$lang = $_SESSION['lang'] ?? 'sr'; // Get language from session or default to English // Uzmi jezik iz sesije ili podesi na engleski
$L = [];
if (file_exists(__DIR__ . '/lang/' . $lang . '.php')) {
    $L = require __DIR__ . '/lang/' . $lang . '.php'; // Load language file // Učitaj fajl sa prevodom
}

// Check if user is logged in // Proveri da li je korisnik prijavljen
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login // Preusmeri na prijavu
    exit();
}

// Allow all logged in users // Dozvoli sve prijavljene korisnike

// Check if pet id is provided // Proveri da li je prosleđen id ljubimca
if (!isset($_GET['id'])) {
    header('Location: pets.php'); // Redirect if no id // Preusmeri ako nema id
    exit();
}

$pet_id = (int)$_GET['id']; // Get pet id as integer // Uzmi id ljubimca kao ceo broj
$pet = $db->fetchOne("SELECT * FROM pets WHERE id = ?", [$pet_id]); // Fetch pet data // Uzmi podatke o ljubimcu

// Check permissions // Proveri dozvole
if (!$pet || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff' && $_SESSION['role'] !== 'volunteer' && $pet['created_by'] != $_SESSION['user_id'])) {
    header('Location: pets.php'); // Redirect if no permission // Preusmeri ako nema dozvolu
    exit();
}

try {
    $vaccinations = $db->fetchAll("SELECT * FROM vaccinations WHERE pet_id = ?", [$pet['id']]); // Fetch vaccinations // Uzmi vakcinacije
} catch (Exception $e) {
    $vaccinations = [];
}

try {
    $health_checks = $db->fetchAll("SELECT * FROM health_checks WHERE pet_id = ? ORDER BY check_date DESC, id DESC", [$pet['id']]); // Fetch health checks // Uzmi zdravstvene preglede
} catch (Exception $e) {
    $health_checks = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is volunteer role - prevent save // Proveri da li je korisnik "volunteer" uloge - spreči čuvanje
    if ($_SESSION['role'] === 'volunteer') {
        $_SESSION['error'] = $L['save_not_allowed'] ?? 'Save not allowed for this role'; // Set error message // Postavi poruku o grešci
        header("Location: view-pet.php?id=$pet_id"); // Redirect back // Preusmeri nazad
        exit();
    }

    // Debug logs (remove in production) // Debug logovi (ukloniti u produkciji)
    try {
        $columns = $db->fetchAll("SHOW COLUMNS FROM pets LIKE 'return_%'");
        error_log('Return columns: ' . print_r($columns, true));
    } catch (Exception $e) {
        error_log('Error checking columns: ' . $e->getMessage());
    }
    error_log('POST data: ' . print_r($_POST, true));

    // Handle image upload with resizing // Obrada slanja slike sa promenom veličine
    $image_path = $pet['image_path'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/pets/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($file_ext, $allowed_ext)) {
            die('Invalid image format');
        }
        
        // Resize image to max 800px // Promeni veličinu slike na max 800px
        $source = $_FILES['image']['tmp_name'];
        $imageInfo = getimagesize($source);
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        $maxSize = 800;
        if ($width > $maxSize || $height > $maxSize) {
            if ($width > $height) {
                $newWidth = $maxSize;
                $newHeight = intval($height * $maxSize / $width);
            } else {
                $newHeight = $maxSize;
                $newWidth = intval($width * $maxSize / $height);
            }
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }
        
        switch ($imageInfo['mime']) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($source);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($source);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($source);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($source);
                break;
            default:
                die('Unsupported image type');
        }
        
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        
        if ($imageInfo['mime'] == 'image/png' || $imageInfo['mime'] == 'image/gif') {
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
            $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
            imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        $file_name = uniqid() . '.jpg';
        $target_path = $upload_dir . $file_name;
        
        if (imagejpeg($resizedImage, $target_path, 85)) {
            if (!empty($pet['image_path']) && file_exists($pet['image_path'])) {
                unlink($pet['image_path']); // Delete old image // Obriši staru sliku
            }
            $image_path = $target_path;
        }
        
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);
    }

    // Update pet data using prepared statement // Ažuriraj podatke o ljubimcu koristeći pripremljeni upit
    $db->query(
        "UPDATE pets SET name=?, species=?, breed=?, sex=?, birth_date=?, coat_color=?, coat_type=?, microchip_number=?, microchip_date=?, microchip_location=?, image_path=?, status=?, in_shelter=?, incoming_date=?, capture_location=?, cage_number=?, return_location=?, return_date=? WHERE id=?",
        [
            trim($_POST['name']),
            trim($_POST['species']),
            trim($_POST['breed']),
            $_POST['sex'],
            $_POST['birth_date'] ?: null,
            trim($_POST['coat_color']),
            trim($_POST['coat_type']),
            trim($_POST['microchip_number']),
            $_POST['microchip_date'] ?: null,
            trim($_POST['microchip_location']),
            $image_path,
            $_POST['status'],
            (int)$_POST['in_shelter'],
            $_POST['incoming_date'] ?: null,
            trim($_POST['capture_location']) ?: null,
            trim($_POST['cage_number']) ?: null,
            trim($_POST['return_location']) ?: null,
            $_POST['return_date'] ?: null,
            $pet_id
        ]
    );

    // Update or insert vaccinations // Ažuriraj ili unesi vakcinacije
    if (!empty($_POST['vaccine_type'])) {
        foreach ($_POST['vaccine_type'] as $id => $type) {
            if ($id === 'new' && !empty($_POST['vaccine_name'][$id])) {
                // Insert new vaccination // Unesi novu vakcinaciju
                $db->query(
                    "INSERT INTO vaccinations (pet_id, vaccine_type, vaccine_name, batch_number, vaccination_date, expiry_date, veterinarian, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $pet_id,
                        $type,
                        $_POST['vaccine_name'][$id],
                        $_POST['batch_number'][$id],
                        $_POST['vaccination_date'][$id] ?: null,
                        $_POST['expiry_date'][$id] ?: null,
                        $_POST['veterinarian'][$id],
                        $_POST['notes'][$id]
                    ]
                );
            } elseif ($id !== 'new') {
                // Update existing vaccination // Ažuriraj postojeću vakcinaciju
                $db->query(
                    "UPDATE vaccinations SET vaccine_type=?, vaccine_name=?, batch_number=?, vaccination_date=?, expiry_date=?, veterinarian=?, notes=? WHERE id=?",
                    [
                        $type,
                        $_POST['vaccine_name'][$id],
                        $_POST['batch_number'][$id],
                        $_POST['vaccination_date'][$id] ?: null,
                        $_POST['expiry_date'][$id] ?: null,
                        $_POST['veterinarian'][$id],
                        $_POST['notes'][$id],
                        $id
                    ]
                );
            }
        }
    }

    // Update or insert health checks // Ažuriraj ili unesi zdravstvene preglede
    if (!empty($_POST['check_date'])) {
        foreach ($_POST['check_date'] as $id => $check_date) {
            if ($id === 'new' && !empty($_POST['check_date'][$id])) {
                // Insert new health check // Unesi novi zdravstveni pregled
                $db->query(
                    "INSERT INTO health_checks (pet_id, check_date, veterinarian, health_status, diagnosis, treatment_plan, clinical_exam, animal_statement, health_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $pet_id,
                        $check_date ?: null,
                        $_POST['health_veterinarian'][$id],
                        $_POST['health_status'][$id],
                        $_POST['diagnosis'][$id],
                        $_POST['treatment_plan'][$id],
                        $_POST['clinical_exam'][$id],
                        $_POST['animal_statement'][$id],
                        $_POST['health_notes'][$id]
                    ]
                );
            } elseif ($id !== 'new') {
                // Update existing health check // Ažuriraj postojeći zdravstveni pregled
                $db->query(
                    "UPDATE health_checks SET
                        check_date=?,
                        veterinarian=?,
                        health_status=?,
                        diagnosis=?,
                        treatment_plan=?,
                        clinical_exam=?,
                        animal_statement=?,
                        health_notes=?
                     WHERE id=?",
                    [
                        $check_date ?: null,
                        $_POST['health_veterinarian'][$id],
                        $_POST['health_status'][$id],
                        $_POST['diagnosis'][$id],
                        $_POST['treatment_plan'][$id],
                        $_POST['clinical_exam'][$id],
                        $_POST['animal_statement'][$id],
                        $_POST['health_notes'][$id],
                        $id
                    ]
                );
            }
        }
    }
    
    $_SESSION['message'] = $L['pet_updated'] ?? 'Pet and related data updated successfully!'; // Set success message // Postavi poruku o uspehu
    header("Location: view-pet.php?id=$pet_id"); // Redirect to pet view // Preusmeri na prikaz ljubimca
    exit();
}
include 'includes/header.php'; // Include header // Uključi zaglavlje
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Pet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<!-- <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="pets.php"><?php echo $L['pet_shelter'] ?? 'Pet Shelter'; ?></a>
        <div class="navbar-nav me-auto">
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a class="nav-link" href="dashboard.php"><?php echo $L['dashboard'] ?? 'Dashboard'; ?></a>
            <?php endif; ?>
            <a class="nav-link" href="pets.php"><?php echo $L['pets'] ?? 'Pets'; ?></a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a class="nav-link" href="users.php"><?php echo $L['users'] ?? 'Users'; ?></a>
            <?php endif; ?>
        </div>
        <div class="navbar-nav align-items-center">
            <a href="?<?php echo http_build_query(array_merge($_GET, ['lang' => 'en'])); ?>" class="nav-link p-0 me-2 <?php echo $lang === 'en' ? 'border border-primary rounded' : ''; ?>" title="English">
                <img src="https://cdn.jsdelivr.net/gh/hjnilsson/country-flags/svg/gb.svg" alt="English" width="28" height="20">
            </a>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['lang' => 'sr'])); ?>" class="nav-link p-0 me-3 <?php echo $lang === 'sr' ? 'border border-primary rounded' : ''; ?>" title="Srpski">
                <img src="https://cdn.jsdelivr.net/gh/hjnilsson/country-flags/svg/rs.svg" alt="Srpski" width="28" height="20">
            </a>
            <span class="navbar-text me-3"><?php echo $_SESSION['username'] ?? ''; ?></span>
            <a class="nav-link" href="logout.php"><?php echo $L['logout'] ?? 'Logout'; ?></a>
        </div>
    </div>
</nav> -->
<div class="container mt-4">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['error']); // Display error safely // Prikaži grešku bezbedno ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <h2><?php echo $L['edit'] ?? 'Edit'; ?>: <?php echo $pet['name']; ?></h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6">
                <!-- Pet basic info -->
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['name'] ?? 'Name'; ?></label>
                    <input type="text" class="form-control" name="name" value="<?php echo $pet['name']; ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['species'] ?? 'Species'; ?></label>
                    <select class="form-select" name="species">
                        <option value="dog" <?php if($pet['species']=='dog') echo 'selected'; ?>><?php echo $L['dog'] ?? 'Dog'; ?></option>
                        <option value="cat" <?php if($pet['species']=='cat') echo 'selected'; ?>><?php echo $L['cat'] ?? 'Cat'; ?></option>
                        <option value="rabbit" <?php if($pet['species']=='rabbit') echo 'selected'; ?>><?php echo $L['rabbit'] ?? 'Rabbit'; ?></option>
                        <option value="bird" <?php if($pet['species']=='bird') echo 'selected'; ?>><?php echo $L['bird'] ?? 'Bird'; ?></option>
                        <option value="other" <?php if($pet['species']=='other') echo 'selected'; ?>><?php echo $L['other'] ?? 'Other'; ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['breed'] ?? 'Breed'; ?></label>
                    <input type="text" class="form-control" name="breed" value="<?php echo $pet['breed']; ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['sex'] ?? 'Sex'; ?></label>
                    <select class="form-select" name="sex">
                        <option value="male" <?php if($pet['sex']=='male') echo 'selected'; ?>><?php echo $L['male'] ?? 'Male'; ?></option>
                        <option value="female" <?php if($pet['sex']=='female') echo 'selected'; ?>><?php echo $L['female'] ?? 'Female'; ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['date_of_birth'] ?? 'Date of Birth'; ?></label>
                    <input type="date" class="form-control" name="birth_date" value="<?php echo $pet['birth_date']; ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['coat_color'] ?? 'Coat Color'; ?></label>
                    <input type="text" class="form-control" name="coat_color" value="<?php echo $pet['coat_color']; ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['coat_type'] ?? 'Coat Type'; ?></label>
                    <input type="text" class="form-control" name="coat_type" value="<?php echo $pet['coat_type']; ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['microchip_number'] ?? 'Microchip Number'; ?></label>
                    <input type="text" class="form-control" name="microchip_number" value="<?php echo $pet['microchip_number']; ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['microchip_date'] ?? 'Microchip Date'; ?></label>
                    <input type="date" class="form-control" name="microchip_date" value="<?php echo $pet['microchip_date']; ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['microchip_location'] ?? 'Microchip Location'; ?></label>
                    <input type="text" class="form-control" name="microchip_location" value="<?php echo $pet['microchip_location']; ?>">
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
                    <input type="text" class="form-control" id="capture_location" name="capture_location" value="<?php echo $pet['capture_location']; ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['incoming_date'] ?? 'Incoming Date'; ?></label>
                    <input type="date" class="form-control" name="incoming_date" value="<?php echo $pet['incoming_date']; ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['presence_in_shelter'] ?? 'Presence In Shelter'; ?></label>
                    <select class="form-select" name="in_shelter">
                        <option value="1" <?php if($pet['in_shelter']) echo 'selected'; ?>><?php echo $L['yes'] ?? 'Yes'; ?></option>
                        <option value="0" <?php if(!$pet['in_shelter']) echo 'selected'; ?>><?php echo $L['no'] ?? 'No'; ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="cage_number" class="form-label"><?php echo $L['cage_number'] ?? 'Cage Number'; ?></label>
                    <input type="text" class="form-control" id="cage_number" name="cage_number" value="<?php echo htmlspecialchars($pet['cage_number'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="return_location" class="form-label"><?php echo $L['return_location'] ?? 'Return Location'; ?></label>
                    <input type="text" class="form-control" id="return_location" name="return_location" value="<?php echo htmlspecialchars($pet['return_location']?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="return_date" class="form-label"><?php echo $L['return_date'] ?? 'Return Date'; ?></label>
                    <input type="date" class="form-control" id="return_date" name="return_date" value="<?php echo $pet['return_date']; ?>">

                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['current_image'] ?? 'Current Image'; ?></label>
                    <div>
                        <?php if (!empty($pet['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($pet['image_path']); ?>" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                        <?php else: ?>
                            <p><?php echo $L['no_image_uploaded'] ?? 'No image uploaded.'; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?php echo $L['change_image'] ?? 'Change Image'; ?></label>
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
        <h4><?php echo $L['vaccinations'] ?? 'Vaccinations'; ?></h4>
        <?php
        // Prikaži samo jednu vakcinaciju - ili postojeću ili praznu
        $vaccination = !empty($vaccinations) ? $vaccinations[0] : null;
        $id = $vaccination ? $vaccination['id'] : 'new';
        ?>
        <div class="border p-3 mb-3">
            <div class="mb-3">
                <label class="form-label"><?php echo $L['vaccine_type'] ?? 'Vaccine Type'; ?></label>
                <select class="form-select" name="vaccine_type[<?php echo $id; ?>]">
                    <option value=""><?php echo $L['choose_type'] ?? 'Choose type'; ?></option>
                    <option value="rabies" <?php if($vaccination && $vaccination['vaccine_type']=='rabies') echo 'selected'; ?>><?php echo $L['rabies'] ?? 'Rabies'; ?></option>
                    <option value="distemper" <?php if($vaccination && $vaccination['vaccine_type']=='distemper') echo 'selected'; ?>><?php echo $L['distemper'] ?? 'Distemper'; ?></option>
                    <option value="parvovirus" <?php if($vaccination && $vaccination['vaccine_type']=='parvovirus') echo 'selected'; ?>><?php echo $L['parvovirus'] ?? 'Parvovirus'; ?></option>
                    <option value="other" <?php if($vaccination && $vaccination['vaccine_type']=='other') echo 'selected'; ?>><?php echo $L['other'] ?? 'Other'; ?></option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo $L['vaccine_name'] ?? 'Vaccine Name'; ?></label>
                <input type="text" class="form-control" name="vaccine_name[<?php echo $id; ?>]" value="<?php echo $vaccination ? htmlspecialchars($vaccination['vaccine_name']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo $L['batch_number'] ?? 'Batch Number'; ?></label>
                <input type="text" class="form-control" name="batch_number[<?php echo $id; ?>]" value="<?php echo $vaccination ? htmlspecialchars($vaccination['batch_number']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo $L['veterinarian'] ?? 'Veterinarian'; ?></label>
                <input type="text" class="form-control" name="veterinarian[<?php echo $id; ?>]" value="<?php echo $vaccination ? htmlspecialchars($vaccination['veterinarian']) : ''; ?>">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?php echo $L['vaccination_date'] ?? 'Vaccination Date'; ?></label>
                    <input type="date" class="form-control" name="vaccination_date[<?php echo $id; ?>]" value="<?php echo $vaccination ? $vaccination['vaccination_date'] : ''; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?php echo $L['expiry_date'] ?? 'Expiry Date'; ?></label>
                    <input type="date" class="form-control" name="expiry_date[<?php echo $id; ?>]" value="<?php echo $vaccination ? $vaccination['expiry_date'] : ''; ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo $L['notes'] ?? 'Notes'; ?></label>
                <textarea class="form-control" name="notes[<?php echo $id; ?>]" rows="2"><?php echo $vaccination ? htmlspecialchars($vaccination['notes'] ?? '') : ''; ?></textarea>
            </div>
        </div>
        
        <hr>
        <h4><?php echo $L['health_checks'] ?? 'Health Checks'; ?></h4>
        <?php
        // Prikaži samo jedan zdravstveni pregled - ili postojeći ili prazan
        $health_check = !empty($health_checks) ? $health_checks[0] : null;
        $hc_id = $health_check ? $health_check['id'] : 'new';
        ?>
        <div class="border p-3 mb-3">
            <div class="mb-3">
                <label class="form-label"><?php echo $L['check_date'] ?? 'Check Date'; ?></label>
                <input type="date" class="form-control" name="check_date[<?php echo $hc_id; ?>]" value="<?php echo $health_check ? $health_check['check_date'] : ''; ?>">
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo $L['veterinarian'] ?? 'Veterinarian'; ?></label>
                <input type="text" class="form-control" name="health_veterinarian[<?php echo $hc_id; ?>]" value="<?php echo $health_check ? htmlspecialchars($health_check['veterinarian']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo $L['health_status'] ?? 'Health Status'; ?></label>
                <select class="form-select" name="health_status[<?php echo $hc_id; ?>]">
                    <option value=""><?php echo $L['select_status'] ?? 'Select status'; ?></option>
                    <option value="excellent" <?php if($health_check && $health_check['health_status']=='excellent') echo 'selected'; ?>><?php echo $L['excellent'] ?? 'Excellent'; ?></option>
                    <option value="good" <?php if($health_check && $health_check['health_status']=='good') echo 'selected'; ?>><?php echo $L['good'] ?? 'Good'; ?></option>
                    <option value="fair" <?php if($health_check && $health_check['health_status']=='fair') echo 'selected'; ?>><?php echo $L['fair'] ?? 'Fair'; ?></option>
                    <option value="poor" <?php if($health_check && $health_check['health_status']=='poor') echo 'selected'; ?>><?php echo $L['poor'] ?? 'Poor'; ?></option>
                    <option value="critical" <?php if($health_check && $health_check['health_status']=='critical') echo 'selected'; ?>><?php echo $L['critical'] ?? 'Critical'; ?></option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo $L['diagnosis'] ?? 'Diagnosis'; ?></label>
                <textarea class="form-control" name="diagnosis[<?php echo $hc_id; ?>]"><?php echo $health_check ? htmlspecialchars($health_check['diagnosis']) : ''; ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo $L['treatment_plan'] ?? 'Treatment Plan'; ?></label>
                <textarea class="form-control" name="treatment_plan[<?php echo $hc_id; ?>]"><?php echo $health_check ? htmlspecialchars($health_check['treatment_plan']) : ''; ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo $L['clinical_exam'] ?? 'Clinical Exam'; ?></label>
                <textarea class="form-control" name="clinical_exam[<?php echo $hc_id; ?>]"><?php echo $health_check ? htmlspecialchars($health_check['clinical_exam']) : ''; ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo $L['animal_statement'] ?? 'Animal Statement'; ?></label>
                <textarea class="form-control" name="animal_statement[<?php echo $hc_id; ?>]"><?php echo $health_check ? htmlspecialchars($health_check['animal_statement']) : ''; ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label"><?php echo $L['health_notes'] ?? 'Additional Notes'; ?></label>
                <textarea class="form-control" name="health_notes[<?php echo $hc_id; ?>]"><?php echo $health_check ? htmlspecialchars($health_check['health_notes']) : ''; ?></textarea>
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><?php echo $L['save'] ?? 'Save'; ?></button>
        <a href="view-pet.php?id=<?php echo $pet_id; ?>" class="btn btn-secondary"><?php echo $L['cancel'] ?? 'Cancel'; ?></a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('file-upload-button').addEventListener('click', function() {
    document.getElementById('image-upload').click();
});
document.getElementById('image-upload').addEventListener('change', function() {
    const fileName = this.files[0] ? this.files[0].name : '';
    document.getElementById('file-name').value = fileName;
        
    // Show image preview
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            let currentImage = document.querySelector('.img-thumbnail');
            if (currentImage) {
                currentImage.src = e.target.result;
            } else {
                // Find the div that contains "No image uploaded" text and replace it
                const labels = document.querySelectorAll('label');
                for (let label of labels) {
                    if (label.textContent.includes('Current Image') || label.textContent.includes('Trenutna slika')) {
                        const container = label.parentElement.querySelector('div');
                        if (container) {
                            container.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">';
                            break;
                        }
                    }
                }
            }
        };
        reader.readAsDataURL(this.files[0]);
    }

});


</script>
</body>
</html>
