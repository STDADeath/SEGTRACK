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

    <!-- CSS para corregir el footer -->
    <style>
        html, body { height: 100%; margin: 0; padding: 0; }
        #wrapper { min-height: 100vh; display: flex; }
        .sidebar { min-height: 100vh; }
        #content-wrapper { display: flex; flex-direction: column; flex: 1; background-color: #f8f9fc; }
        #content { flex: 1 0 auto; }
        .sticky-footer { flex-shrink: 0; }
    </style>

</head>

<body id="page-top">

    <!-- Wrapper general -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Logo -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../Supervisor/DashboardSupervisor.php">
                <div class="sidebar-brand-icon">
                    <img src="../../../Public/img/LOGO_SEGTRACK-con.ico" alt="Logo" id="logo">
                </div>
            </a>

            <hr class="sidebar-divider">

            <!-- Menú del Supervisor -->
            
            <!-- Supervisión de Personal -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSuper1" aria-expanded="true" aria-controls="collapseSuper1">
                    <i class="fas fa-fw fa-user-shield"></i>
                    <span>Supervisión de Personal</span>
                </a>

                <div id="collapseSuper1" class="collapse" aria-labelledby="headingSuper1" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">

                        <h6 class="collapse-header">Reportes de Personal:</h6>
                        <a class="collapse-item" href="../Supervisor/ReporteDotacion.php">Reporte de Dotación</a>
                        <a class="collapse-item" href="../Supervisor/ReporteBitacora.php">Reporte de Bitácoras</a>
                        <a class="collapse-item" href="../Supervisor/ReporteVisitante.php">Reporte de Visitantes</a>

                        <div class="collapse-divider"></div>

                        <h6 class="collapse-header">Control de Turnos:</h6>
                        <a class="collapse-item" href="../Supervisor/GestionTurnos.php">Gestión de Turnos</a>
                        <a class="collapse-item" href="../Supervisor/AsistenciaPersonal.php">Asistencia Personal</a>

                    </div>
                </div>
            </li>

            <!-- Información de Registros -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSuper2" aria-expanded="true" aria-controls="collapseSuper2">
                    <i class="fas fa-fw fa-database"></i>
                    <span>Información de Registros</span>
                </a>

                <div id="collapseSuper2" class="collapse" aria-labelledby="headingSuper2" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">

                        <h6 class="collapse-header">Registros del Sistema:</h6>
                        <a class="collapse-item" href="../Supervisor/DispositivoSupervisor.php">Registros Dispositivos</a>
                        <a class="collapse-item" href="../Supervisor/ParqueaderoSupervisor.php">Registros Parqueadero</a>
                        <a class="collapse-item" href="../Supervisor/FuncionariosSupervisor.php">Registros Funcionarios</a>
                        <a class="collapse-item" href="../Supervisor/IngresosSupervisor.php">Registros Ingresos</a>

                    </div>
                </div>
            </li>

            <!-- Análisis y Estadísticas -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSuper3" aria-expanded="true" aria-controls="collapseSuper3">
                    <i class="fas fa-fw fa-chart-line"></i>
                    <span>Análisis y Estadísticas</span>
                </a>

                <div id="collapseSuper3" class="collapse" aria-labelledby="headingSuper3" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">

                        <h6 class="collapse-header">Reportes Analíticos:</h6>
                        <a class="collapse-item" href="../Supervisor/EstadisticasGenerales.php">Estadísticas Generales</a>
                        <a class="collapse-item" href="../Supervisor/RendimientoPersonal.php">Rendimiento Personal</a>
                        <a class="collapse-item" href="../Supervisor/IncidentesReportados.php">Incidentes Reportados</a>

                    </div>
                </div>
            </li>

            <!-- Separador -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Botón minimizar sidebar -->
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
                                <span class="badge badge-danger badge-counter">7+</span>
                            </a>
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">
                                    Centro de Notificaciones
                                </h6>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-warning">
                                            <i class="fas fa-exclamation-triangle text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">Hace 5 minutos</div>
                                        <span class="font-weight-bold">Bitácora pendiente de revisión</span>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-success">
                                            <i class="fas fa-check-circle text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">Hace 15 minutos</div>
                                        Turno completado correctamente
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-tshirt text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">Hace 30 minutos</div>
                                        Nueva dotación registrada
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-info">
                                            <i class="fas fa-user-clock text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">Hace 1 hora</div>
                                        Cambio de turno programado
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-danger">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">Hace 2 horas</div>
                                        Reporte mensual disponible
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-secondary">
                                            <i class="fas fa-shield-alt text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">Hace 3 horas</div>
                                        Personal de seguridad en posición
                                    </div>
                                </a>
                                <a class="dropdown-item text-center small text-gray-500" href="#">Ver todas las notificaciones</a>
                            </div>
                        </li>

                        <!-- Mensajes/Alertas rápidas -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-envelope fa-fw"></i>
                                <span class="badge badge-primary badge-counter">3</span>
                            </a>
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="messagesDropdown">
                                <h6 class="dropdown-header">
                                    Mensajes Recientes
                                </h6>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src="../../../Public/img/undraw_profile_1.svg" alt="...">
                                        <div class="status-indicator bg-success"></div>
                                    </div>
                                    <div class="font-weight-bold">
                                        <div class="text-truncate">Coordinación turno nocturno confirmada</div>
                                        <div class="small text-gray-500">Personal de Seguridad · Hace 15m</div>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src="../../../Public/img/undraw_profile_2.svg" alt="...">
                                        <div class="status-indicator bg-warning"></div>
                                    </div>
                                    <div>
                                        <div class="text-truncate">Solicitud de revisión de reporte</div>
                                        <div class="small text-gray-500">Administrador · Hace 1h</div>
                                    </div>
                                </a>
                                <a class="dropdown-item text-center small text-gray-500" href="#">Ver todos los mensajes</a>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Usuario -->
                        <li class="nav-item dropdown no-arrow">

                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Supervisor</span>

                                <img class="img-profile rounded-circle"
                                    src="../../../Public/img/undraw_profile.svg">
                            </a>

                            <!-- Dropdown usuario -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">

                                <a class="dropdown-item" href="../Supervisor/Perfil.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Perfil
                                </a>

                                <a class="dropdown-item" href="../Supervisor/Configuracion.php">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Configuración
                                </a>

                                <a class="dropdown-item" href="../Supervisor/RegistroActividad.php">
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
                <div class="container-fluid">