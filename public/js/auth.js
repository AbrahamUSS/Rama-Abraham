// Módulo de autenticación - Login, sesión y recuperación de contraseña

window.SchoolAuth = {
    getSession: function() {
        return window.currentSession || null;
    },
    checkGuard: function(requiredRole) {
        // La protección se ejecuta en el servidor con Security::verificarRol()
    },
    logout: function() {
        var url = (typeof BASE_URL !== 'undefined' && BASE_URL) ? BASE_URL : '/';
        if (!url.endsWith('/')) {
            url += '/';
        }
        window.location.href = url + 'auth/logout';
    }
};

// Formulario de login (AJAX)
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {
        var $loginForm = $('#login-form');
        if ($loginForm.length > 0) {
            $loginForm.on('submit', function(e) {
                e.preventDefault();
                var $btn = $('#btn-login');
                var $btnText = $btn.find('.btn-text');
                var $btnLoading = $btn.find('.btn-loading');
                $btnText.hide();
                $btnLoading.show();
                $btn.prop('disabled', true);
                $.ajax({
                    url: BASE_URL + 'auth/login',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            mostrarAlertaLogin('success', response.mensaje);
                            setTimeout(function() { window.location.href = response.redirect; }, 1000);
                        } else {
                            mostrarAlertaLogin('error', response.mensaje);
                            $btnText.show();
                            $btnLoading.hide();

                            if (response.locked && response.lock_seconds) {
                                iniciarCuentaRegresivaBloqueo(response.lock_seconds, $btn, $btnText);
                            } else {
                                $btn.prop('disabled', false);
                            }
                        }
                    },
                    error: function() {
                        mostrarAlertaLogin('error', 'Error de conexión con el servidor.');
                        $btnText.show();
                        $btnLoading.hide();
                        $btn.prop('disabled', false);
                    }
                });
            });
        }

        var lockoutTimer = null;
        function iniciarCuentaRegresivaBloqueo(segundos, $btn, $btnText) {
            if (lockoutTimer) clearInterval(lockoutTimer);
            var tiempoRestante = segundos;
            $btn.prop('disabled', true);

            lockoutTimer = setInterval(function() {
                var mins = Math.floor(tiempoRestante / 60);
                var secs = tiempoRestante % 60;
                var format = (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;
                $btnText.text('Acceso Bloqueado (' + format + ')').show();

                tiempoRestante--;
                if (tiempoRestante < 0) {
                    clearInterval(lockoutTimer);
                    $btnText.text('Iniciar Sesión');
                    $btn.prop('disabled', false);
                }
            }, 1000);
        }
    });
}

// Funciones auxiliares de alertas
function mostrarAlertaLogin(tipo, mensaje) {
    var $alert = document.getElementById('login-alert-box');
    if (!$alert) return;
    $alert.className = '';
    $alert.style.padding = '10px 15px';
    $alert.style.marginBottom = '15px';
    $alert.style.borderRadius = '6px';
    $alert.style.fontSize = '14px';
    $alert.style.display = 'block';
    if (tipo === 'success') {
        $alert.style.backgroundColor = '#d4edda';
        $alert.style.color = '#155724';
        $alert.style.border = '1px solid #c3e6cb';
    } else {
        $alert.style.backgroundColor = '#f8d7da';
        $alert.style.color = '#721c24';
        $alert.style.border = '1px solid #f5c6cb';
    }
    $alert.innerHTML = mensaje;
}

function mostrarAlertaForm(tipo, mensaje) {
    var $alert = document.getElementById('form-alert');
    if (!$alert) return;
    $alert.className = 'alert alert-' + tipo;
    $alert.innerHTML = mensaje;
    $alert.style.display = 'block';
    if (tipo === 'success') {
        setTimeout(function() { $alert.style.display = 'none'; }, 5000);
    }
}

function mostrarErrores(errores) {
    for (var campo in errores) {
        if (errores.hasOwnProperty(campo)) {
            var errorEl = document.getElementById('error-' + campo);
            var inputEl = document.getElementById(campo);
            if (errorEl) errorEl.textContent = errores[campo];
            if (inputEl) inputEl.classList.add('is-invalid');
        }
    }
}

// Recuperación de contraseña (dos pasos: verificar identidad → nueva contraseña)
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {
        var $showRecovery = $('#show-recovery');
        var $showLogin = $('#show-login');
        var $loginForm = $('#login-form');
        var $recoveryForm = $('#recovery-form');
        var $recoveryAlert = $('#recovery-alert-box');
        var $step1 = $('#recovery-step1');
        var $step2 = $('#recovery-step2');
        var recoveryUsername = '';

        $showRecovery.on('click', function(e) {
            e.preventDefault();
            $loginForm.hide();
            $recoveryForm.show();
        });

        $showLogin.on('click', function(e) {
            e.preventDefault();
            resetRecoveryForm();
            $loginForm.show();
        });

        $recoveryForm.on('submit', function(e) {
            e.preventDefault();
            var $btn = $('#btn-recovery');
            var $btnText = $btn.find('.btn-text');
            var $btnLoading = $btn.find('.btn-loading');
            var csrfToken = '';
            var csrfInput = $('input[name="csrf_token"]').first();
            if (csrfInput.length) csrfToken = csrfInput.val();

            if ($step2.is(':visible')) {
                // Paso 2: guardar nueva contraseña
                var nueva = $('#recovery-new-pass').val();
                var confirmar = $('#recovery-confirm-pass').val();
                if (!nueva || !confirmar) {
                    mostrarAlertaRecovery('error', 'Complete ambos campos de contraseña.');
                    return;
                }
                if (nueva.length < 4) {
                    mostrarAlertaRecovery('error', 'La contraseña debe tener al menos 4 caracteres.');
                    return;
                }
                if (nueva !== confirmar) {
                    mostrarAlertaRecovery('error', 'Las contraseñas no coinciden.');
                    return;
                }
                $btnText.hide();
                $btnLoading.text('Guardando...').show();
                $btn.prop('disabled', true);
                $.ajax({
                    url: BASE_URL + 'auth/recuperarPassword',
                    type: 'POST',
                    data: {
                        dni: $('#recovery-dni').val(),
                        correo: $('#recovery-email').val(),
                        password_nueva: nueva,
                        password_confirmar: confirmar,
                        csrf_token: csrfToken
                    },
                    dataType: 'json',
                    success: function(response) {
                        $btnText.show();
                        $btnLoading.hide();
                        $btn.prop('disabled', false);
                        if (response.success) {
                            mostrarAlertaRecovery('success', '¡Contraseña actualizada! Será redirigido al login en 3 segundos.');
                            setTimeout(function() {
                                resetRecoveryForm();
                                $loginForm.show();
                                if (response.data && response.data.username) {
                                    $('#username').val(response.data.username).focus();
                                }
                            }, 3000);
                        } else {
                            mostrarAlertaRecovery('error', response.mensaje);
                        }
                    },
                    error: function() {
                        mostrarAlertaRecovery('error', 'Error de conexión con el servidor.');
                        $btnText.show();
                        $btnLoading.hide();
                        $btn.prop('disabled', false);
                    }
                });
            } else {
                // Paso 1: verificar identidad (DNI + correo)
                var dni = $.trim($('#recovery-dni').val());
                var correo = $.trim($('#recovery-email').val());
                if (!dni || !correo) {
                    mostrarAlertaRecovery('error', 'Ingrese su DNI y correo electrónico.');
                    return;
                }

                if (!/^\d{8}$/.test(dni)) {
                    mostrarAlertaRecovery('error', 'El DNI debe contener exactamente 8 números.');
                    return;
                }
                $btnText.hide();
                $btnLoading.text('Verificando...').show();
                $btn.prop('disabled', true);
                $.ajax({
                    url: BASE_URL + 'auth/recuperarPassword',
                    type: 'POST',
                    data: { dni: dni, correo: correo, csrf_token: csrfToken },
                    dataType: 'json',
                    success: function(response) {
                        $btnText.show();
                        $btnLoading.hide();
                        $btn.prop('disabled', false);
                        if (response.success && response.step === 'identity_ok') {
                            recoveryUsername = response.data.username;
                            $('#recovery-username-display').text('Usuario: ' + response.data.username);
                            $step1.hide();
                            $step2.show();
                            $('#recovery-new-pass').focus();
                            $btnText.text('Guardar Nueva Contraseña');
                            mostrarAlertaRecovery('success', response.mensaje);
                        } else {
                            mostrarAlertaRecovery('error', response.mensaje);
                        }
                    },
                    error: function() {
                        mostrarAlertaRecovery('error', 'Error de conexión con el servidor.');
                        $btnText.show();
                        $btnLoading.hide();
                        $btn.prop('disabled', false);
                    }
                });
            }
        });

        function resetRecoveryForm() {
            $recoveryForm.hide();
            $recoveryAlert.hide();
            $recoveryForm[0].reset();
            $step1.show();
            $step2.hide();
            recoveryUsername = '';
            $('#btn-recovery .btn-text').text('Buscar Cuenta');
        }

        function mostrarAlertaRecovery(tipo, mensaje) {
            $recoveryAlert.css('display', 'block');
            if (tipo === 'success') {
                $recoveryAlert.css({ backgroundColor: '#d4edda', color: '#155724', border: '1px solid #c3e6cb' });
            } else {
                $recoveryAlert.css({ backgroundColor: '#f8d7da', color: '#721c24', border: '1px solid #f5c6cb' });
            }
            $recoveryAlert.html(mensaje);
        }
    });
}

function limpiarErrores() {
    var errorEls = document.querySelectorAll('[id^="error-"]');
    errorEls.forEach(function(el) { el.textContent = ''; });
    var invalidEls = document.querySelectorAll('.is-invalid');
    invalidEls.forEach(function(el) { el.classList.remove('is-invalid'); });
    var formAlert = document.getElementById('form-alert');
    if (formAlert) formAlert.style.display = 'none';
}
