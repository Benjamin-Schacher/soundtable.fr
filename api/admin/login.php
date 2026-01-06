<?php
session_start();
header('Content-Type: application/json');

// Fonction simple pour charger .env
function loadEnv($path) {
    if (!file_exists($path)) {
        return [];
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $env[trim($name)] = trim($value);
    }
    return $env;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $password = $input['password'] ?? '';

    $env = loadEnv(__DIR__ . '/../../.env');
    $adminPassword = $env['ADMIN_PASSWORD'] ?? 'SoundTableAdmin2024!';

    if ($password === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;
        echo json_encode(['success' => true]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Mot de passe incorrect']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
