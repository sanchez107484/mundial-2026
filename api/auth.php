<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($action === 'register') {
    $input = json_decode(file_get_contents('php://input'), true);
    $name = trim($input['name'] ?? '');
    $pin = trim($input['pin'] ?? '');
    
    if (empty($name) || strlen($name) < 2) {
        jsonResponse(['error' => 'El nombre debe tener al menos 2 caracteres'], 400);
    }
    if (empty($pin) || strlen($pin) !== 4 || !ctype_digit($pin)) {
        jsonResponse(['error' => 'El PIN debe tener 4 dígitos'], 400);
    }
    
    $users = getUsers();
    foreach ($users as $u) {
        if (strtolower($u['name']) === strtolower($name)) {
            jsonResponse(['error' => 'Ese nombre ya está cogido'], 400);
        }
    }
    
    $isAdmin = count($users) === 0;
    
    $newUser = [
        'id' => generateId(),
        'name' => $name,
        'pin' => password_hash($pin, PASSWORD_DEFAULT),
        'is_admin' => $isAdmin,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $users[] = $newUser;
    writeJSON(USERS_FILE, $users);
    
    $_SESSION['user_id'] = $newUser['id'];
    
    jsonResponse([
        'success' => true,
        'user' => [
            'id' => $newUser['id'],
            'name' => $newUser['name'],
            'is_admin' => $newUser['is_admin']
        ]
    ]);
}

if ($action === 'login') {
    $input = json_decode(file_get_contents('php://input'), true);
    $name = trim($input['name'] ?? '');
    $pin = trim($input['pin'] ?? '');
    
    if (empty($name) || empty($pin)) {
        jsonResponse(['error' => 'Nombre y PIN son obligatorios'], 400);
    }
    
    $users = getUsers();
    foreach ($users as $u) {
        if (strtolower($u['name']) === strtolower($name)) {
            if (password_verify($pin, $u['pin'])) {
                $_SESSION['user_id'] = $u['id'];
                jsonResponse([
                    'success' => true,
                    'user' => [
                        'id' => $u['id'],
                        'name' => $u['name'],
                        'is_admin' => $u['is_admin']
                    ]
                ]);
            }
            jsonResponse(['error' => 'PIN incorrecto'], 401);
        }
    }
    
    jsonResponse(['error' => 'Usuario no encontrado'], 404);
}

if ($action === 'logout') {
    session_destroy();
    jsonResponse(['success' => true]);
}

if ($action === 'me') {
    if (!isLoggedIn()) {
        jsonResponse(['error' => 'No autenticado'], 401);
    }
    $user = getCurrentUser();
    if (!$user) {
        session_destroy();
        jsonResponse(['error' => 'Usuario no encontrado'], 404);
    }
    jsonResponse([
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'is_admin' => $user['is_admin']
        ]
    ]);
}

jsonResponse(['error' => 'Acción no válida'], 400);
