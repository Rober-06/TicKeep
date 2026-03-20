<?php
session_start();
require 'config/bd.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// Obtener datos del usuario y sus garantías
try {
    $queryUser = "SELECT u.nombre, c.foto_perfil FROM usuarios u 
                  LEFT JOIN opciones_configuracion c ON u.id_usuario = c.id_usuario 
                  WHERE u.id_usuario = :id";
    $stmtUser = $pdo->prepare($queryUser);
    $stmtUser->execute([':id' => $id_usuario]);
    $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

    $queryGarantias = "SELECT * FROM garantias WHERE id_usuario = :id ORDER BY fecha_compra DESC";
    $stmtGarantias = $pdo->prepare($queryGarantias);
    $stmtGarantias->execute([':id' => $id_usuario]);
    $garantias = $stmtGarantias->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$fotoPerfil = !empty($userData['foto_perfil']) ? $userData['foto_perfil'] : 'default-avatar.png';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TicKeep | Mis Garantías</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>

<body>

    <header class="tk-header">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="index.php" class="tk-logo">TicKeep</a>
            <div class="d-flex align-items-center gap-2">
                <span class="text-white d-none d-sm-block"><?= htmlspecialchars($userData['nombre']); ?></span>
                <img src="assets/img/<?= htmlspecialchars($fotoPerfil); ?>" class="avatar-img" alt="Perfil">
            </div>
        </div>
    </header>

    <main class="container my-5">
        <section class="title-section">
            <h2>Mis garantías</h2>
            <div class="d-flex gap-2">
                <button class="tk-btn-export">Exportar a...</button>
                <a href="nueva-garantia.php" class="tk-btn-primary text-decoration-none">+ Nueva garantía</a>
            </div>
        </section>

        <section class="search-input-wrapper">
            <input type="text" class="search-input" placeholder="Buscar por producto o tienda...">
        </section>

        <section class="filter-pills">
            <button class="filter-pill active">Todo</button>
            <button class="filter-pill">Vigente</button>
            <button class="filter-pill">Próximo a vencer</button>
        </section>

        <section class="mt-4">
            <?php if (count($garantias) > 0): ?>
                <?php foreach ($garantias as $g): ?>
                    <div class="tk-ticket-card bg-white shadow-sm">
                        <img src="uploads/<?= !empty($g['archivo_ticket']) ? $g['archivo_ticket'] : 'default.png'; ?>"
                            class="ticket-thumb" alt="Producto">

                        <div class="ticket-info">
                            <div class="ticket-header">
                                <h3 class="ticket-title"><?= htmlspecialchars($g['nombre_producto']); ?></h3>
                                <?php
                                $status = $g['estado'] ?? 'Vigente';
                                $badge = 'badge-vigente';
                                if ($status == 'Expira pronto')
                                    $badge = 'badge-expira-pronto';
                                if ($status == 'Caducada')
                                    $badge = 'badge-caducada';
                                ?>
                                <span class="status-badge <?= $badge ?>"><?= $status ?></span>
                            </div>
                            <p class="mb-1">Comprado en: <span
                                    class="store-name fw-bold"><?= htmlspecialchars($g['tienda']); ?></span></p>
                            <p class="ticket-coments mb-2 text-muted small"><?= htmlspecialchars($g['comentarios']); ?></p>
                            <p class="ticket-expiry mb-0 small">Vence el:
                                <b><?= date('d/m/Y', strtotime($g['fecha_vencimiento'])); ?></b>
                            </p>
                        </div>

                        <a href="detalle.php?id=<?= $g['id_garantia']; ?>" class="tk-btn-details text-decoration-none">Ver
                            detalles</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5 opacity-50">
                    <p>No tienes garantías registradas actualmente.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="main-footer">
        <p class="mb-1">© 2026 TicKeep. Todos los derechos reservados.</p>
        <p class="mb-0 x-small fw-light">Tu tranquilidad, garantizada.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>