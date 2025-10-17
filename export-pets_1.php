<?php
require_once 'includes/config.php'; // Load configuration // Učitaj konfiguraciju
require_once 'includes/db.php';     // Load database connection // Učitaj konekciju sa bazom

// Check if user is logged in // Proveri da li je korisnik prijavljen
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login // Preusmeri na prijavu
    exit();
}

$lang = $_SESSION['lang'] ?? 'en'; // Get language from session or default to English // Uzmi jezik iz sesije ili podesi na engleski
$L = [];
if (file_exists(__DIR__ . '/lang/' . $lang . '.php')) {
    $L = require __DIR__ . '/lang/' . $lang . '.php'; // Load language file // Učitaj fajl sa prevodom
}

$format = $_GET['format'] ?? 'xls'; // Get export format // Uzmi format izvoza
$search = $_GET['search'] ?? '';    // Get search query // Uzmi upit za pretragu

// Get pets data // Uzmi podatke o ljubimcima
$params = [];
$sql = "SELECT * FROM pets";
if ($search !== '') {
    // Use prepared statement to prevent SQL injection // Koristi pripremljeni upit radi sprečavanja SQL injekcije
    $sql .= " WHERE name LIKE ? OR breed LIKE ? OR microchip_number LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " ORDER BY id DESC";

$pets = $db->fetchAll($sql, $params); // Fetch pets // Uzmi ljubimce

if ($format === 'xls') {
    // Set headers for Excel export // Postavi zaglavlja za Excel izvoz
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="pets_export.xls"');
    
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>" . ($L['name'] ?? 'Name') . "</th><th>" . ($L['species'] ?? 'Species') . "</th><th>" . ($L['breed'] ?? 'Breed') . "</th><th>" . ($L['sex'] ?? 'Sex') . "</th><th>" . ($L['status'] ?? 'Status') . "</th><th>" . ($L['microchip_number'] ?? 'Microchip') . "</th><th>" . ($L['presence_in_shelter'] ?? 'In Shelter') . "</th><th>" . ($L['incoming_date'] ?? 'Incoming Date') . "</th></tr>";
    foreach ($pets as $pet) {
        echo "<tr>";
        echo "<td>" . $pet['id'] . "</td>";
        echo "<td>" . htmlspecialchars($pet['name']) . "</td>"; // Escape output // Očisti izlaz
        echo "<td>" . htmlspecialchars($L[$pet['species']] ?? $pet['species']) . "</td>";
        echo "<td>" . htmlspecialchars($pet['breed']) . "</td>";
        echo "<td>" . htmlspecialchars($L[$pet['sex']] ?? $pet['sex']) . "</td>";
        echo "<td>" . htmlspecialchars($L[$pet['status']] ?? $pet['status']) . "</td>";
        echo "<td>" . htmlspecialchars($pet['microchip_number']) . "</td>";
        echo "<td>" . ($pet['in_shelter'] ? ($L['yes'] ?? 'Yes') : ($L['no'] ?? 'No')) . "</td>";
        echo "<td>" . ($pet['incoming_date'] ? date('d.m.Y', strtotime($pet['incoming_date'])) : '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} elseif ($format === 'pdf') {
    // Set headers for PDF export // Postavi zaglavlja za PDF izvoz
    header('Content-Type: text/html; charset=UTF-8');
    
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
    table { width: 100%; border-collapse: collapse; font-size: 10px; }
    th, td { border: 1px solid #000; padding: 3px; text-align: left; vertical-align: top; }
    th { background-color: #f0f0f0; font-weight: bold; }
    .img-cell { width: 60px; text-align: center; }
    .img-cell img { max-width: 50px; max-height: 50px; }
    </style></head><body>';
    
    echo '<h2>' . ($L['pet_shelter'] ?? 'Pet Shelter') . ' - ' . ($L['export'] ?? 'Export') . '</h2>';
    echo '<table>';
    echo '<tr><th>' . ($L['image'] ?? 'Image') . '</th><th>' . ($L['name'] ?? 'Name') . '</th><th>' . ($L['species'] ?? 'Species') . '</th><th>' . ($L['breed'] ?? 'Breed') . '</th><th>' . ($L['sex'] ?? 'Sex') . '</th><th>' . ($L['status'] ?? 'Status') . '</th><th>' . ($L['microchip_number'] ?? 'Microchip') . '</th><th>' . ($L['presence_in_shelter'] ?? 'In Shelter') . '</th><th>' . ($L['incoming_date'] ?? 'Date') . '</th></tr>';
    
    foreach ($pets as $pet) {
        echo '<tr>';
        echo '<td class="img-cell">';
        if (!empty($pet['image_path'])) {
            echo '<img src="' . htmlspecialchars($pet['image_path']) . '" alt="Pet">'; // Escape output // Očisti izlaz
        } else {
            echo ($L['no_image_available'] ?? 'No image');
        }
        echo '</td>';
        echo '<td>' . htmlspecialchars($pet['name']) . '</td>';
        echo '<td>' . htmlspecialchars($L[$pet['species']] ?? $pet['species']) . '</td>';
        echo '<td>' . htmlspecialchars($pet['breed']) . '</td>';
        echo '<td>' . htmlspecialchars($L[$pet['sex']] ?? $pet['sex']) . '</td>';
        echo '<td>' . htmlspecialchars($L[$pet['status']] ?? $pet['status']) . '</td>';
        echo '<td>' . htmlspecialchars($pet['microchip_number']) . '</td>';
        echo '<td>' . ($pet['in_shelter'] ? ($L['yes'] ?? 'Yes') : ($L['no'] ?? 'No')) . '</td>';
        echo '<td>' . ($pet['incoming_date'] ? date('d.m.Y', strtotime($pet['incoming_date'])) : '') . '</td>';
        echo '</tr>';
    }
    
    echo '</table></body></html>';
}
?>