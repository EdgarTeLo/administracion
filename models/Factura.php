<?php
namespace App\Models;

use App\Config\Database;

class Factura {
    private $db;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    public function getAll() {
        try {
            $query = "
                SELECT 
                    id, 
                    ccFecha AS fecha, 
                    ccFolio AS folio, 
                    ccSubTotal AS subtotal, 
                    ccTotal AS total, 
                    ceNombre AS emisor, 
                    crNombre AS receptor, 
                    tfdUUID AS uuid
                FROM 
                    facturas
                ORDER BY 
                    ccFecha DESC
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Error al obtener facturas: " . $e->getMessage());
            return [];
        }
    }
}