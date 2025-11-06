<?php

class ModeloIngreso {
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
        $sql = "INSERT INTO ingreso (TipoMovimiento, FechaIngreso, IdSede, IdParqueadero, IdFuncionario)
                VALUES (?, NOW(), ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
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
