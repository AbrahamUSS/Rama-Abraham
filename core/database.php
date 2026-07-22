<?php
    class Conexion {
        private static $host = "localhost;port=3306";
        private static $db_name = "colegio_db";
        private static $username = "root";
        private static $password = "";

        // patron singleton para solo tener una conexion abierta 
        private static $instance = null;

        public static function connection() {
            if (self::$instance === null) {
                try {
                    $baseConnection = new PDO(
                        "mysql:host=" . self::$host . ";charset=utf8mb4",
                        self::$username,
                        self::$password,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES => false,
                        ]
                    );
                    $baseConnection->exec("CREATE DATABASE IF NOT EXISTS " . self::$db_name);

                    self::$instance = new PDO(
                        "mysql:host=" . self::$host . ";dbname=" . self::$db_name . ";charset=utf8mb4",
                        self::$username,
                        self::$password,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES => false,
                        ]
                    );
                } catch (PDOException $e) {
                    // error en el server interno
                    error_log("Error de conexión BD: " . $e->getMessage());

                    // excepcion se muestra al usuario sin datos sensibles
                    throw new Exception("Error interno del servidor. Inténtelo más tarde.");
                }
            }
            return self::$instance;
        }
    }
?>