<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
require_once __DIR__ . '/../includes/header.php';

$teams = getTeams();
$knockout = $teams['knockout'];
$results = getResults();

// Build team lookup with Spanish names
$teamLookup = [];
foreach ($teams['groups'] as $group) {
    foreach ($group['teams'] as $t) {
        $teamLookup[$t['code']] = ['name' => $t['name'], 'flag' => $t['flag']];
    }
}

function getTeamDisplay($code, $teamLookup) {
    if (isset($teamLookup[$code])) {
        return '<span class="flag">' . $teamLookup[$code]['flag'] . '</span><span class="name">' . $teamLookup[$code]['name'] . '</span>';
    }
    return '<span class="pending">' . $code . '</span>';
}

function getMatchWinner($matchId, $results, $teamLookup) {
    if (isset($results['knockout'][$matchId]['winner'])) {
        return getTeamDisplay($results['knockout'][$matchId]['winner'], $teamLookup);
    }
    return null;
}
?>
<main class="page-content">
    <div class="page-header">
        <h1 class="page-title">CUADRO DE CRUCES</h1>
        <p class="page-subtitle">Del dieciseisavos a la Final</p>
    </div>

    <div class="bracket-wrapper">
        <div class="bracket-scroll-hint">← Desliza para ver todo el cuadro →</div>
        <div class="bracket-container">
            <!-- ROUND OF 32 -->
            <div class="bracket-round">
                <div class="round-title">DIECISEISAVOS</div>
                <div class="round-matches">
                    <?php foreach ($knockout['roundOf32'] as $match): ?>
                    <div class="bracket-match <?= isset($results['knockout'][$match['id']]) ? 'has-result' : '' ?>">
                        <div class="match-label"><?= $match['label'] ?> · <?= $match['venue'] ?></div>
                        <div class="match-teams">
                            <div class="match-team-row <?= isset($results['knockout'][$match['id']]) && $results['knockout'][$match['id']]['winner'] === $match['team1'] ? 'winner' : '' ?>">
                                <?= getTeamDisplay($match['team1'], $teamLookup) ?>
                            </div>
                            <div class="match-vs">VS</div>
                            <div class="match-team-row <?= isset($results['knockout'][$match['id']]) && $results['knockout'][$match['id']]['winner'] === $match['team2'] ? 'winner' : '' ?>">
                                <?= getTeamDisplay($match['team2'], $teamLookup) ?>
                            </div>
                        </div>
                        <?php if (isset($results['knockout'][$match['id']])): ?>
                        <div class="match-result"><?= getMatchWinner($match['id'], $results, $teamLookup) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ROUND OF 16 -->
            <div class="bracket-round">
                <div class="round-title">OCTAVOS</div>
                <div class="round-matches">
                    <?php foreach ($knockout['roundOf16'] as $match): ?>
                    <div class="bracket-match <?= isset($results['knockout'][$match['id']]) ? 'has-result' : '' ?>">
                        <div class="match-label"><?= $match['label'] ?> · <?= $match['venue'] ?></div>
                        <div class="match-teams">
                            <div class="match-team-row <?= isset($results['knockout'][$match['id']]) ? 'winner' : '' ?>">
                                <?= getTeamDisplay('?', $teamLookup) ?>
                            </div>
                            <div class="match-vs">VS</div>
                            <div class="match-team-row <?= isset($results['knockout'][$match['id']]) ? 'winner' : '' ?>">
                                <?= getTeamDisplay('?', $teamLookup) ?>
                            </div>
                        </div>
                        <?php if (isset($results['knockout'][$match['id']])): ?>
                        <div class="match-result"><?= getMatchWinner($match['id'], $results, $teamLookup) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- QUARTER FINALS -->
            <div class="bracket-round">
                <div class="round-title">CUARTOS</div>
                <div class="round-matches">
                    <?php foreach ($knockout['quarterFinals'] as $match): ?>
                    <div class="bracket-match <?= isset($results['knockout'][$match['id']]) ? 'has-result' : '' ?>">
                        <div class="match-label"><?= $match['label'] ?> · <?= $match['venue'] ?></div>
                        <div class="match-teams">
                            <div class="match-team-row"><?= getTeamDisplay('?', $teamLookup) ?></div>
                            <div class="match-vs">VS</div>
                            <div class="match-team-row"><?= getTeamDisplay('?', $teamLookup) ?></div>
                        </div>
                        <?php if (isset($results['knockout'][$match['id']])): ?>
                        <div class="match-result"><?= getMatchWinner($match['id'], $results, $teamLookup) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- SEMI FINALS -->
            <div class="bracket-round">
                <div class="round-title">SEMIFINALES</div>
                <div class="round-matches">
                    <?php foreach ($knockout['semiFinals'] as $match): ?>
                    <div class="bracket-match <?= isset($results['knockout'][$match['id']]) ? 'has-result' : '' ?>">
                        <div class="match-label"><?= $match['label'] ?> · <?= $match['venue'] ?></div>
                        <div class="match-teams">
                            <div class="match-team-row"><?= getTeamDisplay('?', $teamLookup) ?></div>
                            <div class="match-vs">VS</div>
                            <div class="match-team-row"><?= getTeamDisplay('?', $teamLookup) ?></div>
                        </div>
                        <?php if (isset($results['knockout'][$match['id']])): ?>
                        <div class="match-result"><?= getMatchWinner($match['id'], $results, $teamLookup) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- FINAL -->
            <div class="bracket-round">
                <div class="round-title">FINAL</div>
                <div class="round-matches">
                    <div class="bracket-match final-match <?= isset($results['final']['winner']) ? 'has-result' : '' ?>">
                        <div class="match-label">🏆 <?= $knockout['final']['label'] ?> · <?= $knockout['final']['venue'] ?></div>
                        <div class="match-teams">
                            <div class="match-team-row"><?= getTeamDisplay('?', $teamLookup) ?></div>
                            <div class="match-vs">VS</div>
                            <div class="match-team-row"><?= getTeamDisplay('?', $teamLookup) ?></div>
                        </div>
                        <?php if (isset($results['final']['winner'])): ?>
                        <div class="match-result champion">🏆 <?= getTeamDisplay($results['final']['winner'], $teamLookup) ?></div>
                        <?php if (isset($results['final']['runner_up'])): ?>
                        <div class="match-result" style="font-size:13px; color: var(--text-dim); border-top: none; margin-top: 0; padding-top: 4px;">
                            Subcampeón: <?= getTeamDisplay($results['final']['runner_up'], $teamLookup) ?>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Third Place -->
                    <div class="bracket-match" style="margin-top: 24px;">
                        <div class="match-label">🥉 <?= $knockout['thirdPlace']['label'] ?> · <?= $knockout['thirdPlace']['venue'] ?></div>
                        <div class="match-teams">
                            <div class="match-team-row"><?= getTeamDisplay('?', $teamLookup) ?></div>
                            <div class="match-vs">VS</div>
                            <div class="match-team-row"><?= getTeamDisplay('?', $teamLookup) ?></div>
                        </div>
                        <?php if (isset($results['knockout'][$knockout['thirdPlace']['id']])): ?>
                        <div class="match-result"><?= getMatchWinner($knockout['thirdPlace']['id'], $results, $teamLookup) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>