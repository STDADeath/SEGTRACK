<!DOCTYPE html>
<html lang="es">

<head>

    <!-- Configuración general -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>SEGTRACK | Administrador</title>

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
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../Administrador/DashboardAdministrador.php">
                <div class="sidebar-brand-icon">
                    <img src="../../../Public/img/LOGO_SEGTRACK-con.ico" alt="Logo" id="logo">
                </div>
            </a>

            <hr class="sidebar-divider">

            <!-- Menú del Administrador -->
            
            <!-- Gestión de Instituciones -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAdmin1" aria-expanded="true" aria-controls="collapseAdmin1">
                    <i class="fas fa-fw fa-building"></i>
                    <span>Gestión de Instituciones</span>
                </a>

                <div id="collapseAdmin1" class="collapse" aria-labelledby="headingAdmin1" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">

                        <h6 class="collapse-header">Instituciones:</h6>
                        <a class="collapse-item" href="../Administrador/Instituto.php">Agregar Instituciones</a>
                        <a class="collapse-item" href="../Administrador/InstitutoLista.php">Ver Institución</a>

                        <div class="collapse-divider"></div>

                    </div>
                </div>
            </li>

            <!-- Gestión de Sedes -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAdmin2" aria-expanded="true" aria-controls="collapseAdmin2">
                    <i class="fas fa-fw fa-map-marker-alt"></i>
                    <span>Gestión de Sedes</span>
                </a>

                <div id="collapseAdmin2" class="collapse" aria-labelledby="headingAdmin2" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">

                        <h6 class="collapse-header">Sedes:</h6>
                        <a class="collapse-item" href="../Administrador/sede.php">Agregar Sedes</a>
                        <a class="collapse-item" href="../Administrador/SedeLista.php">Ver Sede</a>
                        

                        <div class="collapse-divider"></div>

                    </div>
                </div>
            </li>

            <!-- Gestión de Usuarios -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAdmin3" aria-expanded="true" aria-controls="collapseAdmin3">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Gestión de Usuarios</span>
                </a>

                <div id="collapseAdmin3" class="collapse" aria-labelledby="headingAdmin3" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">

                        <h6 class="collapse-header">Usuarios del Sistema:</h6>
                        <a class="collapse-item" href="../PersonalSeguridad/Funcionario.php">Registrar funcionarios</a>
                        <a class="collapse-item" href="../PersonalSeguridad/FuncionarioLista.php">Lista funcionarios</a>
                        <a class="collapse-item" href="../Login/Usuario.php">Contraseña Usuario</a>
                        <a class="collapse-item" href="../Administrador/AsignarPermisos.php">Lista de Usuarios</a>

                        <div class="collapse-divider"></div>

                    </div>
                </div>
            </li>

            <!-- Configuración del Sistema -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAdmin4" aria-expanded="true" aria-controls="collapseAdmin4">
                    <i class="fas fa-fw fa-cogs"></i>
                    <span>Configuración</span>
                </a>

                <div id="collapseAdmin4" class="collapse" aria-labelledby="headingAdmin4" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">

                        <h6 class="collapse-header">Sistema:</h6>
                        <a class="collapse-item" href="../Administrador/ConfiguracionGeneral.php">Configuración General</a>
                        <a class="collapse-item" href="../Administrador/RolesPermisos.php">Roles y Permisos</a>
                        <a class="collapse-item" href="../Administrador/AuditoriaLogs.php">Auditoría y Logs</a>

                        <div class="collapse-divider"></div>

                    </div>
                </div>
            </li>

            <!-- Reportes -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAdmin5" aria-expanded="true" aria-controls="collapseAdmin5">
                    <i class="fas fa-fw fa-chart-bar"></i>
                    <span>Reportes</span>
                </a>

                <div id="collapseAdmin5" class="collapse" aria-labelledby="headingAdmin5" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">

                        <h6 class="collapse-header">Informes:</h6>
                        <a class="collapse-item" href="../Administrador/ReporteUsuarios.php">Reporte de Usuarios</a>
                        <a class="collapse-item" href="../Administrador/ReporteActividad.php">Actividad del Sistema</a>
                        <a class="collapse-item" href="../Administrador/ReporteSedes.php">Reporte de Sedes</a>

                    </div>
                </div>
            </li>

            <!-- Separador -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Botón minimizar sidebar (opcional) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Contenido principal -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Botón para abrir el menú lateral en móvil -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <ul class="navbar-nav ml-auto">

                        <!-- Notificaciones -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                <span class="badge badge-danger badge-counter">3+</span>
                            </a>
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">
                                    Centro de Notificaciones
                                </h6>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">Hace 2 horas</div>
                                        <span class="font-weight-bold">Nuevo usuario registrado</span>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-success">
                                            <i class="fas fa-building text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">Hace 5 horas</div>
                                        Nueva institución agregada
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-warning">
                                            <i class="fas fa-exclamation-triangle text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">Hace 1 día</div>
                                        Actualización de sistema disponible
                                    </div>
                                </a>
                                <a class="dropdown-item text-center small text-gray-500" href="#">Ver todas las notificaciones</a>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Usuario -->
                        <li class="nav-item dropdown no-arrow">

                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Administrador</span>

                                <img class="img-profile rounded-circle"
                                    src="../../../Public/img/undraw_profile.svg">
                            </a>

                            <!-- Dropdown usuario -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">

                                <a class="dropdown-item" href="../Administrador/Perfil.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Perfil
                                </a>

                                <a class="dropdown-item" href="../Administrador/Configuracion.php">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Configuración
                                </a>

                                <a class="dropdown-item" href="../Administrador/AuditoriaLogs.php">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Registro de Actividad
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
                <div class="container-fluid"></div>