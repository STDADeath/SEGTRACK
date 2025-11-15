<?php

class ModeloIngreso {
<<<<<<< HEAD
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

=======
    private $pdo;

    public function __construct() {
        // Se obtiene la conexión global a la base de datos
        global $conexion;
        $this->pdo = $conexion;
    }

    // Busca un funcionario usando el código QR escaneado
    public function buscarFuncionarioPorQr($qrCodigo) {

        // El QR debe contener algo como: "ID: 12"
        // Se extrae el número después de "ID:"
        if (preg_match('/ID:\s*(\d+)/i', $qrCodigo, $match)) {
            $id = $match[1]; // Este será el IdFuncionario
        } else {
            // Si el formato del QR no es correcto, no se puede usar
            return false;
        }

        // Consulta para obtener la información del funcionario
        $sql = "SELECT * FROM funcionario WHERE IdFuncionario = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna los datos o false si no existe
    }

    // Registrar un movimiento de entrada o salida en la BD
    public function registrarIngreso($idFuncionario, $idSede, $idParqueadero = null, $tipoMovimiento = 'Entrada') {

        // Se inserta un registro con:
        // - TipoMovimiento (Entrada o Salida)
        // - Fecha actual (NOW)
        // - Sede
        // - Parqueadero (puede ser null)
        // - Funcionario que realizó el movimiento
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
        $sql = "INSERT INTO ingreso (TipoMovimiento, FechaIngreso, IdSede, IdParqueadero, IdFuncionario)
                VALUES (?, NOW(), ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
<<<<<<< HEAD

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
=======
        return $stmt->execute([$tipoMovimiento, $idSede, $idParqueadero, $idFuncionario]);
    }

    // Lista los ingresos recientes con datos del funcionario
    public function listarIngresos() {

        // Se hace un JOIN para mostrar:
        // - Nombre del funcionario
        // - Cargo
        // - Tipo de movimiento
        // - Fecha
        $sql = "SELECT i.IdIngreso, i.TipoMovimiento, i.FechaIngreso, 
                       f.NombreFuncionario, f.CargoFuncionario
                FROM ingreso i
                INNER JOIN funcionario f ON i.IdFuncionario = f.IdFuncionario
                ORDER BY i.IdIngreso DESC"; // Ordenados del más reciente al más viejo
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna todos los registros en arreglo
    }
}
?>
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
