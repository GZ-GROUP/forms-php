<?php
// Detectar qué método se usó
$metodo = $_SERVER['REQUEST_METHOD'];

// Obtener datos según el método
if ($metodo === 'GET') {
    $nombre = htmlspecialchars($_GET['nombre'] ?? '');
    $email  = htmlspecialchars($_GET['email']  ?? '');
} else {
    $nombre = htmlspecialchars($_POST['nombre'] ?? '');
    $email  = htmlspecialchars($_POST['email']  ?? '');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultado</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center">
    <div class="card bg-base-100 shadow-xl p-8 max-w-md w-full">
        <h2 class="text-2xl font-bold mb-4">
            Datos recibidos por 
            <span class="<?= $metodo === 'GET' ? 'text-primary' : 'text-secondary' ?>">
                <?= $metodo ?>
            </span>
        </h2>

        <div class="flex flex-col gap-2">
            <div class="badge badge-outline p-4 text-base">👤 Nombre: <strong><?= $nombre ?></strong></div>
            <div class="badge badge-outline p-4 text-base">📧 Email: <strong><?= $email ?></strong></div>
        </div>

        <?php if ($metodo === 'GET'): ?>
        <div class="alert alert-warning mt-4 text-sm">
            ⚠️ Observa la URL: los datos son visibles como parámetros.
        </div>
        <?php else: ?>
        <div class="alert alert-success mt-4 text-sm">
            ✅ Con POST los datos NO aparecen en la URL.
        </div>
        <?php endif; ?>

        <a href="index.php" class="btn btn-neutral mt-6 w-full">← Volver</a>
    </div>
</body>
</html>