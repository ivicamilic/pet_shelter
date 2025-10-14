<?php
require_once 'includes/config.php'; // Load configuration // Učitaj konfiguraciju
require_once 'includes/db.php';     // Load database connection // Učitaj konekciju sa bazom

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

// $vaccinations = $db->fetchAll("SELECT * FROM vaccinations WHERE pet_id = ?", [$pet['id']]); // Fetch vaccinations (disabled) // Uzmi vakcinacije (onemogućeno)
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pet Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Pet Details: <?php echo htmlspecialchars($pet['name']); // Display pet name safely // Prikaži ime ljubimca bezbedno ?></h2>
        <a href="pets.php" class="btn btn-secondary">Back to All Pets</a>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($pet['name']); // Display pet name safely // Prikaži ime ljubimca bezbedno ?></h5>
                    <p><strong>Species:</strong> <?php echo htmlspecialchars($pet['species']); // Display species safely // Prikaži vrstu bezbedno ?></p>
                    <p><strong>Breed:</strong> <?php echo htmlspecialchars($pet['breed']); // Display breed safely // Prikaži rasu bezbedno ?></p>
                    <p><strong>Sex:</strong> <?php echo htmlspecialchars($pet['sex']); // Display sex safely // Prikaži pol bezbedno ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($pet['status']); // Display status safely // Prikaži status bezbedno ?></p>
                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                        <a href="edit-pet.php?id=<?php echo $pet['id']; ?>" class="btn btn-warning">Edit</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Vaccinations</h5>
                </div>
                <div class="card-body">
                    <?php if (false): // Vaccinations disabled // Vakcinacije onemogućene ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Name</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php // foreach ($vaccinations as $vaccination): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($vaccination['vaccine_type']); // Display type safely // Prikaži tip bezbedno ?></td>
                                        <td><?php echo htmlspecialchars($vaccination['vaccine_name']); // Display name safely // Prikaži naziv bezbedno ?></td>
                                        <td><?php echo date('d.m.Y', strtotime($vaccination['vaccination_date'])); // Format date // Formatiraj datum ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No vaccination records available.</p> <!-- No records message // Poruka za nedostupne podatke -->
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>