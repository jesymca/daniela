<?php
// modules/ventas/listar.php - Creador de Factura de Venta

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

    $stmt = $pdo->query("SELECT id, nombre, rif_cedula FROM clientes ORDER BY nombre ASC");
    $clientes = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al consultar datos: " . $e->getMessage());
}

// Procesar creación de cliente
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_cliente'])) {
    $tipo = trim($_POST['cliente_tipo']);
    $rif_cedula = trim($_POST['cliente_rif_cedula']);
    $nombre = trim($_POST['cliente_nombre']);
    $email = trim($_POST['cliente_email']);
    $telefono = trim($_POST['cliente_telefono']);
    $direccion = trim($_POST['cliente_direccion']);

    if (empty($tipo) || empty($rif_cedula) || empty($nombre)) {
        $error_cliente = "Tipo, RIF/Cédula y nombre son obligatorios.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO clientes (tipo, rif_cedula, nombre, email, telefono, direccion) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$tipo, $rif_cedula, $nombre, $email, $telefono, $direccion]);
            $nuevo_cliente_id = $pdo->lastInsertId();
            // Recargar clientes
            $stmt = $pdo->query("SELECT id, nombre, rif_cedula FROM clientes ORDER BY nombre ASC");
            $clientes = $stmt->fetchAll();
            $success_cliente = "Cliente creado exitosamente.";
        } catch (PDOException $e) {
            $error_cliente = "Error al crear cliente: " . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['productos'])) {
    $cliente_id = $_POST['cliente_id'];
    $productos_seleccionados = $_POST['productos'] ?? [];
    $cantidades = $_POST['cantidades'] ?? [];

    if (empty($productos_seleccionados)) {
        $error = "Debe seleccionar al menos un producto.";
    } elseif (empty($cliente_id)) {
        $error = "Debe seleccionar un cliente.";
    } else {
        try {
            $pdo->beginTransaction();
            $subtotal = 0;
            $iva_tasa = 0.16; // IVA Venezuela 16%

            // Calcular subtotal
            foreach ($productos_seleccionados as $index => $producto_id) {
                $cantidad = $cantidades[$index];
                if ($cantidad <= 0) continue;

                $stmt = $pdo->prepare("SELECT precio, stock FROM productos WHERE id = ?");
                $stmt->execute([$producto_id]);
                $producto = $stmt->fetch();

                if ($producto && $producto['stock'] >= $cantidad) {
                    $subtotal += $producto['precio'] * $cantidad;
                } else {
                    throw new Exception("Stock insuficiente para el producto ID $producto_id");
                }
            }

            $iva = $subtotal * $iva_tasa;
            $total = $subtotal + $iva;

            // Insertar venta
            $stmt = $pdo->prepare("INSERT INTO ventas (cliente_id, subtotal, iva, total) VALUES (?, ?, ?, ?)");
            $stmt->execute([$cliente_id, $subtotal, $iva, $total]);
            $venta_id = $pdo->lastInsertId();

            // Insertar detalles
            foreach ($productos_seleccionados as $index => $producto_id) {
                $cantidad = $cantidades[$index];
                if ($cantidad <= 0) continue;

                $stmt = $pdo->prepare("SELECT precio FROM productos WHERE id = ?");
                $stmt->execute([$producto_id]);
                $producto = $stmt->fetch();

                $stmt = $pdo->prepare("INSERT INTO ventas_detalles (venta_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
                $stmt->execute([$venta_id, $producto_id, $cantidad, $producto['precio']]);

                // Descontar stock
                $stmt = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$cantidad, $producto_id]);
            }

            $pdo->commit();
            $success = "Factura creada correctamente.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error al procesar la factura: " . $e->getMessage();
        }
    }
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
    <h2><i class="fas fa-file-invoice"></i> Creador de Factura de Venta</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if (isset($error_cliente)): ?>
        <div class="alert alert-danger"><?php echo $error_cliente; ?></div>
    <?php endif; ?>
    <?php if (isset($success_cliente)): ?>
        <div class="alert alert-success"><?php echo $success_cliente; ?></div>
    <?php endif; ?>

    <!-- Sección de Creación de Factura -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Nueva Factura</h5>
        </div>
        <div class="card-body">
            <form method="post" id="facturaForm">
                <!-- Selección de Cliente -->
                <div class="mb-3">
                    <label for="cliente_search" class="form-label">Buscar Cliente</label>
                    <input type="text" class="form-control" id="cliente_search" placeholder="Buscar por nombre o RIF/Cédula">
                    <select class="form-control mt-2" id="cliente_id" name="cliente_id" required style="display:none;">
                        <option value="">Seleccionar cliente</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?php echo $cliente['id']; ?>" data-nombre="<?php echo htmlspecialchars($cliente['nombre']); ?>" data-rif="<?php echo htmlspecialchars($cliente['rif_cedula']); ?>"><?php echo htmlspecialchars($cliente['nombre'] . ' (' . $cliente['rif_cedula'] . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div id="cliente_suggestions" class="list-group mt-2" style="max-height: 200px; overflow-y: auto;"></div>
                    <button type="button" class="btn btn-outline-secondary mt-2" data-bs-toggle="modal" data-bs-target="#crearClienteModal"><i class="fas fa-plus"></i> Nuevo Cliente</button>
                </div>

                <!-- Productos -->
                <h5>Productos</h5>
                <div id="productosContainer">
                    <div class="producto-row mb-3 border p-3">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Producto</label>
                                <select class="form-control producto-select" name="productos[]" required>
                                    <option value="">Seleccionar producto</option>
                                    <?php foreach ($productos as $producto): ?>
                                        <option value="<?php echo $producto['id']; ?>" data-precio="<?php echo $producto['precio']; ?>" data-stock="<?php echo $producto['stock']; ?>"><?php echo htmlspecialchars($producto['nombre']); ?> (Stock: <?php echo $producto['stock']; ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
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
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger btn-sm mt-4 remove-product">Remover</button>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary mb-3" id="addProduct"><i class="fas fa-plus"></i> Agregar Producto</button>

                <!-- Totales -->
                <div class="row">
                    <div class="col-md-4">
                        <strong>Subtotal: $<span id="subtotal">0.00</span></strong>
                    </div>
                    <div class="col-md-4">
                        <strong>IVA (16%): $<span id="iva">0.00</span></strong>
                    </div>
                    <div class="col-md-4">
                        <strong>Total: $<span id="total">0.00</span></strong>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-save"></i> Generar Factura</button>
            </form>
        </div>
    </div>

    </div>

    <!-- Modal para crear cliente -->
    <div class="modal fade" id="crearClienteModal" tabindex="-1" aria-labelledby="crearClienteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="crearClienteModalLabel">Crear Nuevo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="cliente_tipo" class="form-label">Tipo *</label>
                            <select class="form-control" id="cliente_tipo" name="cliente_tipo" required>
                                <option value="persona">Persona</option>
                                <option value="empresa">Empresa</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="cliente_rif_cedula" class="form-label">RIF / Cédula *</label>
                            <input type="text" class="form-control" id="cliente_rif_cedula" name="cliente_rif_cedula" required placeholder="V-12345678 o J-123456789">
                        </div>
                        <div class="mb-3">
                            <label for="cliente_nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="cliente_nombre" name="cliente_nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="cliente_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="cliente_email" name="cliente_email">
                        </div>
                        <div class="mb-3">
                            <label for="cliente_telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="cliente_telefono" name="cliente_telefono">
                        </div>
                        <div class="mb-3">
                            <label for="cliente_direccion" class="form-label">Dirección</label>
                            <textarea class="form-control" id="cliente_direccion" name="cliente_direccion"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="crear_cliente" class="btn btn-primary">Crear Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sección de Ventas Existentes -->
    <div class="container mt-4">
        <div class="card-header">
            <h5>Facturas Generadas</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Subtotal</th>
                            <th>IVA</th>
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
                            <td>$<?php echo number_format($venta['subtotal'] ?? 0, 2); ?></td>
                            <td>$<?php echo number_format($venta['iva'] ?? 0, 2); ?></td>
                            <td>$<?php echo number_format($venta['total'] ?? 0, 2); ?></td>
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
    </div>
</div>

<!-- Modal Nueva Venta -->
<!-- Removido, ahora la creación está en la página principal -->

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
        let subtotal = 0;
        document.querySelectorAll('.subtotal-display').forEach(el => {
            subtotal += parseFloat(el.value) || 0;
        });
        const ivaTasa = 0.16;
        const iva = subtotal * ivaTasa;
        const total = subtotal + iva;
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('iva').textContent = iva.toFixed(2);
        document.getElementById('total').textContent = total.toFixed(2);
    }

    // Búsqueda de clientes con soporte flexible para Nombre y Cédula/RIF
    const clienteSearch = document.getElementById('cliente_search');
    const clienteSuggestions = document.getElementById('cliente_suggestions');
    const clienteId = document.getElementById('cliente_id');
    const allClientes = Array.from(clienteId.options).slice(1); // Excluir la primera opción vacía

    if (clienteSearch && clienteSuggestions && clienteId) {
        clienteSearch.addEventListener('input', function() {
            const queryRaw = this.value.toLowerCase().trim();
            const queryAlphaNum = queryRaw.replace(/[^0-9a-z]/g, '');
            const queryDigits = queryRaw.replace(/[^0-9]/g, '');

            clienteSuggestions.innerHTML = '';
            if (queryRaw.length > 0) {
                const filtered = allClientes.filter(option => {
                    const nombre = (option.getAttribute('data-nombre') || option.textContent).toLowerCase();
                    const rif = (option.getAttribute('data-rif') || '').toLowerCase();

                    if (nombre.includes(queryRaw) || rif.includes(queryRaw)) return true;

                    const rifAlphaNum = rif.replace(/[^0-9a-z]/g, '');
                    if (queryAlphaNum && rifAlphaNum.includes(queryAlphaNum)) return true;

                    const rifDigits = rif.replace(/[^0-9]/g, '');
                    if (queryDigits && rifDigits.includes(queryDigits)) return true;

                    return false;
                });

                filtered.forEach(option => {
                    const item = document.createElement('a');
                    item.className = 'list-group-item list-group-item-action';
                    item.href = '#';
                    item.textContent = option.textContent;
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        clienteSearch.value = option.getAttribute('data-nombre') + ' (' + option.getAttribute('data-rif') + ')';
                        clienteId.value = option.value;
                        clienteSuggestions.innerHTML = '';
                    });
                    clienteSuggestions.appendChild(item);
                });
            }
        });
    }
    var detallesVentaModal = document.getElementById('detallesVentaModal');
    if (detallesVentaModal) {
        detallesVentaModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var content = document.getElementById('detallesVentaContent');

            // Cargar detalles vía AJAX
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