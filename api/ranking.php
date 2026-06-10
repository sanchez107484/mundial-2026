<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'get') {
    $ranking = getRanking();
    jsonResponse($ranking);
}

if ($action === 'teams') {
    $teams = getTeams();
    jsonResponse($teams);
}

jsonResponse(['error' => 'Acción no válida'], 400);
