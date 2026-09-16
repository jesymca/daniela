<?php
// modules/clientes/listar.php - Listar clientes

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
    $tipo = trim($_POST['tipo'] ?? '');
    $rif_cedula = trim($_POST['rif_cedula'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if ($action === 'add' || $action === 'edit') {
        if ($tipo === '' || $rif_cedula === '' || $nombre === '') {
            $error = 'Tipo, RIF/Cédula y nombre son obligatorios.';
        } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'El correo electrónico no es válido.';
        } else {
            try {
                if ($action === 'add') {
                    $stmt = $pdo->prepare('INSERT INTO clientes (tipo, rif_cedula, nombre, email, telefono, direccion) VALUES (?, ?, ?, ?, ?, ?)');
                    $stmt->execute([$tipo, $rif_cedula, $nombre, $email, $telefono, $direccion]);
                    $success = 'Cliente agregado correctamente.';
                } else {
                    $id = intval($_POST['id'] ?? 0);
                    if ($id > 0) {
                        $stmt = $pdo->prepare('UPDATE clientes SET tipo = ?, rif_cedula = ?, nombre = ?, email = ?, telefono = ?, direccion = ? WHERE id = ?');
                        $stmt->execute([$tipo, $rif_cedula, $nombre, $email, $telefono, $direccion, $id]);
                        $success = 'Cliente actualizado correctamente.';
                    } else {
                        $error = 'ID de cliente inválido para edición.';
                    }
                }
            } catch (PDOException $e) {
                $error = ($action === 'add' ? 'Error al agregar el cliente: ' : 'Error al actualizar el cliente: ') . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare('DELETE FROM clientes WHERE id = ?');
                $stmt->execute([$id]);
                $success = 'Cliente eliminado correctamente.';
            } catch (PDOException $e) {
                $error = 'Error al eliminar el cliente: ' . $e->getMessage();
            }
        } else {
            $error = 'ID de cliente inválido para eliminación.';
        }
    }
}

try {
    $stmt = $pdo->query("SELECT * FROM clientes ORDER BY nombre ASC");
    $clientes = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al consultar clientes: " . $e->getMessage());
}

include '../../header.php';
?>

<div class="container mt-4 mb-5">
    <div class="row align-items-center mb-3">
        <div class="col-md-6">
            <h2 class="mb-0"><i class="fas fa-users text-primary me-2"></i> Gestión de Clientes</h2>
        </div>
        <div class="col-md-6 text-md-end mt-2 mt-md-0">
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#agregarClienteModal">
                <i class="fas fa-plus me-1"></i> Agregar Cliente
            </button>
        </div>
    </div>

    <!-- Barra de Búsqueda en Tiempo Real para Clientes -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-primary text-white"><i class="fas fa-search"></i></span>
                <input type="text" id="buscarClienteInput" class="form-control" placeholder="Buscar por Nombre o Cédula/RIF en tiempo real (ej: V-12545222 o solo 12545222)...">
                <button type="button" class="btn btn-outline-secondary" id="limpiarBusquedaCliente" title="Limpiar filtro">
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
                    <th>Tipo</th>
                    <th>RIF/Cédula</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $cliente): ?>
                <tr>
                    <td><?php echo $cliente['id']; ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($cliente['tipo'])); ?></td>
                    <td><?php echo htmlspecialchars($cliente['rif_cedula']); ?></td>
                    <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                    <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                    <td><?php echo htmlspecialchars($cliente['direccion']); ?></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editarClienteModal"
                            data-id="<?php echo $cliente['id']; ?>"
                            data-tipo="<?php echo htmlspecialchars($cliente['tipo'], ENT_QUOTES); ?>"
                            data-rif_cedula="<?php echo htmlspecialchars($cliente['rif_cedula'], ENT_QUOTES); ?>"
                            data-nombre="<?php echo htmlspecialchars($cliente['nombre'], ENT_QUOTES); ?>"
                            data-email="<?php echo htmlspecialchars($cliente['email'], ENT_QUOTES); ?>"
                            data-telefono="<?php echo htmlspecialchars($cliente['telefono'], ENT_QUOTES); ?>"
                            data-direccion="<?php echo htmlspecialchars($cliente['direccion'], ENT_QUOTES); ?>">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmarEliminarClienteModal"
                            data-id="<?php echo $cliente['id']; ?>"
                            data-nombre="<?php echo htmlspecialchars($cliente['nombre'], ENT_QUOTES); ?>">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Agregar Cliente -->
<div class="modal fade" id="agregarClienteModal" tabindex="-1" aria-labelledby="agregarClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="agregarClienteModalLabel">Agregar Nuevo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo *</label>
                        <select class="form-control" id="tipo" name="tipo" required>
                            <option value="persona">Persona</option>
                            <option value="empresa">Empresa</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="rif_cedula" class="form-label">RIF / Cédula *</label>
                        <input type="text" class="form-control" id="rif_cedula" name="rif_cedula" required placeholder="V-12345678 o J-123456789">
                    </div>
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
                    <button type="submit" class="btn btn-primary">Agregar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Cliente -->
<div class="modal fade" id="editarClienteModal" tabindex="-1" aria-labelledby="editarClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarClienteModalLabel">Editar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="editarClienteId" name="id" value="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editarTipo" class="form-label">Tipo *</label>
                        <select class="form-control" id="editarTipo" name="tipo" required>
                            <option value="persona">Persona</option>
                            <option value="empresa">Empresa</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editarRifCedula" class="form-label">RIF / Cédula *</label>
                        <input type="text" class="form-control" id="editarRifCedula" name="rif_cedula" required placeholder="V-12345678 o J-123456789">
                    </div>
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
<div class="modal fade" id="confirmarEliminarClienteModal" tabindex="-1" aria-labelledby="confirmarEliminarClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmarEliminarClienteModalLabel">Eliminar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="eliminarClienteId" name="id" value="">
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar el cliente <strong id="eliminarClienteNombre"></strong>?</p>
                    <p class="text-danger">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var editarClienteModal = document.getElementById('editarClienteModal');
        if (editarClienteModal) {
            editarClienteModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var tipo = button.getAttribute('data-tipo');
                var rif_cedula = button.getAttribute('data-rif_cedula');
                var nombre = button.getAttribute('data-nombre');
                var email = button.getAttribute('data-email');
                var telefono = button.getAttribute('data-telefono');
                var direccion = button.getAttribute('data-direccion');

                document.getElementById('editarClienteId').value = id;
                document.getElementById('editarTipo').value = tipo;
                document.getElementById('editarRifCedula').value = rif_cedula;
                document.getElementById('editarNombre').value = nombre;
                document.getElementById('editarEmail').value = email;
                document.getElementById('editarTelefono').value = telefono;
                document.getElementById('editarDireccion').value = direccion;
            });
        }

        var confirmarEliminarClienteModal = document.getElementById('confirmarEliminarClienteModal');
        if (confirmarEliminarClienteModal) {
            confirmarEliminarClienteModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var nombre = button.getAttribute('data-nombre');

                document.getElementById('eliminarClienteId').value = id;
                document.getElementById('eliminarClienteNombre').textContent = nombre;
            });
        }

        /**
         * ====================================================================
         * FILTRADO EN TIEMPO REAL: Clientes (Nombre y Cédula/RIF Flexible)
         * ====================================================================
         * Permite búsquedas completas (ej. V-12545222), sin prefijo (ej. 12545222)
         * o por nombre de cliente.
         */
        var buscarClienteInput = document.getElementById('buscarClienteInput');
        var limpiarClienteBtn = document.getElementById('limpiarBusquedaCliente');
        var tablaClientes = document.querySelector('.table-responsive table');

        if (buscarClienteInput && tablaClientes) {
            var tbodyClientes = tablaClientes.querySelector('tbody');
            var filasClientes = tbodyClientes ? tbodyClientes.querySelectorAll('tr') : [];

            function filtrarClientes() {
                var queryRaw = buscarClienteInput.value.toLowerCase().trim();
                var queryAlphaNum = queryRaw.replace(/[^0-9a-z]/g, '');
                var queryDigits = queryRaw.replace(/[^0-9]/g, '');
                var visibles = 0;

                filasClientes.forEach(function(row) {
                    if (row.id === 'noClientesRow') return;

                    var ctds = row.querySelectorAll('td');
                    if (ctds.length < 4) return;

                    var rifCedulaRaw = (ctds[2].textContent || '').trim();
                    var nombreRaw = (ctds[3].textContent || '').trim();

                    var rifLower = rifCedulaRaw.toLowerCase();
                    var nombreLower = nombreRaw.toLowerCase();

                    var isMatch = false;

                    if (!queryRaw) {
                        isMatch = true;
                    } else if (nombreLower.includes(queryRaw) || rifLower.includes(queryRaw)) {
                        isMatch = true;
                    } else {
                        // Comparar limpiando caracteres especiales (V-12545222 -> v12545222)
                        var rifAlphaNum = rifLower.replace(/[^0-9a-z]/g, '');
                        if (queryAlphaNum && rifAlphaNum.includes(queryAlphaNum)) {
                            isMatch = true;
                        } else {
                            // Comparar solo dígitos (V-12545222 -> 12545222)
                            var rifDigits = rifLower.replace(/[^0-9]/g, '');
                            if (queryDigits && rifDigits.includes(queryDigits)) {
                                isMatch = true;
                            }
                        }
                    }

                    if (isMatch) {
                        row.style.display = '';
                        visibles++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                var noRow = document.getElementById('noClientesRow');
                if (visibles === 0 && queryRaw !== '') {
                    if (!noRow) {
                        noRow = document.createElement('tr');
                        noRow.id = 'noClientesRow';
                        noRow.innerHTML = '<td colspan="8" class="text-center text-muted py-4"><i class="fas fa-search me-2 text-warning"></i>No se encontraron clientes que coincidan con "<strong>' + document.createTextNode(buscarClienteInput.value).textContent + '</strong>".</td>';
                        tbodyClientes.appendChild(noRow);
                    } else {
                        noRow.style.display = '';
                        noRow.querySelector('td').innerHTML = '<i class="fas fa-search me-2 text-warning"></i>No se encontraron clientes que coincidan con "<strong>' + document.createTextNode(buscarClienteInput.value).textContent + '</strong>".';
                    }
                } else if (noRow) {
                    noRow.style.display = 'none';
                }
            }

            buscarClienteInput.addEventListener('input', filtrarClientes);
            if (limpiarClienteBtn) {
                limpiarClienteBtn.addEventListener('click', function() {
                    buscarClienteInput.value = '';
                    filtrarClientes();
                    buscarClienteInput.focus();
                });
            }
        }
    });
</script>

<?php include '../../footer.php'; ?>