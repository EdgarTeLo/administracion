public function login() {
    if (isset($_SESSION['user_id'])) {
        header("Location: " . $_ENV['APP_URL'] . "/dashboard");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $this->userModel->findByUsername($username);
        if ($user === false) {
            $error = "Error al buscar el usuario. Por favor, intenta de nuevo.";
        } elseif ($user && password_verify($password, $user['pass'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['ID'];
            header("Location: " . $_ENV['APP_URL'] . "/dashboard");
            exit();
        } else {
            $error = "Usuario o contraseña incorrectos";
        }
    }

    require_once __DIR__ . '/../views/auth/login.php';
}