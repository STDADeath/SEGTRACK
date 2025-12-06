<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Segtrack - Login y Registro</title>
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
                <p>Estamos para ayudarte. Sé parte de nuestro grupo.</p>
            </div>

            <div class="form-section">
                <div class="form-box">
                    <h2>Login</h2>

                    <div id="loginContainer">
                        <div class="input-box">
                            <i class='bx bxs-user'></i>
                            <input type="email" id="correo" placeholder="Correo electrónico" autocomplete="off">
                            <small class="error" id="errorCorreo"></small>
                        </div>

                        <div class="input-box">
                            <i class='bx bxs-lock-alt'></i>
                            <input type="password" id="contrasena" placeholder="Contraseña" autocomplete="off">
                            <small class="error" id="errorContrasena"></small>
                        </div>

                        <div class="btn-container">
                            <button type="button" class="btn-login" id="btnLogin">
                                <i class='bx bx-log-in'></i>
                                Iniciar Sesión
                            </button>

                            <button type="button" class="btn-register" onclick="location.href='RegistroFun.html'">
                                <i class='bx bx-user-plus'></i>
                                Crear Cuenta
                            </button>
                        </div>
                    </div>

                    <div class="links">
                        <a href="recuperar_password.php">¿Recuperar contraseña?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../../Public/js/javascript/js/Login.js"></script>
</body>

</html>