<?php

class ModeloIngreso {
    private $pdo;

    public function __construct() {
        require_once __DIR__ . '/../Core/conexion.php';
        $conexionObj = new Conexion();
        $this->pdo   = $conexionObj->getConexion();

        if (!$this->pdo) {
            die(json_encode(['success' => false, 'message' => 'Conexión fallida']));
        }
    }

    public function buscarFuncionarioPorQr($qrCodigo) {

        // Intento 1: formato "ID: 12"
        if (preg_match('/ID:\s*(\d+)/i', $qrCodigo, $match)) {
            $id   = (int)$match[1];
            $sql  = "SELECT * FROM funcionario WHERE IdFuncionario = ? LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
        }

        // Intento 2: buscar por QrCodigoFuncionario directo
        $sql  = "SELECT * FROM funcionario 
                 WHERE QrCodigoFuncionario = ? 
                 AND Estado = 'Activo' 
                 LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([trim($qrCodigo)]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) return $result;

        return false;
    }

    // Registra un ingreso o salida del funcionario.

    public function registrarIngreso($idFuncionario, $idSede, $tipoMovimiento = 'Entrada') {

        $sql  = "INSERT INTO ingreso
                     (TipoMovimiento, FechaIngreso, Estado, IdSede, IdFuncionario, IdVehiculo, IdParqueadero)
                 VALUES
                     (?, NOW(), 'Activo', ?, ?, NULL, NULL)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $tipoMovimiento,
            $idSede,
            $idFuncionario
        ]);
    }

    // Lista todos los ingresos de funcionarios

    public function listarIngresos() {

        $sql  = "SELECT
                     i.IdIngreso,
                     i.TipoMovimiento,
                     i.FechaIngreso,
                     f.NombreFuncionario,
                     f.CargoFuncionario
                 FROM ingreso i
                 INNER JOIN funcionario f ON i.IdFuncionario = f.IdFuncionario
                 WHERE i.IdFuncionario IS NOT NULL
                 ORDER BY i.IdIngreso DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>