// Modulo reutilizable de cambio de contraseña
(function() {

  function renderChangePasswordForm(container) {
    if (!container) return;

    const csrfToken = document.querySelector('input[name="csrf_token"]');
    const tokenValue = csrfToken ? csrfToken.value : '';

    container.innerHTML = `
      <div class="card card-accent" style="margin-top: 24px;">
        <div class="card-header">
          <h3 class="card-title">
            <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            Cambiar Contraseña
          </h3>
        </div>
        <form id="change-password-form" class="form-layout" style="display: flex; flex-direction: column; gap: 14px;">
          <div class="form-group">
            <label class="form-label-desc">Contraseña Actual</label>
            <div class="pwd-input-wrapper">
              <input type="password" id="pwd-actual" class="control-input" placeholder="Ingrese su contraseña actual" required>
              <button type="button" class="pwd-toggle-btn" tabindex="-1" aria-label="Mostrar contraseña">
                <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
              </button>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label-desc">Nueva Contraseña</label>
            <div class="pwd-input-wrapper">
              <input type="password" id="pwd-nueva" class="control-input" placeholder="Mínimo 4 caracteres" required>
              <button type="button" class="pwd-toggle-btn" tabindex="-1" aria-label="Mostrar contraseña">
                <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
              </button>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label-desc">Confirmar Nueva Contraseña</label>
            <div class="pwd-input-wrapper">
              <input type="password" id="pwd-confirmar" class="control-input" placeholder="Repita la nueva contraseña" required>
              <button type="button" class="pwd-toggle-btn" tabindex="-1" aria-label="Mostrar contraseña">
                <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
              </button>
            </div>
          </div>
          <div id="pwd-alert" style="display:none; padding: 10px; border-radius: 6px; font-size: 14px; text-align: center;"></div>
          <button type="submit" class="btn btn-primary" style="width: 100%;">Actualizar Contraseña</button>
        </form>
      </div>
    `;

    const form = document.getElementById('change-password-form');
    const alertBox = document.getElementById('pwd-alert');

    container.querySelectorAll('.pwd-toggle-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var wrapper = btn.closest('.pwd-input-wrapper');
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

    function showAlert(type, msg) {
      if (type === 'success') {
        alertBox.style.backgroundColor = '#D1FAE5';
        alertBox.style.color = '#065F46';
        alertBox.style.border = '1px solid #A7F3D0';
      } else {
        alertBox.style.backgroundColor = '#FEE2E2';
        alertBox.style.color = '#991B1B';
        alertBox.style.border = '1px solid #FECACA';
      }
      alertBox.textContent = msg;
      alertBox.style.display = 'block';
    }

    form.addEventListener('submit', function(e) {
      e.preventDefault();
      alertBox.style.display = 'none';

      var actual = document.getElementById('pwd-actual').value;
      var nueva = document.getElementById('pwd-nueva').value;
      var confirmar = document.getElementById('pwd-confirmar').value;

      if (!actual || !nueva || !confirmar) {
        showAlert('error', 'Todos los campos son obligatorios.');
        return;
      }
      if (nueva.length < 4) {
        showAlert('error', 'La contraseña debe tener al menos 4 caracteres.');
        return;
      }
      if (nueva !== confirmar) {
        showAlert('error', 'Las contraseñas nuevas no coinciden.');
        return;
      }

      var url = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + 'auth/cambiarPassword';
      var body = new URLSearchParams();
      body.append('csrf_token', tokenValue);
      body.append('password_actual', actual);
      body.append('password_nueva', nueva);
      body.append('password_confirmar', confirmar);

      fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
        credentials: 'same-origin'
      })
        .then(function(res) { return res.json(); })
        .then(function(data) {
          if (data.success) {
            showAlert('success', data.mensaje);
            form.reset();
          } else {
            showAlert('error', data.mensaje || 'Error al cambiar la contraseña.');
          }
        })
        .catch(function() {
          showAlert('error', 'Error de conexión con el servidor.');
        });
    });
  }

  window.PasswordModule = {
    renderChangePasswordForm: renderChangePasswordForm
  };

})();
