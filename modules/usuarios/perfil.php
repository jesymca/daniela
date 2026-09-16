<?php
/**
 * ============================================================================
 * PROYECTO ACADÉMICO - SISTEMA DE GESTIÓN ADMINISTRATIVO MICHELE C.A.
 * Archivo: modules/usuarios/perfil.php
 * Descripción: Gestión del perfil personal y panel de administración de usuarios
 *              con asignación de Roles (RBAC: Administrador, Vendedor, Almacenista).
 * 
 * Conceptos Académicos Clave:
 *  1. Control de Acceso Basado en Roles (RBAC - Role-Based Access Control):
 *     Restringe la creación y modificación de usuarios exclusivamente a las cuentas
 *     con rol de Administrador ($_SESSION['user_rol'] === 'admin').
 *  2. Hashing de Contraseñas (BCrypt):
 *     Las contraseñas de nuevas cuentas se procesan con password_hash($clave, PASSWORD_BCRYPT),
 *     garantizando el resguardo de credenciales.
 *  3. Inclusión de Botón Ojito (Toggle Password):
 *     Todos los campos de contraseña en la interfaz integran el botón toggle interactivo.
 * ============================================================================
 */

require_once __DIR__ . '/../../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Control de Acceso General: Redirigir al login si no existe sesión válida
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_rol_actual = strtolower($_SESSION['user_rol'] ?? 'usuario');
$isAdmin = ($user_rol_actual === 'admin');

$error = '';
$success = '';

// Procesar formularios POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update_profile';

    // ------------------------------------------------------------------------
    // ACCIÓN 1: Actualizar Perfil Personal del Usuario Autenticado
    // ------------------------------------------------------------------------
    if ($action === 'update_profile') {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($nombre === '' || $email === '') {
            $error = 'Nombre y correo electrónico son obligatorios.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'El correo electrónico ingresado no es válido.';
        } else {
            // Verificar duplicidad de email en otra cuenta
            $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND id != ?');
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetch()) {
                $error = 'El correo electrónico ya está en uso por otra cuenta.';
            } else {
                $updatePassword = false;
                $hashedPassword = null;

                if ($current_password !== '' || $new_password !== '' || $confirm_password !== '') {
                    $stmtClave = $pdo->prepare('SELECT password FROM usuarios WHERE id = ?');
                    $stmtClave->execute([$user_id]);
                    $userDb = $stmtClave->fetch();

                    if (!$userDb || (!password_verify($current_password, $userDb['password']) && $current_password !== $userDb['password'])) {
                        $error = 'La contraseña actual ingresada es incorrecta.';
                    } elseif (strlen($new_password) < 6) {
                        $error = 'La nueva contraseña debe tener al menos 6 caracteres.';
                    } elseif ($new_password !== $confirm_password) {
                        $error = 'La nueva contraseña y su confirmación no coinciden.';
                    } else {
                        $updatePassword = true;
                        $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);
                    }
                }

                if (empty($error)) {
                    if ($updatePassword) {
                        $stmtUpdate = $pdo->prepare('UPDATE usuarios SET nombre = ?, email = ?, password = ? WHERE id = ?');
                        $executed = $stmtUpdate->execute([$nombre, $email, $hashedPassword, $user_id]);
                    } else {
                        $stmtUpdate = $pdo->prepare('UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?');
                        $executed = $stmtUpdate->execute([$nombre, $email, $user_id]);
                    }

                    if ($executed) {
                        $_SESSION['user_name'] = $nombre;
                        $_SESSION['user_email'] = $email;
                        $success = 'Perfil actualizado correctamente' . ($updatePassword ? ' (contraseña modificada).' : '.');
                    } else {
                        $error = 'Error interno al actualizar el perfil.';
                    }
                }
            }
        }
    }

    // ------------------------------------------------------------------------
    // ACCIÓN 2: Crear Nuevo Usuario y Asignar Rol (EXCLUSIVO PARA ADMIN)
    // ------------------------------------------------------------------------
    elseif ($action === 'create_user' && $isAdmin) {
        $nuevo_nombre = trim($_POST['new_user_name'] ?? '');
        $nuevo_email = trim($_POST['new_user_email'] ?? '');
        $nuevo_password = $_POST['new_user_password'] ?? '';
        $nuevo_rol = strtolower(trim($_POST['new_user_rol'] ?? 'vendedor'));

        $rolesPermitidos = ['admin', 'vendedor', 'almacenista', 'usuario'];

        if ($nuevo_nombre === '' || $nuevo_email === '' || $nuevo_password === '') {
            $error = 'Todos los campos son obligatorios para crear un nuevo usuario.';
        } elseif (!filter_var($nuevo_email, FILTER_VALIDATE_EMAIL)) {
            $error = 'El correo electrónico del nuevo usuario no es válido.';
        } elseif (strlen($nuevo_password) < 6) {
            $error = 'La contraseña inicial debe tener al menos 6 caracteres.';
        } elseif (!in_array($nuevo_rol, $rolesPermitidos)) {
            $error = 'El rol seleccionado no es válido.';
        } else {
            // Verificar si el correo ya existe
            $stmtCheck = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
            $stmtCheck->execute([$nuevo_email]);
            if ($stmtCheck->fetch()) {
                $error = 'El correo electrónico "' . htmlspecialchars($nuevo_email) . '" ya pertenece a un usuario registrado.';
            } else {
                /**
                 * Encriptación BCrypt para Nuevos Usuarios:
                 * Se genera el hash de la contraseña usando password_hash().
                 */
                $hashedNewPass = password_hash($nuevo_password, PASSWORD_BCRYPT);
                $stmtInsert = $pdo->prepare('INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)');
                if ($stmtInsert->execute([$nuevo_nombre, $nuevo_email, $hashedNewPass, $nuevo_rol])) {
                    $success = 'Usuario "' . htmlspecialchars($nuevo_nombre) . '" registrado exitosamente con el rol de ' . strtoupper($nuevo_rol) . '.';
                } else {
                    $error = 'Error al registrar el nuevo usuario en la base de datos.';
                }
            }
        }
    }

    // ------------------------------------------------------------------------
    // ACCIÓN 3: Cambiar Rol de Usuario Existente (EXCLUSIVO PARA ADMIN)
    // ------------------------------------------------------------------------
    elseif ($action === 'change_role' && $isAdmin) {
        $target_user_id = intval($_POST['target_user_id'] ?? 0);
        $target_user_rol = strtolower(trim($_POST['target_user_rol'] ?? 'vendedor'));

        if ($target_user_id > 0 && in_array($target_user_rol, ['admin', 'vendedor', 'almacenista', 'usuario'])) {
            $stmtUpdateRole = $pdo->prepare('UPDATE usuarios SET rol = ? WHERE id = ?');
            if ($stmtUpdateRole->execute([$target_user_rol, $target_user_id])) {
                $success = 'Rol de usuario actualizado correctamente.';
            } else {
                $error = 'Error al intentar actualizar el rol del usuario.';
            }
        }
    }

    // ------------------------------------------------------------------------
    // ACCIÓN 4: Editar Datos Completos de Usuario Vía Modal (EXCLUSIVO ADMIN)
    // ------------------------------------------------------------------------
    elseif ($action === 'edit_user' && $isAdmin) {
        $target_user_id = intval($_POST['target_user_id'] ?? 0);
        $edit_nombre = trim($_POST['edit_user_name'] ?? '');
        $edit_email = trim($_POST['edit_user_email'] ?? '');
        $edit_rol = strtolower(trim($_POST['edit_user_rol'] ?? 'vendedor'));
        $edit_password = $_POST['edit_user_password'] ?? '';

        $rolesPermitidos = ['admin', 'vendedor', 'almacenista', 'usuario'];

        if ($target_user_id <= 0 || $edit_nombre === '' || $edit_email === '') {
            $error = 'Nombre y correo electrónico son obligatorios.';
        } elseif (!filter_var($edit_email, FILTER_VALIDATE_EMAIL)) {
            $error = 'El correo electrónico ingresado no es válido.';
        } elseif (!in_array($edit_rol, $rolesPermitidos)) {
            $error = 'El rol seleccionado no es válido.';
        } else {
            // Verificar si el correo ya existe en otro usuario diferente
            $stmtCheck = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND id != ?');
            $stmtCheck->execute([$edit_email, $target_user_id]);
            if ($stmtCheck->fetch()) {
                $error = 'El correo electrónico "' . htmlspecialchars($edit_email) . '" ya pertenece a otro usuario registrado.';
            } else {
                $updatePassword = false;
                $hashedPass = null;

                if ($edit_password !== '') {
                    if (strlen($edit_password) < 6) {
                        $error = 'La nueva contraseña debe tener al menos 6 caracteres.';
                    } else {
                        $updatePassword = true;
                        $hashedPass = password_hash($edit_password, PASSWORD_BCRYPT);
                    }
                }

                if (empty($error)) {
                    if ($updatePassword) {
                        $stmtUpd = $pdo->prepare('UPDATE usuarios SET nombre = ?, email = ?, rol = ?, password = ? WHERE id = ?');
                        $executed = $stmtUpd->execute([$edit_nombre, $edit_email, $edit_rol, $hashedPass, $target_user_id]);
                    } else {
                        $stmtUpd = $pdo->prepare('UPDATE usuarios SET nombre = ?, email = ?, rol = ? WHERE id = ?');
                        $executed = $stmtUpd->execute([$edit_nombre, $edit_email, $edit_rol, $target_user_id]);
                    }

                    if ($executed) {
                        if ($target_user_id === $user_id) {
                            $_SESSION['user_name'] = $edit_nombre;
                            $_SESSION['user_email'] = $edit_email;
                            $_SESSION['user_rol'] = $edit_rol;
                        }
                        $success = 'Datos del usuario "' . htmlspecialchars($edit_nombre) . '" actualizados correctamente.';
                    } else {
                        $error = 'Error al actualizar la información del usuario en la base de datos.';
                    }
                }
            }
        }
    }

    // ------------------------------------------------------------------------
    // ACCIÓN 5: Eliminar Usuario (EXCLUSIVO PARA ADMIN)
    // ------------------------------------------------------------------------
    elseif ($action === 'delete_user' && $isAdmin) {
        $target_user_id = intval($_POST['target_user_id'] ?? 0);

        if ($target_user_id === $user_id) {
            $error = 'No puede eliminar su propia cuenta de usuario en sesión activa.';
        } elseif ($target_user_id > 0) {
            $stmtDelete = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
            if ($stmtDelete->execute([$target_user_id])) {
                $success = 'Usuario eliminado del sistema correctamente.';
            } else {
                $error = 'Error al intentar eliminar el usuario.';
            }
        }
    }
}

// Obtener datos actualizados del usuario autenticado
$stmt = $pdo->prepare('SELECT nombre, email, rol, fecha_creacion FROM usuarios WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: ../../login.php');
    exit;
}

// Si es Admin, consultar lista de todos los usuarios registrados
$listaUsuarios = [];
if ($isAdmin) {
    $stmtTodos = $pdo->query('SELECT id, nombre, email, rol, fecha_creacion FROM usuarios ORDER BY fecha_creacion DESC');
    $listaUsuarios = $stmtTodos->fetchAll();
}

include __DIR__ . '/../../header.php';
?>

<div class="container-fluid mt-4 mb-5">
    <div class="row">
        <div class="col-12 text-center mb-4">
            <h1 class="text-primary fw-bold"><i class="fas fa-user-circle"></i> Mi Perfil y Gestión de Usuarios</h1>
            <p class="text-muted">Administración de credenciales personales y asignación de roles del sistema.</p>
        </div>
    </div>

    <!-- Alertas Globales -->
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

    <div class="row justify-content-center">
        <!-- SECCIÓN 1: Editar Perfil Personal (Disponible para TODOS los usuarios) -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i> Mi Perfil Personal</h5>
                </div>
                <div class="card-body p-4">
                    <form method="post" action="modules/usuarios/perfil.php">
                        <input type="hidden" name="action" value="update_profile">

                        <h6 class="text-secondary mb-3 border-bottom pb-2"><i class="fas fa-id-card me-1"></i> Datos Personales</h6>
                        
                        <div class="mb-3">
                            <label for="nombre" class="form-label font-weight-bold">Nombre Completo</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" id="nombre" name="nombre" class="form-control" value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label font-weight-bold">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label font-weight-bold">Rol Asignado</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-shield"></i></span>
                                    <input type="text" class="form-control bg-light fw-bold text-uppercase" value="<?php echo htmlspecialchars($user['rol']); ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold">Fecha de Registro</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($user['fecha_creacion']))); ?>" readonly>
                            </div>
                        </div>

                        <!-- Sección Cambio de Clave Personal con Ojito -->
                        <h6 class="text-secondary mb-3 border-bottom pb-2 mt-4"><i class="fas fa-key me-1"></i> Cambiar Mi Contraseña</h6>
                        <p class="small text-muted mb-3">Deje estos campos en blanco si no desea modificar su contraseña actual.</p>

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Contraseña Actual</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Su contraseña actual">
                                <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="#current_password" title="Mostrar contraseña">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nueva Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Mínimo 6 caracteres">
                                <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="#new_password" title="Mostrar contraseña">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-check-double"></i></span>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Repita la nueva contraseña">
                                <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="#confirm_password" title="Mostrar contraseña">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-2"><i class="fas fa-save me-1"></i> Actualizar Mis Datos</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 2: Crear Nuevo Usuario con Rol (EXCLUSIVO ADMINISTRADOR) -->
        <?php if ($isAdmin): ?>
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-dark text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-user-plus me-2 text-warning"></i> Registrar Nuevo Usuario y Asignar Rol</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="small text-muted mb-3">
                            Como Administrador, puede dar de alta nuevos usuarios y asignarles permisos específicos de <strong>Vendedor</strong> o <strong>Almacenista</strong>.
                        </p>
                        <form method="post" action="modules/usuarios/perfil.php">
                            <input type="hidden" name="action" value="create_user">

                            <div class="mb-3">
                                <label for="new_user_name" class="form-label font-weight-bold">Nombre Completo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-plus"></i></span>
                                    <input type="text" id="new_user_name" name="new_user_name" class="form-control" placeholder="Ej. Carlos Pérez" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="new_user_email" class="form-label font-weight-bold">Correo Electrónico (Usuario)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" id="new_user_email" name="new_user_email" class="form-control" placeholder="carlos@micheleca.com" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="new_user_password" class="form-label font-weight-bold">Contraseña Inicial</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" id="new_user_password" name="new_user_password" class="form-control" placeholder="Mínimo 6 caracteres" required>
                                    <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="#new_user_password" title="Mostrar contraseña">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="new_user_rol" class="form-label font-weight-bold">Asignación de Rol</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-shield"></i></span>
                                    <select id="new_user_rol" name="new_user_rol" class="form-select fw-bold text-primary" required>
                                        <option value="vendedor" selected>Vendedor (Facturación, Clientes y Catálogo)</option>
                                        <option value="almacenista">Almacenista (Inventario, Compras y Proveedores)</option>
                                        <option value="admin">Administrador (Acceso Total y Mantenimiento)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success py-2"><i class="fas fa-check-circle me-1"></i> Crear Usuario con Rol</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- SECCIÓN 3: Lista de Usuarios Registrados y Control de Roles (EXCLUSIVO ADMIN) -->
    <?php if ($isAdmin): ?>
        <div class="row mt-3">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-users-cog me-2"></i> Usuarios Registrados y Control de Roles</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Correo Electrónico</th>
                                        <th>Rol de Acceso</th>
                                        <th>Fecha de Registro</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($listaUsuarios as $usr): ?>
                                        <tr>
                                            <td><strong>#<?php echo $usr['id']; ?></strong></td>
                                            <td><i class="fas fa-user me-2 text-secondary"></i><?php echo htmlspecialchars($usr['nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($usr['email']); ?></td>
                                            <td>
                                                <?php
                                                $r = strtolower($usr['rol']);
                                                if ($r === 'admin') {
                                                    echo '<span class="badge bg-danger"><i class="fas fa-user-shield me-1"></i> Administrador</span>';
                                                } elseif ($r === 'vendedor') {
                                                    echo '<span class="badge bg-success"><i class="fas fa-user-tag me-1"></i> Vendedor</span>';
                                                } elseif ($r === 'almacenista') {
                                                    echo '<span class="badge bg-info text-dark"><i class="fas fa-boxes me-1"></i> Almacenista</span>';
                                                } else {
                                                    echo '<span class="badge bg-secondary"><i class="fas fa-user me-1"></i> Usuario</span>';
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($usr['fecha_creacion']))); ?></td>
                                            <td class="text-end">
                                                <!-- Botón para Abrir Modal de Edición Completa -->
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-primary me-1" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editUserModal"
                                                        data-id="<?php echo $usr['id']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($usr['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-email="<?php echo htmlspecialchars($usr['email'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-rol="<?php echo htmlspecialchars($usr['rol'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        title="Editar datos del usuario">
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>

                                                <!-- Formulario rápido para cambiar Rol -->
                                                <form method="post" action="modules/usuarios/perfil.php" style="display:inline-block;" class="me-1">
                                                    <input type="hidden" name="action" value="change_role">
                                                    <input type="hidden" name="target_user_id" value="<?php echo $usr['id']; ?>">
                                                    <select name="target_user_rol" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()" title="Cambiar Rol Rápido">
                                                        <option value="vendedor" <?php echo $r === 'vendedor' ? 'selected' : ''; ?>>Vendedor</option>
                                                        <option value="almacenista" <?php echo $r === 'almacenista' ? 'selected' : ''; ?>>Almacenista</option>
                                                        <option value="admin" <?php echo $r === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                                    </select>
                                                </form>

                                                <!-- Formulario para Eliminar Usuario (No permite auto-eliminación) -->
                                                <?php if ($usr['id'] != $user_id): ?>
                                                    <form method="post" action="modules/usuarios/perfil.php" style="display:inline-block;" onsubmit="return confirm('¿Está seguro de eliminar al usuario <?php echo htmlspecialchars($usr['nombre']); ?>?');">
                                                        <input type="hidden" name="action" value="delete_user">
                                                        <input type="hidden" name="target_user_id" value="<?php echo $usr['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar Usuario">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border ms-1">Sesión Activa</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ============================================================================ -->
<!-- MODAL DE EDICIÓN DE USUARIO Y ROL (Bootstrap 5) -->
<!-- ============================================================================ -->
<?php if ($isAdmin): ?>
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editUserModalLabel"><i class="fas fa-user-edit me-2"></i> Editar Datos de Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="modules/usuarios/perfil.php">
                    <div class="modal-body p-4">
                        <input type="hidden" name="action" value="edit_user">
                        <input type="hidden" name="target_user_id" id="modal_edit_user_id">

                        <div class="mb-3">
                            <label for="modal_edit_user_name" class="form-label font-weight-bold">Nombre Completo</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" id="modal_edit_user_name" name="edit_user_name" class="form-control" required placeholder="Nombre completo del usuario">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="modal_edit_user_email" class="form-label font-weight-bold">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" id="modal_edit_user_email" name="edit_user_email" class="form-control" required placeholder="correo@ejemplo.com">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="modal_edit_user_rol" class="form-label font-weight-bold">Rol del Usuario</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-shield"></i></span>
                                <select id="modal_edit_user_rol" name="edit_user_rol" class="form-select fw-bold text-primary" required>
                                    <option value="vendedor">Vendedor (Facturación, Clientes y Catálogo)</option>
                                    <option value="almacenista">Almacenista (Inventario, Compras y Proveedores)</option>
                                    <option value="admin">Administrador (Acceso Total y Mantenimiento)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="modal_edit_user_password" class="form-label font-weight-bold">Nueva Contraseña (Opcional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                <input type="password" id="modal_edit_user_password" name="edit_user_password" class="form-control" placeholder="Dejar en blanco para conservar la actual">
                                <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="#modal_edit_user_password" title="Mostrar contraseña">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Ingrese al menos 6 caracteres solo si desea restablecer la contraseña.</small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript para Carga Dinámica de Datos en el Modal de Edición -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var editUserModal = document.getElementById('editUserModal');
        if (editUserModal) {
            editUserModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var userId = button.getAttribute('data-id');
                var userName = button.getAttribute('data-nombre');
                var userEmail = button.getAttribute('data-email');
                var userRol = button.getAttribute('data-rol');

                document.getElementById('modal_edit_user_id').value = userId || '';
                document.getElementById('modal_edit_user_name').value = userName || '';
                document.getElementById('modal_edit_user_email').value = userEmail || '';
                document.getElementById('modal_edit_user_rol').value = userRol || 'vendedor';
                document.getElementById('modal_edit_user_password').value = '';
            });
        }
    });
    </script>
<?php endif; ?>

<?php include __DIR__ . '/../../footer.php'; ?>