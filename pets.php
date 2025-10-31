<?php
require_once 'includes/config.php'; // Load configuration // Učitaj konfiguraciju
require_once 'includes/auth.php';   // Load authentication // Učitaj autentifikaciju
require_once 'includes/functions.php'; // Load helper functions // Učitaj pomoćne funkcije

$lang = $_SESSION['lang'] ?? 'sr'; // Get language from session or default to Serbian // Uzmi jezik iz sesije ili podesi na srpski
$L = require __DIR__ . '/lang/' . $lang . '.php'; // Load language file // Učitaj fajl sa prevodom

redirectIfNotLoggedIn(); // Redirect if user is not logged in // Preusmeri ako korisnik nije prijavljen

// Pagination parameters // Parametri za paginaciju
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Ensure page >= 1 // Osiguraj da je stranica >= 1
$limit = isset($_GET['limit']) ? min(max(1, (int)$_GET['limit']), 100) : 10; // Limit between 1 and 100 // Limit između 1 i 100
$offset = ($page - 1) * $limit;

// Sorting functionality // Funkcionalnost sortiranja
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'incoming_date';
$order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'asc' : 'desc';

// Validate sort column // Validiraj kolonu za sortiranje
$allowed_sorts = ['microchip_number', 'incoming_date', 'sex', 'in_shelter', 'id'];
if (!in_array($sort, $allowed_sorts)) {
    $sort = 'incoming_date';
}

// Search functionality // Funkcionalnost pretrage
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$params = [];
$count_params = [];
$where = "";

if ($search !== '') {
    // Bilingual search mapping // Dvojezično mapiranje pretrage
    $searchLower = strtolower($search);
    $searchMappings = [
        'da' => 'yes', 'yes' => 'da',
        'ne' => 'no', 'no' => 'ne',
        'mužjak' => 'male', 'male' => 'mužjak',
        'ženka' => 'female', 'female' => 'ženka'
    ];
    $alternativeSearch = $searchMappings[$searchLower] ?? $searchLower;
    
    // Use prepared statement to prevent SQL injection // Koristi pripremljeni upit radi sprečavanja SQL injekcije
    $search_condition = " microchip_number LIKE ? OR capture_location LIKE ? OR DATE_FORMAT(incoming_date, '%d.%m.%Y') LIKE ? OR cage_number LIKE ? OR (CASE WHEN sex = 'female' THEN 'ženka' WHEN sex = 'male' THEN 'mužjak' ELSE sex END LIKE ? OR sex LIKE ?) OR (CASE WHEN in_shelter = 1 THEN 'da' ELSE 'ne' END LIKE ? OR CASE WHEN in_shelter = 1 THEN 'yes' ELSE 'no' END LIKE ?) ";
    if ($where === "") {
        $where = " WHERE " . $search_condition;
    } else {
        $where .= " AND " . $search_condition;
    }
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$alternativeSearch%";
    $params[] = "%$search%";
    $params[] = "%$alternativeSearch%";

    
    // Same parameters for count query // Isti parametri za brojanje
    $count_params = $params;
}

// Get total pets count for pagination (with search filter) // Uzmi ukupan broj ljubimaca za paginaciju (sa filterom pretrage)
$total_pets_row = $db->fetchOne("SELECT COUNT(*) AS cnt FROM pets" . $where, $count_params);
$total_pets = $total_pets_row['cnt'];
$total_pages = ceil($total_pets / $limit);

// Main query for pets // Glavni upit za ljubimce
$sql = "SELECT * FROM pets" . $where . " ORDER BY $sort $order LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$pets = $db->fetchAll($sql, $params); // Fetch pets // Uzmi ljubimce

// Handle AJAX requests // Obradi AJAX zahteve
if (isset($_GET['ajax'])) {
    echo "DEBUG: AJAX received for search='" . htmlspecialchars($_GET['search'] ?? '') . "' limit=" . intval($_GET['limit'] ?? 10) . "\n";
    // Return only table body and pagination for AJAX // Vrati samo telo tabele i paginaciju za AJAX
    ob_start();
    echo '<!--AJAX_START-->';
    include 'pets_table_1.php';
    echo '<!--AJAX_END-->';
    $ajax_content = ob_get_clean();
    echo "DEBUG: AJAX content length=" . strlen($ajax_content) . "\n";
    if (empty(trim($ajax_content))) {
        echo 'ERROR: Empty AJAX content';
    } else {
        echo $ajax_content;
    }
    exit;
}

include 'includes/header.php'; // Include header // Uključi zaglavlje
?>

<div class="container mt-4">
    <?php if (isset($_SESSION['error'])): ?>
        <script>
            // Show JavaScript alert for errors // Prikaži JavaScript alert za greške
            alert("<?php echo addslashes($_SESSION['error']); ?>");
        </script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type'] ?? 'info'; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['message']); // Display message safely // Prikaži poruku bezbedno ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <form method="get" class="me-2">
                <div class="d-flex align-items-center">
                    <label for="limit" class="form-label me-2 mb-0"><?php echo $L['show'] ?? 'Show'; ?></label>
                    <select name="limit" id="limit" class="form-select form-select-sm me-2" style="width:60px;" onchange="this.form.submit()">
                        <option value="10" <?php if($limit == 10) echo 'selected'; ?>>10</option>
                        <option value="25" <?php if($limit == 25) echo 'selected'; ?>>25</option>
                        <option value="50" <?php if($limit == 50) echo 'selected'; ?>>50</option>
                    </select>
                    <span><?php echo $L['rows'] ?? 'rows'; ?></span>
                    <input type="hidden" name="page" value="<?php echo $page; ?>">
                    <?php if (isset($_GET['search'])): ?>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    <?php endif; ?>
                    <?php if (isset($_GET['sort'])): ?>
                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($_GET['sort'] ?? ''); ?>">
                    <?php endif; ?>
                    <?php if (isset($_GET['order'])): ?>
                        <input type="hidden" name="order" value="<?php echo htmlspecialchars($_GET['order'] ?? ''); ?>">
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <h2><?php echo $L['all_pets'] ?? 'All Pets'; ?></h2>
        <div class="d-flex">
            <form method="get" class="d-flex">
                <input type="text" name="search" id="searchInput" class="form-control form-control-sm me-2" placeholder="<?php echo $L['search_pets'] ?? 'Search pets...'; ?>" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search'] ?? '') : ''; ?>" style="width: 200px;">
                <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-search"></i> <?php echo $L['search'] ?? 'Search'; ?></button>
                <input type="hidden" name="limit" value="<?php echo $limit; ?>">
                <input type="hidden" name="page" value="1">
                <?php if (isset($_GET['sort'])): ?>
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($_GET['sort'] ?? ''); ?>">
                <?php endif; ?>
                <?php if (isset($_GET['order'])): ?>
                    <input type="hidden" name="order" value="<?php echo htmlspecialchars($_GET['order'] ?? ''); ?>">
                <?php endif; ?>
            </form>
            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff')): ?>
            <a href="export.php?format=xls<?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo isset($_GET['sort']) ? '&sort=' . urlencode($_GET['sort']) : ''; ?><?php echo isset($_GET['order']) ? '&order=' . urlencode($_GET['order']) : ''; ?>" class="btn btn-outline-success btn-sm ms-2">
                <i class="bi bi-file-earmark-excel"></i> <?php echo $L['export_xls'] ?? 'Export XLS'; ?>
            </a>
            <a href="export.php?format=pdf" target="_blank" class="btn btn-outline-danger btn-sm ms-2">
                <i class="bi bi-file-earmark-pdf"></i> <?php echo $L['export_pdf'] ?? 'Export PDF'; ?>
            </a>
            <?php endif; ?>
            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff')): ?>
                <a href="add-pet.php" class="btn btn-outline-primary btn-sm ms-2"><i class="bi bi-plus-circle"></i> <?php echo $L['add_new_pet'] ?? 'Add New Pet'; ?></a>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'pets_table.php'; // Include pets table // Uključi tabelu ljubimaca ?>
</div>

<!-- Delete Confirmation Modal // Modal za potvrdu brisanja -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo $L['confirm_delete'] ?? 'Confirm Delete'; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><?php echo $L['are_you_sure_delete_pet'] ?? 'Are you sure you want to delete this pet?'; ?></p>
                <p><strong id="petName"></strong></p>
                <p class="text-danger"><?php echo $L['this_action_cannot_be_undone'] ?? 'This action cannot be undone.'; ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $L['cancel'] ?? 'Cancel'; ?></button>
                <a href="#" id="confirmDelete" class="btn btn-danger"><?php echo $L['delete'] ?? 'Delete'; ?></a>
            </div>
        </div>
    </div>
</div>

<script>
// Debounce function // Funkcija debounca
function debounce(func, wait) {
    let timeout;
    return function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, arguments), wait);
    };
}

// Function for performing live search // Funkcija za izvršavanje live pretrage
function performLiveSearch(searchValue) {
    fetch(`pets.php?search=${encodeURIComponent(searchValue)}&limit=<?php echo $limit; ?>&ajax=1&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.text();
        })
        .then(html => {
            console.log('Received HTML for search:', searchValue, 'length:', html.length);
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTable = doc.querySelector('.table-pets tbody');
            const newPagination = doc.querySelector('.pagination');
            console.log('newTable found:', !!newTable, 'newPagination found:', !!newPagination);
            if (newTable) document.querySelector('.table-pets tbody').innerHTML = newTable.innerHTML;
            if (newPagination) document.querySelector('.pagination').innerHTML = newPagination.innerHTML;
        })
        .catch(error => {
            console.error('Live search fetch error:', error);
        });
}

// Live search with debounce // Pretraga uživo sa debouncom
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', debounce(function() {
        const searchValue = this.value;
        performLiveSearch(searchValue);
    }, 150));
}

// Delete confirmation modal // Modal za potvrdu brisanja
function confirmDelete(petId, petName) {
    document.getElementById('petName').textContent = petName;
    document.getElementById('confirmDelete').href = 'delete-pet.php?id=' + petId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
    return false;
}
</script>

<?php include 'includes/footer.php'; // Include footer // Uključi podnožje ?>
