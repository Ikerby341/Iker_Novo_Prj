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
            <h1 style="color: #ffffff;"><a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>">GUARCAR</a></h1>
        </div>
    </header>
    <?php if (is_admin()): ?>
        <div class="site-content">
        <!-- Títol principal de la pàgina -->
        <h1 style="text-align: center;">Gestionar usuaris</h1>
        <section class="CRUD-section form-container-adapted">
                <!-- Contenidor per mostrar missatges de resposta -->    
                <?php if (!empty($missatge)): ?>
                    <div class="form-info"><span class="info-icon">ℹ️</span><span class="info-text"><?php echo htmlspecialchars($missatge); ?></span></div>
                <?php endif; ?>
                <div class="button-row">
                    <!-- Botó per tornar a la pàgina principal -->
                    <button class="principalBox" onclick="location.href='<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>';">← Tornar enrere</button>
                </div>       
        </section>
        </div>
    <?php else: ?>
        <div class="site-content">
            <h2 style="text-align: center; color: red;">Accés denegat. No tens permisos d'administrador.</h2>
        </div>
    <?php endif; ?>
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-text">Pàgina feta per Iker Novo Oliva</div>
            <div class="footer-small">Gràcies per visitar · <script>document.write(new Date().getFullYear());</script></div>
        </div>
    </footer>
</body>
