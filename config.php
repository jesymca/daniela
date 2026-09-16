<?php
// config.php - Configuración de conexión a la base de datos MySQL

$host = 'localhost';
$usuario = 'root';
$clave = '01012023';
$bd = 'michele_ca';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$bd;charset=utf8", $usuario, $clave);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Ruta base dinámica: devuelve la carpeta base de la aplicación (ej. '/daniela')
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = rtrim(dirname($scriptName), '/\\');
if ($basePath === '' || $basePath === '.') {
    $basePath = '';
}
define('BASE_PATH', $basePath);
?>