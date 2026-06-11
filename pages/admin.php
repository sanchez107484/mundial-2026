<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
requireAdmin();

$teams = getTeams();
$groups = $teams['groups'];
$results = getResults();

// Team lookup
$teamLookup = [];
foreach ($groups as $group) {
    foreach ($group['teams'] as $t) {
        $teamLookup[$t['code']] = ['name' => $t['name'], 'flag' => $t['flag']];
    }
}
?>
<main class="page-content">
    <div class="page-header">
        <h1 class="page-title">PANEL ADMIN</h1>
        <p class="page-subtitle">Introduce los resultados reales para calcular las puntuaciones</p>
    </div>

    <form id="resultsForm" class="pred-form">
        <!-- GROUPS -->
        <section class="pred-section">
            <h2 class="section-title"><span class="icon">🏆</span> Resultados de Grupos</h2>

            <?php foreach ($groups as $letter => $group): ?>
            <div class="pred-group">
                <div class="pred-group-header">
                    <span class="pred-group-letter"><?= $letter ?></span>
                    <?php foreach ($group['teams'] as $team): ?>
                    <span class="pred-team-chip"><span class="flag"><?= $team['flag'] ?></span> <?= $team['name'] ?></span>
                    <?php endforeach; ?>
                </div>
                <div class="pred-group-fields">
                    <div class="pred-field">
                        <label>1° clasificado</label>
                        <select name="groups[<?= $letter ?>][first]" class="pred-select <?= !empty($results['groups'][$letter]['first']) ? 'has-value' : '' ?>">
                            <option value="">--</option>
                            <?php foreach ($group['teams'] as $team): ?>
                            <option value="<?= $team['code'] ?>" <?= ($results['groups'][$letter]['first'] ?? '') === $team['code'] ? 'selected' : '' ?>>
                                <?= $team['flag'] ?> <?= $team['name'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pred-field">
                        <label>2° clasificado</label>
                        <select name="groups[<?= $letter ?>][second]" class="pred-select <?= !empty($results['groups'][$letter]['second']) ? 'has-value' : '' ?>">
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

        <!-- KNOCKOUT -->
        <section class="pred-section">
            <h2 class="section-title"><span class="icon">⚽</span> Resultados de Cruces</h2>
            <p class="section-desc">Escribe el ganador de cada partido</p>

            <h3 class="subsection-title">Dieciseisavos</h3>
            <?php foreach ($teams['knockout']['roundOf32'] as $match): ?>
            <?php
            $t1 = isset($teamLookup[$match['team1']]) ? $teamLookup[$match['team1']] : ['name' => $match['team1'], 'flag' => ''];
            $t2 = isset($teamLookup[$match['team2']]) ? $teamLookup[$match['team2']] : ['name' => $match['team2'], 'flag' => ''];
            ?>
            <div class="pred-match">
                <div class="pred-match-label"><?= $match['label'] ?> · <?= $match['venue'] ?></div>
                <div class="pred-match-teams" style="margin-bottom: 12px;">
                    <span class="pred-team-option"><span class="flag"><?= $t1['flag'] ?></span> <?= $t1['name'] ?></span>
                    <span class="pred-vs">VS</span>
                    <span class="pred-team-option"><span class="flag"><?= $t2['flag'] ?></span> <?= $t2['name'] ?></span>
                </div>
                <div class="pred-field">
                    <label>Ganador</label>
                    <input type="text" name="knockout[<?= $match['id'] ?>][winner]" class="pred-input" placeholder="Equipo ganador" value="<?= htmlspecialchars($results['knockout'][$match['id']]['winner'] ?? '') ?>">
                </div>
            </div>
            <?php endforeach; ?>

            <h3 class="subsection-title">Octavos</h3>
            <?php foreach ($teams['knockout']['roundOf16'] as $match): ?>
            <div class="pred-match">
                <div class="pred-match-label"><?= $match['label'] ?> · <?= $match['venue'] ?></div>
                <div class="pred-field">
                    <label>Ganador</label>
                    <input type="text" name="knockout[<?= $match['id'] ?>][winner]" class="pred-input" placeholder="Equipo ganador" value="<?= htmlspecialchars($results['knockout'][$match['id']]['winner'] ?? '') ?>">
                </div>
            </div>
            <?php endforeach; ?>

            <h3 class="subsection-title">Cuartos</h3>
            <?php foreach ($teams['knockout']['quarterFinals'] as $match): ?>
            <div class="pred-match">
                <div class="pred-match-label"><?= $match['label'] ?> · <?= $match['venue'] ?></div>
                <div class="pred-field">
                    <label>Ganador</label>
                    <input type="text" name="knockout[<?= $match['id'] ?>][winner]" class="pred-input" placeholder="Equipo ganador" value="<?= htmlspecialchars($results['knockout'][$match['id']]['winner'] ?? '') ?>">
                </div>
            </div>
            <?php endforeach; ?>

            <h3 class="subsection-title">Semifinales</h3>
            <?php foreach ($teams['knockout']['semiFinals'] as $match): ?>
            <div class="pred-match">
                <div class="pred-match-label"><?= $match['label'] ?> · <?= $match['venue'] ?></div>
                <div class="pred-field">
                    <label>Ganador</label>
                    <input type="text" name="knockout[<?= $match['id'] ?>][winner]" class="pred-input" placeholder="Equipo ganador" value="<?= htmlspecialchars($results['knockout'][$match['id']]['winner'] ?? '') ?>">
                </div>
            </div>
            <?php endforeach; ?>

            <h3 class="subsection-title">🏆 Final</h3>
            <div class="pred-match pred-final">
                <div class="pred-field">
                    <label>Subcampeón (finalista)</label>
                    <input type="text" name="final[runner_up]" class="pred-input" placeholder="El que pierde" value="<?= htmlspecialchars($results['final']['runner_up'] ?? '') ?>">
                </div>
                <div class="pred-field" style="margin-top: 12px;">
                    <label>🏆 Campeón del Mundo</label>
                    <input type="text" name="final[winner]" class="pred-input pred-champion" placeholder="El campeón" value="<?= htmlspecialchars($results['final']['winner'] ?? '') ?>">
                </div>
            </div>
        </section>

        <!-- BONUS -->
        <section class="pred-section">
            <h2 class="section-title"><span class="icon">⭐</span> Bonus</h2>
            <div class="pred-match">
                <div class="pred-field">
                    <label>Equipo más goleador</label>
                    <input type="text" name="top_scorer_team" class="pred-input" placeholder="Ej: Brasil" value="<?= htmlspecialchars($results['top_scorer_team'] ?? '') ?>">
                </div>
                <div class="pred-field" style="margin-top: 12px;">
                    <label>Pichichi (jugador)</label>
                    <input type="text" name="pichichi" class="pred-input" placeholder="Ej: Mbappé" value="<?= htmlspecialchars($results['pichichi'] ?? '') ?>">
                </div>
            </div>
        </section>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-full btn-lg">Guardar Resultados</button>
            <div class="form-success" id="saveSuccess"></div>
            <div class="form-error" id="saveError"></div>
        </div>
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
            headers: { 'Content-Type': 'application/json' },
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
        errEl.textContent = 'Error de conexión';
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>