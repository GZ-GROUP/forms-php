<!DOCTYPE html>

<html lang="es" data-theme="customTheme">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formularios</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5/themes.css" rel="stylesheet" type="text/css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hammersmith+One&family=Clear+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="hero bg-base-200 min-h-screen">
        <div class="hero-content text-center">
            <div class="max-w-md">
                <div class="flex w-full flex-col">
 
                    <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs border p-4">
                        <legend class="fieldset-legend">Inicar Sesión</legend>

                        <label class="label">Correo</label>
                        <input type="email" class="input" placeholder="Correo" />

                        <label class="label">Contraseña</label>
                        <input type="password" class="input" placeholder="Contraseña" />

                        <button class="btn btn-neutral mt-4">Ingresar</button>
                        <a href="register.php" class="link link-primary">
                            ¿No tienes una cuenta? Registrate
                        </a>
                    </fieldset>
 
                    <div class="divider">
                        <span class="text-sm text-base-content/50"></span>
                    </div>
 
                    
 
                </div>              
            </div>
        </div>
    </div>
 
    
</body>

</html>