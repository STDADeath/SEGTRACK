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

    /**
     * Buscar dispositivo por QR
     */
    public function buscarDispositivoPorQr($qrCodigo) {

        if (preg_match('/ID:\s*(\d+)/i', $qrCodigo, $match)) {
            $id = $match[1];
        } else {
            return false;
        }

        $sql = "SELECT 
                    IdDispositivo,
                    QrDispositivo,
                    TipoDispositivo,
                    MarcaDispositivo,
                    NumeroSerial,
                    Estado,
                    IdFuncionario,
                    IdVisitante
                FROM dispositivo
                WHERE IdDispositivo = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Registrar ingreso o salida
     */
    public function registrarIngreso($idDispositivo, $idSede, $idParqueadero = null, $tipoMovimiento = 'Entrada') {

        $sql = "INSERT INTO ingreso 
                (TipoMovimiento, FechaIngreso, IdSede, IdParqueadero, IdDispositivo)
                VALUES (?, NOW(), ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $tipoMovimiento,
            $idSede,
            $idParqueadero,
            $idDispositivo
        ]);
    }

    /**
     * Listar ingresos con datos del dispositivo
     * + funcionario dueño
     */
    public function listarIngresos() {

        $sql = "SELECT 
                    i.IdIngreso,
                    i.TipoMovimiento,
                    i.FechaIngreso,

                    d.IdDispositivo,
                    d.QrDispositivo,
                    d.TipoDispositivo,
                    d.MarcaDispositivo,
                    d.NumeroSerial,
                    d.Estado,

                    f.NombreFuncionario,
                    f.CargoFuncionario

                FROM ingreso i

                INNER JOIN dispositivo d
                    ON i.IdDispositivo = d.IdDispositivo

                LEFT JOIN funcionario f
                    ON d.IdFuncionario = f.IdFuncionario

                ORDER BY i.IdIngreso DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>