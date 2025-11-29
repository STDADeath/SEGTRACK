<?php

class ModeloIngreso {
    private $pdo; // Conexión PDO

    public function __construct() {
        require_once __DIR__ . '/../Core/conexion.php';

        $conexionObj = new Conexion();
        $this->pdo = $conexionObj->getConexion();

        if (!$this->pdo) {
            die("ERROR: La conexión no se inicializó correctamente.");
        }
    }

    // Extrae el ID del funcionario desde el texto del QR y lo busca en BD
    public function buscarFuncionarioPorQr($qrCodigo) {

        // Busca un número después de "ID:"
        if (preg_match('/ID:\s*(\d+)/i', $qrCodigo, $match)) {
            $id = $match[1];
        } else {
            return false; // QR con formato incorrecto
        }

        $sql = "SELECT * FROM funcionario WHERE IdFuncionario = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }

    // Registra una entrada o salida con fecha automática 
    public function registrarIngreso($idFuncionario, $idSede, $idParqueadero = null, $tipoMovimiento = 'Entrada') {

        $sql = "INSERT INTO ingreso (TipoMovimiento, FechaIngreso, IdSede, IdParqueadero, IdFuncionario)
                VALUES (?, NOW(), ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([$tipoMovimiento, $idSede, $idParqueadero, $idFuncionario]);
    }

    // Lista ingreso + información del funcionario usando JOIN
    public function listarIngresos() {

        $sql = "SELECT i.IdIngreso, i.TipoMovimiento, i.FechaIngreso, 
                       f.NombreFuncionario, f.CargoFuncionario
                FROM ingreso i
                INNER JOIN funcionario f ON i.IdFuncionario = f.IdFuncionario
                ORDER BY i.IdIngreso DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>

