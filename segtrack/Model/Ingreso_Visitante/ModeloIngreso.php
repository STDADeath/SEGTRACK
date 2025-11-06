<?php
// modelo_ingreso.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

class ModeloIngreso {
    private $pdo;

    public function __construct() {
        //  Tomamos la conexión creada en conexion.php
        global $conexion;
        $this->pdo = $conexion;
    }

    //  Buscar funcionario por código QR
    public function buscarFuncionarioPorQr($qrCodigo) {
    // 🧠 Extraer el número de ID del texto del QR
    if (preg_match('/ID:\s*(\d+)/i', $qrCodigo, $matches)) {
        $id = $matches[1];
    } else {
        // Si no encuentra el ID, retorna falso
        return false;
    }

    // 🔍 Buscar por IdFuncionario en lugar del campo de texto QR
    $sql = "SELECT * FROM funcionario WHERE IdFuncionario = ?";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


    //  Registrar ingreso
    public function registrarIngreso($idFuncionario, $idSede, $idParqueadero = null) {
        $sql = "INSERT INTO ingreso (TipoMovimiento, FechaIngreso, IdSede, IdParqueadero, IdFuncionario)
                VALUES ('Entrada', NOW(), ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$idSede, $idParqueadero, $idFuncionario]);
    }

    //  Listar ingresos
    public function listarIngresos() {
        $sql = "SELECT i.IdIngreso, i.TipoMovimiento, i.FechaIngreso,
                       f.NombreFuncionario, f.CargoFuncionario, f.CorreoFuncionario
                FROM ingreso i
                INNER JOIN funcionario f ON i.IdFuncionario = f.IdFuncionario
                ORDER BY i.FechaIngreso DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

//  Manejo de peticiones
$metodo = $_SERVER['REQUEST_METHOD'];
$modelo = new ModeloIngreso();

if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $qrCodigo = $input['qr_codigo'] ?? '';

    if (empty($qrCodigo)) {
        echo json_encode(['success' => false, 'message' => 'Código QR no proporcionado']);
        exit;
    }

    $funcionario = $modelo->buscarFuncionarioPorQr($qrCodigo);

    if (!$funcionario) {
        echo json_encode(['success' => false, 'message' => 'Funcionario no encontrado']);
        exit;
    }

    $exito = $modelo->registrarIngreso($funcionario['IdFuncionario'], $funcionario['IdSede']);

    echo json_encode($exito
        ? [
            'success' => true,
            'message' => 'Funcionario ingresado exitosamente ✅',
            'nombre' => $funcionario['NombreFuncionario'],
            'cargo' => $funcionario['CargoFuncionario']
        ]
        : ['success' => false, 'message' => 'Error al registrar el ingreso']
    );

} elseif ($metodo === 'GET') {

    $lista = $modelo->listarIngresos();
    echo json_encode(['success' => true, 'data' => $lista]);

} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
