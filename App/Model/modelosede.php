<?php
// ======================================================================
// MODELO: ModeloSede
// Capa Modelo (MVC): toda interacción con la base de datos.
// El controlador llama estos métodos y recibe arrays con resultado.
// ======================================================================

require_once __DIR__ . "/../Core/Conexion.php";

class ModeloSede
{
    private $conexion; // Objeto PDO reutilizado en todos los métodos

    // ══════════════════════════════════════════════════════
    // CONSTRUCTOR
    // Instancia Conexion y obtiene el objeto PDO.
    // Mismo patrón que ModeloInstituto: new Conexion() → getConexion()
    // ══════════════════════════════════════════════════════
    public function __construct()
    {
        $conexionObj    = new Conexion();
        $this->conexion = $conexionObj->getConexion();
    }


    // ══════════════════════════════════════════════════════
    // REGISTRAR SEDE
    // Verifica duplicado (mismo tipo y ciudad) antes de insertar.
    // Estado siempre 'Activo' al registrar — forzado en el INSERT.
    // Detecta FK inválida con código PDO 23000.
    // ══════════════════════════════════════════════════════
    public function registrarSede($tipoSede, $ciudad, $idInstitucion)
    {
        try {
            // Verifica si ya existe una sede con ese tipo en esa ciudad
            $checkSql = "SELECT IdSede FROM sede
                         WHERE TipoSede = :tipo AND Ciudad = :ciudad
                         LIMIT 1";
            $check = $this->conexion->prepare($checkSql);
            $check->execute([':tipo' => $tipoSede, ':ciudad' => $ciudad]);

            if ($check->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Ya existe una sede con ese nombre en esa ciudad.'
                ];
            }

            // INSERT con Estado siempre 'Activo' — no viene del usuario
            $sql = "INSERT INTO sede (TipoSede, Ciudad, IdInstitucion, Estado)
                    VALUES (:tipo, :ciudad, :institucion, 'Activo')";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':tipo',        $tipoSede,      PDO::PARAM_STR);
            $stmt->bindParam(':ciudad',      $ciudad,        PDO::PARAM_STR);
            $stmt->bindParam(':institucion', $idInstitucion, PDO::PARAM_INT);
            $stmt->execute();

            return ['success' => true, 'message' => 'Sede registrada exitosamente.'];

        } catch (PDOException $e) {
            // Código 23000 = FK inválida (institución no existe)
            if ($e->getCode() == 23000) {
                return ['success' => false,
                        'message' => 'La institución seleccionada no existe.'];
            }
            error_log("Error PDO (registrar sede): " . $e->getMessage());
            return ['success' => false, 'message' => 'Error inesperado al registrar.'];
        }
    }


    // ══════════════════════════════════════════════════════
    // EDITAR SEDE
    // Actualiza TipoSede, Ciudad e IdInstitucion.
    // Retorna success:true aunque rowCount() sea 0 (mismos datos)
    // para no confundir al usuario con un falso error.
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

            // success:true siempre que no haya excepción
            // rowCount() = 0 no es error: el usuario pudo guardar los mismos datos
            return ['success' => true, 'message' => 'Sede actualizada exitosamente.'];

        } catch (PDOException $e) {
            error_log("Error PDO (editar sede): " . $e->getMessage());
            return ['success' => false,
                    'message' => 'Error inesperado al editar la sede.'];
        }
    }


    // ══════════════════════════════════════════════════════
    // OBTENER SEDE POR ID
    // Retorna array asociativo con los datos del registro
    // o null si no existe ese ID.
    // Usado por el controlador para precargar el modal.
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
            return $stmt->fetch(PDO::FETCH_ASSOC); // false si no existe

        } catch (PDOException $e) {
            error_log("Error (obtener sede por ID): " . $e->getMessage());
            return null;
        }
    }


    // ══════════════════════════════════════════════════════
    // CAMBIAR ESTADO (toggle Activo / Inactivo)
    // Primero consulta el estado actual, luego lo invierte.
    // Solo toca el campo Estado, no modifica ningún otro dato.
    // Se activa desde el clic en el candado de la lista.
    // ══════════════════════════════════════════════════════
    public function cambiarEstado($idSede)
    {
        try {
            // Consulta el estado actual del registro
            $sqlSelect = "SELECT Estado FROM sede WHERE IdSede = :id LIMIT 1";
            $stmt = $this->conexion->prepare($sqlSelect);
            $stmt->bindParam(':id', $idSede, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                return ['success' => false, 'message' => 'Sede no encontrada.'];
            }

            // Invierte el estado actual
            $nuevoEstado = ($data['Estado'] === 'Activo') ? 'Inactivo' : 'Activo';

            $sqlUpdate = "UPDATE sede SET Estado = :estado WHERE IdSede = :id";
            $update = $this->conexion->prepare($sqlUpdate);
            $exito = $update->execute([
                ':estado' => $nuevoEstado,
                ':id'     => $idSede
            ]);

            if ($exito) {
                return ['success' => true,
                        'message' => 'Estado cambiado a ' . $nuevoEstado . '.'];
            }
            return ['success' => false, 'message' => 'Error al actualizar el estado.'];

        } catch (PDOException $e) {
            error_log("Error (cambiar estado sede): " . $e->getMessage());
            return ['success' => false,
                    'message' => 'Error de servidor al cambiar estado.'];
        }
    }


    // ══════════════════════════════════════════════════════
    // OBTENER INSTITUCIONES
    // Retorna todas las instituciones para llenar el select
    // del formulario de registro y el modal de edición.
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
    // Hace JOIN con institución para mostrar el nombre
    // en lugar del ID en la tabla de la vista lista.
    // ══════════════════════════════════════════════════════
    public function obtenerSedes()
    {
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
?>