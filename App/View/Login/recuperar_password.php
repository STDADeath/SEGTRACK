<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Segtrack</title>
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
                <img src="../../../public/img/LOGO_SEGTRACK-re-con.ico" alt="Segtrack Logo">
                <h2>BIENVENIDO</h2>
                <p>Ingresa tu correo electrónico para recuperar tu contraseña.</p>
            </div>

            <div class="form-section">
                <div class="form-box">
                    <h2>Recuperar Contraseña</h2>

                    <form id="formRecuperar">
                        <div class="input-box">
                            <i class='bx bxs-envelope'></i>
                            <input type="email" id="correoRecuperar" name="correo" placeholder="Correo electrónico" required autocomplete="off">
                            <small class="error" id="errorCorreoRecuperar"></small>
                        </div>

                        <div class="btn-container">
                            <button type="submit" class="btn-login">
                                <i class='bx bx-send'></i>
                                Enviar Token
                            </button>

                            <button type="button" class="btn-register" onclick="location.href='login.php'">
                                <i class='bx bx-arrow-back'></i>
                                Volver al Login
                            </button>
                        </div>
                    </form>

                    <div class="links">
                        <a href="login.php">¿Ya tienes tu token? Cambiar contraseña</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#formRecuperar').on('submit', function(e) {
                e.preventDefault();
                
                const correo = $('#correoRecuperar').val();
                
                $.ajax({
                    url: 'procesar_recuperacion.php',
                    type: 'POST',
                    data: { correo: correo },
                    dataType: 'json',
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Verificando...',
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
                                title: '¡Token Enviado!',
                                text: response.message,
                                confirmButtonText: 'Cambiar Contraseña'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = 'cambiar_password.php?email=' + encodeURIComponent(correo);
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
                    error: function() {
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