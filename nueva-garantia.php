<?php
session_start();
require 'config/bd.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit();
}

$mensaje = '';
$tipo_alerta = '';

// Función para calcular el estado automáticamente
function calcularEstado($fechaVencimiento) {
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
    $nombre_producto = trim($_POST['nombre_producto'] ?? '');
    $tienda = trim($_POST['tienda'] ?? '');
    $fecha_compra = trim($_POST['fecha_compra'] ?? '');
    $fecha_vencimiento = trim($_POST['fecha_vencimiento'] ?? '');
    $comentarios = trim($_POST['comentarios'] ?? '');

    $archivo_ticket = null;

    // Validación de campos
    if ($nombre_producto === '' || $fecha_compra === '' || $fecha_vencimiento === '') {
        $mensaje = 'Por favor, rellena los campos obligatorios.';
        $tipo_alerta = 'danger';
    } elseif ($fecha_vencimiento < $fecha_compra) {
        $mensaje = 'La fecha de vencimiento no puede ser anterior a la fecha de compra.';
        $tipo_alerta = 'danger';
    } else {
        // Procesar ticket si se ha subido
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
                $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

                if (!in_array($extension, $permitidas, true)) {
                    $mensaje = 'Formato no permitido. Solo se aceptan JPG, PNG, WEBP o PDF.';
                    $tipo_alerta = 'danger';
                } elseif ($tamano > 5 * 1024 * 1024) {
                    $mensaje = 'El archivo supera el máximo permitido de 5 MB.';
                    $tipo_alerta = 'danger';
                } else {
                    $nuevo_nombre = 'ticket_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
                    $ruta_final = $directorio . $nuevo_nombre;

                    if (move_uploaded_file($tmp_name, $ruta_final)) {
                        $archivo_ticket = $ruta_final;
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

        // Insertar nueva garantía si no hay errores
        if ($mensaje === '') {
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

                $mensaje = 'Garantía añadida correctamente.';
                $tipo_alerta = 'success';

                // Limpiar formulario al añadir
                $_POST = [];
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
    <style>
        body {
            background-color: #efefef;
        }

        .topbar {
            background: #202bbf;
            min-height: 70px;
        }

        .topbar .brand {
            color: #fff;
            font-weight: 700;
            text-decoration: none;
        }

        .main-card {
            max-width: 820px;
            margin: 60px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
            padding: 45px 35px;
        }

        .icon-box {
            font-size: 2rem;
            text-align: center;
            margin-bottom: 10px;
        }

        .title-box {
            text-align: center;
            font-weight: 700;
            margin-bottom: 35px;
        }

        .btn-save {
            background: #0d7fc0;
            border: none;
        }

        .btn-save:hover {
            background: #0b6da4;
        }

        footer {
            background: #202bbf;
            color: #fff;
            text-align: center;
            padding: 18px 10px;
            margin-top: 80px;
            font-size: 0.8rem;
        }
    </style>
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
                <input
                    type="text"
                    class="form-control"
                    id="nombre_producto"
                    name="nombre_producto"
                    value="<?= htmlspecialchars($_POST['nombre_producto'] ?? '') ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="tienda" class="form-label">Tienda</label>
                <input
                    type="text"
                    class="form-control"
                    id="tienda"
                    name="tienda"
                    value="<?= htmlspecialchars($_POST['tienda'] ?? '') ?>"
                >
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fecha_compra" class="form-label">Fecha de compra *</label>
                    <input
                        type="date"
                        class="form-control"
                        id="fecha_compra"
                        name="fecha_compra"
                        value="<?= htmlspecialchars($_POST['fecha_compra'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label for="fecha_vencimiento" class="form-label">Fecha de vencimiento *</label>
                    <input
                        type="date"
                        class="form-control"
                        id="fecha_vencimiento"
                        name="fecha_vencimiento"
                        value="<?= htmlspecialchars($_POST['fecha_vencimiento'] ?? '') ?>"
                        required
                    >
                </div>
            </div>

            <div class="mb-3">
                <label for="archivo_ticket" class="form-label">Subir ticket</label>
                <input
                    type="file"
                    class="form-control"
                    id="archivo_ticket"
                    name="archivo_ticket"
                    accept=".jpg,.jpeg,.png,.webp,.pdf"
                >
            </div>

            <div class="mb-4">
                <label for="comentarios" class="form-label">Comentarios</label>
                <textarea
                    class="form-control"
                    id="comentarios"
                    name="comentarios"
                    rows="4"
                ><?= htmlspecialchars($_POST['comentarios'] ?? '') ?></textarea>
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