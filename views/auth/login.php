<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/security.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acceso al Sistema - IEP Corazón de Jesús College</title>
  
  <!-- variables.css define los colores corporativos, tamaños y tokens de diseño globales -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/variables.css">
  <!-- login.css define los estilos específicos del formulario, animaciones y contenedor de login -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/login.css">
  <!-- jQuery necesario para las peticiones AJAX de inicio de sesión -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

  <!-- Contenedor principal centrado de la pantalla de Login -->
  <div class="login-container">
    <div class="login-card">
      <div class="login-logo">
        <img src="<?php echo BASE_URL; ?>public/img/logo_ie.jpg" alt="Logo Sagrado Corazón de Jesús" width="80rem" height="80rem">
      </div>

      <!-- Cabecera del formulario con el nombre del colegio -->
      <div class="login-header">
        <h2>Corazón de Jesús College</h2>
        <p>Sistema de Administración Académica</p>
      </div>

      <!-- Caja de alertas oculta por defecto. Se usará para mostrar mensajes de éxito o error al intentar acceder -->
      <div id="login-alert-box" style="display: none;"></div>

      <!-- Formulario de acceso -->
      <form id="login-form" class="login-form">
        <?php echo Security::campoCSRF(); ?>
        <div class="form-group">
          <label class="form-label" for="username">Nombre de Usuario / Correo</label>
          <div class="input-wrapper">
            <!-- Campo de entrada para el usuario. Requerido y con autocompletado desactivado -->
            <input type="text" id="username" name="email" class="form-input" placeholder="Ingrese su usuario..." autocomplete="off" required>
            <span class="input-icon">
              <!-- Icono de usuario SVG dentro del campo -->
              <svg viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
            </span>
          </div>
          <br>
          <label class="form-label" for="pass">Contraseña</label>
          <div class="input-wrapper">
            <!-- Campo de entrada para la contraseña. Requerido y con autocompletado desactivado -->
            <input type="password" id="pass" name="password" class="form-input" placeholder="Ingrese su Contraseña" autocomplete="off" required>
            <span class="input-icon">
              <!-- Icono de candado SVG dentro del campo -->
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
            </span>
          </div>
        </div>

        <!-- Botón de envío del formulario -->
        <button type="submit" class="btn-submit" id="btn-login">
          <span class="btn-text">Iniciar Sesión</span>
          <span class="btn-loading" style="display: none;">
              <i class="fas fa-spinner fa-spin"></i> Verificando...
          </span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </button>
      </form>
    </div>
  </div>

  <!-- Carga del script de autenticación para realizar la validación del usuario en cliente -->
  <script>
    const BASE_URL = "<?php echo BASE_URL; ?>";
  </script>
  <script src="<?php echo BASE_URL; ?>public/js/auth.js"></script>
  <script>
        $(document).ready(function() {
            setTimeout(function() {
                $('#session-alert').fadeOut('slow');
            }, 5000);
        });
  </script>
</body>
</html>
