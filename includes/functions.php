<?php

function readJSON($file) {
    if (!file_exists($file)) return [];
    $content = file_get_contents($file);
    return json_decode($content, true) ?: [];
}

function writeJSON($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function generateId() {
    return bin2hex(random_bytes(8));
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function getTeams() {
    return readJSON(TEAMS_FILE);
}

function getUsers() {
    return readJSON(USERS_FILE);
}

function getPredictions() {
    return readJSON(PREDICTIONS_FILE);
}

function getResults() {
    return readJSON(RESULTS_FILE);
}

function savePredictions($data) {
    writeJSON(PREDICTIONS_FILE, $data);
}

function saveResults($data) {
    writeJSON(RESULTS_FILE, $data);
}

function calculateUserScore($userId) {
    $teams = getTeams();
    $predictions = getPredictions();
    $results = getResults();
    $scoring = $teams['scoring'];
    
    if (!isset($predictions[$userId])) return 0;
    
    $userPred = $predictions[$userId];
    $score = 0;
    
    if (isset($results['groups']) && isset($userPred['groups'])) {
        foreach ($results['groups'] as $group => $result) {
            if (isset($userPred['groups'][$group])) {
                $pred = $userPred['groups'][$group];
                if (isset($result['first']) && isset($pred['first']) && $result['first'] === $pred['first']) {
                    $score += $scoring['group_first'];
                }
                if (isset($result['second']) && isset($pred['second']) && $result['second'] === $pred['second']) {
                    $score += $scoring['group_second'];
                }
            }
        }
    }
    
    if (isset($results['knockout']) && isset($userPred['knockout'])) {
        foreach ($results['knockout'] as $matchId => $result) {
            if (isset($userPred['knockout'][$matchId]) && isset($result['winner'])) {
                if ($userPred['knockout'][$matchId] === $result['winner']) {
                    if (strpos($matchId, 'R32') === 0) $score += $scoring['round_of_32'];
                    elseif (strpos($matchId, 'R16') === 0) $score += $scoring['round_of_16'];
                    elseif (strpos($matchId, 'QF') === 0) $score += $scoring['quarter_final'];
                    elseif (strpos($matchId, 'SF') === 0) $score += $scoring['semi_finalist'];
                }
            }
        }
    }
    
    if (isset($results['final']) && isset($userPred['final'])) {
        if (isset($results['final']['runner_up']) && isset($userPred['final']['runner_up']) && $results['final']['runner_up'] === $userPred['final']['runner_up']) {
            $score += $scoring['finalist'];
        }
        if (isset($results['final']['winner']) && isset($userPred['final']['winner']) && $results['final']['winner'] === $userPred['final']['winner']) {
            $score += $scoring['winner'];
        }
    }
    
    if (isset($results['top_scorer_team']) && isset($userPred['top_scorer_team']) && $results['top_scorer_team'] === $userPred['top_scorer_team']) {
        $score += $scoring['top_scorer_team'];
    }
    
    if (isset($results['pichichi']) && isset($userPred['pichichi']) && strtolower(trim($results['pichichi'])) === strtolower(trim($userPred['pichichi']))) {
        $score += $scoring['pichichi'];
    }
    
    return $score;
}

function getRanking() {
    $users = getUsers();
    $teams = getTeams();
    $pot = count($users) * $teams['entryFee'];
    $dist = $teams['potDistribution'];
    
    $ranking = [];
    foreach ($users as $user) {
        $score = calculateUserScore($user['id']);
        $ranking[] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'score' => $score,
            'is_admin' => $user['is_admin']
        ];
    }
    
    usort($ranking, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    
    foreach ($ranking as $i => &$r) {
        $r['position'] = $i + 1;
        $r['prize'] = 0;
        if ($i === 0) $r['prize'] = round($pot * $dist['first'], 2);
        elseif ($i === 1) $r['prize'] = round($pot * $dist['second'], 2);
        elseif ($i === 2) $r['prize'] = round($pot * $dist['third'], 2);
    }
    
    return [
        'ranking' => $ranking,
        'pot' => $pot,
        'totalUsers' => count($users)
    ];
}

function getAllTeamCodes() {
    $teams = getTeams();
    $all = [];
    foreach ($teams['groups'] as $group) {
        foreach ($group['teams'] as $team) {
            $all[$team['code']] = $team;
        }
    }
    return $all;
}
