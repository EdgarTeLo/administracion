<?php
session_start();

function checkSession() {
    // Tiempo de inactividad permitido (20 minutos = 1200 segundos)
    $inactive_time = 1200;
    
    if(!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }
    
    // Verificar tiempo de inactividad
    if(isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive_time)) {
        session_unset();
        session_destroy();
        header("Location: login.php?timeout=1");
        exit();
    }
    
    // Actualizar tiempo de última actividad
    $_SESSION['last_activity'] = time();
}
?>