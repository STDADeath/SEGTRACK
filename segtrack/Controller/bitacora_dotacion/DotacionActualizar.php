<?php
// Incluimmos header para que retorne json
header('Content-Type: application/json');
//incluimos la conexion a la base de datos
require_once "conexion.php";

// Verificar que la solicitud sea POST si no es asi retornamos un error
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// obtener datos del formulario dotacion 
$id = isset($_POST['idDotacion']) ? intval($_POST['idDotacion']) : 0;
$NombreDotacion = isset($_POST['NombreDotacion']) ? trim($_POST['NombreDotacion']) : '';
$EstadoDotacion = isset($_POST['EstadoDotacion']) ? trim($_POST['EstadoDotacion']) : '';
$TipoDotacion = isset($_POST['TipoDotacion']) ? trim($_POST['TipoDotacion']) : '';
$novedad = isset($_POST['Novedad']) ? trim($_POST['Novedad']) : '';
$FechaDevolucion = isset($_POST['FechaDevolucion']) ? trim($_POST['FechaDevolucion']) : '';
$FechaEntrega = isset($_POST['FechaEntrega']) ? trim($_POST['FechaEntrega']) : '';
$IdFuncionario = isset($_POST['IdFuncionario']) ? intval($_POST['IdFuncionario']) : 0;

//validamos si el id de la dotacion es valida si no es valida retornamos un error
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de dotación inválida']);
    exit;
}
//validar los campos obligatorios no esten vacios  selecionados
if (empty($qr) || empty($tipo) || empty($marca)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit;
}
//actualizamos la dotacion en la base datos
try {
    $conexion = new Conexion();
    $conn = $conexion->getConexion();
    //preparar la declaracion para evitar inyecciones sql
    //usar declaraciones preparadas para evitar inyecciones sql
    $sql = "UPDATE Dotacion SET 
            IdDotacion = ?,
            NombreDotacion = ?,
            EstadoDotacion = ?,
            TipoDotacion = ?,
            Novedad = ?,
            FechaDevolucion = ?,
            FechaEntrega = ?,
            IdFuncionario = ?
            WHERE IdDotacion = ?";
    //stmt ejecuta la consulta y vincula los parametros dependiendo del tipo de dato         
    $stmt = $conn->prepare($sql);
    //stm ejecuta la consulta y vincula los parametros depediendo del tipo de dato
    $stmt->bind_param("sssiii", $idDOTACION, $NombreDotacion, $EstadoDotacion, $TipoDotacion,$Novedad,$FechaDevolucion,$FechaEntrega,$IdFuncionario);
    //ejecutar la consulta y verificar si fue exitosa
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Dotacion actualizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se realizaron cambios o la dotación no existe']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error en la ejecución de la consulta: ' . $stmt->error]);
    }
    //cierre la declaracion y la conexion
    $stmt->close();
    $conn->close();
    // manejar errores de conexion y otros errores
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()]);
}
?>