<?php

class ModeloDispositivo {

    private $pdo;

    public function __construct() {

        require_once __DIR__ . '/../Core/conexion.php';

        $conexionObj = new Conexion();
        $this->pdo = $conexionObj->getConexion();

        if (!$this->pdo) {
            die("ERROR: No se pudo establecer la conexión con la base de datos.");
        }
    }

    /**
     * Buscar dispositivo por QR
     */
    public function buscarDispositivoPorQr($qrCodigo) {

        try {

            $qrCodigo = trim($qrCodigo);

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
                    WHERE TRIM(QrDispositivo) = TRIM(?)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$qrCodigo]);

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error buscarDispositivoPorQr: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registrar ingreso o salida del dispositivo
     */
    public function registrarIngreso($idDispositivo, $idSede, $idParqueadero = null, $tipoMovimiento = 'Entrada') {

        try {

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

        } catch (PDOException $e) {

            error_log("Error registrarIngreso: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Listar ingresos de dispositivos
     * Incluye datos del dispositivo y funcionario dueño
     */
    public function listarIngresos() {

        try {

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

        } catch (PDOException $e) {

            error_log("Error listarIngresos: " . $e->getMessage());
            return [];
        }
    }

}
?>