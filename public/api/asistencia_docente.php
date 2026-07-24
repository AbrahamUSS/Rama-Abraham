<?php
// Endpoint API de asistencia docente: auto-marcación y consultas
require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/security.php';
require_once __DIR__ . '/../../controllers/AsistenciaDocenteController.php';

session_start();

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, X-Requested-With, X-CSRF-Token');

function responseJson($success, $message, $data = null): void
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data
    ]);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    $method  = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $query   = $_GET;
    $payload = [];

    if ($method === 'POST') {
        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Security::validarTokenCSRF($csrfToken)) {
            responseJson(false, 'Token de seguridad inválido.', null);
            exit;
        }

        $input = file_get_contents('php://input');
        if (!empty($input)) {
            $decoded = json_decode($input, true);
            $payload = is_array($decoded) ? $decoded : [];
            if (empty($payload)) {
                parse_str($input, $payload);
            }
        }
        $payload = array_merge($_POST, $payload);
    }

    if (!isset($_SESSION['usuario_id'])) {
        responseJson(false, 'La sesión no está activa.', null);
        exit;
    }

    $controller = new AsistenciaDocenteController();
    $result     = $controller->handleRequest($method, $payload, $query);
    http_response_code($result['status'] ?? 200);
    responseJson($result['success'], $result['message'], $result['data']);
} catch (Throwable $e) {
    http_response_code(500);
    responseJson(false, 'Error interno del servidor.', null);
}
