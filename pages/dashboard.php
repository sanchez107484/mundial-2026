<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
require_once __DIR__ . '/../includes/header.php';

$teams = getTeams();
$ranking = getRanking();
$currentUser = getCurrentUser();
$myRank = null;
foreach ($ranking['ranking'] as $r) {
    if ($r['id'] === $currentUser['id']) {
        $myRank = $r;
        break;
    }
}
?>
<main class="page-content">
    <div class="dashboard-hero">
        <div class="hero-pot">
            <span class="pot-label">BOTE</span>
            <span class="pot-amount"><?= $ranking['pot'] ?>€</span>
            <span class="pot-players"><?= $ranking['totalUsers'] ?> jugadores · 5€ entrada</span>
        </div>
    </div>

    <?php if ($myRank): ?>
    <div class="my-position-card">
        <div class="my-pos-rank">#<?= $myRank['position'] ?></div>
        <div class="my-pos-info">
            <span class="my-pos-name"><?= htmlspecialchars($myRank['name']) ?></span>
            <span class="my-pos-score"><?= $myRank['score'] ?> puntos</span>
        </div>
        <div class="my-pos-prize">
            <?php if ($myRank['prize'] > 0): ?>
            <span class="prize-amount"><?= $myRank['prize'] ?>€</span>
            <span class="prize-label">tu premio</span>
            <?php else: ?>
            <span class="prize-label">sigue intentando</span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="dashboard-grid">
        <a href="<?= BASE_URL ?>/pages/groups.php" class="dash-card">
            <div class="dash-icon">🏆</div>
            <div class="dash-label">GRUPOS</div>
            <div class="dash-desc">12 grupos · 48 equipos</div>
        </a>
        <a href="<?= BASE_URL ?>/pages/bracket.php" class="dash-card">
            <div class="dash-icon">📊</div>
            <div class="dash-label">CRUCES</div>
            <div class="dash-desc">Del dieciseisavos a la final</div>
        </a>
        <a href="<?= BASE_URL ?>/pages/predictions.php" class="dash-card">
            <div class="dash-icon">✏️</div>
            <div class="dash-label">MI PORRA</div>
            <div class="dash-desc">Tus predicciones</div>
        </a>
        <a href="<?= BASE_URL ?>/pages/ranking.php" class="dash-card">
            <div class="dash-icon">📈</div>
            <div class="dash-label">RANKING</div>
            <div class="dash-desc">Clasificación de la cuadrilla</div>
        </a>
    </div>

    <div class="scoring-info">
        <h3>PUNTUACIÓN</h3>
        <div class="scoring-grid">
            <div class="score-item"><span class="score-val">2</span><span class="score-lbl">1° grupo</span></div>
            <div class="score-item"><span class="score-val">1</span><span class="score-lbl">2° grupo</span></div>
            <div class="score-item"><span class="score-val">3</span><span class="score-lbl">Dieciseisavos</span></div>
            <div class="score-item"><span class="score-val">4</span><span class="score-lbl">Octavos</span></div>
            <div class="score-item"><span class="score-val">6</span><span class="score-lbl">Cuartos</span></div>
            <div class="score-item"><span class="score-val">10</span><span class="score-lbl">Semifinal</span></div>
            <div class="score-item score-highlight"><span class="score-val">25</span><span class="score-lbl">Finalista</span></div>
            <div class="score-item score-highlight"><span class="score-val">40</span><span class="score-lbl">Ganador</span></div>
            <div class="score-item"><span class="score-val">5</span><span class="score-lbl">Más goleador</span></div>
            <div class="score-item score-highlight"><span class="score-val">20</span><span class="score-lbl">Pichichi</span></div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>