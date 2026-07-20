<?php
    class Conexion {
        private static $host = "localhost";
        private static $db_name = "colegio_DB";
        private static $username = "root";
        private static $password = "";

        public static function connection() {
            $conn = null;
            try {
                $conn = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$db_name . ";charset=utf8mb4",
                    self::$username,
                    self::$password
                );
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
            return $conn;
        }
    }
?>