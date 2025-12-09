<?php

class ModeloParqueadero {
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
     * Busca un parqueadero usando el contenido del código QR.
     * El QR debe traer un formato como: "ID: 12"
     * IGUAL QUE FUNCIONARIOS
     */
    public function buscarParqueaderoPorQr($qrCodigo) {
        // Expresión regular que extrae el número después de "ID:"
        if (preg_match('/ID:\s*(\d+)/i', $qrCodigo, $match)) {
            $id = $match[1]; // Se obtiene el IdParqueadero
        } else {
            // Si el QR no tiene formato correcto, se retorna false
            return false;
        }

        // Consulta SQL para obtener los datos del parqueadero por su ID
        // IMPORTANTE: Incluye la sede del parqueadero
        $sql = "SELECT * FROM parqueadero WHERE IdParqueadero = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        // Retorna un arreglo asociativo con los datos, o false si no existe
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Registra un ingreso o salida en la base de datos
     * IMPORTANTE: Usa la IdSede del parqueadero
     * Retorna true si se insertó correctamente, false en caso contrario
     */
    public function registrarIngreso($idParqueadero, $idSede, $tipoMovimiento = 'Entrada') {
        $sql = "INSERT INTO ingreso (TipoMovimiento, FechaIngreso, IdSede, IdParqueadero, IdFuncionario, IdDispositivo)
                VALUES (?, NOW(), ?, ?, NULL, NULL)";
        
        $stmt = $this->pdo->prepare($sql);

        // Se ejecuta la consulta con los parámetros enviados
        return $stmt->execute([$tipoMovimiento, $idSede, $idParqueadero]);
    }

    /**
     * Lista todos los ingresos de parqueaderos registrados
     */
    public function listarIngresos() {
        $sql = "SELECT i.IdIngreso, i.TipoMovimiento, i.FechaIngreso, 
                p.TipoVehiculo, p.PlacaVehiculo, p.DescripcionVehiculo,
                s.NombreSede
                FROM ingreso i
                INNER JOIN parqueadero p ON i.IdParqueadero = p.IdParqueadero
                LEFT JOIN sede s ON i.IdSede = s.IdSede
                ORDER BY i.IdIngreso DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        // Se retorna un arreglo con todos los registros
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>