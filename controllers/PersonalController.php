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
        require_once __DIR__ . '/../views/personal/index.php';
    }
}