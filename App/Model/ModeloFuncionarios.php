<?php
class ModeloFuncionario {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function RegistrarFuncionario(
        string $Cargo,
        string $nombre,
        int $sede,
        int $telefono,
        int $documento,
        string $correo
    ): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no establecida'];
            }

            $sql = "INSERT INTO funcionario 
                         (CargoFuncionario, NombreFuncionario, IdSede, TelefonoFuncionario, DocumentoFuncionario, CorreoFuncionario)
                    VALUES 
                         (:cargo, :nombre, :sede, :telefono, :documento, :correo)";

            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':cargo'      => $Cargo,
                ':nombre'     => $nombre,
                ':sede'       => $sede,
                ':telefono'   => $telefono,
                ':documento'  => $documento,
                ':correo'     => $correo
            ]);

            if ($resultado) {
                return [
                    'success' => true,
                    'id'      => $this->conexion->lastInsertId()
                ];
            } else {
                $errorInfo = $stmt->errorInfo();
                return [
                    'success' => false,
                    'error'   => $errorInfo[2] ?? 'Error desconocido al insertar'
                ];
            }

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function ActualizarQrFuncionario(int $IdFuncionario, string $RutaQr): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no establecida'];
            }

            $sql = "UPDATE funcionario 
                    SET QrCodigoFuncionario = :qr 
                    WHERE IdFuncionario = :id";

            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':qr' => $RutaQr,
                ':id' => $IdFuncionario
            ]);

            return [
                'success' => $resultado,
                'rows'    => $stmt->rowCount()
            ];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // =======================================================
    // ✅ MÉTODO AÑADIDO: Implementación de la firma esperada por el controlador
    // =======================================================
    public function ActualizarFuncionario(
        int $idFuncionario,
        string $Cargo,
        string $nombre,
        int $sede,
        int $telefono,
        int $documento,
        string $correo
    ): array {
        // Mapeamos los argumentos individuales al formato de array que necesita
        // el método 'actualizar' que ya estaba definido.
        $datos = [
            'CargoFuncionario'    => $Cargo,
            'NombreFuncionario'   => $nombre,
            'IdSede'              => $sede,
            'TelefonoFuncionario' => $telefono,
            'DocumentoFuncionario'=> $documento,
            'CorreoFuncionario'   => $correo
        ];
        
        // Llamamos al método que ya contiene la lógica SQL de UPDATE
        $resultado = $this->actualizar($idFuncionario, $datos);
        
        // Adaptamos la respuesta para el controlador que espera 'success' y 'rows_affected'
        if ($resultado['success'] === true) {
            return [
                'success' => true,
                'rows_affected' => $resultado['rows'] // Devolvemos las filas afectadas
            ];
        } else {
            return [
                'success' => false,
                'error' => $resultado['error'] ?? 'Error desconocido al actualizar'
            ];
        }
    }
    // =======================================================


    public function obtenerTodos(): array {
        try {
            if (!$this->conexion) {
                return [];
            }

            $sql = "SELECT * FROM funcionario ORDER BY IdFuncionario DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerPorId(int $IdFuncionario): ?array {
        try {
            if (!$this->conexion) {
                return null;
            }

            $sql = "SELECT * FROM funcionario WHERE IdFuncionario = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $IdFuncionario]);

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (PDOException $e) {
            return null;
        }
    }

    public function obtenerQR(int $IdFuncionario): ?string {
        try {
            if (!$this->conexion) {
                return null;
            }

            $sql = "SELECT QrCodigoFuncionario FROM funcionario WHERE IdFuncionario = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $IdFuncionario]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            return $resultado['QrCodigoFuncionario'] ?? null;

        } catch (PDOException $e) {
            return null;
        }
    }

    public function actualizar(int $idFuncionario, array $datos): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no establecida'];
            }

            $sql = "UPDATE funcionario SET 
                        CargoFuncionario     = :cargo,
                        NombreFuncionario    = :nombre,
                        IdSede               = :sede,
                        TelefonoFuncionario  = :telefono,
                        DocumentoFuncionario = :documento,
                        CorreoFuncionario    = :correo
                    WHERE IdFuncionario = :id";

            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':cargo'      => $datos['CargoFuncionario'],
                ':nombre'     => $datos['NombreFuncionario'],
                ':sede'       => $datos['IdSede'],
                ':telefono'   => $datos['TelefonoFuncionario'],
                ':documento'  => $datos['DocumentoFuncionario'],
                ':correo'     => $datos['CorreoFuncionario'],
                ':id'         => $idFuncionario
            ]);

            return [
                'success' => $resultado,
                'rows'    => $stmt->rowCount()
            ];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function existe(int $idFuncionario): bool {
        try {
            if (!$this->conexion) {
                return false;
            }

            $sql = "SELECT 1 FROM funcionario WHERE IdFuncionario = :id LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idFuncionario]);

            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            return false;
        }
    }

    // ========================================
    // ✨ NUEVO MÉTODO: CAMBIAR ESTADO DEL FUNCIONARIO
    // ========================================
    /**
     * Cambia el estado de un funcionario en la base de datos
     * @param int $idFuncionario - ID del funcionario a modificar
     * @param string $nuevoEstado - Nuevo estado ('Activo' o 'Inactivo')
     * @return array - Array con 'success' (bool) y 'rows' (int) o 'error' (string)
     */
    public function cambiarEstado(int $idFuncionario, string $nuevoEstado): array {
        try {
            // Verifica que la conexión PDO esté establecida
            if (!$this->conexion) {
                return [
                    'success' => false,
                    'error' => 'Conexión a la base de datos no establecida'
                ];
            }

            // Prepara la consulta SQL para actualizar solo el campo Estado
            // WHERE asegura que solo se actualice el funcionario con el ID especificado
            $sql = "UPDATE funcionario 
                    SET Estado = :estado 
                    WHERE IdFuncionario = :id";

            // Prepara la sentencia SQL para prevenir inyección SQL
            $stmt = $this->conexion->prepare($sql);
            
            // Ejecuta la consulta pasando los parámetros de forma segura
            $resultado = $stmt->execute([
                ':estado' => $nuevoEstado,      // Nuevo estado a establecer
                ':id'     => $idFuncionario     // ID del funcionario a actualizar
            ]);

            // Retorna un array indicando si la operación fue exitosa
            // y cuántas filas fueron afectadas (debería ser 1 si el ID existe)
            return [
                'success' => $resultado,           // true si la ejecución fue exitosa
                'rows'    => $stmt->rowCount()    // Número de filas afectadas
            ];

        } catch (PDOException $e) {
            // Captura cualquier error de PDO y lo retorna en el array
            return [
                'success' => false,
                'error'   => $e->getMessage()    // Mensaje de error detallado
            ];
        }
    }
}
?>