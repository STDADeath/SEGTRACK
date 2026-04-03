<?php
// ======================================================================
// MODELO: ModeloSede
// Responsabilidad: SOLO consultas SQL preparadas, sin lógica de negocio.
// La validación de duplicados, respuestas JSON y decisiones
// son responsabilidad del ControladorSede.
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
    // Inserta una nueva sede. Retorna true si se insertó.
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
    // VERIFICAR SI YA EXISTE UNA SEDE CON ESE TIPO Y CIUDAD
    // Retorna true si ya existe (el controlador decide qué hacer).
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
    // Actualiza TipoSede, Ciudad e IdInstitucion.
    // Retorna true si se actualizó al menos una fila.
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
                    FROM sede
                    WHERE IdSede = :id
                    LIMIT 1";

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
    // OBTENER ESTADO ACTUAL DE UNA SEDE
    // Solo devuelve el campo Estado para que el controlador
    // calcule el nuevo estado y llame a actualizarEstado().
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
    // OBTENER TODAS LAS SEDES
    // ══════════════════════════════════════════════════════
    public function obtenerSedes($tipo = '', $ciudad = '', $estado = '')
    {
        try {
            $filtros = [];
            $params  = [];

            if ($tipo !== '') {
                $filtros[] = "s.TipoSede LIKE :tipo";
                $params[':tipo'] = '%' . $tipo . '%';
            }
            if ($ciudad !== '') {
                $filtros[] = "s.Ciudad LIKE :ciudad";
                $params[':ciudad'] = '%' . $ciudad . '%';
            }
            if ($estado !== '') {
                $filtros[] = "s.Estado = :estado";
                $params[':estado'] = $estado;
            }

            $where = $filtros ? "WHERE " . implode(" AND ", $filtros) : "";

            $sql = "SELECT
                        s.IdSede,
                        s.TipoSede,
                        s.Ciudad,
                        s.Estado,
                        s.IdInstitucion,
                        i.NombreInstitucion
                    FROM sede s
                    INNER JOIN institucion i ON i.IdInstitucion = s.IdInstitucion
                    $where
                    ORDER BY s.TipoSede ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error (obtener sedes): " . $e->getMessage());
            return [];
        }
    }

    // ══════════════════════════════════════════════════════
    // OBTENER SEDES ACTIVAS 
    // ══════════════════════════════════════════════════════
    public function obtenerSedesActivas()
    {
        try {
            $sql = "SELECT
                        IdSede,
                        TipoSede      AS NombreSede,
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