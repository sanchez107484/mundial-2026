<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
require_once __DIR__ . '/../includes/header.php';

$teams = getTeams();
$knockout = $teams['knockout'];
$results = getResults();
$currentUser = getCurrentUser();
$predictions = getPredictions();
$userPred = $predictions[$currentUser['id']] ?? [];

$teamLookup = [];
foreach ($teams['groups'] as $group) {
    foreach ($group['teams'] as $t) {
        $teamLookup[$t['code']] = ['name' => $t['name'], 'flag' => $t['flag']];
    }
}

$savedGroups = $userPred['groups'] ?? [];
$savedKORaw = $userPred['knockout'] ?? [];
$savedKO = [];
foreach ($savedKORaw as $k => $v) {
    $savedKO[$k] = is_array($v) ? ($v['winner'] ?? '') : ($v ?? '');
}
$savedFinal = $userPred['final'] ?? [];

function resolvePosCode($pos, $savedGroups) {
    if (!$pos || strpos($pos, '3rd') !== false) return null;
    if (!preg_match('/^(\d)([A-L])$/', $pos, $m)) return null;
    $grp = $m[2]; $place = $m[1];
    return $place === '1'
        ? ($savedGroups[$grp]['first'] ?? null)
        : ($savedGroups[$grp]['second'] ?? null);
}

function getMatchWinnerCode($matchId, $results, $savedKO) {
    if (isset($results['knockout'][$matchId]['winner'])) {
        return $results['knockout'][$matchId]['winner'];
    }
    return $savedKO[$matchId] ?? null;
}

$koWinners = [];
foreach ($knockout['roundOf32'] as $match) {
    $koWinners[$match['id']] = getMatchWinnerCode($match['id'], $results, $savedKO);
}
foreach (['roundOf16', 'quarterFinals', 'semiFinals'] as $roundKey) {
    foreach ($knockout[$roundKey] as $match) {
        $koWinners[$match['id']] = getMatchWinnerCode($match['id'], $results, $savedKO);
    }
}
$koWinners['FINAL'] = $savedFinal['winner'] ?? ($results['final']['winner'] ?? null);
$koWinners['TP'] = getMatchWinnerCode('TP', $results, $savedKO);

function teamDisplay($code, $teamLookup, $is3rd = false) {
    if ($is3rd) {
        return '<span class="pending">♿ Serranillo en silla de ruedas</span>';
    }
    if ($code && isset($teamLookup[$code])) {
        return '<span class="flag">' . $teamLookup[$code]['flag'] . '</span><span class="name">' . $teamLookup[$code]['name'] . '</span>';
    }
    return '<span class="pending">' . ($code ? $code : 'Por definir') . '</span>';
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
                    <?php foreach ($knockout['roundOf32'] as $match):
                        $is3rd1 = strpos($match['team1'], '3rd') !== false;
                        $is3rd2 = strpos($match['team2'], '3rd') !== false;
                        $c1 = $is3rd1 ? null : resolvePosCode($match['team1'], $savedGroups);
                        $c2 = $is3rd2 ? null : resolvePosCode($match['team2'], $savedGroups);
                        $winner = $koWinners[$match['id']] ?? null;
                    ?>
                    <div class="bracket-match <?= $winner ? 'has-result' : '' ?>">
                        <div class="match-label"><?= $match['label'] ?> · <?= $match['venue'] ?></div>
                        <div class="match-teams">
                            <div class="match-team-row <?= $winner && $winner === $c1 ? 'winner' : '' ?>">
                                <?= teamDisplay($c1, $teamLookup, $is3rd1) ?>
                            </div>
                            <div class="match-vs">VS</div>
                            <div class="match-team-row <?= $winner && $winner === $c2 ? 'winner' : '' ?>">
                                <?= teamDisplay($c2, $teamLookup, $is3rd2) ?>
                            </div>
                        </div>
                        <?php if ($winner): ?>
                        <div class="match-result"><?= teamDisplay($winner, $teamLookup) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ROUND OF 16 -->
            <div class="bracket-round">
                <div class="round-title">OCTAVOS</div>
                <div class="round-matches">
                    <?php foreach ($knockout['roundOf16'] as $match):
                        $c1 = $koWinners[$match['from'][0]] ?? null;
                        $c2 = $koWinners[$match['from'][1]] ?? null;
                        $winner = $koWinners[$match['id']] ?? null;
                    ?>
                    <div class="bracket-match <?= $winner ? 'has-result' : '' ?>">
                        <div class="match-label"><?= $match['label'] ?> · <?= $match['venue'] ?></div>
                        <div class="match-teams">
                            <div class="match-team-row <?= $winner && $winner === $c1 ? 'winner' : '' ?>">
                                <?= teamDisplay($c1, $teamLookup) ?>
                            </div>
                            <div class="match-vs">VS</div>
                            <div class="match-team-row <?= $winner && $winner === $c2 ? 'winner' : '' ?>">
                                <?= teamDisplay($c2, $teamLookup) ?>
                            </div>
                        </div>
                        <?php if ($winner): ?>
                        <div class="match-result"><?= teamDisplay($winner, $teamLookup) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- QUARTER FINALS -->
            <div class="bracket-round">
                <div class="round-title">CUARTOS</div>
                <div class="round-matches">
                    <?php foreach ($knockout['quarterFinals'] as $match):
                        $c1 = $koWinners[$match['from'][0]] ?? null;
                        $c2 = $koWinners[$match['from'][1]] ?? null;
                        $winner = $koWinners[$match['id']] ?? null;
                    ?>
                    <div class="bracket-match <?= $winner ? 'has-result' : '' ?>">
                        <div class="match-label"><?= $match['label'] ?> · <?= $match['venue'] ?></div>
                        <div class="match-teams">
                            <div class="match-team-row <?= $winner && $winner === $c1 ? 'winner' : '' ?>">
                                <?= teamDisplay($c1, $teamLookup) ?>
                            </div>
                            <div class="match-vs">VS</div>
                            <div class="match-team-row <?= $winner && $winner === $c2 ? 'winner' : '' ?>">
                                <?= teamDisplay($c2, $teamLookup) ?>
                            </div>
                        </div>
                        <?php if ($winner): ?>
                        <div class="match-result"><?= teamDisplay($winner, $teamLookup) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- SEMI FINALS -->
            <div class="bracket-round">
                <div class="round-title">SEMIFINALES</div>
                <div class="round-matches">
                    <?php foreach ($knockout['semiFinals'] as $match):
                        $c1 = $koWinners[$match['from'][0]] ?? null;
                        $c2 = $koWinners[$match['from'][1]] ?? null;
                        $winner = $koWinners[$match['id']] ?? null;
                    ?>
                    <div class="bracket-match <?= $winner ? 'has-result' : '' ?>">
                        <div class="match-label"><?= $match['label'] ?> · <?= $match['venue'] ?></div>
                        <div class="match-teams">
                            <div class="match-team-row <?= $winner && $winner === $c1 ? 'winner' : '' ?>">
                                <?= teamDisplay($c1, $teamLookup) ?>
                            </div>
                            <div class="match-vs">VS</div>
                            <div class="match-team-row <?= $winner && $winner === $c2 ? 'winner' : '' ?>">
                                <?= teamDisplay($c2, $teamLookup) ?>
                            </div>
                        </div>
                        <?php if ($winner): ?>
                        <div class="match-result"><?= teamDisplay($winner, $teamLookup) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- FINAL -->
            <div class="bracket-round">
                <div class="round-title">FINAL</div>
                <div class="round-matches">
                    <?php
                    $fc1 = $koWinners['SF_1'] ?? null;
                    $fc2 = $koWinners['SF_2'] ?? null;
                    $fWinner = $koWinners['FINAL'] ?? null;
                    ?>
                    <div class="bracket-match final-match <?= $fWinner ? 'has-result' : '' ?>">
                        <div class="match-label">🏆 <?= $knockout['final']['label'] ?> · <?= $knockout['final']['venue'] ?></div>
                        <div class="match-teams">
                            <div class="match-team-row <?= $fWinner && $fWinner === $fc1 ? 'winner' : '' ?>">
                                <?= teamDisplay($fc1, $teamLookup) ?>
                            </div>
                            <div class="match-vs">VS</div>
                            <div class="match-team-row <?= $fWinner && $fWinner === $fc2 ? 'winner' : '' ?>">
                                <?= teamDisplay($fc2, $teamLookup) ?>
                            </div>
                        </div>
                        <?php if ($fWinner): ?>
                        <div class="match-result champion">🏆 <?= teamDisplay($fWinner, $teamLookup) ?></div>
                        <?php if (isset($savedFinal['runner_up']) && $savedFinal['runner_up']): ?>
                        <div class="match-result" style="font-size:13px; color: var(--text-dim); border-top: none; margin-top: 0; padding-top: 4px;">
                            Subcampeón: <?= teamDisplay($savedFinal['runner_up'], $teamLookup) ?>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Third Place -->
                    <div class="bracket-match" style="margin-top: 24px;">
                        <div class="match-label">🥉 <?= $knockout['thirdPlace']['label'] ?> · <?= $knockout['thirdPlace']['venue'] ?></div>
                        <div class="match-teams">
                            <div class="match-team-row">
                                <span class="pending">Por definir</span>
                            </div>
                            <div class="match-vs">VS</div>
                            <div class="match-team-row">
                                <span class="pending">Por definir</span>
                            </div>
                        </div>
                        <?php if ($koWinners['TP']): ?>
                        <div class="match-result"><?= teamDisplay($koWinners['TP'], $teamLookup) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
