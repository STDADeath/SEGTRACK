<?php
require_once __DIR__ . '/../../Core/conexion.php';

class FuncionarioModel {
    private $conexion;

    public function __construct() {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    // ✅ Agregar funcionario
    public function agregarFuncionario($datos) {
        try {
            $sql = "INSERT INTO funcionario 
                (NombreFuncionario, DocumentoFuncionario, TelefonoFuncionario, CorreoFuncionario, CargoFuncionario, SedeFuncionario)
                VALUES (:nombre, :documento, :telefono, :correo, :cargo, :sede)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':nombre', $datos['NombreFuncionario']);
            $stmt->bindParam(':documento', $datos['DocumentoFuncionario']);
            $stmt->bindParam(':telefono', $datos['TelefonoFuncionario']);
            $stmt->bindParam(':correo', $datos['CorreoFuncionario']);
            $stmt->bindParam(':cargo', $datos['CargoFuncionario']);
            $stmt->bindParam(':sede', $datos['SedeFuncionario']);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al agregar funcionario: " . $e->getMessage());
            return false;
        }
    }

    // ✅ Obtener todos los funcionarios
    public function obtenerFuncionarios() {
        try {
            $sql = "SELECT * FROM funcionario";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener funcionarios: " . $e->getMessage());
            return [];
        }
    }

    // ✅ Filtrar por rol/cargo
    public function filtrarPorCargo($cargo) {
        try {
            $sql = "SELECT * FROM funcionario WHERE CargoFuncionario = :cargo";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cargo', $cargo);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al filtrar por cargo: " . $e->getMessage());
            return [];
        }
    }

    // ✅ Editar funcionario
    public function editarFuncionario($id, $datos) {
        try {
            $sql = "UPDATE funcionario 
                    SET NombreFuncionario = :nombre,
                        DocumentoFuncionario = :documento,
                        TelefonoFuncionario = :telefono,
                        CorreoFuncionario = :correo,
                        CargoFuncionario = :cargo,
                        SedeFuncionario = :sede
                    WHERE IdFuncionario = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':nombre', $datos['NombreFuncionario']);
            $stmt->bindParam(':documento', $datos['DocumentoFuncionario']);
            $stmt->bindParam(':telefono', $datos['TelefonoFuncionario']);
            $stmt->bindParam(':correo', $datos['CorreoFuncionario']);
            $stmt->bindParam(':cargo', $datos['CargoFuncionario']);
            $stmt->bindParam(':sede', $datos['SedeFuncionario']);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al editar funcionario: " . $e->getMessage());
            return false;
        }
    }

    // ✅ Eliminar funcionario
    public function eliminarFuncionario($id) {
        try {
            $sql = "DELETE FROM funcionario WHERE IdFuncionario = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar funcionario: " . $e->getMessage());
            return false;
        }
    }
}
?>
