<?php
namespace App\Models;

use App\Config\Database;

class Personal {
    private $db;

    public function __construct() {
        $database = Database::getInstance('jesca01_jesca');
        $this->db = $database->getConnection();
    }

    public function getAllActive() {
        try {
            $query = "
                SELECT 
                    p.IDPERSONAL, 
                    p.NOMBRE, 
                    p.APELLIDOPATERNO, 
                    p.APELLIDOMATERNO, 
                    p.FECHANACIMIENTO, 
                    p.CURP, 
                    p.TELMOVIL, 
                    p.EMAIL, 
                    p.FECHAINGRESO, 
                    p.FECHADEBAJA, 
                    p.ESTADO, 
                    e.EMPRESA, 
                    esp.ESPECIALIDAD, 
                    al.AREALABORAL 
                FROM 
                    personal p
                LEFT JOIN 
                    empresa e ON p.EMPRESA = e.IDEMPRESA
                LEFT JOIN 
                    especialidad esp ON p.ESPECIALIDAD = esp.IDESPECIALIDAD
                LEFT JOIN 
                    arealaboral al ON p.AREALABORAL = al.IDAREALABORAL
                WHERE 
                    p.ESTADO = 1
                ORDER BY 
                    p.NOMBRE ASC
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Error al obtener personal: " . $e->getMessage());
            return [];
        }
    }
}