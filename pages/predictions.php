<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
require_once __DIR__ . '/../includes/header.php';

$teams    = getTeams();
$groups   = $teams['groups'];
$knockout = $teams['knockout'];
$currentUser = getCurrentUser();
$predictions = getPredictions();
$userPred    = $predictions[$currentUser['id']] ?? [];

// Normalise saved knockout to flat { matchId: "CODE" }
$savedGroups  = $userPred['groups']  ?? [];
$savedKORaw   = $userPred['knockout'] ?? [];
$savedKO      = [];
foreach ($savedKORaw as $k => $v) {
    $savedKO[$k] = is_array($v) ? ($v['winner'] ?? '') : ($v ?? '');
}
$savedFinal       = $userPred['final']          ?? [];
$savedTopScorer   = $userPred['top_scorer_team'] ?? '';
$savedPichichi    = $userPred['pichichi']        ?? '';

$teamLookup = [];
foreach ($groups as $group) {
    foreach ($group['teams'] as $t) {
        $teamLookup[$t['code']] = ['name' => $t['name'], 'flag' => $t['flag']];
    }
}

// Count completed groups
$doneGroups = 0;
foreach ($groups as $letter => $g) {
    if (!empty($savedGroups[$letter]['first']) && !empty($savedGroups[$letter]['second'])) {
        $doneGroups++;
    }
}
$totalGroups = count($groups);

// Count completed KO rounds
function countDone($matches, $savedKO) {
    $done = 0;
    foreach ($matches as $m) {
        if (!empty($savedKO[$m['id']])) $done++;
    }
    return $done;
}
$doneR32 = countDone($knockout['roundOf32'],    $savedKO);
$doneR16 = countDone($knockout['roundOf16'],    $savedKO);
$doneQF  = countDone($knockout['quarterFinals'],$savedKO);
$doneSF  = countDone($knockout['semiFinals'],   $savedKO);
$doneF   = !empty($savedFinal['winner']) ? 1 : 0;
$doneBonus = (!empty($savedTopScorer) ? 1 : 0) + (!empty($savedPichichi) ? 1 : 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Porra · La Porra de Zoputos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <script>window.BASE_URL = '<?= BASE_URL ?>';</script>
    <style>
/* ── PREDICTIONS V2 ───────────────────────────────────────────── */

.pred-page {
    min-height: 100vh;
    background: var(--bg-primary);
    padding-bottom: 100px;
}

/* STEP NAV */
.step-nav {
    position: sticky;
    top: var(--nav-height);
    z-index: 90;
    background: rgba(5,10,5,0.97);
    backdrop-filter: blur(16px);
    border-bottom: 1px solid var(--border);
    padding: 0;
}

.step-tabs {
    display: flex;
    overflow-x: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.step-tabs::-webkit-scrollbar { display: none; }

.step-tab {
    flex: 1;
    min-width: 80px;
    padding: 14px 8px 12px;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    color: var(--text-muted);
    font-family: var(--font-display);
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
}

.step-tab .tab-icon { font-size: 18px; }
.step-tab .tab-count {
    font-size: 9px;
    color: var(--text-muted);
    font-family: var(--font-body);
    font-weight: 500;
}
.step-tab .tab-count.done { color: var(--green-primary); }

.step-tab:hover { color: var(--text-dim); }
.step-tab.active {
    color: var(--green-primary);
    border-bottom-color: var(--green-primary);
}
.step-tab.active .tab-count { color: var(--green-secondary); }

/* PANELS */
.step-panel { display: none; padding: 24px 16px 0; max-width: 680px; margin: 0 auto; }
.step-panel.active { display: block; }

.panel-title {
    font-family: var(--font-display);
    font-size: 22px;
    font-weight: 700;
    letter-spacing: 2px;
    color: var(--text-bright);
    margin-bottom: 4px;
}

.panel-subtitle {
    font-size: 13px;
    color: var(--text-dim);
    margin-bottom: 20px;
}

/* PROGRESS BAR */
.progress-bar {
    height: 4px;
    background: rgba(255,255,255,0.08);
    border-radius: 2px;
    margin-bottom: 24px;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--green-dim), var(--green-primary));
    border-radius: 2px;
    transition: width 0.4s ease;
}

/* ── GROUP CARDS ─────────────────────────────────────────────── */
.group-list { display: flex; flex-direction: column; gap: 10px; }

.pred-group-card {
    background: var(--bg-card);
    border: 1.5px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    transition: border-color 0.2s;
}

.pred-group-card.complete { border-color: rgba(0,255,106,0.25); }

.pred-group-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: rgba(255,255,255,0.02);
    border-bottom: 1px solid var(--border);
}

.pred-group-letter {
    font-family: var(--font-display);
    font-size: 22px;
    font-weight: 700;
    color: var(--green-primary);
    min-width: 28px;
}

.pred-group-selection {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.sel-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.sel-badge.pos-1 {
    background: rgba(0,255,106,0.12);
    border: 1px solid rgba(0,255,106,0.3);
    color: var(--green-primary);
}

.sel-badge.pos-2 {
    background: rgba(255,215,0,0.1);
    border: 1px solid rgba(255,215,0,0.25);
    color: var(--gold-primary);
}

.sel-badge.empty {
    background: rgba(255,255,255,0.04);
    border: 1px dashed rgba(255,255,255,0.15);
    color: var(--text-muted);
}

.pred-group-teams {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1px;
    background: var(--border);
}

.pred-team-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 13px 14px;
    background: var(--bg-card);
    border: none;
    color: var(--text);
    cursor: pointer;
    transition: background 0.15s;
    text-align: left;
    width: 100%;
}

.pred-team-btn:hover { background: var(--bg-card-hover); }
.pred-team-btn:active { background: rgba(0,255,106,0.08); }

.pred-team-btn.is-first { background: rgba(0,255,106,0.07); }
.pred-team-btn.is-second { background: rgba(255,215,0,0.06); }

.team-btn-flag { font-size: 22px; flex-shrink: 0; }
.team-btn-info { display: flex; flex-direction: column; min-width: 0; }
.team-btn-name {
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.team-btn-rank {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.5px;
    margin-top: 1px;
}
.team-btn-rank.r1 { color: var(--green-primary); }
.team-btn-rank.r2 { color: var(--gold-primary); }
.team-btn-rank.r0 { color: transparent; }

/* ── KO CARDS ────────────────────────────────────────────────── */
.ko-section { margin-bottom: 28px; }

.ko-section-title {
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 2px;
    color: var(--text-muted);
    text-transform: uppercase;
    margin-bottom: 10px;
    padding-left: 2px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.ko-section-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
}

.ko-list { display: flex; flex-direction: column; gap: 8px; }

.ko-match-btn {
    width: 100%;
    background: var(--bg-card);
    border: 1.5px solid var(--border);
    border-radius: 12px;
    padding: 14px 16px;
    cursor: pointer;
    transition: all 0.15s;
    text-align: left;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 12px;
}

.ko-match-btn:hover { border-color: var(--border-bright); background: var(--bg-card-hover); }
.ko-match-btn.has-pick { border-color: rgba(0,255,106,0.2); }
.ko-match-btn.locked { opacity: 0.45; cursor: not-allowed; }
.ko-match-btn.is-final { border-color: var(--gold-dim); background: rgba(255,215,0,0.04); }
.ko-match-btn.is-final:hover { border-color: var(--gold-primary); }

.ko-match-teams {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.ko-team-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-dim);
}

.ko-team-row.is-winner {
    color: var(--text-bright);
    font-weight: 700;
}

.ko-team-row.is-winner .ko-team-flag { filter: none; }
.ko-team-flag { font-size: 20px; }
.ko-team-name { flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.ko-vs {
    font-size: 10px;
    color: var(--text-muted);
    font-weight: 600;
    letter-spacing: 1px;
    padding-left: 28px;
}

.ko-match-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 3px;
    flex-shrink: 0;
}

.ko-match-label {
    font-size: 10px;
    font-weight: 700;
    color: var(--text-muted);
    letter-spacing: 1px;
    font-family: var(--font-display);
}

.ko-arrow {
    font-size: 16px;
    color: var(--text-muted);
}

.ko-match-btn.has-pick .ko-arrow { color: var(--green-primary); }
.ko-match-btn.is-final.has-pick .ko-arrow { color: var(--gold-primary); }

/* ── BONUS ───────────────────────────────────────────────────── */
.bonus-cards { display: flex; flex-direction: column; gap: 16px; }

.bonus-card {
    background: var(--bg-card);
    border: 1.5px solid var(--border);
    border-radius: 14px;
    padding: 20px;
}

.bonus-card.has-value { border-color: rgba(0,255,106,0.25); }

.bonus-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
}

.bonus-icon { font-size: 28px; }

.bonus-card-title {
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 600;
    letter-spacing: 1px;
    color: var(--text-bright);
}

.bonus-card-pts {
    margin-left: auto;
    font-family: var(--font-display);
    font-size: 20px;
    font-weight: 700;
    color: var(--green-primary);
}

.bonus-card-desc {
    font-size: 12px;
    color: var(--text-dim);
    margin-bottom: 14px;
}

.bonus-input {
    width: 100%;
    padding: 13px 16px;
    font-size: 15px;
    font-family: var(--font-body);
    background: rgba(255,255,255,0.05);
    border: 2px solid var(--border);
    border-radius: 10px;
    color: var(--text-bright);
    transition: all 0.2s;
}

.bonus-input:focus {
    outline: none;
    border-color: var(--green-primary);
    background: rgba(0,255,106,0.03);
    box-shadow: 0 0 0 3px rgba(0,255,106,0.1);
}

.bonus-input::placeholder { color: var(--text-muted); }

/* ── BOTTOM SAVE BAR ─────────────────────────────────────────── */
.save-bar {
    position: fixed;
    bottom: 0; left: 0; right: 0;
    padding: 12px 16px 16px;
    background: linear-gradient(to top, var(--bg-primary) 75%, transparent);
    z-index: 80;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    pointer-events: none;
}

.save-bar-btn {
    pointer-events: all;
    width: 100%;
    max-width: 400px;
    padding: 16px;
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    background: linear-gradient(135deg, var(--green-secondary), var(--green-dim));
    color: #fff;
    border: none;
    border-radius: 14px;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 20px rgba(0,255,106,0.25);
}

.save-bar-btn:hover {
    background: linear-gradient(135deg, var(--green-primary), var(--green-secondary));
    transform: translateY(-1px);
    box-shadow: 0 8px 28px rgba(0,255,106,0.35);
}

.save-bar-btn:active { transform: translateY(0); }

.save-bar-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.save-status {
    pointer-events: none;
    font-size: 13px;
    font-weight: 600;
    min-height: 18px;
    color: var(--green-primary);
    transition: opacity 0.3s;
}

.save-status.error { color: var(--danger); }

/* ── PICKER BOTTOM SHEET ─────────────────────────────────────── */
.picker-overlay {
    position: fixed;
    inset: 0;
    z-index: 200;
    display: none;
}

.picker-overlay.open { display: flex; align-items: flex-end; }

.picker-scrim {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.65);
}

.picker-sheet {
    position: relative;
    width: 100%;
    max-width: 520px;
    margin: 0 auto;
    background: #0e1e0e;
    border: 1px solid var(--border-bright);
    border-radius: 20px 20px 0 0;
    padding: 12px 16px 32px;
    max-height: 80vh;
    overflow-y: auto;
    animation: slideUp 0.25s ease;
}

@keyframes slideUp {
    from { transform: translateY(60px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}

.picker-handle {
    width: 36px; height: 4px;
    background: rgba(255,255,255,0.15);
    border-radius: 2px;
    margin: 0 auto 16px;
}

.picker-heading {
    font-family: var(--font-display);
    font-size: 18px;
    font-weight: 700;
    letter-spacing: 1px;
    text-align: center;
    margin-bottom: 16px;
    color: var(--text-bright);
}

.picker-subheading {
    font-size: 12px;
    color: var(--text-muted);
    text-align: center;
    margin: -10px 0 14px;
    letter-spacing: 0.5px;
}

.picker-opts { display: flex; flex-direction: column; gap: 8px; }

.picker-opt {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 15px 16px;
    background: rgba(255,255,255,0.04);
    border: 1.5px solid var(--border);
    border-radius: 12px;
    cursor: pointer;
    color: var(--text);
    font-size: 15px;
    font-weight: 600;
    transition: all 0.15s;
    width: 100%;
    text-align: left;
}

.picker-opt:hover {
    background: var(--bg-card-hover);
    border-color: var(--green-secondary);
}

.picker-opt.selected {
    background: rgba(0,255,106,0.1);
    border-color: var(--green-primary);
    color: var(--green-primary);
}

.picker-opt-flag { font-size: 26px; }
.picker-opt-name { flex: 1; }
.picker-opt-check { color: var(--green-primary); font-size: 18px; }

.picker-opt.clear-opt {
    justify-content: center;
    color: var(--text-muted);
    font-size: 13px;
    font-weight: 500;
    padding: 12px;
    margin-top: 4px;
    border-style: dashed;
}

.picker-opt.clear-opt:hover { color: var(--danger); border-color: var(--danger); }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/header.php'; // already included above - skip ?>

<div class="pred-page">

    <!-- STEP NAV -->
    <nav class="step-nav">
        <div class="step-tabs">
            <button class="step-tab active" data-panel="grupos">
                <span class="tab-icon">🏆</span>
                <span>Grupos</span>
                <span class="tab-count <?= $doneGroups === $totalGroups ? 'done' : '' ?>" id="tab-count-grupos"><?= $doneGroups ?>/<?= $totalGroups ?></span>
            </button>
            <button class="step-tab" data-panel="r32">
                <span class="tab-icon">⚽</span>
                <span>1/16</span>
                <span class="tab-count <?= $doneR32 === 16 ? 'done' : '' ?>" id="tab-count-r32"><?= $doneR32 ?>/16</span>
            </button>
            <button class="step-tab" data-panel="r16">
                <span class="tab-icon">⚽</span>
                <span>Octavos</span>
                <span class="tab-count <?= $doneR16 === 8 ? 'done' : '' ?>" id="tab-count-r16"><?= $doneR16 ?>/8</span>
            </button>
            <button class="step-tab" data-panel="qf">
                <span class="tab-icon">⚽</span>
                <span>Cuartos</span>
                <span class="tab-count <?= $doneQF === 4 ? 'done' : '' ?>" id="tab-count-qf"><?= $doneQF ?>/4</span>
            </button>
            <button class="step-tab" data-panel="sf">
                <span class="tab-icon">⚽</span>
                <span>Semis</span>
                <span class="tab-count <?= $doneSF === 2 ? 'done' : '' ?>" id="tab-count-sf"><?= $doneSF ?>/2</span>
            </button>
            <button class="step-tab" data-panel="final">
                <span class="tab-icon">🏅</span>
                <span>Final</span>
                <span class="tab-count <?= $doneF ? 'done' : '' ?>" id="tab-count-final"><?= $doneF ?>/1</span>
            </button>
            <button class="step-tab" data-panel="bonus">
                <span class="tab-icon">⭐</span>
                <span>Bonus</span>
                <span class="tab-count <?= $doneBonus === 2 ? 'done' : '' ?>" id="tab-count-bonus"><?= $doneBonus ?>/2</span>
            </button>
        </div>
    </nav>

    <!-- ── PANEL: GRUPOS ──────────────────────────────────────── -->
    <div class="step-panel active" id="panel-grupos">
        <div class="panel-title">Fase de Grupos</div>
        <p class="panel-subtitle">Elige el 1° y 2° de cada grupo · toca un equipo para asignarlo</p>
        <div class="progress-bar">
            <div class="progress-fill" id="prog-grupos" style="width:<?= round($doneGroups/$totalGroups*100) ?>%"></div>
        </div>
        <div class="group-list">
            <?php foreach ($groups as $letter => $group): ?>
            <?php
            $f = $savedGroups[$letter]['first']  ?? '';
            $s = $savedGroups[$letter]['second'] ?? '';
            $fInfo = $f && isset($teamLookup[$f]) ? $teamLookup[$f] : null;
            $sInfo = $s && isset($teamLookup[$s]) ? $teamLookup[$s] : null;
            $complete = $fInfo && $sInfo;
            ?>
            <div class="pred-group-card <?= $complete ? 'complete' : '' ?>" id="gcard-<?= $letter ?>">
                <div class="pred-group-header">
                    <span class="pred-group-letter"><?= $letter ?></span>
                    <div class="pred-group-selection" id="gsel-<?= $letter ?>">
                        <?php if ($fInfo): ?>
                        <span class="sel-badge pos-1"><?= $fInfo['flag'] ?> <?= $fInfo['name'] ?></span>
                        <?php else: ?>
                        <span class="sel-badge empty">1° sin elegir</span>
                        <?php endif; ?>
                        <?php if ($sInfo): ?>
                        <span class="sel-badge pos-2"><?= $sInfo['flag'] ?> <?= $sInfo['name'] ?></span>
                        <?php else: ?>
                        <span class="sel-badge empty">2° sin elegir</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="pred-group-teams">
                    <?php foreach ($group['teams'] as $team): ?>
                    <?php
                    $isFirst  = ($f === $team['code']);
                    $isSecond = ($s === $team['code']);
                    ?>
                    <button type="button"
                        class="pred-team-btn <?= $isFirst ? 'is-first' : ($isSecond ? 'is-second' : '') ?>"
                        data-group="<?= $letter ?>"
                        data-code="<?= $team['code'] ?>"
                        data-flag="<?= $team['flag'] ?>"
                        data-name="<?= htmlspecialchars($team['name']) ?>">
                        <span class="team-btn-flag"><?= $team['flag'] ?></span>
                        <span class="team-btn-info">
                            <span class="team-btn-name"><?= $team['name'] ?></span>
                            <span class="team-btn-rank <?= $isFirst ? 'r1' : ($isSecond ? 'r2' : 'r0') ?>">
                                <?= $isFirst ? '1° CLASIFICADO' : ($isSecond ? '2° CLASIFICADO' : '·') ?>
                            </span>
                        </span>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── PANEL: DIECISEISAVOS ───────────────────────────────── -->
    <div class="step-panel" id="panel-r32">
        <div class="panel-title">Dieciseisavos</div>
        <p class="panel-subtitle">Elige el ganador de cada partido · los equipos dependen de tu fase de grupos</p>
        <div class="progress-bar">
            <div class="progress-fill" id="prog-r32" style="width:<?= round($doneR32/16*100) ?>%"></div>
        </div>
        <div class="ko-list" id="list-r32">
        <?php foreach ($knockout['roundOf32'] as $match): ?>
        <?php
        $winner = $savedKO[$match['id']] ?? '';
        $hasPick = !empty($winner);
        ?>
        <button type="button"
            class="ko-match-btn <?= $hasPick ? 'has-pick' : '' ?>"
            data-match="<?= $match['id'] ?>"
            data-round="r32"
            data-t1pos="<?= htmlspecialchars($match['team1']) ?>"
            data-t2pos="<?= htmlspecialchars($match['team2']) ?>"
            data-winner="<?= htmlspecialchars($winner) ?>">
            <div class="ko-match-teams">
                <div class="ko-team-row <?= ($hasPick && $winner && !str_contains($match['team2'], $winner)) ? '' : '' ?>" data-side="t1">
                    <span class="ko-team-flag" data-flag-t1></span>
                    <span class="ko-team-name" data-name-t1><?= htmlspecialchars($match['team1']) ?></span>
                </div>
                <div class="ko-vs">VS</div>
                <div class="ko-team-row" data-side="t2">
                    <span class="ko-team-flag" data-flag-t2></span>
                    <span class="ko-team-name" data-name-t2><?= htmlspecialchars($match['team2']) ?></span>
                </div>
            </div>
            <div class="ko-match-meta">
                <span class="ko-match-label"><?= $match['label'] ?></span>
                <span class="ko-arrow"><?= $hasPick ? '✓' : '›' ?></span>
            </div>
        </button>
        <?php endforeach; ?>
        </div>
    </div>

    <!-- ── PANEL: OCTAVOS ────────────────────────────────────── -->
    <div class="step-panel" id="panel-r16">
        <div class="panel-title">Octavos de Final</div>
        <p class="panel-subtitle">Los equipos vienen de los ganadores de dieciseisavos</p>
        <div class="progress-bar">
            <div class="progress-fill" id="prog-r16" style="width:<?= round($doneR16/8*100) ?>%"></div>
        </div>
        <div class="ko-list" id="list-r16">
        <?php foreach ($knockout['roundOf16'] as $match): ?>
        <?php
        $winner = $savedKO[$match['id']] ?? '';
        $hasPick = !empty($winner);
        $src1 = $match['from'][0]; $src2 = $match['from'][1];
        $c1 = $savedKO[$src1] ?? ''; $c2 = $savedKO[$src2] ?? '';
        $serranillo = ['name' => 'Serranillo en silla de ruedas', 'flag' => '♿'];
        $t1i = ($c1 === 'SERRANILLO') ? $serranillo : ($c1 && isset($teamLookup[$c1]) ? $teamLookup[$c1] : null);
        $t2i = ($c2 === 'SERRANILLO') ? $serranillo : ($c2 && isset($teamLookup[$c2]) ? $teamLookup[$c2] : null);
        $locked = !$t1i || !$t2i;
        ?>
        <button type="button"
            class="ko-match-btn <?= $hasPick ? 'has-pick' : '' ?> <?= $locked ? 'locked' : '' ?>"
            data-match="<?= $match['id'] ?>"
            data-round="r16"
            data-src1="<?= $src1 ?>" data-src2="<?= $src2 ?>"
            data-winner="<?= htmlspecialchars($winner) ?>"
            <?= $locked ? 'disabled' : '' ?>>
            <div class="ko-match-teams">
                <div class="ko-team-row <?= ($hasPick && $winner === $c1) ? 'is-winner' : '' ?>">
                    <span class="ko-team-flag"><?= $t1i ? $t1i['flag'] : '❓' ?></span>
                    <span class="ko-team-name"><?= $t1i ? htmlspecialchars($t1i['name']) : 'Ganador '.$src1 ?></span>
                </div>
                <div class="ko-vs">VS</div>
                <div class="ko-team-row <?= ($hasPick && $winner === $c2) ? 'is-winner' : '' ?>">
                    <span class="ko-team-flag"><?= $t2i ? $t2i['flag'] : '❓' ?></span>
                    <span class="ko-team-name"><?= $t2i ? htmlspecialchars($t2i['name']) : 'Ganador '.$src2 ?></span>
                </div>
            </div>
            <div class="ko-match-meta">
                <span class="ko-match-label"><?= $match['label'] ?></span>
                <span class="ko-arrow"><?= $hasPick ? '✓' : ($locked ? '🔒' : '›') ?></span>
            </div>
        </button>
        <?php endforeach; ?>
        </div>
    </div>

    <!-- ── PANEL: CUARTOS ────────────────────────────────────── -->
    <div class="step-panel" id="panel-qf">
        <div class="panel-title">Cuartos de Final</div>
        <p class="panel-subtitle">Los equipos vienen de los ganadores de octavos</p>
        <div class="progress-bar">
            <div class="progress-fill" id="prog-qf" style="width:<?= round($doneQF/4*100) ?>%"></div>
        </div>
        <div class="ko-list" id="list-qf">
        <?php foreach ($knockout['quarterFinals'] as $match): ?>
        <?php
        $winner = $savedKO[$match['id']] ?? '';
        $hasPick = !empty($winner);
        $src1 = $match['from'][0]; $src2 = $match['from'][1];
        $c1 = $savedKO[$src1] ?? ''; $c2 = $savedKO[$src2] ?? '';
        $serranillo = ['name' => 'Serranillo en silla de ruedas', 'flag' => '♿'];
        $t1i = ($c1 === 'SERRANILLO') ? $serranillo : ($c1 && isset($teamLookup[$c1]) ? $teamLookup[$c1] : null);
        $t2i = ($c2 === 'SERRANILLO') ? $serranillo : ($c2 && isset($teamLookup[$c2]) ? $teamLookup[$c2] : null);
        $locked = !$t1i || !$t2i;
        ?>
        <button type="button"
            class="ko-match-btn <?= $hasPick ? 'has-pick' : '' ?> <?= $locked ? 'locked' : '' ?>"
            data-match="<?= $match['id'] ?>"
            data-round="qf"
            data-src1="<?= $src1 ?>" data-src2="<?= $src2 ?>"
            data-winner="<?= htmlspecialchars($winner) ?>"
            <?= $locked ? 'disabled' : '' ?>>
            <div class="ko-match-teams">
                <div class="ko-team-row <?= ($hasPick && $winner === $c1) ? 'is-winner' : '' ?>">
                    <span class="ko-team-flag"><?= $t1i ? $t1i['flag'] : '❓' ?></span>
                    <span class="ko-team-name"><?= $t1i ? htmlspecialchars($t1i['name']) : 'Ganador '.$src1 ?></span>
                </div>
                <div class="ko-vs">VS</div>
                <div class="ko-team-row <?= ($hasPick && $winner === $c2) ? 'is-winner' : '' ?>">
                    <span class="ko-team-flag"><?= $t2i ? $t2i['flag'] : '❓' ?></span>
                    <span class="ko-team-name"><?= $t2i ? htmlspecialchars($t2i['name']) : 'Ganador '.$src2 ?></span>
                </div>
            </div>
            <div class="ko-match-meta">
                <span class="ko-match-label"><?= $match['label'] ?></span>
                <span class="ko-arrow"><?= $hasPick ? '✓' : ($locked ? '🔒' : '›') ?></span>
            </div>
        </button>
        <?php endforeach; ?>
        </div>
    </div>

    <!-- ── PANEL: SEMIFINALES ────────────────────────────────── -->
    <div class="step-panel" id="panel-sf">
        <div class="panel-title">Semifinales</div>
        <p class="panel-subtitle">Los equipos vienen de los ganadores de cuartos</p>
        <div class="progress-bar">
            <div class="progress-fill" id="prog-sf" style="width:<?= round($doneSF/2*100) ?>%"></div>
        </div>
        <div class="ko-list" id="list-sf">
        <?php foreach ($knockout['semiFinals'] as $match): ?>
        <?php
        $winner = $savedKO[$match['id']] ?? '';
        $hasPick = !empty($winner);
        $src1 = $match['from'][0]; $src2 = $match['from'][1];
        $c1 = $savedKO[$src1] ?? ''; $c2 = $savedKO[$src2] ?? '';
        $serranillo = ['name' => 'Serranillo en silla de ruedas', 'flag' => '♿'];
        $t1i = ($c1 === 'SERRANILLO') ? $serranillo : ($c1 && isset($teamLookup[$c1]) ? $teamLookup[$c1] : null);
        $t2i = ($c2 === 'SERRANILLO') ? $serranillo : ($c2 && isset($teamLookup[$c2]) ? $teamLookup[$c2] : null);
        $locked = !$t1i || !$t2i;
        ?>
        <button type="button"
            class="ko-match-btn <?= $hasPick ? 'has-pick' : '' ?> <?= $locked ? 'locked' : '' ?>"
            data-match="<?= $match['id'] ?>"
            data-round="sf"
            data-src1="<?= $src1 ?>" data-src2="<?= $src2 ?>"
            data-winner="<?= htmlspecialchars($winner) ?>"
            <?= $locked ? 'disabled' : '' ?>>
            <div class="ko-match-teams">
                <div class="ko-team-row <?= ($hasPick && $winner === $c1) ? 'is-winner' : '' ?>">
                    <span class="ko-team-flag"><?= $t1i ? $t1i['flag'] : '❓' ?></span>
                    <span class="ko-team-name"><?= $t1i ? htmlspecialchars($t1i['name']) : 'Ganador '.$src1 ?></span>
                </div>
                <div class="ko-vs">VS</div>
                <div class="ko-team-row <?= ($hasPick && $winner === $c2) ? 'is-winner' : '' ?>">
                    <span class="ko-team-flag"><?= $t2i ? $t2i['flag'] : '❓' ?></span>
                    <span class="ko-team-name"><?= $t2i ? htmlspecialchars($t2i['name']) : 'Ganador '.$src2 ?></span>
                </div>
            </div>
            <div class="ko-match-meta">
                <span class="ko-match-label"><?= $match['label'] ?></span>
                <span class="ko-arrow"><?= $hasPick ? '✓' : ($locked ? '🔒' : '›') ?></span>
            </div>
        </button>
        <?php endforeach; ?>
        </div>
    </div>

    <!-- ── PANEL: FINAL ──────────────────────────────────────── -->
    <div class="step-panel" id="panel-final">
        <div class="panel-title">La Gran Final 🏆</div>
        <p class="panel-subtitle">MetLife Stadium, Nueva York · El campeón del mundo</p>
        <div class="progress-bar">
            <div class="progress-fill" id="prog-final" style="width:<?= $doneF ? 100 : 0 ?>%"></div>
        </div>
        <div class="ko-list">
        <?php
        $sf1w = $savedKO['SF_1'] ?? '';
        $sf2w = $savedKO['SF_2'] ?? '';
        $serranillo = ['name' => 'Serranillo en silla de ruedas', 'flag' => '♿'];
        $ft1i = ($sf1w === 'SERRANILLO') ? $serranillo : ($sf1w && isset($teamLookup[$sf1w]) ? $teamLookup[$sf1w] : null);
        $ft2i = ($sf2w === 'SERRANILLO') ? $serranillo : ($sf2w && isset($teamLookup[$sf2w]) ? $teamLookup[$sf2w] : null);
        $fWinner  = $savedFinal['winner']   ?? '';
        $fRunnerup= $savedFinal['runner_up']?? '';
        $fLocked  = !$ft1i || !$ft2i;
        ?>
        <button type="button"
            class="ko-match-btn is-final <?= $fWinner ? 'has-pick' : '' ?> <?= $fLocked ? 'locked' : '' ?>"
            id="final-btn"
            data-match="FINAL"
            data-src1="SF_1" data-src2="SF_2"
            data-winner="<?= htmlspecialchars($fWinner) ?>"
            <?= $fLocked ? 'disabled' : '' ?>>
            <div class="ko-match-teams">
                <div class="ko-team-row <?= ($fWinner && $fWinner === $sf1w) ? 'is-winner' : '' ?>">
                    <span class="ko-team-flag"><?= $ft1i ? $ft1i['flag'] : '❓' ?></span>
                    <span class="ko-team-name"><?= $ft1i ? htmlspecialchars($ft1i['name']) : 'Ganador SF1' ?></span>
                </div>
                <div class="ko-vs">VS</div>
                <div class="ko-team-row <?= ($fWinner && $fWinner === $sf2w) ? 'is-winner' : '' ?>">
                    <span class="ko-team-flag"><?= $ft2i ? $ft2i['flag'] : '❓' ?></span>
                    <span class="ko-team-name"><?= $ft2i ? htmlspecialchars($ft2i['name']) : 'Ganador SF2' ?></span>
                </div>
            </div>
            <div class="ko-match-meta">
                <span class="ko-match-label">FINAL</span>
                <span class="ko-arrow" style="font-size:20px"><?= $fWinner ? '🏆' : ($fLocked ? '🔒' : '›') ?></span>
            </div>
        </button>
        </div>
        <?php if ($fWinner && isset($teamLookup[$fWinner])): ?>
        <div style="text-align:center; margin-top:32px; padding:24px; background:rgba(255,215,0,0.06); border:1px solid rgba(255,215,0,0.2); border-radius:16px;">
            <div style="font-size:48px; margin-bottom:8px;"><?= $teamLookup[$fWinner]['flag'] ?></div>
            <div style="font-family:var(--font-display);font-size:28px;font-weight:700;color:var(--gold-primary);letter-spacing:2px;"><?= htmlspecialchars($teamLookup[$fWinner]['name']) ?></div>
            <div style="font-size:13px;color:var(--text-dim);margin-top:6px;">Tu campeón del mundo · 40 pts</div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── PANEL: BONUS ──────────────────────────────────────── -->
    <div class="step-panel" id="panel-bonus">
        <div class="panel-title">Bonus</div>
        <p class="panel-subtitle">Dos predicciones extra con muchos puntos en juego</p>
        <div class="progress-bar">
            <div class="progress-fill" id="prog-bonus" style="width:<?= round($doneBonus/2*100) ?>%"></div>
        </div>
        <div class="bonus-cards">
            <div class="bonus-card <?= $savedTopScorer ? 'has-value' : '' ?>">
                <div class="bonus-card-header">
                    <span class="bonus-icon">🎯</span>
                    <span class="bonus-card-title">Equipo más goleador</span>
                    <span class="bonus-card-pts">5 pts</span>
                </div>
                <p class="bonus-card-desc">¿Qué selección marcará más goles en todo el torneo?</p>
                <input type="text" id="top_scorer_team" class="bonus-input"
                    placeholder="Ej: Francia, Brasil..."
                    value="<?= htmlspecialchars($savedTopScorer) ?>">
            </div>
            <div class="bonus-card <?= $savedPichichi ? 'has-value' : '' ?>">
                <div class="bonus-card-header">
                    <span class="bonus-icon">👟</span>
                    <span class="bonus-card-title">Pichichi</span>
                    <span class="bonus-card-pts">20 pts</span>
                </div>
                <p class="bonus-card-desc">¿Quién será el máximo goleador individual del torneo?</p>
                <input type="text" id="pichichi" class="bonus-input"
                    placeholder="Ej: Mbappé, Haaland..."
                    value="<?= htmlspecialchars($savedPichichi) ?>">
            </div>
        </div>
    </div>

</div><!-- .pred-page -->

<!-- SAVE BAR -->
<div class="save-bar">
    <button type="button" class="save-bar-btn" id="saveBtn">Guardar Porra</button>
    <div class="save-status" id="saveStatus"></div>
</div>

<!-- PICKER -->
<div class="picker-overlay" id="picker">
    <div class="picker-scrim" id="pickerScrim"></div>
    <div class="picker-sheet" id="pickerSheet">
        <div class="picker-handle"></div>
        <div class="picker-heading" id="pickerHeading"></div>
        <div class="picker-subheading" id="pickerSub"></div>
        <div class="picker-opts" id="pickerOpts"></div>
    </div>
</div>

<script>
// ── DATA FROM PHP ───────────────────────────────────────────────
const TEAM_LOOKUP = <?= json_encode($teamLookup) ?>;
const KO_BRACKET  = <?= json_encode($knockout) ?>;
const BASE_URL    = window.BASE_URL;

// ── CLIENT STATE (starts from PHP-rendered values) ──────────────
// Groups: { A: { first: "MEX", second: "RSA" }, ... }
const state = {
    groups: <?= json_encode((object)($savedGroups ?: new stdClass())) ?>,
    // KO flat: { matchId: "CODE" }
    knockout: <?= json_encode((object)($savedKO ?: new stdClass())) ?>,
    // Final: { winner: "CODE", runner_up: "CODE" }
    final: <?= json_encode((object)($savedFinal ?: new stdClass())) ?>,
    top_scorer_team: <?= json_encode($savedTopScorer) ?>,
    pichichi: <?= json_encode($savedPichichi) ?>
};

// ── HELPERS ─────────────────────────────────────────────────────
function teamInfo(code) {
    return code && TEAM_LOOKUP[code] ? TEAM_LOOKUP[code] : null;
}

function codeFromGroupPos(pos) {
    if (!pos || pos.includes('3rd')) return '';
    const m = pos.match(/^(\d)([A-L])$/);
    if (!m) return '';
    const grp = m[2], place = m[1];
    return place === '1'
        ? (state.groups[grp]?.first  || '')
        : (state.groups[grp]?.second || '');
}

// ── TABS ────────────────────────────────────────────────────────
document.querySelectorAll('.step-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.step-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById('panel-' + tab.dataset.panel).classList.add('active');
    });
});

// ── PICKER ──────────────────────────────────────────────────────
let pickerResolve = null;

function openPicker(heading, sub, opts) {
    return new Promise(resolve => {
        pickerResolve = resolve;
        document.getElementById('pickerHeading').textContent = heading;
        const subEl = document.getElementById('pickerSub');
        subEl.textContent = sub || '';
        subEl.style.display = sub ? 'block' : 'none';

        const container = document.getElementById('pickerOpts');
        container.innerHTML = '';

        opts.forEach(o => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'picker-opt' + (o.selected ? ' selected' : '') + (o.clear ? ' clear-opt' : '');
            btn.innerHTML = (o.flag ? `<span class="picker-opt-flag">${o.flag}</span>` : '')
                + `<span class="picker-opt-name">${o.label}</span>`
                + (o.selected ? '<span class="picker-opt-check">✓</span>' : '');
            btn.addEventListener('click', () => {
                const val = o.value;
                document.getElementById('picker').classList.remove('open');
                pickerResolve = null;
                resolve(val);
            });
            container.appendChild(btn);
        });

        document.getElementById('picker').classList.add('open');
    });
}

function closePicker() {
    document.getElementById('picker').classList.remove('open');
    pickerResolve = null;
}

document.getElementById('pickerScrim').addEventListener('click', closePicker);

// ── GROUP LOGIC ─────────────────────────────────────────────────
document.querySelectorAll('.pred-team-btn').forEach(btn => {
    btn.addEventListener('click', () => handleGroupTeamClick(btn));
});

async function handleGroupTeamClick(btn) {
    const grp  = btn.dataset.group;
    const code = btn.dataset.code;
    const flag = btn.dataset.flag;
    const name = btn.dataset.name;

    const cur1 = state.groups[grp]?.first  || '';
    const cur2 = state.groups[grp]?.second || '';

    const opts = [
        { value: 'first',  flag, label: `${name} — 1° clasificado`, selected: cur1 === code },
        { value: 'second', flag, label: `${name} — 2° clasificado`, selected: cur2 === code },
    ];
    if (cur1 || cur2) {
        opts.push({ value: 'clear', label: `Limpiar Grupo ${grp}`, clear: true });
    }

    const chosen = await openPicker(`Grupo ${grp}`, name, opts);
    if (chosen === null) return;

    if (chosen === 'clear') {
        delete state.groups[grp];
    } else {
        if (!state.groups[grp]) state.groups[grp] = {};
        // Remove this code from the other slot if already there
        if (state.groups[grp].first  === code && chosen !== 'first')  state.groups[grp].first  = '';
        if (state.groups[grp].second === code && chosen !== 'second') state.groups[grp].second = '';
        state.groups[grp][chosen] = code;
    }

    refreshGroupCard(grp);
    refreshAllR32Teams();
    updateTabCount('grupos',
        Object.values(state.groups).filter(g => g.first && g.second).length + '/12');
    updateProgress('prog-grupos',
        Object.values(state.groups).filter(g => g.first && g.second).length, 12);
}

function refreshGroupCard(grp) {
    const g     = state.groups[grp] || {};
    const first = g.first  || '';
    const second= g.second || '';
    const f1    = teamInfo(first);
    const f2    = teamInfo(second);

    // Header badges
    const sel = document.getElementById('gsel-' + grp);
    sel.innerHTML =
        (f1 ? `<span class="sel-badge pos-1">${f1.flag} ${f1.name}</span>`
             : `<span class="sel-badge empty">1° sin elegir</span>`)
      + (f2 ? `<span class="sel-badge pos-2">${f2.flag} ${f2.name}</span>`
             : `<span class="sel-badge empty">2° sin elegir</span>`);

    // Card border
    const card = document.getElementById('gcard-' + grp);
    card.classList.toggle('complete', !!(f1 && f2));

    // Team buttons
    card.querySelectorAll('.pred-team-btn').forEach(b => {
        const c = b.dataset.code;
        const isF = c === first, isS = c === second;
        b.className = 'pred-team-btn' + (isF ? ' is-first' : isS ? ' is-second' : '');
        b.querySelector('.team-btn-rank').className =
            'team-btn-rank ' + (isF ? 'r1' : isS ? 'r2' : 'r0');
        b.querySelector('.team-btn-rank').textContent =
            isF ? '1° CLASIFICADO' : isS ? '2° CLASIFICADO' : '·';
    });
}

// ── KO LOGIC ────────────────────────────────────────────────────
// R32 buttons have team positions baked in (from groups)
// We resolve them on click
document.querySelectorAll('#list-r32 .ko-match-btn').forEach(btn => {
    btn.addEventListener('click', () => handleR32Click(btn));
});

async function handleR32Click(btn) {
    if (btn.disabled) return;
    const matchId = btn.dataset.match;
    const pos1 = btn.dataset.t1pos;
    const pos2 = btn.dataset.t2pos;

    const c1 = codeFromGroupPos(pos1);
    const c2 = codeFromGroupPos(pos2);
    const is3rd1 = pos1 && pos1.includes('3rd');
    const is3rd2 = pos2 && pos2.includes('3rd');

    const t1 = is3rd1 ? { name: 'Serranillo en silla de ruedas', flag: '♿' } : teamInfo(c1);
    const t2 = is3rd2 ? { name: 'Serranillo en silla de ruedas', flag: '♿' } : teamInfo(c2);

    if (!t1 || !t2) {
        alert('Primero completa la fase de grupos para este partido');
        return;
    }

    const v1 = is3rd1 ? 'SERRANILLO' : c1;
    const v2 = is3rd2 ? 'SERRANILLO' : c2;

    const curWinner = state.knockout[matchId] || '';
    const opts = [
        { value: v1, flag: t1.flag, label: t1.name, selected: curWinner === v1 },
        { value: v2, flag: t2.flag, label: t2.name, selected: curWinner === v2 },
    ];
    if (curWinner) opts.push({ value: '', label: 'Quitar selección', clear: true });

    const chosen = await openPicker('¿Quién pasa?', btn.querySelector('.ko-match-label').textContent, opts);
    if (chosen === null) return;

    state.knockout[matchId] = chosen;
    refreshKOBtn(btn, matchId, v1, v2, t1, t2, chosen);
    refreshKOPanel('list-r16');
    updateTabCount('r32', Object.keys(state.knockout).filter(k => k.startsWith('R32') && state.knockout[k]).length + '/16');
    updateProgress('prog-r32', Object.keys(state.knockout).filter(k => k.startsWith('R32') && state.knockout[k]).length, 16);
}

function refreshKOBtn(btn, matchId, c1, c2, t1, t2, winner) {
    const rows = btn.querySelectorAll('.ko-team-row');
    const arrow = btn.querySelector('.ko-arrow');

    if (t1) {
        rows[0].querySelector('.ko-team-flag').textContent = t1.flag;
        rows[0].querySelector('.ko-team-name').textContent = t1.name;
    }
    if (t2) {
        rows[1].querySelector('.ko-team-flag').textContent = t2.flag;
        rows[1].querySelector('.ko-team-name').textContent = t2.name;
    }

    const isSerranillo = winner === 'SERRANILLO';
    rows[0].classList.toggle('is-winner', !!(winner && !isSerranillo && winner === c1));
    rows[1].classList.toggle('is-winner', !!(winner && !isSerranillo && winner === c2));
    btn.classList.toggle('has-pick', !!winner);
    arrow.textContent = winner ? '✓' : '›';
    btn.dataset.winner = winner;
}

function refreshKOPanel(listId) {
    const list = document.getElementById(listId);
    if (!list) return;
    list.querySelectorAll('.ko-match-btn').forEach(btn => {
        const src1 = btn.dataset.src1;
        const src2 = btn.dataset.src2;
        const c1 = state.knockout[src1] || '';
        const c2 = state.knockout[src2] || '';
        const t1 = (c1 === 'SERRANILLO') ? { name: 'Serranillo en silla de ruedas', flag: '♿' } : teamInfo(c1);
        const t2 = (c2 === 'SERRANILLO') ? { name: 'Serranillo en silla de ruedas', flag: '♿' } : teamInfo(c2);
        const winner = btn.dataset.winner || '';
        const locked = !t1 || !t2;
        const rows = btn.querySelectorAll('.ko-team-row');

        if (t1) {
            rows[0].querySelector('.ko-team-flag').textContent = t1.flag;
            rows[0].querySelector('.ko-team-name').textContent = t1.name;
        } else {
            rows[0].querySelector('.ko-team-flag').textContent = '❓';
            rows[0].querySelector('.ko-team-name').textContent = 'Ganador ' + src1;
        }
        if (t2) {
            rows[1].querySelector('.ko-team-flag').textContent = t2.flag;
            rows[1].querySelector('.ko-team-name').textContent = t2.name;
        } else {
            rows[1].querySelector('.ko-team-flag').textContent = '❓';
            rows[1].querySelector('.ko-team-name').textContent = 'Ganador ' + src2;
        }

        rows[0].classList.toggle('is-winner', !!(winner && winner === c1));
        rows[1].classList.toggle('is-winner', !!(winner && winner === c2));

        btn.disabled = locked;
        if (locked) {
            btn.classList.add('locked');
            btn.querySelector('.ko-arrow').textContent = '🔒';
        } else {
            btn.classList.remove('locked');
            btn.querySelector('.ko-arrow').textContent = winner ? '✓' : '›';
        }
    });
}

function refreshFinalButton() {
    const btn = document.getElementById('final-btn');
    if (!btn) return;
    const sf1w = state.knockout['SF_1'] || '';
    const sf2w = state.knockout['SF_2'] || '';
    const t1 = (sf1w === 'SERRANILLO') ? { name: 'Serranillo en silla de ruedas', flag: '♿' } : teamInfo(sf1w);
    const t2 = (sf2w === 'SERRANILLO') ? { name: 'Serranillo en silla de ruedas', flag: '♿' } : teamInfo(sf2w);
    const winner = state.final.winner || '';
    const locked = !t1 || !t2;
    const rows = btn.querySelectorAll('.ko-team-row');

    if (t1) {
        rows[0].querySelector('.ko-team-flag').textContent = t1.flag;
        rows[0].querySelector('.ko-team-name').textContent = t1.name;
    } else {
        rows[0].querySelector('.ko-team-flag').textContent = '❓';
        rows[0].querySelector('.ko-team-name').textContent = 'Ganador SF1';
    }
    if (t2) {
        rows[1].querySelector('.ko-team-flag').textContent = t2.flag;
        rows[1].querySelector('.ko-team-name').textContent = t2.name;
    } else {
        rows[1].querySelector('.ko-team-flag').textContent = '❓';
        rows[1].querySelector('.ko-team-name').textContent = 'Ganador SF2';
    }

    rows[0].classList.toggle('is-winner', !!(winner && winner === sf1w));
    rows[1].classList.toggle('is-winner', !!(winner && winner === sf2w));

    btn.disabled = locked;
    if (locked) {
        btn.classList.add('locked');
        btn.querySelector('.ko-arrow').textContent = '🔒';
    } else {
        btn.classList.remove('locked');
        btn.querySelector('.ko-arrow').textContent = winner ? '🏆' : '›';
    }
}

// Generic KO handler for R16, QF, SF
function wireKOPanel(listId, round, total, tabId, progId) {
    document.querySelectorAll(`#${listId} .ko-match-btn`).forEach(btn => {
        btn.addEventListener('click', () => handleKOClick(btn, round, total, tabId, progId));
    });
}

async function handleKOClick(btn, round, total, tabId, progId) {
    if (btn.disabled) return;
    const matchId = btn.dataset.match;
    const src1    = btn.dataset.src1;
    const src2    = btn.dataset.src2;
    const c1      = state.knockout[src1] || '';
    const c2      = state.knockout[src2] || '';
    const t1      = (c1 === 'SERRANILLO') ? { name: 'Serranillo en silla de ruedas', flag: '♿' } : teamInfo(c1);
    const t2      = (c2 === 'SERRANILLO') ? { name: 'Serranillo en silla de ruedas', flag: '♿' } : teamInfo(c2);

    if (!t1 || !t2) return;

    const isFinal  = matchId === 'FINAL';
    const curWinner= isFinal ? (state.final.winner || '') : (state.knockout[matchId] || '');

    const opts = [
        { value: c1, flag: t1.flag, label: t1.name, selected: curWinner === c1 },
        { value: c2, flag: t2.flag, label: t2.name, selected: curWinner === c2 },
    ];
    if (curWinner) opts.push({ value: '', label: 'Quitar selección', clear: true });

    const label = btn.querySelector('.ko-match-label')?.textContent || matchId;
    const chosen = await openPicker(isFinal ? '🏆 ¿Quién gana el Mundial?' : '¿Quién pasa?', label, opts);
    if (chosen === null) return;

    if (isFinal) {
        state.final.winner    = chosen;
        state.final.runner_up = chosen ? (chosen === c1 ? c2 : c1) : '';
        refreshFinalPanel(c1, c2, t1, t2, chosen);
        updateTabCount('final', chosen ? '1/1' : '0/1');
        updateProgress('prog-final', chosen ? 1 : 0, 1);
    } else {
        state.knockout[matchId] = chosen;
        const rows  = btn.querySelectorAll('.ko-team-row');
        const arrow = btn.querySelector('.ko-arrow');
        rows[0].classList.toggle('is-winner', !!(chosen && chosen === c1));
        rows[1].classList.toggle('is-winner', !!(chosen && chosen === c2));
        btn.classList.toggle('has-pick', !!chosen);
        arrow.textContent = chosen ? '✓' : '›';
        btn.dataset.winner = chosen;

        const prefix = { r16:'R16', qf:'QF', sf:'SF' }[round] || round.toUpperCase();
        const done = Object.keys(state.knockout).filter(k => k.startsWith(prefix) && state.knockout[k]).length;
        updateTabCount(tabId, done + '/' + total);
        updateProgress(progId, done, total);

        if (round === 'r16') refreshKOPanel('list-qf');
        else if (round === 'qf') refreshKOPanel('list-sf');
        else if (round === 'sf') refreshFinalButton();
    }
}

function refreshFinalPanel(c1, c2, t1, t2, winner) {
    const btn   = document.getElementById('final-btn');
    if (!btn) return;
    const rows  = btn.querySelectorAll('.ko-team-row');
    const arrow = btn.querySelector('.ko-arrow');
    rows[0].querySelector('.ko-team-flag').textContent = t1.flag;
    rows[0].querySelector('.ko-team-name').textContent = t1.name;
    rows[1].querySelector('.ko-team-flag').textContent = t2.flag;
    rows[1].querySelector('.ko-team-name').textContent = t2.name;
    rows[0].classList.toggle('is-winner', winner === c1);
    rows[1].classList.toggle('is-winner', winner === c2);
    btn.classList.toggle('has-pick', !!winner);
    arrow.textContent = winner ? '🏆' : '›';
    btn.dataset.winner = winner;
    btn.disabled = false;
    btn.classList.remove('locked');
}

wireKOPanel('list-r16', 'r16', 8,  'r16', 'prog-r16');
wireKOPanel('list-qf',  'qf',  4,  'qf',  'prog-qf');
wireKOPanel('list-sf',  'sf',  2,  'sf',  'prog-sf');

document.getElementById('final-btn')?.addEventListener('click', function() {
    handleKOClick(this, 'final', 1, 'final', 'prog-final');
});

// ── BONUS ───────────────────────────────────────────────────────
['top_scorer_team','pichichi'].forEach(id => {
    document.getElementById(id).addEventListener('input', function() {
        state[id] = this.value;
        this.closest('.bonus-card').classList.toggle('has-value', !!this.value.trim());
        const done = (state.top_scorer_team.trim() ? 1 : 0) + (state.pichichi.trim() ? 1 : 0);
        updateTabCount('bonus', done + '/2');
        updateProgress('prog-bonus', done, 2);
    });
});

// ── UI HELPERS ──────────────────────────────────────────────────
function updateTabCount(panel, text) {
    const el = document.getElementById('tab-count-' + panel);
    if (el) el.textContent = text;
}

function updateProgress(progId, done, total) {
    const el = document.getElementById(progId);
    if (el) el.style.width = Math.round(done / total * 100) + '%';
}

// ── SAVE ────────────────────────────────────────────────────────
document.getElementById('saveBtn').addEventListener('click', async () => {
    const btn = document.getElementById('saveBtn');
    const status = document.getElementById('saveStatus');
    btn.disabled = true;
    btn.textContent = 'Guardando...';
    status.textContent = '';
    status.className = 'save-status';

    // Build payload: convert flat knockout back to { matchId: { winner: CODE } }
    const koPayload = {};
    for (const [k, v] of Object.entries(state.knockout)) {
        if (v) koPayload[k] = { winner: v };
    }

    const payload = {
        groups:          state.groups,
        knockout:        koPayload,
        final:           state.final,
        top_scorer_team: document.getElementById('top_scorer_team').value.trim(),
        pichichi:        document.getElementById('pichichi').value.trim()
    };

    try {
        const res = await fetch(BASE_URL + '/api/predictions.php?action=save', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            status.textContent = '✓ Guardado correctamente';
            status.className = 'save-status';
        } else {
            status.textContent = '✗ ' + (data.error || 'Error al guardar');
            status.className = 'save-status error';
        }
    } catch (e) {
        status.textContent = '✗ Error de conexión';
        status.className = 'save-status error';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Guardar Porra';
        setTimeout(() => { status.textContent = ''; }, 3000);
    }
});

// ── R32 TEAM RESOLUTION ──────────────────────────────────────────
function refreshAllR32Teams() {
    document.querySelectorAll('#list-r32 .ko-match-btn').forEach(btn => {
        const pos1 = btn.dataset.t1pos;
        const pos2 = btn.dataset.t2pos;
        const c1 = codeFromGroupPos(pos1);
        const c2 = codeFromGroupPos(pos2);
        const t1 = c1 ? teamInfo(c1) : null;
        const t2 = c2 ? teamInfo(c2) : null;
        const rows = btn.querySelectorAll('.ko-team-row');
        const winner = btn.dataset.winner;
        const is3rd1 = pos1 && pos1.includes('3rd');
        const is3rd2 = pos2 && pos2.includes('3rd');

        if (is3rd1) {
            rows[0].querySelector('[data-flag-t1]').textContent = '♿';
            rows[0].querySelector('[data-name-t1]').textContent = 'Serranillo en silla de ruedas';
        } else if (t1) {
            rows[0].querySelector('[data-flag-t1]').textContent = t1.flag;
            rows[0].querySelector('[data-name-t1]').textContent = t1.name;
        } else {
            rows[0].querySelector('[data-flag-t1]').textContent = '';
            rows[0].querySelector('[data-name-t1]').textContent = pos1 || '❓';
        }

        if (is3rd2) {
            rows[1].querySelector('[data-flag-t2]').textContent = '♿';
            rows[1].querySelector('[data-name-t2]').textContent = 'Serranillo en silla de ruedas';
        } else if (t2) {
            rows[1].querySelector('[data-flag-t2]').textContent = t2.flag;
            rows[1].querySelector('[data-name-t2]').textContent = t2.name;
        } else {
            rows[1].querySelector('[data-flag-t2]').textContent = '';
            rows[1].querySelector('[data-name-t2]').textContent = pos2 || '❓';
        }

        if (winner) {
            const isSerranillo = winner === 'SERRANILLO';
            rows[0].classList.toggle('is-winner', !isSerranillo && winner === c1);
            rows[1].classList.toggle('is-winner', !isSerranillo && winner === c2);
        }

        const team1Ready = is3rd1 || !!t1;
        const team2Ready = is3rd2 || !!t2;
        const locked = !team1Ready || !team2Ready;
        btn.disabled = locked;
        if (locked) {
            btn.classList.add('locked');
            btn.querySelector('.ko-arrow').textContent = '🔒';
        } else {
            btn.classList.remove('locked');
            btn.querySelector('.ko-arrow').textContent = winner ? '✓' : '›';
        }
    });
}

refreshAllR32Teams();

document.querySelector('[data-panel="r32"]').addEventListener('click', () => {
    setTimeout(refreshAllR32Teams, 10);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>