<?php
/**
 * ============================================================================
 * PROYECTO ACADÉMICO - SISTEMA DE GESTIÓN ADMINISTRATIVO MICHELE C.A.
 * Archivo: header.php
 * Descripción: Encabezado universal HTML5 y barra de navegación dinámicamente
 *              filtrada mediante Control de Acceso Basado en Roles (RBAC).
 * 
 * Conceptos Académicos Clave:
 *  1. Control de Acceso Basado en Roles (RBAC - Role-Based Access Control):
 *     La interfaz se adapta según el rol asignado ($_SESSION['user_rol']).
 *     - Administrador ('admin'): Acceso completo a todos los módulos y mantenimiento.
 *     - Vendedor ('vendedor'): Enfoque comercial en ventas, clientes y catálogo.
 *     - Almacenista ('almacenista'): Enfoque logístico en compras, inventario y proveedores.
 *  2. Resolución Dinámica de Ruta Base (<base href>):
 *     Garantiza la correcta carga de assets y enlaces en cualquier entorno XAMPP/Linux.
 * ============================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$applicationRoot = realpath(__DIR__);
$baseHref = '/';

if ($documentRoot && $applicationRoot) {
    // Normalizar separadores de barra (Windows \ -> /)
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

// Obtener rol del usuario autenticado (por defecto 'vendedor' o 'usuario')
$userRol = strtolower($_SESSION['user_rol'] ?? 'usuario');
$userName = $_SESSION['user_name'] ?? 'Usuario';

// Personalizar paleta de colores de la barra de navegación según el rol
$navBgClass = 'bg-primary';
$navStyle = 'background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;';
$badgeClass = 'bg-white text-primary fw-bold';
$roleNameText = 'Usuario';
$roleIcon = 'fa-user';

if ($userRol === 'admin') {
    $navBgClass = 'bg-danger';
    $navStyle = 'background: linear-gradient(135deg, #dc3545 0%, #921c27 100%) !important;';
    $badgeClass = 'bg-white text-danger fw-bold';
    $roleNameText = 'Administrador';
    $roleIcon = 'fa-user-shield';
} elseif ($userRol === 'vendedor') {
    $navBgClass = 'bg-success';
    $navStyle = 'background: linear-gradient(135deg, #198754 0%, #0e4e30 100%) !important;';
    $badgeClass = 'bg-white text-success fw-bold';
    $roleNameText = 'Vendedor';
    $roleIcon = 'fa-user-tag';
} elseif ($userRol === 'almacenista') {
    $navBgClass = 'bg-info';
    $navStyle = 'background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;';
    $badgeClass = 'bg-white text-dark fw-bold';
    $roleNameText = 'Almacenista';
    $roleIcon = 'fa-boxes';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión - Michele C.A.</title>
    <!-- Etiqueta Base Href para resolución relativa de rutas en XAMPP -->
    <base href="<?php echo htmlspecialchars($baseHref, ENT_QUOTES, 'UTF-8'); ?>">
    
    <!-- Hojas de Estilos Locales (Garantiza compatibilidad sin conexión a Internet) -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Barra de Navegación Principal Bootstrap 5 con Colores por Rol -->
    <nav class="navbar navbar-expand-lg navbar-dark <?php echo $navBgClass; ?> shadow-sm" style="<?php echo $navStyle; ?>">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="image/logos.png" alt="Logo Michele C.A." height="40" class="d-inline-block align-text-top bg-white rounded p-1 me-2" style="object-fit: contain;">
                <span class="fw-bold">Michele C.A.</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Inicio / Dashboard (Disponible para todos los roles) -->
                        <li class="nav-item">
                            <a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a>
                        </li>

                        <!-- Módulo Inventario: Admin, Almacenista y Vendedor (catálogo) -->
                        <?php if (in_array($userRol, ['admin', 'almacenista', 'vendedor', 'usuario'])): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="inventarioDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-boxes me-1"></i> Inventario
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="inventarioDropdown">
                                    <li><a class="dropdown-item" href="modules/inventario/listar.php"><i class="fas fa-list me-2"></i>Ver Productos</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>

                        <!-- Módulo Ventas: Admin y Vendedor -->
                        <?php if (in_array($userRol, ['admin', 'vendedor', 'usuario'])): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="ventasDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-shopping-cart me-1"></i> Ventas
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="ventasDropdown">
                                    <li><a class="dropdown-item" href="modules/ventas/listar.php"><i class="fas fa-receipt me-2"></i>Ver Ventas</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>

                        <!-- Módulo Compras: Admin y Almacenista -->
                        <?php if (in_array($userRol, ['admin', 'almacenista'])): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="comprasDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-truck me-1"></i> Compras
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="comprasDropdown">
                                    <li><a class="dropdown-item" href="modules/compras/listar.php"><i class="fas fa-file-invoice me-2"></i>Ver Compras</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>

                        <!-- Módulo Clientes: Admin y Vendedor -->
                        <?php if (in_array($userRol, ['admin', 'vendedor', 'usuario'])): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="clientesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-users me-1"></i> Clientes
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="clientesDropdown">
                                    <li><a class="dropdown-item" href="modules/clientes/listar.php"><i class="fas fa-user-friends me-2"></i>Ver Clientes</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>

                        <!-- Módulo Proveedores: Admin y Almacenista -->
                        <?php if (in_array($userRol, ['admin', 'almacenista'])): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="proveedoresDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-building me-1"></i> Proveedores
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="proveedoresDropdown">
                                    <li><a class="dropdown-item" href="modules/proveedores/listar.php"><i class="fas fa-store me-2"></i>Ver Proveedores</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>

                        <!-- Módulo Otros: Opciones generales y administrativas -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="otrosDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cogs me-1"></i> Otros
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="otrosDropdown">
                                <?php if ($userRol === 'admin'): ?>
                                    <li><a class="dropdown-item" href="modules/reportes/index.php"><i class="fas fa-chart-bar me-2"></i>Reportes Gerenciales</a></li>
                                    <li><a class="dropdown-item" href="modules/otros/mantenimiento.php"><i class="fas fa-database me-2"></i>Mantenimiento de BD</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="modules/otros/about.php"><i class="fas fa-info-circle me-2"></i>Sobre El Sistema</a></li>
                                <li><a class="dropdown-item" href="modules/otros/manual.php"><i class="fas fa-book me-2"></i>Manual de Uso</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Menú de Usuario a la Derecha con Indicador de Rol Coloreado -->
                <ul class="navbar-nav align-items-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item me-2">
                            <span class="badge <?php echo $badgeClass; ?> px-2 py-1 shadow-sm">
                                <i class="fas <?php echo $roleIcon; ?> me-1"></i> <?php echo $roleNameText; ?>
                            </span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white fw-bold" href="modules/usuarios/perfil.php" title="Editar Perfil / Gestión de Usuarios">
                                <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($userName); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-warning fw-bold ms-1" href="logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i> Salir
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" href="login.php"><i class="fas fa-sign-in-alt me-1"></i> Iniciar Sesión</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>