<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Porra del Mundial 2026</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-hero">
            <div class="login-ball">&#9917;</div>
            <h1 class="login-title">LA PORRA<br><span>DEL MUNDIAL</span></h1>
            <p class="login-subtitle">2026 &middot; USA / M&Eacute;XICO / CANAD&Aacute;</p>
            <p class="login-pot">5&euro; por persona &middot; El bote para el que m&aacute;s sepa</p>
        </div>

        <div class="login-form-wrapper">
            <div class="login-tabs">
                <button class="login-tab active" data-tab="login">Entrar</button>
                <button class="login-tab" data-tab="register">Registrarse</button>
            </div>

            <form id="loginForm" class="login-form">
                <div class="form-group">
                    <label for="loginName">Tu nombre</label>
                    <input type="text" id="loginName" placeholder="Ej: Luis" autocomplete="off" required>
                </div>
                <div class="form-group">
                    <label for="loginPin">PIN (4 d&iacute;gitos)</label>
                    <input type="password" id="loginPin" placeholder="&middot;&middot;&middot;&middot;" maxlength="4" inputmode="numeric" pattern="[0-9]{4}" required>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Entrar</button>
                <div class="form-error" id="loginError"></div>
            </form>

            <form id="registerForm" class="login-form" style="display:none">
                <div class="form-group">
                    <label for="regName">Elige un nombre</label>
                    <input type="text" id="regName" placeholder="Ej: Luis" autocomplete="off" required>
                </div>
                <div class="form-group">
                    <label for="regPin">Elige un PIN (4 d&iacute;gitos)</label>
                    <input type="password" id="regPin" placeholder="&middot;&middot;&middot;&middot;" maxlength="4" inputmode="numeric" pattern="[0-9]{4}" required>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Crear cuenta</button>
                <div class="form-error" id="registerError"></div>
                <p class="form-hint">El primero en registrarse ser&aacute; el admin</p>
            </form>
        </div>
    </div>

    <script>
    document.querySelectorAll('.login-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.login-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const target = tab.dataset.tab;
            document.getElementById('loginForm').style.display = target === 'login' ? 'block' : 'none';
            document.getElementById('registerForm').style.display = target === 'register' ? 'block' : 'none';
            document.querySelectorAll('.form-error').forEach(e => e.textContent = '');
        });
    });

    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const errEl = document.getElementById('loginError');
        errEl.textContent = '';
        try {
            const res = await fetch('<?= BASE_URL ?>/api/auth.php?action=login', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    name: document.getElementById('loginName').value,
                    pin: document.getElementById('loginPin').value
                })
            });
            const data = await res.json();
            if (data.success) {
                window.location.href = '<?= BASE_URL ?>/pages/dashboard.php';
            } else {
                errEl.textContent = data.error;
            }
        } catch(err) {
            errEl.textContent = 'Error de conexi\u00f3n';
        }
    });

    document.getElementById('registerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const errEl = document.getElementById('registerError');
        errEl.textContent = '';
        try {
            const res = await fetch('<?= BASE_URL ?>/api/auth.php?action=register', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    name: document.getElementById('regName').value,
                    pin: document.getElementById('regPin').value
                })
            });
            const data = await res.json();
            if (data.success) {
                window.location.href = '<?= BASE_URL ?>/pages/dashboard.php';
            } else {
                errEl.textContent = data.error;
            }
        } catch(err) {
            errEl.textContent = 'Error de conexi\u00f3n';
        }
    });
    </script>
</body>
</html>
