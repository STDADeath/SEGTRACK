<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Segtrack - Registro de Funcionarios</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/RegistroFun.css">
</head>
<body>
    <div class="container">
        <!-- Navbar -->
        <div class="navbar">
            <div class="logo">Segtrack</div>
            <div class="nav-links">
                <a href="../vistas/index.html">Quienes Somos?</a>
                <a href="../models/Parqueadero.php">Más</a>
                <a href="FuncionarioLista.php">Soporte</a>
            </div>
        </div>
        
        <!-- Contenido -->
        <div class="content">
            <div class="welcome-section">
                <img src="img/LOGO_SEGTRACK-con.ico" alt="Segtrack Logo">
                <h2>Registro de Funcionarios</h2>
                <p>El llevar un control preciso ayuda a facilitar el flujo del personal activo.</p>
            </div>
            
            <div class="form-section">
                <div class="form-box">
                    <h2>Registrar Personal</h2>

                    <!-- FORMULARIO -->
                    <form id="formFuncionario" action="../Controller/sede_institucion_funcionario_usuario/ControladorFuncionario.php" method="POST" novalidate>
                        <input type="hidden" name="accion" value="insertar">    

                        <!-- Nombre -->
                        <div class="input-box">
                            <i class='bx bxs-user'></i>
                            <input type="text" name="NombreFuncionario" id="NombreFuncionario" placeholder="Nombre completo" maxlength="30">
                        </div>

                        <!-- Documento -->
                        <div class="input-box">
                            <i class='bx bxs-id-card'></i>
                            <input type="text" name="DocumentoFuncionario" id="DocumentoFuncionario" placeholder="Documento" maxlength="12">
                        </div>

                        <!-- Correo -->
                        <div class="input-box">
                            <i class='bx bxs-envelope'></i>
                            <input type="email" name="CorreoFuncionario" id="CorreoFuncionario" placeholder="Correo electrónico" maxlength="80">
                        </div>

                        <!-- Teléfono -->
                        <div class="input-box">
                            <i class='bx bxs-phone'></i>
                            <input type="text" name="TelefonoFuncionario" id="TelefonoFuncionario" placeholder="Teléfono" maxlength="10">
                        </div>

                        <!-- Cargo -->
                        <div class="input-box">
                            <i class='bx bxs-briefcase'></i>
                            <select name="CargoFuncionario" id="CargoFuncionario" required>
                                <option value="" disabled selected>Seleccione un cargo</option>
                                <option value="Personal Seguridad">Personal Seguridad</option>
                                <option value="Funcionario">Funcionario</option>
                                <option value="Empresa">Empresa</option>
                            </select>
                        </div>

                        <!-- Sede -->
                        <div class="input-box">
                            <i class='bx bxs-building'></i>
                            <select name="IdSede" id="IdSede" required>
                                <option value="" disabled selected>Seleccione una sede</option>
                                <option value="17">Sede chapinero Segtrack</option>
                                <option value="18">Sede Soacha Segtrack</option>
                            </select>
                        </div>

                        <!-- Botón -->
                        <div class="btn-container">
                            <button class="btn-register" type="submit">
                                <i class='bx bx-user-plus'></i> Registrar
                            </button>
                        </div>
                    </form>
                    <!-- /FORMULARIO -->

                    <div class="links">
                        <a href="Instituto.php">Ver Registros</a>
                        <a href="login.html">iniciar Sesion</a>
                        <a href="FuncionarioLista.php">¿Lista de registros?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Librería SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Script externo -->
    <script src="../js/javascript/js/registro-funcionario.js" defer></script>

</body>
</html>
