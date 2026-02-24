<?php
// ==========================================================
// CONTROLADOR: Controladorinstituto.php
// Capa Controlador (MVC): recibe petición HTTP, valida datos,
// llama al modelo y devuelve siempre JSON.
// ==========================================================

error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

try {

    require_once __DIR__ . '/../Model/modeloinstituto.php';
    $institutoModel = new ModeloInstituto();

    $accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

    switch ($accion) {

        // ══════════════════════════════════════════════════════
        // ACCIÓN: registrar
        // Valida campos, fuerza EstadoInstitucion = 'Activo',
        // y llama a insertarInstituto() del modelo.
        // DireccionInstitucion: si viene vacía se guarda '' (no NULL)
        // porque la BD tiene restricción No nulo.
        // ══════════════════════════════════════════════════════
        case 'registrar':

            $nombre    = trim($_POST['NombreInstitucion']    ?? '');
            $nit       = trim($_POST['Nit_Codigo']           ?? '');
            $tipo      = trim($_POST['TipoInstitucion']      ?? '');
            // ✅ FIX: string vacío '' en lugar de null — BD es No nulo
            $direccion = trim($_POST['DireccionInstitucion'] ?? '');
            $estado    = 'Activo'; // Siempre Activo al registrar, forzado en servidor

            if (strlen($nombre) < 3) {
                throw new Exception("El nombre debe tener al menos 3 caracteres.");
            }
            if (!preg_match('/^[A-Za-zÁÉÍÓÚÑáéíóúñ ]+$/', $nombre)) {
                throw new Exception("El nombre solo puede contener letras y espacios.");
            }
            if (!ctype_digit($nit) || strlen($nit) !== 10) {
                throw new Exception("El NIT debe tener exactamente 10 dígitos numéricos.");
            }
            if (empty($tipo)) {
                throw new Exception("Debe seleccionar el tipo de institución.");
            }
            // Dirección: valida solo si se escribió algo
            if ($direccion !== '' && !preg_match('/^[A-Za-zÁÉÍÓÚÑáéíóúñ0-9 \-#,]+$/', $direccion)) {
                throw new Exception("La dirección contiene caracteres no permitidos.");
            }

            $data = [
                "NombreInstitucion"    => $nombre,
                "Nit_Codigo"           => $nit,
                "TipoInstitucion"      => $tipo,
                "EstadoInstitucion"    => $estado,
                "DireccionInstitucion" => $direccion  // '' si vino vacío
            ];

            $respuesta = $institutoModel->insertarInstituto($data);
            echo json_encode($respuesta);
            exit;


        // ══════════════════════════════════════════════════════
        // ACCIÓN: listar
        // Llama a listarInstitutos() y devuelve el array
        // envuelto en {"data": [...]} para DataTables o uso AJAX.
        // ══════════════════════════════════════════════════════
        case 'listar':
            $lista = $institutoModel->listarInstitutos();
            echo json_encode(["data" => $lista]);
            exit;


        // ══════════════════════════════════════════════════════
        // ACCIÓN: obtener
        // Busca un registro por ID y lo retorna completo.
        // Útil si se quiere precargar datos por AJAX puro.
        // ══════════════════════════════════════════════════════
        case 'obtener':
            $id = intval($_GET['IdInstitucion'] ?? $_POST['IdInstitucion'] ?? 0);

            if ($id <= 0) {
                echo json_encode(["ok" => false, "message" => "ID inválido."]);
                exit;
            }

            $institucion = $institutoModel->obtenerInstitutoPorId($id);
            echo json_encode($institucion
                ? ["ok" => true,  "data"    => $institucion]
                : ["ok" => false, "message" => "Institución no encontrada."]
            );
            exit;


        // ══════════════════════════════════════════════════════
        // ACCIÓN: editar
        // Valida todos los campos del modal de edición y llama
        // a editarInstituto() del modelo.
        // DireccionInstitucion: si viene vacía se guarda '' (no NULL)
        // porque la BD tiene restricción No nulo.
        // ══════════════════════════════════════════════════════
        case 'editar':

            $id        = intval(trim($_POST['IdInstitucion']          ?? 0));
            $nombre    = trim($_POST['NombreInstitucion']              ?? '');
            $nit       = trim($_POST['Nit_Codigo']                     ?? '');
            $tipo      = trim($_POST['TipoInstitucion']                ?? '');
            $estado    = trim($_POST['EstadoInstitucion']              ?? 'Activo');
            // ✅ FIX: string vacío '' en lugar de null — BD es No nulo
            $direccion = trim($_POST['DireccionInstitucion']           ?? '');

            if ($id <= 0) {
                throw new Exception("ID de institución inválido.");
            }
            if (strlen($nombre) < 3) {
                throw new Exception("El nombre debe tener al menos 3 caracteres.");
            }
            if (!preg_match('/^[A-Za-zÁÉÍÓÚÑáéíóúñ ]+$/', $nombre)) {
                throw new Exception("El nombre solo puede contener letras y espacios.");
            }
            if (empty($nit)) {
                throw new Exception("El NIT es obligatorio.");
            }
            if (empty($tipo)) {
                throw new Exception("Debe seleccionar el tipo de institución.");
            }
            if (!in_array($estado, ['Activo', 'Inactivo'])) {
                throw new Exception("El estado ingresado no es válido.");
            }
            if ($direccion !== '' && !preg_match('/^[A-Za-zÁÉÍÓÚÑáéíóúñ0-9 \-#,]+$/', $direccion)) {
                throw new Exception("La dirección contiene caracteres no permitidos.");
            }

            $dataEditar = [
                "IdInstitucion"        => $id,
                "NombreInstitucion"    => $nombre,
                "Nit_Codigo"           => $nit,
                "TipoInstitucion"      => $tipo,
                "EstadoInstitucion"    => $estado,
                "DireccionInstitucion" => $direccion  // '' si vino vacío
            ];

            $respuesta = $institutoModel->editarInstituto($dataEditar);
            echo json_encode($respuesta);
            exit;


        // ══════════════════════════════════════════════════════
        // ACCIÓN: cambiarEstado
        // Recibe el nuevo estado y llama a cambiarEstado() del modelo.
        // Solo toca el campo EstadoInstitucion, nada más.
        // Se activa desde el clic en el candado de la lista.
        // ══════════════════════════════════════════════════════
        case 'cambiarEstado':

            $id          = intval($_POST['IdInstitucion']   ?? 0);
            $nuevoEstado = trim($_POST['EstadoInstitucion'] ?? '');

            if ($id <= 0) {
                echo json_encode(["ok" => false, "message" => "ID inválido."]);
                exit;
            }
            if (!in_array($nuevoEstado, ['Activo', 'Inactivo'])) {
                echo json_encode(["ok" => false, "message" => "Estado no válido."]);
                exit;
            }

            $respuesta = $institutoModel->cambiarEstado($id, $nuevoEstado);
            echo json_encode($respuesta);
            exit;


        case '':
            echo json_encode(["ok" => false, "message" => "No se especificó ninguna acción."]);
            exit;

        default:
            echo json_encode([
                "ok"      => false,
                "message" => "Acción no reconocida: '" . htmlspecialchars($accion) . "'"
            ]);
            exit;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'ok'      => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
?>