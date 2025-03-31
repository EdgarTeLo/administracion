<?php
namespace App\Controllers;

use App\Models\Personal;

class PersonalController {
    private $personalModel;

    public function __construct() {
        $this->personalModel = new Personal();
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . $_ENV['APP_URL'] . "/login");
            exit();
        }

        $empleados = $this->personalModel->getAllActive();
        if (isset($empleados['error'])) {
            $error = $empleados['error'];
            $empleados = [];
        }

        if (isset($_GET['message'])) {
            $message = $_GET['message'];
            $success = true;
        }
        if (isset($_GET['error'])) {
            $error = $_GET['error'];
            $success = false;
        }

        require_once __DIR__ . '/../views/personal/index.php';
    }

    public function crear() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . $_ENV['APP_URL'] . "/login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $empleado = [
                'nombre' => $_POST['nombre'] ?? '',
                'apellido_paterno' => $_POST['apellido_paterno'] ?? '',
                'apellido_materno' => $_POST['apellido_materno'] ?? '',
                'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? '',
                'curp' => $_POST['curp'] ?? '',
                'tel_movil' => $_POST['tel_movil'] ?? '',
                'email' => $_POST['email'] ?? '',
                'fecha_ingreso' => $_POST['fecha_ingreso'] ?? '',
                'estado' => $_POST['estado'] ?? 1
            ];

            $result = $this->personalModel->crearEmpleado($empleado);
            if ($result === true) {
                $success = true;
                $message = "Empleado creado exitosamente.";
            } else {
                $success = false;
                $message = "Error al crear el empleado: " . $result;
            }
        }

        require_once __DIR__ . '/../views/personal/crear.php';
    }

    public function editar($id) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . $_ENV['APP_URL'] . "/login");
            exit();
        }

        $empleado = $this->personalModel->getById($id);
        if (isset($empleado['error'])) {
            $error = $empleado['error'];
            $empleado = [];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $empleadoData = [
                'nombre' => $_POST['nombre'] ?? '',
                'apellido_paterno' => $_POST['apellido_paterno'] ?? '',
                'apellido_materno' => $_POST['apellido_materno'] ?? '',
                'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? '',
                'curp' => $_POST['curp'] ?? '',
                'tel_movil' => $_POST['tel_movil'] ?? '',
                'email' => $_POST['email'] ?? '',
                'fecha_ingreso' => $_POST['fecha_ingreso'] ?? '',
                'fecha_baja' => $_POST['fecha_baja'] ?: null,
                'estado' => $_POST['estado'] ?? 1
            ];

            $result = $this->personalModel->editarEmpleado($id, $empleadoData);
            if ($result === true) {
                $success = true;
                $message = "Empleado actualizado exitosamente.";
            } else {
                $success = false;
                $message = "Error al actualizar el empleado: " . $result;
            }
        }

        require_once __DIR__ . '/../views/personal/editar.php';
    }

    public function eliminar($id) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . $_ENV['APP_URL'] . "/login");
            exit();
        }

        $result = $this->personalModel->eliminarEmpleado($id);
        if ($result === true) {
            header("Location: " . $_ENV['APP_URL'] . "/personal?message=Empleado eliminado exitosamente");
            exit();
        } else {
            header("Location: " . $_ENV['APP_URL'] . "/personal?error=" . urlencode($result));
            exit();
        }
    }
}