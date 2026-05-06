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
 
                    <div class="w-full">
    <!-- Tabs para alternar entre GET y POST -->
    <div role="tablist" class="tabs tabs-lifted tabs-lg w-full">

        <!-- TAB GET -->
        <input type="radio" name="form_tabs" role="tab" class="tab font-bold text-primary" aria-label="GET" checked />
        <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
            <h2 class="text-xl font-bold mb-4 text-primary">Formulario GET</h2>
            <form action="procesar.php" method="GET" class="flex flex-col gap-3">
                <label class="input w-full">
                    <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
                    <input type="text" name="nombre" placeholder="Tu nombre" required />
                </label>
                <label class="input w-full">
                    <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg>
                    <input type="email" name="email" placeholder="Tu email" required />
                </label>
                <button type="submit" class="btn btn-primary w-full">Enviar con GET</button>
            </form>
            <div class="mt-3 text-xs text-base-content/50 italic">
                ⚠️ Los datos aparecerán visibles en la URL
            </div>
        </div>

        <!-- TAB POST -->
        <input type="radio" name="form_tabs" role="tab" class="tab font-bold text-secondary" aria-label="POST" />
        <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
            <h2 class="text-xl font-bold mb-4 text-secondary">Formulario POST</h2>
            <form action="procesar.php" method="POST" class="flex flex-col gap-3">
                <label class="input w-full">
                    <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
                    <input type="text" name="nombre" placeholder="Tu nombre" required />
                </label>
                <label class="input w-full">
                    <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg>
                    <input type="email" name="email" placeholder="Tu email" required />
                </label>
                <button type="submit" class="btn btn-secondary w-full">Enviar con POST</button>
            </form>
            <div class="mt-3 text-xs text-base-content/50 italic">
                ✅ Los datos van en el cuerpo de la petición, no en la URL
            </div>
        </div>

    </div>
</div>
 
                    <div class="divider">
                        <span class="text-sm text-base-content/50"></span>
                    </div>
 
                    
 
                </div>              
            </div>
        </div>
    </div>
 
    
</body>

</html>
