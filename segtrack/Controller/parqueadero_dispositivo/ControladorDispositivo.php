<?php
// ✅ Activamos cabecera JSON para trabajar con AJAX
header('Content-Type: application/json');

// ✅ Conexión
require_once __DIR__ . "/../Conexion/conexion.php";

// ✅ Librería de QR
require_once __DIR__ . "/../../libs/phpqrcode/qrlib.php";

class DispositivoController {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // 📌 Método para registrar un dispositivo
    public function registrarDispositivo($datos) {
        try {
            // ⚠️ Validamos campos obligatorios
            if (empty($datos['TipoDispositivo']) || empty($datos['MarcaDispositivo'])) {
                return ['success' => false, 'message' => 'Tipo y Marca son obligatorios'];
            }

            if ((empty($datos['IdFuncionario']) && empty($datos['IdVisitante'])) ||
                (!empty($datos['IdFuncionario']) && !empty($datos['IdVisitante']))) {
                return ['success' => false, 'message' => 'Debe ingresar solo un ID: Funcionario o Visitante'];
            }

            // 📌 Si selecciona "Otro", usar el valor ingresado en texto
            $tipo = ($datos['TipoDispositivo'] === "Otro" && !empty($datos['OtroTipoDispositivo'])) 
                ? $datos['OtroTipoDispositivo'] 
                : $datos['TipoDispositivo'];

            $marca = $datos['MarcaDispositivo'];
            $idFuncionario = !empty($datos['IdFuncionario']) ? $datos['IdFuncionario'] : null;
            $idVisitante = !empty($datos['IdVisitante']) ? $datos['IdVisitante'] : null;


            // 📝 Generar código único para QR
            $codigoQR = $tipo . "_" . $marca . "_" . time();

            // Insertar en la tabla
            $sql = "INSERT INTO dispositivos (QrDispositivo, TipoDispositivo, Marca, IdFuncionario, IdVisitante) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("sssii", $codigoQR, $tipo, $marca, $idFuncionario, $idVisitante);

            if ($stmt->execute()) {
                // 📌 Generar archivo QR
                $this->generarQR($codigoQR);
                return ['success' => true, 'message' => '✅ Dispositivo registrado y QR generado'];
            } else {
                return ['success' => false, 'message' => '❌ Error al registrar dispositivo: ' . $stmt->error];
            }

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Excepción: ' . $e->getMessage()];
        }
    }

    // 📌 Método para eliminar un dispositivo
    public function eliminarDispositivo($id) {
        try {
            $sql = "DELETE FROM dispositivos WHERE IdDispositivo = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => '✅ Dispositivo eliminado correctamente'];
            } else {
                return ['success' => false, 'message' => '❌ Error al eliminar dispositivo'];
            }

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Excepción: ' . $e->getMessage()];
        }
    }

    // 📌 Método para editar un dispositivo
    public function editarDispositivo($datos) {
        try {
            if (empty($datos['IdDispositivo'])) {
                return ['success' => false, 'message' => 'ID inválido'];
            }

            $sql = "UPDATE dispositivos SET 
                        TipoDispositivo = ?, 
                        Marca = ?, 
                        IdFuncionario = ?, 
                        IdVisitante = ?
                    WHERE IdDispositivo = ?";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param(
                "ssiii",
                $datos['TipoDispositivo'],
                $datos['MarcaDispositivo'],
                $datos['IdFuncionario'],
                $datos['IdVisitante'],
                $datos['IdDispositivo']
            );

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    return ['success' => true, 'message' => '✅ Dispositivo actualizado correctamente'];
                } else {
                    return ['success' => false, 'message' => 'No se realizaron cambios'];
                }
            } else {
                return ['success' => false, 'message' => 'Error en la consulta: ' . $stmt->error];
            }

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Excepción: ' . $e->getMessage()];
        }
    }

    // 📌 Generar QR
    private function generarQR($codigo) {
        $dir = __DIR__ . "/../../qrs/";
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $archivoQR = $dir . $codigo . ".png";
        QRcode::png($codigo, $archivoQR, QR_ECLEVEL_L, 10);
        return $archivoQR;
    }
}

// =============================
// 🚀 USO DEL CONTROLADOR
// =============================
$conexionObj = new Conexion();
$conexion = $conexionObj->getConexion();
$controller = new DispositivoController($conexion);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? 'registrar';

    switch ($accion) {
        case 'insertar':
        case 'registrar':
            echo json_encode($controller->registrarDispositivo($_POST));
            break;

        case 'eliminar':
            echo json_encode($controller->eliminarDispositivo($_POST['id']));
            break;

        case 'editar':
            echo json_encode($controller->editarDispositivo($_POST));
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>