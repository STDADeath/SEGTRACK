<?php
class ModeloDispositivo {
    private $conexion;
    private $debugPath;

    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->debugPath = __DIR__ . '/../Controller/Debug_Disp/debug_log.txt';

        $carpetaDebug = dirname($this->debugPath);
        if (!file_exists($carpetaDebug)) {
            mkdir($carpetaDebug, 0777, true);
        }
    }

    /**
     * REGISTRAR - Solo guarda IdFuncionario o IdVisitante
     */
    public function registrarDispositivo(
        string $tipo,
        string $marca,
        string $numeroSerial,
        ?int   $idFuncionario,
        ?int   $idVisitante
    ): array {
        try {
            file_put_contents($this->debugPath,
                "=== MODELO: registrarDispositivo ===\nTipo: $tipo, Marca: $marca, Serial: $numeroSerial\nFuncionario: $idFuncionario, Visitante: $idVisitante\n",
                FILE_APPEND
            );

            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            $sql = "INSERT INTO dispositivo 
                        (TipoDispositivo, MarcaDispositivo, NumeroSerial, IdFuncionario, IdVisitante, QrDispositivo, Estado)
                    VALUES 
                        (:tipo, :marca, :serial, :funcionario, :visitante, '', 'Activo')";

            $stmt   = $this->conexion->prepare($sql);
            $params = [
                ':tipo'        => $tipo,
                ':marca'       => $marca,
                ':serial'      => $numeroSerial ?: null,
                ':funcionario' => $idFuncionario,
                ':visitante'   => $idVisitante,
            ];

            $resultado = $stmt->execute($params);

            if ($resultado) {
                $lastId = $this->conexion->lastInsertId();
                file_put_contents($this->debugPath, "INSERT exitoso, ID: $lastId\n", FILE_APPEND);
                return ['success' => true, 'id' => $lastId];
            } else {
                $errorInfo = $stmt->errorInfo();
                file_put_contents($this->debugPath, "ERROR en execute: " . json_encode($errorInfo) . "\n", FILE_APPEND);
                return ['success' => false, 'error' => $errorInfo[2] ?? 'Error desconocido al insertar'];
            }

        } catch (PDOException $e) {
            file_put_contents($this->debugPath, "EXCEPCIÓN PDO: " . $e->getMessage() . "\n", FILE_APPEND);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Actualiza la ruta del código QR
     */
    public function actualizarQR(int $idDispositivo, string $rutaQR): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión no disponible'];
            }

            $sql  = "UPDATE dispositivo SET QrDispositivo = :qr WHERE IdDispositivo = :id";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([':qr' => $rutaQR, ':id' => $idDispositivo]);

            return ['success' => $resultado, 'rows' => $stmt->rowCount()];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Obtiene todos los dispositivos ACTIVOS con nombres (incluyendo institución y sede mediante JOIN)
     */
    public function obtenerTodos(): array {
        try {
            if (!$this->conexion) return [];

            $sql = "SELECT 
                        d.*,
                        f.NombreFuncionario,
                        f.CorreoFuncionario,
                        v.NombreVisitante,
                        v.CorreoVisitante,
                        s.TipoSede,
                        s.Ciudad,
                        i.NombreInstitucion
                    FROM dispositivo d
                    LEFT JOIN funcionario f ON d.IdFuncionario = f.IdFuncionario
                    LEFT JOIN visitante   v ON d.IdVisitante   = v.IdVisitante
                    LEFT JOIN sede        s ON COALESCE(f.IdSede, v.IdSede) = s.IdSede
                    LEFT JOIN institucion i ON s.IdInstitucion = i.IdInstitucion
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
     * Obtiene todos los dispositivos (activos e inactivos)
     */
    public function obtenerTodosConEstado(): array {
        try {
            if (!$this->conexion) return [];

            $sql = "SELECT 
                        d.*,
                        f.NombreFuncionario,
                        f.CorreoFuncionario,
                        v.NombreVisitante,
                        v.CorreoVisitante,
                        s.TipoSede,
                        s.Ciudad,
                        i.NombreInstitucion
                    FROM dispositivo d
                    LEFT JOIN funcionario f ON d.IdFuncionario = f.IdFuncionario
                    LEFT JOIN visitante   v ON d.IdVisitante   = v.IdVisitante
                    LEFT JOIN sede        s ON COALESCE(f.IdSede, v.IdSede) = s.IdSede
                    LEFT JOIN institucion i ON s.IdInstitucion = i.IdInstitucion
                    ORDER BY CASE WHEN d.Estado = 'Activo' THEN 1 ELSE 2 END, d.IdDispositivo DESC";
                    
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            file_put_contents($this->debugPath, "ERROR en obtenerTodosConEstado: " . $e->getMessage() . "\n", FILE_APPEND);
            return [];
        }
    }

    /**
     * Obtiene un dispositivo por su ID
     */
    public function obtenerPorId(int $idDispositivo): ?array {
        try {
            if (!$this->conexion) return null;

            $sql = "SELECT 
                        d.*,
                        f.NombreFuncionario,
                        f.CorreoFuncionario,
                        v.NombreVisitante,
                        v.CorreoVisitante,
                        s.TipoSede,
                        s.Ciudad,
                        i.NombreInstitucion
                    FROM dispositivo d
                    LEFT JOIN funcionario f ON d.IdFuncionario = f.IdFuncionario
                    LEFT JOIN visitante   v ON d.IdVisitante   = v.IdVisitante
                    LEFT JOIN sede        s ON COALESCE(f.IdSede, v.IdSede) = s.IdSede
                    LEFT JOIN institucion i ON s.IdInstitucion = i.IdInstitucion
                    WHERE d.IdDispositivo = :id";
                    
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idDispositivo]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Obtiene solo la ruta QR de un dispositivo
     */
    public function obtenerQR(int $idDispositivo): ?string {
        try {
            if (!$this->conexion) return null;

            $sql  = "SELECT QrDispositivo FROM dispositivo WHERE IdDispositivo = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idDispositivo]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['QrDispositivo'] ?? null;

        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * ACTUALIZAR dispositivo
     */
    public function actualizar(int $idDispositivo, array $datos): array {
        try {
            file_put_contents($this->debugPath, "=== MODELO: actualizar ID: $idDispositivo ===\n" . json_encode($datos) . "\n", FILE_APPEND);

            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión no disponible'];
            }

            $sql = "UPDATE dispositivo SET 
                        TipoDispositivo  = :tipo,
                        MarcaDispositivo = :marca,
                        NumeroSerial     = :serial,
                        IdFuncionario    = :funcionario,
                        IdVisitante      = :visitante
                    WHERE IdDispositivo = :id";

            $stmt = $this->conexion->prepare($sql);

            $idFunc = null;
            if (isset($datos['IdFuncionario']) && $datos['IdFuncionario'] !== '' && $datos['IdFuncionario'] !== null) {
                $idFunc = (int)$datos['IdFuncionario'];
            }

            $idVis = null;
            if (isset($datos['IdVisitante']) && $datos['IdVisitante'] !== '' && $datos['IdVisitante'] !== null) {
                $idVis = (int)$datos['IdVisitante'];
            }

            $params = [
                ':tipo'        => $datos['TipoDispositivo']  ?? null,
                ':marca'       => $datos['MarcaDispositivo'] ?? null,
                ':serial'      => !empty($datos['NumeroSerial']) ? $datos['NumeroSerial'] : null,
                ':funcionario' => $idFunc,
                ':visitante'   => $idVis,
                ':id'          => $idDispositivo
            ];

            $resultado = $stmt->execute($params);

            if (!$resultado) {
                $errorInfo = $stmt->errorInfo();
                file_put_contents($this->debugPath, "Error SQL: " . json_encode($errorInfo) . "\n", FILE_APPEND);
                return ['success' => false, 'error' => $errorInfo[2] ?? 'Error desconocido'];
            }

            return ['success' => true, 'rows' => $stmt->rowCount()];

        } catch (PDOException $e) {
            file_put_contents($this->debugPath, "EXCEPCIÓN PDO: " . $e->getMessage() . "\n", FILE_APPEND);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cambia el estado del dispositivo
     */
    public function cambiarEstado(int $idDispositivo, string $nuevoEstado): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión no disponible'];
            }

            if (!in_array($nuevoEstado, ['Activo', 'Inactivo'])) {
                return ['success' => false, 'error' => 'Estado no válido'];
            }

            $sql  = "UPDATE dispositivo SET Estado = :estado WHERE IdDispositivo = :id";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([':estado' => $nuevoEstado, ':id' => $idDispositivo]);

            return ['success' => $resultado, 'rows' => $stmt->rowCount(), 'nuevoEstado' => $nuevoEstado];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Verifica si existe un número serial (excluyendo ID para edición)
     */
    public function existeNumeroSerial(string $numeroSerial, ?int $excluirId = null): array {
        try {
            if (!$this->conexion || empty(trim($numeroSerial))) {
                return ['existe' => false];
            }

            if ($excluirId !== null) {
                $sql = "SELECT IdDispositivo, TipoDispositivo, MarcaDispositivo
                        FROM dispositivo
                        WHERE NumeroSerial = :serial AND IdDispositivo != :id
                        LIMIT 1";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([':serial' => $numeroSerial, ':id' => $excluirId]);
            } else {
                $sql = "SELECT IdDispositivo, TipoDispositivo, MarcaDispositivo
                        FROM dispositivo
                        WHERE NumeroSerial = :serial
                        LIMIT 1";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([':serial' => $numeroSerial]);
            }

            $dispositivo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dispositivo) {
                return ['existe' => true, 'dispositivo' => $dispositivo];
            }

            return ['existe' => false];

        } catch (PDOException $e) {
            file_put_contents($this->debugPath, "ERROR en existeNumeroSerial: " . $e->getMessage() . "\n", FILE_APPEND);
            return ['existe' => false, 'error' => $e->getMessage()];
        }
    }

    // ════════════════════════════════════════════════════════════════
    // MÉTODOS PARA CASCADA (No guardan en dispositivo, solo para selects)
    // ════════════════════════════════════════════════════════════════

    /**
     * Obtiene todas las instituciones activas
     */
    public function obtenerInstituciones(): array {
        try {
            $sql = "SELECT IdInstitucion, NombreInstitucion 
                    FROM institucion 
                    WHERE EstadoInstitucion = 'Activo' 
                    ORDER BY NombreInstitucion ASC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtiene sedes por institución
     */
    public function obtenerSedesPorInstitucion(int $idInstitucion): array {
        try {
            $sql = "SELECT IdSede, TipoSede, Ciudad 
                    FROM sede 
                    WHERE IdInstitucion = :id AND Estado = 'Activo'
                    ORDER BY TipoSede ASC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idInstitucion]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtiene funcionarios por sede
     */
    public function obtenerFuncionariosPorSede(int $idSede): array {
        try {
            $sql = "SELECT IdFuncionario, NombreFuncionario 
                    FROM funcionario 
                    WHERE IdSede = :id AND Estado = 'Activo'
                    ORDER BY NombreFuncionario ASC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idSede]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtiene visitantes por sede
     */
    public function obtenerVisitantesPorSede(int $idSede): array {
        try {
            $sql = "SELECT IdVisitante, NombreVisitante 
                    FROM visitante 
                    WHERE IdSede = :id AND Estado = 'Activo'
                    ORDER BY NombreVisitante ASC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idSede]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>