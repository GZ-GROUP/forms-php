<?php
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/db.php';

csrf_session_start();

$errores   = [];
$csrfToken = csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_validate()) {
        $errores['csrf'] = 'Token de seguridad inválido. Recarga la página.';
    } else {

        $nombre = trim($_POST['nombre'] ?? '');
        if ($nombre === '') {
            $errores['nombre'] = 'El nombre es obligatorio.';
        } elseif (!filter_var($nombre, FILTER_VALIDATE_REGEXP,
                  ['options' => ['regexp' => '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,60}$/']])) {
            $errores['nombre'] = 'Solo letras y espacios (2–60 caracteres).';
        }

        $email = trim($_POST['email'] ?? '');
        if ($email === '') {
            $errores['email'] = 'El correo electrónico es obligatorio.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'Formato de correo inválido.';
        } else {
            try {
                $stmt = get_pdo()->prepare('SELECT id FROM users WHERE email = ?');
                $stmt->execute([$email]);
                if ($stmt->fetch()) $errores['email'] = 'Este correo ya está registrado.';
            } catch (PDOException $e) {
                $errores['db'] = 'Error interno. Intenta más tarde.';
            }
        }

        $password  = $_POST['password']  ?? '';
        $password2 = $_POST['password2'] ?? '';
        if ($password === '')             $errores['password'] = 'La contraseña es obligatoria.';
        elseif (strlen($password) < 8)   $errores['password'] = 'Mínimo 8 caracteres.';
        elseif (!preg_match('/[A-Z]/', $password)) $errores['password'] = 'Debe contener al menos una mayúscula.';
        elseif (!preg_match('/[0-9]/', $password)) $errores['password'] = 'Debe contener al menos un número.';
        if (empty($errores['password']) && $password !== $password2)
            $errores['password2'] = 'Las contraseñas no coinciden.';

        if (empty($errores)) {
            try {
                $stmt = get_pdo()->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
                $stmt->execute([$nombre, $email, password_hash($password, PASSWORD_BCRYPT)]);
                header('Location: login.php?registered=1');
                exit;
            } catch (PDOException $e) {
                error_log('DB insert: ' . $e->getMessage());
                $errores['db'] = 'No se pudo guardar el registro.';
            }
        }
    }
    $csrfToken = csrf_token();
}

function old(string $k): string { return htmlspecialchars($_POST[$k] ?? '', ENT_QUOTES, 'UTF-8'); }
function hasErr(string $k, array $e): string { return isset($e[$k]) ? 'input-error' : 'input-bordered'; }
?>
<!DOCTYPE html>
<html lang="es" data-theme="customTheme">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taller 3 · Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5/themes.css" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css2?family=Hammersmith+One&family=Clear+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="hero bg-base-200 min-h-screen py-10">
  <div class="hero-content w-full max-w-lg flex-col">
    <div class="text-center mb-2">
      <div class="badge badge-primary badge-outline mb-3 text-xs tracking-widest uppercase">Taller 3 · UTP</div>
      <h1 class="text-3xl text-base-content">Crear cuenta</h1>
      <p class="text-base-content/50 text-sm mt-1">CSRF · bcrypt · PDO · filter_var · htmlspecialchars</p>
    </div>

    <div class="card bg-base-100 shadow-xl w-full">
      <div class="card-body gap-4">

        <?php if (isset($errores['csrf'])): ?>
        <div class="alert alert-warning"><span><?= htmlspecialchars($errores['csrf'], ENT_QUOTES, 'UTF-8') ?></span></div>
        <?php endif; ?>
        <?php if (isset($errores['db'])): ?>
        <div class="alert alert-error"><span><?= htmlspecialchars($errores['db'], ENT_QUOTES, 'UTF-8') ?></span></div>
        <?php endif; ?>
        <?php if (!empty($errores) && !isset($errores['csrf']) && !isset($errores['db'])): ?>
        <div class="alert alert-error">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span><?= count($errores) ?> error(es) encontrado(s).</span>
        </div>
        <?php endif; ?>

        <form id="secureForm" method="POST" action="" novalidate class="flex flex-col gap-4">
          <?= csrf_field() ?>

          <fieldset class="fieldset">
            <legend class="fieldset-legend">Nombre completo</legend>
            <label class="input w-full <?= hasErr('nombre', $errores) ?>">
              <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              <input type="text" id="nombre" name="nombre" placeholder="Ej: Juan Pérez" value="<?= old('nombre') ?>" autocomplete="name">
            </label>
            <?php if (isset($errores['nombre'])): ?><p class="fieldset-label text-error"><?= htmlspecialchars($errores['nombre'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
            <p id="err-nombre" class="fieldset-label text-error hidden"></p>
          </fieldset>

          <fieldset class="fieldset">
            <legend class="fieldset-legend">Correo electrónico</legend>
            <label class="input w-full <?= hasErr('email', $errores) ?>">
              <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              <input type="email" id="email" name="email" placeholder="usuario@ejemplo.com" value="<?= old('email') ?>" autocomplete="email">
            </label>
            <?php if (isset($errores['email'])): ?><p class="fieldset-label text-error"><?= htmlspecialchars($errores['email'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
            <p id="err-email" class="fieldset-label text-error hidden"></p>
          </fieldset>

          <fieldset class="fieldset">
            <legend class="fieldset-legend">Contraseña</legend>
            <label class="input w-full <?= hasErr('password', $errores) ?>">
              <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
              <input type="password" id="password" name="password" placeholder="Mín. 8 car., 1 mayúscula, 1 número" autocomplete="new-password" oninput="updateStrength(this.value)">
            </label>
            <div class="flex gap-1 mt-1">
              <div class="h-1 flex-1 rounded-full bg-base-300" id="s1"></div>
              <div class="h-1 flex-1 rounded-full bg-base-300" id="s2"></div>
              <div class="h-1 flex-1 rounded-full bg-base-300" id="s3"></div>
              <div class="h-1 flex-1 rounded-full bg-base-300" id="s4"></div>
            </div>
            <p class="fieldset-label" id="strength-label">Escribe tu contraseña</p>
            <?php if (isset($errores['password'])): ?><p class="fieldset-label text-error"><?= htmlspecialchars($errores['password'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
            <p id="err-password" class="fieldset-label text-error hidden"></p>
          </fieldset>

          <fieldset class="fieldset">
            <legend class="fieldset-legend">Confirmar contraseña</legend>
            <label class="input w-full <?= hasErr('password2', $errores) ?>">
              <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
              <input type="password" id="password2" name="password2" placeholder="Repite la contraseña" autocomplete="new-password">
            </label>
            <?php if (isset($errores['password2'])): ?><p class="fieldset-label text-error"><?= htmlspecialchars($errores['password2'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
            <p id="err-password2" class="fieldset-label text-error hidden"></p>
          </fieldset>

          <button type="submit" class="btn btn-primary w-full mt-2">
            Crear cuenta
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
          </button>

          <p class="text-center text-sm text-base-content/50">
            ¿Ya tienes cuenta? <a href="login.php" class="link link-primary">Inicia sesión</a>
          </p>
        </form>
      </div>
    </div>
    <p class="text-center text-xs text-base-content/30 mt-2">Desarrollo de Software VII · Universidad Tecnológica de Panamá</p>
  </div>
</div>
<script>
function updateStrength(val) {
    const bars = ['s1','s2','s3','s4'].map(id => document.getElementById(id));
    const label = document.getElementById('strength-label');
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const colors = ['#f87171','#fb923c','#facc15','#34d399'];
    bars.forEach((b,i) => { b.style.background = i < score ? colors[score-1] : ''; b.className='h-1 flex-1 rounded-full bg-base-300'; });
    label.textContent = val.length === 0 ? 'Escribe tu contraseña' : (['Muy débil','Débil','Aceptable','¡Fuerte!'][score-1] ?? 'Muy débil');
    label.style.color = val.length === 0 ? '' : colors[score-1];
}
(function(){
    const form = document.getElementById('secureForm');
    const campos = [
        {id:'nombre',errId:'err-nombre',msg:'El nombre no puede estar vacío.'},
        {id:'email',errId:'err-email',msg:'El correo no puede estar vacío.'},
        {id:'password',errId:'err-password',msg:'La contraseña no puede estar vacía.'},
        {id:'password2',errId:'err-password2',msg:'Confirma tu contraseña.'},
    ];
    const show=(id,msg)=>{const el=document.getElementById(id);if(el){el.textContent=msg;el.classList.remove('hidden');}};
    const hide=(id)=>{const el=document.getElementById(id);if(el){el.textContent='';el.classList.add('hidden');}};
    campos.forEach(({id,errId,msg})=>{
        const input=document.getElementById(id);if(!input)return;
        input.addEventListener('blur',()=>input.value.trim()===''?show(errId,msg):hide(errId));
        input.addEventListener('input',()=>input.value.trim()!==''&&hide(errId));
    });
    form.addEventListener('submit',e=>{
        let err=false;
        campos.forEach(({id,errId,msg})=>{const input=document.getElementById(id);if(input&&input.value.trim()===''){show(errId,msg);err=true;}else if(input)hide(errId);});
        const p1=document.getElementById('password'),p2=document.getElementById('password2');
        if(p1&&p2&&p1.value&&p2.value&&p1.value!==p2.value){show('err-password2','Las contraseñas no coinciden.');err=true;}
        const em=document.getElementById('email');
        if(em&&em.value.trim()&&!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em.value.trim())){show('err-email','Formato de correo inválido.');err=true;}
        if(err)e.preventDefault();
    });
})();
</script>
</body>
</html>