<?php
session_start();
require 'config/bd.php';
require 'ocr_ticket.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit();
}

$mensaje = '';
$tipo_alerta = '';

$nombre_producto = trim($_POST['nombre_producto'] ?? '');
$tienda = trim($_POST['tienda'] ?? '');
$fecha_compra = trim($_POST['fecha_compra'] ?? '');
$fecha_vencimiento = trim($_POST['fecha_vencimiento'] ?? '');
$comentarios = trim($_POST['comentarios'] ?? '');

function calcularEstado($fechaVencimiento)
{
    $hoy = new DateTime();
    $vencimiento = new DateTime($fechaVencimiento);

    $diferencia = (int)$hoy->diff($vencimiento)->format('%r%a');

    if ($diferencia < 0) {
        return 'Caducada';
    } elseif ($diferencia <= 30) {
        return 'Expira pronto';
    } else {
        return 'Vigente';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $archivo_ticket = null;

    if ($nombre_producto === '' || $fecha_vencimiento === '') {
        $mensaje = 'Por favor, rellena los campos obligatorios.';
        $tipo_alerta = 'danger';
    } else {
        if (isset($_FILES['archivo_ticket']) && $_FILES['archivo_ticket']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['archivo_ticket']['error'] === UPLOAD_ERR_OK) {
                $directorio = 'uploads/tickets/';

                if (!is_dir($directorio)) {
                    mkdir($directorio, 0777, true);
                }

                $nombre_original = $_FILES['archivo_ticket']['name'];
                $tmp_name = $_FILES['archivo_ticket']['tmp_name'];
                $tamano = $_FILES['archivo_ticket']['size'];

                $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
                $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

                if (!in_array($extension, $permitidas, true)) {
                    $mensaje = 'Formato no permitido. Solo se aceptan JPG, JPEG, PNG o WEBP.';
                    $tipo_alerta = 'danger';
                } elseif ($tamano > 5 * 1024 * 1024) {
                    $mensaje = 'El archivo supera el máximo permitido de 5 MB.';
                    $tipo_alerta = 'danger';
                } else {
                    $nuevo_nombre = 'ticket_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
                    $ruta_final = $directorio . $nuevo_nombre;

                    if (move_uploaded_file($tmp_name, $ruta_final)) {
                        $archivo_ticket = $ruta_final;

                        $ocr = procesarOCRTicket($ruta_final);

                        if ($ocr['ok']) {
                            if ($tienda === '' && !empty($ocr['tienda'])) {
                                $tienda = $ocr['tienda'];
                            }

                            if ($fecha_compra === '' && !empty($ocr['fecha_compra'])) {
                                $fecha_compra = $ocr['fecha_compra'];
                            }

                            if ($comentarios === '' && !empty($ocr['texto'])) {
                                $comentarios = $ocr['texto'];
                            }
                        } else {
                            $mensaje = 'Se ha subido el ticket, pero el OCR no ha podido leerlo.';
                            $tipo_alerta = 'warning';
                        }
                    } else {
                        $mensaje = 'No se pudo guardar el archivo del ticket.';
                        $tipo_alerta = 'danger';
                    }
                }
            } else {
                $mensaje = 'Se produjo un error al subir el archivo.';
                $tipo_alerta = 'danger';
            }
        }

        if ($fecha_compra === '' && $tipo_alerta !== 'danger') {
            $mensaje = 'No se ha podido detectar la fecha de compra automáticamente. Revísala manualmente.';
            $tipo_alerta = 'warning';
        }

        if ($fecha_compra !== '' && $fecha_vencimiento !== '' && $fecha_vencimiento < $fecha_compra) {
            $mensaje = 'La fecha de vencimiento no puede ser anterior a la fecha de compra.';
            $tipo_alerta = 'danger';
        }

        if ($tipo_alerta !== 'danger') {
            $estado = calcularEstado($fecha_vencimiento);

            try {
                $sql = "INSERT INTO garantias
                        (id_usuario, nombre_producto, tienda, fecha_compra, fecha_vencimiento, archivo_ticket, comentarios, estado)
                        VALUES
                        (:id_usuario, :nombre_producto, :tienda, :fecha_compra, :fecha_vencimiento, :archivo_ticket, :comentarios, :estado)";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':id_usuario' => $_SESSION['id_usuario'],
                    ':nombre_producto' => $nombre_producto,
                    ':tienda' => $tienda !== '' ? $tienda : null,
                    ':fecha_compra' => $fecha_compra,
                    ':fecha_vencimiento' => $fecha_vencimiento,
                    ':archivo_ticket' => $archivo_ticket,
                    ':comentarios' => $comentarios !== '' ? $comentarios : null,
                    ':estado' => $estado
                ]);

                header('Location: index.php');
                exit();
            } catch (PDOException $e) {
                $mensaje = 'Error al guardar la garantía: ' . $e->getMessage();
                $tipo_alerta = 'danger';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva garantía - TicKeep</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/nueva-garantia.css">
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
        <div class="main-card">
            <div class="icon-box">📦</div>
            <h2 class="title-box">Añadir una nueva garantía</h2>

            <?php if ($mensaje !== ''): ?>
                <div class="alert alert-<?= htmlspecialchars($tipo_alerta) ?> text-center">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="nombre_producto" class="form-label">Nombre del producto *</label>
                    <input type="text" class="form-control" id="nombre_producto" name="nombre_producto"
                        value="<?= htmlspecialchars($nombre_producto) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="tienda" class="form-label">Tienda</label>
                    <input type="text" class="form-control" id="tienda" name="tienda"
                        value="<?= htmlspecialchars($tienda) ?>">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_compra" class="form-label">Fecha de compra *</label>
                        <input type="date" class="form-control" id="fecha_compra" name="fecha_compra"
                            value="<?= htmlspecialchars($fecha_compra) ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="fecha_vencimiento" class="form-label">Fecha de vencimiento *</label>
                        <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento"
                            value="<?= htmlspecialchars($fecha_vencimiento) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="archivo_ticket" class="form-label">Subir ticket</label>
                    <input type="file" class="form-control" id="archivo_ticket" name="archivo_ticket"
                        accept=".jpg,.jpeg,.png,.webp">
                </div>

                <div class="mb-4">
                    <label for="comentarios" class="form-label">Comentarios</label>
                    <textarea class="form-control" id="comentarios" name="comentarios"
                        rows="6"><?= htmlspecialchars($comentarios) ?></textarea>
                </div>

                <button type="submit" class="btn btn-save text-white w-100">Añadir producto</button>
            </form>
        </div>
    </div>

    <footer>
        © 2025 TicKeep. Todos los derechos reservados.<br>
        Tu tranquilidad, garantizada.
    </footer>

</body>

</html>