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
                    p.TELCASA, 
                    p.TELMOVIL, 
                    p.DIRECCION, 
                    p.EMPRESA, 
                    p.PUESTO, 
                    p.AREALABORAL, 
                    p.ESPECIALIDAD, 
                    p.SUELDOIMSS, 
                    p.NUMIMSS, 
                    p.NUMCTABANAMEX, 
                    p.SUPERVISOR, 
                    p.FECHAINGRESO, 
                    p.FECHAINICIOFINCONTRATO, 
                    p.VENCIMIENTOCONTRATO, 
                    p.RENOVACIONCONTRATO, 
                    p.AVISOFINDECONTRATO, 
                    p.OBSERVACIONES, 
                    p.TIPOEMPLEADO, 
                    p.FECHADECONTRATODECONFIDENCIALIDAD, 
                    p.ESTADO, 
                    p.FECHADEBAJA, 
                    p.ESTADOCIVIL, 
                    p.MOTIVODEBAJA, 
                    p.EMAIL, 
                    p.SEXO, 
                    p.AYUDAPASAJESXDIA, 
                    p.DOCINE, 
                    p.DOCCURP, 
                    p.DOCRFC, 
                    p.DOCCOMPDOM, 
                    p.DOCACTNAC, 
                    p.DOCNSS, 
                    p.DOCCONTRATO, 
                    p.DOCCMC, 
                    p.DOCREGLAYAVISO, 
                    p.DOCPAGARE, 
                    p.RFCP, 
                    e.EMPRESA AS NOMBRE_EMPRESA, 
                    esp.ESPECIALIDAD AS NOMBRE_ESPECIALIDAD, 
                    al.AREALABORAL AS NOMBRE_AREALABORAL 
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
                    p.TELCASA, 
                    p.TELMOVIL, 
                    p.DIRECCION, 
                    p.EMPRESA, 
                    p.PUESTO, 
                    p.AREALABORAL, 
                    p.ESPECIALIDAD, 
                    p.SUELDOIMSS, 
                    p.NUMIMSS, 
                    p.NUMCTABANAMEX, 
                    p.SUPERVISOR, 
                    p.FECHAINGRESO, 
                    p.FECHAINICIOFINCONTRATO, 
                    p.VENCIMIENTOCONTRATO, 
                    p.RENOVACIONCONTRATO, 
                    p.AVISOFINDECONTRATO, 
                    p.OBSERVACIONES, 
                    p.TIPOEMPLEADO, 
                    p.FECHADECONTRATODECONFIDENCIALIDAD, 
                    p.ESTADO, 
                    p.FECHADEBAJA, 
                    p.ESTADOCIVIL, 
                    p.MOTIVODEBAJA, 
                    p.EMAIL, 
                    p.SEXO, 
                    p.AYUDAPASAJESXDIA, 
                    p.DOCINE, 
                    p.DOCCURP, 
                    p.DOCRFC, 
                    p.DOCCOMPDOM, 
                    p.DOCACTNAC, 
                    p.DOCNSS, 
                    p.DOCCONTRATO, 
                    p.DOCCMC, 
                    p.DOCREGLAYAVISO, 
                    p.DOCPAGARE, 
                    p.RFCP, 
                    e.EMPRESA AS NOMBRE_EMPRESA, 
                    esp.ESPECIALIDAD AS NOMBRE_ESPECIALIDAD, 
                    al.AREALABORAL AS NOMBRE_AREALABORAL 
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
                    CURP, TELCASA, TELMOVIL, DIRECCION, EMPRESA, PUESTO, 
                    AREALABORAL, ESPECIALIDAD, SUELDOIMSS, NUMIMSS, NUMCTABANAMEX, 
                    SUPERVISOR, FECHAINGRESO, FECHAINICIOFINCONTRATO, VENCIMIENTOCONTRATO, 
                    RENOVACIONCONTRATO, AVISOFINDECONTRATO, OBSERVACIONES, TIPOEMPLEADO, 
                    FECHADECONTRATODECONFIDENCIALIDAD, ESTADO, FECHADEBAJA, ESTADOCIVIL, 
                    MOTIVODEBAJA, EMAIL, SEXO, AYUDAPASAJESXDIA, DOCINE, DOCCURP, DOCRFC, 
                    DOCCOMPDOM, DOCACTNAC, DOCNSS, DOCCONTRATO, DOCCMC, DOCREGLAYAVISO, 
                    DOCPAGARE, RFCP
                ) VALUES (
                    :nombre, :apellido_paterno, :apellido_materno, :fecha_nacimiento, 
                    :curp, :tel_casa, :tel_movil, :direccion, :empresa, :puesto, 
                    :arealaboral, :especialidad, :sueldo_imss, :num_imss, :num_cta_banamex, 
                    :supervisor, :fecha_ingreso, :fecha_inicio_fin_contrato, :vencimiento_contrato, 
                    :renovacion_contrato, :aviso_fin_contrato, :observaciones, :tipo_empleado, 
                    :fecha_contrato_confidencialidad, :estado, :fecha_baja, :estado_civil, 
                    :motivo_baja, :email, :sexo, :ayuda_pasajes_x_dia, :doc_ine, :doc_curp, :doc_rfc, 
                    :doc_comp_dom, :doc_act_nac, :doc_nss, :doc_contrato, :doc_cmc, :doc_reglamento_aviso, 
                    :doc_pagare, :rfcp
                )
            ";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':nombre' => $empleado['nombre'],
                ':apellido_paterno' => $empleado['apellido_paterno'],
                ':apellido_materno' => $empleado['apellido_materno'],
                ':fecha_nacimiento' => $empleado['fecha_nacimiento'],
                ':curp' => $empleado['curp'],
                ':tel_casa' => $empleado['tel_casa'],
                ':tel_movil' => $empleado['tel_movil'],
                ':direccion' => $empleado['direccion'],
                ':empresa' => $empleado['empresa'],
                ':puesto' => $empleado['puesto'],
                ':arealaboral' => $empleado['arealaboral'],
                ':especialidad' => $empleado['especialidad'],
                ':sueldo_imss' => $empleado['sueldo_imss'],
                ':num_imss' => $empleado['num_imss'],
                ':num_cta_banamex' => $empleado['num_cta_banamex'],
                ':supervisor' => $empleado['supervisor'],
                ':fecha_ingreso' => $empleado['fecha_ingreso'],
                ':fecha_inicio_fin_contrato' => $empleado['fecha_inicio_fin_contrato'],
                ':vencimiento_contrato' => $empleado['vencimiento_contrato'],
                ':renovacion_contrato' => $empleado['renovacion_contrato'],
                ':aviso_fin_contrato' => $empleado['aviso_fin_contrato'],
                ':observaciones' => $empleado['observaciones'],
                ':tipo_empleado' => $empleado['tipo_empleado'],
                ':fecha_contrato_confidencialidad' => $empleado['fecha_contrato_confidencialidad'],
                ':estado' => $empleado['estado'],
                ':fecha_baja' => $empleado['fecha_baja'],
                ':estado_civil' => $empleado['estado_civil'],
                ':motivo_baja' => $empleado['motivo_baja'],
                ':email' => $empleado['email'],
                ':sexo' => $empleado['sexo'],
                ':ayuda_pasajes_x_dia' => $empleado['ayuda_pasajes_x_dia'],
                ':doc_ine' => $empleado['doc_ine'],
                ':doc_curp' => $empleado['doc_curp'],
                ':doc_rfc' => $empleado['doc_rfc'],
                ':doc_comp_dom' => $empleado['doc_comp_dom'],
                ':doc_act_nac' => $empleado['doc_act_nac'],
                ':doc_nss' => $empleado['doc_nss'],
                ':doc_contrato' => $empleado['doc_contrato'],
                ':doc_cmc' => $empleado['doc_cmc'],
                ':doc_reglamento_aviso' => $empleado['doc_reglamento_aviso'],
                ':doc_pagare' => $empleado['doc_pagare'],
                ':rfcp' => $empleado['rfcp']
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
                    TELCASA = :tel_casa, 
                    TELMOVIL = :tel_movil, 
                    DIRECCION = :direccion, 
                    EMPRESA = :empresa, 
                    PUESTO = :puesto, 
                    AREALABORAL = :arealaboral, 
                    ESPECIALIDAD = :especialidad, 
                    SUELDOIMSS = :sueldo_imss, 
                    NUMIMSS = :num_imss, 
                    NUMCTABANAMEX = :num_cta_banamex, 
                    SUPERVISOR = :supervisor, 
                    FECHAINGRESO = :fecha_ingreso, 
                    FECHAINICIOFINCONTRATO = :fecha_inicio_fin_contrato, 
                    VENCIMIENTOCONTRATO = :vencimiento_contrato, 
                    RENOVACIONCONTRATO = :renovacion_contrato, 
                    AVISOFINDECONTRATO = :aviso_fin_contrato, 
                    OBSERVACIONES = :observaciones, 
                    TIPOEMPLEADO = :tipo_empleado, 
                    FECHADECONTRATODECONFIDENCIALIDAD = :fecha_contrato_confidencialidad, 
                    ESTADO = :estado, 
                    FECHADEBAJA = :fecha_baja, 
                    ESTADOCIVIL = :estado_civil, 
                    MOTIVODEBAJA = :motivo_baja, 
                    EMAIL = :email, 
                    SEXO = :sexo, 
                    AYUDAPASAJESXDIA = :ayuda_pasajes_x_dia, 
                    DOCINE = :doc_ine, 
                    DOCCURP = :doc_curp, 
                    DOCRFC = :doc_rfc, 
                    DOCCOMPDOM = :doc_comp_dom, 
                    DOCACTNAC = :doc_act_nac, 
                    DOCNSS = :doc_nss, 
                    DOCCONTRATO = :doc_contrato, 
                    DOCCMC = :doc_cmc, 
                    DOCREGLAYAVISO = :doc_reglamento_aviso, 
                    DOCPAGARE = :doc_pagare, 
                    RFCP = :rfcp
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
                ':tel_casa' => $empleado['tel_casa'],
                ':tel_movil' => $empleado['tel_movil'],
                ':direccion' => $empleado['direccion'],
                ':empresa' => $empleado['empresa'],
                ':puesto' => $empleado['puesto'],
                ':arealaboral' => $empleado['arealaboral'],
                ':especialidad' => $empleado['especialidad'],
                ':sueldo_imss' => $empleado['sueldo_imss'],
                ':num_imss' => $empleado['num_imss'],
                ':num_cta_banamex' => $empleado['num_cta_banamex'],
                ':supervisor' => $empleado['supervisor'],
                ':fecha_ingreso' => $empleado['fecha_ingreso'],
                ':fecha_inicio_fin_contrato' => $empleado['fecha_inicio_fin_contrato'],
                ':vencimiento_contrato' => $empleado['vencimiento_contrato'],
                ':renovacion_contrato' => $empleado['renovacion_contrato'],
                ':aviso_fin_contrato' => $empleado['aviso_fin_contrato'],
                ':observaciones' => $empleado['observaciones'],
                ':tipo_empleado' => $empleado['tipo_empleado'],
                ':fecha_contrato_confidencialidad' => $empleado['fecha_contrato_confidencialidad'],
                ':estado' => $empleado['estado'],
                ':fecha_baja' => $empleado['fecha_baja'],
                ':estado_civil' => $empleado['estado_civil'],
                ':motivo_baja' => $empleado['motivo_baja'],
                ':email' => $empleado['email'],
                ':sexo' => $empleado['sexo'],
                ':ayuda_pasajes_x_dia' => $empleado['ayuda_pasajes_x_dia'],
                ':doc_ine' => $empleado['doc_ine'],
                ':doc_curp' => $empleado['doc_curp'],
                ':doc_rfc' => $empleado['doc_rfc'],
                ':doc_comp_dom' => $empleado['doc_comp_dom'],
                ':doc_act_nac' => $empleado['doc_act_nac'],
                ':doc_nss' => $empleado['doc_nss'],
                ':doc_contrato' => $empleado['doc_contrato'],
                ':doc_cmc' => $empleado['doc_cmc'],
                ':doc_reglamento_aviso' => $empleado['doc_reglamento_aviso'],
                ':doc_pagare' => $empleado['doc_pagare'],
                ':rfcp' => $empleado['rfcp'],
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