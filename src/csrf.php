<?php
// ── CSRF TOKEN HELPERS ────────────────────────────────────────────────────────

/**
 * Inicia la sesión si aún no está activa.
 */
function csrf_session_start(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Genera (o reutiliza) el token CSRF de la sesión actual.
 */
function csrf_token(): string {
    csrf_session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Devuelve el campo hidden listo para insertar en cualquier formulario.
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Valida el token CSRF enviado por POST.
 * Regenera el token tras un envío válido (doble-submit protection).
 */
function csrf_validate(): bool {
    csrf_session_start();
    $submitted = $_POST['csrf_token'] ?? '';
    $stored    = $_SESSION['csrf_token'] ?? '';

    if (!$stored || !hash_equals($stored, $submitted)) {
        return false;
    }
    // Regenerar token para el siguiente envío
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return true;
}