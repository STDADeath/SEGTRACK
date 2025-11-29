<?php
// App/Controller/ControladorInstituto.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

try {
    // 1. Incluir el modelo. Si falla el require_once, el script se detiene.
    require_once __DIR__ . '/../Model/modeloinstituto.php'; 

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // ... (Recolección de datos y Validación aquí) ...
        
        $nombre = trim($_POST['NombreInstitucion'] ?? '');
        $nit = trim($_POST['Nit_Codigo'] ?? '');
        $tipo = trim($_POST['TipoInstitucion'] ?? '');
        $estado = trim($_POST['EstadoInstitucion'] ?? 'Activo');

        if (empty($nombre) || strlen($nombre) < 3 || strlen($nit) !== 10 || empty($tipo)) {
            throw new Exception("Faltan campos obligatorios o los datos son incorrectos.");
        }

        // 2. Instanciación e Inserción
        // Si la clase Conexion falla al llamarse, el script muere, pero si llegamos aquí,
        // la única forma de que falle es si el constructor del modelo lanza una excepción.
        $institutoModel = new ModeloInstituto(); 
        
        $datosRegistro = [
            'NombreInstitucion' => $nombre,
            'Nit_Codigo' => $nit,
            'TipoInstitucion' => $tipo,
            'EstadoInstitucion' => $estado
        ];
        
        $respuestaModelo = $institutoModel->insertarInstituto($datosRegistro); 

        if ($respuestaModelo['error'] === true) {
            throw new Exception($respuestaModelo['mensaje']);
        }

        // Respuesta EXITOSA
        echo json_encode([
            'ok' => true,
            'message' => $respuestaModelo['mensaje'] 
        ]);
        exit;
        
    } else {
        http_response_code(405);
        echo json_encode(['ok' => false, 'message' => 'Método no permitido.']);
        exit;
    }

} catch (Exception $e) {
    // Este catch captura errores de la clase ModeloInstituto, validación, o lógica.
    http_response_code(400); 
    echo json_encode([
        'ok' => false,
        'message' => 'Error de Servidor: ' . $e->getMessage()
    ]);
    exit;
}
// *** Si el script muere debido a die() en Conexion.php, la única solución es que ***
// *** la base de datos 'segtrackdb' y las credenciales sean correctas. ***
?>