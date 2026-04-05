<?php

class ModeloIngreso {
    private $pdo;
    private $logPath;

    public function __construct() {
        require_once __DIR__ . '/../Core/conexion.php';
        $conexionObj = new Conexion();
        $this->pdo   = $conexionObj->getConexion();
        $this->logPath = __DIR__ . '/../Controller/Debug_Func/qr_debug.txt';

        if (!$this->pdo) {
            die(json_encode(['success' => false, 'message' => 'Conexión fallida']));
        }
    }

    private function log($msg) {
        file_put_contents(
            $this->logPath,
            date('Y-m-d H:i:s') . " - $msg\n",
            FILE_APPEND
        );
    }

    public function buscarFuncionarioPorQr($qrCodigo) {

        // ── LOG: ver exactamente qué llega del escáner ──────────
        $this->log("=== QR RAW HEX: " . bin2hex($qrCodigo));
        $this->log("=== QR TEXTO:   " . $qrCodigo);

        // Normalizar: quitar \r, espacios sobrantes
        $qrNormalizado = trim(str_replace("\r", "", $qrCodigo));

        $this->log("=== QR NORMALIZADO: " . $qrNormalizado);

        // ── CASO 1: QR contiene texto con "ID: 12" ───────────────
        if (preg_match('/ID:\s*(\d+)/i', $qrNormalizado, $match)) {

            $id = (int)$match[1];
            $this->log("ID extraído del QR: $id");

            if ($id <= 0) {
                $this->log("ERROR: ID extraído es 0 o negativo");
                return ['encontrado' => false, 'inactivo' => false];
            }

            $stmtExiste = $this->pdo->prepare(
                "SELECT IdFuncionario, Estado FROM funcionario WHERE IdFuncionario = ? LIMIT 1"
            );
            $stmtExiste->execute([$id]);
            $existe = $stmtExiste->fetch(PDO::FETCH_ASSOC);

            $this->log("Resultado BD por ID: " . json_encode($existe));

            if (!$existe) {
                $this->log("Funcionario ID $id no existe en BD");
                return ['encontrado' => false, 'inactivo' => false];
            }

            if ($existe['Estado'] !== 'Activo') {
                $this->log("Funcionario ID $id está INACTIVO");
                return ['encontrado' => true, 'inactivo' => true];
            }

            $stmt = $this->pdo->prepare(
                "SELECT * FROM funcionario WHERE IdFuncionario = ? AND Estado = 'Activo' LIMIT 1"
            );
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $this->log("Funcionario encontrado y activo: " . $row['NombreFuncionario']);
                return array_merge($row, ['encontrado' => true, 'inactivo' => false]);
            }

            return ['encontrado' => false, 'inactivo' => false];
        }

        // ── CASO 2: QR es un código simple sin "ID: X" ──────────
        $this->log("No se encontró patrón ID: en el QR, intentando búsqueda directa");

        $stmtExiste = $this->pdo->prepare(
            "SELECT IdFuncionario, Estado FROM funcionario WHERE QrCodigoFuncionario = ? LIMIT 1"
        );
        $stmtExiste->execute([$qrNormalizado]);
        $existe = $stmtExiste->fetch(PDO::FETCH_ASSOC);

        $this->log("Resultado BD por QrCodigo directo: " . json_encode($existe));

        if (!$existe) {
            return ['encontrado' => false, 'inactivo' => false];
        }

        if ($existe['Estado'] !== 'Activo') {
            return ['encontrado' => true, 'inactivo' => true];
        }

        $stmt = $this->pdo->prepare(
            "SELECT * FROM funcionario WHERE QrCodigoFuncionario = ? AND Estado = 'Activo' LIMIT 1"
        );
        $stmt->execute([$qrNormalizado]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row
            ? array_merge($row, ['encontrado' => true, 'inactivo' => false])
            : ['encontrado' => false, 'inactivo' => false];
    }

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