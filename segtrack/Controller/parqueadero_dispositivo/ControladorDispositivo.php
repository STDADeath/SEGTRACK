<?php
/**
 * 🔍 Controlador de Dispositivos con mensajes de depuración.
 * En lugar de notificaciones, devuelve mensajes exactos donde se produce el error.
 */

header('Content-Type: application/json');

// --- PUNTO 1: Conexión a la base de datos ---
try {
    require_once __DIR__ . "/../../Core/conexion.php";
    echo json_encode(["debug" => "✅ conexión.php incluido correctamente"]);
} catch (Throwable $e) {
    echo json_encode(["error" => "❌ Error al incluir conexión.php", "detalle" => $e->getMessage()]);
    exit;
}

// --- PUNTO 2: Modelo ---
try {
    require_once __DIR__ . "/../../model/parqueadero_dispositivo/ModeloDispositivo.php";
    echo json_encode(["debug" => "✅ ModeloDispositivo.php incluido correctamente"]);
} catch (Throwable $e) {
    echo json_encode(["error" => "❌ Error al incluir ModeloDispositivo.php", "detalle" => $e->getMessage()]);
    exit;
}

// --- PUNTO 3: Librería QR ---
try {
    require_once __DIR__ . "/../../libs/phpqrcode/qrlib.php";
    echo json_encode(["debug" => "✅ Librería qrlib.php incluida correctamente"]);
} catch (Throwable $e) {
    echo json_encode(["error" => "❌ Error al incluir librería QR", "detalle" => $e->getMessage()]);
    exit;
}

// --- PUNTO 4: Instanciar modelo ---
try {
    $model = new DispositivoModel($conexion);
    echo json_encode(["debug" => "✅ Modelo instanciado correctamente"]);
} catch (Throwable $e) {
    echo json_encode(["error" => "❌ Fallo al instanciar DispositivoModel", "detalle" => $e->getMessage()]);
    exit;
}

// --- PUNTO 5: Detectar acción ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['accion'])) {
    echo json_encode(["mensaje" => "Controlador alcanzado correctamente"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'registrar':
            try {
                $tipo = $_POST['TipoDispositivo'] ?? '';
                $marca = $_POST['MarcaDispositivo'] ?? '';
                $otro = $_POST['OtroTipoDispositivo'] ?? '';
                $idFuncionario = $_POST['IdFuncionario'] ?? null;
                $idVisitante = $_POST['IdVisitante'] ?? null;

                if (empty($tipo) || empty($marca)) {
                    echo json_encode(["error" => "Campos obligatorios vacíos", "detalle" => "Tipo o Marca no enviados"]);
                    exit;
                }

                if (($idFuncionario && $idVisitante) || (!$idFuncionario && !$idVisitante)) {
                    echo json_encode(["error" => "IDs incorrectos", "detalle" => "Debe haber solo un ID válido (Funcionario o Visitante)"]);
                    exit;
                }

                if ($tipo === 'Otro' && !empty($otro)) {
                    $tipo = $otro;
                }

                $codigoQR = $tipo . "_" . $marca . "_" . time();
                echo json_encode(["debug" => "⚙️ Insertando dispositivo en BD"]);

                $resultado = $model->insertar($codigoQR, $tipo, $marca, $idFuncionario, $idVisitante);

                if ($resultado === true) {
                    $dir = __DIR__ . "/../../qrs/";
                    if (!file_exists($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    $archivoQR = $dir . $codigoQR . ".png";
                    QRcode::png($codigoQR, $archivoQR, QR_ECLEVEL_L, 10);

                    echo json_encode(["success" => true, "mensaje" => "✅ Dispositivo registrado correctamente"]);
                } else {
                    echo json_encode(["error" => "❌ Error al insertar", "detalle" => $resultado]);
                }
            } catch (Throwable $e) {
                echo json_encode(["error" => "❌ Excepción al registrar", "detalle" => $e->getMessage()]);
            }
            break;

        default:
            echo json_encode(["error" => "Acción no válida o vacía", "accion_recibida" => $accion]);
            break;
    }
} else {
    echo json_encode(["error" => "Método no permitido", "detalle" => $_SERVER['REQUEST_METHOD']]);
}
?>
