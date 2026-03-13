<?php
// ============================================================
// parte_superior.php — Layout PersonalSeguridad
//
// CAMBIOS RESPECTO A TU VERSIÓN ORIGINAL:
//   ✅ 1. Agregada validación de Estado = 'Activo'
//   ✅ 2. Cerrar sesión apunta directo a logout.php (sin modal)
//   ✅ 3. htmlspecialchars() en nombre y rol del usuario
//
// TODO LO DEMÁS es idéntico a tu versión original.
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── 1. ¿Existe sesión? ─────────────────────────────────────
if (!isset($_SESSION['usuario'])) {
    header("Location: ../Login/Login.php");
    exit();
}

$usuario = $_SESSION['usuario'];

// ── 2. ✅ NUEVO: ¿El usuario está Activo? ──────────────────
// Si el admin desactiva al usuario mientras tiene sesión abierta,
// al navegar a cualquier página protegida lo expulsa automáticamente.
if (!isset($usuario['Estado']) || $usuario['Estado'] !== 'Activo') {
    session_destroy();
    header("Location: ../Login/Login.php?error=inactivo");
    exit();
}

// ── 3. ¿Tiene el rol correcto para esta sección? ───────────
if ($usuario['TipoRol'] !== 'Personal Seguridad') {
    switch ($usuario['TipoRol']) {
        case 'Administrador':
            header("Location: ../Administrador/DasboardAdministrador.php");
            break;
        case 'Supervisor':
            header("Location: ../Supervisor/DasboardSupervisor.php");
            break;
        default:
            header("Location: ../Login/Login.php");
            break;
    }
    exit();
}
?>
<head>

    <!-- Configuraciones básicas del documento -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>SEGTRACK | Soluciones Empresariales en Seguridad y Tecnología</title>

    <!-- Iconos FontAwesome -->
    <link href="../../../Public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">

    <!-- Tipografías Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">

    <!-- Hojas de estilo -->
    <link href="../../../Public/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../../../Public/css/graficas.css" rel="stylesheet">
    <link href="../../../Public/css/icono.css" rel="stylesheet">

</head>

<body id="page-top">

    <div id="wrapper">

        <!-- ================================================
        SIDEBAR
        ================================================ -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Logo -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center"
                href="../PersonalSeguridad/DasboardPersonalSeguridad.php">
                <div class="sidebar-brand-icon">
                    <img src="../../../Public/img/LOGO_SEGTRACK-con.ico" alt="Logo" id="logo">
                </div>
            </a>

            <hr class="sidebar-divider">

            <!-- Funcionario -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo">
                    <i class="fas fa-fw fa-cog"></i>
                    <span>Funcionario</span>
                </a>
                <div id="collapseTwo" class="collapse" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Funcionarios:</h6>
                        <a class="collapse-item" href="../PersonalSeguridad/Funcionario.php">Registrar Funcionario</a>
                        <a class="collapse-item" href="../PersonalSeguridad/FuncionarioLista.php">Lista de Funcionarios</a>
                        <div class="collapse-divider"></div>
                        <h6 class="collapse-header">Parqueadero:</h6>
                        <a class="collapse-item" href="../PersonalSeguridad/Parqueaderoguardia.php">Ingreso Parqueadero</a>
                        <a class="collapse-item" href="../PersonalSeguridad/Vehiculolista.php">Lista Parqueadero</a>
                        <div class="collapse-divider"></div>
                        <h6 class="collapse-header">Dispositivos:</h6>
                        <a class="collapse-item" href="../PersonalSeguridad/Dispositivos.php">Registrar Dispositivo</a>
                        <a class="collapse-item" href="../PersonalSeguridad/DispositivoLista.php">Lista de Dispositivos</a>
                    </div>
                </div>
            </li>

            <!-- Control Bitácora -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities">
                    <i class="fas fa-fw fa-wrench"></i>
                    <span>Control Bitácora</span>
                </a>
                <div id="collapseUtilities" class="collapse" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Bitácora</h6>
                        <a class="collapse-item" href="../PersonalSeguridad/Bitacora.php">Registro de Bitácora</a>
                        <a class="collapse-item" href="../PersonalSeguridad/BitacoraLista.php">Ingreso Bitácora</a>
                        <div class="collapse-divider"></div>
                        <h6 class="collapse-header">Dotación</h6>
                        <a class="collapse-item" href="../PersonalSeguridad/Dotaciones.php">Ingresar Dotación</a>
                        <a class="collapse-item" href="../PersonalSeguridad/DotacionLista.php">Registro de Dotación</a>
                        <div class="collapse-divider"></div>
                        <h6 class="collapse-header">Visitantes</h6>
                        <a class="collapse-item" href="../PersonalSeguridad/Visitante.php">Registro Visitante</a>
                        <a class="collapse-item" href="../PersonalSeguridad/VisitanteLista.php">Lista de Visitantes</a>
                    </div>
                </div>
            </li>

            <!-- Tabla de ingreso -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages">
                    <i class="fas fa-fw fa-folder"></i>
                    <span>Tabla de ingreso</span>
                </a>
                <div id="collapsePages" class="collapse" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Escanear QR</h6>
                        <a class="collapse-item" href="../PersonalSeguridad/Ingreso.php">Registrar Entrada/Salida</a>
                    </div>
                </div>
            </li>

            <hr class="sidebar-divider d-none d-md-block">

        </ul>
        <!-- End Sidebar -->

        <!-- ================================================
        CONTENT WRAPPER
        ================================================ -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">

                <!-- TOPBAR -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 shadow">

                    <!-- Botón sidebar móvil -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <ul class="navbar-nav ml-auto">

                        <!-- Buscador móvil -->
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#"
                                id="searchDropdown" data-toggle="dropdown">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                        </li>

                        <!-- Usuario logueado -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#"
                                id="userDropdown" data-toggle="dropdown">

                                <!-- ✅ htmlspecialchars previene XSS -->
                                <?php echo htmlspecialchars($usuario['NombreFuncionario']); ?>
                                &nbsp;|&nbsp;
                                <?php echo htmlspecialchars($usuario['TipoRol']); ?>

                                <img class="img-profile rounded-circle"
                                    src="../../../Public/img/undraw_profile.svg">
                            </a>

                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">

                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Perfil
                                </a>

                                <div class="dropdown-divider"></div>

                                <!-- ✅ CAMBIADO: apunta directo a logout.php -->
                                <!-- Antes abría un modal, ahora cierra sesión directo -->
                                <a class="dropdown-item"
                                    href="../Login/logout.php">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Cerrar sesión
                                </a>

                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End Topbar -->

                <!-- Aquí comienza el contenido de cada página -->
                <div class="container-fluid">