<?php
// index.php - Versión 1.0.1
require_once 'config/database.php';
require_once 'includes/functions.php';

// Configuración de paginación
$perPageOptions = [5, 20, 50, 100];
$perPage = isset($_GET['perPage']) && in_array($_GET['perPage'], $perPageOptions) ? (int)$_GET['perPage'] : 20;
$page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'fact'; // Por defecto ordenar por 'fact'
$order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC'; // Por defecto DESC, alternar a ASC
$search = isset($_GET['search']) ? $_GET['search'] : '';

$invoices = getInvoiceList($perPage, $offset, $sort, $order, $search);
$totalInvoices = getTotalInvoices($search);
$totalPages = ceil($totalInvoices / $perPage);

if (defined('DEBUG') && DEBUG) {
    debugLog("Cargando lista de facturas - Página: $page, Por página: $perPage, Orden: $sort $order, Búsqueda: " . (!empty($search) ? $search : 'Ninguna'));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de CFDI</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-responsive { margin-top: 20px; }
        .search-form { margin-bottom: 20px; }
        .pagination { margin-top: 20px; }
        th { cursor: pointer; }
        th:hover { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mt-4">Facturas</h1>
        
        <!-- Filtros y Buscador -->
        <div class="row search-form">
            <div class="col-md-6">
                <form method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" value="<?php echo htmlspecialchars($search); ?>" placeholder="Buscar por cualquier campo">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <select name="perPage" onchange="window.location.href='?page=1&perPage=' + this.value + '<?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $sort ? '&sort=' . $sort . '&order=' . $order : ''; ?>'" class="form-select d-inline-block w-auto">
                    <?php foreach ($perPageOptions as $option): ?>
                        <option value="<?php echo $option; ?>" <?php echo $perPage === $option ? 'selected' : ''; ?>><?php echo $option; ?> por página</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <?php
                        $columns = [
                            'fact' => 'FACT', // Cambiado de 'folio' a 'fact' para coincidir con la base de datos
                            'uuid' => 'Folio Fiscal',
                            'issue_date' => 'Fecha',
                            'receiver_name' => 'Razón Social',
                            'total' => 'Total',
                            'status' => 'Estado',
                            'purchase_order' => 'Orden de Compra',
                            'quotation' => 'Cotización o Presupuesto',
                            'descripcion' => 'Descripción',
                            'work_location' => 'Obra',
                            'subtotal' => 'Subtotal',
                            'vat' => 'IVA',
                            'payment_date' => 'Fecha de Pago',
                            'complement_number' => 'No. de Complemento',
                            'cancellation_date' => 'Fecha de Cancelación'
                        ];
                        foreach ($columns as $key => $label) {
                            $sortOrder = $sort === $key ? ($order === 'DESC' ? 'asc' : 'desc') : 'desc'; // Por defecto DESC, alternar a ASC
                            echo "<th><a href='?page=$page&perPage=$perPage&sort=$key&order=$sortOrder" . ($search ? "&search=" . urlencode($search) : '') . "'>$label</a>" . ($sort === $key ? ($order === 'DESC' ? ' ↓' : ' ↑') : '') . "</th>";
                        }
                        ?>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($invoice['folio'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($invoice['uuid'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($invoice['issue_date'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($invoice['receiver_name'] ?? '-'); ?></td>
                        <td><?php echo number_format($invoice['total'] ?? 0, 2); ?></td>
                        <td><?php echo htmlspecialchars($invoice['status'] ?? 'activa'); ?></td>
                        <td><?php echo htmlspecialchars($invoice['purchase_order'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($invoice['quotation'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($invoice['descripcion'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($invoice['work_location'] ?? '-'); ?></td>
                        <td><?php echo number_format($invoice['subtotal'] ?? 0, 2); ?></td>
                        <td><?php echo number_format($invoice['vat'] ?? 0, 2); ?></td>
                        <td><?php echo htmlspecialchars($invoice['payment_date'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($invoice['complement_number'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($invoice['cancellation_date'] ?? '-'); ?></td>
                        <td>
                            <a href="view_invoice.php?id=<?php echo $invoice['id']; ?>" class="btn btn-info btn-sm">Ver</a>
                            <a href="edit_purchase_order.php?id=<?php echo $invoice['id']; ?>" class="btn btn-warning btn-sm ms-1">Editar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginador -->
        <nav class="pagination">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&perPage=<?php echo $perPage; ?><?php echo $sort ? '&sort=' . $sort . '&order=' . $order : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Anterior</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item<?php echo $i === $page ? ' active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&perPage=<?php echo $perPage; ?><?php echo $sort ? '&sort=' . $sort . '&order=' . $order : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&perPage=<?php echo $perPage; ?><?php echo $sort ? '&sort=' . $sort . '&order=' . $order : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Siguiente</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

        <a href="upload_massive.php" class="btn btn-primary mt-3">Carga Masiva</a>
        <a href="update_status.php" class="btn btn-secondary mt-3 ms-2">Actualizar Estados</a>
        <a href="edit_purchase_order.php" class="btn btn-secondary mt-3 ms-2">Editar Órdenes de Compra</a>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <?php if (defined('DEBUG') && DEBUG): ?>
        <script>
            console.log("Depuración activa (DEBUG=true). Revisar logs en C:\\xampp\\php\\logs\\php_error.log.");
        </script>
    <?php endif; ?>
</body>
</html>