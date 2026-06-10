<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
require_once __DIR__ . '/../includes/header.php';

$ranking = getRanking();
$currentUser = getCurrentUser();
?>
<main class="page-content">
    <h1 class="page-title">Ranking</h1>

    <div class="ranking-pot-bar">
        <div class="pot-info">
            <span class="pot-label">Bote total</span>
            <span class="pot-value"><?= $ranking['pot'] ?>&euro;</span>
        </div>
        <div class="pot-split">
            <span class="split-item">&#129351; 60%</span>
            <span class="split-item">&#129352; 25%</span>
            <span class="split-item">&#129353; 15%</span>
        </div>
    </div>

    <div class="ranking-list">
        <?php foreach ($ranking['ranking'] as $r): ?>
        <div class="ranking-card <?= $r['id'] === $currentUser['id'] ? 'ranking-me' : '' ?> <?= $r['position'] <= 3 ? 'ranking-top' : '' ?>">
            <div class="rank-position <?= $r['position'] === 1 ? 'rank-gold' : ($r['position'] === 2 ? 'rank-silver' : ($r['position'] === 3 ? 'rank-bronze' : '')) ?>">
                #<?= $r['position'] ?>
            </div>
            <div class="rank-info">
                <span class="rank-name">
                    <?= htmlspecialchars($r['name']) ?>
                    <?php if ($r['is_admin']): ?><span class="admin-badge-sm">A</span><?php endif; ?>
                    <?php if ($r['id'] === $currentUser['id']): ?><span class="me-badge">T&Uacute;</span><?php endif; ?>
                </span>
                <span class="rank-score"><?= $r['score'] ?> pts</span>
            </div>
            <?php if ($r['prize'] > 0): ?>
            <div class="rank-prize"><?= $r['prize'] ?>&euro;</div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
