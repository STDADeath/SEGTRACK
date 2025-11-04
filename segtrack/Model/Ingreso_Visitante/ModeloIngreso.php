<?php
// modelo_ingreso.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

class ModeloIngreso {
    private $pdo;

    public function __construct() {
        try {
            // 🔧 Ajusta los datos de conexión a tu base MySQL
            $this->pdo = new PDO('mysql:host=localhost;dbname=tu_basedatos;charset=utf8', 'root', '');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die(json_encode([
                'success' => false,
                'message' => "Error de conexión: " . $e->getMessage()
            ]));
        }
    }

    // 🔹 Buscar funcionario por QR
    public function buscarFuncionarioPorQr($qrCodigo) {
        $sql = "SELECT * FROM funcionarios WHERE QrCodigoFuncionario = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$qrCodigo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 🔹 Registrar ingreso
    public function registrarIngreso($idFuncionario, $idSede, $idParqueadero = null) {
        $sql = "INSERT INTO ingresos (TipoMovimiento, FechaIngreso, IdSede, IdParqueadero, IdFuncionario)
                VALUES ('Entrada', NOW(), ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$idSede, $idParqueadero, $idFuncionario]);
    }

    // 🔹 Listar ingresos
    public function listarIngresos() {
        $sql = "SELECT i.IdIngreso, i.TipoMovimiento, i.FechaIngreso,
                       f.NombreFuncionario, f.CargoFuncionario, f.CorreoFuncionario
                FROM ingresos i
                INNER JOIN funcionarios f ON i.IdFuncionario = f.IdFuncionario
                ORDER BY i.FechaIngreso DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// 🧩 Manejo de las solicitudes
$metodo = $_SERVER['REQUEST_METHOD'];
$modelo = new ModeloIngreso();

if ($metodo === 'POST') {
    // 📥 Registrar ingreso por código QR
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

    // Registrar ingreso
    $exito = $modelo->registrarIngreso($funcionario['IdFuncionario'], $funcionario['IdSede']);

    if ($exito) {
        echo json_encode([
            'success' => true,
            'message' => 'Funcionario ingresado exitosamente',
            'nombre' => $funcionario['NombreFuncionario'],
            'cargo' => $funcionario['CargoFuncionario']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar el ingreso']);
    }

} elseif ($metodo === 'GET') {
    // 📋 Listar todos los ingresos
    $lista = $modelo->listarIngresos();
    echo json_encode(['success' => true, 'data' => $lista]);

} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
