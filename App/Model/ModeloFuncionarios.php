<?php
class ModeloFuncionario {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    /**
     * ✅ Registra un nuevo funcionario en la base de datos
     */
    public function RegistrarFuncionario(string $cargo, string $nombre, int $idSede, int $telefono, int $documento, string $correo): array {
        try {
            // Log de inicio
            file_put_contents(__DIR__ . '/../Controller/debug_log.txt', "=== MODELO: registrarFuncionario ===\n", FILE_APPEND);
            file_put_contents(__DIR__ . '/../Controller/debug_log.txt', "Cargo: $cargo, Nombre: $nombre, Sede: $idSede, Tel: $telefono, Doc: $documento\n", FILE_APPEND);

            if (!$this->conexion) {
                file_put_contents(__DIR__ . '/../Controller/debug_log.txt', "ERROR: Conexión no disponible\n", FILE_APPEND);
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            file_put_contents(__DIR__ . '/../Controller/debug_log.txt', "Conexión OK, preparando SQL\n", FILE_APPEND);

            // ⭐ CAMBIO: Agregamos QrCodigoFuncionario con valor vacío temporal
            $sql = "INSERT INTO funcionario 
                    (CargoFuncionario, NombreFuncionario, IdSede, TelefonoFuncionario, DocumentoFuncionario, CorreoFuncionario, QrCodigoFuncionario)
                    VALUES (:cargo, :nombre, :sede, :telefono, :documento, :correo, '')";

            file_put_contents(__DIR__ . '/../Controller/debug_log.txt', "SQL preparado: $sql\n", FILE_APPEND);

            $stmt = $this->conexion->prepare($sql);
            
            $params = [
                ':cargo' => $cargo,
                ':nombre' => $nombre,
                ':sede' => $idSede,
                ':telefono' => $telefono,
                ':documento' => $documento,
                ':correo' => $correo
            ];
            
            file_put_contents(__DIR__ . '/../Controller/debug_log.txt', "Parámetros: " . json_encode($params) . "\n", FILE_APPEND);
            
            $resultado = $stmt->execute($params);

            file_put_contents(__DIR__ . '/../Controller/debug_log.txt', "Resultado execute: " . ($resultado ? 'true' : 'false') . "\n", FILE_APPEND);

            if ($resultado) {
                $lastId = $this->conexion->lastInsertId();
                file_put_contents(__DIR__ . '/../Controller/debug_log.txt', "INSERT exitoso, ID generado: $lastId\n", FILE_APPEND);
                return ['success' => true, 'id' => $lastId];
            } else {
                $errorInfo = $stmt->errorInfo();
                file_put_contents(__DIR__ . '/../Controller/debug_log.txt', "ERROR en execute: " . json_encode($errorInfo) . "\n", FILE_APPEND);
                return ['success' => false, 'error' => $errorInfo[2] ?? 'Error desconocido al insertar'];
            }

        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            file_put_contents(__DIR__ . '/../Controller/debug_log.txt', "EXCEPCIÓN PDO: $errorMsg\n", FILE_APPEND);
            return ['success' => false, 'error' => $errorMsg];
        }
    }

    /**
     * ✅ Actualiza la ruta del código QR generado
     */
    public function ActualizarQrFuncionario(int $idFuncionario, string $rutaQR): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            $sql = "UPDATE funcionario SET QrCodigoFuncionario = :qr WHERE IdFuncionario = :id";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':qr' => $rutaQR,
                ':id' => $idFuncionario
            ]);

            return [
                'success' => $resultado,
                'rows' => $stmt->rowCount()
            ];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * ✅ Obtiene todos los funcionarios
     */
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

    /**
     * ✅ Obtiene un funcionario por su ID (incluye QR)
     */
    public function obtenerPorId(int $idFuncionario): ?array {
        try {
            if (!$this->conexion) {
                return null;
            }

            $sql = "SELECT * FROM funcionario WHERE IdFuncionario = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idFuncionario]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * ✅ Obtiene solo la ruta del QR de un funcionario
     */
    public function obtenerQR(int $idFuncionario): ?string {
        try {
            if (!$this->conexion) {
                return null;
            }

            $sql = "SELECT QrCodigoFuncionario FROM funcionario WHERE IdFuncionario = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idFuncionario]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $resultado['QrCodigoFuncionario'] ?? null;

        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * ✅ Actualiza los datos del funcionario (sin tocar el QR)
     */
    public function actualizar(int $idFuncionario, array $datos): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            $sql = "UPDATE funcionario SET 
                        CargoFuncionario = :cargo, 
                        NombreFuncionario = :nombre, 
                        IdSede = :sede, 
                        TelefonoFuncionario = :telefono, 
                        DocumentoFuncionario = :documento, 
                        CorreoFuncionario = :correo
                    WHERE IdFuncionario = :id";

            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':cargo' => $datos['CargoFuncionario'] ?? null,
                ':nombre' => $datos['NombreFuncionario'] ?? null,
                ':sede' => $datos['IdSede'] ?? null,
                ':telefono' => $datos['TelefonoFuncionario'] ?? null,
                ':documento' => $datos['DocumentoFuncionario'] ?? null,
                ':correo' => $datos['CorreoFuncionario'] ?? null,
                ':id' => $idFuncionario
            ]);

            return [
                'success' => $resultado,
                'rows' => $stmt->rowCount()
            ];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * ✅ Verifica si existe un funcionario
     */
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
}
?>