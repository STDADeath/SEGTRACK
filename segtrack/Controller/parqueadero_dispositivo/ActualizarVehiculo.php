<?php
// Establecer la cabecera para respuestas JSON 
header('Content-Type: application/json');
//incluimos la conexion a la base de datos 
require_once "conexion.php";

//verficamos que la peticion sea por metodo POST si no es asi retornamos un error
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

//obtenemos los datos del formulario del vehiculo
// Asegurarse de validar y sanitizar los datos según sea necesario
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : '';
$placa = isset($_POST['placa']) ? trim($_POST['placa']) : '';
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
$tarjeta = isset($_POST['tarjeta']) ? trim($_POST['tarjeta']) : '';
$fecha = isset($_POST['fecha']) ? trim($_POST['fecha']) : '';
$idsede = isset($_POST['idsede']) ? intval($_POST['idsede']) : 0;

//verificamos si el id del vehiculo es valido si no es valido retornamos un error
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de vehículo inválido']);
    exit;
}
//validamos que los campos que estan vacios son obligatorios para actualizar el vehiculo
if (empty($tipo) || empty($placa) || empty($descripcion) || empty($tarjeta) || empty($fecha) || $idsede <= 0) {
    echo json_encode(['success' => false, 'message' => 'Complete todos los campos obligatorios']);
    exit;
}
//actualizamos el vehiculo en la base de datos 
try {
    $conexion = new Conexion();
    $conn = $conexion->getConexion();
// usar declaraciones preparadas para evitar inyecciones SQL
    $sql = "UPDATE Parqueadero SET 
                tipoVehiculo = ?, 
                PlacaVehiculo = ?, 
                DescripcionVehiculo = ?, 
                TarjetaPropiedad = ?, 
                FechaParqueadero = ?, 
                IdSede = ? 
            WHERE IdParqueadero = ?";
// Preparar la declaracion para evitar inyecciones sql
    $stmt = $conn->prepare($sql);
// Sstm ejecuta la consulta y vincula los prametros depediendo del tipo de dato
    $stmt->bind_param("sssssii", $tipo, $placa, $descripcion, $tarjeta, $fecha, $idsede, $id);
// ejecutar la consulta y verificar si fue exitosa
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Vehículo actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se realizaron cambios o el vehículo no existe']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error en la ejecución de la consulta: ' . $stmt->error]);
    }
//cierrer la declaracion y la conexion
    $stmt->close();
    $conn->close();
// manejar errores de conexion y otros errores
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()]);
}
