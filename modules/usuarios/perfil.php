<?php
// modules/usuarios/perfil.php - Perfil de usuario

require_once '../../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($nombre === '' || $email === '') {
        $error = 'Nombre y correo electrónico son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Correo electrónico no válido.';
    } else {
        // Verificar si el email ya existe en otro usuario
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND id != ?');
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $error = 'El correo electrónico ya está en uso.';
        } else {
            // Actualizar usuario
            $stmt = $pdo->prepare('UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?');
            if ($stmt->execute([$nombre, $email, $user_id])) {
                $_SESSION['user_name'] = $nombre;
                $_SESSION['user_email'] = $email;
                $success = 'Perfil actualizado correctamente.';
            } else {
                $error = 'Error al actualizar el perfil.';
            }
        }
    }
}

// Obtener datos actuales del usuario
$stmt = $pdo->prepare('SELECT nombre, email, rol, fecha_creacion FROM usuarios WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: ../../login.php');
    exit;
}

include '../../header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="text-center text-primary mb-4"><i class="fas fa-user"></i> Mi Perfil</h1>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fas fa-edit"></i> Editar Perfil</h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <form method="post" action="perfil.php">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars(ucfirst($user['rol'])); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha de creación</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($user['fecha_creacion']))); ?>" readonly>
                        </div>
                        <button type="submit" class="btn btn-primary">Actualizar Perfil</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../footer.php'; ?>