<?php
// ==============================
// 🔐 CONTROL DE SESIÓN
// ==============================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: ../Login/Login.php");
    exit();
}

$usuario = $_SESSION['usuario'];

// ==============================
// 🚫 VALIDAR ESTADO
// ==============================
if (!isset($usuario['Estado']) || $usuario['Estado'] !== 'Activo') {
    session_destroy();
    header("Location: ../Login/Login.php?error=inactivo");
    exit();
}

// ==============================
// 🔄 VALIDAR ROL
// ==============================
if ($usuario['TipoRol'] !== 'Supervisor') {
    switch ($usuario['TipoRol']) {
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

// ==============================
// 🌐 BASE URL
// ==============================
$baseUrl = "http://" . $_SERVER['HTTP_HOST'] . "/SEGTRACK";

// ==============================
// 🖼️ FOTO PERFIL (FIX REAL)
// ==============================
$fotoFallback = $baseUrl . "/Public/img/undraw_profile.svg";
$fotoPerfil = $fotoFallback;

if (!empty($usuario['FotoFuncionario']) && $usuario['FotoFuncionario'] !== 'NULL') {

    // Ruta que viene de la BD
    $rutaBD = $usuario['FotoFuncionario'];

    // Ruta física en el servidor
    $rutaFisica = $_SERVER['DOCUMENT_ROOT'] . "/SEGTRACK/Public/" . $rutaBD;

    // Validar si existe el archivo
    if (file_exists($rutaFisica)) {
        $fotoPerfil = $baseUrl . "/Public/" . $rutaBD;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>SEGTRACK | Supervisor</title>

    <link href="../../../Public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../../../Public/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../../../Public/css/graficas.css" rel="stylesheet">
    <link href="../../../Public/css/icono.css" rel="stylesheet">
</head>

<body id="page-top">
<div id="wrapper">

<!-- SIDEBAR -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center"
        href="../Supervisor/DasboardSupervisor.php">
        <div class="sidebar-brand-icon">
            <img src="../../../Public/img/LOGO_SEGTRACK-con.ico" id="logo">
        </div>
    </a>

    <hr class="sidebar-divider">

    <li class="nav-item">
        <a class="nav-link" href="../Supervisor/FuncionarioListaSUP.php">
            <i class="fas fa-fw fa-user"></i>
            <span>Funcionarios</span>
        </a>
    </li>

    <hr class="sidebar-divider">

   <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities">
                <i class="fas fa-fw fa-wrench"></i>
                <span>Control Bitácora</span>
            </a>
            <div id="collapseUtilities" class="collapse" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Bitácora</h6>
                    <a class="collapse-item" href="../Supervisor/BitacoraSup.php">Registro de Bitácora</a>
                    <a class="collapse-item" href="../Supervisor/BitacoraListaSUP.php">Ingreso Bitácora</a>
                    <div class="collapse-divider"></div>
                    <h6 class="collapse-header">Dotación</h6>
                    <a class="collapse-item" href="../Supervisor/DotacionSup.php">Ingresar Dotación</a>
                    <a class="collapse-item" href="../Supervisor/DotacionListaSup.php">Registro de Dotación</a>
                </div>
            </div>
        </li>

    <hr class="sidebar-divider">

    <li class="nav-item">
        <a class="nav-link" href="../Supervisor/VehiculoSupervisor.php">
            <i class="fas fa-car"></i>
            <span>Vehiculos</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <li class="nav-item">
        <a class="nav-link" href="../Supervisor/DispositivoSupervisor.php">
            <i class="fas fa-laptop"></i>
            <span>Dispositivos</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <li class="nav-item">
        <a class="nav-link" href="../Supervisor/VisitanteListaSUP.php">
            <i class="fas fa-fw fa-users"></i>
            <span>Visitantes</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <li class="nav-item">
        <a class="nav-link" href="../Supervisor/SedeLista.php">
            <i class="fas fa-fw fa-building"></i>
            <span>Sedes</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <li class="nav-item">
        <a class="nav-link" href="../Supervisor/InstitutosListaSUP.php">
            <i class="fas fa-fw fa-university"></i>
            <span>Institutos</span>
        </a>
    </li>

</ul>

<!-- CONTENIDO -->
<div id="content-wrapper" class="d-flex flex-column">
<div id="content">

<!-- TOPBAR -->
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 shadow">

<ul class="navbar-nav ml-auto">

<li class="nav-item dropdown no-arrow">

<a class="nav-link dropdown-toggle"
    href="#"
    id="userDropdown"
    data-toggle="dropdown"
    role="button"
    aria-haspopup="true"
    aria-expanded="false"
    style="display:flex;align-items:center;gap:10px;">

    <div style="text-align:right;">
        <div style="font-size:13px;font-weight:600;">
            <?= htmlspecialchars($usuario['NombreFuncionario']) ?>
        </div>
        <div style="font-size:11px;color:#777;">
            <?= htmlspecialchars($usuario['TipoRol']) ?>
        </div>
    </div>

    <img class="img-profile rounded-circle"
        style="width:42px;height:42px;object-fit:cover;border:2px solid #e0e0e0;"
        src="<?= $fotoPerfil ?>"
        onerror="this.src='<?= $fotoFallback ?>'">
</a>

<div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
     aria-labelledby="userDropdown">

    <div class="dropdown-divider"></div>

    <a class="dropdown-item" href="../Login/logout.php">
        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
        Cerrar sesión
    </a>

</div>

</li>
</ul>
</nav>

<!-- 🔥 IMPORTANTE -->
<div class="container-fluid">