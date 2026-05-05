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
 
                    <div class="skeleton h-80 w-100"></div>
                    
 
                    <div class="divider">
                        <span id="page-indicator" class="text-sm text-base-content/50">1 / 3</span>
                    </div>
 
                    <div class="join grid grid-cols-2">
                        <button id="btn-prev" class="join-item btn btn-outline" onclick="changePage(-1)" disabled>Anterior</button>
                        <button id="btn-next" class="join-item btn btn-outline" onclick="changePage(1)">Siguiente</button>
                    </div>
 
                </div>              
            </div>
        </div>
    </div>
 
    <script>
        const TOTAL_PAGES = 3;
        let currentPage = 1;
 
        function changePage(direction) {
            // Hide current page
            document.getElementById(`page-${currentPage}`).classList.add('hidden');
 
            // Update page index
            currentPage += direction;
 
            // Show new page
            document.getElementById(`page-${currentPage}`).classList.remove('hidden');
 
            // Update indicator
            document.getElementById('page-indicator').textContent = `${currentPage} / ${TOTAL_PAGES}`;
 
            // Update button states
            document.getElementById('btn-prev').disabled = currentPage === 1;
            document.getElementById('btn-next').disabled = currentPage === TOTAL_PAGES;
        }
    </script>
</body>

</html>
