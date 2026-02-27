<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: ../Login/Login.php");
    exit();
}

if ($_SESSION['usuario']['TipoRol'] !== 'Supervisor') {

    switch ($_SESSION['usuario']['TipoRol']) {

        case 'Administrador':
            header("Location: ../Administrador/DasboardAdministrador.php");
            break;

        case 'Personal Seguridad':
            header("Location: ../PersonalSeguridad/DasboardPersonalSeguridad.php");
            break;

        default:
            header("Location: ../Login/Login.php");
            break;
    }

    exit();
}

$nombreUsuario = htmlspecialchars($_SESSION['usuario']['NombreFuncionario'] ?? 'Usuario');
$rolUsuario    = htmlspecialchars($_SESSION['usuario']['TipoRol'] ?? 'Supervisor');
?>
<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>SEGTRACK | Supervisor</title>

    <link href="../../../Public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../../../Public/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../../../Public/css/graficas.css" rel="stylesheet">
    <link href="../../../Public/css/icono.css" rel="stylesheet">

    <style>
        #content-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        #content {
            flex: 1 0 auto;
        }
        .sticky-footer {
            flex-shrink: 0;
        }
    </style>

</head>
<body id="page-top">

<div id="wrapper">

    <!-- ================= SIDEBAR ================= -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

        <!-- Logo -->
        <a class="sidebar-brand d-flex align-items-center justify-content-center"
           href="../Supervisor/DasboardSupervisor.php">
            <div class="sidebar-brand-icon">
                <img src="../../../Public/img/LOGO_SEGTRACK-con.ico" alt="Logo" id="logo">
            </div>
        </a>

        <hr class="sidebar-divider">
        
<!-- Sección de Funcionario -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo">
        <i class="fas fa-fw fa-cog"></i>
        <span>Funcionario</span>
    </a>
    <div id="collapseTwo" class="collapse" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Funcionarios:</h6>
        
            <a class="collapse-item" href="../Supervisor/FuncionarioListaSUP.php">Lista de Funcionarios</a>

            <div class="collapse-divider"></div>

            <h6 class="collapse-header">Sedes:</h6>
              
            <a class="collapse-item" href="../Supervisor/SedeLista.php">Lista de Sedes</a>
        </div>
    </div>
</li>

        <hr class="sidebar-divider d-none d-md-block">

    </ul>
    <!-- ================= END SIDEBAR ================= -->

    <!-- ================= CONTENT ================= -->
    <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">

        <!-- ================= TOPBAR ================= -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 shadow">

            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>

            <ul class="navbar-nav ml-auto">

                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-toggle="dropdown">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                            <?php echo $nombreUsuario; ?> | <?php echo $rolUsuario; ?>
                        </span>
                        <img class="img-profile rounded-circle"
                             src="../../../Public/img/undraw_profile.svg">
                    </a>

                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                            Perfil
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="../Login/logout.php">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Cerrar sesión
                        </a>
                    </div>
                </li>

            </ul>
        </nav>
        <!-- ================= END TOPBAR ================= -->

        <!-- CONTENIDO DINÁMICO -->
        <div class="container-fluid">