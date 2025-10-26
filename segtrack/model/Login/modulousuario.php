<?php

require_once(__DIR__ . "/../../Core/conexion.php");

class ModuloUsuario {
    private $pdo;

    public function __construct() {
        $this->pdo = (new Conexion())->getConexion();
    }

    /**
     * validarLogin
     * Busca por correo o documento en la tabla funcionario,
     * obtiene la contraseña desde la tabla usuario (join por IdFuncionario)
     * Retorna el array $usuario si todo OK, o false si no.
     *
     * IMPORTANTE: para depuración devuelve mensajes claros (temporal).
     */
    public function validarLogin($correo, $contrasena) {
        try {
            $sql = "
                SELECT 
                    u.IdUsuario,
                    u.TipoRol,
                    u.Contrasena,
                    f.IdFuncionario,
                    f.NombreFuncionario,
                    f.CorreoFuncionario,
                    f.DocumentoFuncionario
                FROM usuario u
                INNER JOIN funcionario f ON f.IdFuncionario = u.IdFuncionario
                WHERE f.CorreoFuncionario = :correo
                   OR f.DocumentoFuncionario = :correo
                LIMIT 1
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":correo", $correo, PDO::PARAM_STR);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                // Depuración clara: no encontró registro
                return [
                    'ok' => false,
                    'reason' => 'no_user',
                    'message' => 'No existe funcionario con ese correo o documento'
                ];
            }

            // Comparamos contraseñas sin hash (tu caso actual).
            $stored = trim((string)$usuario['Contrasena']);
            $given  = trim((string)$contrasena);

            if ($stored === $given) {
                // Credenciales correctas: devolver datos útiles
                return [
                    'ok' => true,
                    'usuario' => $usuario
                ];
            } else {
                // Depuración: contraseña no coincide
                return [
                    'ok' => false,
                    'reason' => 'bad_password',
                    'message' => 'La contraseña no coincide',
                    'stored' => $stored,    // temporal: muestra lo que hay en BD
                    'given'  => $given      // temporal: muestra lo que enviaste
                ];
            }

        } catch (PDOException $e) {
            return [
                'ok' => false,
                'reason' => 'exception',
                'message' => 'Error en BD: ' . $e->getMessage()
            ];
        }
    }

    /* - Los demás métodos (agregar/editar/eliminar/filtrar) quedan igual
       que tenías antes: los dejo intactos para que no rompa nada. */
}
?>
