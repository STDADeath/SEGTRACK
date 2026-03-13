<?php
// ======================================================================
// MODELO: ModeloFuncionarios.php
// ======================================================================

class ModeloFuncionarios
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    // ══════════════════════════════════════════════════════
    // REGISTRAR FUNCIONARIO
    // ✅ Teléfono y Documento como STRING para evitar
    //    truncamiento de ceros y desbordamiento de int
    // ══════════════════════════════════════════════════════
    public function RegistrarFuncionario(
        string $Cargo,
        string $nombre,
        int    $sede,
        string $telefono,   // ✅ era int — cambiado a string
        string $documento,  // ✅ era int — cambiado a string
        string $correo
    ): array {

        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión no establecida'];
            }

            if (!$this->existeSedeActiva($sede)) {
                return ['success' => false, 'error' => 'La sede no existe o está inactiva'];
            }

            if ($this->existeDuplicado($documento, $correo)) {
                return ['success' => false, 'error' => 'Documento o correo ya registrados'];
            }

            $sql = "INSERT INTO funcionario
                        (CargoFuncionario, NombreFuncionario, IdSede,
                         TelefonoFuncionario, DocumentoFuncionario, CorreoFuncionario)
                    VALUES
                        (:cargo, :nombre, :sede, :telefono, :documento, :correo)";

            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':cargo'     => $Cargo,
                ':nombre'    => $nombre,
                ':sede'      => $sede,
                ':telefono'  => $telefono,
                ':documento' => $documento,
                ':correo'    => $correo
            ]);

            if ($resultado) {
                return ['success' => true, 'id' => $this->conexion->lastInsertId()];
            }

            return ['success' => false, 'error' => 'No se pudo insertar'];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ══════════════════════════════════════════════════════
    // ACTUALIZAR QR
    // ══════════════════════════════════════════════════════
    public function ActualizarQrFuncionario(int $IdFuncionario, string $RutaQr): array
    {
        try {
            $sql  = "UPDATE funcionario
                     SET QrCodigoFuncionario = :qr
                     WHERE IdFuncionario = :id";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([':qr' => $RutaQr, ':id' => $IdFuncionario]);
            return ['success' => $resultado];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ══════════════════════════════════════════════════════
    // ACTUALIZAR FOTO FUNCIONARIO
    // ══════════════════════════════════════════════════════
    public function actualizarFotoFuncionario(int $idFuncionario, string $rutaFoto): bool
    {
        try {
            $sql  = "UPDATE funcionario
                     SET FotoFuncionario = :foto
                     WHERE IdFuncionario = :id";
            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([':foto' => $rutaFoto, ':id' => $idFuncionario]);

        } catch (PDOException $e) {
            return false;
        }
    }

    // ══════════════════════════════════════════════════════
    // OBTENER QR ACTUAL
    // ══════════════════════════════════════════════════════
    public function obtenerQrActual(int $idFuncionario): ?string
    {
        $sql  = "SELECT QrCodigoFuncionario FROM funcionario WHERE IdFuncionario = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id' => $idFuncionario]);
        return $stmt->fetchColumn() ?: null;
    }

    // ══════════════════════════════════════════════════════
    // OBTENER POR ID
    // ══════════════════════════════════════════════════════
    public function obtenerPorId(int $idFuncionario): ?array
    {
        $sql  = "SELECT * FROM funcionario WHERE IdFuncionario = :id LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id' => $idFuncionario]);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ══════════════════════════════════════════════════════
    // CAMBIAR ESTADO FUNCIONARIO
    // ══════════════════════════════════════════════════════
    public function cambiarEstado(int $idFuncionario, string $estado): bool
    {
        $sql  = "UPDATE funcionario SET Estado = :estado WHERE IdFuncionario = :id";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([':estado' => $estado, ':id' => $idFuncionario]);
    }

    // ══════════════════════════════════════════════════════
    // VALIDAR EXISTENCIA DE SEDE
    // ══════════════════════════════════════════════════════
    public function existeSede(int $idSede): bool
    {
        $sql  = "SELECT COUNT(*) FROM sede WHERE IdSede = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id' => $idSede]);
        return $stmt->fetchColumn() > 0;
    }

    // ══════════════════════════════════════════════════════
    // VALIDAR SEDE ACTIVA
    // ══════════════════════════════════════════════════════
    public function existeSedeActiva(int $idSede): bool
    {
        $sql  = "SELECT COUNT(*) FROM sede WHERE IdSede = :id AND Estado = 'Activo'";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id' => $idSede]);
        return $stmt->fetchColumn() > 0;
    }

    // ══════════════════════════════════════════════════════
    // VALIDAR DUPLICADOS AL REGISTRAR
    // ✅ Documento como string para comparar correctamente
    // ══════════════════════════════════════════════════════
    public function existeDuplicado(string $documento, string $correo): bool
    {
        $sql  = "SELECT COUNT(*) FROM funcionario
                 WHERE DocumentoFuncionario = :doc OR CorreoFuncionario = :correo";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':doc' => $documento, ':correo' => $correo]);
        return $stmt->fetchColumn() > 0;
    }

    // ══════════════════════════════════════════════════════
    // VALIDAR DUPLICADOS GENERAL (usado por el controlador)
    // ══════════════════════════════════════════════════════
    public function validarDuplicados($documento, $correo, $idExcluir = null)
    {
        $sql = "SELECT IdFuncionario FROM funcionario
                WHERE (DocumentoFuncionario = :documento OR CorreoFuncionario = :correo)";

        if ($idExcluir !== null) {
            $sql .= " AND IdFuncionario != :id";
        }

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':documento', $documento);
        $stmt->bindParam(':correo',    $correo);

        if ($idExcluir !== null) {
            $stmt->bindParam(':id', $idExcluir, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }

    // ══════════════════════════════════════════════════════
    // ACTUALIZAR FUNCIONARIO
    // ══════════════════════════════════════════════════════
    public function actualizar(int $idFuncionario, array $datos): array
    {
        try {
            if ($this->existeDuplicadoActualizar(
                $datos['DocumentoFuncionario'],
                $datos['CorreoFuncionario'],
                $idFuncionario
            )) {
                return ['success' => false, 'error' => 'Documento o correo duplicado'];
            }

            if (!$this->existeSedeActiva((int)$datos['IdSede'])) {
                return ['success' => false, 'error' => 'La sede está inactiva'];
            }

            $sql = "UPDATE funcionario SET
                        CargoFuncionario     = :cargo,
                        NombreFuncionario    = :nombre,
                        IdSede               = :sede,
                        TelefonoFuncionario  = :telefono,
                        DocumentoFuncionario = :documento,
                        CorreoFuncionario    = :correo
                    WHERE IdFuncionario = :id";

            $stmt      = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                ':cargo'     => $datos['CargoFuncionario'],
                ':nombre'    => $datos['NombreFuncionario'],
                ':sede'      => (int)$datos['IdSede'],
                ':telefono'  => $datos['TelefonoFuncionario'],
                ':documento' => $datos['DocumentoFuncionario'],
                ':correo'    => $datos['CorreoFuncionario'],
                ':id'        => $idFuncionario
            ]);

            return ['success' => $resultado];

        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ══════════════════════════════════════════════════════
    // VALIDAR DUPLICADOS AL ACTUALIZAR
    // ══════════════════════════════════════════════════════
    public function existeDuplicadoActualizar($doc, $correo, $idFuncionario): bool
    {
        $sql  = "SELECT COUNT(*) FROM funcionario
                 WHERE (DocumentoFuncionario = :doc OR CorreoFuncionario = :correo)
                 AND IdFuncionario != :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':doc' => $doc, ':correo' => $correo, ':id' => $idFuncionario]);
        return $stmt->fetchColumn() > 0;
    }
}
?>