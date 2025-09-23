<?php
// ✅ Activamos cabecera JSON para trabajar con AJAX
header('Content-Type: application/json');

// ✅ Requerimos la conexión (ya creada previamente en conexion.php)
require_once "../Conexion/conexion.php";
// ✅ Requerimos la librería de QR que ya tienes instalada
require_once "../libs/phpqrcode/qrlib.php";

class DispositivoController {
    // 🔒 Atributo privado para la conexión
    private $conexion;

    // 🚀 Constructor: se ejecuta al instanciar la clase
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // 📌 Método para registrar un dispositivo en la BD
    public function registrarDispositivo($datos) {
        try {
            // ✅ Validamos que todos los datos obligatorios estén presentes
            if (empty($datos['nombre']) || empty($datos['marca']) || empty($datos['serial'])) {
                return ['success' => false, 'message' => 'Faltan datos obligatorios'];
            }

            // ✅ Insertamos en la tabla dispositivos
            $sql = "INSERT INTO dispositivos (nombre, marca, serial) VALUES (?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("sss", $datos['nombre'], $datos['marca'], $datos['serial']);

            if ($stmt->execute()) {
                // ✅ Generamos QR una vez insertado
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
                return ['success' => true, 'message' => 'Dispositivo eliminado correctamente'];
            } else {
                return ['success' => false, 'message' => 'Error al eliminar dispositivo'];
            }

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Excepción: ' . $e->getMessage()];
        }
    }

    // 📌 Método para generar QR de un dispositivo (basado en serial)
    private function generarQR($serial) {
        // 📂 Carpeta donde se guardarán los QR
        $dir = "qrs/";

        if (!file_exists($dir)) {
            mkdir($dir);
        }

        // 📄 Nombre del archivo del QR
        $archivoQR = $dir . $serial . ".png";

        // 📌 Contenido que irá en el QR (en este caso el serial)
        $contenido = "Dispositivo: " . $serial;

        // 📌 Generamos QR con librería (ya instalada en tu proyecto)
        QRcode::png($contenido, $archivoQR, QR_ECLEVEL_L, 10);

        return $archivoQR;
    }
}

// =============================
// 🚀 USO DEL CONTROLADOR
// =============================

// ✅ Creamos instancia pasando la conexión
$controller = new DispositivoController($conexion);

// ✅ Detectamos la acción (POST viene de formularios o AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'registrar':
            echo json_encode($controller->registrarDispositivo($_POST));
            break;

        case 'eliminar':
            echo json_encode($controller->eliminarDispositivo($_POST['id']));
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
