<?php
/**
 * ============================================================================
 * PROYECTO ACADÉMICO - SISTEMA DE GESTIÓN ADMINISTRATIVO MICHELE C.A.
 * Archivo: modules/otros/mantenimiento.php
 * Descripción: Módulo de Mantenimiento de Base de Datos y Gestión de Respaldos SQL.
 * 
 * Conceptos Académicos Clave:
 *  1. Compatibilidad Cross-Platform (Windows XAMPP / Linux):
 *     Detección del sistema operativo del servidor (PHP_OS_FAMILY) para ejecutar
 *     utilidades de volcado SQL CLI (`mysqldump` / `mariadb-dump`).
 *  2. Fallback Nativo vía PDO:
 *     Si las herramientas de línea de comandos no están disponibles en XAMPP,
 *     el sistema realiza la serialización de la base de datos mediante inspección
 *     de esquemas (SHOW TABLES, SHOW CREATE TABLE) e insersión estructurada de registros.
 *  3. Gestión Segura de Cabeceras HTTP:
 *     Las solicitudes de descarga de archivos de respaldo (`$_GET['download']`) se
 *     procesan ANTES de emitir cualquier salida HTML para evitar errores tipo
 *     "Headers already sent".
 * ============================================================================
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cargar la configuración de base de datos antes de cualquier salida HTML
require_once __DIR__ . '/../../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Control de Acceso: Redirigir al login si no hay sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

$backup_dir = __DIR__ . '/../../backups/';

/**
 * PROCESAMIENTO DE DESCARGA DE RESPALDO (Headers HTTP):
 * Debe ejecutarse antes de incluir header.php o generar cualquier etiqueta HTML.
 */
if (isset($_GET['download'])) {
    $filename = basename($_GET['download']);
    $filepath = $backup_dir . $filename;
    $realBackupDir = realpath($backup_dir);
    $realFilePath = realpath($filepath);

    if ($realFilePath && file_exists($realFilePath) && is_file($realFilePath) && strpos($realFilePath, $realBackupDir) === 0) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($realFilePath));
        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        readfile($realFilePath);
        exit;
    } else {
        die('Error: El archivo de respaldo solicitado no es válido o no existe.');
    }
}

/**
 * Función Auxiliar Multiplataforma para Respaldo de BD:
 * Intenta utilizar los binarios mysqldump/mariadb-dump en Windows (XAMPP) y Linux.
 * Si no se encuentra un ejecutable CLI, conmuta a un generador nativo en PHP con PDO.
 */
function generateDatabaseBackup($host, $usuario, $clave, $bd, $full_path, $pdo) {
    $isWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

    // 1. Búsqueda de ejecutables CLI según el sistema operativo
    $dumpBinary = null;
    if ($isWindows) {
        $possiblePaths = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\wamp64\\bin\\mysql\\bin\\mysqldump.exe',
            'mysqldump.exe',
            'mysqldump'
        ];
        foreach ($possiblePaths as $path) {
            if (file_exists($path) || $path === 'mysqldump.exe' || $path === 'mysqldump') {
                $dumpBinary = '"' . $path . '"';
                break;
            }
        }
    } else {
        $possiblePaths = ['/usr/bin/mariadb-dump', '/usr/bin/mysqldump', 'mysqldump', 'mariadb-dump'];
        foreach ($possiblePaths as $path) {
            if (file_exists($path) || $path === 'mysqldump' || $path === 'mariadb-dump') {
                $dumpBinary = $path;
                break;
            }
        }
    }

    // 2. Intentar ejecución vía Shell
    if ($dumpBinary) {
        $nullDevice = $isWindows ? 'NUL' : '/dev/null';
        $passFlag = ($clave !== '') ? "-p\"$clave\"" : "";
        $command = "$dumpBinary --opt -h \"$host\" -u \"$usuario\" $passFlag \"$bd\" 2>$nullDevice > \"$full_path\"";
        @exec($command, $output, $return_var);
        if ($return_var === 0 && file_exists($full_path) && filesize($full_path) > 0) {
            return true;
        }
    }

    // 3. Fallback Nativo PHP con PDO (Garantiza funcionamiento en XAMPP sin CLI)
    try {
        $sqlScript = "-- ========================================================\n";
        $sqlScript .= "-- RESPALDO DE BASE DE DATOS: $bd\n";
        $sqlScript .= "-- Generado por Sistema Michele C.A. (Modo Fallback PDO)\n";
        $sqlScript .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
        $sqlScript .= "-- ========================================================\n\n";
        $sqlScript .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $tablesQuery = $pdo->query("SHOW TABLES");
        $tables = $tablesQuery->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $createTableQuery = $pdo->query("SHOW CREATE TABLE `$table`")->fetch();
            $sqlScript .= "-- Estructura de la tabla `$table` --\n";
            $sqlScript .= "DROP TABLE IF EXISTS `$table`;\n";
            $sqlScript .= $createTableQuery['Create Table'] . ";\n\n";

            $rowsQuery = $pdo->query("SELECT * FROM `$table`");
            $rows = $rowsQuery->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $sqlScript .= "-- Datos de la tabla `$table` --\n";
                foreach ($rows as $row) {
                    $keys = array_map(function($k) { return "`$k`"; }, array_keys($row));
                    $values = array_map(function($v) use ($pdo) {
                        return $v === null ? "NULL" : $pdo->quote($v);
                    }, array_values($row));
                    $sqlScript .= "INSERT INTO `$table` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");\n";
                }
                $sqlScript .= "\n";
            }
        }

        $sqlScript .= "SET FOREIGN_KEY_CHECKS=1;\n";
        return @file_put_contents($full_path, $sqlScript) !== false;
    } catch (Exception $e) {
        return false;
    }
}

$message = '';

// Procesar peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['backup'])) {
        // Crear directorio de respaldos si no existe y asegurar permisos de escritura (0777)
        if (!is_dir($backup_dir)) {
            @mkdir($backup_dir, 0777, true);
        }
        @chmod($backup_dir, 0777);

        // Verificar si el directorio permite escritura antes de intentar generar el archivo
        if (!is_writable($backup_dir)) {
            $message = 'Error de permisos: La carpeta de respaldos (' . htmlspecialchars(realpath($backup_dir) ?: $backup_dir) . ') no tiene permisos de escritura para el servidor web. En Linux ejecute: chmod 777 backups';
        } else {
            $backup_file = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $full_path = $backup_dir . $backup_file;

            if (generateDatabaseBackup($host, $usuario, $clave, $bd, $full_path, $pdo)) {
                $message = 'Respaldo generado exitosamente: ' . htmlspecialchars($backup_file);
            } else {
                $message = 'Error al intentar generar el respaldo de la base de datos. Verifique los permisos en el servidor.';
            }
        }
    } elseif (isset($_POST['optimize'])) {
        // Optimizar tablas del sistema
        $tables = ['ventas', 'ventas_detalles', 'compras', 'compras_detalles', 'clientes', 'proveedores', 'productos', 'usuarios'];
        $errors = [];
        foreach ($tables as $table) {
            try {
                $pdo->query("OPTIMIZE TABLE `$table`");
            } catch (PDOException $e) {
                $errors[] = "$table: " . $e->getMessage();
            }
        }
        if (empty($errors)) {
            $message = 'Base de datos optimizada exitosamente.';
        } else {
            $message = 'Advertencias al optimizar: ' . implode(', ', $errors);
        }
    } elseif (isset($_POST['delete_backup']) && isset($_POST['backup_name'])) {
        $backup_name = basename($_POST['backup_name']);
        $file_path = $backup_dir . $backup_name;
        if (file_exists($file_path) && is_file($file_path) && strpos(realpath($file_path), realpath($backup_dir)) === 0) {
            if (unlink($file_path)) {
                $message = 'Respaldo eliminado correctamente.';
            } else {
                $message = 'Error al intentar eliminar el archivo de respaldo.';
            }
        } else {
            $message = 'El archivo especificado no es válido.';
        }
    }
}

// Obtener lista de respaldos existentes
$backups = [];
if (is_dir($backup_dir)) {
    $files = glob($backup_dir . '*.sql');
    if ($files) {
        foreach ($files as $file) {
            $backups[] = [
                'name' => basename($file),
                'size' => filesize($file),
                'date' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
        usort($backups, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
    }
}

// Incluir la vista de la cabecera después del procesamiento HTTP
include __DIR__ . '/../../header.php';
?>

<div class="container mt-4 mb-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-primary"><i class="fas fa-database"></i> Mantenimiento de Base de Datos</h2>
            <p class="text-muted">Herramientas de respaldo, optimización y mantenimiento de la base de datos MySQL/MariaDB.</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i><?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-download me-2"></i> Respaldo de Base de Datos</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <p>Genera un archivo de respaldo completo en formato SQL compatible con Windows (XAMPP) y Linux. El archivo se guardará localmente en el servidor.</p>
                    <form method="post">
                        <button type="submit" name="backup" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-file-export me-1"></i> Crear Nuevo Respaldo SQL
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-magic me-2"></i> Optimización de Tablas</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <p>Ejecuta la instrucción `OPTIMIZE TABLE` en las tablas principales para desfragmentar el almacenamiento y mejorar la velocidad de consulta.</p>
                    <form method="post">
                        <button type="submit" name="optimize" class="btn btn-success w-100 py-2">
                            <i class="fas fa-tachometer-alt me-1"></i> Optimizar Tablas del Sistema
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-2">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-archive me-2"></i> Respaldos Disponibles</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($backups)): ?>
                <p class="p-4 text-center text-muted mb-0">No hay archivos de respaldo generados en el servidor.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre del Archivo</th>
                                <th>Fecha de Creación</th>
                                <th>Tamaño</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($backups as $backup): ?>
                                <tr>
                                    <td class="font-weight-bold">
                                        <i class="fas fa-file-code text-primary me-2"></i>
                                        <?php echo htmlspecialchars($backup['name']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($backup['date']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo number_format($backup['size'] / 1024, 2) . ' KB'; ?></span></td>
                                    <td class="text-end">
                                        <a href="modules/otros/mantenimiento.php?download=<?php echo urlencode($backup['name']); ?>" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="fas fa-download me-1"></i> Descargar
                                        </a>
                                        <form method="post" style="display:inline;" onsubmit="return confirm('¿Está seguro de eliminar este archivo de respaldo?');">
                                            <input type="hidden" name="backup_name" value="<?php echo htmlspecialchars($backup['name']); ?>">
                                            <button type="submit" name="delete_backup" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash-alt me-1"></i> Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../footer.php'; ?>