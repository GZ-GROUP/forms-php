<?php
// ══════════════════════════════════════════════════════════════════════════════
//  TALLER 3 – Formulario Seguro Completo
//  Capas de seguridad:
//    1. Token CSRF  (session-based, hash_equals)
//    2. Validación backend con filter_var + regex
//    3. Sanitización con htmlspecialchars (anti-XSS)
//    4. Hash bcrypt para contraseñas
//    5. PDO + prepared statements (anti-SQL-injection)
//    6. Información del servidor con $_SERVER
//    7. Validación frontend en JavaScript (anti-blank submit)
// ══════════════════════════════════════════════════════════════════════════════

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/db.php';

// Inicia sesión una sola vez (csrf.php la gestiona internamente)
csrf_session_start();

// ── ESTADO INICIAL ────────────────────────────────────────────────────────────
$errores  = [];
$exito    = false;
$serverInfo = [];
$registroId = null;

// Genera token ANTES de procesar POST (para la vista)
$csrfToken = csrf_token();

// ── PROCESAMIENTO POST ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── 0. VALIDAR CSRF ──────────────────────────────────────────────────────
    if (!csrf_validate()) {
        $errores['csrf'] = 'Token de seguridad inválido o expirado. Recarga la página.';
    } else {

        // ── 1. NOMBRE ────────────────────────────────────────────────────────
        $nombre = trim($_POST['nombre'] ?? '');
        if ($nombre === '') {
            $errores['nombre'] = 'El nombre es obligatorio.';
        } elseif (!filter_var($nombre, FILTER_VALIDATE_REGEXP,
                  ['options' => ['regexp' => '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,60}$/']])) {
            $errores['nombre'] = 'Solo letras y espacios (2–60 caracteres).';
        }

        // ── 2. EMAIL ─────────────────────────────────────────────────────────
        $email = trim($_POST['email'] ?? '');
        if ($email === '') {
            $errores['email'] = 'El correo electrónico es obligatorio.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'Formato de correo inválido.';
        } else {
            // Comprobar duplicado antes de insertar
            try {
                $stmt = get_pdo()->prepare('SELECT id FROM users WHERE email = ?');
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $errores['email'] = 'Este correo ya está registrado.';
                }
            } catch (PDOException $e) {
                error_log('DB check email: ' . $e->getMessage());
                $errores['db'] = 'Error interno. Intenta más tarde.';
            }
        }

        // ── 3. CONTRASEÑA ────────────────────────────────────────────────────
        $password  = $_POST['password']  ?? '';
        $password2 = $_POST['password2'] ?? '';

        if ($password === '') {
            $errores['password'] = 'La contraseña es obligatoria.';
        } elseif (strlen($password) < 8) {
            $errores['password'] = 'Mínimo 8 caracteres.';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errores['password'] = 'Debe contener al menos una mayúscula.';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errores['password'] = 'Debe contener al menos un número.';
        }

        if (empty($errores['password']) && $password !== $password2) {
            $errores['password2'] = 'Las contraseñas no coinciden.';
        }

        // ── 4. INSERTAR EN BASE DE DATOS ─────────────────────────────────────
        if (empty($errores)) {
            $hashPassword = password_hash($password, PASSWORD_BCRYPT);

            try {
                $pdo  = get_pdo();
                $stmt = $pdo->prepare(
                    'INSERT INTO users (name, email, password) VALUES (?, ?, ?) RETURNING id, created_at'
                );
                $stmt->execute([$nombre, $email, $hashPassword]);
                $row        = $stmt->fetch();
                $registroId = $row['id'];
                $createdAt  = $row['created_at'];

                // ── 5. INFO DE SERVIDOR ──────────────────────────────────────
                $serverInfo = [
                    'Método'       => htmlspecialchars($_SERVER['REQUEST_METHOD'],          ENT_QUOTES, 'UTF-8'),
                    'IP cliente'   => htmlspecialchars($_SERVER['REMOTE_ADDR']    ?? 'N/A', ENT_QUOTES, 'UTF-8'),
                    'User-Agent'   => htmlspecialchars(substr($_SERVER['HTTP_USER_AGENT'] ?? 'N/A', 0, 80), ENT_QUOTES, 'UTF-8'),
                    'Host'         => htmlspecialchars($_SERVER['HTTP_HOST']      ?? 'N/A', ENT_QUOTES, 'UTF-8'),
                    'URI'          => htmlspecialchars($_SERVER['REQUEST_URI']    ?? 'N/A', ENT_QUOTES, 'UTF-8'),
                    'Protocolo'    => htmlspecialchars($_SERVER['SERVER_PROTOCOL']?? 'N/A', ENT_QUOTES, 'UTF-8'),
                    'HTTPS'        => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'Sí' : 'No',
                    'Creado (UTC)' => htmlspecialchars($createdAt,                          ENT_QUOTES, 'UTF-8'),
                ];

                $exito = true;

            } catch (PDOException $e) {
                error_log('DB insert error: ' . $e->getMessage());
                $errores['db'] = 'No se pudo guardar el registro. Intenta más tarde.';
            }
        }
    } // end csrf válido

    // Regenerar token para la siguiente vista
    $csrfToken = csrf_token();
}

// ── HELPERS ───────────────────────────────────────────────────────────────────
function old(string $key): string {
    return htmlspecialchars($_POST[$key] ?? '', ENT_QUOTES, 'UTF-8');
}
function hasErr(string $key, array $e): string {
    return isset($e[$key]) ? 'input-error' : 'input-bordered';
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="customTheme">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taller 3 · Formulario Seguro</title>
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

        <!-- ── ENCABEZADO ─────────────────────────────────────────────────── -->
        <div class="text-center mb-2">
            <div class="badge badge-primary badge-outline mb-3 text-xs tracking-widest uppercase">
                Taller 3 · UTP
            </div>
            <h1 class="text-3xl text-base-content">Formulario Seguro Completo</h1>
            <p class="text-base-content/50 text-sm mt-1">
                CSRF · XSS · SQL-Injection · bcrypt · DB · $_SERVER
            </p>
        </div>

        <!-- ── CAPAS DE SEGURIDAD (badge strip) ───────────────────────────── -->
        <div class="flex flex-wrap justify-center gap-2 mb-2">
            <?php foreach (['🛡️ CSRF Token','🔒 bcrypt','🧹 htmlspecialchars','💉 PDO Prepared','✅ filter_var','🖥️ $_SERVER'] as $badge): ?>
                <span class="badge badge-ghost badge-sm font-mono"><?= $badge ?></span>
            <?php endforeach; ?>
        </div>

        <!-- ── CARD ───────────────────────────────────────────────────────── -->
        <div class="card bg-base-100 shadow-xl w-full">
            <div class="card-body gap-4">

                <!-- ── CSRF ERROR ──────────────────────────────────────────── -->
                <?php if (isset($errores['csrf'])): ?>
                <div class="alert alert-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <span><?= htmlspecialchars($errores['csrf'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <?php endif; ?>

                <!-- ── DB ERROR ────────────────────────────────────────────── -->
                <?php if (isset($errores['db'])): ?>
                <div class="alert alert-error">
                    <span><?= htmlspecialchars($errores['db'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <?php endif; ?>

                <!-- ── ÉXITO ───────────────────────────────────────────────── -->
                <?php if ($exito): ?>
                <div class="alert alert-success">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>¡Usuario #<?= $registroId ?> registrado y guardado en PostgreSQL!</span>
                </div>

                <!-- Tabla de resultados -->
                <div class="rounded-box border border-base-300 overflow-hidden text-sm">
                    <table class="table table-sm">
                        <thead class="bg-base-200">
                            <tr>
                                <th colspan="2" class="text-base-content/60 text-xs tracking-wider uppercase">
                                    📋 Datos procesados &amp; $_SERVER
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-base-content/50 w-36">ID en BD</td>
                                <td><span class="badge badge-primary badge-sm">#<?= $registroId ?></span></td>
                            </tr>
                            <tr>
                                <td class="text-base-content/50">Nombre</td>
                                <td class="font-medium"><?= htmlspecialchars(old('nombre') ?: ($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                            <tr>
                                <td class="text-base-content/50">Email</td>
                                <td class="font-medium"><?= htmlspecialchars(old('email') ?: ($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                            <tr>
                                <td class="text-base-content/50">Hash bcrypt</td>
                                <td class="font-mono text-xs break-all text-success">
                                    [guardado en DB — nunca en pantalla completa]
                                </td>
                            </tr>
                            <tr>
                                <td class="text-base-content/50">CSRF</td>
                                <td><span class="badge badge-success badge-sm">✓ Válido</span></td>
                            </tr>
                            <?php foreach ($serverInfo as $key => $val): ?>
                            <tr>
                                <td class="text-base-content/50"><?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="font-mono text-xs"><?= $val ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="divider text-xs text-base-content/30">Nuevo registro</div>
                <?php endif; ?>

                <!-- ── ERRORES GENERALES ───────────────────────────────────── -->
                <?php if (!empty($errores) && !isset($errores['csrf']) && !isset($errores['db'])): ?>
                <div class="alert alert-error">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span><?= count($errores) ?> error(es) encontrado(s). Revisa los campos.</span>
                </div>
                <?php endif; ?>

                <!-- ══════════════════════════════════════════════════════════
                     FORMULARIO  (method="POST" + token CSRF oculto)
                ══════════════════════════════════════════════════════════ -->
                <form id="secureForm" method="POST" action="" novalidate class="flex flex-col gap-4">

                    <!-- ▶ Campo oculto CSRF -->
                    <?= csrf_field() ?>

                    <!-- NOMBRE -->
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Nombre completo</legend>
                        <label class="input w-full <?= hasErr('nombre', $errores) ?>">
                            <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <input
                                type="text"
                                id="nombre"
                                name="nombre"
                                placeholder="Ej: Juan Pérez"
                                value="<?= old('nombre') ?>"
                                autocomplete="name"
                                data-required="true"
                            >
                        </label>
                        <?php if (isset($errores['nombre'])): ?>
                            <p class="fieldset-label text-error"><?= htmlspecialchars($errores['nombre'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                        <p id="err-nombre" class="fieldset-label text-error hidden"></p>
                    </fieldset>

                    <!-- EMAIL -->
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Correo electrónico</legend>
                        <label class="input w-full <?= hasErr('email', $errores) ?>">
                            <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="usuario@ejemplo.com"
                                value="<?= old('email') ?>"
                                autocomplete="email"
                                data-required="true"
                            >
                        </label>
                        <?php if (isset($errores['email'])): ?>
                            <p class="fieldset-label text-error"><?= htmlspecialchars($errores['email'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                        <p id="err-email" class="fieldset-label text-error hidden"></p>
                    </fieldset>

                    <!-- CONTRASEÑA -->
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Contraseña</legend>
                        <label class="input w-full <?= hasErr('password', $errores) ?>">
                            <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Mín. 8 caracteres, 1 mayúscula, 1 número"
                                autocomplete="new-password"
                                data-required="true"
                                oninput="updateStrength(this.value)"
                            >
                        </label>
                        <!-- Barra de fortaleza -->
                        <div class="flex gap-1 mt-1" id="strength-bars">
                            <div class="h-1 flex-1 rounded-full bg-base-300" id="s1"></div>
                            <div class="h-1 flex-1 rounded-full bg-base-300" id="s2"></div>
                            <div class="h-1 flex-1 rounded-full bg-base-300" id="s3"></div>
                            <div class="h-1 flex-1 rounded-full bg-base-300" id="s4"></div>
                        </div>
                        <p class="fieldset-label" id="strength-label">Escribe tu contraseña</p>
                        <?php if (isset($errores['password'])): ?>
                            <p class="fieldset-label text-error"><?= htmlspecialchars($errores['password'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                        <p id="err-password" class="fieldset-label text-error hidden"></p>
                    </fieldset>

                    <!-- CONFIRMAR CONTRASEÑA -->
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Confirmar contraseña</legend>
                        <label class="input w-full <?= hasErr('password2', $errores) ?>">
                            <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <input
                                type="password"
                                id="password2"
                                name="password2"
                                placeholder="Repite la contraseña"
                                autocomplete="new-password"
                                data-required="true"
                            >
                        </label>
                        <?php if (isset($errores['password2'])): ?>
                            <p class="fieldset-label text-error"><?= htmlspecialchars($errores['password2'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                        <p id="err-password2" class="fieldset-label text-error hidden"></p>
                    </fieldset>

                    <!-- SUBMIT -->
                    <button type="submit" id="submitBtn" class="btn btn-primary w-full mt-2">
                        Registrarse de forma segura
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </button>
                </form>

            </div><!-- /card-body -->
        </div><!-- /card -->

        <!-- PIE -->
        <p class="text-center text-xs text-base-content/30 mt-2">
            Desarrollo de Software VII · Universidad Tecnológica de Panamá
        </p>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════
     JAVASCRIPT – Validación frontend (reto extra)
     Previene el envío si hay campos vacíos o contraseñas que no coinciden.
     No reemplaza la validación backend; es una primera línea defensiva.
══════════════════════════════════════════════════════════════════════════ -->
<script>
// ── Indicador de fortaleza ────────────────────────────────────────────────────
function updateStrength(val) {
    const bars  = ['s1','s2','s3','s4'].map(id => document.getElementById(id));
    const label = document.getElementById('strength-label');
    let score = 0;
    if (val.length >= 8)           score++;
    if (/[A-Z]/.test(val))         score++;
    if (/[0-9]/.test(val))         score++;
    if (/[^A-Za-z0-9]/.test(val))  score++;
    const colors = ['#f87171','#fb923c','#facc15','#34d399'];
    const labels = ['Muy débil','Débil','Aceptable','¡Fuerte!'];
    bars.forEach((b, i) => {
        b.style.background = i < score ? colors[score - 1] : '';
        b.className = 'h-1 flex-1 rounded-full bg-base-300';
    });
    label.textContent = val.length === 0 ? 'Escribe tu contraseña' : (labels[score - 1] ?? 'Muy débil');
    label.style.color = val.length === 0 ? '' : colors[score - 1];
}

// ── Validación de campos vacíos (reto extra) ──────────────────────────────────
(function () {
    const form    = document.getElementById('secureForm');
    const campos  = [
        { id: 'nombre',    errId: 'err-nombre',    msg: 'El nombre no puede estar vacío.' },
        { id: 'email',     errId: 'err-email',      msg: 'El correo no puede estar vacío.' },
        { id: 'password',  errId: 'err-password',   msg: 'La contraseña no puede estar vacía.' },
        { id: 'password2', errId: 'err-password2',  msg: 'Confirma tu contraseña.' },
    ];

    function mostrarError(errId, msg) {
        const el = document.getElementById(errId);
        if (el) { el.textContent = msg; el.classList.remove('hidden'); }
    }
    function limpiarError(errId) {
        const el = document.getElementById(errId);
        if (el) { el.textContent = ''; el.classList.add('hidden'); }
    }

    // Validación en tiempo real (blur)
    campos.forEach(({ id, errId, msg }) => {
        const input = document.getElementById(id);
        if (!input) return;
        input.addEventListener('blur', () => {
            if (input.value.trim() === '') mostrarError(errId, msg);
            else limpiarError(errId);
        });
        input.addEventListener('input', () => {
            if (input.value.trim() !== '') limpiarError(errId);
        });
    });

    // Validación al enviar
    form.addEventListener('submit', function (e) {
        let hayError = false;

        campos.forEach(({ id, errId, msg }) => {
            const input = document.getElementById(id);
            if (!input) return;
            if (input.value.trim() === '') {
                mostrarError(errId, msg);
                hayError = true;
            } else {
                limpiarError(errId);
            }
        });

        // Verificar que las contraseñas coincidan en el front
        const p1 = document.getElementById('password');
        const p2 = document.getElementById('password2');
        if (p1 && p2 && p1.value && p2.value && p1.value !== p2.value) {
            mostrarError('err-password2', 'Las contraseñas no coinciden.');
            hayError = true;
        }

        // Validación de email básica
        const emailInput = document.getElementById('email');
        if (emailInput && emailInput.value.trim() !== '') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailInput.value.trim())) {
                mostrarError('err-email', 'Formato de correo inválido.');
                hayError = true;
            }
        }

        if (hayError) {
            e.preventDefault(); // Bloquear envío al servidor
        }
    });
})();
</script>
</body>
</html>