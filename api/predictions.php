<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'No autenticado'], 401);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

if ($action === 'get') {
    $predictions = getPredictions();
    $userPred = $predictions[$userId] ?? null;
    jsonResponse(['predictions' => $userPred]);
}

if ($action === 'save') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        jsonResponse(['error' => 'Datos inválidos'], 400);
    }
    
    $predictions = getPredictions();
    
    $predictions[$userId] = [
        'groups' => $input['groups'] ?? [],
        'knockout' => $input['knockout'] ?? [],
        'final' => $input['final'] ?? [],
        'top_scorer_team' => $input['top_scorer_team'] ?? '',
        'pichichi' => $input['pichichi'] ?? '',
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    savePredictions($predictions);
    
    jsonResponse(['success' => true, 'message' => 'Predicciones guardadas']);
}

jsonResponse(['error' => 'Acción no válida'], 400);
