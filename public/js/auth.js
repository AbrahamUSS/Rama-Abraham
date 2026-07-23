/**
 * =====================================================================
 * AUTH.JS - Módulo de Autenticación del Sistema
 * IEP Corazón de Jesús
 * =====================================================================
 * 
 * Este archivo maneja:
 *  1. window.SchoolAuth - Utilidades de sesión, guardia y logout
 *  2. Formulario de login (AJAX) - Solo se ejecuta si jQuery y el form existen
 *  3. Funciones auxiliares de alertas y errores de formulario
 */

// =====================================================================
// 1. UTILIDADES DE SESIÓN (Sin dependencia de jQuery)
// =====================================================================
window.SchoolAuth = {
    /**
     * Retorna los datos de la sesión actual inyectados desde PHP
     * via window.currentSession en las vistas admin.php y docente.php
     */
    getSession: function() {
        return window.currentSession || null;
    },

    /**
     * Guardia de seguridad del lado del cliente.
     * La protección real se ejecuta en el servidor con Security::verificarRol().
     */
    checkGuard: function(requiredRole) {
        // No-op: el servidor ya protege las vistas
    },

    /**
     * Cierra la sesión redirigiendo al endpoint de logout del servidor.
     */
    logout: function() {
        var url = typeof BASE_URL !== 'undefined' ? BASE_URL : '/Sistema_Gestion_IE/';
        window.location.href = url + 'auth/logout';
    }
};

// =====================================================================
// 2. FORMULARIO DE LOGIN (Solo se ejecuta si jQuery y el formulario existen)
// =====================================================================
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {
        var $loginForm = $('#login-form');

        // Solo adjuntar el handler si el formulario existe en la página actual
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
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1000);
                        } else {
                            mostrarAlertaLogin('error', response.mensaje);
                            $btnText.show();
                            $btnLoading.hide();
                            $btn.prop('disabled', false);
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
    });
}

// =====================================================================
// 3. FUNCIONES AUXILIARES DE ALERTAS
// =====================================================================

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
        setTimeout(function() {
            $alert.style.display = 'none';
        }, 5000);
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

function limpiarErrores() {
    var errorEls = document.querySelectorAll('[id^="error-"]');
    errorEls.forEach(function(el) { el.textContent = ''; });
    var invalidEls = document.querySelectorAll('.is-invalid');
    invalidEls.forEach(function(el) { el.classList.remove('is-invalid'); });
    var formAlert = document.getElementById('form-alert');
    if (formAlert) formAlert.style.display = 'none';
}