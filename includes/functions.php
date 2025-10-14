<?php
require_once 'db.php';

// Ensure global $db is available // Osiguraj da je globalni $db dostupan
global $db;

function getAllPets($limit = 10) {
    global $db;
    // Fetch all pets with limit // Uzmi sve ljubimce sa limitom
    return $db->fetchAll("SELECT * FROM pets ORDER BY id DESC LIMIT ?", [$limit]);
}

function getPetById($id) {
    global $db;
    // Fetch pet by ID and join with user // Uzmi ljubimca po ID i spoji sa korisnikom
    $pet = $db->fetchOne("SELECT p.*, u.username as created_by_name 
                          FROM pets p 
                          JOIN user u ON p.created_by = u.id 
                          WHERE p.id = ?", [$id]);
    
    if ($pet) {
        // Fetch related records for pet // Uzmi povezane zapise za ljubimca
        $pet['vaccinations'] = $db->fetchAll("SELECT * FROM vaccinations WHERE pet_id = ?", [$id]);
        $pet['treatments'] = $db->fetchAll("SELECT * FROM treatments WHERE pet_id = ?", [$id]);
        $pet['health_checks'] = $db->fetchAll("SELECT * FROM health_checks WHERE pet_id = ? ORDER BY check_date DESC", [$id]);
    }
    
    return $pet;
}

function getRecentActivity($limit = 5) {
    global $db;
    // Fetch recent activity (pets and vaccinations) // Uzmi nedavne aktivnosti (ljubimci i vakcinacije)
    return $db->fetchAll("
        SELECT 'pet' as type, p.id, p.name as title, p.breed, p.microchip_number, p.created_at, u.username as created_by
        FROM pets p
        JOIN user u ON p.created_by = u.id
        UNION
        SELECT 'vaccination' as type, v.id, CONCAT('Vaccination for pet #', v.pet_id) as title, p.breed, p.microchip_number, v.created_at, u.username as created_by
        FROM vaccinations v
        JOIN pets p ON v.pet_id = p.id
        JOIN user u ON p.created_by = u.id
        ORDER BY created_at DESC
        LIMIT ?
    ", [$limit]);
}

function getPetStats() {
    global $db;
    // Fetch pet statistics by status // Uzmi statistiku ljubimaca po statusu
    return $db->fetchAll("SELECT status, COUNT(*) as count FROM pets GROUP BY status");
}
?>