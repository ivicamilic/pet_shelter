<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: pets.php');
    exit();
}

$pet = $db->fetchOne("SELECT * FROM pets WHERE id = ?", [$_GET['id']]);
if (!$pet) {
    header('Location: pets.php');
    exit();
}
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
    <h2>Pet Details: <?php echo $pet['name']; ?></h2>
    <a href="pets.php" class="btn btn-secondary">Back to All Pets</a>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5><?php echo $pet['name']; ?></h5>
                    <p><strong>Species:</strong> <?php echo $pet['species']; ?></p>
                    <p><strong>Breed:</strong> <?php echo $pet['breed']; ?></p>
                    <p><strong>Sex:</strong> <?php echo $pet['sex']; ?></p>
                    <p><strong>Status:</strong> <?php echo $pet['status']; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>