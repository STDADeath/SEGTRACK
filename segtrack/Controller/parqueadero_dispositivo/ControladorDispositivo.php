<?php
// ✅ Activamos cabecera JSON para trabajar con AJAX
header('Content-Type: application/json');

// ✅ Conexión
require_once __DIR__ . "/../Conexion/conexion.php";

// ✅ Librería de QR
require_once __DIR__ . "/../../libs/phpqrcode/qrlib.php";

class DispositivoController {
    // 🔒 Atributo privado para la conexión
    private $conexion;

    // 🚀 Constructor: recibe la conexión al instanciar la clase
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // 📌 Método para registrar un dispositivo
    public function registrarDispositivo($datos) {
        try {
            // ⚠️ Validamos que los datos no estén vacíos
            if (empty($datos['nombre']) || empty($datos['marca']) || empty($datos['serial'])) {
                return ['success' => false, 'message' => 'Faltan datos obligatorios'];
            }

            // 📝 Insertamos en la tabla dispositivos
            $sql = "INSERT INTO dispositivos (nombre, marca, serial) VALUES (?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("sss", $datos['nombre'], $datos['marca'], $datos['serial']);

            if ($stmt->execute()) {
                // 📌 Generamos QR basado en el serial
                $this->generarQR($datos['serial']);
                return ['success' => true, 'message' => 'Dispositivo registrado y QR generado'];
            } else {
                return ['success' => false, 'message' => 'Error al registrar dispositivo'];
            }

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Excepción: ' . $e->getMessage()];
        }
    }

    // 📌 Método para eliminar un dispositivo
    public function eliminarDispositivo($id) {
        try {
            $sql = "DELETE FROM dispositivos WHERE id = ?";
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
            // ⚠️ Validamos ID
            if (empty($datos['id']) || intval($datos['id']) <= 0) {
                return ['success' => false, 'message' => 'ID inválido'];
            }

            // ⚠️ Validamos campos requeridos
            if (empty($datos['qr']) || empty($datos['tipo']) || empty($datos['marca'])) {
                return ['success' => false, 'message' => 'Todos los campos son obligatorios'];
            }

            // 📝 Consulta SQL para actualizar
            $sql = "UPDATE dispositivos SET 
                        qr = ?, 
                        tipo = ?, 
                        marca = ?, 
                        id_funcionario = ?, 
                        id_visitante = ? 
                    WHERE id = ?";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param(
                "sssiii",
                $datos['qr'],
                $datos['tipo'],
                $datos['marca'],
                $datos['id_funcionario'],
                $datos['id_visitante'],
                $datos['id']
            );

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    return ['success' => true, 'message' => 'Dispositivo actualizado correctamente'];
                } else {
                    return ['success' => false, 'message' => 'No se realizaron cambios o el dispositivo no existe'];
                }
            } else {
                return ['success' => false, 'message' => 'Error en la consulta: ' . $stmt->error];
            }

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Excepción: ' . $e->getMessage()];
        }
    }

    // 📌 Método privado para generar QR
    private function generarQR($serial) {
        $dir = "qrs/";
        if (!file_exists($dir)) {
            mkdir($dir);
        }

        $archivoQR = $dir . $serial . ".png";
        $contenido = "Dispositivo: " . $serial;

        // 📌 Generamos QR
        QRcode::png($contenido, $archivoQR, QR_ECLEVEL_L, 10);
        return $archivoQR;
    }
}

// =============================
// 🚀 USO DEL CONTROLADOR
// =============================
$conexionObj = new Conexion();          // Instancia la clase
$conexion = $conexionObj->getConexion(); // Obtén la conexión real (mysqli)
$controller = new DispositivoController($conexion);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
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
