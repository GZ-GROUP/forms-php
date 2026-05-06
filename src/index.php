<?php

//  TALLER 2 – Formulario de Registro con Validación

$errores  = [];
$exito    = false;
$nombre   = $email = '';
$nombreSeguro = $emailSeguro = $hashPassword = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── 1. NOMBRE ────────────────────────────────────────────
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

    if (empty($nombre)) {
        $errores['nombre'] = 'El nombre es obligatorio.';
    } elseif (!filter_var($nombre, FILTER_VALIDATE_REGEXP,
              ['options' => ['regexp' => '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,60}$/']])) {
        $errores['nombre'] = 'Solo letras y espacios (2–60 caracteres).';
    }

    // ── 2. EMAIL ─────────────────────────────────────────────
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if (empty($email)) {
        $errores['email'] = 'El correo electrónico es obligatorio.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'Formato de correo inválido.';
    }

    // ── 3. CONTRASEÑA ────────────────────────────────────────
    $password  = isset($_POST['password'])  ? $_POST['password']  : '';
    $password2 = isset($_POST['password2']) ? $_POST['password2'] : '';

    if (empty($password)) {
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

    // ── 4. PROCESAR SI NO HAY ERRORES ────────────────────────
    if (empty($errores)) {
        // Sanitizar salida con htmlspecialchars (previene XSS)
        $nombreSeguro = htmlspecialchars($nombre,  ENT_QUOTES, 'UTF-8');
        $emailSeguro  = htmlspecialchars($email,   ENT_QUOTES, 'UTF-8');

        // Hash seguro de contraseña — nunca en texto plano
        $hashPassword = password_hash($password, PASSWORD_BCRYPT);

        $exito  = true;
        $nombre = $email = '';
    }
}

// Mantiene valor previo en inputs al haber error
function old(string $key): string {
    return htmlspecialchars($_POST[$key] ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="customTheme">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taller 2 · Registro</title>
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
    <div class="hero-content w-full max-w-md flex-col">

        <!-- ── ENCABEZADO ── -->
        <div class="text-center mb-2">
            <div class="badge badge-primary badge-outline mb-3 text-xs tracking-widest uppercase">
                Taller 2 · UTP
            </div>
            <h1 class="text-3xl text-base-content">Formulario de Registro</h1>
            <p class="text-base-content/50 text-sm mt-1">
                Validación con <code class="text-primary">filter_var</code> ·
                <code class="text-primary">htmlspecialchars</code> ·
                <code class="text-primary">password_hash</code>
            </p>
        </div>

        <!-- ── CARD ── -->
        <div class="card bg-base-100 shadow-xl w-full">
            <div class="card-body gap-4">

                <?php if ($exito): ?>
                <!-- ── ALERTA ÉXITO ── -->
                <div class="alert alert-success">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>¡Registro procesado correctamente!</span>
                </div>

                <!-- ── TABLA DE RESULTADOS ── -->
                <div class="rounded-box border border-base-300 overflow-hidden text-sm">
                    <table class="table table-sm">
                        <thead class="bg-base-200">
                            <tr>
                                <th colspan="2" class="text-base-content/60 text-xs tracking-wider uppercase">
                                    📋 Datos procesados
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-base-content/50 w-32">Nombre</td>
                                <td class="font-medium"><?= $nombreSeguro ?></td>
                            </tr>
                            <tr>
                                <td class="text-base-content/50">Email</td>
                                <td class="font-medium"><?= $emailSeguro ?></td>
                            </tr>
                            <tr>
                                <td class="text-base-content/50">Hash</td>
                                <td class="font-mono text-xs break-all text-success">
                                    <?= htmlspecialchars(substr($hashPassword, 0, 45), ENT_QUOTES, 'UTF-8') ?>…
                                </td>
                            </tr>
                            <tr>
                                <td class="text-base-content/50">Algoritmo</td>
                                <td><span class="badge badge-outline badge-sm">PASSWORD_BCRYPT</span></td>
                            </tr>
                            <tr>
                                <td class="text-base-content/50">Método</td>
                                <td><span class="badge badge-primary badge-sm"><?= htmlspecialchars($_SERVER['REQUEST_METHOD'], ENT_QUOTES, 'UTF-8') ?></span></td>
                            </tr>
                            <tr>
                                <td class="text-base-content/50">IP</td>
                                <td class="font-mono text-xs"><?= htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="divider text-xs text-base-content/30">Nuevo registro</div>
                <?php endif; ?>

                <?php if (!empty($errores)): ?>
                <!-- ── ALERTA ERROR GENERAL ── -->
                <div class="alert alert-error">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span><?= count($errores) ?> error(es) encontrado(s). Revisa los campos.</span>
                </div>
                <?php endif; ?>

                <!-- ── FORMULARIO ── -->
                <form method="POST" action="" novalidate class="flex flex-col gap-4">

                    <!-- NOMBRE -->
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Nombre completo</legend>
                        <label class="input w-full <?= isset($errores['nombre']) ? 'input-error' : 'input-bordered' ?>">
                            <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <input
                                type="text"
                                name="nombre"
                                placeholder="Ej: Juan Pérez"
                                value="<?= old('nombre') ?>"
                                autocomplete="name"
                            >
                        </label>
                        <?php if (isset($errores['nombre'])): ?>
                            <p class="fieldset-label text-error">
                                <?= htmlspecialchars($errores['nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        <?php endif; ?>
                    </fieldset>

                    <!-- EMAIL -->
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Correo electrónico</legend>
                        <label class="input w-full <?= isset($errores['email']) ? 'input-error' : 'input-bordered' ?>">
                            <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <input
                                type="email"
                                name="email"
                                placeholder="usuario@ejemplo.com"
                                value="<?= old('email') ?>"
                                autocomplete="email"
                            >
                        </label>
                        <?php if (isset($errores['email'])): ?>
                            <p class="fieldset-label text-error">
                                <?= htmlspecialchars($errores['email'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        <?php endif; ?>
                    </fieldset>

                    <!-- CONTRASEÑA -->
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Contraseña</legend>
                        <label class="input w-full <?= isset($errores['password']) ? 'input-error' : 'input-bordered' ?>">
                            <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Mín. 8 caracteres, 1 mayúscula, 1 número"
                                autocomplete="new-password"
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
                            <p class="fieldset-label text-error">
                                <?= htmlspecialchars($errores['password'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        <?php endif; ?>
                    </fieldset>

                    <!-- CONFIRMAR CONTRASEÑA -->
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Confirmar contraseña</legend>
                        <label class="input w-full <?= isset($errores['password2']) ? 'input-error' : 'input-bordered' ?>">
                            <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <input
                                type="password"
                                name="password2"
                                placeholder="Repite la contraseña"
                                autocomplete="new-password"
                            >
                        </label>
                        <?php if (isset($errores['password2'])): ?>
                            <p class="fieldset-label text-error">
                                <?= htmlspecialchars($errores['password2'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        <?php endif; ?>
                    </fieldset>

                    <!-- SUBMIT -->
                    <button type="submit" class="btn btn-primary w-full mt-2">
                        Registrarse
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </button>

                </form>

            </div><!-- /card-body -->
        </div><!-- /card -->

        <!-- PIE DE PÁGINA -->
        <p class="text-center text-xs text-base-content/30 mt-2">
            Desarrollo de Software VII · Universidad Tecnológica de Panamá
        </p>

    </div>
</div>

<script>
// ── Indicador de fortaleza de contraseña (frontend JS) ───────
function updateStrength(val) {
    const bars  = ['s1','s2','s3','s4'].map(id => document.getElementById(id));
    const label = document.getElementById('strength-label');

    let score = 0;
    if (val.length >= 8)            score++;
    if (/[A-Z]/.test(val))          score++;
    if (/[0-9]/.test(val))          score++;
    if (/[^A-Za-z0-9]/.test(val))   score++;

    const colors = ['#f87171', '#fb923c', '#facc15', '#34d399'];
    const labels = ['Muy débil', 'Débil', 'Aceptable', '¡Fuerte!'];

    bars.forEach((b, i) => {
        b.style.background = i < score ? colors[score - 1] : '';
        b.className = 'h-1 flex-1 rounded-full bg-base-300';
    });

    if (val.length === 0) {
        label.textContent = 'Escribe tu contraseña';
        label.style.color = '';
    } else {
        label.textContent = labels[score - 1] ?? 'Muy débil';
        label.style.color = colors[score - 1];
    }
}
</script>

</body>
</html>