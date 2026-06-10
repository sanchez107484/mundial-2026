<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
require_once __DIR__ . '/../includes/header.php';

$teams = getTeams();
$knockout = $teams['knockout'];
$results = getResults();
?>
<main class="page-content">
    <h1 class="page-title">Cuadro de Cruces</h1>
    <p class="page-subtitle">De dieciseisavos a la Final</p>

    <div class="bracket-wrapper">
        <div class="bracket-scroll-hint">Desliza para ver todo el cuadro &rarr;</div>
        <div class="bracket-container">
            <div class="bracket-round bracket-r32">
                <h3 class="round-title">Dieciseisavos</h3>
                <?php foreach ($knockout['roundOf32'] as $match): ?>
                <div class="bracket-match" data-match="<?= $match['id'] ?>">
                    <div class="match-label"><?= $match['label'] ?></div>
                    <div class="match-team"><?= $match['team1'] ?></div>
                    <div class="match-vs">vs</div>
                    <div class="match-team"><?= $match['team2'] ?></div>
                    <?php if (isset($results['knockout'][$match['id']])): ?>
                    <div class="match-result"><?= $results['knockout'][$match['id']]['winner'] ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="bracket-round bracket-r16">
                <h3 class="round-title">Octavos</h3>
                <?php foreach ($knockout['roundOf16'] as $match): ?>
                <div class="bracket-match" data-match="<?= $match['id'] ?>">
                    <div class="match-label"><?= $match['label'] ?></div>
                    <div class="match-team">Ganador <?= $match['from'][0] ?></div>
                    <div class="match-vs">vs</div>
                    <div class="match-team">Ganador <?= $match['from'][1] ?></div>
                    <?php if (isset($results['knockout'][$match['id']])): ?>
                    <div class="match-result"><?= $results['knockout'][$match['id']]['winner'] ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="bracket-round bracket-qf">
                <h3 class="round-title">Cuartos</h3>
                <?php foreach ($knockout['quarterFinals'] as $match): ?>
                <div class="bracket-match" data-match="<?= $match['id'] ?>">
                    <div class="match-label"><?= $match['label'] ?></div>
                    <div class="match-team">Ganador <?= $match['from'][0] ?></div>
                    <div class="match-vs">vs</div>
                    <div class="match-team">Ganador <?= $match['from'][1] ?></div>
                    <?php if (isset($results['knockout'][$match['id']])): ?>
                    <div class="match-result"><?= $results['knockout'][$match['id']]['winner'] ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="bracket-round bracket-sf">
                <h3 class="round-title">Semifinales</h3>
                <?php foreach ($knockout['semiFinals'] as $match): ?>
                <div class="bracket-match" data-match="<?= $match['id'] ?>">
                    <div class="match-label"><?= $match['label'] ?></div>
                    <div class="match-team">Ganador <?= $match['from'][0] ?></div>
                    <div class="match-vs">vs</div>
                    <div class="match-team">Ganador <?= $match['from'][1] ?></div>
                    <?php if (isset($results['knockout'][$match['id']])): ?>
                    <div class="match-result"><?= $results['knockout'][$match['id']]['winner'] ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="bracket-round bracket-final">
                <h3 class="round-title">Final</h3>
                <div class="bracket-match final-match" data-match="FINAL">
                    <div class="match-label">&#127942; FINAL</div>
                    <div class="match-team">Ganador SF1</div>
                    <div class="match-vs">vs</div>
                    <div class="match-team">Ganador SF2</div>
                    <?php if (isset($results['final']['winner'])): ?>
                    <div class="match-result champion"><?= $results['final']['winner'] ?> &#127942;</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
