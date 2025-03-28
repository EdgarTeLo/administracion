<header>
    <nav>
        <ul>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="<?php echo $_ENV['APP_URL']; ?>/dashboard">Inicio</a></li>
                <li><a href="<?php echo $_ENV['APP_URL']; ?>/facturas">Facturas</a></li>
                <li><a href="<?php echo $_ENV['APP_URL']; ?>/personal">Personal</a></li>
                <li><a href="<?php echo $_ENV['APP_URL']; ?>/logout">Cerrar Sesión</a></li>
            <?php else: ?>
                <li><a href="<?php echo $_ENV['APP_URL']; ?>/login">Iniciar Sesión</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>