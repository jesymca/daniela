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
    $stmt = $pdo->prepare("SELECT v.*, c.nombre as cliente_nombre, c.rif_cedula FROM ventas v LEFT JOIN clientes c ON v.cliente_id = c.id WHERE v.id = ?");
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
            <p><strong>RIF/Cédula:</strong> <?php echo htmlspecialchars($venta['rif_cedula'] ?? 'N/A'); ?></p>
        </div>
        <div class="col-md-6">
            <p><strong>Subtotal:</strong> $<?php echo number_format($venta['subtotal'], 2); ?></p>
            <p><strong>IVA (16%):</strong> $<?php echo number_format($venta['iva'], 2); ?></p>
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

    <div class="text-center mt-4">
        <a href="<?php echo BASE_PATH; ?>/detalles.php?id=<?php echo $venta['id']; ?>" target="_blank" class="btn btn-primary"><i class="fas fa-external-link-alt"></i> Ver e Imprimir</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
    </div>
    <?php
    exit;
}

include '../../header.php';
?>

<style media="print">
    .btn, .text-center { display: none !important; }
    body { font-size: 12px; }
</style>

<div class="container mt-4">
    <h2><i class="fas fa-receipt"></i> Detalles de Venta #<?php echo $venta['id']; ?></h2>
    <div class="row mb-4">
        <div class="col-md-6">
            <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?></p>
            <p><strong>Cliente:</strong> <?php echo htmlspecialchars($venta['cliente_nombre'] ?? 'Cliente no encontrado'); ?></p>
            <p><strong>RIF/Cédula:</strong> <?php echo htmlspecialchars($venta['rif_cedula'] ?? 'N/A'); ?></p>
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
                <tr>
                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                    <td><strong>$<?php echo number_format($venta['subtotal'], 2); ?></strong></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end"><strong>IVA (16%):</strong></td>
                    <td><strong>$<?php echo number_format($venta['iva'], 2); ?></strong></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                    <td><strong>$<?php echo number_format($venta['total'], 2); ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="text-center mt-4">
        <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Imprimir Recibo</button>
        <button type="button" class="btn btn-secondary" onclick="window.close()"><i class="fas fa-times"></i> Cerrar</button>
    </div>
</div>

<?php include '../../footer.php'; ?>