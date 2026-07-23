<?php

session_start();

require_once "core/config.php";
require_once "core/database.php";
require_once "core/router.php";
require_once "core/security.php";

// 1. Obtener el directorio base
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$request_uri = $_SERVER['REQUEST_URI'];

// 2. Limpiar query string
$path = parse_url($request_uri, PHP_URL_PATH);

// 3. Remover el subdirectorio base
if ($base_dir !== '/' && strpos($path, $base_dir) === 0) {
    $path = substr($path, strlen($base_dir));
}

// Limpiar diagonales iniciales y finales
$path = trim($path, '/');

// 4. Normalizar rutas de recursos estáticos enviados por error a views/
if (preg_match('/^views\/(css|js|docs)\//', $path)) {
    $path = substr($path, 6);
}

// --- ENRUTAMIENTO ---

// A. ARCHIVOS ESTÁTICOS (/public)
if (preg_match('/^(css|js|docs)\//', $path)) {
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

// B. MAPEO DE VISTAS PHP (/views)
// Asociamos la URL amigable con la ubicación real del archivo PHP
$routes = [
    ''              => 'views/auth/login.php', // Ruta raíz
    'index.php'     => 'views/auth/login.php',
    'login'         => 'views/auth/login.php',
    'docente'       => 'views/docente.html',
    'admin'         => 'views/admin.html'
];

// Si la ruta solicitada existe en nuestro mapa de rutas
if (array_key_exists($path, $routes)) {
    $file = __DIR__ . '/' . $routes[$path];
    
    if (file_exists($file)) {
        // Ejecutamos el script PHP en lugar de leerlo como texto
        require_once $file;
        exit;
    }
}

// C. RESPUESTA DE ERROR 404
header("HTTP/1.0 404 Not Found");
echo "<h1>404 Not Found</h1>";
echo "<p>La ruta '{$path}' no existe en el sistema.</p>";
exit;