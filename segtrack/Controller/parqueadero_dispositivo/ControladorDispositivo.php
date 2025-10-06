<?php
/**
 * ✅ Controlador de Dispositivos
 * Recibe las peticiones AJAX desde el formulario y llama al modelo correspondiente.
 */

header('Content-Type: application/json');

// 🔗 Requerimos los archivos necesarios
require_once __DIR__ . "/../../Core/conexion.php";
require_once __DIR__ . "/../../model/parqueadero_dispositivo/ModeloDispositivo.php";
require_once __DIR__ . "/../../libs/phpqrcode/qrlib.php";

// 🧠 Instanciamos el modelo con la conexión
$model = new DispositivoModel($conexion);

// 🔍 Verificación inicial (para probar acceso desde navegador)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['accion'])) {
    echo json_encode(['success' => false, 'message' => '✅ Controlador alcanzado correctamente']);
    exit;
}

// ⚙️ Procesamos las acciones enviadas por AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        /* ------------------------------------------
         ✅ Registrar un nuevo dispositivo
        ------------------------------------------ */
        case 'registrar':
            $tipo = $_POST['TipoDispositivo'] ?? '';
            $marca = $_POST['MarcaDispositivo'] ?? '';
            $otro = $_POST['OtroTipoDispositivo'] ?? '';
            $idFuncionario = $_POST['IdFuncionario'] ?? null;
            $idVisitante = $_POST['IdVisitante'] ?? null;

            // 🧩 Validaciones básicas
            if (empty($tipo) || empty($marca)) {
                echo json_encode(['success' => false, 'message' => 'Tipo y Marca son obligatorios']);
                exit;
            }

            if (($idFuncionario && $idVisitante) || (!$idFuncionario && !$idVisitante)) {
                echo json_encode(['success' => false, 'message' => 'Debe ingresar solo un ID: Funcionario o Visitante']);
                exit;
            }

            if ($tipo === 'Otro' && !empty($otro)) {
                $tipo = $otro;
            }

            // 🏷️ Generamos el código único del QR
            $codigoQR = $tipo . "_" . $marca . "_" . time();

            // 💾 Insertamos en la base de datos
            $resultado = $model->insertar($codigoQR, $tipo, $marca, $idFuncionario, $idVisitante);

            if ($resultado === true) {
                // 📁 Verificamos carpeta para guardar los QR
                $dir = __DIR__ . "/../../qrs/";
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }

                // 🖼️ Generamos la imagen QR
                $archivoQR = $dir . $codigoQR . ".png";
                QRcode::png($codigoQR, $archivoQR, QR_ECLEVEL_L, 10);

                echo json_encode(['success' => true, 'message' => '✅ Dispositivo registrado y QR generado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => $resultado]);
            }
            break;

        /* ------------------------------------------
         🗑️ Eliminar dispositivo
        ------------------------------------------ */
        case 'eliminar':
            $id = $_POST['id'] ?? null;

            if ($id && $model->eliminar($id)) {
                echo json_encode(['success' => true, 'message' => '✅ Dispositivo eliminado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => '❌ Error al eliminar el dispositivo']);
            }
            break;

        /* ------------------------------------------
         ✏️ Editar dispositivo
        ------------------------------------------ */
        case 'editar':
            $id = $_POST['IdDispositivo'] ?? null;
            $tipo = $_POST['TipoDispositivo'] ?? '';
            $marca = $_POST['MarcaDispositivo'] ?? '';
            $idFuncionario = $_POST['IdFuncionario'] ?? null;
            $idVisitante = $_POST['IdVisitante'] ?? null;

            $resultado = $model->editar($id, $tipo, $marca, $idFuncionario, $idVisitante);

            if ($resultado > 0) {
                echo json_encode(['success' => true, 'message' => '✅ Dispositivo actualizado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se realizaron cambios']);
            }
            break;

        /* ------------------------------------------
        🚫 Acción no válida
        ------------------------------------------ */
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
}
?>
