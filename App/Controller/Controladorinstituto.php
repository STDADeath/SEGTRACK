<?php
// ==========================================================
// CONTROLADOR: Controladorinstituto.php
// ==========================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

try {

    require_once __DIR__ . '/../Model/modeloinstituto.php';

    $institutoModel = new ModeloInstituto();

    // IMPORTANTE: Primero POST, luego GET
    $accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

    switch ($accion) {

        // REGISTRAR
        case 'registrar':
            $nombre = trim($_POST['NombreInstitucion'] ?? '');
            $nit = trim($_POST['Nit_Codigo'] ?? '');
            $tipo = trim($_POST['TipoInstitucion'] ?? '');
            $estado = trim($_POST['EstadoInstitucion'] ?? 'Activo');

            if (strlen($nombre) < 3) {
                throw new Exception("El nombre debe tener al menos 3 caracteres.");
            }
            if (!ctype_digit($nit) || strlen($nit) !== 10) {
                throw new Exception("El NIT debe tener exactamente 10 dígitos.");
            }
            if (empty($tipo)) {
                throw new Exception("Debe seleccionar el tipo de institución.");
            }

            $data = [
                "NombreInstitucion" => $nombre,
                "Nit_Codigo" => $nit,
                "TipoInstitucion" => $tipo,
                "EstadoInstitucion" => $estado
            ];

            $respuesta = $institutoModel->insertarInstituto($data);
            echo json_encode($respuesta);
            exit;

        // LISTAR
        case 'listar':
            $lista = $institutoModel->listarInstitutos();
            echo json_encode(["data" => $lista]);
            exit;

        // OBTENER (para edición)
        case 'obtener':
            $id = intval($_GET['IdInstitucion'] ?? $_POST['IdInstitucion'] ?? 0);
            
            if ($id <= 0) {
                echo json_encode([
                    "ok" => false,
                    "message" => "ID inválido."
                ]);
                exit;
            }

            $institucion = $institutoModel->obtenerInstitutoPorId($id);
            
            if ($institucion) {
                echo json_encode([
                    "ok" => true,
                    "data" => $institucion
                ]);
            } else {
                echo json_encode([
                    "ok" => false,
                    "message" => "Institución no encontrada."
                ]);
            }
            exit;

        // EDITAR
        case 'editar':
            $id = intval($_POST['IdInstitucion'] ?? 0);
            $nombre = trim($_POST['NombreInstitucion'] ?? '');
            $nit = trim($_POST['Nit_Codigo'] ?? '');
            $tipo = trim($_POST['TipoInstitucion'] ?? '');
            $estado = trim($_POST['EstadoInstitucion'] ?? '');

            if ($id <= 0) {
                throw new Exception("ID inválido.");
            }
            if (strlen($nombre) < 3) {
                throw new Exception("El nombre debe tener al menos 3 caracteres.");
            }
            if (empty($nit)) {
                throw new Exception("El NIT es obligatorio.");
            }

            $dataEditar = [
                "IdInstitucion" => $id,
                "NombreInstitucion" => $nombre,
                "Nit_Codigo" => $nit,
                "TipoInstitucion" => $tipo,
                "EstadoInstitucion" => $estado
            ];

            $respuesta = $institutoModel->editarInstituto($dataEditar);
            echo json_encode($respuesta);
            exit;

        // CAMBIAR ESTADO (toggle rápido Activo/Inactivo)
        case 'cambiarEstado':
            $id = intval($_POST['IdInstitucion'] ?? 0);
            $nuevoEstado = trim($_POST['EstadoInstitucion'] ?? '');

            if ($id <= 0) {
                echo json_encode([
                    "ok" => false,
                    "message" => "ID inválido."
                ]);
                exit;
            }

            if (!in_array($nuevoEstado, ['Activo', 'Inactivo'])) {
                echo json_encode([
                    "ok" => false,
                    "message" => "Estado no válido."
                ]);
                exit;
            }

            $respuesta = $institutoModel->cambiarEstado($id, $nuevoEstado);
            echo json_encode($respuesta);
            exit;

        // Acción vacía o no válida
        case '':
            echo json_encode([
                "ok" => false,
                "message" => "No se especificó ninguna acción."
            ]);
            exit;

        default:
            echo json_encode([
                "ok" => false,
                "message" => "Acción no válida: '" . $accion . "'"
            ]);
            exit;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
?>