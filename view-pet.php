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

$lang = $_SESSION['lang'] ?? 'sr'; // Get language from session or default to Serbian // Uzmi jezik iz sesije ili podesi na srpski
$L = [];
if (file_exists(__DIR__ . '/lang/' . $lang . '.php')) {
    $L = require __DIR__ . '/lang/' . $lang . '.php'; // Load language file // Učitaj fajl sa prevodom
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect if not logged in // Preusmeri ako nije prijavljen
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: pets.php'); // Redirect if no pet id // Preusmeri ako nema id ljubimca
    exit();
}

$pet = $db->fetchOne("SELECT * FROM pets WHERE id = ?", [$_GET['id']]); // Fetch pet data // Uzmi podatke o ljubimcu
if (!$pet) {
    header('Location: pets.php'); // Redirect if pet not found // Preusmeri ako ljubimac nije pronađen
    exit();
}

// Try to get vaccinations and health checks, but don't fail if tables don't exist // Pokušaj da uzmeš vakcinacije i preglede, ali ne prekidaj ako tabele ne postoje
try {
    $vaccinations = $db->fetchAll("SELECT * FROM vaccinations WHERE pet_id = ?", [$pet['id']]);
} catch (Exception $e) {
    $vaccinations = [];
}

try {
    $health_checks = $db->fetchAll("SELECT * FROM health_checks WHERE pet_id = ? ORDER BY check_date DESC, id DESC", [$pet['id']]);
} catch (Exception $e) {
    $health_checks = [];
}
include 'includes/header.php'; // Include header // Uključi zaglavlje
?>

<!-- HTML output below is safe, all dynamic data is escaped with htmlspecialchars where needed // HTML izlaz je bezbedan, svi dinamički podaci su očišćeni sa htmlspecialchars gde je potrebno -->

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pet Details</title>
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
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); // Display message safely // Prikaži poruku bezbedno ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['error']); // Display error safely // Prikaži grešku bezbedno ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <script>
            // Show JavaScript alert for modal errors // Prikaži JavaScript alert za greške u modalima
            alert("<?php echo addslashes($_SESSION['error']); ?>");
        </script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?php echo $L['pet_details'] ?? 'Pet Details'; ?>: <?php echo htmlspecialchars($pet['name'] ?? ''); // Display pet name safely // Prikaži ime ljubimca bezbedno ?></h2>
        <a href="pets.php" class="btn btn-secondary"><?php echo $L['back_to_all_pets'] ?? 'Back to All Pets'; ?></a>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <?php if ($pet['image_path']): ?>
                    <a href="<?php echo htmlspecialchars($pet['image_path'] ?? ''); ?>" target="_blank">
                        <img src="<?php echo htmlspecialchars($pet['image_path'] ?? ''); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($pet['name'] ?? ''); ?>">
                    </a>
                <?php else: ?>
                    <div class="text-center py-5 bg-light">
                        <i class="bi bi-image" style="font-size: 3rem;"></i>
                        <p><?php echo $L['no_image_available'] ?? 'No image available'; ?></p>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($pet['name'] ?? ''); ?></h5>
                    <p class="card-text">
                        <strong><?php echo $L['status'] ?? 'Status'; ?>:</strong> 
                        <span class="badge 
                            <?php 
                            // Status color coding // Boja za status
                            switch($pet['status']) {
                                case 'available': echo 'bg-success'; break;
                                case 'adopted': echo 'bg-secondary'; break;
                                case 'fostered': echo 'bg-info'; break;
                                case 'medical': echo 'bg-warning'; break;
                                default: echo 'bg-light text-dark';
                            }
                            ?>">
                            <?php echo $L[$pet['status']] ?? ucfirst($pet['status'] ?? ''); ?>
                        </span>
                    </p>
                    <p class="card-text">
                        <strong><?php echo $L['presence_in_shelter'] ?? 'Presence in Shelter'; ?>:</strong>
                            <?php echo $pet['in_shelter'] ? '<span class="text-success">' . ($L['yes'] ?? 'Yes') . '</span>' : '<span class="text-danger">' . ($L['no'] ?? 'No') . '</span>'; ?>
                    </p>
                    <?php if (!empty($pet['incoming_date']) && $pet['incoming_date'] !== '0000-00-00'): ?>
                        <p class="card-text">
                            <strong><?php echo $L['incoming_date'] ?? 'Incoming Date'; ?>:</strong>
                            <?php echo date('d.m.Y', strtotime($pet['incoming_date'])); ?>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($pet['capture_location'])): ?>
                        <p class="card-text">
                            <strong><?php echo $L['capture_location'] ?? 'Capture Location'; ?>:</strong>
                            <?php echo htmlspecialchars($pet['capture_location'] ?? ''); ?>
                        </p>
                    <?php endif; ?>
                    
                    <?php if (!empty($pet['cage_number'])): ?>
                        <p class="card-text">
                            <strong><?php echo $L['cage_number'] ?? 'Cage Number'; ?>:</strong>
                            <?php echo htmlspecialchars($pet['cage_number'] ?? ''); ?>
                        </p>
                    <?php endif; ?>
                    
                    <?php if (!empty($pet['return_location'])): ?>
                        <p class="card-text">
                            <strong><?php echo $L['return_location'] ?? 'Return Location'; ?>:</strong>
                            <?php echo htmlspecialchars($pet['return_location'] ?? ''); ?>
                        </p>
                    <?php endif; ?>
                    
                    <?php if (!empty($pet['return_date']) && $pet['return_date'] !== '0000-00-00'): ?>
                        <p class="card-text">
                            <strong><?php echo $L['return_date'] ?? 'Return Date'; ?>:</strong>
                            <?php echo date('d.m.Y', strtotime($pet['return_date'])); ?>
                        </p>
                    <?php endif; ?>

                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong><?php echo $L['species'] ?? 'Species'; ?>:</strong> <?php echo $L[$pet['species']] ?? ucfirst($pet['species'] ?? ''); ?>
                    </li>
                    <li class="list-group-item">
                        <strong><?php echo $L['breed'] ?? 'Breed'; ?>:</strong> <?php echo htmlspecialchars($pet['breed'] ?? ''); ?>
                    </li>
                    <li class="list-group-item">
                        <strong><?php echo $L['sex'] ?? 'Sex'; ?>:</strong> <?php echo $L[$pet['sex']] ?? ucfirst($pet['sex'] ?? ''); ?>
                    </li>
                    <?php if (!empty($pet['birth_date']) && $pet['birth_date'] !== '0000-00-00'): ?>
                        <li class="list-group-item">
                            <strong><?php echo $L['date_of_birth'] ?? 'Date of Birth'; ?>:</strong>
                            <?php echo date('d.m.Y', strtotime($pet['birth_date'])); ?>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($pet['coat_color'])): ?>
                        <li class="list-group-item">
                            <strong><?php echo $L['coat_color'] ?? 'Coat Color'; ?>:</strong> <?php echo htmlspecialchars($pet['coat_color']); ?>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($pet['coat_type'])): ?>
                        <li class="list-group-item">
                            <strong><?php echo $L['coat_type'] ?? 'Coat Type'; ?>:</strong> <?php echo htmlspecialchars($pet['coat_type']); ?>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="card-body">
                    <?php if (in_array($_SESSION['role'], ['admin', 'staff', 'volunteer'])): ?>
                        <a href="edit-pet.php?id=<?php echo $pet['id']; ?>" class="card-link btn btn-warning"><?php echo $L['edit'] ?? 'Edit'; ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <!-- Identification section // Sekcija za identifikaciju -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><?php echo $L['identification'] ?? 'Identification'; ?></h5>
                    <?php if (in_array($_SESSION['role'], ['admin', 'staff', 'volunteer'])): ?>
                        <?php
                        $hasIdentificationData = !empty($pet['microchip_number']) ||
                                                (!empty($pet['microchip_date']) && $pet['microchip_date'] !== '0000-00-00') ||
                                                !empty($pet['microchip_location']);
                        ?>
                        <button type="button"
                            class="btn btn-sm <?php echo $hasIdentificationData ? 'btn-warning' : 'btn-info'; ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#identificationModal">
                            <?php echo $hasIdentificationData
                                ? ($L['edit_identification'] ?? 'Edit Identification')
                                : ($L['add_identification'] ?? 'Add Identification'); ?>
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($pet['microchip_number'])): ?>
                        <p><strong><?php echo $L['microchip_number'] ?? 'Microchip Number'; ?>:</strong> <?php echo htmlspecialchars($pet['microchip_number'] ?? ''); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($pet['microchip_date']) && $pet['microchip_date'] !== '0000-00-00'): ?>
                        <p><strong><?php echo $L['microchip_date'] ?? 'Microchip Date'; ?>:</strong> <?php echo date('d.m.Y', strtotime($pet['microchip_date'])); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($pet['microchip_location'])): ?>
                        <p><strong><?php echo $L['microchip_location'] ?? 'Microchip Location'; ?>:</strong> <?php echo htmlspecialchars($pet['microchip_location'] ?? ''); ?></p>
                    <?php endif; ?>
                    <?php if (empty($pet['microchip_number']) && (empty($pet['microchip_date']) || $pet['microchip_date'] === '0000-00-00') && empty($pet['microchip_location'])): ?>
                        <p class="text-muted"><?php echo $L['no_identification_data'] ?? 'No identification data available.'; ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Vaccinations section // Sekcija za vakcinacije -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><?php echo $L['vaccinations'] ?? 'Vaccinations'; ?></h5>
                    <?php if (in_array($_SESSION['role'], ['admin', 'staff', 'volunteer'])): ?>
                        <button type="button"
                            class="btn btn-sm <?php echo !empty($vaccinations) ? 'btn-warning' : 'btn-primary'; ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#vaccinationModal">
                            <?php echo !empty($vaccinations)
                                ? ($L['edit_vaccination'] ?? 'Edit Vaccination')
                                : ($L['add_vaccination'] ?? 'Add Vaccination'); ?>
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php 
                    $vaccination = !empty($vaccinations) ? $vaccinations[0] : null;
                    if ($vaccination): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <?php if (!empty($vaccination['vaccine_type'])): ?>
                                    <p class="mb-2"><strong><?php echo $L['vaccine_type'] ?? 'Vaccine Type'; ?>:</strong> 
                                        <span class="badge bg-primary"><?php echo $L[$vaccination['vaccine_type']] ?? ucfirst($vaccination['vaccine_type']); ?></span>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($vaccination['vaccine_name'])): ?>
                                    <p class="mb-2"><strong><?php echo $L['vaccine_name'] ?? 'Vaccine Name'; ?>:</strong> <?php echo htmlspecialchars($vaccination['vaccine_name'] ?? ''); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($vaccination['batch_number'])): ?>
                                    <p class="mb-2"><strong><?php echo $L['batch_number'] ?? 'Batch Number'; ?>:</strong> <?php echo htmlspecialchars($vaccination['batch_number'] ?? ''); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($vaccination['veterinarian'])): ?>
                                    <p class="mb-2"><strong><?php echo $L['veterinarian'] ?? 'Veterinarian'; ?>:</strong> <?php echo htmlspecialchars($vaccination['veterinarian'] ?? ''); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <?php if (!empty($vaccination['vaccination_date']) && $vaccination['vaccination_date'] !== '0000-00-00'): ?>
                                    <p class="mb-2"><strong><?php echo $L['vaccination_date'] ?? 'Vaccination Date'; ?>:</strong> <?php echo date('d.m.Y', strtotime($vaccination['vaccination_date'])); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($vaccination['expiry_date']) && $vaccination['expiry_date'] !== '0000-00-00'): ?>
                                    <p class="mb-2"><strong><?php echo $L['expiry_date'] ?? 'Expiry Date'; ?>:</strong> <?php echo date('d.m.Y', strtotime($vaccination['expiry_date'])); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($vaccination['notes'])): ?>
                                    <p class="mb-2"><strong><?php echo $L['notes'] ?? 'Notes'; ?>:</strong> <?php echo nl2br(htmlspecialchars($vaccination['notes'] ?? '')); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted"><?php echo $L['no_vaccination_records'] ?? 'No vaccination records available.'; ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Health checks section // Sekcija za zdravstvene preglede -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><?php echo $L['health_checks'] ?? 'Health Checks'; ?></h5>
                    <?php if (in_array($_SESSION['role'], ['admin', 'staff', 'volunteer'])): ?>
                        <button type="button"
                            class="btn btn-sm <?php echo !empty($health_checks) ? 'btn-warning' : 'btn-success'; ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#healthCheckModal">
                            <?php echo !empty($health_checks)
                                ? ($L['edit_health_check'] ?? 'Edit Health Check')
                                : ($L['add_health_check'] ?? 'Add Health Check'); ?>
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($health_checks)): ?>
                        <div class="accordion" id="healthChecksAccordion">
                            <?php foreach ($health_checks as $index => $check): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                        <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $index; ?>">
                                            <?php if (isset($check['health_status']) && !empty($check['health_status'])): ?>
                                                <strong><?php echo $L['health_status'] ?? 'Health Status'; ?>:</strong>
                                                <span class="ms-2 badge 
                                                    <?php 
                                                    // Health status color coding // Boja za zdravsteni status
                                                    switch($check['health_status']) {
                                                        case 'excellent': echo 'bg-success'; break;
                                                        case 'good': echo 'bg-primary'; break;
                                                        case 'fair': echo 'bg-info'; break;
                                                        case 'poor': echo 'bg-warning'; break;
                                                        case 'critical': echo 'bg-danger'; break;
                                                        default: echo 'bg-light text-dark';
                                                    }
                                                    ?>">
                                                    <?php echo $L[$check['health_status']] ?? ucfirst($check['health_status']); ?>
                                                </span>
                                            <?php else: ?>
                                                <?php echo $L['health_check'] ?? 'Health Check'; ?>
                                            <?php endif; ?>
                                        </button>
                                    </h2>
                                    <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#healthChecksAccordion">
                                        <div class="accordion-body">
                                            <?php if (!empty($check['check_date']) && $check['check_date'] !== '0000-00-00'): ?>
                                                <p><strong><?php echo $L['check_date'] ?? 'Check Date'; ?>:</strong> <?php echo date('d.m.Y', strtotime($check['check_date'])); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($check['veterinarian'])): ?>
                                                <p><strong><?php echo $L['veterinarian'] ?? 'Veterinarian'; ?>:</strong> <?php echo htmlspecialchars($check['veterinarian'] ?? ''); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($check['diagnosis'])): ?>
                                                <p><strong><?php echo $L['diagnosis'] ?? 'Diagnosis'; ?>:</strong> <?php echo nl2br(htmlspecialchars($check['diagnosis'] ?? '')); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($check['treatment_plan'])): ?>
                                                <p><strong><?php echo $L['treatment_plan'] ?? 'Treatment Plan'; ?>:</strong> <?php echo nl2br(htmlspecialchars($check['treatment_plan'] ?? '')); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($check['clinical_exam'])): ?>
                                                <p><strong><?php echo $L['clinical_exam'] ?? 'Clinical Exam'; ?>:</strong> <?php echo nl2br(htmlspecialchars($check['clinical_exam'] ?? '')); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($check['animal_statement'])): ?>
                                                <p><strong><?php echo $L['animal_statement'] ?? 'Animal Statement'; ?>:</strong> <?php echo nl2br(htmlspecialchars($check['animal_statement'] ?? '')); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($check['condition'])): ?>
                                                <p><strong><?php echo $L['condition'] ?? 'Condition'; ?>:</strong> <?php echo htmlspecialchars($check['condition'] ?? ''); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($check['health_notes'])): ?>
                                                <p><strong><?php echo $L['health_notes'] ?? 'Health Notes'; ?>:</strong> <?php echo nl2br(htmlspecialchars($check['health_notes'] ?? '')); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p><?php echo $L['no_health_check_records'] ?? 'No health check records available.'; ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ZA DODAVANJE VAKCINACIJE -->
<div class="modal fade" id="addVaccinationModal" tabindex="-1" aria-labelledby="addVaccinationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="add-vaccination.php?pet_id=<?php echo $pet['id']; ?>">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addVaccinationModalLabel"><?php echo $L['add_vaccination'] ?? 'Add Vaccination'; ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label"><?php echo $L['vaccine_type'] ?? 'Vaccine Type'; ?></label>
            <select class="form-select" name="vaccine_type">
              <option value=""><?php echo $L['choose_type'] ?? 'Choose type'; ?></option>
              <option value="rabies"><?php echo $L['rabies'] ?? 'Rabies'; ?></option>
              <option value="distemper"><?php echo $L['distemper'] ?? 'Distemper'; ?></option>
              <option value="parvovirus"><?php echo $L['parvovirus'] ?? 'Parvovirus'; ?></option>
              <option value="other"><?php echo $L['other'] ?? 'Other'; ?></option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['vaccine_name'] ?? 'Vaccine Name'; ?></label>
            <input type="text" class="form-control" name="vaccine_name">
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['batch_number'] ?? 'Batch Number'; ?></label>
            <input type="text" class="form-control" name="batch_number">
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label"><?php echo $L['vaccination_date'] ?? 'Vaccination Date'; ?></label>
              <input type="date" class="form-control" name="vaccination_date">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label"><?php echo $L['expiry_date'] ?? 'Expiry Date'; ?></label>
              <input type="date" class="form-control" name="expiry_date">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['veterinarian'] ?? 'Veterinarian'; ?></label>
            <input type="text" class="form-control" name="veterinarian">
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['notes'] ?? 'Notes'; ?></label>
            <textarea class="form-control" name="notes" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $L['cancel'] ?? 'Cancel'; ?></button>
          <button type="submit" class="btn btn-primary"><?php echo $L['save_vaccination'] ?? 'Save Vaccination'; ?></button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- MODAL ZA DODAVANJE ZDRAVSTVENOG PREGLEDA -->
<div class="modal fade" id="addHealthCheckModal" tabindex="-1" aria-labelledby="addHealthCheckModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="add-health-check.php?pet_id=<?php echo $pet['id']; ?>">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addHealthCheckModalLabel"><?php echo $L['add_health_check'] ?? 'Add Health Check'; ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label"><?php echo $L['check_date'] ?? 'Check Date'; ?></label>
            <input type="date" class="form-control" name="check_date">
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['veterinarian'] ?? 'Veterinarian'; ?></label>
            <input type="text" class="form-control" name="veterinarian">
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['health_status'] ?? 'Health Status'; ?></label>
            <select class="form-select" name="health_status">
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
            <textarea class="form-control" name="diagnosis"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['treatment_plan'] ?? 'Treatment Plan'; ?></label>
            <textarea class="form-control" name="treatment_plan"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['clinical_exam'] ?? 'Clinical Exam'; ?></label>
            <textarea class="form-control" name="clinical_exam"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['animal_statement'] ?? 'Animal Statement'; ?></label>
            <textarea class="form-control" name="animal_statement"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['health_notes'] ?? 'Health Notes'; ?></label>
            <textarea class="form-control" name="health_notes"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $L['cancel'] ?? 'Cancel'; ?></button>
          <button type="submit" class="btn btn-success"><?php echo $L['save_health_check'] ?? 'Save Health Check'; ?></button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- MODAL ZA UREDJIVANJE IDENTIFIKACIJE -->
<div class="modal fade" id="identificationModal" tabindex="-1" aria-labelledby="identificationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="edit-pet.php?id=<?php echo $pet['id']; ?>">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="identificationModalLabel">
            <?php echo $L['edit_identification'] ?? 'Edit Identification'; ?>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label"><?php echo $L['microchip_number'] ?? 'Microchip Number'; ?></label>
            <input type="text" class="form-control" name="microchip_number" value="<?php echo htmlspecialchars($pet['microchip_number'] ?? ''); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['microchip_date'] ?? 'Microchip Date'; ?></label>
            <input type="date" class="form-control" name="microchip_date" value="<?php echo htmlspecialchars($pet['microchip_date'] ?? ''); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['microchip_location'] ?? 'Microchip Location'; ?></label>
            <input type="text" class="form-control" name="microchip_location" value="<?php echo htmlspecialchars($pet['microchip_location'] ?? ''); ?>">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $L['cancel'] ?? 'Cancel'; ?></button>
          <button type="submit" class="btn btn-info">
            <?php echo $L['save_identification'] ?? 'Save Identification'; ?>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- MODAL ZA UREDJIVANJE VAKCINACIJE -->
<?php
$v = !empty($vaccinations) ? $vaccinations[0] : [
    'vaccine_type' => '',
    'vaccine_name' => '',
    'batch_number' => '',
    'vaccination_date' => '',
    'expiry_date' => '',
    'veterinarian' => '',
    'notes' => ''
];
?>
<div class="modal fade" id="vaccinationModal" tabindex="-1" aria-labelledby="vaccinationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="<?php echo !empty($vaccinations)
        ? 'edit-vaccination.php?pet_id=' . $pet['id'] . '&vaccination_id=' . $v['id']
        : 'add-vaccination.php?pet_id=' . $pet['id']; ?>">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="vaccinationModalLabel">
            <?php echo !empty($vaccinations)
                ? ($L['edit_vaccination'] ?? 'Edit Vaccination')
                : ($L['add_vaccination'] ?? 'Add Vaccination'); ?>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label"><?php echo $L['vaccine_type'] ?? 'Vaccine Type'; ?></label>
            <select class="form-select" name="vaccine_type">
              <option value=""><?php echo $L['choose_type'] ?? 'Choose type'; ?></option>
              <option value="rabies" <?php if($v['vaccine_type']=='rabies') echo 'selected'; ?>><?php echo $L['rabies'] ?? 'Rabies'; ?></option>
              <option value="distemper" <?php if($v['vaccine_type']=='distemper') echo 'selected'; ?>><?php echo $L['distemper'] ?? 'Distemper'; ?></option>
              <option value="parvovirus" <?php if($v['vaccine_type']=='parvovirus') echo 'selected'; ?>><?php echo $L['parvovirus'] ?? 'Parvovirus'; ?></option>
              <option value="other" <?php if($v['vaccine_type']=='other') echo 'selected'; ?>><?php echo $L['other'] ?? 'Other'; ?></option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['vaccine_name'] ?? 'Vaccine Name'; ?></label>
            <input type="text" class="form-control" name="vaccine_name" value="<?php echo htmlspecialchars($v['vaccine_name'] ?? ''); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['batch_number'] ?? 'Batch Number'; ?></label>
            <input type="text" class="form-control" name="batch_number" value="<?php echo htmlspecialchars($v['batch_number'] ?? ''); ?>">
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label"><?php echo $L['vaccination_date'] ?? 'Vaccination Date'; ?></label>
              <input type="date" class="form-control" name="vaccination_date" value="<?php echo htmlspecialchars($v['vaccination_date'] ?? ''); ?>">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label"><?php echo $L['expiry_date'] ?? 'Expiry Date'; ?></label>
              <input type="date" class="form-control" name="expiry_date" value="<?php echo htmlspecialchars($v['expiry_date'] ?? ''); ?>">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['veterinarian'] ?? 'Veterinarian'; ?></label>
            <input type="text" class="form-control" name="veterinarian" value="<?php echo htmlspecialchars($v['veterinarian'] ?? ''); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['notes'] ?? 'Notes'; ?></label>
            <textarea class="form-control" name="notes" rows="2"><?php echo htmlspecialchars($v['notes'] ?? ''); ?></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $L['cancel'] ?? 'Cancel'; ?></button>
          <button type="submit" class="btn <?php echo !empty($vaccinations) ? 'btn-warning' : 'btn-primary'; ?>">
            <?php echo $L['save_vaccination'] ?? 'Save Vaccination'; ?>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- MODAL ZA UREDJIVANJE ZDRAVSTVENOG PREGLEDA -->
<?php
$hc = !empty($health_checks) ? $health_checks[0] : [
    'check_date' => '',
    'veterinarian' => '',
    'health_status' => '',
    'diagnosis' => '',
    'treatment_plan' => '',
    'clinical_exam' => '',
    'animal_statement' => '',
    'health_notes' => ''
];
?>
<div class="modal fade" id="healthCheckModal" tabindex="-1" aria-labelledby="healthCheckModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="<?php echo !empty($health_checks)
        ? 'edit-health-check.php?pet_id=' . $pet['id'] . '&health_check_id=' . $hc['id']
        : 'add-health-check.php?pet_id=' . $pet['id']; ?>">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="healthCheckModalLabel">
            <?php echo !empty($health_checks)
                ? ($L['edit_health_check'] ?? 'Edit Health Check')
                : ($L['add_health_check'] ?? 'Add Health Check'); ?>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label"><?php echo $L['check_date'] ?? 'Check Date'; ?></label>
            <input type="date" class="form-control" name="check_date" value="<?php echo htmlspecialchars($hc['check_date'] ?? ''); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['veterinarian'] ?? 'Veterinarian'; ?></label>
            <input type="text" class="form-control" name="veterinarian" value="<?php echo htmlspecialchars($hc['veterinarian'] ?? ''); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['health_status'] ?? 'Health Status'; ?></label>
            <select class="form-select" name="health_status">
              <option value=""><?php echo $L['select_status'] ?? 'Select status'; ?></option>
              <option value="excellent" <?php if($hc['health_status']=='excellent') echo 'selected'; ?>><?php echo $L['excellent'] ?? 'Excellent'; ?></option>
              <option value="good" <?php if($hc['health_status']=='good') echo 'selected'; ?>><?php echo $L['good'] ?? 'Good'; ?></option>
              <option value="fair" <?php if($hc['health_status']=='fair') echo 'selected'; ?>><?php echo $L['fair'] ?? 'Fair'; ?></option>
              <option value="poor" <?php if($hc['health_status']=='poor') echo 'selected'; ?>><?php echo $L['poor'] ?? 'Poor'; ?></option>
              <option value="critical" <?php if($hc['health_status']=='critical') echo 'selected'; ?>><?php echo $L['critical'] ?? 'Critical'; ?></option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['diagnosis'] ?? 'Diagnosis'; ?></label>
            <textarea class="form-control" name="diagnosis"><?php echo htmlspecialchars($hc['diagnosis'] ?? ''); ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['treatment_plan'] ?? 'Treatment Plan'; ?></label>
            <textarea class="form-control" name="treatment_plan"><?php echo htmlspecialchars($hc['treatment_plan'] ?? ''); ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['clinical_exam'] ?? 'Clinical Exam'; ?></label>
            <textarea class="form-control" name="clinical_exam"><?php echo htmlspecialchars($hc['clinical_exam'] ?? ''); ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['animal_statement'] ?? 'Animal Statement'; ?></label>
            <textarea class="form-control" name="animal_statement"><?php echo htmlspecialchars($hc['animal_statement'] ?? ''); ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label"><?php echo $L['health_notes'] ?? 'Health Notes'; ?></label>
            <textarea class="form-control" name="health_notes"><?php echo htmlspecialchars($hc['health_notes'] ?? ''); ?></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $L['cancel'] ?? 'Cancel'; ?></button>
          <button type="submit" class="btn <?php echo !empty($health_checks) ? 'btn-warning' : 'btn-success'; ?>">
            <?php echo $L['save_health_check'] ?? 'Save Health Check'; ?>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
