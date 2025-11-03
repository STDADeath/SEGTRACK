<?php
    class ModeloFuncionario {
        private $conexion;

        public function __construct($conexion) {
            $this->conexion = $conexion;
        }

        public function RegistrarFuncionario(string $Cargo,string $nombre, int $sede,int $telefono, int $documento, string $correo): array {
        try {
            if (!$this->conexion){
                return ['success' => false, 'error' => 'conexion a la base de datos no establecida'];
            }
            $sql = "INSERT INTO funcionario (CargoFuncionaro, NombreFuncionario, IdSede, TelefonoFuncionario, DocumentoFuncionario, CorreoFuncionario)
                    VALUES (:cargo, :nombre, :sede, :telefono, :documento, :correo)";
            
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':cargo' => $Cargo,
                ':nombre' => $nombre,
                ':sede' => $sede,
                ':telefono' => $telefono,
                ':documento' => $documento,
                ':correo' => $correo
            ]);
            if ($resultado) {
                return[ 'success' => true, 'id' => $this->conexion->lastInsertId() ];
            } else {
                return ['success' => false, 'error' => $errorInfo[2] ?? 'Error desconocido al insertar'];
            } 
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];

        }
    }

    public function ActualizarQrFuncionario (int $IdFuncionario, string $RutaQr): array {
        try {
            if(!$this-> conexion);{
                return ['success' => false, 'error' => 'conexion a la base de datos no establecida'];
            }
            $sql = "UPDATE funcionario SET QrCodigoFuncionario = :qr WHERE IdFuncionario = :id";
            $stmt = $this->conexion->prepare($sql);
            $resultado  = $stmt->execute ([
                ':qr' => $RutaQr,
                ':id' => $IdFuncionario
            ]);

            return  [
                'success' => $resultado,
                'rows' => $stmt->rowCount()  
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
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

            $sql = "SELECT * FROM funcionario WHERE IdFuncionario =  :id";
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

            $sql = "SELECT QrCodigoFuncionario FROM funcionario WHERE IdFuncionario  = :id";
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
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            $sql = "UPDATE funcionarios SET 
                        CargoFuncionario = :cargo,
                        NombreFuncionario = :nombre,
                        IdSede = :sede,
                        TelefonoFuncionario = :telefono,
                        DocumentoFuncionario = :documento,
                        CorreoFuncionario = :correo
                    WHERE IdFuncionario = :id";

            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':cargo' => $datos['CargoFuncionario'],
                ':qr' => $datos['QrCodigoFuncionario'],
                ':nombre' => $datos['NombreFuncionario'],
                ':sede' => $datos['IdSede'],
                ':telefono' => $datos['TelefonoFuncionario'] ,
                ':documento' => $datos['DocumentoFuncionario'] ,
                ':correo' => $datos['CorreoFuncionario'] ,
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

            $sql = "SELECT 1 FROM funcionarios WHERE IdFuncionario = :id LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idFuncionario]);
            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            return false;
        }
    }
}


?>