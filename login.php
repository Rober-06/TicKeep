<?php
session_start();
require 'config/bd.php';

$mensaje = "";

// Comprobar si los datos introducidos están ya registrados
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $mensaje = "Introduce tu email y contraseña.";
    } else {
        $stmt = $pdo->prepare("SELECT id_usuario, nombre, contrasena FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario['contrasena'])) {
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
<head>
    <style>/* ===== VARIABLES ===== */
:root {
    --tk-dark:               #111827;
    --tk-blue-primary:       #1D4ED8;
    --tk-blue-hover:         #1e40af;
    --tk-blue-light:         #EFF6FF;
    --tk-gray-muted:         #6B7280;
    --tk-gray-border:        #E5E7EB;
    --tk-bg:                 #F3F4F6;
}

/* ===== GLOBAL ===== */
*, *::before, *::after { box-sizing: border-box; }

body {
    background-color: var(--tk-bg);
    font-family: 'Poppins', sans-serif;
    color: var(--tk-dark);
    min-height: 100vh;
    margin: 0;
    display: flex;
    flex-direction: column;
}

/* ===== HEADER ===== */
.tk-header {
    background: linear-gradient(135deg, var(--tk-blue-primary) 0%, #1e3a8a 100%);
    padding: 1rem 0;
    width: 100%;
    box-shadow: 0 2px 8px rgba(29,78,216,0.25);
}
.tk-logo {
    color: #fff;
    font-weight: 700;
    font-size: 1.5rem;
    text-decoration: none;
    letter-spacing: -0.5px;
}
.tk-logo:hover { color: #bfdbfe; }

.avatar-img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,0.6);
}

/* ===== AUTH LAYOUT ===== */
.tk-auth {
    flex: 1;
    min-height: calc(100vh - 68px - 100px);
    padding: 2.5rem 0;
    background: linear-gradient(160deg, #EFF6FF 0%, #F3F4F6 60%);
}

/* ===== AUTH CARD ===== */
.tk-card {
    border-radius: 18px !important;
    overflow: hidden;
}
.tk-card::before {
    content: '';
    display: block;
    height: 5px;
    background: linear-gradient(90deg, var(--tk-blue-primary), #60a5fa);
}
.card-body { padding: 2rem 2.2rem !important; }

/* ===== AUTH TABS ===== */
.tk-tabs {
    display: flex;
    border-bottom: 2px solid var(--tk-gray-border);
    margin-bottom: 1.75rem;
    gap: 0;
}
.tk-tab {
    flex: 1;
    text-align: center;
    text-decoration: none;
    padding: 0.65rem 1rem;
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--tk-gray-muted);
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    transition: color 0.15s, border-color 0.15s;
}
.tk-tab:hover { color: var(--tk-blue-primary); }
.tk-tab.is-active {
    color: var(--tk-blue-primary);
    border-bottom-color: var(--tk-blue-primary);
}

/* ===== FORM LABELS ===== */
.form-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.35rem;
}

/* ===== INPUTS ===== */
.form-control {
    border-radius: 10px !important;
    border: 1.5px solid var(--tk-gray-border);
    font-size: 0.95rem;
    padding: 0.65rem 1rem;
    transition: border-color 0.2s, box-shadow 0.2s;
    background-color: #FAFAFA;
}
.form-control:focus {
    border-color: var(--tk-blue-primary);
    box-shadow: 0 0 0 3px rgba(29,78,216,0.12);
    background-color: #fff;
}
.form-control-lg { font-size: 1rem !important; padding: 0.75rem 1.1rem !important; }

/* ===== SUBMIT BUTTON ===== */
.btn-primary {
    background: linear-gradient(135deg, var(--tk-blue-primary), #2563eb) !important;
    border: none !important;
    border-radius: 10px !important;
    font-weight: 600;
    letter-spacing: 0.02em;
    padding: 0.75rem 1.5rem;
    transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
    box-shadow: 0 4px 14px rgba(29,78,216,0.3);
}
.btn-primary:hover {
    opacity: 0.93;
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(29,78,216,0.35);
}
.btn-primary:active { transform: translateY(0); }

/* ===== ALERTS ===== */
.alert {
    border-radius: 10px;
    font-size: 0.88rem;
    padding: 0.65rem 1rem;
}

/* ===== INDEX — TITLE SECTION ===== */
.title-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 0.75rem;
}
.title-section h2 {
    color: var(--tk-blue-primary);
    font-weight: 700;
    font-size: 1.5rem;
}

/* ===== BUTTONS ===== */
.tk-btn-primary {
    background: linear-gradient(135deg, var(--tk-blue-primary), #2563eb);
    color: #fff;
    border: none;
    border-radius: 50px;
    padding: 0.55rem 1.4rem;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    box-shadow: 0 3px 10px rgba(29,78,216,0.25);
    transition: opacity 0.2s, transform 0.15s;
}
.tk-btn-primary:hover { opacity: 0.9; transform: translateY(-1px); color: #fff; }

.tk-btn-export {
    background: #E2E8F0;
    color: #475569;
    border-radius: 50px;
    padding: 0.55rem 1.2rem;
    border: none;
    font-weight: 500;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background 0.15s;
}
.tk-btn-export:hover { background: #cbd5e1; }

/* ===== SEARCH ===== */
.search-input-wrapper { position: relative; }
.search-input {
    width: 100%;
    padding: 0.75rem 1.2rem 0.75rem 3rem;
    border-radius: 50px;
    border: 1.5px solid var(--tk-gray-border);
    background: #fff;
    font-size: 0.9rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.search-input:focus {
    outline: none;
    border-color: var(--tk-blue-primary);
    box-shadow: 0 0 0 3px rgba(29,78,216,0.1);
}

/* ===== FILTER PILLS ===== */
.filter-pills { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.filter-pill {
    background: #E2E8F0;
    color: #475569;
    padding: 0.35rem 1.1rem;
    border-radius: 50px;
    border: none;
    font-size: 0.82rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}
.filter-pill:hover { background: #cbd5e1; }
.filter-pill.active { background: var(--tk-blue-primary); color: #fff; }

/* ===== TICKET CARD ===== */
.tk-ticket-card {
    background: #fff;
    border: 1px solid var(--tk-gray-border);
    border-radius: 14px;
    padding: 1.1rem 1.3rem;
    margin-bottom: 1rem;
    display: flex;
    gap: 1.25rem;
    align-items: center;
    transition: transform 0.18s, box-shadow 0.18s;
}
.tk-ticket-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
}
.ticket-thumb {
    width: 90px;
    height: 90px;
    border-radius: 10px;
    object-fit: cover;
    flex-shrink: 0;
    background: #f1f5f9;
}
.ticket-info { flex-grow: 1; min-width: 0; }
.ticket-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.3rem; }
.ticket-title { margin: 0; font-weight: 700; font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.store-name { color: var(--tk-blue-primary); }
.ticket-coments { color: var(--tk-gray-muted); font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ticket-expiry { font-size: 0.82rem; color: #374151; }

.tk-btn-details {
    background: #EFF6FF;
    color: var(--tk-blue-primary);
    border-radius: 8px;
    padding: 0.5rem 1rem;
    font-size: 0.83rem;
    font-weight: 600;
    white-space: nowrap;
    flex-shrink: 0;
    transition: background 0.15s;
}
.tk-btn-details:hover { background: #dbeafe; color: var(--tk-blue-primary); }

/* ===== STATUS BADGES ===== */
.status-badge {
    padding: 0.2rem 0.65rem;
    border-radius: 50px;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    white-space: nowrap;
    flex-shrink: 0;
}
.badge-vigente      { background: #D1FAE5; color: #065F46; }
.badge-expira-pronto{ background: #FEF3C7; color: #92400E; }
.badge-caducada     { background: #FEE2E2; color: #991B1B; }

/* ===== FOOTER ===== */
.main-footer {
    background: linear-gradient(135deg, var(--tk-blue-primary) 0%, #1e3a8a 100%);
    color: rgba(255,255,255,0.9);
    text-align: center;
    padding: 1.75rem;
    margin-top: auto;
    font-size: 0.88rem;
}
.x-small { font-size: 0.78rem; color: rgba(255,255,255,0.65); }

/* ===== RESPONSIVE ===== */
@media (max-width: 576px) {
    .tk-ticket-card { flex-wrap: wrap; }
    .ticket-thumb { width: 70px; height: 70px; }
    .tk-btn-details { width: 100%; text-align: center; }
    .title-section { flex-direction: column; align-items: flex-start; }
}
</style>
</head>
<form method="POST" novalidate>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control form-control-lg" id="email" name="email"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required />
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