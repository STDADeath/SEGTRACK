<?php

class ModeloParqueadero {
    private $pdo;

    public function __construct() {
        require_once __DIR__ . '/../Core/conexion.php';
        $conexionObj = new Conexion();
        $this->pdo = $conexionObj->getConexion();

        if (!$this->pdo) {
            throw new Exception("ERROR: La conexión a la base de datos falló");
        }
    }

    /**
     * Busca un vehículo usando el contenido del código QR.
     * El QR debe traer un formato como: "ID: 12"
     */
    public function buscarVehiculoPorQr($qrCodigo) {
        try {
            // Expresión regular que extrae el número después de "ID:"
            if (preg_match('/ID:\s*(\d+)/i', $qrCodigo, $match)) {
                $id = $match[1];
            } else {
                return false;
            }

            // Consulta SQL para obtener los datos del vehículo con la sede vinculada
            $sql = "SELECT p.*, 
                           COALESCE(s.NombreSede, 'Sin sede') AS NombreSede, 
                           s.DireccionSede 
                    FROM parqueadero p
                    LEFT JOIN sede s ON p.IdSede = s.IdSede
                    WHERE p.IdParqueadero = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en buscarVehiculoPorQr: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registra un movimiento actualizando el estado y la fecha
     */
    public function registrarMovimiento($idParqueadero, $tipoMovimiento = 'Entrada') {
        try {
            // Determinar el nuevo estado según el tipo de movimiento
            $nuevoEstado = ($tipoMovimiento === 'Entrada') ? 'Activo' : 'Inactivo';

            // Actualizar estado y fecha del vehículo
            $sql = "UPDATE parqueadero 
                    SET Estado = ?, FechaParqueadero = NOW() 
                    WHERE IdParqueadero = ?";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$nuevoEstado, $idParqueadero]);
        } catch (PDOException $e) {
            error_log("Error en registrarMovimiento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lista todos los movimientos de vehículos registrados
     */
    public function listarMovimientos() {
        try {
            $sql = "SELECT p.IdParqueadero, 
                           COALESCE(p.PlacaVehiculo, 'Sin placa') AS PlacaVehiculo, 
                           COALESCE(p.TipoVehiculo, 'N/A') AS TipoVehiculo, 
                           COALESCE(p.DescripcionVehiculo, 'Sin descripción') AS DescripcionVehiculo,
                           COALESCE(p.Estado, 'Inactivo') AS Estado, 
                           COALESCE(p.FechaParqueadero, NOW()) AS FechaParqueadero,
                           COALESCE(s.NombreSede, 'Sin sede') AS NombreSede,
                           CASE 
                               WHEN p.Estado = 'Activo' THEN 'Entrada'
                               ELSE 'Salida'
                           END AS TipoMovimiento
                    FROM parqueadero p
                    LEFT JOIN sede s ON p.IdSede = s.IdSede
                    ORDER BY p.FechaParqueadero DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Retornar array vacío si no hay resultados
            return $resultado ? $resultado : [];
            
        } catch (PDOException $e) {
            error_log("Error en listarMovimientos: " . $e->getMessage());
            // Retornar array vacío en caso de error
            return [];
        }
    }
}

?>