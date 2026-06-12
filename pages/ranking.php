<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
require_once __DIR__ . '/../includes/header.php';

$ranking = getRanking();
$currentUser = getCurrentUser();
?>
<main class="page-content">
    <div class="page-header">
        <h1 class="page-title">RANKING</h1>
    </div>

    <div class="ranking-header">
        <div class="ranking-pot-info">
            <span class="ranking-pot-label">BOTE TOTAL</span>
            <span class="ranking-pot-value"><?= $ranking['pot'] ?>€</span>
        </div>
        <div class="ranking-splits">
            <div class="split-item">
                <span class="split-medal">🥇</span>
                <span class="split-pct"><?= $ranking['pot']  - 10 ?>€</span>
            </div>
            <div class="split-item">
                <span class="split-medal">🥈</span>
                <span class="split-pct">10€</span>
            </div>
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
                    <?php if ($r['id'] === $currentUser['id']): ?><span class="me-badge">TÚ</span><?php endif; ?>
                </span>
                <span class="rank-score"><?= $r['score'] ?> pts</span>
            </div>
            <?php if ($r['prize'] > 0): ?>
            <div class="rank-prize"><?= $r['prize'] ?>€</div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>