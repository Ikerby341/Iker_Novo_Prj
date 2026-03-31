<?php
// Include the articles model to access database functions
require_once __DIR__ . '/../app/Model/articles_model.php';

// Set headers for JSON API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow cross-origin requests if needed
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Mètode no permès!']);
    exit;
}

$apiPath = $_SERVER['PATH_INFO'] ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (!str_starts_with($apiPath, '/api')) {
    $apiPath = '/api' . $apiPath;
}

if ($apiPath === '/api/vehicles') {
    // Endpoint: GET /api/vehicles - Retrorna tots els vehicles
    $vehicles = generar_articles(1, 1000, 'ID', 'ASC', false, null); // High limit to get all
    echo json_encode($vehicles);
} elseif (preg_match('/^\/api\/users\/(\d+)\/vehicles$/', $apiPath, $matches)) {
    // Endpoint: GET /api/users/{id}/vehicles - Retorna els vehicles associats a un usuari específic
    $userId = (int)$matches[1];
    $vehicles = generar_articles(1, 1000, 'ID', 'ASC', true, $userId);
    echo json_encode($vehicles);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint no trobat!']);
}
?>