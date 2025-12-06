<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña - Segtrack</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
     <link rel="stylesheet" href="../../../public/css/loginn.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <div class="logo">Segtrack</div>
            <div class="nav-links">
                <a href="#">Quienes Somos</a>
                <a href="#">Mas</a>             
            </div>
        </div>

        <div class="content">
            <div class="welcome-section">
                <img src="../../Public/img/LOGO_SEGTRACK-re-con.ico" alt="Segtrack Logo">
                <h2>BIENVENIDO</h2>
                <p>Ingresa el token que recibiste por correo y tu nueva contraseña.</p>
            </div>

            <div class="form-section">
                <div class="form-box">
                    <h2>Cambiar Contraseña</h2>

                    <form id="formCambiarPassword">
                        <input type="hidden" id="correoUsuario" name="correo" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
                        
                        <div class="input-box">
                            <i class='bx bx-key'></i>
                            <input type="text" id="token" name="token" placeholder="Token de verificación (6 dígitos)" required autocomplete="off" maxlength="6">
                            <small class="error" id="errorToken"></small>
                        </div>

                        <div class="input-box">
                            <i class='bx bxs-lock-alt'></i>
                            <input type="password" id="nuevaPassword" name="nueva_password" placeholder="Nueva Contraseña" required autocomplete="off">
                            <small class="error" id="errorPassword"></small>
                        </div>

                        <div class="input-box">
                            <i class='bx bxs-lock-alt'></i>
                            <input type="password" id="confirmarPassword" name="confirmar_password" placeholder="Confirmar Contraseña" required autocomplete="off">
                            <small class="error" id="errorConfirmar"></small>
                        </div>

                        <div class="btn-container">
                            <button type="submit" class="btn-login">
                                <i class='bx bx-check'></i>
                                Cambiar Contraseña
                            </button>

                            <button type="button" class="btn-register" onclick="location.href='login.php'">
                                <i class='bx bx-arrow-back'></i>
                                Volver al Login
                            </button>
                        </div>
                    </form>

                    <div class="links">
                        <a href="recuperar_password.php">¿No recibiste el token? Reenviar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Validar que las contraseñas coincidan en tiempo real
            $('#confirmarPassword').on('keyup', function() {
                const nuevaPass = $('#nuevaPassword').val();
                const confirmarPass = $(this).val();
                
                if (nuevaPass !== confirmarPass && confirmarPass !== '') {
                    $('#errorConfirmar').text('Las contraseñas no coinciden').show();
                } else {
                    $('#errorConfirmar').text('').hide();
                }
            });

            // Validar token solo números
            $('#token').on('keypress', function(e) {
                if (e.which < 48 || e.which > 57) {
                    e.preventDefault();
                }
            });

            $('#formCambiarPassword').on('submit', function(e) {
                e.preventDefault();
                
                const correo = $('#correoUsuario').val();
                const token = $('#token').val();
                const nuevaPassword = $('#nuevaPassword').val();
                const confirmarPassword = $('#confirmarPassword').val();
                
                // Validaciones
                if (!correo) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Error',
                        text: 'No se encontró el correo electrónico. Por favor vuelve a solicitar el token.'
                    });
                    return;
                }

                if (token.length !== 6) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Token inválido',
                        text: 'El token debe tener 6 dígitos'
                    });
                    return;
                }
                
                if (nuevaPassword.length < 6) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Contraseña débil',
                        text: 'La contraseña debe tener al menos 6 caracteres'
                    });
                    return;
                }
                
                if (nuevaPassword !== confirmarPassword) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Error',
                        text: 'Las contraseñas no coinciden'
                    });
                    return;
                }
                
                $.ajax({
                    url: 'procesar_cambio_password.php',
                    type: 'POST',
                    data: {
                        correo: correo,
                        token: token,
                        nueva_password: nuevaPassword
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Cambiando contraseña...',
                            text: 'Por favor espera',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Contraseña Cambiada!',
                                text: response.message,
                                confirmButtonText: 'Ir al Login'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = 'login.php';
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al procesar la solicitud'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>