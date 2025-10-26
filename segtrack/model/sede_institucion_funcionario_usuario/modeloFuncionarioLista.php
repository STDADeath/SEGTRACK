<?php
require_once __DIR__ . "/../Core/conexion.php";

class FuncionarioModel {
    private $pdo;

    public function __construct() {
        $this->pdo = (new Conexion())->getConexion();
    }

    // Crear funcionario
    public function registrar($nombre, $telefono, $correo, $documento, $cargo, $sede) {
        try {
            $sql = "INSERT INTO funcionario (NombreFuncionario, TelefonoFuncionario, CorreoFuncionario, DocumentoFuncionario, CargoFuncionario, SedeFuncionario)
                    VALUES (:nombre, :telefono, :correo, :documento, :cargo, :sede)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ":nombre" => $nombre,
                ":telefono" => $telefono,
                ":correo" => $correo,
                ":documento" => $documento,
                ":cargo" => $cargo,
                ":sede" => $sede
            ]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Listar todos
    public function listar() {
        $sql = "SELECT * FROM funcionario ORDER BY IdFuncionario DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener uno
    public function obtener($id) {
        $sql = "SELECT * FROM funcionario WHERE IdFuncionario = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([":id" => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar
    public function actualizar($id, $nombre, $telefono, $correo, $documento, $cargo, $sede) {
        try {
            $sql = "UPDATE funcionario SET
                    NombreFuncionario = :nombre,
                    TelefonoFuncionario = :telefono,
                    CorreoFuncionario = :correo,
                    DocumentoFuncionario = :documento,
                    CargoFuncionario = :cargo,
                    SedeFuncionario = :sede
                    WHERE IdFuncionario = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ":id" => $id,
                ":nombre" => $nombre,
                ":telefono" => $telefono,
                ":correo" => $correo,
                ":documento" => $documento,
                ":cargo" => $cargo,
                ":sede" => $sede
            ]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Eliminar
    public function eliminar($id) {
        try {
            $sql = "DELETE FROM funcionario WHERE IdFuncionario = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([":id" => $id]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
