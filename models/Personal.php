<?php
namespace App\Models;

use App\Config\Database;

class Personal {
    private $db;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    public function getAllActive() {
        try {
            $query = "
                SELECT 
                    e.ID, 
                    e.NOMBRE, 
                    e.APELLIDOPATERNO, 
                    e.APELLIDOMATERNO, 
                    e.CURP, 
                    e.RFC, 
                    e.TELMOVIL, 
                    e.EMAIL, 
                    e.FECHAINGRESO, 
                    e.FECHABAJA, 
                    e.ACTIVO, 
                    c.COMPANIA AS EMPRESA, 
                    esp.ESPECIALIDAD, 
                    at.AREADETRABAJO 
                FROM 
                    jescadb_empleados e
                LEFT JOIN 
                    jescadb_compania c ON e.EMPRESA = c.ID
                LEFT JOIN 
                    jescadb_especialidades esp ON e.ESPECIALIDAD = esp.ID
                LEFT JOIN 
                    jescadb_areatrabajo at ON e.AREADETRABAJO = at.ID
                WHERE 
                    e.ACTIVO = 1
                ORDER BY 
                    e.NOMBRE ASC
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Error al obtener empleados: " . $e->getMessage());
            return [];
        }
    }
}