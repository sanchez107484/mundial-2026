<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
requireAdmin();
require_once __DIR__ . '/../includes/header.php';

$teams = getTeams();
$groups = $teams['groups'];
$results = getResults();
?>
<main class="page-content">
    <h1 class="page-title">Panel Admin</h1>
    <p class="page-subtitle">Introduce los resultados reales para calcular las puntuaciones</p>

    <form id="resultsForm" class="predictions-form">
        <section class="pred-section">
            <h2 class="section-title">&#127942; Resultados de Grupos</h2>

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
                        <label>1&ordm; clasificado</label>
                        <select name="groups[<?= $letter ?>][first]" class="pred-select">
                            <option value="">--</option>
                            <?php foreach ($group['teams'] as $team): ?>
                            <option value="<?= $team['code'] ?>" <?= ($results['groups'][$letter]['first'] ?? '') === $team['code'] ? 'selected' : '' ?>>
                                <?= $team['flag'] ?> <?= $team['name'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pred-field">
                        <label>2&ordm; clasificado</label>
                        <select name="groups[<?= $letter ?>][second]" class="pred-select">
                            <option value="">--</option>
                            <?php foreach ($group['teams'] as $team): ?>
                            <option value="<?= $team['code'] ?>" <?= ($results['groups'][$letter]['second'] ?? '') === $team['code'] ? 'selected' : '' ?>>
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
            <h2 class="section-title">&#9876; Resultados de Cruces</h2>
            <p class="section-desc">Escribe el ganador de cada partido</p>

            <h3 class="subsection-title">Dieciseisavos</h3>
            <?php foreach ($teams['knockout']['roundOf32'] as $match): ?>
            <div class="pred-match">
                <div class="pred-match-label"><?= $match['label'] ?> &middot; <?= $match['team1'] ?> vs <?= $match['team2'] ?></div>
                <div class="pred-field">
                    <label>Ganador</label>
                    <input type="text" name="knockout[<?= $match['id'] ?>][winner]" class="pred-input" placeholder="Equipo ganador" value="<?= htmlspecialchars($results['knockout'][$match['id']]['winner'] ?? '') ?>">
                </div>
            </div>
            <?php endforeach; ?>

            <h3 class="subsection-title">Octavos</h3>
            <?php foreach ($teams['knockout']['roundOf16'] as $match): ?>
            <div class="pred-match">
                <div class="pred-match-label"><?= $match['label'] ?></div>
                <div class="pred-field">
                    <label>Ganador</label>
                    <input type="text" name="knockout[<?= $match['id'] ?>][winner]" class="pred-input" placeholder="Equipo ganador" value="<?= htmlspecialchars($results['knockout'][$match['id']]['winner'] ?? '') ?>">
                </div>
            </div>
            <?php endforeach; ?>

            <h3 class="subsection-title">Cuartos</h3>
            <?php foreach ($teams['knockout']['quarterFinals'] as $match): ?>
            <div class="pred-match">
                <div class="pred-match-label"><?= $match['label'] ?></div>
                <div class="pred-field">
                    <label>Ganador</label>
                    <input type="text" name="knockout[<?= $match['id'] ?>][winner]" class="pred-input" placeholder="Equipo ganador" value="<?= htmlspecialchars($results['knockout'][$match['id']]['winner'] ?? '') ?>">
                </div>
            </div>
            <?php endforeach; ?>

            <h3 class="subsection-title">Semifinales</h3>
            <?php foreach ($teams['knockout']['semiFinals'] as $match): ?>
            <div class="pred-match">
                <div class="pred-match-label"><?= $match['label'] ?></div>
                <div class="pred-field">
                    <label>Ganador</label>
                    <input type="text" name="knockout[<?= $match['id'] ?>][winner]" class="pred-input" placeholder="Equipo ganador" value="<?= htmlspecialchars($results['knockout'][$match['id']]['winner'] ?? '') ?>">
                </div>
            </div>
            <?php endforeach; ?>

            <h3 class="subsection-title">&#127942; Final</h3>
            <div class="pred-match pred-final">
                <div class="pred-field">
                    <label>Subcampe&oacute;n (finalista)</label>
                    <input type="text" name="final[runner_up]" class="pred-input" placeholder="El que pierde" value="<?= htmlspecialchars($results['final']['runner_up'] ?? '') ?>">
                </div>
                <div class="pred-field">
                    <label>&#127942; Campe&oacute;n del Mundo</label>
                    <input type="text" name="final[winner]" class="pred-input pred-champion" placeholder="El campe&oacute;n" value="<?= htmlspecialchars($results['final']['winner'] ?? '') ?>">
                </div>
            </div>
        </section>

        <section class="pred-section">
            <h2 class="section-title">&#127919; Bonus</h2>
            <div class="pred-match">
                <div class="pred-field">
                    <label>Equipo m&aacute;s goleador</label>
                    <input type="text" name="top_scorer_team" class="pred-input" placeholder="Ej: Brazil" value="<?= htmlspecialchars($results['top_scorer_team'] ?? '') ?>">
                </div>
                <div class="pred-field">
                    <label>Pichichi (jugador)</label>
                    <input type="text" name="pichichi" class="pred-input" placeholder="Ej: Mbapp&eacute;" value="<?= htmlspecialchars($results['pichichi'] ?? '') ?>">
                </div>
            </div>
        </section>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-full btn-lg">Guardar Resultados</button>
        </div>
        <div class="form-success" id="saveSuccess"></div>
        <div class="form-error" id="saveError"></div>
    </form>
</main>

<script>
document.getElementById('resultsForm').addEventListener('submit', async (e) => {
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
        const k = key.match(/^knockout\[(.+)\]\[winner\]$/);
        if (k) {
            if (!data.knockout[k[1]]) data.knockout[k[1]] = {};
            data.knockout[k[1]].winner = val;
            continue;
        }
        const f = key.match(/^final\[(.+)\]$/);
        if (f) { data.final[f[1]] = val; continue; }
        if (key === 'top_scorer_team') { data.top_scorer_team = val; continue; }
        if (key === 'pichichi') { data.pichichi = val; continue; }
    }

    try {
        const res = await fetch(window.BASE_URL + '/api/results.php?action=save', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
            okEl.textContent = 'Resultados guardados';
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
