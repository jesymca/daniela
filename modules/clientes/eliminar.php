<?php
// modules/clientes/eliminar.php - Eliminar cliente

require_once '../../config.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: listar.php');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM clientes WHERE id=?");
    $stmt->execute([$id]);
    header('Location: listar.php');
    exit;
} catch (PDOException $e) {
    die("Error al eliminar cliente: " . $e->getMessage());
}
?>