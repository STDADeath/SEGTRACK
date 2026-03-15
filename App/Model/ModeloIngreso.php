<?php

class ModeloIngreso {
    private $pdo; // Almacena la conexión a la base de datos

    // Constructor: se ejecuta automáticamente al crear el objeto del modelo
    public function __construct() {
        require_once __DIR__ . '/../Core/conexion.php'; 
        // Carga la clase Conexion para acceder a la BD

        $conexionObj = new Conexion();      
        $this->pdo = $conexionObj->getConexion();  
        // Se obtiene la conexión PDO desde la clase Conexion


        if (!$this->pdo) {
            die("ERROR: La conexión no se inicializó correctamente.");
        }
    }


    // Busca un funcionario usando el contenido del código QR.
    // El QR debe traer un formato como: "ID: 12"

    public function buscarFuncionarioPorQr($qrCodigo) {

        // Expresión regular que extrae el número después de "ID:"
        if (preg_match('/ID:\s*(\d+)/i', $qrCodigo, $match)) {
            $id = $match[1]; // Se obtiene el IdFuncionario
        } else {
            // Si el QR no tiene formato correcto, se retorna false
            return false;
        }

        // Consulta SQL para obtener los datos del funcionario por su ID
        $sql = "SELECT * FROM funcionario WHERE IdFuncionario = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        // Retorna un arreglo asociativo con los datos, o false si no existe
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Registra un ingreso o salida en la base de datos
    // Retorna true si se insertó correctamente, false en caso contrario
    public function registrarIngreso($idFuncionario, $idSede, $idParqueadero = null, $tipoMovimiento = 'Entrada') {

        $sql = "INSERT INTO ingreso (TipoMovimiento, FechaIngreso, IdSede, IdParqueadero, IdFuncionario)
                VALUES (?, NOW(), ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);

        // Se ejecuta la consulta con los parámetros enviados
        return $stmt->execute([$tipoMovimiento, $idSede, $idParqueadero, $idFuncionario]);
    }

    /**
     * Lista todos los ingresos registrados, mostrando información del funcionario.
     * Se utiliza un JOIN para unir la tabla ingreso con funcionario.
     */
    public function listarIngresos() {

        $sql = "SELECT i.IdIngreso, i.TipoMovimiento, i.FechaIngreso, 
                f.NombreFuncionario, f.CargoFuncionario
                FROM ingreso i
                INNER JOIN funcionario f ON i.IdFuncionario = f.IdFuncionario
                ORDER BY i.IdIngreso DESC"; // Se muestran del más reciente al más antiguo
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        // Se retorna un arreglo con todos los registros
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
