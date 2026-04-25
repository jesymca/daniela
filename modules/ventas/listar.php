<?php
// modules/ventas/listar.php - Listar ventas

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
    $cliente_id = $_POST['cliente_id'] ?? '';
    $producto_id = $_POST['producto_id'] ?? '';
    $cantidad = $_POST['cantidad'] ?? '';

    if ($cliente_id === '' || $producto_id === '' || $cantidad === '') {
        $error = 'Cliente, producto y cantidad son obligatorios.';
    } elseif (!is_numeric($cantidad) || $cantidad <= 0) {
        $error = 'La cantidad debe ser un número positivo.';
    } else {
        try {
            // Verificar stock disponible
            $stmt = $pdo->prepare('SELECT stock, precio FROM productos WHERE id = ?');
            $stmt->execute([$producto_id]);
            $producto = $stmt->fetch();

            if (!$producto) {
                $error = 'Producto no encontrado.';
            } elseif ($producto['stock'] < $cantidad) {
                $error = 'Stock insuficiente. Disponible: ' . $producto['stock'];
            } else {
                $total = $producto['precio'] * $cantidad;

                // Iniciar transacción
                $pdo->beginTransaction();

                // Insertar venta
                $stmt = $pdo->prepare('INSERT INTO ventas (cliente_id, total) VALUES (?, ?)');
                $stmt->execute([$cliente_id, $total]);
                $venta_id = $pdo->lastInsertId();

                // Insertar detalle de venta
                $stmt = $pdo->prepare('INSERT INTO ventas_detalles (venta_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)');
                $stmt->execute([$venta_id, $producto_id, $cantidad, $producto['precio']]);

                // Actualizar stock
                $stmt = $pdo->prepare('UPDATE productos SET stock = stock - ? WHERE id = ?');
                $stmt->execute([$cantidad, $producto_id]);

                $pdo->commit();
                $success = 'Venta creada correctamente.';
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Error al crear la venta: ' . $e->getMessage();
        }
    }
}

// Obtener clientes y productos para el modal
$clientes = [];
$productos = [];
try {
    $stmt = $pdo->query("SELECT id, nombre FROM clientes ORDER BY nombre ASC");
    $clientes = $stmt->fetchAll();

    $stmt = $pdo->query("SELECT id, nombre, stock FROM productos WHERE stock > 0 ORDER BY nombre ASC");
    $productos = $stmt->fetchAll();
} catch (PDOException $e) {
    // Ignorar errores aquí
}

try {
    $stmt = $pdo->query("SELECT v.*, c.nombre as cliente_nombre FROM ventas v LEFT JOIN clientes c ON v.cliente_id = c.id ORDER BY v.fecha DESC");
    $ventas = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al consultar ventas: " . $e->getMessage());
}

include '../../header.php';
?>

<div class="container mt-4">
    <h2><i class="fas fa-shopping-cart"></i> Gestión de Ventas</h2>
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#nuevaVentaModal">
        <i class="fas fa-plus"></i> Nueva Venta
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
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventas as $venta): ?>
                <tr>
                    <td><?php echo $venta['id']; ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?></td>
                    <td><?php echo htmlspecialchars($venta['cliente_nombre'] ?? 'Cliente no encontrado'); ?></td>
                    <td>$<?php echo number_format($venta['total'], 2); ?></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detallesVentaModal" data-id="<?php echo $venta['id']; ?>">
                            <i class="fas fa-eye"></i> Detalles
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Nueva Venta -->
<div class="modal fade" id="nuevaVentaModal" tabindex="-1" aria-labelledby="nuevaVentaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nuevaVentaModalLabel">Crear Nueva Venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="cliente_id" class="form-label">Cliente *</label>
                        <select class="form-control" id="cliente_id" name="cliente_id" required>
                            <option value="">Seleccionar cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo $cliente['id']; ?>"><?php echo htmlspecialchars($cliente['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="producto_id" class="form-label">Producto *</label>
                        <select class="form-control" id="producto_id" name="producto_id" required>
                            <option value="">Seleccionar producto</option>
                            <?php foreach ($productos as $producto): ?>
                                <option value="<?php echo $producto['id']; ?>"><?php echo htmlspecialchars($producto['nombre'] . ' (Stock: ' . $producto['stock'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="cantidad" class="form-label">Cantidad *</label>
                        <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Venta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detalles Venta -->
<div class="modal fade" id="detallesVentaModal" tabindex="-1" aria-labelledby="detallesVentaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detallesVentaModalLabel">Detalles de Venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detallesVentaContent">
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
        var detallesVentaModal = document.getElementById('detallesVentaModal');
        if (detallesVentaModal) {
            detallesVentaModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var content = document.getElementById('detallesVentaContent');

                // Cargar detalles vía AJAX
                fetch('modules/ventas/detalles.php?id=' + id + '&ajax=1')
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