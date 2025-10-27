<?php
require_once 'includes/config.php'; // Load configuration // Učitaj konfiguraciju
require_once 'includes/auth.php';   // Load authentication // Učitaj autentifikaciju
require_once 'includes/db.php';     // Load database connection // Učitaj konekciju sa bazom
require_once 'includes/functions.php'; // Load helper functions // Učitaj pomoćne funkcije

redirectIfNotLoggedIn(); // Redirect if user is not logged in // Preusmeri ako korisnik nije prijavljen
redirectIfNotAdmin();    // Redirect if user is not admin // Preusmeri ako korisnik nije admin

$lang = $_SESSION['lang'] ?? 'sr'; // Get language from session or default to English // Uzmi jezik iz sesije ili podesi na engleski
$L = require __DIR__ . '/lang/' . $lang . '.php'; // Load language file // Učitaj fajl sa prevodom

$users = $db->fetchAll("SELECT * FROM user ORDER BY created_at DESC"); // Fetch all users // Uzmi sve korisnike

include 'includes/header.php'; // Include header // Uključi zaglavlje
?>

<div class="container mt-4">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); // Display success message safely // Prikaži poruku o uspehu bezbedno ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); // Display error message safely // Prikaži poruku o grešci bezbedno ?></div>
    <?php endif; ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?php echo $L['user_management'] ?? 'User Management'; // User management title // Naslov za upravljanje korisnicima ?></h2>
        <a href="register.php" class="btn btn-primary"><?php echo $L['add_new_user'] ?? 'Add New User'; // Add user button // Dugme za dodavanje korisnika ?></a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th><?php echo $L['id'] ?? 'ID'; // ID column // Kolona za ID ?></th>
                            <th><?php echo $L['username'] ?? 'Username'; // Username column // Kolona za korisničko ime ?></th>
                            <th><?php echo $L['email'] ?? 'Email'; // Email column // Kolona za email ?></th>
                            <th><?php echo $L['full_name'] ?? 'Full Name'; // Full name column // Kolona za puno ime ?></th>
                            <th><?php echo $L['role'] ?? 'Role'; // Role column // Kolona za ulogu ?></th>
                            <th><?php echo $L['created_at'] ?? 'Created At'; // Created at column // Kolona za datum kreiranja ?></th>
                            <th><?php echo $L['actions'] ?? 'Actions'; // Actions column // Kolona za akcije ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; // Display user ID // Prikaži ID korisnika ?></td>
                                <td><?php echo htmlspecialchars($user['username']); // Display username safely // Prikaži korisničko ime bezbedno ?></td>
                                <td><?php echo htmlspecialchars($user['email']); // Display email safely // Prikaži email bezbedno ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); // Display full name safely // Prikaži puno ime bezbedno ?></td>
                                <td>
                                    <span class="badge
                                        <?php
                                        // Color coding for roles // Boja za uloge
                                        switch($user['role']) {
                                            case 'admin': echo 'bg-danger'; break;
                                            case 'staff': echo 'bg-info'; break;
                                            case 'info': echo 'bg-warning'; break;
                                            default: echo 'bg-secondary';
                                        }
                                        ?>">
                                        <?php echo ucfirst($L[$user['role']] ?? $user['role']); // Display role // Prikaži ulogu ?>
                                    </span>
                                </td>
                                <td><?php echo date('d.m.Y', strtotime($user['created_at'])); // Format and display date // Formatiraj i prikaži datum ?></td>
                                <td>
                                    <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning"><?php echo $L['edit'] ?? 'Edit'; // Edit button // Dugme za izmenu ?></a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <!-- Delete button with confirmation // Dugme za brisanje sa potvrdom -->
                                        <a href="delete-user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('<?php echo $L['are_you_sure'] ?? 'Are you sure?'; ?>')"><?php echo $L['delete'] ?? 'Delete'; // Delete button // Dugme za brisanje ?></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; // Include footer // Uključi podnožje ?>
