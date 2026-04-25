<?php
// modules/proveedores/listar.php - Listar proveedores

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
    $action = $_POST['action'] ?? 'add';
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if ($action === 'add' || $action === 'edit') {
        if ($nombre === '') {
            $error = 'El nombre es obligatorio.';
        } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'El correo electrónico no es válido.';
        } else {
            try {
                if ($action === 'add') {
                    $stmt = $pdo->prepare('INSERT INTO proveedores (nombre, email, telefono, direccion) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$nombre, $email, $telefono, $direccion]);
                    $success = 'Proveedor agregado correctamente.';
                } else {
                    $id = intval($_POST['id'] ?? 0);
                    if ($id > 0) {
                        $stmt = $pdo->prepare('UPDATE proveedores SET nombre = ?, email = ?, telefono = ?, direccion = ? WHERE id = ?');
                        $stmt->execute([$nombre, $email, $telefono, $direccion, $id]);
                        $success = 'Proveedor actualizado correctamente.';
                    } else {
                        $error = 'ID de proveedor inválido para edición.';
                    }
                }
            } catch (PDOException $e) {
                $error = ($action === 'add' ? 'Error al agregar el proveedor: ' : 'Error al actualizar el proveedor: ') . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare('DELETE FROM proveedores WHERE id = ?');
                $stmt->execute([$id]);
                $success = 'Proveedor eliminado correctamente.';
            } catch (PDOException $e) {
                $error = 'Error al eliminar el proveedor: ' . $e->getMessage();
            }
        } else {
            $error = 'ID de proveedor inválido para eliminación.';
        }
    }
}

try {
    $stmt = $pdo->query("SELECT * FROM proveedores ORDER BY nombre ASC");
    $proveedores = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al consultar proveedores: " . $e->getMessage());
}

include '../../header.php';
?>

<div class="container mt-4">
    <h2><i class="fas fa-building"></i> Gestión de Proveedores</h2>
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#agregarProveedorModal">
        <i class="fas fa-plus"></i> Agregar Proveedor
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
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($proveedores as $proveedor): ?>
                <tr>
                    <td><?php echo $proveedor['id']; ?></td>
                    <td><?php echo htmlspecialchars($proveedor['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($proveedor['email']); ?></td>
                    <td><?php echo htmlspecialchars($proveedor['telefono']); ?></td>
                    <td><?php echo htmlspecialchars($proveedor['direccion']); ?></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editarProveedorModal"
                            data-id="<?php echo $proveedor['id']; ?>"
                            data-nombre="<?php echo htmlspecialchars($proveedor['nombre'], ENT_QUOTES); ?>"
                            data-email="<?php echo htmlspecialchars($proveedor['email'], ENT_QUOTES); ?>"
                            data-telefono="<?php echo htmlspecialchars($proveedor['telefono'], ENT_QUOTES); ?>"
                            data-direccion="<?php echo htmlspecialchars($proveedor['direccion'], ENT_QUOTES); ?>">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmarEliminarModal"
                            data-id="<?php echo $proveedor['id']; ?>"
                            data-nombre="<?php echo htmlspecialchars($proveedor['nombre'], ENT_QUOTES); ?>">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Agregar Proveedor -->
<div class="modal fade" id="agregarProveedorModal" tabindex="-1" aria-labelledby="agregarProveedorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="agregarProveedorModalLabel">Agregar Nuevo Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="telefono" name="telefono">
                    </div>
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <textarea class="form-control" id="direccion" name="direccion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agregar Proveedor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Proveedor -->
<div class="modal fade" id="editarProveedorModal" tabindex="-1" aria-labelledby="editarProveedorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarProveedorModalLabel">Editar Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="editarProveedorId" name="id" value="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editarNombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="editarNombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="editarEmail" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="editarEmail" name="email">
                    </div>
                    <div class="mb-3">
                        <label for="editarTelefono" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="editarTelefono" name="telefono">
                    </div>
                    <div class="mb-3">
                        <label for="editarDireccion" class="form-label">Dirección</label>
                        <textarea class="form-control" id="editarDireccion" name="direccion" rows="3"></textarea>
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

<!-- Modal Confirmar Eliminación -->
<div class="modal fade" id="confirmarEliminarModal" tabindex="-1" aria-labelledby="confirmarEliminarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmarEliminarModalLabel">Eliminar Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="eliminarProveedorId" name="id" value="">
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar el proveedor <strong id="eliminarProveedorNombre"></strong>?</p>
                    <p class="text-danger">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar proveedor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var editarProveedorModal = document.getElementById('editarProveedorModal');
        if (editarProveedorModal) {
            editarProveedorModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var nombre = button.getAttribute('data-nombre');
                var email = button.getAttribute('data-email');
                var telefono = button.getAttribute('data-telefono');
                var direccion = button.getAttribute('data-direccion');

                document.getElementById('editarProveedorId').value = id;
                document.getElementById('editarNombre').value = nombre;
                document.getElementById('editarEmail').value = email;
                document.getElementById('editarTelefono').value = telefono;
                document.getElementById('editarDireccion').value = direccion;
            });
        }

        var confirmarEliminarModal = document.getElementById('confirmarEliminarModal');
        if (confirmarEliminarModal) {
            confirmarEliminarModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var nombre = button.getAttribute('data-nombre');

                document.getElementById('eliminarProveedorId').value = id;
                document.getElementById('eliminarProveedorNombre').textContent = nombre;
            });
        }
    });
</script>

<?php include '../../footer.php'; ?>