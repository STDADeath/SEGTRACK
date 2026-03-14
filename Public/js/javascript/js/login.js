$(document).ready(function() {

    function validarCorreo() {
        const correo   = $('#correo').val().trim();
        const errorCorreo = $('#errorCorreo');
        const inputBox = $('#correo').parent('.input-box');
        const regex    = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!correo) {
            errorCorreo.text('El correo es obligatorio');
            inputBox.addClass('error-border');
            return false;
        }
        if (!regex.test(correo)) {
            errorCorreo.text('Ingresa un correo válido');
            inputBox.addClass('error-border');
            return false;
        }
        errorCorreo.text('');
        inputBox.removeClass('error-border');
        return true;
    }

    function validarContrasena() {
        const pass     = $('#contrasena').val().trim();
        const errorContrasena = $('#errorContrasena');
        const inputBox = $('#contrasena').parent('.input-box');

        if (!pass) {
            errorContrasena.text('La contraseña es obligatoria');
            inputBox.addClass('error-border');
            return false;
        }
        if (pass.length < 6) {
            errorContrasena.text('Mínimo 6 caracteres');
            inputBox.addClass('error-border');
            return false;
        }
        errorContrasena.text('');
        inputBox.removeClass('error-border');
        return true;
    }

    $('#correo, #contrasena').on('input', function() {
        $(this).siblings('.error').text('');
        $(this).parent('.input-box').removeClass('error-border');
    });

    $('#correo').on('blur', validarCorreo);
    $('#contrasena').on('blur', validarContrasena);

    function realizarLogin() {

        if (!validarCorreo() || !validarContrasena()) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Por favor verifica los datos ingresados',
                confirmButtonColor: '#667eea'
            });
            return;
        }

        const correo     = $('#correo').val().trim();
        const contrasena = $('#contrasena').val().trim();

        Swal.fire({
            title: 'Iniciando sesión...',
            html: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: '../../../App/Controller/controladorlogin.php',
            method: 'POST',
            data: { correo: correo, contrasena: contrasena },
            dataType: 'json',

            success: function(response) {
                Swal.close();

                if (response.ok === true) {
                    // ✅ usa response.message que ya trae el nombre
                    Swal.fire({
                        icon: 'success',
                        title: '¡Bienvenido!',
                        text: response.message,
                        confirmButtonColor: '#667eea',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = response.redirect;
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de acceso',
                        text: response.message,
                        confirmButtonColor: '#f44336'
                    });
                }
            },

            error: function(xhr) {
                Swal.close();

                let mensaje = 'Error de conexión con el servidor.';

                if (xhr.status === 0)   mensaje = 'No se pudo conectar con el servidor.';
                if (xhr.status === 404) mensaje = 'Controlador no encontrado.';
                if (xhr.status === 500) mensaje = 'Error interno del servidor.';

                try {
                    const err = JSON.parse(xhr.responseText);
                    if (err.message) mensaje = err.message;
                } catch(e) {}

                // ✅ Sin código numérico visible
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: mensaje,
                    confirmButtonColor: '#f44336'
                });
            }
        });
    }

    $('#btnLogin').click(function(e) {
        e.preventDefault();
        realizarLogin();
    });

    $('#correo, #contrasena').keypress(function(e) {
        if (e.which === 13) {
            e.preventDefault();
            realizarLogin();
        }
    });
});