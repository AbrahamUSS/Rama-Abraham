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
  
  <!-- estilos globales -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/variables.css">
  <!-- estilos login -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/login.css">
  <!-- jQuery AJAX -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

  <!-- contenedor login -->
  <div class="login-container">
    <div class="login-card">
      <div class="login-logo">
        <img src="<?php echo BASE_URL; ?>public/img/logo_ie.jpg" alt="Logo Sagrado Corazón de Jesús" width="80rem" height="80rem">
      </div>

      <!-- cabecera -->
      <div class="login-header">
        <h2>Corazón de Jesús College</h2>
        <p>Sistema de Administración Académica</p>
      </div>

      <!-- alertas -->
      <div id="login-alert-box" style="display: none;"></div>

      <!-- formulario login -->
      <form id="login-form" class="login-form">
        <?php echo Security::campoCSRF(); ?>
        <div class="form-group">
          <label class="form-label" for="username">Nombre de Usuario / Correo</label>
          <div class="input-wrapper">
            <!-- campo usuario -->
            <input type="text" id="username" name="email" class="form-input" placeholder="Ingrese su usuario..." autocomplete="off" required>
            <span class="input-icon">
              <!-- icono usuario -->
              <svg viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
            </span>
          </div>
          <br>
          <label class="form-label" for="pass">Contraseña</label>
          <div class="input-wrapper">
            <!-- campo contraseña -->
            <input type="password" id="pass" name="password" class="form-input" placeholder="Ingrese su Contraseña" autocomplete="off" required>
            <span class="input-icon">
              <!-- icono candado -->
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
            </span>
            <button type="button" class="pwd-toggle-btn" tabindex="-1" aria-label="Mostrar contraseña">
              <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
              <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
            </button>
          </div>
        </div>

        <!-- botón enviar -->
        <button type="submit" class="btn-submit" id="btn-login">
          <span class="btn-text">Iniciar Sesión</span>
          <span class="btn-loading" style="display: none;">
              <i class="fas fa-spinner fa-spin"></i> Verificando...
          </span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </button>

        <div style="text-align:center; margin-top:16px;">
          <a href="#" id="show-recovery" style="font-size:13px; color:var(--primary-orange); text-decoration:none;">¿Olvidaste tu contraseña?</a>
        </div>
      </form>

      <!-- formulario recuperación -->
      <form id="recovery-form" class="login-form" novalidate style="display:none; text-align:left;">
        <div style="text-align:center; margin-bottom:20px;">
          <h3 style="font-size:18px; color:var(--primary-dark); margin-bottom:4px;">Recuperar Contraseña</h3>
          <p style="font-size:13px; color:var(--neutral-medium);">Ingrese su DNI y correo electrónico registrados</p>
        </div>

        <!-- paso 1 -->
        <div id="recovery-step1">
          <div class="form-group">
            <label class="form-label" for="recovery-dni">DNI</label>
            <div class="input-wrapper">
              <input type="text" id="recovery-dni" class="form-input" placeholder="Ingrese su DNI" maxlength="15" autocomplete="off" required>
              <span class="input-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                  <line x1="2" y1="10" x2="22" y2="10"></line>
                </svg>
              </span>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="recovery-email">Correo Electrónico</label>
            <div class="input-wrapper">
              <input type="email" id="recovery-email" class="form-input" placeholder="Ingrese su correo" autocomplete="off" required>
              <span class="input-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                  <polyline points="22,4 12,13 2,4"></polyline>
                </svg>
              </span>
            </div>
          </div>
        </div>

        <!-- paso 2 -->
        <div id="recovery-step2" style="display:none;">
          <div style="text-align:center; margin-bottom:16px; padding:10px; background:#d4edda; border-radius:6px; color:#155724; font-size:13px;">
            <strong id="recovery-username-display"></strong>
          </div>
          <div class="form-group">
            <label class="form-label" for="recovery-new-pass">Nueva Contraseña</label>
            <div class="input-wrapper">
              <input type="password" id="recovery-new-pass" class="form-input" placeholder="Mínimo 4 caracteres" autocomplete="off" required>
              <span class="input-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
              </span>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="recovery-confirm-pass">Confirmar Contraseña</label>
            <div class="input-wrapper">
              <input type="password" id="recovery-confirm-pass" class="form-input" placeholder="Repita la contraseña" autocomplete="off" required>
              <span class="input-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
              </span>
            </div>
          </div>
        </div>

        <div id="recovery-alert-box" style="display:none; padding:10px 15px; border-radius:6px; font-size:14px; margin-bottom:16px; text-align:center;"></div>

        <button type="submit" class="btn-submit" id="btn-recovery" style="background-color:#6b7280;">
          <span class="btn-text">Buscar Cuenta</span>
          <span class="btn-loading" style="display:none;">Verificando...</span>
        </button>
        <div style="text-align:center; margin-top:14px;">
          <a href="#" id="show-login" style="font-size:13px; color:var(--neutral-medium); text-decoration:none;">← Volver al inicio de sesión</a>
        </div>
      </form>
    </div>
  </div>

  <!-- script autenticación -->
  <script>
    const BASE_URL = "<?php echo BASE_URL; ?>";
  </script>
  <script src="<?php echo BASE_URL; ?>public/js/auth.js"></script>
  <script>
        document.querySelectorAll('.pwd-toggle-btn').forEach(function(btn) {
          btn.addEventListener('click', function() {
            var wrapper = btn.closest('.input-wrapper');
            var input = wrapper.querySelector('input[type="password"], input[type="text"]');
            var eyeOpen = btn.querySelector('.eye-open');
            var eyeClosed = btn.querySelector('.eye-closed');
            if (input.type === 'password') {
              input.type = 'text';
              eyeOpen.style.display = 'none';
              eyeClosed.style.display = '';
            } else {
              input.type = 'password';
              eyeOpen.style.display = '';
              eyeClosed.style.display = 'none';
            }
          });
        });
  </script>
  <script>
        $(document).ready(function() {
            setTimeout(function() {
                $('#session-alert').fadeOut('slow');
            }, 5000);
        });
  </script>
</body>
</html>
