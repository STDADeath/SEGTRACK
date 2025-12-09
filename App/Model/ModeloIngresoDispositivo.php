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
     * Buscar dispositivo por código QR y obtener la sede asociada
     */
    public function buscarDispositivoPorQr($qrCodigo) {
        // Limpiar espacios en blanco
        $qrCodigo = trim($qrCodigo);
        
        // Intentar extraer ID si tiene formato "ID: X"
        if (preg_match('/ID:\s*(\d+)/i', $qrCodigo, $match)) {
            $id = $match[1];
            $sql = "SELECT d.*, 
                           COALESCE(f.IdSede, v.IdSede) as IdSedeAsociada
                    FROM dispositivo d
                    LEFT JOIN funcionario f ON d.IdFuncionario = f.IdFuncionario
                    LEFT JOIN visitante v ON d.IdVisitante = v.IdVisitante
                    WHERE d.IdDispositivo = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // Si no tiene formato ID, buscar directamente por el QR
        $sql = "SELECT d.*, 
                       COALESCE(f.IdSede, v.IdSede) as IdSedeAsociada
                FROM dispositivo d
                LEFT JOIN funcionario f ON d.IdFuncionario = f.IdFuncionario
                LEFT JOIN visitante v ON d.IdVisitante = v.IdVisitante
                WHERE TRIM(d.QrDispositivo) = ? 
                OR TRIM(LOWER(d.QrDispositivo)) = LOWER(?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$qrCodigo, $qrCodigo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Registrar movimiento de dispositivo en la tabla ingreso
     * IdSede puede ser NULL si no se proporciona
     */
    public function registrarMovimiento($idDispositivo, $idSede = null, $tipoMovimiento = 'Entrada') {
        $sql = "INSERT INTO ingreso (TipoMovimiento, FechaIngreso, IdSede, IdDispositivo, IdFuncionario)
                VALUES (?, NOW(), ?, ?, NULL)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$tipoMovimiento, $idSede, $idDispositivo]);
    }

    /**
     * Listar movimientos de dispositivos
     */
    public function listarMovimientos() {
        $sql = "SELECT i.IdIngreso, i.TipoMovimiento, i.FechaIngreso,
                       d.QrDispositivo, d.TipoDispositivo, d.MarcaDispositivo
                FROM ingreso i
                INNER JOIN dispositivo d ON i.IdDispositivo = d.IdDispositivo
                WHERE i.IdDispositivo IS NOT NULL
                ORDER BY i.IdIngreso DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>