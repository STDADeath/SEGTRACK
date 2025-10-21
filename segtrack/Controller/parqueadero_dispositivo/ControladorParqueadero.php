<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

file_put_contents(__DIR__ . '/debug_log.txt', "\n" . date('Y-m-d H:i:s') . " === INICIO ===\n", FILE_APPEND);

try {
    $ruta_modelo = __DIR__ . '/../../model/parqueadero_dispositivo/ModeloParqueadero.php';
    if (!file_exists($ruta_modelo)) {
        throw new Exception("Modelo no encontrado: $ruta_modelo");
    }

    require_once $ruta_modelo;
    file_put_contents(__DIR__ . '/debug_log.txt', "Modelo cargado correctamente\n", FILE_APPEND);

    $modelo = new ModeloParqueadero();
    file_put_contents(__DIR__ . '/debug_log.txt', "Instancia de ModeloParqueadero creada\n", FILE_APPEND);

    // Captura acción
    $accion = $_POST['accion'] ?? '';
    file_put_contents(__DIR__ . '/debug_log.txt', "Acción: $accion | Método: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
    file_put_contents(__DIR__ . '/debug_log.txt', "POST: " . json_encode($_POST, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

    // =============================
    // 📌 REGISTRAR VEHÍCULO
    // =============================
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'registrar') {
        file_put_contents(__DIR__ . '/debug_log.txt', "Iniciando registro de vehículo\n", FILE_APPEND);

        $TipoVehiculo = trim($_POST['TipoVehiculo'] ?? '');
        $PlacaVehiculo = trim($_POST['PlacaVehiculo'] ?? '');
        $DescripcionVehiculo = trim($_POST['DescripcionVehiculo'] ?? '');
        $TarjetaPropiedad = trim($_POST['TarjetaPropiedad'] ?? '');
        $FechaParqueadero = trim($_POST['FechaParqueadero'] ?? date('Y-m-d H:i:s'));
        $IdSede = trim($_POST['IdSede'] ?? '');

        if (empty($TipoVehiculo) || empty($PlacaVehiculo) || empty($IdSede)) {
            $error = "Campos obligatorios faltantes";
            file_put_contents(__DIR__ . '/debug_log.txt', "❌ $error\n", FILE_APPEND);
            echo json_encode(['success' => false, 'message' => $error]);
            exit;
        }

        $resultado = $modelo->registrarVehiculo($TipoVehiculo, $PlacaVehiculo, $DescripcionVehiculo, $TarjetaPropiedad, $FechaParqueadero, $IdSede);
        echo json_encode($resultado);
        exit;
    }

    // =============================
    // 📌 ACTUALIZAR VEHÍCULO
    // =============================
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'actualizar') {
        file_put_contents(__DIR__ . '/debug_log.txt', "Iniciando actualización de vehículo\n", FILE_APPEND);

        $id = trim($_POST['id'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $idsede = trim($_POST['idsede'] ?? '');

        file_put_contents(__DIR__ . '/debug_log.txt', "Datos actualizar - ID: $id | Tipo: $tipo | Descripción: $descripcion | IdSede: $idsede\n", FILE_APPEND);

        if (empty($id) || empty($tipo) || empty($idsede)) {
            $error = "Campos requeridos: id, tipo, idsede";
            file_put_contents(__DIR__ . '/debug_log.txt', "❌ $error\n", FILE_APPEND);
            echo json_encode(['success' => false, 'message' => $error]);
            exit;
        }

        $resultado = $modelo->actualizarVehiculo($id, $tipo, $descripcion, $idsede);
        file_put_contents(__DIR__ . '/debug_log.txt', "Resultado: " . json_encode($resultado, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
        echo json_encode($resultado);
        exit;
    }

    // =============================
    // 📌 ELIMINAR VEHÍCULO
    // =============================
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'eliminar') {
        file_put_contents(__DIR__ . '/debug_log.txt', "Iniciando eliminación de vehículo\n", FILE_APPEND);

        $id = trim($_POST['id'] ?? '');

        file_put_contents(__DIR__ . '/debug_log.txt', "ID a eliminar: $id\n", FILE_APPEND);

        if (empty($id)) {
            $error = "ID de vehículo requerido";
            file_put_contents(__DIR__ . '/debug_log.txt', "❌ $error\n", FILE_APPEND);
            echo json_encode(['success' => false, 'message' => $error]);
            exit;
        }

        $resultado = $modelo->eliminarVehiculo($id);
        file_put_contents(__DIR__ . '/debug_log.txt', "Resultado: " . json_encode($resultado, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
        echo json_encode($resultado);
        exit;
    }

    // No hay acción válida
    file_put_contents(__DIR__ . '/debug_log.txt', "⚠️ No se especificó acción válida\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Acción no especificada']);
    exit;

} catch (Exception $e) {
    $error = $e->getMessage();
    file_put_contents(__DIR__ . '/debug_log.txt', "❌ EXCEPCIÓN: $error\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => "Error: $error"]);
}
exit;
?>