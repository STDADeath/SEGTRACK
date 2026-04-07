<?php

class ModeloIngresoDispositivo {
    private $pdo;
    private $logPath;

    public function __construct() {
        require_once __DIR__ . '/../Core/conexion.php';
        $conexionObj = new Conexion();
        $this->pdo   = $conexionObj->getConexion();
        $this->logPath = __DIR__ . '/../Controller/Debug_Dispositivo/qr_debug.txt';

        if (!$this->pdo) {
            die(json_encode(['success' => false, 'message' => 'Conexión fallida']));
        }
    }

    private function log($msg) {
        $dir = dirname($this->logPath);
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        
        file_put_contents(
            $this->logPath,
            date('Y-m-d H:i:s') . " - $msg\n",
            FILE_APPEND
        );
    }

    public function buscarDispositivoPorQr($qrCodigo) {

        $this->log("=== QR RAW HEX: " . bin2hex($qrCodigo));
        $this->log("=== QR TEXTO:   " . $qrCodigo);

        $qrNormalizado = trim(str_replace("\r", "", $qrCodigo));
        $this->log("=== QR NORMALIZADO: " . $qrNormalizado);

        // Extraer serial del QR (formato: "Serial: XYZ123" o directamente el serial)
        $serial = null;
        
        if (preg_match('/Serial:\s*(.+)/i', $qrNormalizado, $match)) {
            $serial = trim($match[1]);
        } else {
            // Si no tiene formato "Serial:", asumimos que el QR es directamente el serial
            $serial = $qrNormalizado;
        }

        $this->log("=== SERIAL EXTRAÍDO: " . $serial);

        if (!$serial) {
            $this->log("ERROR: No se pudo extraer serial del QR");
            return ['encontrado' => false, 'inactivo' => false];
        }

        // PRIMERO: Verificar si el dispositivo existe y su estado
        $stmtExiste = $this->pdo->prepare(
            "SELECT IdDispositivo, Estado, TipoDispositivo, MarcaDispositivo, NumeroSerial 
             FROM dispositivo 
             WHERE NumeroSerial = ? 
             LIMIT 1"
        );
        $stmtExiste->execute([$serial]);
        $existe = $stmtExiste->fetch(PDO::FETCH_ASSOC);

        $this->log("Resultado BD por serial: " . json_encode($existe));

        if (!$existe) {
            $this->log("Dispositivo con serial $serial no existe en BD");
            return ['encontrado' => false, 'inactivo' => false];
        }

        // Verificar si está inactivo
        if ($existe['Estado'] !== 'Activo') {
            $this->log("Dispositivo con serial $serial está INACTIVO");
            return ['encontrado' => true, 'inactivo' => true, 'datos' => $existe];
        }

        // Dispositivo activo - obtener todos los datos incluyendo funcionario asignado
        $sql = "SELECT d.*,
                       COALESCE(f.NombreFuncionario, 'Sin asignar') AS NombreFuncionario,
                       COALESCE(f.CargoFuncionario, 'Sin asignar') AS CargoFuncionario,
                       f.FotoFuncionario,
                       f.IdSede,
                       f.Estado AS EstadoFuncionario
                FROM dispositivo d
                LEFT JOIN funcionario f ON d.IdFuncionario = f.IdFuncionario
                WHERE d.NumeroSerial = ?
                  AND d.Estado = 'Activo'
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$serial]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->log("Dispositivo encontrado y activo: " . $row['NumeroSerial']);
            
            // Verificar si el funcionario asignado está inactivo
            if ($row['IdFuncionario'] && $row['EstadoFuncionario'] !== 'Activo') {
                $this->log("ADVERTENCIA: El funcionario asignado al dispositivo está INACTIVO");
                // No bloqueamos el dispositivo, solo advertimos
            }
            
            return array_merge($row, ['encontrado' => true, 'inactivo' => false]);
        }

        return ['encontrado' => false, 'inactivo' => false];
    }

    public function obtenerUltimoMovimientoDispositivo($idDispositivo) {
        $stmt = $this->pdo->prepare(
            "SELECT TipoMovimiento FROM ingreso
             WHERE IdDispositivo = ?
             ORDER BY IdIngreso DESC
             LIMIT 1"
        );
        $stmt->execute([$idDispositivo]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila ? $fila['TipoMovimiento'] : null;
    }

    public function registrarIngreso($idDispositivo, $idFuncionario = null, $idSede = null, $tipoMovimiento = 'Entrada') {

        $sql = "INSERT INTO ingreso 
                    (TipoMovimiento, FechaIngreso, Estado, IdSede, IdFuncionario, IdDispositivo, IdVehiculo, IdParqueadero)
                VALUES 
                    (?, NOW(), 'Activo', ?, ?, ?, NULL, NULL)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $tipoMovimiento,
            $idSede,
            $idFuncionario,
            $idDispositivo
        ]);
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