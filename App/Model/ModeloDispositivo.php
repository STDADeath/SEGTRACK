<?php
class ModeloDispositivo {
    private $conexion;
    private $debugPath;

    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->debugPath = __DIR__ . '/../Controller/Debug_Disp/debug_log.txt';
        
        // Crear carpeta Debug_Disp si no existe
        $carpetaDebug = dirname($this->debugPath);
        if (!file_exists($carpetaDebug)) {
            mkdir($carpetaDebug, 0777, true);
        }
    }

    /**
     * Registra un nuevo dispositivo en la base de datos
     */
    public function registrarDispositivo(string $tipo, string $marca, string $numeroSerial, ?int $idFuncionario, ?int $idVisitante): array {
        try {
            file_put_contents($this->debugPath, "=== MODELO: registrarDispositivo ===\n", FILE_APPEND);
            file_put_contents($this->debugPath, "Tipo: $tipo, Marca: $marca, Serial: $numeroSerial, IdFunc: $idFuncionario, IdVis: $idVisitante\n", FILE_APPEND);

            if (!$this->conexion) {
                file_put_contents($this->debugPath, "ERROR: Conexión no disponible\n", FILE_APPEND);
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            file_put_contents($this->debugPath, "Conexión OK, preparando SQL\n", FILE_APPEND);

            $sql = "INSERT INTO dispositivo 
                    (TipoDispositivo, MarcaDispositivo, NumeroSerial, IdFuncionario, IdVisitante, QrDispositivo, Estado)
                    VALUES (:tipo, :marca, :serial, :funcionario, :visitante, '', 'Activo')";

            file_put_contents($this->debugPath, "SQL preparado: $sql\n", FILE_APPEND);

            $stmt = $this->conexion->prepare($sql);
            
            $params = [
                ':tipo' => $tipo,
                ':marca' => $marca,
                ':serial' => $numeroSerial,
                ':funcionario' => $idFuncionario ?: null,
                ':visitante' => $idVisitante ?: null
            ];
            
            file_put_contents($this->debugPath, "Parámetros: " . json_encode($params) . "\n", FILE_APPEND);
            
            $resultado = $stmt->execute($params);

            file_put_contents($this->debugPath, "Resultado execute: " . ($resultado ? 'true' : 'false') . "\n", FILE_APPEND);

            if ($resultado) {
                $lastId = $this->conexion->lastInsertId();
                file_put_contents($this->debugPath, "INSERT exitoso, ID generado: $lastId\n", FILE_APPEND);
                return ['success' => true, 'id' => $lastId];
            } else {
                $errorInfo = $stmt->errorInfo();
                file_put_contents($this->debugPath, "ERROR en execute: " . json_encode($errorInfo) . "\n", FILE_APPEND);
                return ['success' => false, 'error' => $errorInfo[2] ?? 'Error desconocido al insertar'];
            }

        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            file_put_contents($this->debugPath, "EXCEPCIÓN PDO: $errorMsg\n", FILE_APPEND);
            return ['success' => false, 'error' => $errorMsg];
        }
    }

    /**
     * Actualiza la ruta del código QR generado
     */
    public function actualizarQR(int $idDispositivo, string $rutaQR): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            $sql = "UPDATE dispositivo SET QrDispositivo = :qr WHERE IdDispositivo = :id";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':qr' => $rutaQR,
                ':id' => $idDispositivo
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
     * Obtiene todos los dispositivos ACTIVOS con nombres de funcionarios y visitantes
     */
    public function obtenerTodos(): array {
        try {
            if (!$this->conexion) {
                return [];
            }

            $sql = "SELECT 
                        d.*,
                        f.NombreFuncionario,
                        v.NombreVisitante
                    FROM dispositivo d
                    LEFT JOIN funcionario f ON d.IdFuncionario = f.IdFuncionario
                    LEFT JOIN visitante v ON d.IdVisitante = v.IdVisitante
                    WHERE d.Estado = 'Activo' 
                    ORDER BY d.IdDispositivo DESC";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            file_put_contents($this->debugPath, "ERROR en obtenerTodos: " . $e->getMessage() . "\n", FILE_APPEND);
            return [];
        }
    }

    /**
     * Obtiene todos los dispositivos (incluye activos e inactivos) con nombres
     */
    public function obtenerTodosConEstado(): array {
        try {
            if (!$this->conexion) {
                return [];
            }

            $sql = "SELECT 
                        d.*,
                        f.NombreFuncionario,
                        v.NombreVisitante
                    FROM dispositivo d
                    LEFT JOIN funcionario f ON d.IdFuncionario = f.IdFuncionario
                    LEFT JOIN visitante v ON d.IdVisitante = v.IdVisitante
                    ORDER BY 
                        CASE 
                            WHEN d.Estado = 'Activo' THEN 1 
                            WHEN d.Estado = 'Inactivo' THEN 2 
                            ELSE 3 
                        END, 
                        d.IdDispositivo DESC";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            file_put_contents($this->debugPath, "ERROR en obtenerTodosConEstado: " . $e->getMessage() . "\n", FILE_APPEND);
            return [];
        }
    }

    /**
     * Obtiene un dispositivo por su ID (incluye QR y nombres)
     */
    public function obtenerPorId(int $idDispositivo): ?array {
        try {
            if (!$this->conexion) {
                return null;
            }

            $sql = "SELECT 
                        d.*,
                        f.NombreFuncionario,
                        v.NombreVisitante
                    FROM dispositivo d
                    LEFT JOIN funcionario f ON d.IdFuncionario = f.IdFuncionario
                    LEFT JOIN visitante v ON d.IdVisitante = v.IdVisitante
                    WHERE d.IdDispositivo = :id";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idDispositivo]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Obtiene solo la ruta del QR de un dispositivo
     */
    public function obtenerQR(int $idDispositivo): ?string {
        try {
            if (!$this->conexion) {
                return null;
            }

            $sql = "SELECT QrDispositivo FROM dispositivo WHERE IdDispositivo = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idDispositivo]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $resultado['QrDispositivo'] ?? null;

        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Obtiene todos los funcionarios activos para el select
     */
    public function obtenerFuncionarios(): array {
        try {
            if (!$this->conexion) {
                return [];
            }

            $sql = "SELECT IdFuncionario, NombreFuncionario 
                    FROM funcionario 
                    WHERE Estado = 'Activo' 
                    ORDER BY NombreFuncionario ASC";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            file_put_contents($this->debugPath, "ERROR en obtenerFuncionarios: " . $e->getMessage() . "\n", FILE_APPEND);
            return [];
        }
    }

    /**
     * Obtiene todos los visitantes activos para el select
     */
    public function obtenerVisitantes(): array {
        try {
            if (!$this->conexion) {
                return [];
            }

            $sql = "SELECT IdVisitante, NombreVisitante 
                    FROM visitante 
                    WHERE Estado = 'Activo' 
                    ORDER BY NombreVisitante ASC";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            file_put_contents($this->debugPath, "ERROR en obtenerVisitantes: " . $e->getMessage() . "\n", FILE_APPEND);
            return [];
        }
    }

    /**
     * Actualiza los datos del dispositivo (incluye NumeroSerial)
     */
    public function actualizar(int $idDispositivo, array $datos): array {
        try {
            file_put_contents($this->debugPath, "=== MODELO: actualizar ID: $idDispositivo ===\n", FILE_APPEND);
            file_put_contents($this->debugPath, "Datos recibidos: " . json_encode($datos) . "\n", FILE_APPEND);
            
            if (!$this->conexion) {
                file_put_contents($this->debugPath, "ERROR: Conexión no disponible\n", FILE_APPEND);
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            $sql = "UPDATE dispositivo SET 
                        TipoDispositivo = :tipo, 
                        MarcaDispositivo = :marca,
                        NumeroSerial = :serial,
                        IdFuncionario = :funcionario, 
                        IdVisitante = :visitante
                    WHERE IdDispositivo = :id";

            file_put_contents($this->debugPath, "SQL: $sql\n", FILE_APPEND);

            $stmt = $this->conexion->prepare($sql);
            
            // Procesar los valores correctamente
            $idFunc = null;
            if (isset($datos['IdFuncionario']) && $datos['IdFuncionario'] !== '' && $datos['IdFuncionario'] !== null) {
                $idFunc = (int)$datos['IdFuncionario'];
            }
            
            $idVis = null;
            if (isset($datos['IdVisitante']) && $datos['IdVisitante'] !== '' && $datos['IdVisitante'] !== null) {
                $idVis = (int)$datos['IdVisitante'];
            }
            
            $params = [
                ':tipo' => $datos['TipoDispositivo'] ?? null,
                ':marca' => $datos['MarcaDispositivo'] ?? null,
                ':serial' => $datos['NumeroSerial'] ?? null,
                ':funcionario' => $idFunc,
                ':visitante' => $idVis,
                ':id' => $idDispositivo
            ];
            
            file_put_contents($this->debugPath, "Parámetros SQL: " . json_encode($params) . "\n", FILE_APPEND);
            
            $resultado = $stmt->execute($params);
            
            file_put_contents($this->debugPath, "Resultado execute: " . ($resultado ? 'true' : 'false') . "\n", FILE_APPEND);
            file_put_contents($this->debugPath, "Filas afectadas: " . $stmt->rowCount() . "\n", FILE_APPEND);

            if (!$resultado) {
                $errorInfo = $stmt->errorInfo();
                file_put_contents($this->debugPath, "Error SQL: " . json_encode($errorInfo) . "\n", FILE_APPEND);
                return ['success' => false, 'error' => $errorInfo[2] ?? 'Error desconocido'];
            }

            return [
                'success' => true,
                'rows' => $stmt->rowCount()
            ];

        } catch (PDOException $e) {
            file_put_contents($this->debugPath, "EXCEPCIÓN PDO: " . $e->getMessage() . "\n", FILE_APPEND);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cambia el estado del dispositivo (Activo <-> Inactivo)
     */
    public function cambiarEstado(int $idDispositivo, string $nuevoEstado): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            if (!in_array($nuevoEstado, ['Activo', 'Inactivo'])) {
                return ['success' => false, 'error' => 'Estado no válido'];
            }

            $sql = "UPDATE dispositivo SET Estado = :estado WHERE IdDispositivo = :id";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':estado' => $nuevoEstado,
                ':id' => $idDispositivo
            ]);

            return [
                'success' => $resultado,
                'rows' => $stmt->rowCount(),
                'nuevoEstado' => $nuevoEstado
            ];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Verifica si existe un dispositivo
     */
    public function existe(int $idDispositivo): bool {
        try {
            if (!$this->conexion) {
                return false;
            }

            $sql = "SELECT 1 FROM dispositivo WHERE IdDispositivo = :id LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idDispositivo]);
            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * 🆕 Verifica si un número serial ya existe (excluyendo un ID específico para edición)
     */
    public function existeNumeroSerial(string $numeroSerial, ?int $excluirId = null): array {
        try {
            if (!$this->conexion) {
                return ['existe' => false];
            }

            // Solo validar si el serial no está vacío
            if (empty(trim($numeroSerial))) {
                return ['existe' => false];
            }

            if ($excluirId !== null) {
                // Para edición: excluir el ID actual
                $sql = "SELECT IdDispositivo, TipoDispositivo, MarcaDispositivo 
                        FROM dispositivo 
                        WHERE NumeroSerial = :serial 
                        AND IdDispositivo != :id 
                        AND Estado = 'Activo'
                        LIMIT 1";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([':serial' => $numeroSerial, ':id' => $excluirId]);
            } else {
                // Para registro nuevo
                $sql = "SELECT IdDispositivo, TipoDispositivo, MarcaDispositivo 
                        FROM dispositivo 
                        WHERE NumeroSerial = :serial 
                        AND Estado = 'Activo'
                        LIMIT 1";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([':serial' => $numeroSerial]);
            }

            $dispositivo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dispositivo) {
                return [
                    'existe' => true,
                    'dispositivo' => $dispositivo
                ];
            }

            return ['existe' => false];

        } catch (PDOException $e) {
            file_put_contents($this->debugPath, "ERROR en existeNumeroSerial: " . $e->getMessage() . "\n", FILE_APPEND);
            return ['existe' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * 🆕 Verifica si un funcionario ya tiene un dispositivo activo del mismo tipo
     */
    public function funcionarioTieneDispositivo(int $idFuncionario, string $tipoDispositivo, ?int $excluirId = null): array {
        try {
            if (!$this->conexion) {
                return ['existe' => false];
            }

            if ($excluirId !== null) {
                // Para edición: excluir el ID actual
                $sql = "SELECT d.IdDispositivo, d.TipoDispositivo, d.MarcaDispositivo, d.NumeroSerial
                        FROM dispositivo d
                        WHERE d.IdFuncionario = :idFunc 
                        AND d.TipoDispositivo = :tipo
                        AND d.IdDispositivo != :id
                        AND d.Estado = 'Activo'
                        LIMIT 1";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([
                    ':idFunc' => $idFuncionario,
                    ':tipo' => $tipoDispositivo,
                    ':id' => $excluirId
                ]);
            } else {
                // Para registro nuevo
                $sql = "SELECT d.IdDispositivo, d.TipoDispositivo, d.MarcaDispositivo, d.NumeroSerial
                        FROM dispositivo d
                        WHERE d.IdFuncionario = :idFunc 
                        AND d.TipoDispositivo = :tipo
                        AND d.Estado = 'Activo'
                        LIMIT 1";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([
                    ':idFunc' => $idFuncionario,
                    ':tipo' => $tipoDispositivo
                ]);
            }

            $dispositivo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dispositivo) {
                return [
                    'existe' => true,
                    'dispositivo' => $dispositivo
                ];
            }

            return ['existe' => false];

        } catch (PDOException $e) {
            file_put_contents($this->debugPath, "ERROR en funcionarioTieneDispositivo: " . $e->getMessage() . "\n", FILE_APPEND);
            return ['existe' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * 🆕 Verifica si un visitante ya tiene un dispositivo activo del mismo tipo
     */
    public function visitanteTieneDispositivo(int $idVisitante, string $tipoDispositivo, ?int $excluirId = null): array {
        try {
            if (!$this->conexion) {
                return ['existe' => false];
            }

            if ($excluirId !== null) {
                // Para edición: excluir el ID actual
                $sql = "SELECT d.IdDispositivo, d.TipoDispositivo, d.MarcaDispositivo, d.NumeroSerial
                        FROM dispositivo d
                        WHERE d.IdVisitante = :idVis 
                        AND d.TipoDispositivo = :tipo
                        AND d.IdDispositivo != :id
                        AND d.Estado = 'Activo'
                        LIMIT 1";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([
                    ':idVis' => $idVisitante,
                    ':tipo' => $tipoDispositivo,
                    ':id' => $excluirId
                ]);
            } else {
                // Para registro nuevo
                $sql = "SELECT d.IdDispositivo, d.TipoDispositivo, d.MarcaDispositivo, d.NumeroSerial
                        FROM dispositivo d
                        WHERE d.IdVisitante = :idVis 
                        AND d.TipoDispositivo = :tipo
                        AND d.Estado = 'Activo'
                        LIMIT 1";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([
                    ':idVis' => $idVisitante,
                    ':tipo' => $tipoDispositivo
                ]);
            }

            $dispositivo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dispositivo) {
                return [
                    'existe' => true,
                    'dispositivo' => $dispositivo
                ];
            }

            return ['existe' => false];

        } catch (PDOException $e) {
            file_put_contents($this->debugPath, "ERROR en visitanteTieneDispositivo: " . $e->getMessage() . "\n", FILE_APPEND);
            return ['existe' => false, 'error' => $e->getMessage()];
        }
    }
}
?>