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
            return ['error' => "Error al obtener personal: " . $e->getMessage()];
        }
    }

    public function getById($id) {
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
                    p.IDPERSONAL = :id
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            return ['error' => "Error al obtener empleado: " . $e->getMessage()];
        }
    }

    public function crearEmpleado($empleado) {
        try {
            $query = "
                INSERT INTO personal (
                    NOMBRE, APELLIDOPATERNO, APELLIDOMATERNO, FECHANACIMIENTO, 
                    CURP, TELMOVIL, EMAIL, FECHAINGRESO, ESTADO
                ) VALUES (
                    :nombre, :apellido_paterno, :apellido_materno, :fecha_nacimiento, 
                    :curp, :tel_movil, :email, :fecha_ingreso, :estado
                )
            ";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':nombre' => $empleado['nombre'],
                ':apellido_paterno' => $empleado['apellido_paterno'],
                ':apellido_materno' => $empleado['apellido_materno'],
                ':fecha_nacimiento' => $empleado['fecha_nacimiento'],
                ':curp' => $empleado['curp'],
                ':tel_movil' => $empleado['tel_movil'],
                ':email' => $empleado['email'],
                ':fecha_ingreso' => $empleado['fecha_ingreso'],
                ':estado' => $empleado['estado']
            ]);
            if (!$result) {
                throw new \Exception("Fallo al ejecutar la consulta de inserción de empleado.");
            }
            return true;
        } catch (\PDOException $e) {
            return "Error al crear el empleado: " . $e->getMessage();
        }
    }

    public function editarEmpleado($id, $empleado) {
        try {
            $query = "
                UPDATE personal 
                SET 
                    NOMBRE = :nombre, 
                    APELLIDOPATERNO = :apellido_paterno, 
                    APELLIDOMATERNO = :apellido_materno, 
                    FECHANACIMIENTO = :fecha_nacimiento, 
                    CURP = :curp, 
                    TELMOVIL = :tel_movil, 
                    EMAIL = :email, 
                    FECHAINGRESO = :fecha_ingreso, 
                    FECHADEBAJA = :fecha_baja, 
                    ESTADO = :estado
                WHERE 
                    IDPERSONAL = :id
            ";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':nombre' => $empleado['nombre'],
                ':apellido_paterno' => $empleado['apellido_paterno'],
                ':apellido_materno' => $empleado['apellido_materno'],
                ':fecha_nacimiento' => $empleado['fecha_nacimiento'],
                ':curp' => $empleado['curp'],
                ':tel_movil' => $empleado['tel_movil'],
                ':email' => $empleado['email'],
                ':fecha_ingreso' => $empleado['fecha_ingreso'],
                ':fecha_baja' => $empleado['fecha_baja'],
                ':estado' => $empleado['estado'],
                ':id' => $id
            ]);
            if (!$result) {
                throw new \Exception("Fallo al ejecutar la consulta de actualización de empleado.");
            }
            return true;
        } catch (\PDOException $e) {
            return "Error al actualizar el empleado: " . $e->getMessage();
        }
    }

    public function eliminarEmpleado($id) {
        try {
            $query = "DELETE FROM personal WHERE IDPERSONAL = :id";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([':id' => $id]);
            if (!$result) {
                throw new \Exception("Fallo al ejecutar la consulta de eliminación de empleado.");
            }
            return true;
        } catch (\PDOException $e) {
            return "Error al eliminar el empleado: " . $e->getMessage();
        }
    }
}