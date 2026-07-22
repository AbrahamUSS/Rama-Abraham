<?php
/**
 * Router / Front Controller for IEP Corazón de Jesús College
 * Resolves static assets from /public and views from /views
 */

// Get the base directory path (useful if hosted in a subdirectory like /Sistema_Gestion_IE)
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$request_uri = $_SERVER['REQUEST_URI'];

// Clean up query string parameters
$path = parse_url($request_uri, PHP_URL_PATH);

// Remove the base directory from the path to get the route
if ($base_dir !== '/' && strpos($path, $base_dir) === 0) {
    $path = substr($path, strlen($base_dir));
}

$path = ltrim($path, '/');

// Default route to index.html
if ($path === '' || $path === 'index.php') {
    $path = 'index.html';
}

// Normalize paths that are requested relative to views/ directory
if (preg_match('/^views\/(css|js|docs)\//', $path)) {
    $path = substr($path, 6); // remove 'views/' (6 characters)
}

// 1. Route static assets from the /public directory
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

// 2. Route HTML views from the /views directory
$views = ['index.html', 'login.html', 'docente.html', 'admin.html'];
if (in_array($path, $views)) {
    $file = __DIR__ . '/views/' . $path;
    if (file_exists($file)) {
        readfile($file);
        exit;
    }
}

// 3. Fallback to index.html or 404
header("HTTP/1.0 404 Not Found");
echo "<h1>404 Not Found</h1>";
echo "<p>La ruta especificada no existe en el sistema.</p>";
exit;
