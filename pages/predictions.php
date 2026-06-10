<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
require_once __DIR__ . '/../includes/header.php';

$teams = getTeams();
$groups = $teams['groups'];
$currentUser = getCurrentUser();
$predictions = getPredictions();
$userPred = $predictions[$currentUser['id']] ?? [];
?>
<main class="page-content">
    <h1 class="page-title">Mi Porra</h1>
    <p class="page-subtitle">Rellena tus predicciones. Puedes cambiarlas cuando quieras.</p>

    <form id="predictionsForm" class="predictions-form">
        <section class="pred-section">
            <h2 class="section-title">&#127942; Fase de Grupos</h2>
            <p class="section-desc">Elige 1&ordm; y 2&ordm; de cada grupo</p>

            <?php foreach ($groups as $letter => $group): ?>
            <div class="pred-group">
                <div class="pred-group-header">
                    <span class="pred-group-letter"><?= $letter ?></span>
                    <?php foreach ($group['teams'] as $team): ?>
                    <span class="pred-team-chip"><?= $team['flag'] ?> <?= $team['name'] ?></span>
                    <?php endforeach; ?>
                </div>
                <div class="pred-group-fields">
                    <div class="pred-field">
                        <label>1&ordm; puesto</label>
                        <select name="groups[<?= $letter ?>][first]" class="pred-select">
                            <option value="">--</option>
                            <?php foreach ($group['teams'] as $team): ?>
                            <option value="<?= $team['code'] ?>" <?= ($userPred['groups'][$letter]['first'] ?? '') === $team['code'] ? 'selected' : '' ?>>
                                <?= $team['flag'] ?> <?= $team['name'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pred-field">
                        <label>2&ordm; puesto</label>
                        <select name="groups[<?= $letter ?>][second]" class="pred-select">
                            <option value="">--</option>
                            <?php foreach ($group['teams'] as $team): ?>
                            <option value="<?= $team['code'] ?>" <?= ($userPred['groups'][$letter]['second'] ?? '') === $team['code'] ? 'selected' : '' ?>>
                                <?= $team['flag'] ?> <?= $team['name'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </section>

        <section class="pred-section">
            <h2 class="section-title">&#9876; Cruces (Dieciseisavos)</h2>
            <p class="section-desc">Elige qui&eacute;n pasa en cada partido</p>

            <?php foreach ($teams['knockout']['roundOf32'] as $match): ?>
            <div class="pred-match">
                <div class="pred-match-label"><?= $match['label'] ?> &middot; <?= $match['venue'] ?></div>
                <div class="pred-match-teams">
                    <span class="pred-team-option"><?= $match['team1'] ?></span>
                    <span class="pred-vs">vs</span>
                    <span class="pred-team-option"><?= $match['team2'] ?></span>
                </div>
                <div class="pred-field">
                    <label>Qui&eacute;n pasa</label>
                    <select name="knockout[<?= $match['id'] ?>]" class="pred-select">
                        <option value="">--</option>
                        <option value="<?= $match['team1'] ?>" <?= ($userPred['knockout'][$match['id']] ?? '') === $match['team1'] ? 'selected' : '' ?>><?= $match['team1'] ?></option>
                        <option value="<?= $match['team2'] ?>" <?= ($userPred['knockout'][$match['id']] ?? '') === $match['team2'] ? 'selected' : '' ?>><?= $match['team2'] ?></option>
                    </select>
                </div>
            </div>
            <?php endforeach; ?>
        </section>

        <section class="pred-section">
            <h2 class="section-title">&#127942; Rondas Finales</h2>
            <p class="section-desc">Octavos, cuartos, semis y final</p>

            <h3 class="subsection-title">Octavos de Final</h3>
            <?php foreach ($teams['knockout']['roundOf16'] as $match): ?>
            <div class="pred-match">
                <div class="pred-match-label"><?= $match['label'] ?> &middot; <?= $match['venue'] ?></div>
                <div class="pred-field">
                    <label>Qui&eacute;n pasa</label>
                    <input type="text" name="knockout[<?= $match['id'] ?>]" class="pred-input" placeholder="Escribe el equipo" value="<?= htmlspecialchars($userPred['knockout'][$match['id']] ?? '') ?>">
                </div>
            </div>
            <?php endforeach; ?>

            <h3 class="subsection-title">Cuartos de Final</h3>
            <?php foreach ($teams['knockout']['quarterFinals'] as $match): ?>
            <div class="pred-match">
                <div class="pred-match-label"><?= $match['label'] ?> &middot; <?= $match['venue'] ?></div>
                <div class="pred-field">
                    <label>Qui&eacute;n pasa</label>
                    <input type="text" name="knockout[<?= $match['id'] ?>]" class="pred-input" placeholder="Escribe el equipo" value="<?= htmlspecialchars($userPred['knockout'][$match['id']] ?? '') ?>">
                </div>
            </div>
            <?php endforeach; ?>

            <h3 class="subsection-title">Semifinales</h3>
            <?php foreach ($teams['knockout']['semiFinals'] as $match): ?>
            <div class="pred-match">
                <div class="pred-match-label"><?= $match['label'] ?> &middot; <?= $match['venue'] ?></div>
                <div class="pred-field">
                    <label>Qui&eacute;n pasa</label>
                    <input type="text" name="knockout[<?= $match['id'] ?>]" class="pred-input" placeholder="Escribe el equipo" value="<?= htmlspecialchars($userPred['knockout'][$match['id']] ?? '') ?>">
                </div>
            </div>
            <?php endforeach; ?>

            <h3 class="subsection-title">&#127942; Final</h3>
            <div class="pred-match pred-final">
                <div class="pred-field">
                    <label>Finalista (subcampe&oacute;n)</label>
                    <input type="text" name="final[runner_up]" class="pred-input" placeholder="El que pierde la final" value="<?= htmlspecialchars($userPred['final']['runner_up'] ?? '') ?>">
                </div>
                <div class="pred-field">
                    <label>&#127942; Ganador del Mundial</label>
                    <input type="text" name="final[winner]" class="pred-input pred-champion" placeholder="El campe&oacute;n" value="<?= htmlspecialchars($userPred['final']['winner'] ?? '') ?>">
                </div>
            </div>
        </section>

        <section class="pred-section">
            <h2 class="section-title">&#127919; Bonus</h2>

            <div class="pred-match">
                <div class="pred-field">
                    <label>Equipo m&aacute;s goleador del torneo</label>
                    <input type="text" name="top_scorer_team" class="pred-input" placeholder="Ej: Brazil" value="<?= htmlspecialchars($userPred['top_scorer_team'] ?? '') ?>">
                </div>
                <div class="pred-field">
                    <label>Pichichi del Mundial (jugador)</label>
                    <input type="text" name="pichichi" class="pred-input" placeholder="Ej: Mbapp&eacute;" value="<?= htmlspecialchars($userPred['pichichi'] ?? '') ?>">
                </div>
            </div>
        </section>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-full btn-lg">Guardar Mi Porra</button>
        </div>
        <div class="form-success" id="saveSuccess"></div>
        <div class="form-error" id="saveError"></div>
    </form>
</main>

<script>
document.getElementById('predictionsForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const errEl = document.getElementById('saveError');
    const okEl = document.getElementById('saveSuccess');
    errEl.textContent = '';
    okEl.textContent = '';

    const fd = new FormData(e.target);
    const data = { groups: {}, knockout: {}, final: {}, top_scorer_team: '', pichichi: '' };

    for (const [key, val] of fd.entries()) {
        const m = key.match(/^groups\[([A-L])\]\[(first|second)\]$/);
        if (m) {
            if (!data.groups[m[1]]) data.groups[m[1]] = {};
            data.groups[m[1]][m[2]] = val;
            continue;
        }
        const k = key.match(/^knockout\[(.+)\]$/);
        if (k) { data.knockout[k[1]] = val; continue; }
        const f = key.match(/^final\[(.+)\]$/);
        if (f) { data.final[f[1]] = val; continue; }
        if (key === 'top_scorer_team') { data.top_scorer_team = val; continue; }
        if (key === 'pichichi') { data.pichichi = val; continue; }
    }

    try {
        const res = await fetch(window.BASE_URL + '/api/predictions.php?action=save', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
            okEl.textContent = 'Guardado correctamente';
            setTimeout(() => okEl.textContent = '', 3000);
        } else {
            errEl.textContent = result.error;
        }
    } catch(err) {
        errEl.textContent = 'Error de conexi\u00f3n';
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
