<?php
session_start();

// Función de verificación de sesión
function checkSession() {
    $inactive_time = 1200; // 20 minutos en segundos
    
    if (!isset($_SESSION['usuario']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
        header("Location: /administracion/login.php");
        exit();
    }
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive_time) && basename($_SERVER['PHP_SELF']) != 'login.php') {
        session_unset();
        session_destroy();
        header("Location: /administracion/login.php?timeout=1");
        exit();
    }
    
    if (isset($_SESSION['usuario'])) {
        $_SESSION['last_activity'] = time();
    }
}

// Verificar la sesión excepto en login.php
checkSession();

// Base URL relativa al raíz para los enlaces
$base_url = '/administracion/';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Administración de Personal'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>styles.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo $base_url; ?>personal/read.php">Admin Personal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php if (isset($_SESSION['usuario'])): ?>
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>personal/read.php">Lista</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>personal/create.php">Agregar</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <span class="nav-link text-light">Bienvenido, <?php echo $_SESSION['usuario']; ?> (Nivel: <?php echo $_SESSION['nivel_acceso']; ?>)</span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>logout.php">Cerrar Sesión</a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <div class="container mt-4">
        <?php if (isset($_GET['timeout']) && $_GET['timeout'] == 1): ?>
            <div class="alert alert-warning" role="alert">
                La sesión ha expirado por inactividad. Por favor, inicia sesión nuevamente.
            </div>
        <?php endif; ?>