<?php
namespace App\Controllers;

use App\Models\User;

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login() {
        if (isset($_SESSION['user_id'])) {
            header("Location: " . $_ENV['APP_URL'] . "/dashboard");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->findByUsername($username);
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                header("Location: " . $_ENV['APP_URL'] . "/dashboard");
                exit();
            } else {
                $error = "Usuario o contraseña incorrectos";
            }
        }

        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function logout() {
        session_destroy();
        header("Location: " . $_ENV['APP_URL'] . "/login");
        exit();
    }

    public function dashboard() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . $_ENV['APP_URL'] . "/login");
            exit();
        }

        require_once __DIR__ . '/../views/dashboard.php';
    }
}