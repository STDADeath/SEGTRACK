<?php
require_once __DIR__ . '/../../Model/sede_institucion_funcionario_usuario/ModeloFuncionario.php';

$modelo = new ModeloFuncionario();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? 'insertar'; // Valor por defecto

    $datos = [
        'NombreFuncionario' => $_POST['NombreFuncionario'] ?? '',
        'DocumentoFuncionario' => $_POST['DocumentoFuncionario'] ?? '',
        'CorreoFuncionario' => $_POST['CorreoFuncionario'] ?? '',
        'TelefonoFuncionario' => $_POST['TelefonoFuncionario'] ?? '',
        'CargoFuncionario' => $_POST['CargoFuncionario'] ?? '',
        'IdSede' => $_POST['IdSede'] ?? ''
    ];

    switch ($accion) {
        case 'insertar':
            $resultado = $modelo->insertarFuncionario($datos);
            break;

        case 'actualizar':
            $id = $_POST['IdFuncionario'] ?? null;
            $resultado = $modelo->actualizarFuncionario($id, $datos);
            break;

        case 'eliminar':
            $id = $_POST['IdFuncionario'] ?? null;
            $resultado = $modelo->eliminarFuncionario($id);
            break;

        case 'filtrar':
            $id = $_POST['IdFuncionario'] ?? null;
            $resultado = $modelo->filtrarFuncionarioPorId($id);
            break;

        default:
            $resultado = ['error' => '⚠️ Acción no reconocida.'];
            break;
    }

    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
        Swal.fire({
            icon: '" . (isset($resultado['error']) ? 'error' : 'success') . "',
            title: 'Resultado',
            html: `" . print_r($resultado, true) . "`,
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#7066e0'
        });
    </script>";
}
?>
