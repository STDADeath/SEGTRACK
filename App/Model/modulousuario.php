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
    
    // ------------------------------------------------------------------
    // 🔍 NUEVAS FUNCIONES DE FILTRADO CON CONSULTAS PREPARADAS
    // ------------------------------------------------------------------

    /**
     * Filtra funcionarios por Cargo.
     * @param string $cargo El cargo por el que se desea filtrar.
     * @return array Un array de funcionarios que coinciden con el cargo.
     */
    public function filtrarFuncionariosPorCargo(string $cargo): array {
        try {
            $sql = "SELECT 
                        f.IdFuncionario,
                        f.NombreFuncionario,
                        f.CorreoFuncionario,
                        f.DocumentoFuncionario,
                        f.CargoFuncionario,
                        f.IdSede
                    FROM funcionario f
                    WHERE f.CargoFuncionario = :cargo
                    ORDER BY f.NombreFuncionario ASC";

            $stmt = $this->conexion->prepare($sql);
            
            // Vinculación de parámetro de tipo STR (String)
            $stmt->bindParam(':cargo', $cargo, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            // Manejo de errores de BD
            return [];
        }
    }

    /**
     * Obtiene un funcionario por su ID.
     * @param int $idFuncionario El ID del funcionario.
     * @return array|null El funcionario o null si no se encuentra o hay un error.
     */
    public function filtrarFuncionarioPorId(int $idFuncionario): ?array {
        try {
            $sql = "SELECT 
                        f.IdFuncionario,
                        f.NombreFuncionario,
                        f.CorreoFuncionario,
                        f.DocumentoFuncionario,
                        f.CargoFuncionario,
                        f.IdSede
                    FROM funcionario f
                    WHERE f.IdFuncionario = :id
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            
            // Vinculación de parámetro de tipo INT (Integer)
            $stmt->bindParam(':id', $idFuncionario, PDO::PARAM_INT);
            $stmt->execute();

            // Retorna una sola fila
            return $stmt->fetch(PDO::FETCH_ASSOC); 

        } catch (PDOException $e) {
            // Manejo de errores de BD
            return null;
        }
    }
    
    /**
     * Obtiene un funcionario por su Correo Electrónico.
     * @param string $correoFuncionario El correo electrónico del funcionario.
     * @return array|null El funcionario o null si no se encuentra o hay un error.
     */
    public function filtrarFuncionarioPorCorreo(string $correoFuncionario): ?array {
        try {
            $sql = "SELECT 
                        f.IdFuncionario,
                        f.NombreFuncionario,
                        f.CorreoFuncionario,
                        f.DocumentoFuncionario,
                        f.CargoFuncionario,
                        f.IdSede
                    FROM funcionario f
                    WHERE f.CorreoFuncionario = :correo
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            
            // Vinculación de parámetro de tipo STR (String)
            $stmt->bindParam(':correo', $correoFuncionario, PDO::PARAM_STR);
            $stmt->execute();

            // Retorna una sola fila
            return $stmt->fetch(PDO::FETCH_ASSOC); 
        } catch (PDOException $e) {
            // Manejo de errores de BD
            return null;
        }
    }
}