<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../header.php';
include '../../config.php';

$message = '';

$backup_dir = __DIR__ . '/../../backups/';

// Manejar descarga de respaldo existente (esto debe ir antes de cualquier salida HTML)
if (isset($_GET['download'])) {
    $filename = basename($_GET['download']);
    $filepath = $backup_dir . $filename;
    if (file_exists($filepath) && is_file($filepath) && strpos(realpath($filepath), realpath($backup_dir)) === 0) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    } else {
        die('Archivo no válido.');
    }
}

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['backup'])) {
        // Crear respaldo
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
        $backup_file = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $full_path = $backup_dir . $backup_file;
        // Redirigir stderr a /dev/null para evitar mensajes de advertencia
        $command = "/usr/bin/mariadb-dump --opt -h $host -u $usuario -p$clave $bd 2>/dev/null > $full_path";

        exec($command, $output, $return_var);
        if ($return_var === 0 && file_exists($full_path)) {
            $message = 'Respaldo creado exitosamente: ' . htmlspecialchars($backup_file);
        } else {
            $message = 'Error al crear el respaldo. Código: ' . $return_var;
        }
} elseif (isset($_POST['optimize'])) {
    // Optimizar tablas usando MySQLi (nueva conexión independiente)
    $mysqli = new mysqli($host, $usuario, $clave, $bd);
    if ($mysqli->connect_error) {
        $message = 'Error de conexión para optimizar: ' . $mysqli->connect_error;
    } else {
        // Asegúrate de que los nombres de las tablas sean los correctos
        $tables = ['ventas', 'ventas_detalles', 'compras', 'compras_detalles', 'clientes', 'proveedores', 'productos', 'usuarios'];
        $errors = [];
        foreach ($tables as $table) {
            if (!$mysqli->query("OPTIMIZE TABLE `$table`")) {
                $errors[] = "$table: " . $mysqli->error;
            }
        }
        $mysqli->close();
        if (empty($errors)) {
            $message = 'Base de datos optimizada exitosamente.';
        } else {
            $message = 'Errores al optimizar: ' . implode(', ', $errors);
        }
    }
}elseif (isset($_POST['delete_backup']) && isset($_POST['backup_name'])) {
        $backup_name = basename($_POST['backup_name']);
        $file_path = $backup_dir . $backup_name;
        if (file_exists($file_path) && is_file($file_path) && strpos(realpath($file_path), realpath($backup_dir)) === 0) {
            if (unlink($file_path)) {
                $message = 'Respaldo eliminado correctamente.';
            } else {
                $message = 'Error al eliminar el respaldo.';
            }
        } else {
            $message = 'Archivo no válido.';
        }
    }
}

// Después de procesar el POST, leer los respaldos actualizados
$backups = [];
if (is_dir($backup_dir)) {
    $files = glob($backup_dir . '*.sql');
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
?>

<div class="container mt-4">
    <h2>Mantenimiento de Base de Datos</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Respaldo de Base de Datos</div>
                <div class="card-body">
                    <p>Crea un respaldo completo de la base de datos en formato SQL. El archivo se guardará en el servidor y estará disponible en la lista de respaldos.</p>
                    <form method="post">
                        <button type="submit" name="backup" class="btn btn-primary">Crear Respaldo</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Optimización de Base de Datos</div>
                <div class="card-body">
                    <p>Optimiza las tablas para mejorar el rendimiento.</p>
                    <form method="post">
                        <button type="submit" name="optimize" class="btn btn-success">Optimizar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="card mt-4">
        <div class="card-header">Respaldos Disponibles</div>
        <div class="card-body">
            <?php if (empty($backups)): ?>
                <p>No hay respaldos disponibles.</p>
            <?php else: ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Fecha</th>
                            <th>Tamaño</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($backup['name']); ?></td>
                                <td><?php echo $backup['date']; ?></td>
                                <td><?php echo number_format($backup['size'] / 1024, 2) . ' KB'; ?></td>
                                <td>
                                    <a href="backups/<?php echo urlencode($backup['name']); ?>" class="btn btn-sm btn-primary">Descargar</a>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('¿Está seguro de eliminar este respaldo?');">
                                        <input type="hidden" name="backup_name" value="<?php echo htmlspecialchars($backup['name']); ?>">
                                        <button type="submit" name="delete_backup" class="btn btn-sm btn-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../footer.php'; ?>