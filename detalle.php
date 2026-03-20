<?php
session_start();
require 'config/bd.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_garantia = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_garantia <= 0) {
    header('Location: mis_garantias.php');
    exit();
}

$mensaje = '';
$tipo_alerta = '';

// Añadir comentario a la garantía
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_comentario'])) {
    $nuevo_comentario = trim($_POST['nuevo_comentario']);

    if ($nuevo_comentario === '') {
        $mensaje = 'Escribe un comentario antes de añadirlo.';
        $tipo_alerta = 'danger';
    } else {
        try {
            $sql = "SELECT comentarios
                    FROM garantias
                    WHERE id_garantia = :id_garantia
                      AND id_usuario = :id_usuario";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_garantia' => $id_garantia,
                ':id_usuario' => $id_usuario
            ]);

            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila) {
                header('Location: mis_garantias.php');
                exit();
            }

            $comentarioConFecha = "[" . date('d/m/Y') . "] " . $nuevo_comentario;
            $comentariosActuales = trim($fila['comentarios'] ?? '');

            if ($comentariosActuales !== '') {
                $comentariosFinales = $comentariosActuales . "\n\n" . $comentarioConFecha;
            } else {
                $comentariosFinales = $comentarioConFecha;
            }

            $sqlUpdate = "UPDATE garantias
                          SET comentarios = :comentarios
                          WHERE id_garantia = :id_garantia
                            AND id_usuario = :id_usuario";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([
                ':comentarios' => $comentariosFinales,
                ':id_garantia' => $id_garantia,
                ':id_usuario' => $id_usuario
            ]);

            $mensaje = 'Comentario añadido correctamente.';
            $tipo_alerta = 'success';

        } catch (PDOException $e) {
            $mensaje = 'Error al guardar el comentario: ' . $e->getMessage();
            $tipo_alerta = 'danger';
        }
    }
}

// Cargar garantía
try {
    $sql = "SELECT id_garantia, id_usuario, nombre_producto, tienda, fecha_compra,
                   fecha_vencimiento, archivo_ticket, comentarios, estado
            FROM garantias
            WHERE id_garantia = :id_garantia
              AND id_usuario = :id_usuario";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_garantia' => $id_garantia,
        ':id_usuario' => $id_usuario
    ]);

    $garantia = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$garantia) {
        header('Location: mis_garantias.php');
        exit();
    }

} catch (PDOException $e) {
    die("Error al cargar la garantía: " . $e->getMessage());
}

$claseEstado = 'secondary';
if ($garantia['estado'] === 'Vigente') {
    $claseEstado = 'success';
} elseif ($garantia['estado'] === 'Expira pronto') {
    $claseEstado = 'warning';
} elseif ($garantia['estado'] === 'Caducada') {
    $claseEstado = 'danger';
}

// Comprobar si el archivo es una imagen
$mostrarImagen = false;
if (!empty($garantia['archivo_ticket'])) {
    $extension = strtolower(pathinfo($garantia['archivo_ticket'], PATHINFO_EXTENSION));
    $mostrarImagen = in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver artículo - TicKeep</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/detalle.css">
</head>

<body>

    <nav class="topbar d-flex align-items-center">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="index.php" class="brand">TicKeep</a>
            <div class="text-white">
                <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="mx-auto" style="max-width: 820px; margin-top: 40px;">
            <a href="index.php" class="back-link">← Volver a mis garantías</a>
        </div>

        <div class="main-card">
            <?php if ($mensaje !== ''): ?>
                <div class="alert alert-<?= htmlspecialchars($tipo_alerta) ?> text-center">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <div class="row g-4 align-items-start">
                <div class="col-md-auto">
                    <?php if ($mostrarImagen): ?>
                        <img src="<?= htmlspecialchars($garantia['archivo_ticket']) ?>" alt="Ticket o imagen del producto"
                            class="product-image">
                    <?php else: ?>
                        <div class="product-image d-flex align-items-center justify-content-center">
                            📦
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col">
                    <h3 class="fw-bold mb-2"><?= htmlspecialchars($garantia['nombre_producto']) ?></h3>

                    <span class="badge text-bg-<?= $claseEstado ?> mb-3">
                        <?= htmlspecialchars($garantia['estado']) ?>
                    </span>

                    <p class="mb-2">
                        <strong>Tienda:</strong>
                        <?= htmlspecialchars($garantia['tienda'] ?: 'No indicada') ?>
                    </p>

                    <p class="mb-2">
                        <strong>Fecha de compra:</strong>
                        <?= date('d/m/Y', strtotime($garantia['fecha_compra'])) ?>
                    </p>

                    <p class="mb-0">
                        <strong>Fin de garantía:</strong>
                        <?= date('d/m/Y', strtotime($garantia['fecha_vencimiento'])) ?>
                    </p>
                </div>
            </div>

            <div class="mt-5">
                <h5 class="fw-bold">Comentarios personales</h5>
                <p class="text-muted small">
                    Comentarios privados para recordar información relevante sobre esta garantía.
                </p>

                <div class="comment-box mb-3">
                    <?= htmlspecialchars($garantia['comentarios'] ?: 'Todavía no hay comentarios guardados.') ?>
                </div>

                <form method="POST">
                    <div class="mb-3">
                        <textarea name="nuevo_comentario" class="form-control" rows="3"
                            placeholder="Escribe un nuevo comentario..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-light border btn-soft">
                        Añadir comentario
                    </button>
                </form>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="editar_garantia.php?id=<?= (int) $garantia['id_garantia'] ?>" class="btn btn-primary btn-soft">
                    Editar
                </a>

                <a href="eliminar_garantia.php?id=<?= (int) $garantia['id_garantia'] ?>" class="btn btn-danger btn-soft"
                    onclick="return confirm('¿Seguro que quieres eliminar esta garantía?');">
                    Borrar garantía
                </a>
            </div>
        </div>
    </div>

    <footer>
        © 2025 TicKeep. Todos los derechos reservados.<br>
        Tu tranquilidad, garantizada.
    </footer>

</body>

</html>