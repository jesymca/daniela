<?php
// index.php - Dashboard principal

require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Consultas para estadísticas del dashboard
try {
    // Total productos en inventario
    $stmt = $pdo->query("SELECT COUNT(*) as total_productos FROM productos");
    $total_productos = $stmt->fetch()['total_productos'];

    // Total clientes
    $stmt = $pdo->query("SELECT COUNT(*) as total_clientes FROM clientes");
    $total_clientes = $stmt->fetch()['total_clientes'];

    // Total proveedores
    $stmt = $pdo->query("SELECT COUNT(*) as total_proveedores FROM proveedores");
    $total_proveedores = $stmt->fetch()['total_proveedores'];

    // Ventas del mes actual
    $stmt = $pdo->query("SELECT COUNT(*) as ventas_mes FROM ventas WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE())");
    $ventas_mes = $stmt->fetch()['ventas_mes'];

    // Compras del mes actual
    $stmt = $pdo->query("SELECT COUNT(*) as compras_mes FROM compras WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE())");
    $compras_mes = $stmt->fetch()['compras_mes'];

} catch (PDOException $e) {
    die("Error al consultar la base de datos: " . $e->getMessage());
}

include 'header.php';

$userRol = strtolower($_SESSION['user_rol'] ?? 'usuario');
$roleBadgeHeader = '<span class="badge bg-secondary"><i class="fas fa-user"></i> Usuario</span>';

if ($userRol === 'admin') {
    $roleBadgeHeader = '<span class="badge bg-danger fs-6 px-3 py-2"><i class="fas fa-user-shield me-1"></i> Panel de Control - Administrador</span>';
} elseif ($userRol === 'vendedor') {
    $roleBadgeHeader = '<span class="badge bg-success fs-6 px-3 py-2"><i class="fas fa-user-tag me-1"></i> Panel de Control - Vendedor</span>';
} elseif ($userRol === 'almacenista') {
    $roleBadgeHeader = '<span class="badge bg-info text-dark fs-6 px-3 py-2"><i class="fas fa-boxes me-1"></i> Panel de Control - Almacenista</span>';
}
?>

<div class="container-fluid mt-4 mb-5">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h1 class="fw-bold mb-2"><i class="fas fa-tachometer-alt me-2 text-primary"></i> Dashboard - Michele C.A.</h1>
            <div><?php echo $roleBadgeHeader; ?></div>
        </div>
    </div>

    <!-- Tarjetas de estadísticas adaptadas al rol -->
    <div class="row mb-4">
        <?php if (in_array($userRol, ['admin', 'almacenista', 'vendedor', 'usuario'])): ?>
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-boxes me-1"></i> Inventario</h5>
                        <p class="card-text display-4 mb-0 fw-bold"><?php echo $total_productos; ?></p>
                        <small>Productos registrados</small>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (in_array($userRol, ['admin', 'vendedor', 'usuario'])): ?>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-users me-1"></i> Clientes</h5>
                        <p class="card-text display-4 mb-0 fw-bold"><?php echo $total_clientes; ?></p>
                        <small>Clientes activos</small>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (in_array($userRol, ['admin', 'almacenista'])): ?>
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-building me-1"></i> Proveedores</h5>
                        <p class="card-text display-4 mb-0 fw-bold"><?php echo $total_proveedores; ?></p>
                        <small>Proveedores registrados</small>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-chart-line me-1"></i> Actividad Mensual</h5>
                    <p class="card-text mb-1"><strong>Ventas del Mes:</strong> <?php echo $ventas_mes; ?></p>
                    <p class="card-text mb-0"><strong>Compras del Mes:</strong> <?php echo $compras_mes; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Accesos rápidos filtrados por rol -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2 text-warning"></i> Accesos Rápidos por Rol</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <?php if (in_array($userRol, ['admin', 'vendedor', 'usuario'])): ?>
                            <div class="col-6">
                                <a href="modules/ventas/listar.php" class="btn btn-success btn-lg w-100 py-3 shadow-sm">
                                    <i class="fas fa-cash-register d-block fs-3 mb-1"></i> Ventas
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array($userRol, ['admin', 'almacenista'])): ?>
                            <div class="col-6">
                                <a href="modules/compras/listar.php" class="btn btn-warning text-white btn-lg w-100 py-3 shadow-sm">
                                    <i class="fas fa-cart-plus d-block fs-3 mb-1"></i> Compras
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array($userRol, ['admin', 'almacenista', 'vendedor', 'usuario'])): ?>
                            <div class="col-6">
                                <a href="modules/inventario/listar.php" class="btn btn-info text-white btn-lg w-100 py-3 shadow-sm">
                                    <i class="fas fa-boxes d-block fs-3 mb-1"></i> Productos
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if ($userRol === 'admin'): ?>
                            <div class="col-6">
                                <a href="modules/reportes/index.php" class="btn btn-primary btn-lg w-100 py-3 shadow-sm">
                                    <i class="fas fa-chart-bar d-block fs-3 mb-1"></i> Reportes
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-secondary text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Información del Sistema</h5>
                </div>
                <div class="card-body p-4">
                    <p class="mb-2"><strong><i class="fas fa-code-branch me-1 text-primary"></i> Versión del Sistema:</strong> 2.0.0 (RBAC Multi-Rol)</p>
                    <p class="mb-2"><strong><i class="fas fa-calendar-alt me-1 text-primary"></i> Actualización:</strong> Septiembre 2026 (UPTPC)</p>
                    <p class="mb-2"><strong><i class="fas fa-database me-1 text-warning"></i> Motor de Base de Datos:</strong> MySQL / MariaDB (PDO)</p>
                    <p class="mb-0"><strong><i class="fab fa-bootstrap me-1 text-purple" style="color:#6f42c1;"></i> Framework Visual:</strong> Bootstrap 5 Responsive</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>