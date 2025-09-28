<?php
<?php
require_once "conexion.php";

class Funcionario {
    private $conn;

    public function __construct() {
        $this->conn = (new Conexion())->getConexion();
    }

    // ✅ Registrar funcionario
    public function registrar($NombreFuncionario, $DocumentoFuncionario, $TelefonoFuncionario, $CorreoFuncionario, $CargoFuncionario, $IdSede) {
        try {
            if (empty($NombreFuncionario) || empty($DocumentoFuncionario) || empty($TelefonoFuncionario) || empty($CorreoFuncionario) || empty($CargoFuncionario) || empty($IdSede)) {
                return "❌ Complete todos los campos obligatorios.";
            }

            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM funcionario WHERE DocumentoFuncionario = ?");
            $stmt->execute([$DocumentoFuncionario]);
            if ($stmt->fetchColumn() > 0) {
                return "❌ Ya existe un funcionario con el documento $DocumentoFuncionario.";
            }

            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM funcionario WHERE CorreoFuncionario = ?");
            $stmt->execute([$CorreoFuncionario]);
            if ($stmt->fetchColumn() > 0) {
                return "❌ Ya existe un funcionario con el correo $CorreoFuncionario.";
            }

            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM sede WHERE IdSede = ?");
            $stmt->execute([$IdSede]);
            if ($stmt->fetchColumn() == 0) {
                return "❌ La sede seleccionada no existe.";
            }

            $QrCodigoFuncionario = "QR-FUNC-" . strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));

            $sql = "INSERT INTO funcionario 
                    (CargoFuncionario, QrCodigoFuncionario, NombreFuncionario, IdSede, TelefonoFuncionario, DocumentoFuncionario, CorreoFuncionario) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$CargoFuncionario, $QrCodigoFuncionario, $NombreFuncionario, $IdSede, $TelefonoFuncionario, $DocumentoFuncionario, $CorreoFuncionario]);

            $idInsertado = $this->conn->lastInsertId();
            return "
                ✅ Funcionario registrado correctamente.<br><br>
                <strong>ID:</strong> $idInsertado <br>
                <strong>Nombre:</strong> $NombreFuncionario <br>
                <strong>Documento:</strong> $DocumentoFuncionario <br>
                <strong>Teléfono:</strong> $TelefonoFuncionario <br>
                <strong>Correo:</strong> $CorreoFuncionario <br>
                <strong>Cargo:</strong> $CargoFuncionario <br>
                <strong>Sede:</strong> $IdSede <br>
                <strong>Código QR:</strong> $QrCodigoFuncionario <br>
            ";

        } catch (PDOException $e) {
            return "❌ Error en el registro: " . $e->getMessage();
        }
    }

    // ✅ Obtener todos los funcionarios
    public function obtenerTodos() {
        try {
            $stmt = $this->conn->query("SELECT * FROM funcionario");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // ✅ Obtener funcionario por ID
    public function obtenerPorId($IdFuncionario) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM funcionario WHERE IdFuncionario = ?");
            $stmt->execute([$IdFuncionario]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    // ✅ Actualizar funcionario
    public function actualizar($IdFuncionario, $NombreFuncionario, $DocumentoFuncionario, $TelefonoFuncionario, $CorreoFuncionario, $CargoFuncionario, $IdSede) {
        try {
            $sql = "UPDATE funcionario SET 
                        NombreFuncionario = ?, 
                        DocumentoFuncionario = ?, 
                        TelefonoFuncionario = ?, 
                        CorreoFuncionario = ?, 
                        CargoFuncionario = ?, 
                        IdSede = ?
                    WHERE IdFuncionario = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$NombreFuncionario, $DocumentoFuncionario, $TelefonoFuncionario, $CorreoFuncionario, $CargoFuncionario, $IdSede, $IdFuncionario]);

            return "✅ Funcionario actualizado correctamente.";
        } catch (PDOException $e) {
            return "❌ Error al actualizar: " . $e->getMessage();
        }
    }

    // ✅ Eliminar funcionario
    public function eliminar($IdFuncionario) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM funcionario WHERE IdFuncionario = ?");
            $stmt->execute([$IdFuncionario]);
            return "✅ Funcionario eliminado correctamente.";
        } catch (PDOException $e) {
            return "❌ Error al eliminar: " . $e->getMessage();
        }
    }
}
?>

?>
