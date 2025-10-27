<?php
require_once 'includes/config.php'; // Load configuration // Učitaj konfiguraciju
require_once 'includes/auth.php';   // Load authentication // Učitaj autentifikaciju
require_once 'includes/db.php';     // Load database connection // Učitaj konekciju sa bazom
require_once 'includes/functions.php'; // Load helper functions // Učitaj pomoćne funkcije

redirectIfNotLoggedIn(); // Redirect if user is not logged in // Preusmeri ako korisnik nije prijavljen
redirectIfNotAdmin();    // Redirect if user is not admin // Preusmeri ako korisnik nije admin

$lang = $_SESSION['lang'] ?? 'sr'; // Get language from session or default to English // Uzmi jezik iz sesije ili podesi na engleski
$L = require __DIR__ . '/lang/' . $lang . '.php'; // Load language file // Učitaj fajl sa prevodom

if (!isset($_GET['id'])) {
    header('Location: users.php'); // Redirect if no id // Preusmeri ako nema id
    exit();
}

$user_id = $_GET['id']; // Get user id // Uzmi id korisnika
$user = getUserById($user_id); // Fetch user data // Uzmi podatke o korisniku

if (!$user) {
    header('Location: users.php'); // Redirect if user not found // Preusmeri ako korisnik nije pronađen
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']); // Sanitize username // Očisti korisničko ime
    $email = trim($_POST['email']);       // Sanitize email // Očisti email
    $full_name = trim($_POST['full_name']); // Sanitize full name // Očisti puno ime
    $role = $_POST['role'];               // Get role // Uzmi ulogu
    $password = $_POST['password'];       // Get password // Uzmi lozinku
    $password_confirm = $_POST['password_confirm']; // Get password confirmation // Uzmi potvrdu lozinke

    // Validation // Validacija
    if (empty($username) || empty($email) || empty($full_name) || empty($role)) {
        $error = $L['required_fields_missing'] ?? 'Required fields are missing.'; // Error for missing fields // Greška za nedostajuća polja
    } elseif (!empty($password) && $password !== $password_confirm) {
        $error = $L['passwords_do_not_match'] ?? 'Passwords do not match.'; // Error for password mismatch // Greška za neusklađene lozinke
    } else {
        // Check if user already exists (except current) // Proveri da li korisnik već postoji (osim trenutnog)
        $existing_user = $db->fetchOne(
            "SELECT id FROM user WHERE (username = ? OR email = ?) AND id != ?",
            [$username, $email, $user_id]
        );
        
        if ($existing_user) {
            $error = $L['username_or_email_exists'] ?? 'Username or email already exists.'; // Error for duplicate // Greška za duplikat
        } else {
            // Update user // Ažuriranje korisnika
            $sql = "UPDATE user SET username = ?, email = ?, full_name = ?, role = ?";
            $params = [$username, $email, $full_name, $role];
            
            // Update password only if entered // Ažuriraj lozinku samo ako je uneta
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash password // Hesiraj lozinku
                $sql .= ", password = ?";
                $params[] = $hashed_password;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $user_id;
            
            $db->query($sql, $params); // Use prepared statement // Koristi pripremljeni upit
            
            $_SESSION['message'] = $L['user_updated_successfully'] ?? 'User updated successfully!'; // Success message // Poruka o uspehu
            header('Location: users.php'); // Redirect to users // Preusmeri na korisnike
            exit();
        }
    }
}

include 'includes/header.php'; // Include header // Uključi zaglavlje
?>

<div class="container mt-4">
    <h2><?php echo $L['edit_user'] ?? 'Edit User'; // Edit user title // Naslov za izmenu korisnika ?>: <?php echo htmlspecialchars($user['username']); // Display username safely // Prikaži korisničko ime bezbedno ?></h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; // Display error // Prikaži grešku ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="username" class="form-label"><?php echo $L['username'] ?? 'Username'; ?>*</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label"><?php echo $L['email'] ?? 'Email'; ?>*</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="full_name" class="form-label"><?php echo $L['full_name'] ?? 'Full Name'; ?>*</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" 
                           value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="role" class="form-label"><?php echo $L['role'] ?? 'Role'; ?>*</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>><?php echo $L['admin'] ?? 'Admin'; ?></option>
                        <option value="staff" <?php echo $user['role'] === 'staff' ? 'selected' : ''; ?>><?php echo $L['staff'] ?? 'Staff'; ?></option>
                        <option value="info" <?php echo $user['role'] === 'info' ? 'selected' : ''; ?>><?php echo $L['info'] ?? 'Info'; ?></option>
                        <option value="volunteer" <?php echo $user['role'] === 'volunteer' ? 'selected' : ''; ?>><?php echo $L['volunteer'] ?? 'Volunteer'; ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label"><?php echo $L['new_password'] ?? 'New Password'; ?></label>
                    <input type="password" class="form-control" id="password" name="password">
                    <small class="text-muted"><?php echo $L['leave_blank_keep_password'] ?? 'Leave blank to keep current password'; ?></small>
                </div>
                <div class="mb-3">
                    <label for="password_confirm" class="form-label"><?php echo $L['confirm_new_password'] ?? 'Confirm New Password'; ?></label>
                    <input type="password" class="form-control" id="password_confirm" name="password_confirm">
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><?php echo $L['update_user'] ?? 'Update User'; ?></button>
            <a href="users.php" class="btn btn-secondary"><?php echo $L['cancel'] ?? 'Cancel'; ?></a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; // Include footer // Uključi podnožje ?>
