<?php
require_once 'config.php'; // Load configuration // Učitaj konfiguraciju
require_once 'db.php';     // Load database connection // Učitaj konekciju sa bazom

function loginUser($username, $password) {
    global $db;
    
    // Fetch user by username using prepared statement // Uzmi korisnika po korisničkom imenu koristeći pripremljeni upit
    $user = $db->fetchOne("SELECT * FROM user WHERE username = ?", [$username]);
    
    // Verify password using password_hash // Proveri lozinku koristeći password_hash
    if ($user && password_verify($password, $user['password'])) {
        // Set session variables // Postavi sesijske promenljive
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        return true;
    }
    
    return false; // Login failed // Prijava nije uspela
}

function logoutUser() {
    session_unset();   // Unset all session variables // Ukloni sve sesijske promenljive
    session_destroy(); // Destroy session // Uništi sesiju
}

function getUserById($id) {
    global $db;
    // Fetch user by ID using prepared statement // Uzmi korisnika po ID koristeći pripremljeni upit
    return $db->fetchOne("SELECT * FROM user WHERE id = ?", [$id]);
}
?>