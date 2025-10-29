<?php

<<<<<<< HEAD
class ModeloFuncionario {
    private $conexion;

    public function __construct() {
        $conexionObj = new Conexion();
        $this->conexion = $conexionObj->getConexion();
    }

    public function insertarFuncionario($datos) {
        try {
            // Validar duplicado
            $consulta = $this->conexion->prepare("SELECT COUNT(*) FROM funcionario WHERE DocumentoFuncionario = ?");
            $consulta->execute([$datos['DocumentoFuncionario']]);
            if ($consulta->fetchColumn() > 0) {
                return ['error' => '⚠️ El documento ya está registrado.'];
            }

            // Generar el código QR (solo texto, sin imagen)
            $codigoQR = 'QR-FUNC-' . strtoupper(substr(md5(uniqid()), 0, 5));

            // Insertar funcionario
            $sql = "INSERT INTO funcionario 
                    (NombreFuncionario, DocumentoFuncionario, CorreoFuncionario, TelefonoFuncionario, CargoFuncionario, IdSede, QrCodigoFuncionario)
                    VALUES (:nombre, :documento, :correo, :telefono, :cargo, :idsede, :qr)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':nombre', $datos['NombreFuncionario']);
            $stmt->bindParam(':documento', $datos['DocumentoFuncionario']);
            $stmt->bindParam(':correo', $datos['CorreoFuncionario']);
            $stmt->bindParam(':telefono', $datos['TelefonoFuncionario']);
            $stmt->bindParam(':cargo', $datos['CargoFuncionario']);
            $stmt->bindParam(':idsede', $datos['IdSede']);
            $stmt->bindParam(':qr', $codigoQR);

            $stmt->execute();

            return ['mensaje' => '✅ Registro exitoso. Código QR: ' . $codigoQR];
        } catch (PDOException $e) {
            return ['error' => '❌ Error al registrar: ' . $e->getMessage()];
        }
    }

    public function actualizarFuncionario($id, $datos) {
        try {
            $sql = "UPDATE funcionario 
                    SET NombreFuncionario = :nombre, DocumentoFuncionario = :documento,
                        CorreoFuncionario = :correo, TelefonoFuncionario = :telefono,
                        CargoFuncionario = :cargo, IdSede = :idsede
                    WHERE IdFuncionario = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':nombre' => $datos['NombreFuncionario'],
                ':documento' => $datos['DocumentoFuncionario'],
                ':correo' => $datos['CorreoFuncionario'],
                ':telefono' => $datos['TelefonoFuncionario'],
                ':cargo' => $datos['CargoFuncionario'],
                ':idsede' => $datos['IdSede'],
                ':id' => $id
            ]);
            return ['mensaje' => '✅ Funcionario actualizado correctamente.'];
        } catch (PDOException $e) {
            return ['error' => '❌ Error al actualizar: ' . $e->getMessage()];
        }
    }

    public function eliminarFuncionario($id) {
        try {
            $stmt = $this->conexion->prepare("DELETE FROM funcionario WHERE IdFuncionario = ?");
            $stmt->execute([$id]);
            return ['mensaje' => '🗑️ Funcionario eliminado correctamente.'];
        } catch (PDOException $e) {
            return ['error' => '❌ Error al eliminar: ' . $e->getMessage()];
        }
    }

    public function actualizarCargoSegunRol($idFuncionario) {
        try {
            $sql = "UPDATE funcionario f 
                    JOIN usuario u ON f.IdFuncionario = u.IdFuncionario 
                    SET f.CargoFuncionario = u.tipoRol 
                    WHERE f.IdFuncionario = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$idFuncionario]);
            return ['mensaje' => '🔄 Cargo sincronizado con el rol del usuario.'];
        } catch (PDOException $e) {
            return ['error' => '❌ Error al sincronizar cargo: ' . $e->getMessage()];
        }
    }
}
=======
class Modelofuncionario {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function RegistrarFuncionario(string $Cargo, string $Nombre, int $Sede, int $Telefono, int $Documento, string $Correo): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            $sql = "INSERT INTO funcionario
                    (CargoFuncionario, NombreFuncionario, IdSede, TelefonoFuncionario, DocumentoFuncionario, CorreoFuncionario)
                    VALUES (:Cargo, :Nombre, :Sede, :Telefono, :Documento, :Correo)";

          $stmt = $this -> conexion -> prepare($sql);
          $resultado = $stmt -> execute([
            ':cargo' => $Cargo,
            ':Nombre' =>$nombre,
            ':Sede' => $Sede,
            ':Telefono' => $Telefono,
            ':Documento' => $Documento,
            ':Correo' => $Correo

          ]);
          if ($resultado) {
            return ['success' => true, 'id' => $this->conexion->lastInsertId()];
          } else {
                 $errorInfo = $stmt->errorinfo();
                 return['success' => true, 'error' => $errorInfo[2] ?? 'error desconocido al insertar '];
           }
          } catch(PDOException $e){
           return ['success' => false, 'error' => $e->getMessage()];
          }
    }

        public function ActuaizarQR(int $IdFuncionario, string $rutaQR): array {
            try {
                if(!$this->conexion){
                    return ['success' => false, 'error' => 'conexion a la base de datos no disponible'];
                }

                $sql = "UPDATE funcionario SET QrCodigoFuncionario = :qr WHERE  IdFuncionario = :id";
                $stmt = $this->conexion->prepare($sql);
                $resultado = $stmt ->execute([
                    ':qr'=> $rutaQR,
                    'id' => $IdFuncionario
                ]);
            }
        }
}

>>>>>>> 5117bf3459d7c75113b2c6c82144a473bf2194c3
?>
