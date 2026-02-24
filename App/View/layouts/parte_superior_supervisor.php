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
?>
<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<title>SEGTRACK | Administrador</title>

<link href="../../../Public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
<link href="../../../Public/css/sb-admin-2.min.css" rel="stylesheet">
<link href="../../../Public/css/graficas.css" rel="stylesheet">
<link href="../../../Public/css/icono.css" rel="stylesheet">

<style>
#content-wrapper{
    min-height:100vh;
    display:flex;
    flex-direction:column;
}
#content{
    flex:1 0 auto;
}
.sticky-footer{
    flex-shrink:0;
}
</style>

</head>
<body id="page-top">

<div id="wrapper">

<!-- ================= SIDEBAR ================= -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

<!-- Logo -->
<a class="sidebar-brand d-flex align-items-center justify-content-center"
   href="../Administrador/DasboardAdministrador.php">
    <div class="sidebar-brand-icon">
        <img src="../../../Public/img/LOGO_SEGTRACK-con.ico" alt="Logo" id="logo">
    </div>
</a>

<hr class="sidebar-divider">

<!-- ========================================= -->
<!-- 🏢 MODULO INSTITUCIONES -->
<!-- ========================================= -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseInst">
        <i class="fas fa-building"></i>
        <span>Gestión Instituciones</span>
    </a>

    <div id="collapseInst" class="collapse" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
<?php
// ===============================
// 🔐 CONTROL DE SESIÓN
// ===============================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: ../Login/Login.php");
    exit;
}

// Datos usuario
$nombreUsuario = htmlspecialchars($_SESSION['usuario']['NombreFuncionario'] ?? 'Usuario');
$rolUsuario    = htmlspecialchars($_SESSION['usuario']['TipoRol'] ?? 'Administrador');
?>
<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<title>SEGTRACK | Administrador</title>

<link href="../../../Public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
<link href="../../../Public/css/sb-admin-2.min.css" rel="stylesheet">
<link href="../../../Public/css/graficas.css" rel="stylesheet">
<link href="../../../Public/css/icono.css" rel="stylesheet">

<style>
#content-wrapper{
    min-height:100vh;
    display:flex;
    flex-direction:column;
}
#content{
    flex:1 0 auto;
}
.sticky-footer{
    flex-shrink:0;
}
</style>

</head>
<body id="page-top">

<div id="wrapper">

<!-- ================= SIDEBAR ================= -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

<!-- Logo -->
<a class="sidebar-brand d-flex align-items-center justify-content-center"
   href="../Administrador/DasboardAdministrador.php">
    <div class="sidebar-brand-icon">
        <img src="../../../Public/img/LOGO_SEGTRACK-con.ico" alt="Logo" id="logo">
    </div>
</a>

<hr class="sidebar-divider">

<!-- ========================================= -->
<!-- 🏢 MODULO INSTITUCIONES -->
<!-- ========================================= -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseInst">
        <i class="fas fa-building"></i>
        <span>Gestión Instituciones</span>
    </a>

    <div id="collapseInst" class="collapse" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="../Administrador/InstitutoLista.php">Lista Instituciones</a>
        </div>
    </div>
</li>

<!-- ========================================= -->
<!-- 📍 MODULO SEDES -->
<!-- ========================================= -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSede">
        <i class="fas fa-map-marker-alt"></i>
        <span>Gestión Sedes</span>
    </a>

    <div id="collapseSede" class="collapse" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="../Administrador/SedeLista.php">Lista Sedes</a>
        </div>
    </div>
</li>

<!-- ========================================= -->
<!-- 👥 MODULO USUARIOS -->
<!-- ========================================= -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUser">
        <i class="fas fa-users"></i>
        <span>Gestión Usuarios</span>
    </a>

    <div id="collapseUser" class="collapse" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">

            <h6 class="collapse-header">Personal:</h6>
            <a class="collapse-item" href="../Administrador/FuncionariosADM.php">
                Registrar Funcionario
            </a>
            <a class="collapse-item" href="../Administrador/FuncionarioListaADM.php">
                Lista Funcionarios
            </a>

            <div class="collapse-divider"></div>

            <h6 class="collapse-header">Credenciales:</h6>
            <a class="collapse-item" href="../Login/Usuario.php">
                Generar Credenciales
            </a>
            <a class="collapse-item" href="../Login/UsuariosLista.php">
                Lista Usuarios
            </a>

        </div>
    </div>
</li>

<!-- ========================================= -->
<!-- 📊 MODULO REGISTROS -->
<!-- ========================================= -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReg">
        <i class="fas fa-clipboard-list"></i>
        <span>Registros</span>
    </a>

    <div id="collapseReg" class="collapse" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">

            <a class="collapse-item" href="../Supervisor/DispositivoSupervisor.php">
                Registros Dispositivos
            </a>
            <a class="collapse-item" href="../Supervisor/ParqueaderoSupervisor.php">
                Registros Parqueadero
            </a>
            <a class="collapse-item" href="../Administrador/InstitutoLista.php">
                Registros Instituto
            </a>

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

<a class="dropdown-item" href="../Administrador/Perfil.php">
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

<!-- CONTENIDO DINÁMICO -->
<div class="container-fluid">

            <a class="collapse-item" href="../Administrador/InstitutoLista.php">Lista Instituciones</a>
        </div>
    </div>
</li>

<!-- ========================================= -->
<!-- 📍 MODULO SEDES -->
<!-- ========================================= -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSede">
        <i class="fas fa-map-marker-alt"></i>
        <span>Gestión Sedes</span>
    </a>

    <div id="collapseSede" class="collapse" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="../Administrador/Sede.php">Agregar Sede</a>
            <a class="collapse-item" href="../Administrador/SedeLista.php">Lista Sedes</a>
        </div>
    </div>
</li>

<!-- ========================================= -->
<!-- 👥 MODULO USUARIOS -->
<!-- ========================================= -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUser">
        <i class="fas fa-users"></i>
        <span>Gestión Usuarios</span>
    </a>

    <div id="collapseUser" class="collapse" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">

            <h6 class="collapse-header">Personal:</h6>
            <a class="collapse-item" href="../Administrador/FuncionariosADM.php">
                Registrar Funcionario
            </a>
            <a class="collapse-item" href="../Administrador/FuncionarioListaADM.php">
                Lista Funcionarios
            </a>

            <div class="collapse-divider"></div>

            <h6 class="collapse-header">Credenciales:</h6>
            <a class="collapse-item" href="../Login/Usuario.php">
                Generar Credenciales
            </a>
            <a class="collapse-item" href="../Login/UsuariosLista.php">
                Lista Usuarios
            </a>

        </div>
    </div>
</li>

<!-- ========================================= -->
<!-- 📊 MODULO REGISTROS -->
<!-- ========================================= -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReg">
        <i class="fas fa-clipboard-list"></i>
        <span>Registros</span>
    </a>

    <div id="collapseReg" class="collapse" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">

            <a class="collapse-item" href="../Supervisor/DispositivoSupervisor.php">
                Registros Dispositivos
            </a>
            <a class="collapse-item" href="../Supervisor/ParqueaderoSupervisor.php">
                Registros Parqueadero
            </a>
            <a class="collapse-item" href="../Administrador/InstitutoLista.php">
                Registros Instituto
            </a>

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
<?php echo $_SESSION['usuario']['NombreFuncionario']; ?>
|
<?php echo $_SESSION['usuario']['TipoRol']; ?>
</span>

<img class="img-profile rounded-circle"
     src="../../../Public/img/undraw_profile.svg">

</a>

<div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">

<a class="dropdown-item" href="../Administrador/Perfil.php">
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

<!-- CONTENIDO DINÁMICO -->
<div class="container-fluid">
