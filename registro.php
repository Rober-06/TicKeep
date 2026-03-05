<?php
require 'config/db.php';

$mensaje = "";
$tipo_alerta = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nombre = trim($_POST['nombre'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  if ($nombre === '' || $email === '' || $password === '' || $confirm_password === '') {
    $mensaje = "Por favor, rellena todos los campos.";
    $tipo_alerta = "danger";
  } elseif ($password !== $confirm_password) {
    $mensaje = "Las contraseñas no coinciden.";
    $tipo_alerta = "danger";
  } else {
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);

    if ($stmt->rowCount() > 0) {
      $mensaje = "Ese correo ya está registrado.";
      $tipo_alerta = "warning";
    } else {
      $password_hash = password_hash($password, PASSWORD_BCRYPT);

      try {
        $pdo->beginTransaction();

        $sql = "INSERT INTO usuarios (nombre, email, password) VALUES (:nombre, :email, :pass)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
          ':nombre' => $nombre,
          ':email' => $email,
          ':pass' => $password_hash
        ]);

        $id_nuevo_usuario = $pdo->lastInsertId();

        $sqlConfig = "INSERT INTO opciones_configuracion (id_usuario) VALUES (:id)";
        $stmtConfig = $pdo->prepare($sqlConfig);
        $stmtConfig->execute([':id' => $id_nuevo_usuario]);

        $pdo->commit();

        $mensaje = "¡Cuenta creada con éxito! Redirigiendo...";
        $tipo_alerta = "success";
        header("refresh:2;url=login.php");
      } catch (PDOException $e) {
        $pdo->rollBack();
        $mensaje = "Error en el sistema.";
        $tipo_alerta = "danger";
      }
    }
  }
}

$pageTitle = "TicKeep | Registro";
$activeTab = "register";
require 'partials/header_auth.php';
require 'partials/auth_tabs.php';
?>

<?php if ($mensaje !== ""): ?>
  <div class="alert alert-<?= htmlspecialchars($tipo_alerta); ?> text-center" role="alert">
    <?= htmlspecialchars($mensaje); ?>
  </div>
<?php endif; ?>

<form method="POST" novalidate>
  <div class="mb-3">
    <label for="nombre" class="form-label">Nombre</label>
    <input type="text" class="form-control form-control-lg" id="nombre" name="nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required />
  </div>

  <div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control form-control-lg" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required />
  </div>

  <div class="mb-3">
    <label for="password" class="form-label">Contraseña</label>
    <input type="password" class="form-control form-control-lg" id="password" name="password" required />
  </div>

  <div class="mb-4">
    <label for="confirm_password" class="form-label">Confirmar contraseña</label>
    <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password" required />
  </div>

  <button type="submit" class="btn btn-primary btn-lg w-100">
    Crear Cuenta
  </button>
</form>

<?php require 'partials/footer_auth.php'; ?>