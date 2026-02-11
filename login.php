<?php
// login.php
session_start(); // Obligatorio para manejar sesiones
require 'config/db.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $mensaje = "Introduce tu email y contraseña.";
    } else {
        // Buscar usuario por email
        $stmt = $pdo->prepare("SELECT id_usuario, nombre, password FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        // Verificar si existe y si la contraseña coincide
        if ($usuario && password_verify($password, $usuario['password'])) {
            // ¡LOGIN CORRECTO!
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            
            // Redirigir al panel principal (Dashboard)
            header("Location: index.php"); 
            exit();
        } else {
            $mensaje = "Credenciales incorrectas.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - TicKeep</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="auth-card">
    <div class="brand-title">TicKeep</div>
    <h4 class="text-center mb-4">Bienvenido de nuevo</h4>

    <?php if(!empty($mensaje)): ?>
        <div class="alert alert-danger text-center"><?= $mensaje; ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" name="email" placeholder="Introduce tu email" required>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" name="password" placeholder="Introduce tu contraseña" required>
        </div>

        <button type="submit" class="btn btn-tickeep">Iniciar sesión</button>
    </form>

    <div class="auth-links">
        ¿No tienes cuenta? <a href="registro.php">Regístrate gratis</a>
    </div>
</div>

</body>
</html>