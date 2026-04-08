<?php
require_once __DIR__ . '/../../config/db-connection.php';
require_once __DIR__ . '/../Model/articles_model.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function get_api_key_from_request() {
    if (!empty($_GET['api_key'])) {
        return trim($_GET['api_key']);
    }

    if (!empty($_SERVER['HTTP_X_API_KEY'])) {
        return trim($_SERVER['HTTP_X_API_KEY']);
    }

    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = trim($_SERVER['HTTP_AUTHORIZATION']);
    } elseif (function_exists('getallheaders')) {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    } else {
        $authHeader = null;
    }

    if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
        return trim(substr($authHeader, 7));
    }

    return null;
}

$apiKey = get_api_key_from_request();
if (!$apiKey || !is_valid_api_key($apiKey)) {
    http_response_code(401);
    echo json_encode(['error' => 'API key invàlida o no proporcionada.']);
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