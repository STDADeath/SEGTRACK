<?php
session_start();
require_once __DIR__ . '/../../Model/sede_institucion_funcionario_usuario/modelosede.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipoSede = trim($_POST['TipoSede'] ?? '');
    $ciudad = trim($_POST['Ciudad'] ?? '');
    $idInstitucion = trim($_POST['IdInstitucion'] ?? '');

    if ($tipoSede === '' || $ciudad === '' || $idInstitucion === '') {
        echo "<script>alert('Por favor llena todos los campos'); window.history.back();</script>";
        exit;
    }

    $sedeModel = new ModeloSede();
    $insertado = $sedeModel->insertar($tipoSede, $ciudad, $idInstitucion);

    if ($insertado) {
        echo "<script>alert('Sede agregada correctamente'); window.location.href='../../View/SedeUsuario.php';</script>";
    } else {
        echo "<script>alert('Error al agregar la sede'); window.history.back();</script>";
    }
}
?>
