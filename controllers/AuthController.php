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
            $rol = strtolower(trim($_SESSION['rol_nombre'] ?? ''));
            if (in_array($rol, ['director', 'administrador', 'admin'])) {
                header("Location: " . BASE_URL . "admin");
                exit;
            } else if ($rol === 'docente') {
                header("Location: " . BASE_URL . "docente");
                exit;
            }
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

            // Verificación de bloqueo por demasiados intentos fallidos
            if (isset($_SESSION['login_lockout_time']) && time() < $_SESSION['login_lockout_time']) {
                $remainingSeconds = $_SESSION['login_lockout_time'] - time();
                $remainingMinutes = ceil($remainingSeconds / 60);
                echo json_encode([
                    "success"      => false,
                    "mensaje"      => "Demasiados intentos fallidos. Su acceso está bloqueado por " . $remainingMinutes . " minuto(s). Intente nuevamente más tarde.",
                    "locked"       => true,
                    "lock_seconds" => $remainingSeconds
                ]);
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
                $this->registrarIntentoFallido();
                return;
            }

            if (Security::verificarPassword($password, $usuario['password'])) {
                // Reiniciar contador de intentos fallidos al tener éxito
                unset($_SESSION['login_attempts'], $_SESSION['login_lockout_time']);

                $nombreCompleto = trim($usuario['nombre'] . ' ' . ($usuario['ap_paterno'] ?? ''));
                $_SESSION['usuario_id']     = $usuario['id'];
                $_SESSION['usuario_nombre'] = !empty($nombreCompleto) ? $nombreCompleto : $usuario['nombre'];
                $_SESSION['usuario_email']  = $usuario['email'];
                $_SESSION['rol_id']         = $usuario['rol_id'];
                $_SESSION['rol_nombre']     = $usuario['rol_nombre'];
                $_SESSION['last_activity']  = time();

                // Si el rol es Docente, resolver y guardar el id_docente en sesión
                $rol = strtolower(trim($usuario['rol_nombre']));
                if ($rol === 'docente') {
                    require_once "models/UsuarioModel.php";
                    $usuarioModel = new UsuarioModel();
                    $idDocente = $usuarioModel->getIdDocenteByCredencial($usuario['id']);
                    $_SESSION['id_docente'] = $idDocente;
                }

                session_regenerate_id(true);

                // Determinar la URL de redirección según el rol asignado
                $rol = strtolower(trim($usuario['rol_nombre']));
                if (in_array($rol, ['director', 'administrador', 'admin'])) {
                    $redirect = BASE_URL . "admin";
                } else if ($rol === 'docente') {
                    $redirect = BASE_URL . "docente";
                } else {
                    $redirect = BASE_URL . "admin";
                }

                echo json_encode([
                    "success"  => true,
                    "mensaje"  => "Bienvenido, " . $_SESSION['usuario_nombre'],
                    "redirect" => $redirect
                ]);
            } else {
                $this->registrarIntentoFallido();
            }
        } else {
            echo json_encode(["success" => false, "mensaje" => "Método no permitido."]);
        }
    }

    private function registrarIntentoFallido()
    {
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        $maxAttempts = 5;

        if ($_SESSION['login_attempts'] >= $maxAttempts) {
            $_SESSION['login_lockout_time'] = time() + 300; // 5 minutos de bloqueo
            $_SESSION['login_attempts'] = 0;
            echo json_encode([
                "success"      => false,
                "mensaje"      => "Ha superado el límite de 5 intentos fallidos. El acceso ha sido bloqueado por 5 minutos.",
                "locked"       => true,
                "lock_seconds" => 300
            ]);
        } else {
            $restantes = $maxAttempts - $_SESSION['login_attempts'];
            echo json_encode([
                "success" => false,
                "mensaje" => "Correo electrónico o contraseña incorrectos. Intentos restantes: " . $restantes . "."
            ]);
        }
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        header("Location: " . BASE_URL . "auth");
        exit;
    }

    public function cambiarPassword()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            echo json_encode(["success" => false, "mensaje" => "Método no permitido."]);
            return;
        }

        Security::verificarSesion();

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Security::validarTokenCSRF($csrfToken)) {
            echo json_encode(["success" => false, "mensaje" => "Token de seguridad inválido."]);
            return;
        }

        $actual    = $_POST['password_actual'] ?? '';
        $nueva     = $_POST['password_nueva'] ?? '';
        $confirmar = $_POST['password_confirmar'] ?? '';

        if (empty($actual) || empty($nueva) || empty($confirmar)) {
            echo json_encode(["success" => false, "mensaje" => "Todos los campos son obligatorios."]);
            return;
        }

        if ($nueva !== $confirmar) {
            echo json_encode(["success" => false, "mensaje" => "Las contraseñas nuevas no coinciden."]);
            return;
        }

        if (strlen($nueva) < 4) {
            echo json_encode(["success" => false, "mensaje" => "La contraseña debe tener al menos 4 caracteres."]);
            return;
        }

        $idCredenciales = $_SESSION['usuario_id'];
        $hashActual = $this->usuarios->getPasswordHashById($idCredenciales);

        if (!$hashActual || !Security::verificarPassword($actual, $hashActual)) {
            echo json_encode(["success" => false, "mensaje" => "La contraseña actual es incorrecta."]);
            return;
        }

        $nuevoHash = Security::encriptarPassword($nueva);
        $this->usuarios->cambiarPassword($idCredenciales, $nuevoHash);

        echo json_encode(["success" => true, "mensaje" => "Contraseña actualizada correctamente."]);
    }

    public function accesoDenegado()
    {
        require_once "views/errors/403.php";
    }

    public function recuperarPassword()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            echo json_encode(["success" => false, "mensaje" => "Método no permitido."]);
            return;
        }

        $dni    = trim($_POST['dni'] ?? '');
        $correo = trim($_POST['correo'] ?? '');

        if (empty($dni) || empty($correo)) {
            echo json_encode(["success" => false, "mensaje" => "Ingrese su DNI y correo electrónico."]);
            return;
        }

        if (!preg_match('/^[0-9]{8}$/', $dni)) {
            echo json_encode(["success" => false, "mensaje" => "El DNI debe contener exactamente 8 dígitos numéricos."]);
            return;
        }

        $usuario = $this->usuarios->recuperarPassword($dni, $correo);

        if (!$usuario) {
            echo json_encode(["success" => false, "mensaje" => "No se encontró una cuenta con esos datos."]);
            return;
        }

        $nueva     = trim($_POST['password_nueva'] ?? '');
        $confirmar = trim($_POST['password_confirmar'] ?? '');

        if (!empty($nueva) && !empty($confirmar)) {
            if ($nueva !== $confirmar) {
                echo json_encode(["success" => false, "mensaje" => "Las contraseñas no coinciden."]);
                return;
            }
            if (strlen($nueva) < 4) {
                echo json_encode(["success" => false, "mensaje" => "La contraseña debe tener al menos 4 caracteres."]);
                return;
            }

            $nuevoHash = Security::encriptarPassword($nueva);
            $this->usuarios->resetPassword((int)$usuario['id_credenciales'], $nuevoHash);

            echo json_encode([
                "success" => true,
                "step"    => "reset_done",
                "mensaje" => "Contraseña actualizada correctamente. Ahora puede iniciar sesión.",
                "data"    => ["username" => $usuario['username']]
            ]);
            return;
        }

        echo json_encode([
            "success" => true,
            "step"    => "identity_ok",
            "mensaje" => "Identidad verificada. Ahora establezca su nueva contraseña.",
            "data"    => [
                "username" => $usuario['username'],
                "nombre"   => trim($usuario['nombre'] . ' ' . $usuario['ap_paterno'])
            ]
        ]);
    }
}
