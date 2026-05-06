<?php
// ══════════════════════════════════════════════════════════════════════════════
//  DASHBOARD – Página protegida (solo usuarios autenticados)
// ══════════════════════════════════════════════════════════════════════════════

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/db.php';

csrf_session_start();

// Guard: redirigir al login si no hay sesión
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId    = (int) $_SESSION['user_id'];
$userName  = htmlspecialchars($_SESSION['user_name']  ?? '', ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');
$loggedAt  = htmlspecialchars($_SESSION['logged_at']  ?? '', ENT_QUOTES, 'UTF-8');
$userIp    = htmlspecialchars($_SESSION['user_ip']    ?? '', ENT_QUOTES, 'UTF-8');

// Datos del servidor en esta petición
$serverInfo = [
    'IP cliente'  => htmlspecialchars($_SERVER['REMOTE_ADDR']     ?? 'N/A', ENT_QUOTES, 'UTF-8'),
    'User-Agent'  => htmlspecialchars(substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 80), ENT_QUOTES, 'UTF-8'),
    'Host'        => htmlspecialchars($_SERVER['HTTP_HOST']       ?? 'N/A', ENT_QUOTES, 'UTF-8'),
    'Protocolo'   => htmlspecialchars($_SERVER['SERVER_PROTOCOL'] ?? 'N/A', ENT_QUOTES, 'UTF-8'),
    'HTTPS'       => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'Sí ✅' : 'No ❌',
    'Session ID'  => htmlspecialchars(session_id(), ENT_QUOTES, 'UTF-8'),
];
?>
<!DOCTYPE html>
<html lang="es" data-theme="customTheme">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard · UTP</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5/themes.css" rel="stylesheet" type="text/css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hammersmith+One&family=Clear+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="hero bg-base-200 min-h-screen py-10">
    <div class="hero-content w-full max-w-lg flex-col">

        <div class="text-center mb-2">
            <div class="badge badge-accent badge-outline mb-3 text-xs tracking-widest uppercase">
                Dashboard · Área protegida
            </div>
            <h1 class="text-3xl text-base-content">¡Bienvenido, <?= $userName ?>!</h1>
            <p class="text-base-content/50 text-sm mt-1">
                Sesión activa · ID #<?= $userId ?>
            </p>
        </div>

        <div class="card bg-base-100 shadow-xl w-full">
            <div class="card-body gap-4">

                <!-- Datos de sesión -->
                <div class="rounded-box border border-base-300 overflow-hidden text-sm">
                    <table class="table table-sm">
                        <thead class="bg-base-200">
                            <tr>
                                <th colspan="2" class="text-xs tracking-wider uppercase text-base-content/60">
                                    👤 Datos de sesión
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td class="text-base-content/50 w-36">ID</td>
                                <td><span class="badge badge-primary badge-sm">#<?= $userId ?></span></td></tr>
                            <tr><td class="text-base-content/50">Nombre</td>
                                <td class="font-medium"><?= $userName ?></td></tr>
                            <tr><td class="text-base-content/50">Email</td>
                                <td class="font-mono text-xs"><?= $userEmail ?></td></tr>
                            <tr><td class="text-base-content/50">Login en</td>
                                <td class="font-mono text-xs"><?= $loggedAt ?></td></tr>
                            <tr><td class="text-base-content/50">IP login</td>
                                <td class="font-mono text-xs"><?= $userIp ?></td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- $_SERVER info -->
                <div class="rounded-box border border-base-300 overflow-hidden text-sm">
                    <table class="table table-sm">
                        <thead class="bg-base-200">
                            <tr>
                                <th colspan="2" class="text-xs tracking-wider uppercase text-base-content/60">
                                    🖥️ $_SERVER (petición actual)
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($serverInfo as $k => $v): ?>
                            <tr>
                                <td class="text-base-content/50 w-36"><?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="font-mono text-xs break-all"><?= $v ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Logout -->
                <a href="logout.php" class="btn btn-outline btn-error w-full">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Cerrar sesión
                </a>

            </div>
        </div>

        <p class="text-center text-xs text-base-content/30 mt-2">
            Desarrollo de Software VII · Universidad Tecnológica de Panamá
        </p>
    </div>
</div>
</body>
</html>