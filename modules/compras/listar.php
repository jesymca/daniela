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
    if (isset($_POST['crear_proveedor'])) {
        $tipo = trim($_POST['proveedor_tipo'] ?? 'persona');
        $rif_cedula = trim($_POST['proveedor_rif_cedula'] ?? '');
        $nombre_proveedor = trim($_POST['proveedor_nombre'] ?? '');
        $email_proveedor = trim($_POST['proveedor_email'] ?? '');
        $telefono_proveedor = trim($_POST['proveedor_telefono'] ?? '');
        $direccion_proveedor = trim($_POST['proveedor_direccion'] ?? '');

        if ($tipo === '' || $rif_cedula === '' || $nombre_proveedor === '') {
            $error = 'Tipo, RIF/Cédula y nombre del proveedor son obligatorios.';
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO proveedores (tipo, rif_cedula, nombre, email, telefono, direccion) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([$tipo, $rif_cedula, $nombre_proveedor, $email_proveedor, $telefono_proveedor, $direccion_proveedor]);
                $success = 'Proveedor agregado correctamente.';
            } catch (PDOException $e) {
                $error = 'Error al crear el proveedor: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['productos'])) {
        $numero_factura = trim($_POST['numero_factura'] ?? '');
        $proveedor_id = $_POST['proveedor_id'] ?? '';
        $productos_seleccionados = $_POST['productos'] ?? [];
        $cantidades = $_POST['cantidades'] ?? [];
        $precios = $_POST['precios'] ?? [];

        if ($numero_factura === '' || $proveedor_id === '' || empty($productos_seleccionados)) {
            $error = 'Número de factura, proveedor y al menos un producto son obligatorios.';
        } else {
            try {
                $pdo->beginTransaction();
                $total = 0;

                foreach ($productos_seleccionados as $index => $producto_id) {
                    $cantidad = intval($cantidades[$index] ?? 0);
                    $precio_unitario = floatval($precios[$index] ?? 0);
                    if ($cantidad <= 0 || $precio_unitario < 0) {
                        continue;
                    }

                    $stmt = $pdo->prepare('SELECT stock FROM productos WHERE id = ?');
                    $stmt->execute([$producto_id]);
                    $producto = $stmt->fetch();
                    if (!$producto) {
                        throw new Exception('Producto no encontrado para ID ' . $producto_id);
                    }

                    $subtotal = $cantidad * $precio_unitario;
                    $total += $subtotal;
                }

                if ($total <= 0) {
                    throw new Exception('El total de la compra debe ser mayor a cero.');
                }

                $stmt = $pdo->prepare('INSERT INTO compras (proveedor_id, total, numero_factura) VALUES (?, ?, ?)');
                $stmt->execute([$proveedor_id, $total, $numero_factura]);
                $compra_id = $pdo->lastInsertId();

                $stmtDetalle = $pdo->prepare('INSERT INTO compras_detalles (compra_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)');
                $stmtStock = $pdo->prepare('UPDATE productos SET stock = stock + ? WHERE id = ?');
                foreach ($productos_seleccionados as $index => $producto_id) {
                    $cantidad = intval($cantidades[$index] ?? 0);
                    $precio_unitario = floatval($precios[$index] ?? 0);
                    if ($cantidad <= 0 || $precio_unitario < 0) {
                        continue;
                    }

                    $stmtDetalle->execute([$compra_id, $producto_id, $cantidad, $precio_unitario]);
                    $stmtStock->execute([$cantidad, $producto_id]);
                }

                $pdo->commit();
                $success = 'Compra creada correctamente.';
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Error al crear la compra: ' . $e->getMessage();
            }
        }
    }
}

// Obtener proveedores y productos para el modal
$proveedores = [];
$productos = [];
try {
    $stmt = $pdo->query("SELECT id, tipo, rif_cedula, nombre FROM proveedores ORDER BY nombre ASC");
    $proveedores = $stmt->fetchAll();

    $stmt = $pdo->query("SELECT id, nombre, precio, stock FROM productos ORDER BY nombre ASC");
    $productos = $stmt->fetchAll();
} catch (PDOException $e) {
    // Ignorar errores aquí
}

try {
    $stmt = $pdo->query("SELECT c.*, p.nombre as proveedor_nombre, c.numero_factura FROM compras c LEFT JOIN proveedores p ON c.proveedor_id = p.id ORDER BY c.fecha DESC");
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
                    <th>Factura</th>
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
                    <td><?php echo htmlspecialchars($compra['numero_factura'] ?? ''); ?></td>
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
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nuevaCompraModalLabel">Crear Nueva Compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="numero_factura" class="form-label">Número de Factura *</label>
                        <input type="text" class="form-control" id="numero_factura" name="numero_factura" required>
                    </div>
                    <div class="mb-3">
                        <label for="proveedor_search" class="form-label">Proveedor *</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="proveedor_search" placeholder="Buscar por nombre o RIF/Cédula" required>
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#crearProveedorModal"><i class="fas fa-plus"></i> Nuevo Proveedor</button>
                        </div>
                        <input type="hidden" id="proveedor_id" name="proveedor_id" required>
                        <div id="proveedor_suggestions" class="list-group mt-2" style="max-height: 200px; overflow-y: auto;"></div>
                    </div>

                    <h5>Productos</h5>
                    <div id="productosContainer">
                        <div class="producto-row mb-3 border p-3">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Producto</label>
                                    <select class="form-control producto-select" name="productos[]" required>
                                        <option value="">Seleccionar producto</option>
                                        <?php foreach ($productos as $producto): ?>
                                            <option value="<?php echo $producto['id']; ?>" data-nombre="<?php echo htmlspecialchars($producto['nombre'], ENT_QUOTES); ?>" data-precio="<?php echo $producto['precio']; ?>" data-stock="<?php echo $producto['stock']; ?>"><?php echo htmlspecialchars($producto['nombre']); ?> (Stock: <?php echo $producto['stock']; ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Cantidad</label>
                                    <input type="number" class="form-control cantidad-input" name="cantidades[]" min="1" value="1" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Precio Unitario</label>
                                    <input type="number" class="form-control precio-input" name="precios[]" step="0.01" min="0" value="0.00" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Subtotal</label>
                                    <input type="text" class="form-control subtotal-display" readonly value="0.00">
                                </div>
                                <div class="col-md-1 text-end">
                                    <button type="button" class="btn btn-danger btn-sm remove-product"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="addProduct"><i class="fas fa-plus"></i> Agregar Producto</button>

                    <div class="mb-3">
                        <strong>Total: $<span id="total">0.00</span></strong>
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

<!-- Modal Crear Proveedor -->
<div class="modal fade" id="crearProveedorModal" tabindex="-1" aria-labelledby="crearProveedorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="crearProveedorModalLabel">Crear Nuevo Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <input type="hidden" name="crear_proveedor" value="1">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="proveedor_tipo" class="form-label">Tipo *</label>
                        <select class="form-control" id="proveedor_tipo" name="proveedor_tipo" required>
                            <option value="persona">Persona</option>
                            <option value="empresa">Empresa</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="proveedor_rif_cedula" class="form-label">RIF / Cédula *</label>
                        <input type="text" class="form-control" id="proveedor_rif_cedula" name="proveedor_rif_cedula" required placeholder="J-123456789 o V-12345678">
                    </div>
                    <div class="mb-3">
                        <label for="proveedor_nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="proveedor_nombre" name="proveedor_nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="proveedor_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="proveedor_email" name="proveedor_email">
                    </div>
                    <div class="mb-3">
                        <label for="proveedor_telefono" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="proveedor_telefono" name="proveedor_telefono">
                    </div>
                    <div class="mb-3">
                        <label for="proveedor_direccion" class="form-label">Dirección</label>
                        <textarea class="form-control" id="proveedor_direccion" name="proveedor_direccion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Proveedor</button>
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
        const proveedoresList = <?php echo json_encode($proveedores); ?>;
        const proveedorSearch = document.getElementById('proveedor_search');
        const proveedorSuggestions = document.getElementById('proveedor_suggestions');
        const proveedorId = document.getElementById('proveedor_id');
        const productosContainer = document.getElementById('productosContainer');
        const addProductButton = document.getElementById('addProduct');

        function renderProveedorSuggestions(query) {
            proveedorSuggestions.innerHTML = '';
            const value = query.trim().toLowerCase();
            if (!value) {
                proveedorId.value = '';
                return;
            }
            const filtered = proveedoresList.filter(proveedor => {
                const nombre = proveedor.nombre.toLowerCase();
                const rif = (proveedor.rif_cedula || '').toLowerCase();
                return nombre.includes(value) || rif.includes(value);
            });

            filtered.forEach(proveedor => {
                const item = document.createElement('a');
                item.href = '#';
                item.className = 'list-group-item list-group-item-action';
                item.textContent = proveedor.nombre + ' (' + (proveedor.rif_cedula || 'Sin RIF') + ')';
                item.addEventListener('click', function (e) {
                    e.preventDefault();
                    proveedorSearch.value = this.textContent;
                    proveedorId.value = proveedor.id;
                    proveedorSuggestions.innerHTML = '';
                });
                proveedorSuggestions.appendChild(item);
            });
        }

        if (proveedorSearch) {
            proveedorSearch.addEventListener('input', function () {
                renderProveedorSuggestions(this.value);
            });
        }

        function updateSubtotal(input) {
            const row = input.closest('.producto-row');
            const cantidad = parseInt(row.querySelector('.cantidad-input').value) || 0;
            const precio = parseFloat(row.querySelector('.precio-input').value) || 0;
            const subtotal = cantidad * precio;
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

        function attachRowListeners(row) {
            row.querySelectorAll('.cantidad-input, .precio-input').forEach(input => {
                input.addEventListener('input', function () {
                    updateSubtotal(this);
                });
            });
            const removeButton = row.querySelector('.remove-product');
            if (removeButton) {
                removeButton.addEventListener('click', function () {
                    if (document.querySelectorAll('.producto-row').length > 1) {
                        row.remove();
                        updateTotal();
                    }
                });
            }
            const productSelect = row.querySelector('.producto-select');
            productSelect.addEventListener('change', function () {
                const option = this.options[this.selectedIndex];
                const precio = parseFloat(option.getAttribute('data-precio') || 0) || 0;
                const rowPrecio = row.querySelector('.precio-input');
                if (rowPrecio.value === '0.00' || rowPrecio.value === '') {
                    rowPrecio.value = precio.toFixed(2);
                }
                updateSubtotal(rowPrecio);
            });
        }

        if (productosContainer) {
            attachRowListeners(productosContainer.querySelector('.producto-row'));
        }

        if (addProductButton) {
            addProductButton.addEventListener('click', function () {
                const firstRow = document.querySelector('.producto-row');
                const newRow = firstRow.cloneNode(true);
                newRow.querySelectorAll('input').forEach(input => {
                    if (input.type === 'number') {
                        input.value = input.name === 'cantidades[]' ? '1' : '0.00';
                    } else {
                        input.value = '';
                    }
                });
                newRow.querySelector('.subtotal-display').value = '0.00';
                newRow.querySelector('.producto-select').selectedIndex = 0;
                productosContainer.appendChild(newRow);
                attachRowListeners(newRow);
            });
        }

        var detallesCompraModal = document.getElementById('detallesCompraModal');
        if (detallesCompraModal) {
            detallesCompraModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var content = document.getElementById('detallesCompraContent');

                fetch('<?php echo BASE_PATH; ?>/detalles.php?id=' + id + '&ajax=1')
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