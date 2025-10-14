<?php
require_once 'includes/config.php'; // Load configuration // Učitaj konfiguraciju
require_once 'includes/auth.php';   // Load authentication // Učitaj autentifikaciju
require_once 'includes/functions.php'; // Load helper functions // Učitaj pomoćne funkcije

$lang = $_SESSION['lang'] ?? 'en'; // Get language from session or default to English // Uzmi jezik iz sesije ili podesi na engleski
$L = require __DIR__ . '/lang/' . $lang . '.php'; // Load language file // Učitaj fajl sa prevodom

redirectIfNotLoggedIn(); // Redirect if user is not logged in // Preusmeri ako korisnik nije prijavljen

$stats = getPetStats(); // Get pet statistics // Uzmi statistiku ljubimaca
$recent_activity = getRecentActivity(); // Get recent activity // Uzmi nedavne aktivnosti

include 'includes/header.php'; // Include header // Uključi zaglavlje
?>

<div class="container mt-4">
    <h2><?php echo $L['dashboard'] ?? 'Dashboard'; // Dashboard title // Naslov kontrolne table ?></h2>
    <p><?php echo $L['welcome'] ?? 'Welcome'; // Welcome message // Poruka dobrodošlice ?>, <?php echo htmlspecialchars($_SESSION['full_name']); // Display user name safely // Prikaži ime korisnika bezbedno ?>!</p>
    
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><?php echo $L['recent_activity'] ?? 'Recent Activity'; // Recent activity title // Naslov nedavne aktivnosti ?></h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?php echo $L['breed'] ?? 'Breed'; // Breed column // Kolona rasa ?></th>                                
                                <th><?php echo $L['title'] ?? 'Title'; // Title column // Kolona naslov ?></th>
                                <th><?php echo $L['microchip_number'] ?? 'Microchip Number'; // Microchip column // Kolona broj mikročipa ?> #</th>
                                <th><?php echo $L['created_by'] ?? 'Created By'; // Created by column // Kolona kreirao ?></th>
                                <th><?php echo $L['created_at'] ?? 'Created At'; // Created at column // Kolona datum kreiranja ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_activity as $activity): ?>
                                <tr>
                                    <td><?php echo ucfirst($activity['breed']); // Display breed // Prikaži rasu ?></td>
                                    <td><?php echo htmlspecialchars($activity['title']); // Display title safely // Prikaži naslov bezbedno ?></td>
                                    <td><?php echo htmlspecialchars($activity['microchip_number']); // Display microchip safely // Prikaži mikročip bezbedno ?></td>
                                    <td><?php echo htmlspecialchars($activity['created_by']); // Display creator safely // Prikaži autora bezbedno ?></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($activity['created_at'])); // Format date // Formatiraj datum ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><?php echo $L['pet_status'] ?? 'Pet Status'; // Pet status title // Naslov statusa ljubimaca ?></h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php foreach ($stats as $stat): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo $L[$stat['status']] ??  ucfirst($stat['status']); // Display status // Prikaži status ?>
                                <span class="badge bg-primary rounded-pill"><?php echo $stat['count']; // Display count // Prikaži broj ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5><?php echo $L['actions'] ?? 'Actions'; // Actions title // Naslov akcija ?></h5>
                </div>
                <div class="card-body">
                    <a href="add-pet.php" class="btn btn-primary mb-2"><?php echo $L['add_pet'] ?? 'Add Pet'; // Add pet button // Dugme za dodavanje ljubimca ?></a>
                    <a href="pets.php" class="btn btn-secondary mb-2"><?php echo $L['view_pets'] ?? 'View Pets'; // View pets button // Dugme za prikaz ljubimaca ?></a>
                    <?php if (isAdmin()): // Check if user is admin // Proveri da li je korisnik admin ?>
                        <a href="register.php" class="btn btn-outline-primary"><?php echo $L['register_user'] ?? 'Register User'; // Register user button // Dugme za registraciju korisnika ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; // Include footer // Uključi podnožje ?>