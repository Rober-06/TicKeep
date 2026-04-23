<?php
session_start();
require 'config/bd.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_garantia = $_GET['id'] ?? null;

if (!$id_garantia) {
    header("Location: index.php");
    exit();
}

try {

    // Obtener garantía actual
    $stmt = $pdo->prepare("SELECT * FROM garantias WHERE id_garantia = :id AND id_usuario = :user");
    $stmt->execute([
        ':id' => $id_garantia,
        ':user' => $id_usuario
    ]);

    $garantia = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$garantia) {
        die("Garantía no encontrada.");
    }
} catch (PDOException $e) {
    die($e->getMessage());
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST['nombre_producto']);
    $tienda = trim($_POST['tienda']);
    $fecha_compra = $_POST['fecha_compra'];
    $fecha_vencimiento = $_POST['fecha_vencimiento'];
    $comentarios = trim($_POST['comentarios']);

    try {

        $sql = "UPDATE garantias
                SET nombre_producto = :nombre,
                    tienda = :tienda,
                    fecha_compra = :fecha_compra,
                    fecha_vencimiento = :fecha_vencimiento,
                    comentarios = :comentarios
                WHERE id_garantia = :id AND id_usuario = :user";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':nombre' => $nombre,
            ':tienda' => $tienda,
            ':fecha_compra' => $fecha_compra,
            ':fecha_vencimiento' => $fecha_vencimiento,
            ':comentarios' => $comentarios,
            ':id' => $id_garantia,
            ':user' => $id_usuario
        ]);

        // Redirigir al index después de guardar
        header("Location: index.php");
        exit();

        // Recargar datos
        $garantia = [
            'nombre_producto' => $nombre,
            'tienda' => $tienda,
            'fecha_compra' => $fecha_compra,
            'fecha_vencimiento' => $fecha_vencimiento,
            'comentarios' => $comentarios
        ];
    } catch (PDOException $e) {
        $mensaje = "Error al actualizar.";
    }
}
?>

<h2>Editar garantía</h2>

<?php if ($mensaje): ?>
    <p><?= $mensaje ?></p>
<?php endif; ?>

<form method="POST">

    <label>Producto</label>
    <input type="text" name="nombre_producto" value="<?= $garantia['nombre_producto'] ?>" required>

    <label>Tienda</label>
    <input type="text" name="tienda" value="<?= $garantia['tienda'] ?>">

    <label>Fecha compra</label>
    <input type="date" name="fecha_compra" value="<?= $garantia['fecha_compra'] ?>" required>

    <label>Fecha vencimiento</label>
    <input type="date" name="fecha_vencimiento" value="<?= $garantia['fecha_vencimiento'] ?>" required>

    <label>Comentarios</label>
    <textarea name="comentarios"><?= $garantia['comentarios'] ?></textarea>

    <button type="submit">Guardar cambios</button>

</form>