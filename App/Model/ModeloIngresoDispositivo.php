<?php

class ModeloIngresoDispositivo {
    private $pdo;

    public function __construct() {
        require_once __DIR__ . '/../Core/conexion.php';
        $conexionObj = new Conexion();
        $this->pdo   = $conexionObj->getConexion();

        if (!$this->pdo) {
            die("ERROR: La conexión no se inicializó correctamente.");
        }
    }

    public function buscarDispositivoPorQr($qrCodigo) {

        if (preg_match('/Serial:\s*(.+)/i', $qrCodigo, $match)) {
            $serial = trim($match[1]);
        } else {
            return false;
        }

        $sql = "SELECT d.*,
                       COALESCE(f.NombreFuncionario, 'Sin asignar') AS NombreFuncionario,
                       COALESCE(f.CargoFuncionario,  'Sin asignar') AS CargoFuncionario,
                       f.FotoFuncionario,
                       f.IdSede
                FROM dispositivo d
                LEFT JOIN funcionario f ON d.IdFuncionario = f.IdFuncionario
                WHERE d.NumeroSerial = ?
                  AND d.Estado = 'Activo'";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$serial]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registrarIngreso($idDispositivo, $idFuncionario = null, $idSede = null, $tipoMovimiento = 'Entrada') {

        $sql  = "INSERT INTO ingreso (TipoMovimiento, FechaIngreso, IdSede, IdFuncionario, IdDispositivo)
                 VALUES (?, NOW(), ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([$tipoMovimiento, $idSede, $idFuncionario, $idDispositivo]);
    }

    public function listarIngresos() {

        $sql = "SELECT
                    i.IdIngreso,
                    d.TipoDispositivo,
                    d.MarcaDispositivo,
                    d.NumeroSerial,
                    COALESCE(f.NombreFuncionario, 'Sin asignar') AS NombreFuncionario,
                    i.TipoMovimiento,
                    i.FechaIngreso
                FROM ingreso i
                INNER JOIN dispositivo d ON i.IdDispositivo = d.IdDispositivo
                LEFT  JOIN funcionario f ON i.IdFuncionario = f.IdFuncionario
                WHERE i.IdDispositivo IS NOT NULL
                ORDER BY i.IdIngreso DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>