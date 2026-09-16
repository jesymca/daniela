<?php
/**
 * ============================================================================
 * PROYECTO ACADÉMICO - SISTEMA DE GESTIÓN ADMINISTRATIVO MICHELE C.A.
 * Archivo: login.php
 * Descripción: Módulo de autenticación de usuarios.
 * Conceptos aplicados:
 *  - Control de acceso basado en sesiones PHP ($_SESSION).
 *  - Consultas preparadas PDO para prevenir Inyección SQL (SQL Injection).
 *  - Verificación segura de contraseñas con password_verify() (BCrypt hash).
 *  - Sanitización de salidas para prevenir Cross-Site Scripting (XSS).
 * ============================================================================
 */

session_start();
require_once 'config.php';

// Si el usuario ya cuenta con una sesión activa, se redirige directamente al Dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$email = '';

// Procesamiento del formulario de inicio de sesión mediante el método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitización básica de entradas recibidas
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Ingrese su correo electrónico y contraseña.';
    } else {
        /**
         * Prevención de Inyección SQL:
         * Se utiliza una sentencia preparada en PDO. Las variables del usuario nunca se concatenan
         * directamente en la consulta SQL, evitando la ejecución de código SQL malicioso.
         */
        $stmt = $pdo->prepare('SELECT id, nombre, email, password, rol FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        /**
         * Verificación de credenciales:
         * 1. password_verify(): Compara la contraseña en texto plano recibida contra el hash almacenado.
         * 2. ($password === $user['password']): Permite compatibilidad temporal con registros antiguos en texto plano.
         */
        if ($user && (password_verify($password, $user['password']) || $password === $user['password'])) {
            // Inicialización de variables de sesión para persistencia de autenticación
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_rol'] = $user['rol'];

            header('Location: index.php');
            exit;
        }

        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Michele C.A.</title>
    <!-- Assets Locales: Garantizan compatibilidad offline en entornos locales XAMPP / Windows -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body text-center p-4">
                        <img src="./image/logo_m.jpeg" alt="Logo Michele C.A." class="img-fluid mb-3" style="max-height: 180px; object-fit: contain;">
                        <h2 class="card-title text-center mb-4 text-primary">Iniciar Sesión</h2>

                        <!-- Mensaje de error sanitizado con htmlspecialchars para evitar XSS -->
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <form method="post" action="login.php" novalidate>
                            <div class="mb-3 text-start">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" placeholder="ejemplo@micheleca.com" required>
                                </div>
                            </div>

                            <!-- Campo de Contraseña con Botón Toggle ("Ojito") -->
                            <div class="mb-4 text-start">
                                <label for="password" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" id="password" name="password" class="form-control" placeholder="Ingrese su contraseña" required>
                                    <!-- Botón interactivo para alternar visibilidad de contraseña -->
                                    <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="#password" title="Mostrar contraseña" aria-label="Mostrar contraseña">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2"><i class="fas fa-sign-in-alt me-1"></i> Entrar al Sistema</button>
                        </form>
                    </div>
                </div>
                <p class="text-center text-muted mt-3">Acceda con su usuario para ver el dashboard del sistema.</p>
            </div>
        </div>
    </div>

    <!-- Scripts de Bootstrap y funcionalidad personalizada del Ojito -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>

