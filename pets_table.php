<?php
// Get sorting parameters // Uzmi parametre za sortiranje
$sort = $_GET['sort'] ?? 'incoming_date';
$order = $_GET['order'] ?? 'desc';
// Additional security for $limit, $page and $search // Dodatno osiguranje za $limit, $page i $search
$limit = $_GET['limit'] ?? 10;
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
?>
<div class="table-responsive">
    <table class="table table-striped table-sortable table-hover table-pets order-column">
        <thead>
            <tr>
                <th class="text-center"><?php echo $L['image'] ?? 'Image'; // Image column // Kolona za sliku ?></th>
                <!-- Sortable columns // Kolone za sortiranje -->
                <!-- Each column header is a link that sorts by that column // Svaka kolona je link za sortiranje po toj koloni -->
                <!-- Icons indicate sort direction // Ikone pokazuju smer sortiranja -->
                <th class="text-center">
                    <a href="?sort=sex&order=<?php echo ($sort=='sex' && $order=='asc') ? 'desc' : 'asc'; ?>&limit=<?php echo $limit; ?>&page=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="text-decoration-none text-dark">
                        <?php echo $L['sex'] ?? 'Sex'; ?>
                        <?php if ($sort=='sex'): ?>
                            <i class="bi bi-caret-<?php echo $order=='asc' ? 'up' : 'down'; ?>-fill text-dark"></i>
                        <?php else: ?>
                            <i class="bi bi-caret-up-down" style="opacity: 0.4;"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th class="text-center">
                    <a href="?sort=microchip_number&order=<?php echo ($sort=='microchip_number' && $order=='asc') ? 'desc' : 'asc'; ?>&limit=<?php echo $limit; ?>&page=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="text-decoration-none text-dark">
                        <?php echo $L['microchip_number'] ?? 'Microchip #'; ?>
                        <?php if ($sort=='microchip_number'): ?>
                            <i class="bi bi-caret-<?php echo $order=='asc' ? 'up' : 'down'; ?>-fill text-dark"></i>
                        <?php else: ?>
                            <i class="bi bi-caret-up-down" style="opacity: 0.4;"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th class="text-center">
                    <a href="?sort=capture_location&order=<?php echo ($sort=='capture_location' && $order=='asc') ? 'desc' : 'asc'; ?>&limit=<?php echo $limit; ?>&page=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="text-decoration-none text-dark">
                        <?php echo $L['capture_location'] ?? 'Capture Location'; ?>
                        <?php if ($sort=='capture_location'): ?>
                            <i class="bi bi-caret-<?php echo $order=='asc' ? 'up' : 'down'; ?>-fill text-dark"></i>
                        <?php else: ?>
                            <i class="bi bi-caret-up-down" style="opacity: 0.4;"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th class="text-center">
                    <a href="?sort=incoming_date&order=<?php echo ($sort=='incoming_date' && $order=='asc') ? 'desc' : 'asc'; ?>&limit=<?php echo $limit; ?>&page=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="text-decoration-none text-dark">
                        <?php echo $L['incoming_date'] ?? 'Incoming Date'; ?>
                        <?php if ($sort=='incoming_date'): ?>
                            <i class="bi bi-caret-<?php echo $order=='asc' ? 'up' : 'down'; ?>-fill text-dark"></i>
                        <?php else: ?>
                            <i class="bi bi-caret-up-down" style="opacity: 0.4;"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th class="text-center">
                    <a href="?sort=in_shelter&order=<?php echo ($sort=='in_shelter' && $order=='asc') ? 'desc' : 'asc'; ?>&limit=<?php echo $limit; ?>&page=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="text-decoration-none text-dark">
                        <?php echo $L['presence_in_shelter'] ?? 'Presence in Shelter'; ?>
                        <?php if ($sort=='in_shelter'): ?>
                            <i class="bi bi-caret-<?php echo $order=='asc' ? 'up' : 'down'; ?>-fill text-dark"></i>
                        <?php else: ?>
                            <i class="bi bi-caret-up-down" style="opacity: 0.4;"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th class="text-center">
                    <a href="?sort=cage_number&order=<?php echo ($sort=='cage_number' && $order=='asc') ? 'desc' : 'asc'; ?>&limit=<?php echo $limit; ?>&page=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="text-decoration-none text-dark">
                        <?php echo $L['cage_number'] ?? 'Cage Number'; ?>
                        <?php if ($sort=='cage_number'): ?>
                            <i class="bi bi-caret-<?php echo $order=='asc' ? 'up' : 'down'; ?>-fill text-dark"></i>
                        <?php else: ?>
                            <i class="bi bi-caret-up-down" style="opacity: 0.4;"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th class="text-center"><?php echo $L['actions'] ?? 'Actions'; ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pets as $pet): ?>
                <tr>
                    <td class="text-center">
                        <?php if (!empty($pet['image_path'])): ?>
                            <!-- Display pet image safely // Prikaži sliku ljubimca bezbedno -->
                            <a href="<?php echo htmlspecialchars($pet['image_path'] ?? ''); ?>" target="_blank">
                                <img src="<?php echo htmlspecialchars($pet['image_path'] ?? ''); ?>" alt="<?php echo $L['image'] ?? 'Pet Image'; ?>" class="img-thumbnail" style="max-width: 60px; max-height: 60px;">
                            </a>
                        <?php else: ?>
                            <span class="text-muted"><?php echo $L['no_image_available'] ?? 'No image'; // No image label // Oznaka za nedostupnu sliku ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?php echo htmlspecialchars(ucfirst($L[$pet['sex']] ?? $pet['sex'] ?? '')); // Display sex safely // Prikaži pol bezbedno ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($pet['microchip_number'] ?? ''); // Display microchip safely // Prikaži mikročip bezbedno ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($pet['capture_location'] ?? ''); // Display location safely // Prikaži lokaciju bezbedno ?></td>
                    <td class="text-center">
                        <?php echo !empty($pet['incoming_date']) 
                            ? date('d.m.Y', strtotime($pet['incoming_date'])) // Format date // Formatiraj datum
                            : '<span class="text-muted">' . ($L['not_available'] ?? 'N/A') . '</span>'; // Not available label // Oznaka za nedostupno ?>
                    </td>
                    <td class="text-center">
                        <?php echo $pet['in_shelter'] ? '<span class="text-success">' . ($L['yes'] ?? 'Yes') . '</span>' : '<span class="text-danger">' . ($L['no'] ?? 'No') . '</span>'; // Display presence // Prikaži prisustvo ?>
                    </td>
                    <td class="text-center"><?php echo htmlspecialchars($pet['cage_number'] ?? ''); // Display cage safely // Prikaži broj boksa bezbedno ?></td>

                    <td class="text-center">
                        <div class="d-flex gap-1">
                            <a href="view-pet.php?id=<?php echo $pet['id']; ?>" class="btn btn-sm btn-info"><?php echo $L['view'] ?? 'View'; // View button // Dugme za prikaz ?></a>
                            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                                <a href="edit-pet.php?id=<?php echo $pet['id']; ?>" class="btn btn-sm btn-warning"><?php echo $L['edit'] ?? 'Edit'; // Edit button // Dugme za izmenu ?></a>
                            <?php endif; ?>
                            <?php if (($_SESSION['role'] === 'admin') || ($_SESSION['role'] === 'staff' && $_SESSION['user_id'] == $pet['created_by'])): ?>
                                <!-- Delete button with confirmation // Dugme za brisanje sa potvrdom -->
                                <a href="delete-pet.php?id=<?php echo $pet['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete(<?php echo $pet['id']; ?>, '<?php echo addslashes($pet['microchip_number'] ?? ''); ?>')">
                                    <?php echo $L['delete'] ?? 'Delete'; // Delete button // Dugme za brisanje ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Pagination // Paginacija -->
<nav aria-label="Pets pagination">
    <ul class="pagination justify-content-center mt-4">
        <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?limit=<?php echo $limit; ?>&page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>"><?php echo $L['previous'] ?? 'Previous'; // Previous button // Dugme za prethodnu ?></a>
            </li>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                <a class="page-link" href="?limit=<?php echo $limit; ?>&page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>"><?php echo $i; // Page number // Broj stranice ?></a>
            </li>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
            <li class="page-item">
                <a class="page-link" href="?limit=<?php echo $limit; ?>&page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>"><?php echo $L['next'] ?? 'Next'; // Next button // Dugme za sledeću ?></a>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<script>
    // JavaScript code for handling the search // JavaScript za pretragu
    document.getElementById('searchButton').addEventListener('click', function() {
        var searchValue = document.getElementById('searchInput').value;
        fetch(`pets.php?search=${encodeURIComponent(searchValue)}&limit=<?php echo $limit; ?>&ajax=1&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>`)
            .then(response => response.json())
            .then(data => {
                // Handle the response data to update the pets table // Obradi podatke za ažuriranje tabele
                console.log(data);
            });
    });
    
    // Confirmation dialog for delete // Dijalog za potvrdu brisanja
    function confirmDelete(petId, microchipNumber) {
        var identifier = microchipNumber ? microchipNumber : petId;
        var message = '<?php echo ($L['confirm_delete_pet'] ?? 'Are you sure you want to delete'); ?> ' + identifier + '?\n<?php echo ($L['this_action_cannot_be_undone'] ?? 'This action cannot be undone.'); ?>';
        if (confirm(message)) {
            window.location.href = 'delete-pet.php?id=' + petId;
            return true;
        }
        return false; // Prevent default link action when cancelled // Spreči podrazumevanu akciju linka ako je otkazano
    }
</script>