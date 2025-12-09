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
     * Busca un dispositivo usando el contenido del código QR.
     * El QR debe traer un formato como: "ID: 12"
     * IGUAL QUE FUNCIONARIOS
     */
    public function buscarDispositivoPorQr($qrCodigo) {
        // Expresión regular que extrae el número después de "ID:"
        if (preg_match('/ID:\s*(\d+)/i', $qrCodigo, $match)) {
            $id = $match[1]; // Se obtiene el IdDispositivo
        } else {
            // Si el QR no tiene formato correcto, se retorna false
            return false;
        }

        // Consulta SQL para obtener los datos del dispositivo por su ID
        $sql = "SELECT * FROM dispositivo WHERE IdDispositivo = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        // Retorna un arreglo asociativo con los datos, o false si no existe
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Registra un ingreso o salida en la base de datos
     * IGUAL QUE FUNCIONARIOS
     * Retorna true si se insertó correctamente, false en caso contrario
     */
    public function registrarIngreso($idDispositivo, $idSede, $idParqueadero = null, $tipoMovimiento = 'Entrada') {
        $sql = "INSERT INTO ingreso (TipoMovimiento, FechaIngreso, IdSede, IdParqueadero, IdDispositivo)
                VALUES (?, NOW(), ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);

        // Se ejecuta la consulta con los parámetros enviados
        return $stmt->execute([$tipoMovimiento, $idSede, $idParqueadero, $idDispositivo]);
    }

    /**
     * Lista todos los ingresos de dispositivos registrados
     * IGUAL QUE FUNCIONARIOS pero adaptado a dispositivo
     */
    public function listarIngresos() {
        $sql = "SELECT i.IdIngreso, i.TipoMovimiento, i.FechaIngreso, 
                d.QrDispositivo, d.TipoDispositivo, d.MarcaDispositivo
                FROM ingreso i
                INNER JOIN dispositivo d ON i.IdDispositivo = d.IdDispositivo
                ORDER BY i.IdIngreso DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        // Se retorna un arreglo con todos los registros
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>