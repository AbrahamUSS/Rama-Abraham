<?php

// front controller

session_start();

// dependencias
require_once "core/config.php";
require_once "core/database.php";
require_once "core/security.php";

// helper de rutas
if (file_exists("core/routes.php")) {
    require_once "core/routes.php";
}

// normalización de ruta y archivos estáticos
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

if ($base_dir !== '/' && strpos($path, $base_dir) === 0) {
    $path = substr($path, strlen($base_dir));
}
$path = trim($path, '/');

// normalizar prefijo views/public
if (preg_match('/^(views|public)\/(css|js|docs|img)\//', $path)) {
    $path = preg_replace('/^(views|public)\//', '', $path);
}

// servir archivos estáticos
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

// función despacho de controladores
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

// despacho MVC por GET
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

// rutas base
$routes = [
    ''                     => 'views/auth/login.php',
    'index.php'            => 'views/auth/login.php',
    'login'                => 'views/auth/login.php',
    'auth'                 => 'views/auth/login.php',
    'docente'              => 'views/docente.php',
    'docente.php'          => 'views/docente.php',
    'views/docente'        => 'views/docente.php',
    'views/docente.php'    => 'views/docente.php',
    'admin'                => 'views/admin.php',
    'admin.php'            => 'views/admin.php',
    'views/admin'          => 'views/admin.php',
    'views/admin.php'      => 'views/admin.php',
    'views/auth/login'     => 'views/auth/login.php',
    'views/auth/login.php' => 'views/auth/login.php'
];

if (array_key_exists($path, $routes)) {
    $file = __DIR__ . '/' . $routes[$path];
    if (file_exists($file)) {
        require_once $file;
        exit;
    }
}

// despacho MVC por URL amigable
if (!empty($path)) {
    $segments = explode('/', $path);
    $controllerName = $segments[0];
    $actionName = $segments[1] ?? ACCION_DEFAULT;
    $id = $segments[2] ?? null;

    if (despacharControlador($controllerName, $actionName, $id)) {
        exit;
    }
}

// controlador por defecto
if (despacharControlador(CONTROLADOR_DEFAULT, ACCION_DEFAULT)) {
    exit;
}

// 404 final
header("HTTP/1.0 404 Not Found");
echo "<h1>404 Not Found</h1><p>La ruta '{$path}' no existe en el sistema.</p>";
exit;