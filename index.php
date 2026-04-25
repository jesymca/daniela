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
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="text-center text-primary mb-4"><i class="fas fa-tachometer-alt"></i> Dashboard - Michele C.A.</h1>
        </div>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-boxes"></i> Inventario</h5>
                    <p class="card-text display-4"><?php echo $total_productos; ?></p>
                    <p>Productos registrados</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-users"></i> Clientes</h5>
                    <p class="card-text display-4"><?php echo $total_clientes; ?></p>
                    <p>Clientes activos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-building"></i> Proveedores</h5>
                    <p class="card-text display-4"><?php echo $total_proveedores; ?></p>
                    <p>Proveedores registrados</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-chart-line"></i> Actividad Mensual</h5>
                    <p class="card-text">Ventas: <?php echo $ventas_mes; ?></p>
                    <p>Compras: <?php echo $compras_mes; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Accesos rápidos -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fas fa-bolt"></i> Accesos Rápidos</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <a href="modules/ventas/listar.php" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-plus"></i> Nueva Venta
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="modules/compras/listar.php" class="btn btn-warning btn-lg w-100">
                                <i class="fas fa-cart-plus"></i> Nueva Compra
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="modules/inventario/agregar.php" class="btn btn-info btn-lg w-100">
                                <i class="fas fa-box"></i> Agregar Producto
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="modules/reportes/index.php" class="btn btn-secondary btn-lg w-100">
                                <i class="fas fa-chart-bar"></i> Ver Reportes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5><i class="fas fa-info-circle"></i> Información del Sistema</h5>
                </div>
                <div class="card-body">
                    <p><strong>Versión del Sistema:</strong> 1.0.0</p>
                    <p><strong>Última Actualización:</strong> Abril 2024</p>
                    <p><strong>Base de Datos:</strong> MySQL</p>
                    <p><strong>Framework Frontend:</strong> Bootstrap 5</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>