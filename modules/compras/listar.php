<?php
// modules/compras/listar.php - Listar compras

require_once '../../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $proveedor_id = $_POST['proveedor_id'] ?? '';
    $producto_id = $_POST['producto_id'] ?? '';
    $cantidad = $_POST['cantidad'] ?? '';
    $precio_unitario = $_POST['precio_unitario'] ?? '';

    if ($proveedor_id === '' || $producto_id === '' || $cantidad === '' || $precio_unitario === '') {
        $error = 'Proveedor, producto, cantidad y precio unitario son obligatorios.';
    } elseif (!is_numeric($cantidad) || $cantidad <= 0) {
        $error = 'La cantidad debe ser un número positivo.';
    } elseif (!is_numeric($precio_unitario) || $precio_unitario < 0) {
        $error = 'El precio unitario debe ser un número positivo.';
    } else {
        try {
            $total = $precio_unitario * $cantidad;

            // Iniciar transacción
            $pdo->beginTransaction();

            // Insertar compra
            $stmt = $pdo->prepare('INSERT INTO compras (proveedor_id, total) VALUES (?, ?)');
            $stmt->execute([$proveedor_id, $total]);
            $compra_id = $pdo->lastInsertId();

            // Insertar detalle de compra
            $stmt = $pdo->prepare('INSERT INTO compras_detalles (compra_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)');
            $stmt->execute([$compra_id, $producto_id, $cantidad, $precio_unitario]);

            // Actualizar stock (agregar)
            $stmt = $pdo->prepare('UPDATE productos SET stock = stock + ? WHERE id = ?');
            $stmt->execute([$cantidad, $producto_id]);

            $pdo->commit();
            $success = 'Compra creada correctamente.';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Error al crear la compra: ' . $e->getMessage();
        }
    }
}

// Obtener proveedores y productos para el modal
$proveedores = [];
$productos = [];
try {
    $stmt = $pdo->query("SELECT id, nombre FROM proveedores ORDER BY nombre ASC");
    $proveedores = $stmt->fetchAll();

    $stmt = $pdo->query("SELECT id, nombre FROM productos ORDER BY nombre ASC");
    $productos = $stmt->fetchAll();
} catch (PDOException $e) {
    // Ignorar errores aquí
}

try {
    $stmt = $pdo->query("SELECT c.*, p.nombre as proveedor_nombre FROM compras c LEFT JOIN proveedores p ON c.proveedor_id = p.id ORDER BY c.fecha DESC");
    $compras = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al consultar compras: " . $e->getMessage());
}

include '../../header.php';
?>

<div class="container mt-4">
    <h2><i class="fas fa-truck"></i> Gestión de Compras</h2>
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#nuevaCompraModal">
        <i class="fas fa-plus"></i> Nueva Compra
    </button>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Proveedor</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($compras as $compra): ?>
                <tr>
                    <td><?php echo $compra['id']; ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($compra['fecha'])); ?></td>
                    <td><?php echo htmlspecialchars($compra['proveedor_nombre'] ?? 'Proveedor no encontrado'); ?></td>
                    <td>$<?php echo number_format($compra['total'], 2); ?></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detallesCompraModal" data-id="<?php echo $compra['id']; ?>">
                            <i class="fas fa-eye"></i> Detalles
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Nueva Compra -->
<div class="modal fade" id="nuevaCompraModal" tabindex="-1" aria-labelledby="nuevaCompraModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nuevaCompraModalLabel">Crear Nueva Compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="proveedor_id" class="form-label">Proveedor *</label>
                        <select class="form-control" id="proveedor_id" name="proveedor_id" required>
                            <option value="">Seleccionar proveedor</option>
                            <?php foreach ($proveedores as $proveedor): ?>
                                <option value="<?php echo $proveedor['id']; ?>"><?php echo htmlspecialchars($proveedor['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="producto_id" class="form-label">Producto *</label>
                        <select class="form-control" id="producto_id" name="producto_id" required>
                            <option value="">Seleccionar producto</option>
                            <?php foreach ($productos as $producto): ?>
                                <option value="<?php echo $producto['id']; ?>"><?php echo htmlspecialchars($producto['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="cantidad" class="form-label">Cantidad *</label>
                        <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="precio_unitario" class="form-label">Precio Unitario *</label>
                        <input type="number" class="form-control" id="precio_unitario" name="precio_unitario" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Compra</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detalles Compra -->
<div class="modal fade" id="detallesCompraModal" tabindex="-1" aria-labelledby="detallesCompraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detallesCompraModalLabel">Detalles de Compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detallesCompraContent">
                <!-- Contenido cargado dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var detallesCompraModal = document.getElementById('detallesCompraModal');
        if (detallesCompraModal) {
            detallesCompraModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var content = document.getElementById('detallesCompraContent');

                // Cargar detalles vía AJAX
                fetch('modules/compras/detalles.php?id=' + id + '&ajax=1')
                    .then(response => response.text())
                    .then(data => {
                        content.innerHTML = data;
                    })
                    .catch(error => {
                        content.innerHTML = '<p>Error al cargar detalles.</p>';
                    });
            });
        }
    });
</script>

<?php include '../../footer.php'; ?>