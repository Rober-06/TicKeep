<?php
session_start();
require 'config/db.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $mensaje = "Introduce tu email y contraseña.";
    } else {
        $stmt = $pdo->prepare("SELECT id_usuario, nombre, password FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            header("Location: index.php");
            exit;
        } else {
            $mensaje = "Credenciales incorrectas.";
        }
    }
}

$pageTitle = "TicKeep | Acceso";
$activeTab = "login";
require 'partials/header_auth.php';
require 'partials/auth_tabs.php';
?>

<form method="POST" novalidate>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control form-control-lg" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required />
    </div>

    <div class="mb-4">
        <label for="password" class="form-label">Contraseña</label>
        <input type="password" class="form-control form-control-lg" id="password" name="password" required />
    </div>

    <?php if ($mensaje !== ""): ?>
        <div class="alert alert-danger text-center" role="alert">
            <?= htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <button type="submit" class="btn btn-primary btn-lg w-100">
        Iniciar Sesión
    </button>
</form>

<?php require 'partials/footer_auth.php'; ?>