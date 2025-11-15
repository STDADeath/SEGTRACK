<?php
require_once __DIR__ . '/../../Core/conexion.php';

class Modelo_Usuario {
    private $conexion;

    public function __construct() {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    // ==========================================
    // ✅ CREAR - REGISTRAR USUARIO
    // ==========================================
    public function registrarUsuario($tipoRol, $contrasena, $idFuncionario) {
        try {
            $sql = "INSERT INTO usuario (TipoRol, Contrasena, IdFuncionario) 
                    VALUES (:tipoRol, :contrasena, :idFuncionario)";
            $stmt = $this->conexion->prepare($sql);
            
            // Encriptar contraseña
            $hash = password_hash($contrasena, PASSWORD_BCRYPT);

            $stmt->bindParam(':tipoRol', $tipoRol);
            $stmt->bindParam(':contrasena', $hash);
            $stmt->bindParam(':idFuncionario', $idFuncionario, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al registrar usuario: " . $e->getMessage());
        }
    }

    // ==========================================
    // ✅ LEER - VERIFICAR SI USUARIO EXISTE
    // ==========================================
    public function usuarioExiste($idFuncionario) {
        try {
            $sql = "SELECT COUNT(*) as total FROM usuario WHERE IdFuncionario = :id_funcionario";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_funcionario', $idFuncionario, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $resultado['total'] > 0;
        } catch (PDOException $e) {
            throw new Exception("Error al verificar usuario existente: " . $e->getMessage());
        }
    }

    // ==========================================
    // ✅ LEER - OBTENER TODOS LOS USUARIOS
    // ==========================================
    public function obtenerTodosUsuarios() {
        try {
            $sql = "SELECT u.IdUsuario, u.TipoRol, u.IdFuncionario, 
                           f.NombreFuncionario, f.DocumentoFuncionario, f.CargoFuncionario
                    FROM usuario u
                    INNER JOIN funcionario f ON u.IdFuncionario = f.IdFuncionario
                    ORDER BY u.IdUsuario DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener usuarios: " . $e->getMessage());
        }
    }

    // ==========================================
    // ✅ LEER - OBTENER USUARIO POR ID
    // ==========================================
    public function obtenerUsuarioPorId($idUsuario) {
        try {
            $sql = "SELECT u.IdUsuario, u.TipoRol, u.IdFuncionario,
                           f.NombreFuncionario, f.DocumentoFuncionario, f.CargoFuncionario
                    FROM usuario u
                    INNER JOIN funcionario f ON u.IdFuncionario = f.IdFuncionario
                    WHERE u.IdUsuario = :id_usuario";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener usuario: " . $e->getMessage());
        }
    }

    // ==========================================
    // ✅ FILTRAR - BUSCAR USUARIOS POR CRITERIO
    // ==========================================
    public function filtrarUsuarios($criterio, $valor) {
        try {
            // Criterios válidos: 'rol', 'nombre', 'documento', 'cargo'
            $sqlBase = "SELECT u.IdUsuario, u.TipoRol, u.IdFuncionario, 
                               f.NombreFuncionario, f.DocumentoFuncionario, f.CargoFuncionario
                        FROM usuario u
                        INNER JOIN funcionario f ON u.IdFuncionario = f.IdFuncionario
                        WHERE ";
            
            switch ($criterio) {
                case 'rol':
                    $sql = $sqlBase . "u.TipoRol LIKE :valor";
                    break;
                case 'nombre':
                    $sql = $sqlBase . "f.NombreFuncionario LIKE :valor";
                    break;
                case 'documento':
                    $sql = $sqlBase . "f.DocumentoFuncionario LIKE :valor";
                    break;
                case 'cargo':
                    $sql = $sqlBase . "f.CargoFuncionario LIKE :valor";
                    break;
                default:
                    throw new Exception("Criterio de búsqueda no válido");
            }
            
            $sql .= " ORDER BY u.IdUsuario DESC";
            
            $stmt = $this->conexion->prepare($sql);
            $valorBusqueda = "%{$valor}%";
            $stmt->bindParam(':valor', $valorBusqueda);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al filtrar usuarios: " . $e->getMessage());
        }
    }

    // ==========================================
    // ✅ ACTUALIZAR - MODIFICAR USUARIO
    // ==========================================
    public function actualizarUsuario($idUsuario, $tipoRol, $contrasena = null) {
        try {
            // Si se proporciona nueva contraseña, actualizar también
            if ($contrasena !== null && $contrasena !== '') {
                $sql = "UPDATE usuario 
                        SET TipoRol = :tipoRol, Contrasena = :contrasena
                        WHERE IdUsuario = :id_usuario";
                $stmt = $this->conexion->prepare($sql);
                
                $hash = password_hash($contrasena, PASSWORD_BCRYPT);
                $stmt->bindParam(':contrasena', $hash);
            } else {
                // Solo actualizar el rol
                $sql = "UPDATE usuario 
                        SET TipoRol = :tipoRol
                        WHERE IdUsuario = :id_usuario";
                $stmt = $this->conexion->prepare($sql);
            }
            
            $stmt->bindParam(':tipoRol', $tipoRol);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al actualizar usuario: " . $e->getMessage());
        }
    }

    // ==========================================
    // ✅ ACTUALIZAR - CAMBIAR SOLO CONTRASEÑA
    // ==========================================
    public function cambiarContrasena($idUsuario, $nuevaContrasena) {
        try {
            $sql = "UPDATE usuario 
                    SET Contrasena = :contrasena
                    WHERE IdUsuario = :id_usuario";
            $stmt = $this->conexion->prepare($sql);
            
            $hash = password_hash($nuevaContrasena, PASSWORD_BCRYPT);
            $stmt->bindParam(':contrasena', $hash);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al cambiar contraseña: " . $e->getMessage());
        }
    }

    // ==========================================
    // ✅ ELIMINAR - BORRAR USUARIO
    // ==========================================
    public function eliminarUsuario($idUsuario) {
        try {
            $sql = "DELETE FROM usuario WHERE IdUsuario = :id_usuario";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al eliminar usuario: " . $e->getMessage());
        }
    }

    // ==========================================
    // ✅ VALIDACIÓN - VERIFICAR CREDENCIALES LOGIN
    // ==========================================
    public function validarLogin($idFuncionario, $contrasena) {
        try {
            $sql = "SELECT u.IdUsuario, u.TipoRol, u.Contrasena, u.IdFuncionario,
                           f.NombreFuncionario
                    FROM usuario u
                    INNER JOIN funcionario f ON u.IdFuncionario = f.IdFuncionario
                    WHERE u.IdFuncionario = :id_funcionario";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_funcionario', $idFuncionario, PDO::PARAM_INT);
            $stmt->execute();
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario && password_verify($contrasena, $usuario['Contrasena'])) {
                // Remover contraseña del array antes de retornar
                unset($usuario['Contrasena']);
                return $usuario;
            }
            
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error al validar credenciales: " . $e->getMessage());
        }
    }

    // ==========================================
    // ✅ CONTAR - TOTAL DE USUARIOS
    // ==========================================
    public function contarUsuarios() {
        try {
            $sql = "SELECT COUNT(*) as total FROM usuario";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'];
        } catch (PDOException $e) {
            throw new Exception("Error al contar usuarios: " . $e->getMessage());
        }
    }
}