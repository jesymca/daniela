<?php
/**
 * ============================================================================
 * PROYECTO ACADÉMICO - SISTEMA DE GESTIÓN ADMINISTRATIVO MICHELE C.A.
 * Archivo: config.php
 * Descripción: Configuración global del sistema y conexión a la BD MySQL/MariaDB.
 * 
 * Conceptos Académicos Clave:
 *  1. PDO (PHP Data Objects): Capa de abstracción de acceso a datos para PHP
 *     que proporciona una interfaz consistente y previene Inyecciones SQL mediante
 *     el uso obligatorio de sentencias preparadas.
 *  2. Manejo de Excepciones (Try-Catch): Estructura para capturar fallos de red o
 *     credenciales al intentar establecer la conexión sin exponer información sensible.
 *  3. Compatibilidad Multiplataforma (Windows/Linux - XAMPP): Normalización de
 *     separadores de directorios y resolución dinámica de la ruta base del proyecto.
 * ============================================================================
 */

// Parámetros de Conexión a la Base de Datos
$host = 'localhost';
$usuario = 'root';
$clave = '01012023';
$bd = 'michele_ca';

try {
    /**
     * Instanciación del Objeto PDO:
     * DSN (Data Source Name): Especifica el controlador (mysql), el host, la base de datos
     * y el juego de caracteres (utf8mb4 para soporte completo de símbolos y acentos).
     */
    $dsn = "mysql:host=$host;dbname=$bd;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones ante cualquier error SQL
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retorna los resultados como arreglos asociativos
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Utiliza sentencias preparadas nativas del motor
    ];

    $pdo = new PDO($dsn, $usuario, $clave, $options);
} catch (PDOException $e) {
    // Si falla la clave configurada (ej. XAMPP recién instalado sin clave), intentar fallback con clave vacía
    if ($clave !== '') {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$bd;charset=utf8mb4", $usuario, '', $options);
        } catch (PDOException $ex) {
            die("Error crítico de conexión a la base de datos MySQL/MariaDB: " . htmlspecialchars($e->getMessage()));
        }
    } else {
        die("Error crítico de conexión a la base de datos MySQL/MariaDB: " . htmlspecialchars($e->getMessage()));
    }
}

/**
 * Cálculo de Ruta Base Dinámica (BASE_PATH):
 * Normaliza las barras separadoras para garantizar compatibilidad entre Windows (\) y Linux (/),
 * asegurando que los enlaces y recursos funcionen sin importar la carpeta de despliegue en XAMPP.
 */
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$basePath = rtrim(dirname($scriptName), '/');
if ($basePath === '' || $basePath === '.') {
    $basePath = '';
}
define('BASE_PATH', $basePath);
?>