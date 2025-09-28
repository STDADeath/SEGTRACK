<?php
require_once "conexion.php";

class Funcionario {
    private $conn;

    public function __construct() {
        $this->conn = (new Conexion())->getConexion();
    }

    // Método para registrar un funcionario
    public function registrar($NombreFuncionario, $DocumentoFuncionario, $TelefonoFuncionario, $CorreoFuncionario, $CargoFuncionario, $IdSede) {
        try {
            // 1. Validar campos vacíos
            if (empty($NombreFuncionario) || empty($DocumentoFuncionario) || empty($TelefonoFuncionario) || empty($CorreoFuncionario) || empty($CargoFuncionario) || empty($IdSede)) {
                return "❌ Complete todos los campos obligatorios.";
            }

            // 2. Validar duplicado en documento
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM funcionario WHERE DocumentoFuncionario = ?");
            $stmt->execute([$DocumentoFuncionario]);
            if ($stmt->fetchColumn() > 0) {
                return "❌ Ya existe un funcionario con el documento $DocumentoFuncionario.";
            }

            // 3. Validar duplicado en correo
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM funcionario WHERE CorreoFuncionario = ?");
            $stmt->execute([$CorreoFuncionario]);
            if ($stmt->fetchColumn() > 0) {
                return "❌ Ya existe un funcionario con el correo $CorreoFuncionario.";
            }

            // 4. Validar sede existente
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM sede WHERE IdSede = ?");
            $stmt->execute([$IdSede]);
            if ($stmt->fetchColumn() == 0) {
                return "❌ La sede seleccionada no existe.";
            }

            // 5. Generar QR único
            $QrCodigoFuncionario = "QR-FUNC-" . strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));

            // 6. Insertar funcionario
            $sql = "INSERT INTO funcionario 
                    (CargoFuncionario, QrCodigoFuncionario, NombreFuncionario, IdSede, TelefonoFuncionario, DocumentoFuncionario, CorreoFuncionario) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$CargoFuncionario, $QrCodigoFuncionario, $NombreFuncionario, $IdSede, $TelefonoFuncionario, $DocumentoFuncionario, $CorreoFuncionario]);

            // 7. Retornar mensaje de éxito con datos
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
}

?>
