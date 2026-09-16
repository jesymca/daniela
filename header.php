<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$applicationRoot = realpath(__DIR__);
$baseHref = '/';

if ($documentRoot && $applicationRoot) {
    $docRootNormalized = str_replace('\\', '/', $documentRoot);
    $appRootNormalized = str_replace('\\', '/', $applicationRoot);

    if (stripos($appRootNormalized, $docRootNormalized) === 0) {
        $baseHref = substr($appRootNormalized, strlen($docRootNormalized));
        $baseHref = '/' . trim($baseHref, '/') . '/';
        if ($baseHref === '//') {
            $baseHref = '/';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión - Michele C.A.</title>
    <base href="<?php echo htmlspecialchars($baseHref, ENT_QUOTES, 'UTF-8'); ?>">
    <!-- Bootstrap 5 CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="assets/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navegación -->
    <nav class="navbar navbar-expand-lg navbar-light bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><img src="image/logos.png" alt="Logo" width="300"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="inventarioDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-boxes"></i> Inventario
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="inventarioDropdown">
                                <li><a class="dropdown-item" href="modules/inventario/listar.php">Ver Productos</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="ventasDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-shopping-cart"></i> Ventas
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="ventasDropdown">
                                <li><a class="dropdown-item" href="modules/ventas/listar.php">Ver Ventas</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="comprasDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-truck"></i> Compras
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="comprasDropdown">
                                <li><a class="dropdown-item" href="modules/compras/listar.php">Ver Compras</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="clientesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-users"></i> Clientes
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="clientesDropdown">
                                <li><a class="dropdown-item" href="modules/clientes/listar.php">Ver Clientes</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="proveedoresDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-building"></i> Proveedores
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="proveedoresDropdown">
                                <li><a class="dropdown-item" href="modules/proveedores/listar.php">Ver Proveedores</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="otrosDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cogs"></i> Otros
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="otrosDropdown">
                                <li><a class="dropdown-item" href="modules/reportes/index.php">Reportes</a></li>
                                <li><a class="dropdown-item" href="modules/otros/mantenimiento.php">Mantenimiento de Base de Datos</a></li>
                                <li><a class="dropdown-item" href="modules/otros/about.php">Sobre El Sistema</a></li>
                                <li><a class="dropdown-item" href="modules/otros/manual.php">Manual de Uso</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="modules/usuarios/perfil.php"><i class="fas fa-user"></i> Perfil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Salir</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>