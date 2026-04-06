<?php
// ======================================================================
// MODELO: ModeloSede.php
// ======================================================================

require_once __DIR__ . "/../Core/Conexion.php";

class ModeloSede
{
    private $conexion;

    public function __construct()
    {
        $conexionObj    = new Conexion();
        $this->conexion = $conexionObj->getConexion();
    }

    // ══════════════════════════════════════════════════════
    // REGISTRAR SEDE
    // ══════════════════════════════════════════════════════
    public function registrarSede($tipoSede, $ciudad, $idInstitucion)
    {
        try {
            $sql = "INSERT INTO sede (TipoSede, Ciudad, IdInstitucion, Estado)
                    VALUES (:tipo, :ciudad, :institucion, 'Activo')";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':tipo',        $tipoSede,      PDO::PARAM_STR);
            $stmt->bindParam(':ciudad',      $ciudad,        PDO::PARAM_STR);
            $stmt->bindParam(':institucion', $idInstitucion, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            error_log("Error PDO (registrar sede): " . $e->getMessage());
            return false;
        }
    }

    // ══════════════════════════════════════════════════════
    // VERIFICAR SI YA EXISTE UNA SEDE
    // ══════════════════════════════════════════════════════
    public function existeSede($tipoSede, $ciudad)
    {
        try {
            $sql = "SELECT IdSede FROM sede
                    WHERE TipoSede = :tipo AND Ciudad = :ciudad
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':tipo' => $tipoSede, ':ciudad' => $ciudad]);
            return $stmt->fetch() !== false;

        } catch (PDOException $e) {
            error_log("Error PDO (existeSede): " . $e->getMessage());
            return false;
        }
    }

    // ══════════════════════════════════════════════════════
    // EDITAR SEDE
    // ══════════════════════════════════════════════════════
    public function editarSede($idSede, $tipoSede, $ciudad, $idInstitucion)
    {
        try {
            $sql = "UPDATE sede
                    SET TipoSede      = :tipo,
                        Ciudad        = :ciudad,
                        IdInstitucion = :institucion
                    WHERE IdSede = :id";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':tipo',        $tipoSede,      PDO::PARAM_STR);
            $stmt->bindParam(':ciudad',      $ciudad,        PDO::PARAM_STR);
            $stmt->bindParam(':institucion', $idInstitucion, PDO::PARAM_INT);
            $stmt->bindParam(':id',          $idSede,        PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            error_log("Error PDO (editar sede): " . $e->getMessage());
            return false;
        }
    }

    // ══════════════════════════════════════════════════════
    // OBTENER SEDE POR ID
    // ══════════════════════════════════════════════════════
    public function obtenerSedePorId($idSede)
    {
        try {
            $sql = "SELECT IdSede, TipoSede, Ciudad, IdInstitucion, Estado
                    FROM sede WHERE IdSede = :id LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $idSede, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (PDOException $e) {
            error_log("Error (obtener sede por ID): " . $e->getMessage());
            return null;
        }
    }

    // ══════════════════════════════════════════════════════
    // OBTENER ESTADO ACTUAL
    // ══════════════════════════════════════════════════════
    public function obtenerEstado($idSede)
    {
        try {
            $sql  = "SELECT Estado FROM sede WHERE IdSede = :id LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $idSede, PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila ? $fila['Estado'] : null;

        } catch (PDOException $e) {
            error_log("Error (obtenerEstado sede): " . $e->getMessage());
            return null;
        }
    }

    // ══════════════════════════════════════════════════════
    // ACTUALIZAR ESTADO
    // ══════════════════════════════════════════════════════
    public function actualizarEstado($idSede, $nuevoEstado)
    {
        try {
            $sql  = "UPDATE sede SET Estado = :estado WHERE IdSede = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':estado' => $nuevoEstado, ':id' => $idSede]);
            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            error_log("Error (actualizarEstado sede): " . $e->getMessage());
            return false;
        }
    }

    // ══════════════════════════════════════════════════════
    // CAMBIAR ESTADO (toggle Activo/Inactivo)
    // ══════════════════════════════════════════════════════
    public function cambiarEstado($idSede)
    {
        try {
            $estadoActual = $this->obtenerEstado($idSede);
            if ($estadoActual === null) {
                return ['success' => false, 'message' => 'Sede no encontrada'];
            }
            $nuevoEstado = ($estadoActual === 'Activo') ? 'Inactivo' : 'Activo';
            $actualizado = $this->actualizarEstado($idSede, $nuevoEstado);
            return [
                'success' => $actualizado,
                'message' => $actualizado ? 'Estado actualizado correctamente' : 'Error al actualizar estado',
                'nuevo_estado' => $nuevoEstado
            ];
        } catch (PDOException $e) {
            error_log("Error en cambiarEstado: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error en la base de datos'];
        }
    }

    // ══════════════════════════════════════════════════════
    // OBTENER INSTITUCIONES (para formularios)
    // ══════════════════════════════════════════════════════
    public function obtenerInstituciones()
    {
        try {
            $sql = "SELECT IdInstitucion, NombreInstitucion
                    FROM institucion
                    ORDER BY NombreInstitucion ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error (obtener instituciones): " . $e->getMessage());
            return [];
        }
    }

    // ══════════════════════════════════════════════════════
    // OBTENER TODAS LAS SEDES sin filtros (para selects y mapa JS)
    // ══════════════════════════════════════════════════════
    public function obtenerSedes(): array
    {
        try {
            $sql = "SELECT
                        s.IdSede,
                        s.TipoSede                AS NombreSede,
                        s.TipoSede,
                        s.Ciudad,
                        s.Estado,
                        CAST(s.IdInstitucion AS CHAR) AS IdInstitucion,
                        i.NombreInstitucion
                    FROM sede s
                    INNER JOIN institucion i ON i.IdInstitucion = s.IdInstitucion
                    ORDER BY i.NombreInstitucion ASC, s.TipoSede ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error (obtenerSedes): " . $e->getMessage());
            return [];
        }
    }

    // ══════════════════════════════════════════════════════
    // OBTENER SEDES FILTRADAS (para la tabla de SedeLista.php)
    // ══════════════════════════════════════════════════════
    public function obtenerSedesFiltradas(
        string $tipo        = '',
        string $ciudad      = '',
        string $estado      = '',
        string $institutoId = '',
        string $sedeId      = ''
    ): array {
        try {
            $filtros = ["1 = 1"];
            $params  = [];

            if ($tipo !== '') {
                $filtros[] = "s.TipoSede = :tipo";
                $params[':tipo'] = $tipo;
            }
            if ($ciudad !== '') {
                $filtros[] = "s.Ciudad LIKE :ciudad";
                $params[':ciudad'] = '%' . $ciudad . '%';
            }
            if ($estado !== '') {
                $filtros[] = "s.Estado = :estado";
                $params[':estado'] = $estado;
            }
            if ($institutoId !== '') {
                $filtros[] = "s.IdInstitucion = :instituto";
                $params[':instituto'] = $institutoId;
            }
            if ($sedeId !== '') {
                $filtros[] = "s.IdSede = :sede";
                $params[':sede'] = $sedeId;
            }

            $where = "WHERE " . implode(" AND ", $filtros);

            $sql = "SELECT
                        s.IdSede,
                        s.TipoSede,
                        s.TipoSede AS NombreSede,
                        s.Ciudad,
                        s.Estado,
                        s.IdInstitucion,
                        i.NombreInstitucion
                    FROM sede s
                    JOIN institucion i ON s.IdInstitucion = i.IdInstitucion
                    $where
                    ORDER BY i.NombreInstitucion ASC, s.TipoSede ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error (obtenerSedesFiltradas): " . $e->getMessage());
            return [];
        }
    }

    // ══════════════════════════════════════════════════════
    // OBTENER SEDES ACTIVAS (para selects en otros formularios)
    // ══════════════════════════════════════════════════════
    public function obtenerSedesActivas(): array
    {
        try {
            $sql = "SELECT
                        IdSede,
                        TipoSede AS NombreSede,
                        IdInstitucion
                    FROM sede
                    WHERE Estado = 'Activo'
                    ORDER BY TipoSede ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error (obtener sedes activas): " . $e->getMessage());
            return [];
        }
    }
}
?>