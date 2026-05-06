<?php
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/db.php';

csrf_session_start();

if (!empty($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }

$errores   = [];
$csrfToken = csrf_token();
$registered = isset($_GET['registered']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate()) {
        $errores['csrf'] = 'Token inválido. Recarga la página.';
    } else {
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '')    $errores['email']    = 'El correo es obligatorio.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores['email'] = 'Formato inválido.';
        if ($password === '') $errores['password'] = 'La contraseña es obligatoria.';

        if (empty($errores)) {
            try {
                $stmt = get_pdo()->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if (!$user || !password_verify($password, $user['password'])) {
                    $errores['general'] = 'Credenciales incorrectas.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user_id']    = $user['id'];
                    $_SESSION['user_name']  = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['logged_at']  = date('Y-m-d H:i:s');
                    $_SESSION['user_ip']    = $_SERVER['REMOTE_ADDR'] ?? '';
                    header('Location: dashboard.php');
                    exit;
                }
            } catch (PDOException $e) {
                $errores['general'] = 'Error interno. Intenta más tarde.';
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
    <title>Iniciar Sesión · UTP</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5/themes.css" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css2?family=Hammersmith+One&family=Clear+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="hero bg-base-200 min-h-screen py-10">
  <div class="hero-content w-full max-w-md flex-col">
    <div class="text-center mb-2">
      <div class="badge badge-secondary badge-outline mb-3 text-xs tracking-widest uppercase">Login · UTP</div>
      <h1 class="text-3xl text-base-content">Iniciar Sesión</h1>
      <p class="text-base-content/50 text-sm mt-1">CSRF · password_verify · sesiones seguras</p>
    </div>

    <div class="card bg-base-100 shadow-xl w-full">
      <div class="card-body gap-4">

        <?php if ($registered): ?>
        <div class="alert alert-success">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span>¡Cuenta creada! Ya puedes iniciar sesión.</span>
        </div>
        <?php endif; ?>

        <?php if (isset($errores['csrf'])): ?>
        <div class="alert alert-warning"><span><?= htmlspecialchars($errores['csrf'], ENT_QUOTES, 'UTF-8') ?></span></div>
        <?php endif; ?>

        <?php if (isset($errores['general'])): ?>
        <div class="alert alert-error">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span><?= htmlspecialchars($errores['general'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <?php endif; ?>

        <form id="loginForm" method="POST" action="" novalidate class="flex flex-col gap-4">
          <?= csrf_field() ?>

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
              <input type="password" id="password" name="password" placeholder="Tu contraseña" autocomplete="current-password">
            </label>
            <?php if (isset($errores['password'])): ?><p class="fieldset-label text-error"><?= htmlspecialchars($errores['password'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
            <p id="err-password" class="fieldset-label text-error hidden"></p>
          </fieldset>

          <button type="submit" class="btn btn-secondary w-full mt-2">
            Entrar
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
          </button>

          <p class="text-center text-sm text-base-content/50">
            ¿No tienes cuenta? <a href="index.php" class="link link-primary">Regístrate</a>
          </p>
        </form>
      </div>
    </div>
    <p class="text-center text-xs text-base-content/30 mt-2">Desarrollo de Software VII · Universidad Tecnológica de Panamá</p>
  </div>
</div>
<script>
(function(){
    const form=document.getElementById('loginForm');
    const campos=[{id:'email',errId:'err-email',msg:'El correo no puede estar vacío.'},{id:'password',errId:'err-password',msg:'La contraseña no puede estar vacía.'}];
    const show=(id,msg)=>{const el=document.getElementById(id);if(el){el.textContent=msg;el.classList.remove('hidden');}};
    const hide=(id)=>{const el=document.getElementById(id);if(el){el.textContent='';el.classList.add('hidden');}};
    campos.forEach(({id,errId,msg})=>{
        const input=document.getElementById(id);if(!input)return;
        input.addEventListener('blur',()=>input.value.trim()===''?show(errId,msg):hide(errId));
        input.addEventListener('input',()=>input.value.trim()!==''&&hide(errId));
    });
    form.addEventListener('submit',e=>{
        let err=false;
        campos.forEach(({id,errId,msg})=>{const input=document.getElementById(id);if(input&&input.value.trim()===''){show(errId,msg);err=true;}});
        if(err)e.preventDefault();
    });
})();
</script>
</body>
</html>