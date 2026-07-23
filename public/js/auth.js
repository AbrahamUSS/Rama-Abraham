document.ready(function() {
  $('#loginForm').on('submit', function (e) {
        e.preventDefault();

        var $btn = $('#btnLogin');
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
            success: function (response) {
                if (response.success) {
                    mostrarAlertaLogin('success', '<i class="fas fa-check-circle"></i> ' + response.mensaje);
                    setTimeout(function () {
                        window.location.href = response.redirect;
                    }, 1000);
                } else {
                    mostrarAlertaLogin('error', '<i class="fas fa-exclamation-circle"></i> ' + response.mensaje);
                    $btnText.show();
                    $btnLoading.hide();
                    $btn.prop('disabled', false);
                }
            },
            error: function () {
                mostrarAlertaLogin('error', '<i class="fas fa-times-circle"></i> Error de conexión con el servidor.');
                $btnText.show();
                $btnLoading.hide();
                $btn.prop('disabled', false);
            }
        });
    });
});

function mostrarAlertaLogin(tipo, mensaje) {
    var $alert = $('#login-alert');
    $alert.removeClass('login-alert-error login-alert-success');
    $alert.addClass(tipo === 'success' ? 'login-alert-success' : 'login-alert-error');
    $alert.html(mensaje);
    $alert.fadeIn(300);
}

function mostrarAlertaForm(tipo, mensaje) {
    var $alert = $('#form-alert');
    $alert.removeClass().addClass('alert alert-' + tipo);
    $alert.html(mensaje);
    $alert.fadeIn(300);

    if (tipo === 'success') {
        setTimeout(function () {
            $alert.fadeOut(300);
        }, 5000);
    }
}

function mostrarErrores(errores) {
    for (var campo in errores) {
        if (errores.hasOwnProperty(campo)) {
            $('#error-' + campo).text(errores[campo]);
            $('#' + campo).addClass('is-invalid');
        }
    }
}

function limpiarErrores() {
    $('[id^="error-"]').text('');
    $('.is-invalid').removeClass('is-invalid');
    $('#form-alert').hide();
}