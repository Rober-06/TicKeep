<?php
// registro.php
require 'config/db.php';

$mensaje = "";
$tipo_alerta = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validaciones básicas
    if (empty($nombre) || empty($email) || empty($password)) {
        $mensaje = "Por favor, rellena todos los campos.";
        $tipo_alerta = "danger";
    } elseif ($password !== $confirm_password) {
        $mensaje = "Las contraseñas no coinciden.";
        $tipo_alerta = "danger";
    } else {
        // Verificar duplicados
        $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        
        if ($stmt->rowCount() > 0) {
            $mensaje = "Ese correo ya está registrado.";
            $tipo_alerta = "warning";
        } else {
            // Cifrado de contraseña (Seguridad)
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            try {
                $pdo->beginTransaction(); // Iniciamos transacción para asegurar integridad

                // 1. Crear Usuario
                $sql = "INSERT INTO usuarios (nombre, email, password) VALUES (:nombre, :email, :pass)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':nombre' => $nombre, ':email' => $email, ':pass' => $password_hash]);
                
                $id_nuevo_usuario = $pdo->lastInsertId();

                // 2. Crear Configuración por defecto (Relación 1:1)
                $sqlConfig = "INSERT INTO opciones_configuracion (id_usuario) VALUES (:id)";
                $stmtConfig = $pdo->prepare($sqlConfig);
                $stmtConfig->execute([':id' => $id_nuevo_usuario]);

                $pdo->commit(); // Guardamos todo

                $mensaje = "¡Cuenta creada con éxito! Redirigiendo...";
                $tipo_alerta = "success";
                header("refresh:2;url=login.php"); // Redirección automática

            } catch (PDOException $e) {
                $pdo->rollBack(); // Si falla algo, deshacemos cambios
                $mensaje = "Error en el sistema: " . $e->getMessage();
                $tipo_alerta = "danger";
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
    <title>Registro - TicKeep</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="auth-card">
    <div class="brand-title">TicKeep</div>
    <h4 class="text-center mb-4">Crear cuenta</h4>

    <?php if(!empty($mensaje)): ?>
        <div class="alert alert-<?= $tipo_alerta; ?> text-center"><?= $mensaje; ?></div>
    <?php endif; ?>

    <form method="POST" action="registro.php">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" name="nombre" placeholder="Tu nombre completo" required>
        </div>
        
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" name="email" placeholder="ejemplo@correo.com" required>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" name="password" placeholder="Mínimo 6 caracteres" required>
        </div>
        
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirmar contraseña</label>
            <input type="password" class="form-control" name="confirm_password" placeholder="Repite tu contraseña" required>
        </div>

        <button type="submit" class="btn btn-tickeep">Registrarse</button>
    </form>

    <div class="auth-links">
        ¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a>
    </div>
</div>

</body>
</html>