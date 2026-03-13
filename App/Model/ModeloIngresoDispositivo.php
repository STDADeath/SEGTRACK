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
    // El QR debe tener formato: "DISP: 12"
    // ========================================

    public function buscarDispositivoPorQr($qrCodigo) {

        // Expresión regular que extrae el número después de "DISP:"
        if (preg_match('/DISP:\s*(\d+)/i', $qrCodigo, $match)) {
            $id = $match[1]; // Se obtiene el IdDispositivo
        } else {
            // Si el QR no tiene el formato correcto, se retorna false
            return false;
        }

        // Busca el dispositivo activo con su funcionario asignado (si tiene)
        $sql = "SELECT d.*,
                       COALESCE(f.NombreFuncionario, 'Sin asignar') AS NombreFuncionario,
                       COALESCE(f.CargoFuncionario,  'Sin asignar') AS CargoFuncionario,
                       f.IdSede
                FROM dispositivo d
                LEFT JOIN funcionario f ON d.IdFuncionario = f.IdFuncionario
                WHERE d.IdDispositivo = ?
                  AND d.Estado = 'Activo'";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        // Retorna un arreglo asociativo con los datos, o false si no existe
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // ========================================
    // REGISTRAR MOVIMIENTO EN LA TABLA ingreso
    // Usa el campo IdDispositivo que ya existe en ingreso
    // ========================================

    public function registrarIngresoDispositivo($idDispositivo, $idSede = null, $tipoMovimiento = 'Entrada') {

        $sql = "INSERT INTO ingreso (TipoMovimiento, FechaIngreso, IdSede, IdDispositivo)
                VALUES (?, NOW(), ?, ?)";

        $stmt = $this->pdo->prepare($sql);

        // Se ejecuta con los parámetros enviados
        return $stmt->execute([$tipoMovimiento, $idSede, $idDispositivo]);
    }


    // ========================================
    // LISTAR MOVIMIENTOS DE DISPOSITIVOS
    // Filtra los registros de ingreso donde IdDispositivo no es NULL
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
                INNER JOIN dispositivo d  ON i.IdDispositivo  = d.IdDispositivo
                LEFT  JOIN funcionario f  ON d.IdFuncionario  = f.IdFuncionario
                WHERE i.IdDispositivo IS NOT NULL
                ORDER BY i.IdIngreso DESC"; // Del más reciente al más antiguo

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        // Retorna un arreglo con todos los registros
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>