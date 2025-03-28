<?php
require_once 'header.php';
$pageTitle = "Login";

include 'config/conexion.php';
$db = new Database();
$conn = $db->getConnection();

if (isset($_SESSION['usuario'])) {
    header("Location: personal/dashboard.php"); // O read.php si no usas dashboard
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = trim($_POST['usuario']);
    $pass = trim($_POST['pass']);
    
    if (!empty($usuario) && !empty($pass)) {
        $stmt = $conn->prepare("SELECT usuario, pass, nivel_acceso FROM jescadb_usuarios WHERE usuario = :usuario AND ACTIVO = 1");
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($pass, $user['pass'])) {
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['nivel_acceso'] = $user['nivel_acceso'];
            $_SESSION['last_activity'] = time();
            header("Location: personal/read.php");
            exit();
        } else {
            $error = "Usuario o contraseña incorrectos";
        }
    } else {
        $error = "Por favor completa todos los campos";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h1 class="text-center">Iniciar Sesión</h1>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario:</label>
                <input type="text" class="form-control" id="usuario" name="usuario" required>
            </div>
            <div class="mb-3">
                <label for="pass" class="form-label">Contraseña:</label>
                <input type="password" class="form-control" id="pass" name="pass" required>
            </div>
            <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>