<?php
require_once __DIR__ . '../../Core/conexion.php';

class ModuloUsuario {
    private $conexion;

    public function __construct() {
        $this->conexion = (new Conexion())->getConexion();
    }

    public function validarLogin($correo, $contrasena) {
        try {
            $sql = "SELECT 
                        u.IdUsuario,
                        u.TipoRol,
                        u.Contrasena AS Contrasena,
                        f.IdFuncionario,
                        f.NombreFuncionario,
                        f.CorreoFuncionario,
                        f.DocumentoFuncionario,
                        f.IdSede
                    FROM usuario u
                    INNER JOIN funcionario f 
                        ON u.IdFuncionario = f.IdFuncionario
                    WHERE f.CorreoFuncionario = :correo 
                        OR f.DocumentoFuncionario = :correo
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':correo', $correo);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                return ['ok'=>false, 'message'=>'❌ No existe usuario con ese correo o documento.'];
            }

            $hashBD = trim($usuario['Contrasena']);
            $loginValido = false;

            // Verificar contraseña hash o texto plano
            if (password_verify($contrasena, $hashBD)) {
                $loginValido = true;
            } elseif ($contrasena === $hashBD) {
                $loginValido = true;
                // Actualizar a hash
                $nuevoHash = password_hash($contrasena, PASSWORD_DEFAULT);
                $update = $this->conexion->prepare("UPDATE usuario SET Contrasena = :newHash WHERE IdUsuario = :id");
                $update->execute([':newHash'=>$nuevoHash, ':id'=>$usuario['IdUsuario']]);
            }

            if (!$loginValido) {
                return ['ok'=>false, 'message'=>'❌ Contraseña incorrecta.'];
            }

            return ['ok'=>true, 'usuario'=>[
                'IdUsuario'=>$usuario['IdUsuario'],
                'IdFuncionario'=>$usuario['IdFuncionario'],
                'NombreFuncionario'=>$usuario['NombreFuncionario'],
                'CorreoFuncionario'=>$usuario['CorreoFuncionario'],
                'TipoRol'=>$usuario['TipoRol'],
                'IdSede'=>$usuario['IdSede']
            ]];

        } catch (PDOException $e) {
            return ['ok'=>false, 'message'=>'Error en BD: '.$e->getMessage()];
        }
    }

    public function actualizarRol($idFuncionario, $nuevoRol) {
        try {
            $update = $this->conexion->prepare("UPDATE usuario SET TipoRol = :rol WHERE IdFuncionario = :id");
            $update->execute([':rol'=>$nuevoRol, ':id'=>$idFuncionario]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
