<?php

require_once "models/UsuarioModel.php";

class AuthController
{

    protected $usuarios;

    public function __construct()
    {
        $this->usuarios = new UsuarioModel();
    }

    public function index()
    {
        if (isset($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "dashboard");
            exit;
        }
        require_once "views/auth/login.php";
    }

    public function login()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $csrfToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
            if (!Security::validarTokenCSRF($csrfToken)) {
                echo json_encode(["success" => false, "mensaje" => "Token de seguridad inválido. Recargue la página."]);
                return;
            }

            $email = Security::sanitizarEntrada($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                echo json_encode(["success" => false, "mensaje" => "Todos los campos son obligatorios."]);
                return;
            }

            $usuario = $this->usuarios->getUsuarioByEmail($email);

            if (!$usuario) {
                echo json_encode(["success" => false, "mensaje" => "Correo electrónico o contraseña incorrectos."]);
                return;
            }

            if (Security::verificarPassword($password, $usuario['password'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['rol_id'] = $usuario['rol_id'];
                $_SESSION['rol_nombre'] = $usuario['rol_nombre'];
                $_SESSION['last_activity'] = time();

                session_regenerate_id(true);

                echo json_encode([
                    "success" => true,
                    "mensaje" => "Bienvenido, " . $usuario['nombre'],
                    "redirect" => BASE_URL . "dashboard"
                ]);
            } else {
                echo json_encode(["success" => false, "mensaje" => "Correo electrónico o contraseña incorrectos."]);
            }
        } else {
            echo json_encode(["success" => false, "mensaje" => "Método no permitido."]);
        }
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        header("Location: " . BASE_URL . "auth");
        exit;
    }

    public function accesoDenegado()
    {
        require_once "views/errors/403.php";
    }
}
