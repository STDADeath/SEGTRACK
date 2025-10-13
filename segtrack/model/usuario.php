<?php 
// ✅ Incluir la clase Conexion ANTES de usarla
require_once __DIR__ . '/../Controller/Conexion/conexion.php';
class Usuario {
    private $conexion;

    public function __construct() {
        $db = new Conexion(); // ✅ Asignar a $db
        $this->conexion = $db->getConexion(); // ✅ Ahora sí existe $db
    }

    /**
     * Valida las credenciales ingresadas en el login.
     * @param string $correo Correo del funcionario.
     * @param string $contrasena Contraseña del usuario.
     * @return array Resultado con éxito o error.
     */
    public function validarLogin(string $correo, string $contrasena): array {
        try {
            $sql = "SELECT u.IdUsuario, u.TipoRol, u.Contrasena, f.NombreFuncionario, f.IdFuncionario, f.CorreoFuncionario
                    FROM usuario u
                    INNER JOIN funcionario f ON u.IdFuncionario = f.IdFuncionario
                    WHERE f.CorreoFuncionario = :correo
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':correo' => $correo]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                return ['success' => false, 'mensaje' => 'El correo no existe'];
            }

            // 🔐 Verificar la contraseña encriptada
            if (password_verify($contrasena, $usuario['Contrasena'])) {
                return ['success' => true, 'usuario' => $usuario];
            } else {
                return ['success' => false, 'mensaje' => 'Contraseña incorrecta'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'mensaje' => $e->getMessage()];
        }
    }
}
?>