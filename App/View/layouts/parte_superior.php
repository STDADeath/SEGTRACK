<!DOCTYPE html>
<html lang="es">

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

    <!-- Hojas de estilo del tema y personalizadas -->
    <link href="../../../Public/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../../../Public/css/styles.css" rel="stylesheet">
    <link href="../../../Public/css/graficas.css" rel="stylesheet">
    <link href="../../../Public/css/icono.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Logo del sistema -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../PersonalSeguridad/DasboardPersonalSeguridad.php">
                <div class="sidebar-brand-icon">
                    <img src="../../../Public/img/LOGO_SEGTRACK-con.ico" alt="Logo" id="logo">
                </div>  
            </a>

            <hr class="sidebar-divider">

            <!-- Sección de Funcionario -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                    <i class="fas fa-fw fa-id-card"></i>
                    <span>Funcionario</span>
                </a>

                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        
                        <h6 class="collapse-header">Funcionarios:</h6>
                        <a class="collapse-item" href="../PersonalSeguridad/Funcionario.php">Registrar Funcionario</a>
                        <a class="collapse-item" href="../PersonalSeguridad/FuncionarioLista.php">Lista de Funcionarios</a>

                        <div class="collapse-divider"></div>

                        <h6 class="collapse-header">Parqueadero:</h6>
                        <a class="collapse-item" href="../PersonalSeguridad/Parqueadero.php">Ingreso Parqueadero</a>
                        <a class="collapse-item" href="../PersonalSeguridad/Vehiculolista.php">Lista Parqueadero</a>

                        <div class="collapse-divider"></div>

                        <h6 class="collapse-header">Dispositivos:</h6>
                        <a class="collapse-item" href="../PersonalSeguridad/Dispositivos.php">Registrar Dispositivo</a>
                        <a class="collapse-item" href="../PersonalSeguridad/DispositivoLista.php">Lista de Dispositivos</a>
                    </div>
                </div>
            </li>

            <!-- Control de Bitácora -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities" aria-expanded="true" aria-controls="collapseUtilities">
                    <i class="fas fa-fw fa-clipboard-list"></i>
                    <span>Control Bitácora</span>
                </a>

                <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">

                        <h6 class="collapse-header">Bitácora:</h6>
                        <a class="collapse-item" href="../PersonalSeguridad/Bitacora.php">Registro de Bitácora</a>
                        <a class="collapse-item" href="../PersonalSeguridad/BitacoraLista.php">Ingreso Bitácora</a>

                        <div class="collapse-divider"></div>

                        <h6 class="collapse-header">Dotación:</h6>
                        <a class="collapse-item" href="../PersonalSeguridad/Dotaciones.php">Ingresar Dotación</a>
                        <a class="collapse-item" href="../PersonalSeguridad/DotacionLista.php">Registro de Dotación</a>

                        <div class="collapse-divider"></div>

                        <h6 class="collapse-header">Visitantes:</h6>
                        <a class="collapse-item" href="../PersonalSeguridad/Visitante.php">Registro Visitante</a>
                        <a class="collapse-item" href="../PersonalSeguridad/VisitanteLista.php">Lista de Visitantes</a>

                    </div>
                </div>
            </li>

            <!-- Registro de ingreso -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages" aria-expanded="true" aria-controls="collapsePages">
                    <i class="fas fa-fw fa-qrcode"></i>
                    <span>Tabla de Ingreso</span>
                </a>

                <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">

                        <h6 class="collapse-header">Escanear QR:</h6>
                        <a class="collapse-item" href="../PersonalSeguridad/Ingreso.php">Registrar Entrada/Salida</a>

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

            <!-- Área principal de contenido -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Botón para abrir el sidebar en móvil -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Navegación a la derecha -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Notificaciones -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                <span class="badge badge-danger badge-counter">5+</span>
                            </a>
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">
                                    Centro de Notificaciones
                                </h6>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-user-check text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">Hace 10 minutos</div>
                                        <span class="font-weight-bold">Nuevo visitante registrado</span>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-success">
                                            <i class="fas fa-car text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">Hace 30 minutos</div>
                                        Vehículo ingresó al parqueadero
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-warning">
                                            <i class="fas fa-clipboard text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">Hace 1 hora</div>
                                        Bitácora pendiente de completar
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-info">
                                            <i class="fas fa-mobile-alt text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">Hace 2 horas</div>
                                        Dispositivo registrado correctamente
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-danger">
                                            <i class="fas fa-exclamation-triangle text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">Hace 3 horas</div>
                                        Dotación pendiente de entrega
                                    </div>
                                </a>
                                <a class="dropdown-item text-center small text-gray-500" href="#">Ver todas las notificaciones</a>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Usuario -->
                        <li class="nav-item dropdown no-arrow">

                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Personal de Seguridad</span>

                                <img class="img-profile rounded-circle"
                                     src="../../../Public/img/undraw_profile.svg">
                            </a>

                            <!-- Dropdown usuario -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                 aria-labelledby="userDropdown">

                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Perfil
                                </a>

                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Configuración
                                </a>

                                <a class="dropdown-item" href="#">
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

                <!-- Aquí comienza el contenido de cada página -->
                <div class="container-fluid">