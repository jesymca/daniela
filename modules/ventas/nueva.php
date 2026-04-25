<?php
// modules/ventas/nueva.php - Nueva venta

require_once '../../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

$productos = [];
$clientes = [];

try {
    $stmt = $pdo->query("SELECT id, nombre, precio, stock FROM productos WHERE stock > 0 ORDER BY nombre ASC");
    $productos = $stmt->fetchAll();

    $stmt = $pdo->query("SELECT id, nombre FROM clientes ORDER BY nombre ASC");
    $clientes = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al consultar datos: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cliente_id = $_POST['cliente_id'];
    $productos_seleccionados = $_POST['productos'] ?? [];
    $cantidades = $_POST['cantidades'] ?? [];

    if (empty($productos_seleccionados)) {
        $error = "Debe seleccionar al menos un producto.";
    } else {
        try {
            $pdo->beginTransaction();
            $total = 0;

            // Insertar venta
            $stmt = $pdo->prepare("INSERT INTO ventas (cliente_id, total) VALUES (?, 0)");
            $stmt->execute([$cliente_id]);
            $venta_id = $pdo->lastInsertId();

            // Insertar detalles y calcular total
            foreach ($productos_seleccionados as $index => $producto_id) {
                $cantidad = $cantidades[$index];
                if ($cantidad <= 0) continue;

                $stmt = $pdo->prepare("SELECT precio, stock FROM productos WHERE id = ?");
                $stmt->execute([$producto_id]);
                $producto = $stmt->fetch();

                if ($producto && $producto['stock'] >= $cantidad) {
                    $subtotal = $producto['precio'] * $cantidad;
                    $total += $subtotal;

                    $stmt = $pdo->prepare("INSERT INTO ventas_detalles (venta_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$venta_id, $producto_id, $cantidad, $producto['precio']]);

                    // Descontar stock
                    $stmt = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
                    $stmt->execute([$cantidad, $producto_id]);
                } else {
                    throw new Exception("Stock insuficiente para el producto ID $producto_id");
                }
            }

            // Actualizar total de la venta
            $stmt = $pdo->prepare("UPDATE ventas SET total = ? WHERE id = ?");
            $stmt->execute([$total, $venta_id]);

            $pdo->commit();
            header('Location: listar.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error al procesar la venta: " . $e->getMessage();
        }
    }
}

include '../../header.php';
?>

<div class="container mt-4">
    <h2><i class="fas fa-plus"></i> Nueva Venta</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post" id="ventaForm">
        <div class="mb-3">
            <label for="cliente_id" class="form-label">Cliente</label>
            <select class="form-control" id="cliente_id" name="cliente_id" required>
                <option value="">Seleccionar cliente</option>
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?php echo $cliente['id']; ?>"><?php echo htmlspecialchars($cliente['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <h4>Productos</h4>
        <div id="productosContainer">
            <div class="producto-row mb-3 border p-3">
                <div class="row">
                    <div class="col-md-5">
                        <label class="form-label">Producto</label>
                        <select class="form-control producto-select" name="productos[]" required>
                            <option value="">Seleccionar producto</option>
                            <?php foreach ($productos as $producto): ?>
                                <option value="<?php echo $producto['id']; ?>" data-precio="<?php echo $producto['precio']; ?>" data-stock="<?php echo $producto['stock']; ?>"><?php echo htmlspecialchars($producto['nombre']); ?> (Stock: <?php echo $producto['stock']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cantidad</label>
                        <input type="number" class="form-control cantidad-input" name="cantidades[]" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Precio Unit.</label>
                        <input type="text" class="form-control precio-display" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Subtotal</label>
                        <input type="text" class="form-control subtotal-display" readonly>
                        <button type="button" class="btn btn-danger btn-sm mt-2 remove-product">Remover</button>
                    </div>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-secondary mb-3" id="addProduct"><i class="fas fa-plus"></i> Agregar Producto</button>

        <div class="mb-3">
            <strong>Total: $<span id="total">0.00</span></strong>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Procesar Venta</button>
        <a href="listar.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let productIndex = 1;

    document.getElementById('addProduct').addEventListener('click', function() {
        const container = document.getElementById('productosContainer');
        const newRow = container.querySelector('.producto-row').cloneNode(true);
        newRow.querySelectorAll('input').forEach(input => input.value = '');
        newRow.querySelector('.producto-select').selectedIndex = 0;
        container.appendChild(newRow);
        productIndex++;
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-product')) {
            if (document.querySelectorAll('.producto-row').length > 1) {
                e.target.closest('.producto-row').remove();
                updateTotal();
            }
        }
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('producto-select')) {
            updateProductInfo(e.target);
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('cantidad-input')) {
            updateSubtotal(e.target);
        }
    });

    function updateProductInfo(select) {
        const option = select.options[select.selectedIndex];
        const precio = option.getAttribute('data-precio') || 0;
        const row = select.closest('.producto-row');
        row.querySelector('.precio-display').value = precio;
        row.querySelector('.cantidad-input').max = option.getAttribute('data-stock') || 0;
        updateSubtotal(row.querySelector('.cantidad-input'));
    }

    function updateSubtotal(input) {
        const row = input.closest('.producto-row');
        const precio = parseFloat(row.querySelector('.precio-display').value) || 0;
        const cantidad = parseInt(input.value) || 0;
        const subtotal = precio * cantidad;
        row.querySelector('.subtotal-display').value = subtotal.toFixed(2);
        updateTotal();
    }

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.subtotal-display').forEach(el => {
            total += parseFloat(el.value) || 0;
        });
        document.getElementById('total').textContent = total.toFixed(2);
    }
});
</script>

<?php include '../../footer.php'; ?>