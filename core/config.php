<?php
    define("CONTROLADOR_DEFAULT", "auth");
    define("ACCION_DEFAULT", "index");

    // Detección dinámica de BASE_URL para que funcione automáticamente en cualquier puerto/servidor/Docker/XAMPP
    if (!defined("BASE_URL")) {
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
        $script_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $script_dir = preg_replace('/[\/\\\\]views(\/.*)?$/i', '', $script_dir);
        $baseUrl = getenv("BASE_URL") ?: ($scheme . "://" . $host . ($script_dir !== '' ? $script_dir : "") . "/");
        define("BASE_URL", $baseUrl);
    }

    define("ERROR_404", "views/errors/404.php");
    define("ERROR_403", "views/errors/403.php");

    define("SESSION_TIMEOUT", 900);
?>