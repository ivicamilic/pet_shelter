<?php
require_once 'config.php';

class Database {
    private $conn;

    public function __construct() {
        // Create new MySQLi connection // Kreiraj novu MySQLi konekciju
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->conn->connect_error) {
            // Stop execution if connection fails // Zaustavi izvršavanje ako konekcija nije uspela
            die("Connection failed: " . $this->conn->connect_error);
        }
        // Set charset to UTF-8 for security and compatibility // Podesi UTF-8 kodnu stranicu radi sigurnosti i kompatibilnosti
        $this->conn->set_charset('utf8mb4');
    }

    public function getConnection() {
        // Return raw connection // Vrati sirovu konekciju
        return $this->conn;
    }

    public function query($sql, $params = []) {
        // Prepare SQL statement // Pripremi SQL upit
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            // Stop execution if statement preparation fails // Zaustavi izvršavanje ako priprema upita nije uspela
            die("SQL error: " . $this->conn->error);
        }
        
        if (!empty($params)) {
            $types = '';
            $values = [];
            // Determine parameter types // Odredi tipove parametara
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
                $values[] = $param;
            }
            // Bind parameters to statement // Veži parametre za upit
            $stmt->bind_param($types, ...$values);
        }
        
        $stmt->execute(); // Execute statement // Izvrši upit
        return $stmt;     // Return statement // Vrati upit
    }

    public function fetchAll($sql, $params = []) {
        // Fetch all rows as associative array // Uzmi sve redove kao asocijativni niz
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function fetchOne($sql, $params = []) {
        // Fetch single row as associative array // Uzmi jedan red kao asocijativni niz
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}

$db = new Database(); // Create global database object // Kreiraj globalni objekat baze
?>