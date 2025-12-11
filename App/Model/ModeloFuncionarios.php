
<?php
class ModeloFuncionario {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // =======================================================
    // 🔍 VALIDAR SI EL DOCUMENTO YA EXISTE
    // =======================================================
    public function existeDocumento(int $documento): bool {
        try {
            $sql = "SELECT IdFuncionario FROM funcionario WHERE DocumentoFuncionario = :documento LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':documento' => $documento]);

            // Si encuentra un registro, retorna true
            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            return false; // Si falla, lo tomamos como que no existe
        }
    }

    // =======================================================
    // 🟩 REGISTRAR FUNCIONARIO (CON VALIDACIÓN DE CÉDULA)
    // =======================================================
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

            // ===========================
            // 🛑 VALIDACIÓN NUEVA AQUÍ
            // ===========================
            if ($this->existeDocumento($documento)) {
                return [
                    'success' => false,
                    'error'   => 'La cédula ya está registrada en el sistema'
                ];
            }
            // ===========================

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

    // =======================================================
    // NO MODIFIQUÉ NADA DE ESTO — TODO IGUAL
    // =======================================================

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

    public function ActualizarFuncionario(
        int $idFuncionario,
        string $Cargo,
        string $nombre,
        int $sede,
        int $telefono,
        int $documento,
        string $correo
    ): array {

        $datos = [
            'CargoFuncionario'    => $Cargo,
            'NombreFuncionario'   => $nombre,
            'IdSede'              => $sede,
            'TelefonoFuncionario' => $telefono,
            'DocumentoFuncionario'=> $documento,
            'CorreoFuncionario'   => $correo
        ];
        
        $resultado = $this->actualizar($idFuncionario, $datos);
        
        if ($resultado['success'] === true) {
            return [
                'success' => true,
                'rows_affected' => $resultado['rows']
            ];
        } else {
            return [
                'success' => false,
                'error' => $resultado['error'] ?? 'Error desconocido al actualizar'
            ];
        }
    }

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

    public function cambiarEstado(int $idFuncionario, string $nuevoEstado): array {
        try {
            if (!$this->conexion) {
                return [
                    'success' => false,
                    'error' => 'Conexión a la base de datos no establecida'
                ];
            }

            $sql = "UPDATE funcionario 
                    SET Estado = :estado 
                    WHERE IdFuncionario = :id";

            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':estado' => $nuevoEstado,
                ':id'     => $idFuncionario
            ]);

            return [
                'success' => $resultado,
                'rows'    => $stmt->rowCount()
            ];

        } catch (PDOException $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage()
            ];
        }
    }
}
?>