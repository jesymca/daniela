<?php
// modules/inventario/listar.php - Listar productos del inventario

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

// Detectar si la columna proveedor_id existe en productos
$productosTieneProveedorId = false;
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM productos LIKE 'proveedor_id'");
    $productosTieneProveedorId = (bool) $stmt->fetch();
} catch (PDOException $e) {
    $productosTieneProveedorId = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_category') {
        $categoria_nombre = trim($_POST['categoria_nombre'] ?? '');
        $categoria_descripcion = trim($_POST['categoria_descripcion'] ?? '');

        if ($categoria_nombre === '') {
            $error = 'El nombre de la categoría es obligatorio.';
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)');
                $stmt->execute([$categoria_nombre, $categoria_descripcion]);
                $success = 'Categoría agregada correctamente.';
            } catch (PDOException $e) {
                $error = 'Error al agregar la categoría: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete_category') {
        $categoria_id = $_POST['categoria_id'] ?? null;
        if ($categoria_id) {
            try {
                $stmt = $pdo->prepare('DELETE FROM categorias WHERE id = ?');
                $stmt->execute([$categoria_id]);
                $success = 'Categoría eliminada correctamente.';
            } catch (PDOException $e) {
                $error = 'Error al eliminar la categoría: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'edit_product') {
        $id = intval($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = $_POST['precio'] ?? '';
        $stock = $_POST['stock'] ?? '';
        $categoria_id = $_POST['categoria_id'] ?? null;
        $proveedor_id = $_POST['proveedor_id'] ?? null;
        $codigo_barras = trim($_POST['codigo_barras'] ?? '');

        if ($id <= 0) {
            $error = 'ID de producto inválido para edición.';
        } elseif ($nombre === '' || $precio === '' || $stock === '') {
            $error = 'Nombre, precio y stock son obligatorios.';
        } elseif (!is_numeric($precio) || $precio < 0) {
            $error = 'El precio debe ser un número positivo.';
        } elseif (!is_numeric($stock) || $stock < 0) {
            $error = 'El stock debe ser un número positivo.';
        } else {
            try {
                if ($productosTieneProveedorId) {
                    $stmt = $pdo->prepare('UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ?, proveedor_id = ?, categoria_id = ?, codigo_barras = ? WHERE id = ?');
                    $stmt->execute([$nombre, $descripcion, $precio, $stock, $proveedor_id ?: null, $categoria_id ?: null, $codigo_barras ?: null, $id]);
                } else {
                    $stmt = $pdo->prepare('UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ?, categoria_id = ?, codigo_barras = ? WHERE id = ?');
                    $stmt->execute([$nombre, $descripcion, $precio, $stock, $categoria_id ?: null, $codigo_barras ?: null, $id]);
                }
                $success = 'Producto actualizado correctamente.';
            } catch (PDOException $e) {
                $error = 'Error al actualizar el producto: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete_product') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare('DELETE FROM productos WHERE id = ?');
                $stmt->execute([$id]);
                $success = 'Producto eliminado correctamente.';
            } catch (PDOException $e) {
                $error = 'Error al eliminar el producto: ' . $e->getMessage();
            }
        } else {
            $error = 'ID de producto inválido para eliminación.';
        }
    } else {
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = $_POST['precio'] ?? '';
        $stock = $_POST['stock'] ?? '';
        $categoria_id = $_POST['categoria_id'] ?? null;
        $proveedor_id = $_POST['proveedor_id'] ?? null;
        $codigo_barras = trim($_POST['codigo_barras'] ?? '');

        if ($nombre === '' || $precio === '' || $stock === '') {
            $error = 'Nombre, precio y stock son obligatorios.';
        } elseif (!is_numeric($precio) || $precio < 0) {
            $error = 'El precio debe ser un número positivo.';
        } elseif (!is_numeric($stock) || $stock < 0) {
            $error = 'El stock debe ser un número positivo.';
        } else {
            try {
                if ($productosTieneProveedorId) {
                    $stmt = $pdo->prepare('INSERT INTO productos (nombre, descripcion, precio, stock, proveedor_id, categoria_id, codigo_barras) VALUES (?, ?, ?, ?, ?, ?, ?)');
                    $stmt->execute([$nombre, $descripcion, $precio, $stock, $proveedor_id ?: null, $categoria_id ?: null, $codigo_barras ?: null]);
                } else {
                    $stmt = $pdo->prepare('INSERT INTO productos (nombre, descripcion, precio, stock, categoria_id, codigo_barras) VALUES (?, ?, ?, ?, ?, ?)');
                    $stmt->execute([$nombre, $descripcion, $precio, $stock, $categoria_id ?: null, $codigo_barras ?: null]);
                }
                $success = 'Producto agregado correctamente.';
            } catch (PDOException $e) {
                $error = 'Error al agregar el producto: ' . $e->getMessage();
            }
        }
    }
}

try {
    $stmt = $pdo->query('SELECT * FROM categorias ORDER BY nombre ASC');
    $categorias = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Error al consultar categorías: ' . $e->getMessage());
}

// Obtener proveedores para formulario
try {
    $stmt = $pdo->query('SELECT id, nombre FROM proveedores ORDER BY nombre ASC');
    $proveedores = $stmt->fetchAll();
} catch (PDOException $e) {
    $proveedores = [];
}

try {
    if ($productosTieneProveedorId) {
        $sql = "SELECT p.*, c.nombre AS categoria_nombre,
            COALESCE(pr.nombre,
            (SELECT pr2.nombre FROM compras_detalles cd
                JOIN compras co ON cd.compra_id = co.id
                JOIN proveedores pr2 ON co.proveedor_id = pr2.id
                WHERE cd.producto_id = p.id AND co.proveedor_id IS NOT NULL
                ORDER BY co.fecha DESC LIMIT 1)) AS proveedor_nombre
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            LEFT JOIN proveedores pr ON p.proveedor_id = pr.id
            ORDER BY p.nombre ASC";
    } else {
        $sql = "SELECT p.*, c.nombre AS categoria_nombre,
            (SELECT pr2.nombre FROM compras_detalles cd
                JOIN compras co ON cd.compra_id = co.id
                JOIN proveedores pr2 ON co.proveedor_id = pr2.id
                WHERE cd.producto_id = p.id AND co.proveedor_id IS NOT NULL
                ORDER BY co.fecha DESC LIMIT 1) AS proveedor_nombre
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            ORDER BY p.nombre ASC";
    }
    $stmt = $pdo->query($sql);
    $productos = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al consultar productos: " . $e->getMessage());
}

include '../../header.php';
?>

<div class="container mt-4 mb-5">
    <div class="row align-items-center mb-3">
        <div class="col-md-6">
            <h2 class="mb-0"><i class="fas fa-boxes text-primary me-2"></i> Inventario de Productos</h2>
        </div>
        <div class="col-md-6 text-md-end mt-2 mt-md-0">
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#agregarProductoModal">
                <i class="fas fa-plus me-1"></i> Agregar Producto
            </button>
        </div>
    </div>

    <!-- Barra de Búsqueda en Tiempo Real para Productos -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-primary text-white"><i class="fas fa-search"></i></span>
                <input type="text" id="buscarProductoInput" class="form-control" placeholder="Buscar por Nombre, Proveedor, Categoría o Descripción en tiempo real...">
                <button type="button" class="btn btn-outline-secondary" id="limpiarBusquedaProducto" title="Limpiar filtro">
                    <i class="fas fa-times"></i> Limpiar
                </button>
            </div>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Código de Barras</th>
                    <th>Nombre</th>
                    <th>Proveedor</th>
                    <th>Categoría</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto): ?>
                <tr>
                    <td><?php echo $producto['id']; ?></td>
                    <td><?php echo htmlspecialchars($producto['codigo_barras']); ?></td>
                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($producto['proveedor_nombre'] ?? 'Sin proveedor'); ?></td>
                    <td><?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría'); ?></td>
                    <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                    <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                    <td><?php echo $producto['stock']; ?></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editarProductoModal"
                            data-id="<?php echo $producto['id']; ?>"
                            data-codigo="<?php echo htmlspecialchars($producto['codigo_barras'], ENT_QUOTES); ?>"
                            data-nombre="<?php echo htmlspecialchars($producto['nombre'], ENT_QUOTES); ?>"
                            data-proveedor="<?php echo intval($producto['proveedor_id'] ?? 0); ?>"
                            data-categoria="<?php echo intval($producto['categoria_id']); ?>"
                            data-descripcion="<?php echo htmlspecialchars($producto['descripcion'], ENT_QUOTES); ?>"
                            data-precio="<?php echo htmlspecialchars($producto['precio'], ENT_QUOTES); ?>"
                            data-stock="<?php echo htmlspecialchars($producto['stock'], ENT_QUOTES); ?>">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmarEliminarProductoModal"
                            data-id="<?php echo $producto['id']; ?>"
                            data-nombre="<?php echo htmlspecialchars($producto['nombre'], ENT_QUOTES); ?>">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <h3><i class="fas fa-tags"></i> Gestión de Categorías</h3>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-secondary mb-3" data-bs-toggle="modal" data-bs-target="#agregarCategoriaModal">
                <i class="fas fa-plus"></i> Nueva Categoría
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categorias as $categoria): ?>
                <tr>
                    <td><?php echo $categoria['id']; ?></td>
                    <td><?php echo htmlspecialchars($categoria['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($categoria['descripcion']); ?></td>
                    <td>
                        <form method="post" action="" class="d-inline">
                            <input type="hidden" name="action" value="delete_category">
                            <input type="hidden" name="categoria_id" value="<?php echo $categoria['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta categoría?')"><i class="fas fa-trash"></i> Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Agregar Producto -->
<div class="modal fade" id="agregarProductoModal" tabindex="-1" aria-labelledby="agregarProductoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="agregarProductoModalLabel">Agregar Nuevo Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <input type="hidden" name="action" value="add_product">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="codigo_barras" class="form-label">Código de Barras</label>
                        <input type="text" class="form-control" id="codigo_barras" name="codigo_barras">
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="categoria_id" class="form-label">Categoría</label>
                        <select class="form-control" id="categoria_id" name="categoria_id">
                            <option value="">Sin categoría</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['id']; ?>"><?php echo htmlspecialchars($categoria['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($productosTieneProveedorId): ?>
                    <div class="mb-3">
                        <label for="proveedor_id" class="form-label">Proveedor</label>
                        <select class="form-control" id="proveedor_id" name="proveedor_id">
                            <option value="">Sin proveedor</option>
                            <?php foreach ($proveedores as $prov): ?>
                                <option value="<?php echo $prov['id']; ?>"><?php echo htmlspecialchars($prov['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">La base de datos no tiene la columna <code>proveedor_id</code> en <code>productos</code>. Ejecuta la migración para habilitar la asignación de proveedor.</div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="precio" class="form-label">Precio *</label>
                        <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock *</label>
                        <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agregar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Agregar Categoría -->
<div class="modal fade" id="agregarCategoriaModal" tabindex="-1" aria-labelledby="agregarCategoriaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="agregarCategoriaModalLabel">Agregar Nueva Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <input type="hidden" name="action" value="add_category">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="categoria_nombre" class="form-label">Nombre de la Categoría *</label>
                        <input type="text" class="form-control" id="categoria_nombre" name="categoria_nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="categoria_descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="categoria_descripcion" name="categoria_descripcion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agregar Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Producto -->
<div class="modal fade" id="editarProductoModal" tabindex="-1" aria-labelledby="editarProductoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarProductoModalLabel">Editar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <input type="hidden" name="action" value="edit_product">
                <input type="hidden" id="editarProductoId" name="id" value="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editarCodigoBarras" class="form-label">Código de Barras</label>
                        <input type="text" class="form-control" id="editarCodigoBarras" name="codigo_barras">
                    </div>
                    <div class="mb-3">
                        <label for="editarNombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="editarNombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="editarDescripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="editarDescripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editarCategoriaId" class="form-label">Categoría</label>
                        <select class="form-control" id="editarCategoriaId" name="categoria_id">
                            <option value="">Sin categoría</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['id']; ?>"><?php echo htmlspecialchars($categoria['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($productosTieneProveedorId): ?>
                    <div class="mb-3">
                        <label for="editarProveedorId" class="form-label">Proveedor</label>
                        <select class="form-control" id="editarProveedorId" name="proveedor_id">
                            <option value="">Sin proveedor</option>
                            <?php foreach ($proveedores as $prov): ?>
                                <option value="<?php echo $prov['id']; ?>"><?php echo htmlspecialchars($prov['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">La base de datos no tiene la columna <code>proveedor_id</code> en <code>productos</code>. Ejecuta la migración para habilitar la asignación de proveedor.</div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="editarPrecio" class="form-label">Precio *</label>
                        <input type="number" class="form-control" id="editarPrecio" name="precio" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="editarStock" class="form-label">Stock *</label>
                        <input type="number" class="form-control" id="editarStock" name="stock" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Confirmar Eliminación Producto -->
<div class="modal fade" id="confirmarEliminarProductoModal" tabindex="-1" aria-labelledby="confirmarEliminarProductoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmarEliminarProductoModalLabel">Eliminar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <input type="hidden" name="action" value="delete_product">
                <input type="hidden" id="eliminarProductoId" name="id" value="">
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar el producto <strong id="eliminarProductoNombre"></strong>?</p>
                    <p class="text-danger">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var editarProductoModal = document.getElementById('editarProductoModal');
        if (editarProductoModal) {
            editarProductoModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var codigo = button.getAttribute('data-codigo');
                var nombre = button.getAttribute('data-nombre');
                var proveedor = button.getAttribute('data-proveedor');
                var categoria = button.getAttribute('data-categoria');
                var descripcion = button.getAttribute('data-descripcion');
                var precio = button.getAttribute('data-precio');
                var stock = button.getAttribute('data-stock');

                document.getElementById('editarProductoId').value = id;
                document.getElementById('editarCodigoBarras').value = codigo;
                document.getElementById('editarNombre').value = nombre;
                const editarProveedorSelect = document.getElementById('editarProveedorId');
                if (editarProveedorSelect) {
                    editarProveedorSelect.value = proveedor || '';
                }
                document.getElementById('editarCategoriaId').value = categoria;
                document.getElementById('editarDescripcion').value = descripcion;
                document.getElementById('editarPrecio').value = precio;
                document.getElementById('editarStock').value = stock;
            });
        }

        var confirmarEliminarProductoModal = document.getElementById('confirmarEliminarProductoModal');
        if (confirmarEliminarProductoModal) {
            confirmarEliminarProductoModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var nombre = button.getAttribute('data-nombre');

                document.getElementById('eliminarProductoId').value = id;
                document.getElementById('eliminarProductoNombre').textContent = nombre;
            });
        }

        /**
         * ====================================================================
         * FILTRADO EN TIEMPO REAL: Productos (Nombre, Proveedor, Categoría, Descripción)
         * ====================================================================
         */
        var buscarInput = document.getElementById('buscarProductoInput');
        var limpiarBtn = document.getElementById('limpiarBusquedaProducto');
        var tablaProductos = document.querySelector('.table-responsive table');

        if (buscarInput && tablaProductos) {
            var tbody = tablaProductos.querySelector('tbody');
            var filas = tbody ? tbody.querySelectorAll('tr') : [];

            function filtrarProductos() {
                var query = buscarInput.value.toLowerCase().trim();
                var visibles = 0;

                filas.forEach(function(row) {
                    if (row.id === 'noProductosRow') return;

                    var ctds = row.querySelectorAll('td');
                    if (ctds.length < 6) return;

                    var codigo = (ctds[1].textContent || '').toLowerCase();
                    var nombre = (ctds[2].textContent || '').toLowerCase();
                    var proveedor = (ctds[3].textContent || '').toLowerCase();
                    var categoria = (ctds[4].textContent || '').toLowerCase();
                    var descripcion = (ctds[5].textContent || '').toLowerCase();

                    if (!query || nombre.includes(query) || proveedor.includes(query) || categoria.includes(query) || descripcion.includes(query) || codigo.includes(query)) {
                        row.style.display = '';
                        visibles++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                var noRow = document.getElementById('noProductosRow');
                if (visibles === 0 && query !== '') {
                    if (!noRow) {
                        noRow = document.createElement('tr');
                        noRow.id = 'noProductosRow';
                        noRow.innerHTML = '<td colspan="9" class="text-center text-muted py-4"><i class="fas fa-search me-2 text-warning"></i>No se encontraron productos que coincidan con "<strong>' + document.createTextNode(buscarInput.value).textContent + '</strong>".</td>';
                        tbody.appendChild(noRow);
                    } else {
                        noRow.style.display = '';
                        noRow.querySelector('td').innerHTML = '<i class="fas fa-search me-2 text-warning"></i>No se encontraron productos que coincidan con "<strong>' + document.createTextNode(buscarInput.value).textContent + '</strong>".';
                    }
                } else if (noRow) {
                    noRow.style.display = 'none';
                }
            }

            buscarInput.addEventListener('input', filtrarProductos);
            if (limpiarBtn) {
                limpiarBtn.addEventListener('click', function() {
                    buscarInput.value = '';
                    filtrarProductos();
                    buscarInput.focus();
                });
            }
        }
    });
</script>

<?php include '../../footer.php'; ?>