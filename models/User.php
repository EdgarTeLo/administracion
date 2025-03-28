<?php
namespace App\Models;

use App\Config\Database;

class User {
    private $db;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
}