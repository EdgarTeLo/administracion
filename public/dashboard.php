<?php
include 'check_session.php';
checkSession();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <h2>Bienvenido, <?php echo $_SESSION['usuario']; ?></h2>
    <p>Nivel de acceso: <?php echo $_SESSION['nivel_acceso']; ?></p>
    <a href="logout.php">Cerrar Sesión</a>
    <a href="personal/index.php">Personal</a>
</body>
</html>