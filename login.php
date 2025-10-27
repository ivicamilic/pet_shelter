<?php
require_once 'includes/config.php'; // Load configuration // Učitaj konfiguraciju
require_once 'includes/db.php';     // Load database connection // Učitaj konekciju sa bazom
require_once 'includes/auth.php';   // Load authentication // Učitaj autentifikaciju

$lang = $_SESSION['lang'] ?? 'sr'; // Get language from session or default to English // Uzmi jezik iz sesije ili podesi na engleski
$L = require __DIR__ . '/lang/' . $lang . '.php'; // Load language file // Učitaj fajl sa prevodom

if (isLoggedIn()) {
    header('Location: pets.php'); // Redirect if already logged in // Preusmeri ako je korisnik već prijavljen
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']); // Sanitize username // Očisti korisničko ime
    $password = trim($_POST['password']); // Sanitize password // Očisti lozinku
    
    if (empty($username) || empty($password)) {
        $error = $L['error_fields_required'] ?? 'Both username and password are required.'; // Error for missing fields // Greška za nedostajuća polja
    } elseif (loginUser($username, $password)) { // Try to login // Pokušaj prijavu
        header('Location: pets.php'); // Redirect on success // Preusmeri na uspeh
        exit();
    } else {
        $error = $L['error_invalid_credentials'] ?? 'Invalid username or password.'; // Error for invalid credentials // Greška za neispravne podatke
    }
}

include 'includes/header.php'; // Include header // Uključi zaglavlje
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4><?php echo $L['login'] ?? 'Login'; // Login title // Naslov prijave ?></h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); // Display error safely // Prikaži grešku bezbedno ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['message']); // Display message safely // Prikaži poruku bezbedno ?></div>
                        <?php unset($_SESSION['message']); ?>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label"><?php echo $L['username'] ?? 'Username'; // Username label // Oznaka za korisničko ime ?></label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label"><?php echo $L['password'] ?? 'Password'; // Password label // Oznaka za lozinku ?></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary"><?php echo $L['login'] ?? 'Login'; // Login button // Dugme za prijavu ?></button>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <?php echo $L['dont_have_account'] ?? 'Don\'t have an account?'; // Register prompt // Poruka za registraciju ?> 
                    <a href="register.php"><?php echo $L['register'] ?? 'Register'; // Register link // Link za registraciju ?></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; // Include footer // Uključi podnožje ?>