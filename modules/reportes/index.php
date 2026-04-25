<?php
// modules/reportes/index.php - Reportes interactivos

require_once '../../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

$tipo = $_GET['tipo'] ?? 'clientes';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$errors = [];
$params = [];
$where = '';
$reportTitle = '';
$totalItems = 0;
$totalAmount = null;
$details = [];

function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

function buildDateFilter($field, &$fecha_inicio, &$fecha_fin, &$params) {
    $conditions = [];
    if ($fecha_inicio) {
        if (validateDate($fecha_inicio)) {
            $conditions[] = "$field >= ?";
            $params[] = $fecha_inicio . ' 00:00:00';
        }
    }
    if ($fecha_fin) {
        if (validateDate($fecha_fin)) {
            $conditions[] = "$field <= ?";
            $params[] = $fecha_fin . ' 23:59:59';
        }
    }
    return $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
}

if ($fecha_inicio && !validateDate($fecha_inicio)) {
    $errors[] = 'La fecha de inicio no tiene el formato correcto (AAAA-MM-DD).';
}
if ($fecha_fin && !validateDate($fecha_fin)) {
    $errors[] = 'La fecha de fin no tiene el formato correcto (AAAA-MM-DD).';
}
if ($fecha_inicio && $fecha_fin && $fecha_inicio > $fecha_fin) {
    $errors[] = 'La fecha de inicio no puede ser posterior a la fecha de fin.';
}

try {
    switch ($tipo) {
        case 'clientes':
            $reportTitle = 'Cantidad de Clientes';
            $where = buildDateFilter('fecha_creacion', $fecha_inicio, $fecha_fin, $params);
            $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM clientes' . $where);
            $stmt->execute($params);
            $totalItems = $stmt->fetchColumn();
            $stmt = $pdo->prepare('SELECT nombre, email, telefono, fecha_creacion FROM clientes' . $where . ' ORDER BY fecha_creacion DESC LIMIT 50');
            $stmt->execute($params);
            $details = $stmt->fetchAll();
            break;
        case 'proveedores':
            $reportTitle = 'Cantidad de Proveedores';
            $where = buildDateFilter('fecha_creacion', $fecha_inicio, $fecha_fin, $params);
            $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM proveedores' . $where);
            $stmt->execute($params);
            $totalItems = $stmt->fetchColumn();
            $stmt = $pdo->prepare('SELECT nombre, email, telefono, fecha_creacion FROM proveedores' . $where . ' ORDER BY fecha_creacion DESC LIMIT 50');
            $stmt->execute($params);
            $details = $stmt->fetchAll();
            break;
        case 'usuarios':
            $reportTitle = 'Cantidad de Usuarios';
            $where = buildDateFilter('fecha_creacion', $fecha_inicio, $fecha_fin, $params);
            $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM usuarios' . $where);
            $stmt->execute($params);
            $totalItems = $stmt->fetchColumn();
            $stmt = $pdo->prepare('SELECT nombre, email, rol, fecha_creacion FROM usuarios' . $where . ' ORDER BY fecha_creacion DESC LIMIT 50');
            $stmt->execute($params);
            $details = $stmt->fetchAll();
            break;
        case 'inventario':
            $reportTitle = 'Cantidad de Productos en Inventario';
            $params_count = [];
            $where_count = buildDateFilter('fecha_creacion', $fecha_inicio, $fecha_fin, $params_count);
            $stmt = $pdo->prepare('SELECT COUNT(*) as total, COALESCE(SUM(stock), 0) as total_stock FROM productos' . $where_count);
            $stmt->execute($params_count);
            $row = $stmt->fetch();
            $totalItems = $row ? $row['total'] : 0;
            $totalAmount = $row ? $row['total_stock'] : 0;
            $params_details = [];
            $where_details = buildDateFilter('p.fecha_creacion', $fecha_inicio, $fecha_fin, $params_details);
            $stmt = $pdo->prepare('SELECT p.id, p.nombre, p.codigo_barras, COALESCE(c.nombre, "Sin categoría") as categoria, p.stock, p.precio, p.fecha_creacion FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id' . $where_details . ' ORDER BY p.fecha_creacion DESC LIMIT 50');
            $stmt->execute($params_details);
            $details = $stmt->fetchAll();
            break;
        case 'ventas':
            $reportTitle = 'Cantidad de Ventas';
            $where = buildDateFilter('fecha', $fecha_inicio, $fecha_fin, $params);
            $stmt = $pdo->prepare('SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as total_amount FROM ventas' . $where);
            $stmt->execute($params);
            $row = $stmt->fetch();
            $totalItems = $row ? $row['total'] : 0;
            $totalAmount = $row ? $row['total_amount'] : 0;
            $stmt = $pdo->prepare('SELECT v.id, v.fecha, v.total, c.nombre as cliente FROM ventas v LEFT JOIN clientes c ON v.cliente_id = c.id' . $where . ' ORDER BY v.fecha DESC LIMIT 50');
            $stmt->execute($params);
            $details = $stmt->fetchAll();
            break;
        case 'compras':
            $reportTitle = 'Cantidad de Compras';
            $where = buildDateFilter('fecha', $fecha_inicio, $fecha_fin, $params);
            $stmt = $pdo->prepare('SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as total_amount FROM compras' . $where);
            $stmt->execute($params);
            $row = $stmt->fetch();
            $totalItems = $row ? $row['total'] : 0;
            $totalAmount = $row ? $row['total_amount'] : 0;
            $stmt = $pdo->prepare('SELECT c.id, c.fecha, c.total, p.nombre as proveedor FROM compras c LEFT JOIN proveedores p ON c.proveedor_id = p.id' . $where . ' ORDER BY c.fecha DESC LIMIT 50');
            $stmt->execute($params);
            $details = $stmt->fetchAll();
            break;
        default:
            $reportTitle = 'Cantidad de Clientes';
            $where = buildDateFilter('fecha_creacion', $fecha_inicio, $fecha_fin, $params);
            $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM clientes' . $where);
            $stmt->execute($params);
            $totalItems = $stmt->fetchColumn();
            $stmt = $pdo->prepare('SELECT nombre, email, telefono, fecha_creacion FROM clientes' . $where . ' ORDER BY fecha_creacion DESC LIMIT 50');
            $stmt->execute($params);
            $details = $stmt->fetchAll();
            break;
    }
} catch (PDOException $e) {
    die('Error al generar reporte: ' . $e->getMessage());
}

include '../../header.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-12">
            <h2><i class="fas fa-chart-bar"></i> Dashboard de Reportes</h2>
            <p class="text-muted">Selecciona el tipo de reporte y el rango de fechas para generar los datos.</p>
        </div>
    </div>

    <?php if ($errors): ?>
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-lg-8">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="tipo" class="form-label">Tipo de Reporte</label>
                    <select id="tipo" name="tipo" class="form-select">
                        <option value="clientes" <?php echo $tipo === 'clientes' ? 'selected' : ''; ?>>Cantidad de Clientes</option>
                        <option value="compras" <?php echo $tipo === 'compras' ? 'selected' : ''; ?>>Compras</option>
                        <option value="inventario" <?php echo $tipo === 'inventario' ? 'selected' : ''; ?>>Inventario</option>
                        <option value="proveedores" <?php echo $tipo === 'proveedores' ? 'selected' : ''; ?>>Proveedores</option>
                        <option value="usuarios" <?php echo $tipo === 'usuarios' ? 'selected' : ''; ?>>Usuarios</option>
                        <option value="ventas" <?php echo $tipo === 'ventas' ? 'selected' : ''; ?>>Ventas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="fecha_inicio" class="form-label">Fecha inicio</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
                </div>
                <div class="col-md-3">
                    <label for="fecha_fin" class="form-label">Fecha fin</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="<?php echo htmlspecialchars($fecha_fin); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Generar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Reporte</h5>
                    <p class="card-text text-muted"><?php echo htmlspecialchars($reportTitle); ?></p>
                    <h2 class="display-5"><?php echo number_format($totalItems); ?></h2>
                    <p class="mb-0">Total registrado en el período seleccionado.</p>
                </div>
            </div>
        </div>
        <?php if ($totalAmount !== null): ?>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Total acumulado</h5>
                        <p class="card-text text-muted">Sumatoria dentro del rango</p>
                        <h2 class="display-5">$<?php echo number_format($totalAmount, 2); ?></h2>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Rango de fechas</h5>
                    <p class="card-text mb-0"><?php echo $fecha_inicio ? htmlspecialchars($fecha_inicio) : 'Inicio no definido'; ?> - <?php echo $fecha_fin ? htmlspecialchars($fecha_fin) : 'Fin no definido'; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title">Detalles del reporte</h4>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <?php if ($tipo === 'clientes'): ?>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Fecha de creación</th>
                                    <?php elseif ($tipo === 'proveedores'): ?>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Fecha de creación</th>
                                    <?php elseif ($tipo === 'usuarios'): ?>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Fecha de creación</th>
                                    <?php elseif ($tipo === 'inventario'): ?>
                                        <th>Código de Barras</th>
                                        <th>Producto</th>
                                        <th>Categoría</th>
                                        <th>Stock</th>
                                        <th>Precio</th>
                                        <th>Creado</th>
                                    <?php elseif ($tipo === 'ventas'): ?>
                                        <th>ID</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Total</th>
                                    <?php elseif ($tipo === 'compras'): ?>
                                        <th>ID</th>
                                        <th>Fecha</th>
                                        <th>Proveedor</th>
                                        <th>Total</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($details)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No hay datos para el rango seleccionado.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($details as $row): ?>
                                        <tr>
                                            <?php if ($tipo === 'clientes'): ?>
                                                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_creacion'])); ?></td>
                                            <?php elseif ($tipo === 'proveedores'): ?>
                                                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_creacion'])); ?></td>
                                            <?php elseif ($tipo === 'usuarios'): ?>
                                                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td><?php echo htmlspecialchars($row['rol']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_creacion'])); ?></td>
                                            <?php elseif ($tipo === 'inventario'): ?>
                                                <td><?php echo htmlspecialchars($row['codigo_barras']); ?></td>
                                                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($row['categoria']); ?></td>
                                                <td><?php echo $row['stock']; ?></td>
                                                <td>$<?php echo number_format($row['precio'], 2); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_creacion'])); ?></td>
                                            <?php elseif ($tipo === 'ventas'): ?>
                                                <td><?php echo $row['id']; ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($row['fecha'])); ?></td>
                                                <td><?php echo htmlspecialchars($row['cliente']); ?></td>
                                                <td>$<?php echo number_format($row['total'], 2); ?></td>
                                            <?php elseif ($tipo === 'compras'): ?>
                                                <td><?php echo $row['id']; ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($row['fecha'])); ?></td>
                                                <td><?php echo htmlspecialchars($row['proveedor']); ?></td>
                                                <td>$<?php echo number_format($row['total'], 2); ?></td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if (!empty($details)): ?>
        <div class="row mt-2">
            <div class="col-12">
                <a href="descargar_pdf.php?tipo=<?php echo urlencode($tipo); ?>&fecha_inicio=<?php echo urlencode($fecha_inicio); ?>&fecha_fin=<?php echo urlencode($fecha_fin); ?>" class="btn btn-success">
                    <i class="fas fa-file-pdf"></i> Descargar PDF
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../../footer.php'; ?>