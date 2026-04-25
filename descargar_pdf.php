<?php
// Alias root a modules/reportes/descargar_pdf.php para compatibilidad de URL
$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: modules/reportes/descargar_pdf.php' . ($query ? '?' . $query : ''));
exit;
