<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo $_ENV['APP_URL']; ?>/dashboard">Sistema de Administración</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $_ENV['APP_URL']; ?>/dashboard">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $_ENV['APP_URL']; ?>/facturas">Facturas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $_ENV['APP_URL']; ?>/facturas/crear">Crear Factura</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $_ENV['APP_URL']; ?>/facturas/ordenes">Órdenes de Compra</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $_ENV['APP_URL']; ?>/facturas/upload">Subir Factura/OC</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $_ENV['APP_URL']; ?>/personal">Personal</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $_ENV['APP_URL']; ?>/logout">Cerrar Sesión</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $_ENV['APP_URL']; ?>/login">Iniciar Sesión</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>