<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
require_once __DIR__ . '/../includes/header.php';

$teams = getTeams();
$groups = $teams['groups'];
$knockout = $teams['knockout'];
$currentUser = getCurrentUser();
$predictions = getPredictions();
$userPred = $predictions[$currentUser['id']] ?? [];

$teamLookup = [];
foreach ($groups as $group) {
    foreach ($group['teams'] as $t) {
        $teamLookup[$t['code']] = ['name' => $t['name'], 'flag' => $t['flag']];
    }
}

$allPredictions = $userPred;
?>
<main class="page-content page-content-with-savebar">
    <div class="page-header">
        <h1 class="page-title">MI PORRA</h1>
        <p class="page-subtitle">Completa tus predicciones · Guarda cuando quieras</p>
    </div>

    <!-- GROUPS -->
    <section class="pred-section">
        <h2 class="section-title"><span class="icon">🏆</span> Grupos</h2>
        <div class="groups-grid">
            <?php foreach ($groups as $letter => $group): ?>
            <?php
            $first = $userPred['groups'][$letter]['first'] ?? '';
            $second = $userPred['groups'][$letter]['second'] ?? '';
            $firstInfo = $first && isset($teamLookup[$first]) ? $teamLookup[$first] : null;
            $secondInfo = $second && isset($teamLookup[$second]) ? $teamLookup[$second] : null;
            ?>
            <div class="group-card" data-group="<?= $letter ?>">
                <div class="group-card-header">
                    <span class="group-card-letter"><?= $letter ?></span>
                    <div class="group-slots">
                        <div class="slot" id="slot-<?= $letter ?>-1">
                            <?php if ($firstInfo): ?>
                            <span class="slot-flag"><?= $firstInfo['flag'] ?></span>
                            <span class="slot-name"><?= $firstInfo['name'] ?></span>
                            <?php else: ?>
                            <span class="slot-placeholder">1°</span>
                            <?php endif; ?>
                        </div>
                        <div class="slot-divider">/</div>
                        <div class="slot" id="slot-<?= $letter ?>-2">
                            <?php if ($secondInfo): ?>
                            <span class="slot-flag"><?= $secondInfo['flag'] ?></span>
                            <span class="slot-name"><?= $secondInfo['name'] ?></span>
                            <?php else: ?>
                            <span class="slot-placeholder">2°</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="group-card-teams">
                    <?php foreach ($group['teams'] as $team): ?>
                    <button type="button" class="team-chip <?= $first === $team['code'] ? 'is-first' : '' ?> <?= $second === $team['code'] ? 'is-second' : '' ?>"
                        data-code="<?= $team['code'] ?>"
                        data-group="<?= $letter ?>">
                        <span class="chip-flag"><?= $team['flag'] ?></span>
                        <span class="chip-name"><?= $team['name'] ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ELIMINATORIAS -->
    <section class="pred-section">
        <h2 class="section-title"><span class="icon">⚽</span> Eliminatorias</h2>

        <h3 class="subsection-title">Dieciseisavos</h3>
        <div class="ko-grid" id="r32-grid"></div>

        <h3 class="subsection-title">Octavos</h3>
        <div class="ko-grid" id="r16-grid"></div>

        <h3 class="subsection-title">Cuartos</h3>
        <div class="ko-grid" id="qf-grid"></div>

        <h3 class="subsection-title">Semifinales</h3>
        <div class="ko-grid" id="sf-grid"></div>

        <h3 class="subsection-title">Final</h3>
        <div class="ko-grid" id="final-grid"></div>
    </section>

    <!-- BONUS -->
    <section class="pred-section">
        <h2 class="section-title"><span class="icon">⭐</span> Bonus</h2>
        <div class="bonus-row">
            <input type="text" name="top_scorer_team" placeholder="Equipo más goleador" value="<?= htmlspecialchars($userPred['top_scorer_team'] ?? '') ?>">
            <input type="text" name="pichichi" placeholder="Pichichi (jugador)" value="<?= htmlspecialchars($userPred['pichichi'] ?? '') ?>">
        </div>
    </section>
</main>

<!-- STICKY SAVE BAR -->
<div class="save-bar-fixed">
    <button type="button" id="saveBtn" class="btn btn-primary btn-full">Guardar Porra</button>
    <div class="save-feedback" id="saveFeedback"></div>
</div>

<!-- PICKER MODAL -->
<div id="pickerModal" class="picker-modal">
    <div class="picker-backdrop" id="pickerBackdrop"></div>
    <div class="picker-sheet">
        <div class="picker-handle"></div>
        <div class="picker-title" id="pickerTitle">Elige</div>
        <div class="picker-options" id="pickerOptions"></div>
    </div>
</div>

<script>
const teamLookup = <?= json_encode($teamLookup) ?>;
const BASE_URL = window.BASE_URL;
const knockoutBracket = <?= json_encode($knockout) ?>;
const savedPred = <?= json_encode($allPredictions) ?>;

// Build reverse lookup: name -> code
const teamByName = {};
for (const [code, info] of Object.entries(teamLookup)) {
    teamByName[info.name] = code;
}

// State
console.log('[LOAD] savedPred:', JSON.stringify(savedPred));
let groups = JSON.parse(JSON.stringify(savedPred.groups || {}));
let knockout = JSON.parse(JSON.stringify(savedPred.knockout || {}));
let final = JSON.parse(JSON.stringify((savedPred.final && typeof savedPred.final === 'object') ? savedPred.final : {}));

// Debounce save
let saveTimeout = null;

function scheduleSave() {
    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(() => {
        if (!final || typeof final !== 'object') final = {};
        const data = {
            groups: groups,
            knockout: knockout,
            final: final,
            top_scorer_team: document.querySelector('input[name="top_scorer_team"]').value,
            pichichi: document.querySelector('input[name="pichichi"]').value
        };
        console.log('[SAVE] Sending data:', JSON.stringify(data));
        fetch(BASE_URL + '/api/predictions.php?action=save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        }).then(res => res.json()).then(result => {
            console.log('[SAVE] Result:', result);
            if (result.success) {
                const fb = document.getElementById('saveFeedback');
                fb.textContent = '✓';
                fb.className = 'save-feedback success';
                setTimeout(() => { fb.textContent = ''; }, 1500);
            }
        }).catch(err => {
            console.error('[SAVE] Error:', err);
        });
    }, 800);
}

function getTeamFromPosition(pos) {
    if (pos.includes('3rd')) return null;
    const m = pos.match(/^(\d)([A-L])$/);
    if (!m) return null;
    const place = parseInt(m[1]);
    const grp = m[2];
    const code = place === 1
        ? (groups[grp]?.first || '')
        : (groups[grp]?.second || '');
    return code && teamLookup[code] ? teamLookup[code] : null;
}

function getWinnerCode(matchId) {
    return knockout[matchId] || '';
}

function renderKOGrid(containerId, matches, getTeamsFn) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = '';

    matches.forEach(match => {
        const teams = getTeamsFn(match);
        const winnerCode = getWinnerCode(match.id);
        const t1 = teams[0];
        const t2 = teams[1];
        const t1Name = t1 ? t1.name : '?';
        const t1Flag = t1 ? t1.flag : '';
        const t2Name = t2 ? t2.name : '?';
        const t2Flag = t2 ? t2.flag : '';
        const winnerT1 = winnerCode && t1 && (winnerCode === teamByName[t1Name]);
        const winnerT2 = winnerCode && t2 && (winnerCode === teamByName[t2Name]);

        const card = document.createElement('button');
        card.className = 'ko-card' + (winnerCode ? ' has-winner' : '') + (!t1 || !t2 ? ' incomplete' : '');
        card.dataset.match = match.id;
        card.innerHTML =
            '<div class="ko-card-label">' + match.label + ' · ' + match.venue + '</div>' +
            '<div class="ko-card-match">' +
                '<div class="ko-option' + (winnerT1 ? ' winner' : '') + '" data-team="' + t1Name + '">' +
                    '<span class="ko-flag">' + t1Flag + '</span>' +
                    '<span class="ko-name">' + t1Name + '</span>' +
                '</div>' +
                '<div class="ko-sep">VS</div>' +
                '<div class="ko-option' + (winnerT2 ? ' winner' : '') + '" data-team="' + t2Name + '">' +
                    '<span class="ko-flag">' + t2Flag + '</span>' +
                    '<span class="ko-name">' + t2Name + '</span>' +
                '</div>' +
            '</div>';
        card.addEventListener('click', () => handleKoClick(card));
        container.appendChild(card);
    });
}

function getR32Teams(match) {
    return [getTeamFromPosition(match.team1), getTeamFromPosition(match.team2)];
}

function getR16Teams(match) {
    const c1 = knockout[match.from[0]] || '';
    const c2 = knockout[match.from[1]] || '';
    return [c1 ? (teamLookup[c1] || null) : null, c2 ? (teamLookup[c2] || null) : null];
}

function getQFTeams(match) { return getR16Teams(match); }
function getSFTeams(match) { return getR16Teams(match); }

function getFinalTeams() {
    const c1 = knockout['SF_1'] || '';
    const c2 = knockout['SF_2'] || '';
    return [c1 ? (teamLookup[c1] || null) : null, c2 ? (teamLookup[c2] || null) : null];
}

function renderAll() {
    renderKOGrid('r32-grid', knockoutBracket.roundOf32, getR32Teams);
    renderKOGrid('r16-grid', knockoutBracket.roundOf16, getR16Teams);
    renderKOGrid('qf-grid', knockoutBracket.quarterFinals, getQFTeams);
    renderKOGrid('sf-grid', knockoutBracket.semiFinals, getSFTeams);

    const fc = document.getElementById('final-grid');
    if (fc) {
        const teams = getFinalTeams();
        const winnerCode = final.winner || '';
        const t1 = teams[0];
        const t2 = teams[1];
        const winnerT1 = winnerCode && t1 && (winnerCode === teamByName[t1?.name]);
        const winnerT2 = winnerCode && t2 && (winnerCode === teamByName[t2?.name]);
        fc.innerHTML =
            '<button class="ko-card ko-final' + (winnerCode ? ' has-winner' : '') + (!t1 || !t2 ? ' incomplete' : '') + '" data-match="FINAL">' +
                '<div class="ko-card-label">🏆 Final · MetLife Stadium</div>' +
                '<div class="ko-card-match">' +
                    '<div class="ko-option' + (winnerT1 ? ' winner' : '') + '" data-team="' + (t1?.name || '') + '">' +
                        '<span class="ko-flag">' + (t1?.flag || '') + '</span>' +
                        '<span class="ko-name">' + (t1?.name || '?') + '</span>' +
                    '</div>' +
                    '<div class="ko-sep">VS</div>' +
                    '<div class="ko-option' + (winnerT2 ? ' winner' : '') + '" data-team="' + (t2?.name || '') + '">' +
                        '<span class="ko-flag">' + (t2?.flag || '') + '</span>' +
                        '<span class="ko-name">' + (t2?.name || '?') + '</span>' +
                    '</div>' +
                '</div>' +
            '</button>';
        fc.querySelector('.ko-card').addEventListener('click', () => handleKoClick(fc.querySelector('.ko-card')));
    }
}

renderAll();

// PICKER MODAL
function openPicker(title, options) {
    return new Promise((resolve) => {
        document.getElementById('pickerTitle').textContent = title;
        document.getElementById('pickerOptions').innerHTML = options.map(o =>
            '<button class="picker-btn" data-value="' + o.value + '" data-name="' + o.label + '">' +
                (o.flag ? '<span class="picker-flag">' + o.flag + '</span>' : '') +
                '<span class="picker-name">' + o.label + '</span>' +
            '</button>'
        ).join('');

        document.querySelectorAll('.picker-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('pickerModal').classList.remove('open');
                resolve({ value: btn.dataset.value, name: btn.dataset.name });
            });
        });

        document.getElementById('pickerModal').classList.add('open');
    });
}

document.getElementById('pickerBackdrop').addEventListener('click', () => {
    document.getElementById('pickerModal').classList.remove('open');
});

// GROUPS
document.querySelectorAll('.team-chip').forEach(btn => {
    btn.addEventListener('click', () => handleGroupClick(btn));
});

async function handleGroupClick(btn) {
    const code = btn.dataset.code;
    const group = btn.dataset.group;
    const info = teamLookup[code];
    const current1 = groups[group]?.first || '';
    const current2 = groups[group]?.second || '';

    const options = [];
    options.push({ value: code + '|first', label: info.name, flag: info.flag, pos: '1°' });
    options.push({ value: code + '|second', label: info.name, flag: info.flag, pos: '2°' });
    if (current1 || current2) {
        options.push({ value: 'clear', label: 'Limpiar', flag: '' });
    }

    const mappedOptions = options.map(o => ({
        value: o.value,
        label: o.pos ? o.label + ' — ' + o.pos : o.label,
        flag: o.flag
    }));

    const chosen = await openPicker(info.name, mappedOptions);
    if (!chosen) return;

    if (chosen.value === 'clear') {
        delete groups[group];
    } else {
        const [teamCode, pos] = chosen.value.split('|');
        if (!groups[group]) groups[group] = {};
        groups[group][pos] = teamCode;
        if (groups[group].first === groups[group].second) {
            groups[group][pos === 'first' ? 'second' : 'first'] = '';
        }
    }

    updateGroupUI(group);
    renderAll();
    scheduleSave();
}

function updateGroupUI(group) {
    const g = groups[group] || {};
    const first = g.first || '';
    const second = g.second || '';

    const s1 = document.getElementById('slot-' + group + '-1');
    const s2 = document.getElementById('slot-' + group + '-2');

    if (first && teamLookup[first]) {
        s1.innerHTML = '<span class="slot-flag">' + teamLookup[first].flag + '</span><span class="slot-name">' + teamLookup[first].name + '</span>';
    } else {
        s1.innerHTML = '<span class="slot-placeholder">1°</span>';
    }
    if (second && teamLookup[second]) {
        s2.innerHTML = '<span class="slot-flag">' + teamLookup[second].flag + '</span><span class="slot-name">' + teamLookup[second].name + '</span>';
    } else {
        s2.innerHTML = '<span class="slot-placeholder">2°</span>';
    }

    document.querySelectorAll('.group-card[data-group="' + group + '"] .team-chip').forEach(chip => {
        chip.classList.remove('is-first', 'is-second');
        if (chip.dataset.code === first) chip.classList.add('is-first');
        if (chip.dataset.code === second) chip.classList.add('is-second');
    });
}

// KNOCKOUT
document.querySelectorAll('.ko-card').forEach(card => {
    card.addEventListener('click', () => handleKoClick(card));
});

async function handleKoClick(card) {
    const match = card.dataset.match;
    const opts = card.querySelectorAll('.ko-option');
    if (opts.length < 2) return;

    const t1Name = opts[0].dataset.team;
    const t2Name = opts[1].dataset.team;
    const t1Code = teamByName[t1Name] || '';
    const t2Code = teamByName[t2Name] || '';

    const pickerOpts = [];
    if (t1Code) pickerOpts.push({ value: t1Code, label: t1Name, flag: teamLookup[t1Code]?.flag || '' });
    if (t2Code) pickerOpts.push({ value: t2Code, label: t2Name, flag: teamLookup[t2Code]?.flag || '' });

    const currentWinner = match === 'FINAL' ? final.winner : (knockout[match] || '');
    if (currentWinner) pickerOpts.push({ value: '', label: 'Limpiar', flag: '' });

    const chosen = await openPicker(match === 'FINAL' ? '🏆 Campeón' : '¿Quién pasa?', pickerOpts);
    if (!chosen) return;

    if (match === 'FINAL') {
        final.winner = chosen.value;
    } else {
        knockout[match] = chosen.value;
    }

    renderAll();
    scheduleSave();
}

// SAVE BUTTON
document.getElementById('saveBtn').addEventListener('click', () => {
    const fb = document.getElementById('saveFeedback');
    fb.textContent = '';
    fb.className = 'save-feedback';
    if (!final || typeof final !== 'object') final = {};

    const data = {
        groups: groups,
        knockout: knockout,
        final: final,
        top_scorer_team: document.querySelector('input[name="top_scorer_team"]').value,
        pichichi: document.querySelector('input[name="pichichi"]').value
    };

    fetch(BASE_URL + '/api/predictions.php?action=save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    }).then(res => res.json()).then(result => {
        if (result.success) {
            fb.textContent = '✓ Guardado';
            fb.className = 'save-feedback success';
            setTimeout(() => { fb.textContent = ''; }, 2000);
        } else {
            fb.textContent = result.error;
            fb.className = 'save-feedback error';
        }
    }).catch(() => {
        fb.textContent = 'Error de conexión';
        fb.className = 'save-feedback error';
    });
});

// Bonus auto-save
document.querySelectorAll('.bonus-row input').forEach(input => {
    input.addEventListener('input', scheduleSave);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>