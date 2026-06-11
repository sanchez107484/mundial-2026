<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
require_once __DIR__ . '/../includes/header.php';

$teams = getTeams();
$groups = $teams['groups'];
?>
<main class="page-content">
    <div class="page-header">
        <h1 class="page-title">FASE DE GRUPOS</h1>
        <p class="page-subtitle">12 grupos · 48 equipos · Clasifican 1°, 2° y los 8 mejores 3°</p>
    </div>

    <div class="groups-container">
        <?php foreach ($groups as $letter => $group): ?>
        <div class="group-card">
            <div class="group-header">
                <span class="group-letter"><?= $letter ?></span>
                <span class="group-name"><?= $group['name'] ?></span>
            </div>
            <table class="group-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Equipo</th>
                        <th>Cod</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($group['teams'] as $i => $team): ?>
                    <tr>
                        <td class="pos-cell"><?= $i + 1 ?></td>
                        <td class="team-cell">
                            <span class="team-flag"><?= $team['flag'] ?></span>
                            <span class="team-name"><?= $team['name'] ?></span>
                        </td>
                        <td class="seed-cell"><?= $team['code'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>