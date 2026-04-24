<?php
session_start();
require 'config/bd.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$mensaje = '';
$tipo_alerta = '';

try {
    $sql = "SELECT u.nombre, u.email, c.foto_perfil, c.notificaciones_email, c.aviso_vencimiento, c.idioma, c.tema
            FROM usuarios u
            LEFT JOIN opciones_configuracion c ON u.id_usuario = c.id_usuario
            WHERE u.id_usuario = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        die("Usuario no encontrado.");
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$fotoPerfil = !empty($usuario['foto_perfil']) ? $usuario['foto_perfil'] : 'default-avatar.png';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $notificaciones_email = isset($_POST['notificaciones_email']) ? 1 : 0;
    $aviso_vencimiento = isset($_POST['aviso_vencimiento']) ? 1 : 0;
    $idioma = trim($_POST['idioma'] ?? 'Español');
    $tema = trim($_POST['tema'] ?? 'claro');

    if ($nombre === '' || $email === '') {
        $mensaje = 'El nombre y el correo son obligatorios.';
        $tipo_alerta = 'danger';
    } else {
        try {
            $pdo->beginTransaction();

            if ($password !== '') {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);

                $sqlUser = "UPDATE usuarios 
                            SET nombre = :nombre, email = :email, contrasena = :contrasena 
                            WHERE id_usuario = :id";
                $stmtUser = $pdo->prepare($sqlUser);
                $stmtUser->execute([
                    ':nombre' => $nombre,
                    ':email' => $email,
                    ':contrasena' => $passwordHash,
                    ':id' => $id_usuario
                ]);
            } else {
                $sqlUser = "UPDATE usuarios 
                            SET nombre = :nombre, email = :email 
                            WHERE id_usuario = :id";
                $stmtUser = $pdo->prepare($sqlUser);
                $stmtUser->execute([
                    ':nombre' => $nombre,
                    ':email' => $email,
                    ':id' => $id_usuario
                ]);
            }

            $nuevaFoto = $fotoPerfil;

            if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                    $directorio = 'assets/img/';

                    $extension = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
                    $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

                    if (in_array($extension, $permitidas, true)) {
                        $nuevoNombre = 'perfil_' . $id_usuario . '_' . time() . '.' . $extension;
                        $rutaFinal = $directorio . $nuevoNombre;

                        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $rutaFinal)) {
                            $nuevaFoto = $nuevoNombre;
                        }
                    }
                }
            }

            $sqlConfig = "UPDATE opciones_configuracion
                          SET foto_perfil = :foto,
                              notificaciones_email = :notificaciones,
                              aviso_vencimiento = :aviso,
                              idioma = :idioma,
                              tema = :tema
                          WHERE id_usuario = :id";

            $stmtConfig = $pdo->prepare($sqlConfig);
            $stmtConfig->execute([
                ':foto' => $nuevaFoto,
                ':notificaciones' => $notificaciones_email,
                ':aviso' => $aviso_vencimiento,
                ':idioma' => $idioma,
                ':tema' => $tema,
                ':id' => $id_usuario
            ]);

            $pdo->commit();

            $_SESSION['nombre'] = $nombre;

            header("Location: configuracion.php?guardado=1");
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            $mensaje = 'Error al guardar la configuración.';
            $tipo_alerta = 'danger';
        }
    }
}

if (isset($_GET['guardado'])) {
    $mensaje = 'Configuración guardada correctamente.';
    $tipo_alerta = 'success';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración - TicKeep</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css">

    <style>
        body {
            background: #f2f2f2;
        }

        .config-card {
            max-width: 720px;
            margin: 70px auto;
            background: white;
            border-radius: 8px;
            padding: 45px;
            box-shadow: 0 2px 8px rgba(0,0,0,.15);
        }

        .profile-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 25px;
            position: relative;
        }

        .profile-img {
            width: 145px;
            height: 145px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #333;
        }

        .edit-photo {
            position: absolute;
            bottom: 5px;
            margin-left: 95px;
            background: white;
            border: 1px solid #444;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .section-title {
            color: #202bbf;
            font-weight: 700;
            margin-top: 30px;
            margin-bottom: 18px;
        }

        .btn-logout-red {
            background: #dc2626;
            color: white;
            border: none;
            padding: 9px 25px;
            border-radius: 7px;
            text-decoration: none;
        }

        .btn-logout-red:hover {
            background: #b91c1c;
            color: white;
        }

        footer {
            background: #202bbf;
            color: white;
            text-align: center;
            padding: 18px;
            font-size: .8rem;
            margin-top: 80px;
        }
    </style>
</head>
<body>

<header class="tk-header">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="index.php" class="tk-logo">TicKeep</a>
        <div class="d-flex align-items-center gap-2">
            <span class="text-white d-none d-sm-block"><?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?></span>
            <img src="assets/img/<?= htmlspecialchars($fotoPerfil); ?>" class="avatar-img" alt="Perfil">
        </div>
    </div>
</header>

<main class="container">
    <div style="max-width:720px; margin:50px auto 0;">
        <a href="index.php" class="text-decoration-none">← Volver</a>
    </div>

    <div class="config-card">
        <form method="POST" enctype="multipart/form-data">
            <div class="profile-wrapper">
                <img src="assets/img/<?= htmlspecialchars($fotoPerfil); ?>" class="profile-img" alt="Foto perfil">

                <label for="foto_perfil" class="edit-photo">
                    ✎
                </label>

                <input type="file" id="foto_perfil" name="foto_perfil" accept=".jpg,.jpeg,.png,.webp" hidden>
            </div>

            <h2 class="section-title">Configuración</h2>

            <?php if ($mensaje !== ''): ?>
                <div class="alert alert-<?= htmlspecialchars($tipo_alerta) ?>">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <h5 class="section-title">Perfil de usuario</h5>

            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control"
                       value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Correo electrónico</label>
                <input type="email" name="email" class="form-control"
                       value="<?= htmlspecialchars($usuario['email']) ?>" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Contraseña nueva</label>
                <input type="password" name="password" class="form-control"
                       placeholder="Déjalo vacío si no quieres cambiarla">
            </div>

            <h5 class="section-title">Notificaciones</h5>

            <div class="form-check mb-3 d-flex justify-content-between">
                <label class="form-check-label" for="notificaciones_email">
                    Recibir notificaciones por correo electrónico
                </label>
                <input class="form-check-input" type="checkbox" id="notificaciones_email"
                       name="notificaciones_email"
                    <?= !empty($usuario['notificaciones_email']) ? 'checked' : '' ?>>
            </div>

            <div class="form-check mb-4 d-flex justify-content-between">
                <label class="form-check-label" for="aviso_vencimiento">
                    Avisar cuando una garantía esté a punto de vencer
                </label>
                <input class="form-check-input" type="checkbox" id="aviso_vencimiento"
                       name="aviso_vencimiento"
                    <?= !empty($usuario['aviso_vencimiento']) ? 'checked' : '' ?>>
            </div>

            <h5 class="section-title">Preferencias</h5>

            <div class="mb-4">
                <label class="form-label">Idioma</label>
                <select name="idioma" class="form-control">
                    <option value="Español" <?= ($usuario['idioma'] ?? '') === 'Español' ? 'selected' : '' ?>>Español</option>
                    <option value="Inglés" <?= ($usuario['idioma'] ?? '') === 'Inglés' ? 'selected' : '' ?>>Inglés</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label d-block">Tema</label>

                <label class="me-3">
                    <input type="radio" name="tema" value="claro" <?= ($usuario['tema'] ?? 'claro') === 'claro' ? 'checked' : '' ?>>
                    Claro
                </label>

                <label>
                    <input type="radio" name="tema" value="oscuro" <?= ($usuario['tema'] ?? '') === 'oscuro' ? 'checked' : '' ?>>
                    Oscuro
                </label>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Guardar configuración
            </button>
        </form>
    </div>

    <div class="text-end" style="max-width:720px; margin: -45px auto 0;">
        <a href="logout.php" class="btn-logout-red">Cerrar sesión</a>
    </div>
</main>

<footer>
    © 2025 TicKeep. Todos los derechos reservados.<br>
    Tu tranquilidad, garantizada.
</footer>

</body>
</html>