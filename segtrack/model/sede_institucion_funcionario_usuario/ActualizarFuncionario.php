<?php
// establecer la cabecera para respuestas JSON
header('Content-Type: application/json');
//INCLUIMOS LA CONEXION A LA BASE DE DATOS
require_once "conexion.php";
//VERIFICAMOS QUE LA PETICION SEA POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}
//obtenemos los datos del formulario 
// Asegurarse de validar y sanitizar los datos según sea necesario
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$documento = isset($_POST['documento']) ? trim($_POST['documento']) : '';
$telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$cargo = isset($_POST['cargo']) ? trim($_POST['cargo']) : '';
$id_sede = isset($_POST['id_sede']) ? intval($_POST['id_sede']) : 0;
$qr = isset($_POST['qr']) ? trim($_POST['qr']) : '';
// validamos si el id corresponde a un funcionario existente
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de funcionario inválido']);
    exit;
}
// validar los campos vacios son  obligatorios para actualizar
if (empty($nombre) || empty($documento) || empty($telefono) || empty($correo) || empty($cargo) || $id_sede <= 0 || empty($qr)) {
    echo json_encode(['success' => false, 'message' => 'Complete todos los campos obligatorios']);
    exit;
}
// Actualizamos el funcionario en la base de datos 
try {
    $conexion = new Conexion();
    $conn = $conexion->getConexion();
// Usar declaraciones preparadas para evitar inyecciones SQL
    $sql = "UPDATE funcionario SET 
                Nombre = ?, 
                Documento = ?, 
                Telefono = ?, 
                Correo = ?, 
                Cargo = ?, 
                IdSede = ?, 
                QrCodigo = ?
            WHERE IdFuncionario = ?";
// preparar la declaración para evitar inyecciones SQL
    $stmt = $conn->prepare($sql);
    // Stmt ejecuta la consulta y vicula los paramentros dependiendo del tipo de dato
    $stmt->bind_param("sssssisi", $nombre, $documento, $telefono, $correo, $cargo, $id_sede, $qr, $id);
// ejecutar la consulta y verificar si fue exitosa
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Funcionario actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se realizaron cambios o el funcionario no existe']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error en la ejecución de la consulta: ' . $stmt->error]);
    }
// cerrar la declaración y la conexión
    $stmt->close();
    $conn->close();
// manejar errores de conexión y otros errores
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()]);
}
