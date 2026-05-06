<?php
// ── LOGOUT – destruye la sesión de forma segura ───────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Vaciar datos de sesión
$_SESSION = [];

// 2. Destruir cookie de sesión en el navegador
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// 3. Destruir la sesión en el servidor
session_destroy();

// 4. Redirigir al login
header('Location: login.php');
exit;