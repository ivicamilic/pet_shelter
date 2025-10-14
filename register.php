<?php
require_once 'includes/config.php'; // Load configuration // Učitaj konfiguraciju
require_once 'includes/db.php';     // Load database connection // Učitaj konekciju sa bazom

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']); // Sanitize username // Očisti korisničko ime
    $email = trim($_POST['email']);       // Sanitize email // Očisti email
    $password = $_POST['password'];       // Get password // Uzmi lozinku
    $password_confirm = $_POST['password_confirm']; // Get password confirmation // Uzmi potvrdu lozinke
    $full_name = trim($_POST['full_name']); // Sanitize full name // Očisti puno ime
    $role = 'volunteer'; // Default role // Podrazumevana uloga

    // Validation // Validacija
    if (empty($username) || empty($email) || empty($password) || empty($password_confirm)) {
        $error = "All fields are required."; // Error for missing fields // Greška za nedostajuća polja
    } elseif ($password !== $password_confirm) {
        $error = "Passwords do not match."; // Error for password mismatch // Greška za neusklađene lozinke
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long."; // Error for short password // Greška za kratku lozinku
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format."; // Error for invalid email // Greška za neispravan email
    } elseif (!preg_match('/^[a-zA-Z0-9_\-\.]{3,32}$/', $username)) {
        $error = "Username must be 3-32 characters, letters, numbers, dot, dash or underscore."; // Error for invalid username // Greška za neispravno korisničko ime
    } else {
        // Check if user already exists // Provera da li korisnik već postoji
        $existing_user = $db->fetchOne("SELECT id FROM user WHERE username = ? OR email = ?", [$username, $email]);
        
        if ($existing_user) {
            $error = "Username or email already exists."; // Error for duplicate // Greška za duplikat
        } else {
            // Hash password // Hesiraj lozinku
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user using prepared statement // Unesi korisnika koristeći pripremljeni upit
            $db->query(
                "INSERT INTO user (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)",
                [$username, $email, $hashed_password, $full_name, $role]
            );
            
            $_SESSION['message'] = $L['registration_success'] ?? 'Registration successful. You can now login.'; // Success message // Poruka o uspehu
            header('Location: login.php'); // Redirect to login // Preusmeri na prijavu
            exit();
        }
    }
}

include 'includes/header.php'; // Include header // Uključi zaglavlje
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4><?php echo $L['register'] ?? 'Register User'; // Register title // Naslov registracije ?></h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); // Display error safely // Prikaži grešku bezbedno ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label"><?php echo $L['username'] ?? 'Username'; ?></label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label"><?php echo $L['email'] ?? 'Email'; ?></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="full_name" class="form-label"><?php echo $L['full_name'] ?? 'Full Name'; ?></label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label"><?php echo $L['password'] ?? 'Password'; ?></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_confirm" class="form-label"><?php echo $L['password_confirm'] ?? 'Confirm Password'; ?></label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                        </div>
                        <button type="submit" class="btn btn-primary"><?php echo $L['register'] ?? 'Register'; ?></button>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <?php echo $L['already_have_account'] ?? 'Already have an account?'; ?> <a href="login.php"><?php echo $L['login'] ?? 'Login'; ?></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; // Include footer // Uključi podnožje ?>