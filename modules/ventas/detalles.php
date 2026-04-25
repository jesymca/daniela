<?php
// modules/ventas/detalles.php - Detalles de venta

require_once '../../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: listar.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT v.*, c.nombre as cliente_nombre FROM ventas v LEFT JOIN clientes c ON v.cliente_id = c.id WHERE v.id = ?");
    $stmt->execute([$id]);
    $venta = $stmt->fetch();

    if (!$venta) {
        header('Location: listar.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT vd.*, p.nombre as producto_nombre FROM ventas_detalles vd JOIN productos p ON vd.producto_id = p.id WHERE vd.venta_id = ?");
    $stmt->execute([$id]);
    $detalles = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al consultar detalles de venta: " . $e->getMessage());
}

$isAjax = isset($_GET['ajax']);

if ($isAjax) {
    // Devolver solo el contenido del modal
    ?>
    <div class="row mb-4">
        <div class="col-md-6">
            <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?></p>
            <p><strong>Cliente:</strong> <?php echo htmlspecialchars($venta['cliente_nombre'] ?? 'Cliente no encontrado'); ?></p>
        </div>
        <div class="col-md-6">
            <p><strong>Total:</strong> $<?php echo number_format($venta['total'], 2); ?></p>
        </div>
    </div>

    <h4>Productos Vendidos</h4>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $detalle): ?>
                <tr>
                    <td><?php echo htmlspecialchars($detalle['producto_nombre']); ?></td>
                    <td><?php echo $detalle['cantidad']; ?></td>
                    <td>$<?php echo number_format($detalle['precio_unitario'], 2); ?></td>
                    <td>$<?php echo number_format($detalle['cantidad'] * $detalle['precio_unitario'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    exit;
}

include '../../header.php';
?>

<div class="container mt-4">
    <h2><i class="fas fa-receipt"></i> Detalles de Venta #<?php echo $venta['id']; ?></h2>
    <div class="row mb-4">
        <div class="col-md-6">
            <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?></p>
            <p><strong>Cliente:</strong> <?php echo htmlspecialchars($venta['cliente_nombre'] ?? 'Cliente no encontrado'); ?></p>
        </div>
        <div class="col-md-6">
            <p><strong>Total:</strong> $<?php echo number_format($venta['total'], 2); ?></p>
        </div>
    </div>

    <h4>Productos Vendidos</h4>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $detalle): ?>
                <tr>
                    <td><?php echo htmlspecialchars($detalle['producto_nombre']); ?></td>
                    <td><?php echo $detalle['cantidad']; ?></td>
                    <td>$<?php echo number_format($detalle['precio_unitario'], 2); ?></td>
                    <td>$<?php echo number_format($detalle['cantidad'] * $detalle['precio_unitario'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <a href="listar.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<?php include '../../footer.php'; ?>