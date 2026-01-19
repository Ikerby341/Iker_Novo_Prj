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
        <div class="header-container">
            <?php if (is_logged_in()): ?>
                <h1 style="color: #ffffff; margin-right: 74%;"><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>">GUARCAR</a></h1>
            <?php else: ?>
                <h1 style="color: #ffffff; margin-right: 80%;"><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>">GUARCAR</a></h1>
            <?php endif; ?>
            <div class="header-inner">
                <?php if (is_logged_in()): ?>
                    <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>app/View/create.php" class="menu">➕ Crear</a>
                <?php endif; ?>
                <div class="header-right">
                    <?php if (is_logged_in()): ?>
                        <div class="signin" id="signin">
                            <button id="signinBtn" class="signin-btn" aria-haspopup="true" aria-expanded="false">👤 <?php echo htmlspecialchars($_SESSION['username']); ?></button>
                            <div id="signinDropdown" class="signin-dropdown" aria-hidden="true">
                                <?php if (is_admin()): ?>
                                    <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>app/View/admin.php">Gestionar usuaris</a>
                                <?php endif; ?>
                                <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>app/View/editprofile.php">Editar perfil</a>
                                <a href="?logout=1">Tancar sessió</a>
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
            $currentPerPage = isset($_GET['per_page']) && is_numeric($_GET['per_page']) ? (int)$_GET['per_page'] : 8;
        ?>
            
            <?php
                $per = isset($_GET['per_page']) ? (int)$_GET['per_page'] : $currentPerPage;
                $base = 'index.php?per_page=' . urlencode($per) . '&page=1';
                $curSort = isset($_GET['sort']) ? $_GET['sort'] : '';
                $curDir = isset($_GET['dir']) ? strtoupper($_GET['dir']) : '';
                $curSearch = '';
            ?>

            <div class="search-bar-container">
                <input type="text" id="searchInput" class="search-bar" placeholder="Buscar marca o model..." value="<?php echo htmlspecialchars($curSearch); ?>" />
                <input type="hidden" id="currentSort" value="<?php echo htmlspecialchars($curSort); ?>">
                <input type="hidden" id="currentDir" value="<?php echo htmlspecialchars($curDir); ?>">
                <input type="hidden" id="currentPerPage" value="<?php echo htmlspecialchars($per); ?>">
            </div>

            <?php
                // Precarreguem totes les dades al client per fer cerca local (només del propietari si està logat)
                $allRows = listar_tots_articles(null);
                $jsRows = [];
                foreach ($allRows as $r) {
                    $jsRows[] = [
                        'ID' => isset($r['ID']) ? (int)$r['ID'] : 0,
                        'marca' => isset($r['marca']) ? $r['marca'] : '',
                        'model' => isset($r['model']) ? $r['model'] : '',
                        'ruta_img' => (defined('BASE_URL') ? BASE_URL : '/') . (isset($r['ruta_img']) ? $r['ruta_img'] : '')
                    ];
                }
            ?>
            <script>
                window.__ALL_ARTICLES__ = <?php echo json_encode($jsRows, JSON_UNESCAPED_UNICODE); ?>;
                window.__IS_LOGGED_IN__ = <?php echo is_logged_in() ? 'true' : 'false'; ?>;
            </script>

            <div class="controls-row">
                <div class="controls-item">
                    <label for="orderby" class="controls-label">Ordenar per:</label>
                    <select id="orderby" name="orderby" class="controls-select">
                        <option value="?sort=ID&dir=ASC"<?php echo ($curSort==='' ? ' selected' : ''); ?>>Per defecte</option>
                        <option value="?sort=marca&dir=ASC"<?php echo ($curSort==='marca' && $curDir==='ASC' ? ' selected' : ''); ?>>Marca (Asc)</option>
                        <option value="?sort=marca&dir=DESC"<?php echo ($curSort==='marca' && $curDir==='DESC' ? ' selected' : ''); ?>>Marca (Desc)</option>
                        <option value="?sort=model&dir=ASC"<?php echo ($curSort==='model' && $curDir==='ASC' ? ' selected' : ''); ?>>Model (Asc)</option>
                        <option value="?sort=model&dir=DESC"<?php echo ($curSort==='model' && $curDir==='DESC' ? ' selected' : ''); ?>>Model (Desc)</option>
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
            // Contenidor renderitzat i actualitzat al client (filtrat en JS)
            echo '<div id="articlesContainer">' . mostrar_articles() . '</div>';
        ?>
    </div>

    <!-- Paginació: es mostra abans del footer i no es mou amb el contingut -->
    <div class="fixed-pagination">
        <?php echo mostrar_paginacio(); ?>
    </div>
    </div> <!-- .site-content -->
    
    <script>
        // Cerca completament al client: filtrem sobre window.__ALL_ARTICLES__
        (function(){
            const all = window.__ALL_ARTICLES__ || [];
            const isLogged = window.__IS_LOGGED_IN__ === true || window.__IS_LOGGED_IN__ === 'true';
            const input = document.getElementById('searchInput');
            const orderby = document.getElementById('orderby');
            const perPage = parseInt(document.getElementById('currentPerPage').value, 10) || 8;
            const articlesContainer = document.getElementById('articlesContainer');
            const paginationContainer = document.querySelector('.fixed-pagination');
            let currentPage = 1;
            let debounceTimer;
            let currentSort = 'ID';
            let currentDir = 'ASC';

            function renderPage(items, page) {
                const filtered = filterArticles(input.value || '');
                const sorted = sortArticles(filtered, currentSort, currentDir);
                const start = (page - 1) * perPage;
                const pageItems = sorted.slice(start, start + perPage);
                let html = '<div class="articles-grid">';
                pageItems.forEach(fila => {
                    const marca = escapeHtml(fila.marca || '');
                    const model = escapeHtml(fila.model || '');
                    const ruta_img = escapeHtml(fila.ruta_img || '');
                    const id = fila.ID || 0;
                    html += '<div class="article-card">';
                    html += '<div class="article-image">';
                    html += '<img src="' + ruta_img + '" alt="' + marca + ' ' + model + '" />';
                    html += '</div>';
                    html += '<div class="article-content">';
                    html += '<h3>' + marca + '</h3>';
                    html += '<p>' + model + '</p>';
                    html += '</div>';
                    if (isLogged) {
                        html += '<div class="article-actions">';
                        html += '<form method="post" action="app/View/update.php">';
                        html += '<input type="hidden" name="id" value="' + id + '">';
                        html += '<button type="submit" class="edit-btn" title="Editar">✏️</button>';
                        html += '</form>';
                        html += '<form method="post" action="app/View/delete.php">';
                        html += '<input type="hidden" name="id" value="' + id + '">';
                        html += '<button type="submit" class="delete-btn" title="Esborrar" onclick="return confirm(\'Est\'as segur que vols eliminar aquest article?\')">🗑️</button>';
                        html += '</form>';
                        html += '</div>';
                    }
                    html += '</div>';
                });
                html += '</div>';
                if (articlesContainer) articlesContainer.innerHTML = html;
                renderPagination(items.length, page);
            }

            function renderPagination(totalItems, page) {
                const filtered = filterArticles(input.value || '');
                const sorted = sortArticles(filtered, currentSort, currentDir);
                const totalPages = Math.max(1, Math.ceil(sorted.length / perPage));
                currentPage = Math.max(1, Math.min(page, totalPages));
                let html = '';
                if (totalPages <= 1) {
                    paginationContainer.innerHTML = '';
                    return;
                }
                html += '<div class="paginacio">';
                if (currentPage > 1) {
                    html += '<a class="btn prev" data-page="' + (currentPage - 1) + '"><button type="button">◀</button></a> ';
                }
                for (let i = 1; i <= totalPages; i++) {
                    if (i === currentPage) html += '<span class="page-number active">' + i + '</span> ';
                    else html += '<a class="page-number" data-page="' + i + '">' + i + '</a> ';
                }
                if (currentPage < totalPages) {
                    html += '<a class="btn next" data-page="' + (currentPage + 1) + '"><button type="button">▶</button></a>';
                }
                html += '</div>';
                paginationContainer.innerHTML = html;
                // bind
                paginationContainer.querySelectorAll('[data-page]').forEach(el => {
                    el.addEventListener('click', function(e){
                        const p = parseInt(this.getAttribute('data-page'), 10) || 1;
                        renderPage(all, p);
                    });
                });
            }

            function filterArticles(term) {
                term = (term || '').trim().toLowerCase();
                if (term === '') return all.slice();
                return all.filter(a => {
                    return String(a.marca || '').toLowerCase().includes(term) || String(a.model || '').toLowerCase().includes(term);
                });
            }

            function sortArticles(items, field, direction) {
                const sorted = items.slice();
                const dir = direction === 'DESC' ? -1 : 1;
                sorted.sort((a, b) => {
                    let aVal = a[field] || '';
                    let bVal = b[field] || '';
                    // Si son números, comparar como números
                    if (typeof aVal === 'number' && typeof bVal === 'number') {
                        return (aVal - bVal) * dir;
                    }
                    // Si no, comparar como strings
                    aVal = String(aVal).toLowerCase();
                    bVal = String(bVal).toLowerCase();
                    return aVal.localeCompare(bVal) * dir;
                });
                return sorted;
            }

            function escapeHtml(str) {
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            // handlers
            input.addEventListener('input', function(){
                clearTimeout(debounceTimer);
                const val = this.value;
                debounceTimer = setTimeout(function(){
                    const filtered = filterArticles(val);
                    renderPage(filtered, 1);
                    input.focus();
                }, 250);
            });

            input.addEventListener('change', function(){
                // 'change' fires when input loses focus or Enter pressed — keep for fallback
                renderPage(all, 1);
            });

            input.addEventListener('keypress', function(e){
                if (e.key === 'Enter') {
                    e.preventDefault();
                    renderPage(all, 1);
                }
            });

            // Event listener para el selector de ordenamiento
            if (orderby) {
                orderby.addEventListener('change', function(){
                    const val = this.value;
                    // Parse the URL to extract sort and dir
                    const urlParams = new URLSearchParams(val.split('?')[1] || '');
                    currentSort = urlParams.get('sort') || 'ID';
                    currentDir = urlParams.get('dir') || 'ASC';
                    renderPage(all, 1);
                });
            }

            // inicialitza amb tots
            renderPage(all, 1);
        })();
    </script>
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
            <div class="footer-small">Gràcies per visitar · <script>document.write(new Date().getFullYear());</script></div>
        </div>
    </footer>
</body>
</html>