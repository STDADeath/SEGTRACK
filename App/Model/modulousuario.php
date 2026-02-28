<?php

/**
 * Modelo encargado de gestionar operaciones del usuario
 * incluyendo login y filtros de funcionarios.
 */

require_once __DIR__ . '/../Core/conexion.php';

class ModuloUsuario {

    /**
     * @var PDO
     */
    private $conexion;

    /**
     * Constructor
     * Inicializa la conexión a la base de datos
     */
    public function __construct() {
        $this->conexion = (new Conexion())->getConexion();
    }

    /**
     * ==========================================
     * 🔐 VALIDAR LOGIN
     * ==========================================
     * Permite iniciar sesión usando:
     * - Correo electrónico
     * - Documento
     *
     * @param string $correo
     * @param string $contrasena
     * @return array
     */
    public function validarLogin(string $correo, string $contrasena): array {

        try {

            // Consulta preparada para evitar SQL Injection
            $sql = "SELECT 
                        u.IdUsuario,
                        u.TipoRol,
                        u.Contrasena,
                        f.IdFuncionario,
                        f.NombreFuncionario,
                        f.CorreoFuncionario,
                        f.DocumentoFuncionario,
                        f.IdSede
                    FROM usuario u
                    INNER JOIN funcionario f 
                        ON u.IdFuncionario = f.IdFuncionario
                    WHERE (f.CorreoFuncionario = :correo 
                        OR f.DocumentoFuncionario = :correo)
                    AND u.Estado = 'Activo'
                    LIMIT 1"; // ← CORREGIDO

            $stmt = $this->conexion->prepare($sql);

            // Bind del parámetro
            $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);

            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // Si no existe el usuario
            if (!$usuario) {
                return [
                    'ok' => false,
                    'message' => '❌ No existe usuario con ese correo o documento.'
                ];
            }

            // Obtener contraseña almacenada
            $hashBD = trim($usuario['Contrasena']);
            $loginValido = false;

            /**
             * Verificación de contraseña:
             * - Primero intenta password_verify (hash moderno)
             * - Si no coincide, compara texto plano (para migración)
             */
            if (password_verify($contrasena, $hashBD)) {

                $loginValido = true;

            } elseif ($contrasena === $hashBD) {

                // Migrar automáticamente a hash seguro
                $loginValido = true;

                $nuevoHash = password_hash($contrasena, PASSWORD_DEFAULT);

                $update = $this->conexion->prepare(
                    "UPDATE usuario 
                     SET Contrasena = :newHash 
                     WHERE IdUsuario = :id"
                );

                $update->execute([
                    ':newHash' => $nuevoHash,
                    ':id'      => $usuario['IdUsuario']
                ]);
            }

            // Si contraseña incorrecta
            if (!$loginValido) {
                return [
                    'ok' => false,
                    'message' => '❌ Contraseña incorrecta.'
                ];
            }

            // Retornar datos del usuario
            return [
                'ok' => true,
                'usuario' => [
                    'IdUsuario'        => $usuario['IdUsuario'],
                    'IdFuncionario'    => $usuario['IdFuncionario'],
                    'NombreFuncionario'=> $usuario['NombreFuncionario'],
                    'CorreoFuncionario'=> $usuario['CorreoFuncionario'],
                    'TipoRol'          => $usuario['TipoRol'],
                    'IdSede'           => $usuario['IdSede']
                ]
            ];

        } catch (PDOException $e) {

            // Error controlado de base de datos
            return [
                'ok' => false,
                'message' => 'Error en BD: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ==========================================
     * 🔄 ACTUALIZAR ROL
     * ==========================================
     */
    public function actualizarRol(int $idFuncionario, string $nuevoRol): bool {

        try {

            $sql = "UPDATE usuario 
                    SET TipoRol = :rol 
                    WHERE IdFuncionario = :id";

            $stmt = $this->conexion->prepare($sql);

            return $stmt->execute([
                ':rol' => $nuevoRol,
                ':id'  => $idFuncionario
            ]);

        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * ==========================================
     * 🔍 FILTRAR FUNCIONARIOS POR CARGO
     * ==========================================
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
            $stmt->bindParam(':cargo', $cargo, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * ==========================================
     * 🔎 FILTRAR FUNCIONARIO POR ID
     * ==========================================
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
            $stmt->bindParam(':id', $idFuncionario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * ==========================================
     * 📧 FILTRAR FUNCIONARIO POR CORREO
     * ==========================================
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
            $stmt->bindParam(':correo', $correoFuncionario, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return null;
        }
    }
}
