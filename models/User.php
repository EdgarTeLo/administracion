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
        try {
            $stmt = $this->db->prepare("SELECT ID, usuario, pass FROM jescadb_usuarios WHERE usuario = ? AND ACTIVO = 1");
            $stmt->execute([$username]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            error_log("Error al buscar usuario: " . $e->getMessage());
            return false;
        }
    }
}