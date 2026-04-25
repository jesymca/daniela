<?php
// modules/inventario/editar.php - Editar producto

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $categoria_id = $_POST['categoria_id'] ?? null;
    $codigo_barras = trim($_POST['codigo_barras'] ?? '');

    try {
        $stmt = $pdo->prepare("UPDATE productos SET nombre=?, descripcion=?, precio=?, stock=?, categoria_id=?, codigo_barras=? WHERE id=?");
        $stmt->execute([$nombre, $descripcion, $precio, $stock, $categoria_id ?: null, $codigo_barras ?: null, $id]);
        header('Location: listar.php');
        exit;
    } catch (PDOException $e) {
        $error = "Error al actualizar producto: " . $e->getMessage();
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id=?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch();
    if (!$producto) {
        header('Location: listar.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error al consultar producto: " . $e->getMessage());
}

try {
    $stmt = $pdo->query('SELECT id, nombre FROM categorias ORDER BY nombre ASC');
    $categorias = $stmt->fetchAll();
} catch (PDOException $e) {
    $categorias = [];
}

include '../../header.php';
?>

<div class="container mt-4">
    <h2><i class="fas fa-edit"></i> Editar Producto</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="precio" class="form-label">Precio</label>
            <input type="number" step="0.01" class="form-control" id="precio" name="precio" value="<?php echo $producto['precio']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="stock" class="form-label">Stock</label>
            <input type="number" class="form-control" id="stock" name="stock" value="<?php echo $producto['stock']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="codigo_barras" class="form-label">Código de Barras</label>
            <input type="text" class="form-control" id="codigo_barras" name="codigo_barras" value="<?php echo htmlspecialchars($producto['codigo_barras']); ?>">
        </div>
        <div class="mb-3">
            <label for="categoria_id" class="form-label">Categoría</label>
            <select class="form-control" id="categoria_id" name="categoria_id">
                <option value="">Sin categoría</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo $categoria['id']; ?>" <?php echo $producto['categoria_id'] == $categoria['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($categoria['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Actualizar</button>
        <a href="listar.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include '../../footer.php'; ?>