<?php

class ModeloIngresoDispositivo {

    private $pdo; // Almacena la conexión a la base de datos

    // Constructor: se ejecuta automáticamente al crear el objeto del modelo
    public function __construct() {
        require_once __DIR__ . '/../Core/conexion.php';

        $conexionObj = new Conexion();
        $this->pdo = $conexionObj->getConexion();

        if (!$this->pdo) {
            die("ERROR: La conexión no se inicializó correctamente.");
        }
    }


    // ========================================
    // BUSCAR DISPOSITIVO POR QR
    // El campo QrDispositivo guarda la ruta del QR generado
    // Igual que QrCodigoFuncionario en funcionario
    // ========================================

    public function buscarDispositivoPorQr($qrCodigo) {

        // Busca el dispositivo cuyo QrDispositivo coincida exactamente
        $sql = "SELECT d.*,
                       COALESCE(f.NombreFuncionario, 'Sin asignar') AS NombreFuncionario,
                       COALESCE(f.CargoFuncionario,  'Sin asignar') AS CargoFuncionario,
                       f.IdSede
                FROM dispositivo d
                LEFT JOIN funcionario f ON d.IdFuncionario = f.IdFuncionario
                WHERE d.QrDispositivo = ?
                  AND d.Estado = 'Activo'";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$qrCodigo]);

        // Retorna un arreglo asociativo con los datos, o false si no existe
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // ========================================
    // REGISTRAR MOVIMIENTO EN LA TABLA ingreso
    // Misma tabla que funcionarios — usa IdDispositivo
    // IdFuncionario se llena si el dispositivo tiene uno asignado
    // ========================================

    public function registrarIngresoDispositivo($idDispositivo, $idFuncionario = null, $idSede, $tipoMovimiento = 'Entrada') {

        $sql = "INSERT INTO ingreso (TipoMovimiento, FechaIngreso, IdSede, IdFuncionario, IdDispositivo)
                VALUES (?, NOW(), ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([$tipoMovimiento, $idSede, $idFuncionario, $idDispositivo]);
    }


    // ========================================
    // LISTAR MOVIMIENTOS DE DISPOSITIVOS
    // Filtra los registros donde IdDispositivo no es NULL
    // ========================================

    public function listarIngresosDispositivos() {

        $sql = "SELECT
                    i.IdIngreso,
                    d.TipoDispositivo,
                    d.MarcaDispositivo,
                    d.NumeroSerial,
                    COALESCE(f.NombreFuncionario, 'Sin asignar') AS NombreFuncionario,
                    i.TipoMovimiento,
                    i.FechaIngreso
                FROM ingreso i
                INNER JOIN dispositivo d ON i.IdDispositivo  = d.IdDispositivo
                LEFT  JOIN funcionario f ON i.IdFuncionario  = f.IdFuncionario
                WHERE i.IdDispositivo IS NOT NULL
                ORDER BY i.IdIngreso DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>