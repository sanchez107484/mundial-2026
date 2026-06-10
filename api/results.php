<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'No autenticado'], 401);
}

$user = getCurrentUser();
if (!$user['is_admin']) {
    jsonResponse(['error' => 'Solo el admin puede hacer esto'], 403);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($action === 'get') {
    $results = getResults();
    jsonResponse(['results' => $results]);
}

if ($action === 'save') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        jsonResponse(['error' => 'Datos inválidos'], 400);
    }
    
    $results = [
        'groups' => $input['groups'] ?? [],
        'knockout' => $input['knockout'] ?? [],
        'final' => $input['final'] ?? [],
        'top_scorer_team' => $input['top_scorer_team'] ?? '',
        'pichichi' => $input['pichichi'] ?? '',
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    saveResults($results);
    
    jsonResponse(['success' => true, 'message' => 'Resultados guardados']);
}

jsonResponse(['error' => 'Acción no válida'], 400);
