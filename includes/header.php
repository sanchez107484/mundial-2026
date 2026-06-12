<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Porra de Zoputos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <script>window.BASE_URL = '<?= BASE_URL ?>';</script>
</head>
<body>
<?php if ($currentUser): ?>
<nav class="main-nav">
    <div class="nav-brand">
        <img src="<?= BASE_URL ?>/assets/img/icono.png" alt="Logo" class="nav-logo-img">
        <span class="nav-title">LA PORRA DE ZOPUTOS</span>
    </div>
    <button class="nav-toggle" id="navToggle" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>
</nav>
<div class="nav-menu" id="navMenu">
    <a href="<?= BASE_URL ?>/pages/dashboard.php" class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">Inicio</a>
    <a href="<?= BASE_URL ?>/pages/groups.php" class="nav-link <?= $currentPage === 'groups' ? 'active' : '' ?>">Grupos</a>
    <a href="<?= BASE_URL ?>/pages/bracket.php" class="nav-link <?= $currentPage === 'bracket' ? 'active' : '' ?>">Cruces</a>
    <a href="<?= BASE_URL ?>/pages/predictions.php" class="nav-link <?= $currentPage === 'predictions' ? 'active' : '' ?>">Mi Porra</a>
    <a href="<?= BASE_URL ?>/pages/ranking.php" class="nav-link <?= $currentPage === 'ranking' ? 'active' : '' ?>">Ranking</a>
    <?php if ($currentUser['is_admin']): ?>
    <a href="<?= BASE_URL ?>/pages/admin.php" class="nav-link nav-admin <?= $currentPage === 'admin' ? 'active' : '' ?>">Admin</a>
    <?php endif; ?>
    <a href="#" class="nav-link nav-logout" id="logoutBtn">Salir</a>
</div>
<div class="nav-user-bar">
    <span class="user-greeting"><?= htmlspecialchars($currentUser['name']) ?></span>
    <?php if ($currentUser['is_admin']): ?>
    <span class="admin-badge">ADMIN</span>
    <?php endif; ?>
</div>
<?php endif; ?>
