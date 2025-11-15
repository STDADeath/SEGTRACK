<!DOCTYPE html>
<html lang="es">

<head>

    <!-- Configuración general -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>SEGTRACK | Supervisor</title>

    <!-- Iconos -->
    <link href="../../../Public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">

    <!-- Tipografías -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">

    <!-- CSS del template -->
    <link href="../../../Public/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../../../Public/css/styles.css" rel="stylesheet">
    <link href="../../../Public/css/graficas.css" rel="stylesheet">
    <link href="../../../Public/css/icono.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Wrapper general -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Logo -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../Supervisor/DasboardSupervisor.php">
                <div class="sidebar-brand-icon">
                    <img src="../../../Public/img/LOGO_SEGTRACK-con.ico" alt="Logo" id="logo">
                </div>
            </a>

            <hr class="sidebar-divider">

            <!-- Menú del Supervisor -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSuper1">
                    <i class="fas fa-fw fa-user-shield"></i>
                    <span>Supervisión de Personal</span>
                </a>

                <div id="collapseSuper1" class="collapse" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">

                        <h6 class="collapse-header">Personal Seguridad</h6>
                        <a class="collapse-item" href="">Reporte De Dotacion</a>
                        <a class="collapse-item" href="">Reporte De Bitacoras</a>
                           <a class="collapse-item" href="">Reporte De Visitante</a>

                        <div class="collapse-divider"></div>

                    </div>
                </div>
            </li>

            <!-- Gestión -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSuper2">
                    <i class="fas fa-fw fa-clipboard-list"></i>
                    <span>Informacion De Registros </span>
                </a>

                <div id="collapseSuper2" class="collapse" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">

                        <h6 class="collapse-header">Registros Seguridad</h6>
                        <a class="collapse-item" href="../Supervisor/DispositivoSupervisor.php">Registros Dipositivos</a>
                        <a class="collapse-item" href="../Supervisor/ParqueaderoSupervisor.php">Registros Parqueadero</a>
                          <a class="collapse-item" href="">Registros Funcionarios</a>
                        <a class="collapse-item" href="">Registros Ingresos</a>

                    </div>
                </div>
            </li>

            <!-- Separador -->
            <hr class="sidebar-divider d-none d-md-block">

        </ul>
        <!-- End Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Contenido principal -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 shadow">

                    <!-- Botón para abrir el menú lateral en móvil -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <ul class="navbar-nav ml-auto">

                        <!-- Usuario -->
                        <li class="nav-item dropdown no-arrow">

                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-toggle="dropdown">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Supervisor</span>

                                <img class="img-profile rounded-circle"
                                    src="../../../Public/img/undraw_profile.svg">
                            </a>

                            <!-- Dropdown usuario -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">

                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Perfil
                                </a>

                                <div class="dropdown-divider"></div>

                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Cerrar sesión
                                </a>

                            </div>

                        </li>

                    </ul>

                </nav>
                <!-- End Topbar -->

                <!-- Inicio del contenido dinámico -->
                <div class="container-fluid">
