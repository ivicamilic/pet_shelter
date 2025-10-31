<?php
require_once 'includes/config.php'; // Load configuration // Učitaj konfiguraciju
require_once 'includes/auth.php';   // Load authentication // Učitaj autentifikaciju
require_once 'includes/functions.php'; // Load helper functions // Učitaj pomoćne funkcije

$lang = $_SESSION['lang'] ?? 'sr'; // Get language from session or default to Serbian // Uzmi jezik iz sesije ili podesi na srpski
$L = require __DIR__ . '/../lang/' . $lang . '.php'; // Load language file // Učitaj fajl sa prevodom

// Language switcher (handle language change) // Jezički izbor (obrada promene jezika)
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'sr'])) {
    $_SESSION['lang'] = $_GET['lang'];

    // Copy all existing GET parameters // Kopiraj sve postojeće GET parametre
    $queryParams = $_GET;

    // Remove 'lang' parameter to avoid duplication // Ukloni 'lang' parametar da se ne bi ponavljao
    unset($queryParams['lang']);

    // Get base path without parameters // Uzmi osnovnu putanju bez parametara
    $url = strtok($_SERVER['REQUEST_URI'], '?');

    // Create new query string with saved parameters // Kreiraj novi string sa sačuvanim parametrima
    $newQueryString = http_build_query($queryParams);

    // Build final redirect URL // Sastavi finalni URL za preusmeravanje
    $redirectUrl = $url;
    if (!empty($newQueryString)) {
        $redirectUrl .= '?' . $newQueryString;
    }

    header("Location: " . $redirectUrl); // Redirect after language change // Preusmeri nakon promene jezika
    exit();
}

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $L['pet_shelter'] ?? 'Pet Shelter'; // System name // Naziv sistema ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation bar // Navigaciona traka -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <img src="logo_m.png" alt="Logo" height="32" class="me-2" onerror="this.src='assets/images/logo_m.png'; this.onerror=null;">
                <?php echo $L['pet_shelter'] ?? 'Pet Shelter'; // System name // Naziv sistema ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><?php echo $L['dashboard'] ?? 'Dashboard'; // Dashboard link // Link za kontrolnu tablu ?></a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="pets.php"><?php echo $L['pets'] ?? 'Pets'; // Pets link // Link za ljubimce ?></a>
                    </li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php"><?php echo $L['users'] ?? 'Users'; // Users link // Link za korisnike ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav align-items-center">
                    <!-- Language Switcher // Izbor jezika -->
<?php
                        // Build correct links preserving all GET parameters // Pravi ispravne linkove sa svim GET parametrima
                        $queryParams = $_GET;

                        // English link // Link za engleski
                        $queryParams['lang'] = 'en';
                        $en_url = '?' . http_build_query($queryParams);

                        // Serbian link // Link za srpski
                        $queryParams['lang'] = 'sr';
                        $sr_url = '?' . http_build_query($queryParams);
                    ?>
                    <li class="nav-item me-2">
                        <a href="<?php echo $en_url; ?>" class="nav-link p-0 <?php echo $lang === 'en' ? 'border border-primary rounded' : ''; ?>" title="English">
                            <img src="https://cdn.jsdelivr.net/gh/hjnilsson/country-flags/svg/gb.svg" alt="English" width="28" height="20">
                        </a>
                    </li>
                    <li class="nav-item me-3">
                        <a href="<?php echo $sr_url; ?>" class="nav-link p-0 <?php echo $lang === 'sr' ? 'border border-primary rounded' : ''; ?>" title="Srpski">
                            <img src="https://cdn.jsdelivr.net/gh/hjnilsson/country-flags/svg/rs.svg" alt="Srpski" width="28" height="20">
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['username'] ?? ''); // Display username safely // Prikaži korisničko ime bezbedno ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="edit-user.php"><?php echo $L['profile'] ?? 'Profile'; // Profile link // Link za profil ?></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><?php echo $L['logout'] ?? 'Logout'; // Logout link // Link za odjavu ?></a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4">
