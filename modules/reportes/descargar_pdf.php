<?php
// modules/reportes/descargar_pdf.php - Generador de PDF para reportes

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

$params = [];
$where = '';
$reportTitle = '';
$details = [];

try {
    switch ($tipo) {
        case 'proveedores':
            $reportTitle = 'Cantidad de Proveedores';
            $where = buildDateFilter('fecha_creacion', $fecha_inicio, $fecha_fin, $params);
            $stmt = $pdo->prepare('SELECT nombre, email, telefono, fecha_creacion FROM proveedores' . $where . ' ORDER BY fecha_creacion DESC');
            break;
        case 'usuarios':
            $reportTitle = 'Cantidad de Usuarios';
            $where = buildDateFilter('fecha_creacion', $fecha_inicio, $fecha_fin, $params);
            $stmt = $pdo->prepare('SELECT nombre, email, rol, fecha_creacion FROM usuarios' . $where . ' ORDER BY fecha_creacion DESC');
            break;
        case 'inventario':
            $reportTitle = 'Cantidad de Productos en Inventario';
            $where = buildDateFilter('fecha_creacion', $fecha_inicio, $fecha_fin, $params);
            $stmt = $pdo->prepare('SELECT p.nombre, p.codigo_barras, COALESCE(c.nombre, "Sin categoría") as categoria, p.stock, p.precio, p.fecha_creacion FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id' . $where . ' ORDER BY p.fecha_creacion DESC');
            break;
        case 'ventas':
            $reportTitle = 'Cantidad de Ventas';
            $where = buildDateFilter('fecha', $fecha_inicio, $fecha_fin, $params);
            $stmt = $pdo->prepare('SELECT v.id, v.fecha, v.total, c.nombre as cliente FROM ventas v LEFT JOIN clientes c ON v.cliente_id = c.id' . $where . ' ORDER BY v.fecha DESC');
            break;
        case 'compras':
            $reportTitle = 'Cantidad de Compras';
            $where = buildDateFilter('fecha', $fecha_inicio, $fecha_fin, $params);
            $stmt = $pdo->prepare('SELECT c.id, c.fecha, c.total, p.nombre as proveedor FROM compras c LEFT JOIN proveedores p ON c.proveedor_id = p.id' . $where . ' ORDER BY c.fecha DESC');
            break;
        default:
            $reportTitle = 'Cantidad de Clientes';
            $where = buildDateFilter('fecha_creacion', $fecha_inicio, $fecha_fin, $params);
            $stmt = $pdo->prepare('SELECT nombre, email, telefono, fecha_creacion FROM clientes' . $where . ' ORDER BY fecha_creacion DESC');
            break;
    }

    $stmt->execute($params);
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error al generar PDF: ' . $e->getMessage());
}

if (empty($details)) {
    header('Location: index.php?tipo=' . urlencode($tipo) . '&fecha_inicio=' . urlencode($fecha_inicio) . '&fecha_fin=' . urlencode($fecha_fin));
    exit;
}

function pdfEscape($text) {
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}

$contentLines = [];
$contentLines[] = "Reporte: $reportTitle";
$contentLines[] = "Rango: " . ($fecha_inicio ?: 'No definido') . ' - ' . ($fecha_fin ?: 'No definido');
$contentLines[] = str_repeat('-', 90);

switch ($tipo) {
    case 'clientes':
    case 'proveedores':
    case 'usuarios':
        if (empty($details)) {
            $contentLines[] = 'No hay datos para el rango seleccionado.';
        } else {
            $headers = array_keys($details[0]);
            $contentLines[] = implode(' | ', $headers);
            foreach ($details as $row) {
                $contentLines[] = implode(' | ', array_map('trim', $row));
            }
        }
        break;
    case 'inventario':
        if (empty($details)) {
            $contentLines[] = 'No hay datos para el rango seleccionado.';
        } else {
            $contentLines[] = 'Producto | Código de Barras | Categoría | Stock | Precio | Creado';
            foreach ($details as $row) {
                $contentLines[] = $row['nombre'] . ' | ' . $row['codigo_barras'] . ' | ' . $row['categoria'] . ' | ' . $row['stock'] . ' | ' . number_format($row['precio'], 2) . ' | ' . $row['fecha_creacion'];
            }
        }
        break;
    case 'ventas':
        if (empty($details)) {
            $contentLines[] = 'No hay datos para el rango seleccionado.';
        } else {
            $contentLines[] = 'ID | Fecha | Cliente | Total';
            foreach ($details as $row) {
                $contentLines[] = $row['id'] . ' | ' . $row['fecha'] . ' | ' . $row['cliente'] . ' | ' . number_format($row['total'], 2);
            }
        }
        break;
    case 'compras':
        if (empty($details)) {
            $contentLines[] = 'No hay datos para el rango seleccionado.';
        } else {
            $contentLines[] = 'ID | Fecha | Proveedor | Total';
            foreach ($details as $row) {
                $contentLines[] = $row['id'] . ' | ' . $row['fecha'] . ' | ' . $row['proveedor'] . ' | ' . number_format($row['total'], 2);
            }
        }
        break;
}

$linesPerPage = 40;
$pages = array_chunk($contentLines, $linesPerPage);

$objects = [];
$objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
$objects[] = "2 0 obj\n<< /Type /Pages /Kids ";
$pagesCount = count($pages);
$kids = [];
for ($i = 1; $i <= $pagesCount; $i++) {
    $kids[] = ($i + 2) . ' 0 R';
}
$objects[1] .= '[ ' . implode(' ', $kids) . ' ] /Count ' . $pagesCount . ' >>\nendobj\n';

$contents = [];
$fontObjNum = 2 * $pagesCount + 3;
for ($i = 0; $i < $pagesCount; $i++) {
    $pageNum = $i + 3;
    $contentObjectNum = $pageNum + $pagesCount;
    $objects[] = "$pageNum 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 $fontObjNum 0 R >> >> /Contents $contentObjectNum 0 R >>\nendobj\n";
    $pageContent = "BT /F1 12 Tf 50 760 Td\n";
    foreach ($pages[$i] as $index => $line) {
        $safe = pdfEscape($line);
        $pageContent .= "($safe) Tj\n";
        if ($index < count($pages[$i]) - 1) {
            $pageContent .= "0 -16 Td\n";
        }
    }
    $pageContent .= "ET\n";
    $stream = $pageContent;
    $contentLength = strlen($stream);
    $objects[] = "$contentObjectNum 0 obj\n<< /Length $contentLength >>\nstream\n$stream\nendstream\nendobj\n";
}
$objects[] = "$fontObjNum 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";

$offsets = [0];
$pdf = "%PDF-1.3\n";
foreach ($objects as $obj) {
    $offsets[] = strlen($pdf);
    $pdf .= $obj;
}

$xref = "xref\n0 " . (count($objects) + 1) . "\n";
$xref .= "0000000000 65535 f \n";
for ($i = 1; $i <= count($objects); $i++) {
    $xref .= sprintf("%010d 00000 n \n", $offsets[$i]);
}

$trailer = "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . strlen($pdf) . "\n%%EOF";

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="reporte_' . $tipo . '.pdf"');
header('Cache-Control: private, max-age=0, must-revalidate');

echo $pdf;
echo $xref;
echo $trailer;
