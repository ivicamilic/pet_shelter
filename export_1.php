<?php
require_once 'includes/config.php'; // Load configuration // Učitaj konfiguraciju
require_once 'includes/auth.php';   // Load authentication // Učitaj autentifikaciju
require_once 'includes/functions.php'; // Load helper functions // Učitaj pomoćne funkcije

$lang = $_SESSION['lang'] ?? 'sr'; // Get language from session or default to Serbian // Uzmi jezik iz sesije ili podesi na srpski
$L = require __DIR__ . '/lang/' . $lang . '.php'; // Load language file // Učitaj fajl sa prevodom

redirectIfNotLoggedIn(); // Redirect if user is not logged in // Preusmeri ako korisnik nije prijavljen

$format = $_GET['format'] ?? 'xls'; // Get export format // Uzmi format izvoza
$search = isset($_GET['search']) ? trim($_GET['search']) : ''; // Get search query // Uzmi upit za pretragu

// Get pets data // Uzmi podatke o ljubimcima
$params = [];
$sql = "SELECT * FROM pets";
$where = "";

if ($search !== '') {
    // Use prepared statement to prevent SQL injection // Koristi pripremljeni upit radi sprečavanja SQL injekcije
    $where = " WHERE name LIKE ? OR breed LIKE ? OR microchip_number LIKE ? ";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= $where . " ORDER BY incoming_date DESC";
$pets = $db->fetchAll($sql, $params); // Fetch pets // Uzmi ljubimce

if ($format === 'xls') {
    // Export to Excel // Izvoz u Excel
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="pets_export.xls"');
    
    echo "<style>table { border-collapse: collapse; } th, td { text-align: center; white-space: nowrap; }</style>";
    echo "<table border='1' style='width: auto;'>";
    
    echo "<tr>";
    // Uncommented columns can be added if needed // Zakomentarisane kolone mogu se dodati po potrebi
    /*echo "<th>" . ($L['name'] ?? 'Name') . "</th>";
    echo "<th>" . ($L['species'] ?? 'Species') . "</th>";
    echo "<th>" . ($L['breed'] ?? 'Breed') . "</th>";*/
    echo "<th>" . ($L['sex'] ?? 'Sex') . "</th>";
    echo "<th>" . ($L['microchip_number'] ?? 'Microchip #') . "</th>";
    //echo "<th>" . ($L['status'] ?? 'Status') . "</th>";
    echo "<th>" . ($L['capture_location'] ?? 'Capture Location') . "</th>";
    echo "<th>" . ($L['incoming_date'] ?? 'Incoming Date') . "</th>";
    echo "<th>" . ($L['presence_in_shelter'] ?? 'Presence in Shelter') . "</th>";
    echo "<th>" . ($L['cage_number'] ?? 'Cage Number') . "</th>";
    echo "</tr>";
    
    foreach ($pets as $pet) {
        echo "<tr>";
        // All output is escaped to prevent XSS // Sav izlaz je očišćen radi sprečavanja XSS-a
        /*echo "<td>" . htmlspecialchars($pet['name']) . "</td>";
        echo "<td>" . htmlspecialchars($L[$pet['species']] ?? $pet['species']) . "</td>";
        echo "<td>" . htmlspecialchars($pet['breed']) . "</td>";*/
        echo "<td>" . htmlspecialchars($L[$pet['sex']] ?? $pet['sex'] ?? '') . "</td>";
        echo "<td style='mso-number-format:\"0\";'>" . htmlspecialchars($pet['microchip_number'] ?? '') . "</td>";
        //echo "<td>" . htmlspecialchars($L[$pet['status']] ?? $pet['status']) . "</td>";
        echo "<td>" . htmlspecialchars($L[$pet['capture_location']] ?? $pet['capture_location'] ?? '') . "</td>";
        echo "<td>" . (!empty($pet['incoming_date']) ? date('d.m.Y', strtotime($pet['incoming_date'])) : '') . "</td>";
        echo "<td>" . ($pet['in_shelter'] ? ($L['yes'] ?? 'Yes') : ($L['no'] ?? 'No')) . "</td>";
        echo "<td>" . htmlspecialchars($L[$pet['cage_number']] ?? $pet['cage_number'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} elseif ($format === 'pdf') {
    // HTML page for printing to PDF // HTML stranica za štampu u PDF
    header('Content-Type: text/html; charset=UTF-8');
    
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>' . ($L['pets_report'] ?? 'Pets Report') . '</title>
        <style>
            @media print {
                .no-print { display: none; }
                thead { display: table-header-group; }
                tfoot { display: table-footer-group; }
                tbody { display: table-row-group; }
            }
            body { font-family: Arial, sans-serif; margin: 20px; }
            table { width: auto; border-collapse: collapse; font-size: 12px; margin: 0 auto; }
            th, td { border: 1px solid #000; padding: 5px 20px; text-align: center; vertical-align: middle; }
            th { background-color: #f0f0f0; font-weight: bold; }
            img { max-width: 40px; max-height: 40px; object-fit: cover; }
            h2 { text-align: center; margin-bottom: 20px; }
            .print-btn { margin: 20px 0; text-align: center; }
            @page {
                margin: 1cm;
                @bottom-center {
                    content: "Stranica " counter(page) " od " counter(pages);
                    font-size: 10px;
                }
            }
        </style>
        <script>
            // PDF opens in new tab without auto-printing // PDF se otvara u novom tabu bez automatske štampe
            // User can manually print if needed // Korisnik može ručno da štampa po potrebi
            }
        </script>
    </head>
    <body>
        <div class="no-print print-btn">
            <button onclick="window.print()">Print to PDF</button>
            <button onclick="window.close()">Close</button>
        </div>
        <h2>' . ($L['pets_report'] ?? 'Pets Report') . '</h2>
        <table>
            <thead>
                <tr>
                    <th>' . ($L['image'] ?? 'Image') . '</th>
                    <th>' . ($L['sex'] ?? 'Sex') . '</th>
                    <th>' . ($L['microchip_number'] ?? 'Microchip #') . '</th>
                    <th>' . ($L['capture_location'] ?? 'Capture Location') . '</th>
                    <th>' . ($L['incoming_date'] ?? 'Date') . '</th>
                    <th>' . ($L['presence_in_shelter'] ?? 'Presence') . '</th>
                    <th>' . ($L['cage_number'] ?? 'Cage #') . '</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($pets as $pet) {
        $imageCell = !empty($pet['image_path']) ? '<div style="width:30px;height:30px;display:inline-block;background:#f0f0f0;border:1px solid #ccc;text-align:center;font-size:8px;vertical-align:middle;">IMG</div>' : '';
        echo '<tr>
                <td>' . $imageCell . '</td>
                <td>' . htmlspecialchars($L[$pet['sex']] ?? $pet['sex'] ?? '') . '</td>
                <td>' . htmlspecialchars($pet['microchip_number'] ?? '') . '</td>
                <td>' . htmlspecialchars($L[$pet['capture_location']] ?? $pet['capture_location'] ?? '') . '</td>
                <td>' . (!empty($pet['incoming_date']) ? date('d.m.Y', strtotime($pet['incoming_date'])) : '') . '</td>
                <td>' . ($pet['in_shelter'] ? ($L['yes'] ?? 'Yes') : ($L['no'] ?? 'No')) . '</td>
                <td>' . htmlspecialchars($L[$pet['cage_number']] ?? $pet['cage_number'] ?? '') . '</td>
              </tr>';
    }
    echo '</tbody>
    </table>
    </body>
    </html>';
}
?>
