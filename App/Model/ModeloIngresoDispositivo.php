<?php
class ModeloDispositivo {
    private $pdo;

    public function __construct() {
        require_once __DIR__ . '/../Core/conexion.php';
        $conexionObj = new Conexion();
        $this->pdo = $conexionObj->getConexion();

        if (!$this->pdo) {
            die("ERROR: La conexión no se inicializó correctamente.");
        }
    }

    // Buscar dispositivo por código QR
    public function buscarDispositivoPorQr($qrCodigo) {
        $sql = "SELECT * FROM dispositivo WHERE QrDispositivo = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$qrCodigo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Registrar movimiento de dispositivo
    public function registrarMovimiento($idDispositivo, $idSede, $tipoMovimiento = 'Entrada') {
        $sql = "INSERT INTO ingreso (TipoMovimiento, FechaIngreso, IdSede, IdDispositivo, IdFuncionario)
                VALUES (?, NOW(), ?, ?, NULL)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$tipoMovimiento, $idSede, $idDispositivo]);
    }

    // Listar movimientos de dispositivos
    public function listarMovimientos() {
        $sql = "SELECT i.IdIngreso, i.TipoMovimiento, i.FechaIngreso,
                       d.QrDispositivo, d.TipoDispositivo, d.MarcaDispositivo
                FROM ingreso i
                INNER JOIN dispositivo d ON i.IdDispositivo = d.IdDispositivo
                ORDER BY i.IdIngreso DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
