<?php
require_once __DIR__ . "/../Controller/sede_institucion_funcionario_usuario/ControladorFuncionarioLista.php";
$controller = new FuncionarioController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'];

    switch ($accion) {
        case 'registrar':
            $resultado = $controller->registrarFuncionario($_POST);
            echo json_encode(["success" => $resultado]);
            break;

        case 'actualizar':
            $resultado = $controller->actualizarFuncionario($_POST);
            echo json_encode(["success" => $resultado]);
            break;

        case 'eliminar':
            $resultado = $controller->eliminarFuncionario($_POST['id']);
            echo json_encode(["success" => $resultado]);
            break;
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'];

    switch ($accion) {
        case 'listar':
            $datos = $controller->obtenerFuncionarios();
            echo json_encode($datos);
            break;

        case 'obtener':
            $dato = $controller->obtenerFuncionario($_GET['id']);
            echo json_encode($dato);
            break;
    }
    exit;
}
?>
