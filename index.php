<?php

/**
 * =====================================================================
 * FRONT CONTROLLER - PUNTO DE ENTRADA UNIFICADO
 * IEP Corazón de Jesús
 * =====================================================================
 */

session_start();

// 1. Carga de dependencias del sistema
require_once "core/config.php";
require_once "core/database.php";
require_once "core/security.php";

// Cargar helper de rutas si existe
if (file_exists("core/routes.php")) {
    require_once "core/routes.php";
}

// ---------------------------------------------------------------------
// 2. NORMALIZACIÓN DE RUTA Y SERVIDOR DE ARCHIVOS ESTÁTICOS
// ---------------------------------------------------------------------
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

if ($base_dir !== '/' && strpos($path, $base_dir) === 0) {
    $path = substr($path, strlen($base_dir));
}
$path = trim($path, '/');

// Normalizar recursos estáticos enviados con prefijo views/ o public/
if (preg_match('/^(views|public)\/(css|js|docs|img)\//', $path)) {
    $path = preg_replace('/^(views|public)\//', '', $path);
}

// Servir archivos estáticos (CSS, JS, PDF, Imágenes)
if (preg_match('/^(css|js|docs|img)\//', $path)) {
    $file = __DIR__ . '/public/' . $path;
    if (file_exists($file)) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $content_types = [
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'pdf'  => 'application/pdf',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'svg'  => 'image/svg+xml',
            'json' => 'application/json'
        ];
        if (isset($content_types[$ext])) {
            header('Content-Type: ' . $content_types[$ext]);
        }
        readfile($file);
        exit;
    }
}

// ---------------------------------------------------------------------
// FUNCIÓN AUXILIAR DE DESPACHO DE CONTROLADORES
// ---------------------------------------------------------------------
function despacharControlador($controllerName, $actionName, $id = null) {
    $claseControlador = ucfirst($controllerName) . "Controller";
    $archivoControlador = __DIR__ . "/controllers/" . $claseControlador . ".php";

    if (file_exists($archivoControlador)) {
        require_once $archivoControlador;
        if (class_exists($claseControlador)) {
            $controlador = new $claseControlador();
            if (method_exists($controlador, $actionName)) {
                if ($id !== null && $id !== '') {
                    $controlador->$actionName($id);
                } else {
                    $controlador->$actionName();
                }
                return true;
            }
        }
    }
    return false;
}

// ---------------------------------------------------------------------
// 3. DESPACHO MVC POR PARÁMETROS GET (?controller=...&action=...&id=...)
// ---------------------------------------------------------------------
if (isset($_GET["controller"])) {
    $controllerName = $_GET["controller"];
    $actionName = $_GET["action"] ?? ACCION_DEFAULT;
    $id = $_GET["id"] ?? null;

    if (despacharControlador($controllerName, $actionName, $id)) {
        exit;
    } else {
        header("HTTP/1.0 404 Not Found");
        echo "<h1>404 Not Found</h1><p>El controlador o método solicitado no existe.</p>";
        exit;
    }
}

// ---------------------------------------------------------------------
// 4. MAPEO DE VISTAS DIRECTAS / RUTAS BASE
// ---------------------------------------------------------------------
$routes = [
    ''          => 'views/auth/login.php',
    'index.php' => 'views/auth/login.php',
    'login'     => 'views/auth/login.php',
    'auth'      => 'views/auth/login.php',
    'docente'   => 'views/docente.php',
    'admin'     => 'views/admin.php'
];

if (array_key_exists($path, $routes)) {
    $file = __DIR__ . '/' . $routes[$path];
    if (file_exists($file)) {
        require_once $file;
        exit;
    }
}

// ---------------------------------------------------------------------
// 5. DESPACHO MVC POR RUTA DE URL AMIGABLE (ej: auth/login o usuario/eliminar/5)
// ---------------------------------------------------------------------
if (!empty($path)) {
    $segments = explode('/', $path);
    $controllerName = $segments[0];
    $actionName = $segments[1] ?? ACCION_DEFAULT;
    $id = $segments[2] ?? null;

    if (despacharControlador($controllerName, $actionName, $id)) {
        exit;
    }
}

// ---------------------------------------------------------------------
// 6. CONTROLADOR Y ACCIÓN POR DEFECTO
// ---------------------------------------------------------------------
if (despacharControlador(CONTROLADOR_DEFAULT, ACCION_DEFAULT)) {
    exit;
}

// 7. RESPUESTA 404 SI NINGUNA REGLA COINCIDIÓ
header("HTTP/1.0 404 Not Found");
echo "<h1>404 Not Found</h1><p>La ruta '{$path}' no existe en el sistema.</p>";
exit;