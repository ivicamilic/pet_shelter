<?php
require_once 'includes/config.php'; // Load configuration // Učitaj konfiguraciju
require_once 'includes/auth.php';   // Load authentication // Učitaj autentifikaciju
require_once 'includes/db.php';     // Load database connection // Učitaj konekciju sa bazom

$lang = $_SESSION['lang'] ?? 'sr'; // Get language from session or default to English // Uzmi jezik iz sesije ili podesi na engleski
$L = require __DIR__ . '/lang/' . $lang . '.php'; // Load language file // Učitaj fajl sa prevodom

redirectIfNotLoggedIn(); // Redirect if user is not logged in // Preusmeri ako korisnik nije prijavljen
redirectIfNotAdmin();    // Redirect if user is not admin // Preusmeri ako korisnik nije admin

// Check if user id is provided // Proveri da li je prosleđen id korisnika
if (!isset($_GET['id'])) {
    header('Location: users.php'); // Redirect if no id // Preusmeri ako nema id
    exit();
}

$user_id = (int)$_GET['id']; // Get user id as integer // Uzmi id korisnika kao ceo broj

// Prevent admin from deleting themselves // Spreči admina da obriše samog sebe
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['error'] = $L['cannot_delete_yourself'] ?? 'You cannot delete yourself.'; // Set error message // Postavi poruku o grešci
    header('Location: users.php'); // Redirect to users // Preusmeri na korisnike
    exit();
}

// Check if user exists // Proveri da li korisnik postoji
$user = $db->fetchOne("SELECT * FROM user WHERE id = ?", [$user_id]);
if (!$user) {
    $_SESSION['error'] = $L['user_not_found'] ?? 'User not found.'; // Set error message // Postavi poruku o grešci
    header('Location: users.php'); // Redirect to users // Preusmeri na korisnike
    exit();
}

// Delete the user // Obriši korisnika
try {
    $db->query("DELETE FROM user WHERE id = ?", [$user_id]); // Use prepared statement // Koristi pripremljeni upit
    $_SESSION['message'] = $L['user_deleted'] ?? 'User deleted successfully.'; // Set success message // Postavi poruku o uspehu
} catch (Exception $e) {
    $_SESSION['error'] = $L['error_deleting_user'] ?? 'Error deleting user.'; // Set error message // Postavi poruku o grešci
}

header('Location: users.php'); // Redirect to users // Preusmeri na korisnike
exit();
?>