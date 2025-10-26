<?php
require_once __DIR__ . '/../../Core/conexion.php';

class ModeloFuncionario {

    private $conexion;

    public function __construct() {
        // ✅ Obtenemos la conexión desde la clase Conexion
        $conexionObj = new Conexion();
        $this->conexion = $conexionObj->getConexion();
    }

    // ➕ Insertar funcionario
    public function insertarFuncionario($datos) {
    try {
        // ✅ 1. Verificar si ya existe un funcionario con ese documento o correo
        $sqlVerificar = "SELECT COUNT(*) FROM funcionario 
                         WHERE DocumentoFuncionario = :DocumentoFuncionario 
                            OR CorreoFuncionario = :CorreoFuncionario";
        $stmtVerificar = $this->conexion->prepare($sqlVerificar);
        $stmtVerificar->execute([
            ':DocumentoFuncionario' => $datos['DocumentoFuncionario'],
            ':CorreoFuncionario' => $datos['CorreoFuncionario']
        ]);

        if ($stmtVerificar->fetchColumn() > 0) {
            return ['error' => '⚠️ Ya existe un funcionario con ese documento o correo.'];
        }

        // ✅ 2. Insertar si no existe duplicado
        $sql = "INSERT INTO funcionario 
                (NombreFuncionario, DocumentoFuncionario, CorreoFuncionario, TelefonoFuncionario, CargoFuncionario, IdSede)
                VALUES (:NombreFuncionario, :DocumentoFuncionario, :CorreoFuncionario, :TelefonoFuncionario, :CargoFuncionario, :IdSede)";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($datos);

        return ['mensaje' => '✅ Funcionario registrado correctamente'];

    } catch (PDOException $e) {
        return ['error' => '❌ Error al insertar: ' . $e->getMessage()];
    }
}

    // 🔄 Actualizar funcionario
    public function actualizarFuncionario($id, $datos) {
        try {
            $sql = "UPDATE funcionario 
                    SET NombreFuncionario = :NombreFuncionario,
                        DocumentoFuncionario = :DocumentoFuncionario,
                        CorreoFuncionario = :CorreoFuncionario,
                        TelefonoFuncionario = :TelefonoFuncionario,
                        CargoFuncionario = :CargoFuncionario,
                        IdSede = :IdSede
                    WHERE IdFuncionario = :IdFuncionario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute(array_merge($datos, ['IdFuncionario' => $id]));

            return ['mensaje' => '✅ Funcionario actualizado correctamente'];
        } catch (PDOException $e) {
            return ['error' => '❌ Error al actualizar: ' . $e->getMessage()];
        }
    }

    // ❌ Eliminar funcionario
    public function eliminarFuncionario($id) {
        try {
            $sql = "DELETE FROM funcionario WHERE IdFuncionario = :IdFuncionario";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute(['IdFuncionario' => $id]);
            return ['mensaje' => '🗑️ Funcionario eliminado correctamente'];
        } catch (PDOException $e) {
            return ['error' => '❌ Error al eliminar: ' . $e->getMessage()];
        }
    }
  
    public function verificarDuplicado($documento, $correo) {
    try {
        $sql = "SELECT COUNT(*) FROM funcionario 
                WHERE DocumentoFuncionario = :documento OR CorreoFuncionario = :correo";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['documento' => $documento, 'correo' => $correo]);
        $existe = $stmt->fetchColumn();
        return $existe > 0;
    } catch (PDOException $e) {
        return false;
    }
}

    // 🔍 Filtrar funcionario por ID
    public function filtrarFuncionarioPorId($id) {
        try {
            $sql = "SELECT * FROM funcionario WHERE IdFuncionario = :IdFuncionario";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute(['IdFuncionario' => $id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: ['error' => '⚠️ Funcionario no encontrado'];
        } catch (PDOException $e) {
            return ['error' => '❌ Error al filtrar: ' . $e->getMessage()];
        }
    }

    // 📋 Listar todos los funcionarios
    public function listarFuncionarios() {
        try {
            $sql = "SELECT * FROM funcionario";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => '❌ Error al listar: ' . $e->getMessage()];
        }
    }
}
?>
