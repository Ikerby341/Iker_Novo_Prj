<?php
    // Incloure el controlador al principi per permetre redireccions amb headers
    include_once __DIR__ . '/../Controller/controlador.php';
    // Validar i possiblement redirigir si la pagina sol.licitada no existeix
    validar_pagina_solicitada();
?>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projecte Iker Novo</title>
    <link rel="stylesheet" href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>resources/styles/style.css">
</head>
<body>
    <header>
        <div class="header-inner">
            <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>" class="menu">🏠 Home</a>
            <div class="header-right">
                <?php if (is_logged_in()): ?>
                    <div class="signin" id="signin">
                        <button id="signinBtn" class="signin-btn" aria-haspopup="true" aria-expanded="false">👤 <?php echo htmlspecialchars($_SESSION['username']); ?></button>
                        <div id="signinDropdown" class="signin-dropdown" aria-hidden="true">
                            <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>app/View/create.php">Crear articles</a>
                            <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>app/View/editprofile.php">Editar perfil</a>
                            <a href="<?php logout_user()?>">Tancar sessió</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="signin" id="signin">
                        <button id="signinBtn" class="signin-btn" aria-haspopup="true" aria-expanded="false">🔐 Sign-in</button>
                        <div id="signinDropdown" class="signin-dropdown" aria-hidden="true">
                            <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>app/View/login.php">Iniciar sessió</a>
                            <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>app/View/register.php">Registrar-se</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <div class="site-content">
    <h1>Cotxes</h1>
    <div class="divPrincipal">
        <?php
            // Mostrar mensaje flash si existe (usar formato informacional azul similar a .form-errors)
            if (isset($_SESSION['flash']) && $_SESSION['flash'] !== '') {
                $msg = htmlspecialchars($_SESSION['flash']);
                echo '<div class="form-info"><span class="info-icon">ℹ️</span><span class="info-text">' . $msg . '</span></div>';
                unset($_SESSION['flash']);
            }

            // Mostrar input per a articles per pàgina (entrada lliure)
            $currentPerPage = isset($_GET['per_page']) && is_numeric($_GET['per_page']) ? (int)$_GET['per_page'] : 3;
        ?>
            
            <?php
                $per = isset($_GET['per_page']) ? (int)$_GET['per_page'] : $currentPerPage;
                $base = 'index.php?per_page=' . urlencode($per) . '&page=1';
                $curSort = isset($_GET['sort']) ? $_GET['sort'] : '';
                $curDir = isset($_GET['dir']) ? strtoupper($_GET['dir']) : '';
            ?>

            <div class="controls-row">
                <div class="controls-item">
                    <label for="orderby" class="controls-label">Ordenar per:</label>
                    <select id="orderby" name="orderby" class="controls-select" onchange="if(this.value) location = this.value;">
                        <option value="<?php echo $base; ?>"<?php echo ($curSort==='' ? ' selected' : ''); ?>>Per defecte</option>
                        <option value="<?php echo $base . '&sort=marca&dir=ASC'; ?>"<?php echo ($curSort==='marca' && $curDir==='ASC' ? ' selected' : ''); ?>>Marca (Asc)</option>
                        <option value="<?php echo $base . '&sort=marca&dir=DESC'; ?>"<?php echo ($curSort==='marca' && $curDir==='DESC' ? ' selected' : ''); ?>>Marca (Desc)</option>
                        <option value="<?php echo $base . '&sort=model&dir=ASC'; ?>"<?php echo ($curSort==='model' && $curDir==='ASC' ? ' selected' : ''); ?>>Model (Asc)</option>
                        <option value="<?php echo $base . '&sort=model&dir=DESC'; ?>"<?php echo ($curSort==='model' && $curDir==='DESC' ? ' selected' : ''); ?>>Model (Desc)</option>
                    </select>
                </div>

                <form method="get" id="perPageForm" class="controls-item controls-form">
                    <label for="per_page" class="controls-label">Articles per pàgina:</label>
                    <input type="number" id="per_page" name="per_page" class="controls-input" value="<?php echo $currentPerPage; ?>" min="1" max="100" />
                    <input type="hidden" name="page" value="1">
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($curSort); ?>">
                    <input type="hidden" name="dir" value="<?php echo htmlspecialchars($curDir); ?>">
                    <button type="submit" class="controls-button">Aplicar</button>
                </form>
            </div>

        <?php
            echo mostrar_articles();  
        ?>
    </div>

    <!-- Paginació: es mostra abans del footer i no es mou amb el contingut -->
    <div class="fixed-pagination">
        <?php echo mostrar_paginacio(); ?>
    </div>
    </div> <!-- .site-content -->
    
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
    <?php
        // Si l'usuari està logat, establim un temporitzador client-side
        // perquè la pàgina es recarregui quan la sessió caduqui al servidor.
        if (is_logged_in() && isset($_SESSION['last_activity'])) {
            $remaining = SESSION_TIMEOUT_SECONDS - (time() - $_SESSION['last_activity']);
            if ($remaining < 0) $remaining = 0;
    ?>
    <script>
        (function(){
            var remaining = <?php echo (int)$remaining; ?>; // segons
            // Afegim un petit marge d'1s per assegurar-nos que el servidor ja hagi invalidat la sessió
            var ms = (remaining + 1) * 1000;
            // Programem recarrega automàtica
            setTimeout(function(){ try { location.reload(); } catch(e) { /* ignore */ } }, ms);
        })();
    </script>
    <?php } ?>
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-text">Pàgina feta per Iker Novo Oliva</div>
            <div class="footer-small">Gràcies per visitar · 2025</div>
        </div>
    </footer>
</body>
</html>