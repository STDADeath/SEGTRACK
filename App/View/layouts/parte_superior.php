<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: ../Login/Login.php");
    exit();
}

$usuario = $_SESSION['usuario'];

if (!isset($usuario['Estado']) || $usuario['Estado'] !== 'Activo') {
    session_destroy();
    header("Location: ../Login/Login.php?error=inactivo");
    exit();
}

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

$baseUrl = rtrim(
    str_replace('\\', '/',
        dirname(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))))
    ), '/'
);

$fotoPerfil   = (!empty($usuario['FotoFuncionario']))
    ? $baseUrl . '/Public/' . htmlspecialchars($usuario['FotoFuncionario'])
    : $baseUrl . '/Public/img/undraw_profile.svg';

$fotoFallback = $baseUrl . '/Public/img/undraw_profile.svg';
?>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SEGTRACK | Soluciones Empresariales en Seguridad y Tecnología</title>
    <link href="../../../Public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="../../../Public/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../../../Public/css/graficas.css" rel="stylesheet">
    <link href="../../../Public/css/icono.css" rel="stylesheet">
</head>

<body id="page-top">
<div id="wrapper">

    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

        <a class="sidebar-brand d-flex align-items-center justify-content-center"
            href="../PersonalSeguridad/DasboardPersonalSeguridad.php">
            <div class="sidebar-brand-icon">
                <img src="../../../Public/img/LOGO_SEGTRACK-con.ico" alt="Logo" id="logo">
            </div>
        </a>

        <hr class="sidebar-divider">

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo">
                <i class="fas fa-fw fa-cog"></i><span>Funcionario</span>
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
                    <h6 class="collapse-header">Sedes:</h6>
                    <a class="collapse-item" href="../PersonalSeguridad/ListaInstituciones.php">Lista de Institutos</a>
                    <a class="collapse-item" href="../PersonalSeguridad/SedeLista.php">Lista de Sedes</a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities">
                <i class="fas fa-fw fa-wrench"></i><span>Control Bitácora</span>
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

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages">
                <i class="fas fa-fw fa-folder"></i><span>Tabla de ingreso</span>
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

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">

            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 shadow">

                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>

                <ul class="navbar-nav ml-auto">

                    <li class="nav-item dropdown no-arrow d-sm-none">
                        <a class="nav-link dropdown-toggle" href="#"
                            id="searchDropdown" data-toggle="dropdown">
                            <i class="fas fa-search fa-fw"></i>
                        </a>
                    </li>

                    <li class="nav-item dropdown no-arrow">

                        <a class="nav-link dropdown-toggle" href="#"
                            id="userDropdown" data-toggle="dropdown"
                            style="display:flex;align-items:center;gap:10px;">

                            <div style="text-align:right;line-height:1.3;">
                                <div style="font-size:13px;font-weight:600;color:#333;">
                                    <?= htmlspecialchars($usuario['NombreFuncionario']) ?>
                                </div>
                                <div style="font-size:11px;color:#888;">
                                    <?= htmlspecialchars($usuario['TipoRol']) ?>
                                </div>
                            </div>

                            <img class="img-profile rounded-circle"
                                style="width:42px;height:42px;object-fit:cover;
                                       border:2px solid #e0e0e0;flex-shrink:0;"
                                src="<?= $fotoPerfil ?>"
                                onerror="this.src='<?= $fotoFallback ?>'">
                        </a>

                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                            aria-labelledby="userDropdown">

                            <div class="text-center" style="padding:16px 12px 12px;">
                                <img src="<?= $fotoPerfil ?>"
                                    style="width:72px;height:72px;object-fit:cover;
                                           border-radius:50%;border:2px solid #4e73df;
                                           display:block;margin:0 auto;"
                                    onerror="this.src='<?= $fotoFallback ?>'">

                                <div style="margin-top:10px;font-size:13px;
                                            font-weight:600;color:#333;">
                                    <?= htmlspecialchars($usuario['NombreFuncionario']) ?>
                                </div>

                                <div style="margin-top:3px;font-size:11px;color:#777;">
                                    <?= htmlspecialchars($usuario['TipoRol']) ?>
                                </div>
                            </div>

                            <div class="dropdown-divider"></div>

                            <a class="dropdown-item" href="../Login/logout.php">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                Cerrar sesión
                            </a>

                        </div>
                    </li>

                </ul>
            </nav>

            <div class="container-fluid">