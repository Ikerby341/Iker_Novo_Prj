<?php
    // Incloure el controlador al principi per permetre redireccions amb headers
    include_once __DIR__ . '/../Controller/controlador.php';
    // Validar i possiblement redirigir si la pagina sol.licitada no existeix
    validar_pagina_solicitada();
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projecte Iker Novo</title>
    <link rel="stylesheet" href="/practiques/backend/Iker_Novo_Prj/resources/styles/style.css">
</head>
<body>
    <header>
        <div class="header-inner">
            <a href="index.php" class="menu">🏠 Home</a>
            <div class="header-right">
                <?php if (is_logged_in()): ?>
                    <div class="signin" id="signin">
                        <button id="signinBtn" class="signin-btn" aria-haspopup="true" aria-expanded="false">👤 <?php echo htmlspecialchars($_SESSION['username']); ?></button>
                        <div id="signinDropdown" class="signin-dropdown" aria-hidden="true">
                                <a href="./app/View/create.php">Crear articles</a>
                                <a href="./app/Controller/logout.php">Tancar sessió</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="signin" id="signin">
                        <button id="signinBtn" class="signin-btn" aria-haspopup="true" aria-expanded="false">🔐 Sign-in</button>
                        <div id="signinDropdown" class="signin-dropdown" aria-hidden="true">
                            <a href="./app/View/login.php">Iniciar sessió</a>
                                <a href="./app/View/register.php">Registrar-se</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <h1>Cotxes</h1>
    <div class="divPrincipal">
        <?php
            // Mostrar mensaje flash si existe
            if (isset($_SESSION['flash']) && $_SESSION['flash'] !== '') {
                echo '<div class="flash-message" style="padding:10px;margin-bottom:10px;background:#f8f9fa;border:1px solid #ddd;border-radius:4px; color: pink;">' . htmlspecialchars($_SESSION['flash']) . '</div>';
                unset($_SESSION['flash']);
            }

            // Mostrar input per a articles per pàgina (entrada lliure)
            $currentPerPage = isset($_GET['per_page']) && is_numeric($_GET['per_page']) ? (int)$_GET['per_page'] : 3;
        ?>

            <form method="get" id="perPageForm" style="margin-bottom:16px; text-align:right;">
                <label for="per_page">Articles per pàgina: </label>
                <input type="number" id="per_page" name="per_page" value="<?php echo $currentPerPage; ?>" min="1" max="100" style="width:40px; text-align:center;" />
                <input type="hidden" name="page" value="1">
                <button type="submit">Aplicar</button>
            </form>

        <?php
            echo mostrar_articles();  
        ?>
    </div>
    <div class="divPrincipal">
        <?php
            echo mostrar_paginacio();
        ?>
    </div>
    
    <script>
        // Dropdown para Sign-in: abre/cierra y cierra al click fuera o ESC
        (function(){
            const signinBtn = document.getElementById('signinBtn');
            const signinDropdown = document.getElementById('signinDropdown');

            function closeSignin(){
                signinDropdown.classList.remove('show');
                signinBtn.setAttribute('aria-expanded', 'false');
                signinDropdown.setAttribute('aria-hidden', 'true');
            }

            function toggleSignin(e){
                e.stopPropagation();
                const shown = signinDropdown.classList.toggle('show');
                signinBtn.setAttribute('aria-expanded', String(shown));
                signinDropdown.setAttribute('aria-hidden', String(!shown));
            }

            signinBtn && signinBtn.addEventListener('click', toggleSignin);

            // Cerrar al hacer click fuera
            document.addEventListener('click', function(){
                if(signinDropdown.classList.contains('show')) closeSignin();
            });

            // Cerrar con ESC
            document.addEventListener('keydown', function(e){
                if(e.key === 'Escape') closeSignin();
            });
        })();
    </script>
</body>
</html>