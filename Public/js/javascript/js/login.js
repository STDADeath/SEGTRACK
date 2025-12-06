$(document).ready(function() {
            console.log('=== SISTEMA DE LOGIN INICIADO ===');
            console.log('jQuery:', $.fn.jquery);
            
            // ========================================
            // VALIDACIONES
            // ========================================
            function validarCorreo() {
                const correo = $('#correo').val().trim();
                const errorCorreo = $('#errorCorreo');
                const inputBox = $('#correo').parent('.input-box');
                const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
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
                const pass = $('#contrasena').val().trim();
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
            
            // Limpiar errores al escribir
            $('#correo, #contrasena').on('input', function() {
                $(this).siblings('.error').text('');
                $(this).parent('.input-box').removeClass('error-border');
            });
            
            // Validar al salir del campo
            $('#correo').on('blur', validarCorreo);
            $('#contrasena').on('blur', validarContrasena);
            
            // ========================================
            // FUNCIÓN DE LOGIN
            // ========================================
            function realizarLogin() {
                console.log('=== INICIANDO PROCESO DE LOGIN ===');
                
                if (!validarCorreo() || !validarContrasena()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Campos incompletos',
                        text: 'Por favor verifica los datos ingresados',
                        confirmButtonColor: '#667eea'
                    });
                    return;
                }
                
                const correo = $('#correo').val().trim();
                const contrasena = $('#contrasena').val().trim();
                
                console.log('Correo:', correo);
                
                // Loading
                Swal.fire({
                    title: 'Iniciando sesión...',
                    html: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
                
                // AJAX
                $.ajax({
                    url: '../../../App/Controller/controladorlogin.php',
                    method: 'POST',
                    data: {
                        correo: correo,
                        contrasena: contrasena
                    },
                    dataType: 'json',
                    
                    success: function(response) {
                        console.log('✓ Respuesta:', response);
                        Swal.close();
                        
                        if (response.ok === true) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Bienvenido!',
                                text: response.usuario.NombreFuncionario,
                                confirmButtonColor: '#667eea',
                                allowOutsideClick: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    console.log('Redirigiendo a:', response.redirect);
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
                    
                    error: function(xhr, status, error) {
                        console.error('❌ Error AJAX:', {
                            status: xhr.status,
                            statusText: xhr.statusText,
                            responseText: xhr.responseText,
                            error: error
                        });
                        
                        Swal.close();
                        
                        let mensaje = 'Error de conexión con el servidor';
                        
                        if (xhr.status === 0) {
                            mensaje = 'No se pudo conectar con el servidor. Verifica la URL del controlador.';
                        } else if (xhr.status === 404) {
                            mensaje = 'Controlador no encontrado (Error 404)';
                        } else if (xhr.status === 500) {
                            mensaje = 'Error interno del servidor';
                        }
                        
                        // Intentar parsear respuesta JSON
                        try {
                            const errorResponse = JSON.parse(xhr.responseText);
                            if (errorResponse.message) {
                                mensaje = errorResponse.message;
                            }
                        } catch (e) {
                            console.log('No se pudo parsear como JSON');
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: `<p>${mensaje}</p><small>Código: ${xhr.status}</small>`,
                            confirmButtonColor: '#f44336'
                        });
                    }
                });
            }
            
            // ========================================
            // EVENTOS
            // ========================================
            
            // Click en botón
            $('#btnLogin').click(function(e) {
                e.preventDefault();
                realizarLogin();
            });
            
            // Enter en inputs
            $('#correo, #contrasena').keypress(function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    realizarLogin();
                }
            });
            
            console.log('✓ Sistema listo');
        });