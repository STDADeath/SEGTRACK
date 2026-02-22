<?php
// ======================================================================
// MODELO: ModeloSede
// Gestión completa de sedes (CRUD + cambio de estado)
// ======================================================================

require_once __DIR__ . "/../Core/Conexion.php";

class ModeloSede {

    private $conexion;

    // ======================================================
    // CONSTRUCTOR
    // ======================================================
    public function __construct() {
        $conexionObj = new Conexion();
        $this->conexion = $conexionObj->getConexion();
    }

    // ======================================================
    // REGISTRAR SEDE
    // ======================================================
    public function registrarSede($tipoSede, $ciudad, $idInstitucion) {
        try {

            // Verificar si ya existe una sede con ese tipo en esa ciudad
            $checkSql = "SELECT IdSede 
                         FROM sede 
                         WHERE TipoSede = :tipo 
                           AND Ciudad = :ciudad 
                         LIMIT 1";

            $check = $this->conexion->prepare($checkSql);
            $check->execute([
                ':tipo'   => $tipoSede,
                ':ciudad' => $ciudad
            ]);

            if ($check->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Ya existe una sede con ese nombre en esa ciudad.'
                ];
            }

            $sql = "INSERT INTO sede (TipoSede, Ciudad, IdInstitucion, Estado)
                    VALUES (:tipo, :ciudad, :institucion, 'Activo')";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':tipo',        $tipoSede);
            $stmt->bindParam(':ciudad',      $ciudad);
            $stmt->bindParam(':institucion', $idInstitucion, PDO::PARAM_INT);
            $stmt->execute();

            return ['success' => true, 'message' => 'Sede registrada exitosamente.'];

        } catch (PDOException $e) {

            if ($e->getCode() == 23000) {
                return ['success' => false, 'message' => 'La institución seleccionada no existe.'];
            }

            error_log("Error PDO (registrar sede): " . $e->getMessage());
            return ['success' => false, 'message' => 'Error inesperado al registrar.'];
        }
    }

    // ======================================================
    // EDITAR SEDE
    // CORRECCIÓN: se devuelve success:true siempre que el
    // UPDATE se ejecute sin excepción, sin importar rowCount,
    // ya que puede que el usuario guarde los mismos datos.
    // ======================================================
    public function editarSede($idSede, $tipoSede, $ciudad, $idInstitucion) {
        try {

            $sql = "UPDATE sede 
                    SET TipoSede      = :tipo,
                        Ciudad        = :ciudad,
                        IdInstitucion = :institucion
                    WHERE IdSede = :id";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':tipo',        $tipoSede);
            $stmt->bindParam(':ciudad',      $ciudad);
            $stmt->bindParam(':institucion', $idInstitucion, PDO::PARAM_INT);
            $stmt->bindParam(':id',          $idSede,        PDO::PARAM_INT);
            $stmt->execute();

            // ✅ CORRECCIÓN CLAVE: devolver success:true siempre que no
            // haya excepción, incluso si rowCount() === 0 (mismos datos).
            return ['success' => true, 'message' => 'Sede actualizada exitosamente.'];

        } catch (PDOException $e) {
            error_log("Error PDO (editar sede): " . $e->getMessage());
            return ['success' => false, 'message' => 'Error inesperado al editar la sede.'];
        }
    }

    // ======================================================
    // OBTENER SEDE POR ID
    // ======================================================
    public function obtenerSedePorId($idSede) {
        try {

            $sql = "SELECT IdSede, TipoSede, Ciudad, IdInstitucion, Estado
                    FROM sede
                    WHERE IdSede = :id
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $idSede, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error (obtener sede por ID): " . $e->getMessage());
            return null;
        }
    }

    // ======================================================
    // CAMBIAR ESTADO (Activo / Inactivo)
    // ======================================================
    public function cambiarEstado($idSede) {
        try {

            $sqlSelect = "SELECT Estado FROM sede WHERE IdSede = :id LIMIT 1";
            $stmt = $this->conexion->prepare($sqlSelect);
            $stmt->bindParam(':id', $idSede, PDO::PARAM_INT);
            $stmt->execute();

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                return ['success' => false, 'message' => 'Sede no encontrada.'];
            }

            $nuevoEstado = ($data['Estado'] === 'Activo') ? 'Inactivo' : 'Activo';

            $sqlUpdate = "UPDATE sede SET Estado = :estado WHERE IdSede = :id";
            $update = $this->conexion->prepare($sqlUpdate);
            $exito = $update->execute([
                ':estado' => $nuevoEstado,
                ':id'     => $idSede
            ]);

            if ($exito) {
                return ['success' => true, 'message' => 'Estado cambiado a ' . $nuevoEstado . '.'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar el estado.'];
            }

        } catch (PDOException $e) {
            error_log("Error (cambiar estado sede): " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de servidor al cambiar estado.'];
        }
    }

    // ======================================================
    // OBTENER INSTITUCIONES
    // ======================================================
    public function obtenerInstituciones() {
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

    // ======================================================
    // OBTENER TODAS LAS SEDES
    // ======================================================
    public function obtenerSedes() {
        try {

            $query = "SELECT 
                          s.IdSede,
                          s.TipoSede,
                          s.Ciudad,
                          s.Estado,
                          s.IdInstitucion,
                          i.NombreInstitucion
                      FROM sede s
                      INNER JOIN institucion i 
                             ON i.IdInstitucion = s.IdInstitucion
                      ORDER BY s.TipoSede ASC";

            $stmt = $this->conexion->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error (obtener sedes): " . $e->getMessage());
            return [];
        }
    }
}