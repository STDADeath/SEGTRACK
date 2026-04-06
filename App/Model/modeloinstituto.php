
<?php
// ======================================================================
// MODELO: modeloinstituto.php
// Responsabilidad: SOLO consultas SQL preparadas, sin lógica de negocio.
// ======================================================================

require_once __DIR__ . '/../Core/conexion.php';

class ModeloInstituto
{
    private $conexion;

    public function __construct()
    {
        try {
            $conexionObj    = new Conexion();
            $this->conexion = $conexionObj->getConexion();
        } catch (\PDOException $e) {
            throw new Exception("Fallo al inicializar la conexión del modelo.");
        }
    }

    // ══════════════════════════════════════════════════════
    // INSERTAR nueva institución
    // ══════════════════════════════════════════════════════
    public function insertarInstituto(array $datos)
    {
        try {
            $sql = "INSERT INTO institucion
                        (NombreInstitucion, Nit_Codigo, TipoInstitucion, EstadoInstitucion, DireccionInstitucion)
                    VALUES
                        (:nombre, :nit, :tipo, :estado, :direccion)";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':nombre',    $datos['NombreInstitucion'], PDO::PARAM_STR);
            $stmt->bindParam(':nit',       $datos['Nit_Codigo'],        PDO::PARAM_STR);
            $stmt->bindParam(':tipo',      $datos['TipoInstitucion'],   PDO::PARAM_STR);
            $stmt->bindParam(':estado',    $datos['EstadoInstitucion'], PDO::PARAM_STR);
            $direccion = $datos['DireccionInstitucion'] ?? '';
            $stmt->bindParam(':direccion', $direccion, PDO::PARAM_STR);
            $stmt->execute();

            return [
                'ok'      => true,
                'message' => 'Institución "' . $datos['NombreInstitucion'] . '" registrada con éxito.'
            ];

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return [
                    'ok'      => false,
                    'message' => 'El NIT/Código "' . $datos['Nit_Codigo'] . '" ya está registrado.'
                ];
            }
            return ['ok' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    // ══════════════════════════════════════════════════════
    // LISTAR TODAS sin filtros
    // Usada para poblar los selects y obtener valores únicos
    // ══════════════════════════════════════════════════════
    public function listarInstitutos(): array
    {
        try {
            $sql = "SELECT
                        IdInstitucion,
                        EstadoInstitucion,
                        NombreInstitucion,
                        Nit_Codigo,
                        TipoInstitucion,
                        DireccionInstitucion
                    FROM institucion
                    ORDER BY NombreInstitucion ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            throw new Exception("Error al listar instituciones: " . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════
    // LISTAR FILTRADAS — para la tabla de InstitutoLista.php
    // Todos los filtros son opcionales y se aplican en SQL
    // con parámetros preparados (seguro contra inyección).
    // ══════════════════════════════════════════════════════
    public function listarInstitutosFiltrados(
        string $nombre    = '',
        string $tipo      = '',
        string $estado    = '',
        string $direccion = ''
    ): array {
        try {
            $filtros = ["1 = 1"];
            $params  = [];

            if ($nombre !== '') {
                $filtros[] = "NombreInstitucion LIKE :nombre";
                $params[':nombre'] = '%' . $nombre . '%';
            }
            if ($tipo !== '') {
                $filtros[] = "TipoInstitucion = :tipo";
                $params[':tipo'] = $tipo;
            }
            if ($estado !== '') {
                $filtros[] = "EstadoInstitucion = :estado";
                $params[':estado'] = $estado;
            }
            if ($direccion !== '') {
                $filtros[] = "DireccionInstitucion LIKE :direccion";
                $params[':direccion'] = '%' . $direccion . '%';
            }

            $where = "WHERE " . implode(" AND ", $filtros);

            $sql = "SELECT
                        IdInstitucion,
                        EstadoInstitucion,
                        NombreInstitucion,
                        Nit_Codigo,
                        TipoInstitucion,
                        DireccionInstitucion
                    FROM institucion
                    $where
                    ORDER BY NombreInstitucion ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error (listarInstitutosFiltrados): " . $e->getMessage());
            return [];
        }
    }

    // ══════════════════════════════════════════════════════
    // OBTENER UNA por ID
    // ══════════════════════════════════════════════════════
    public function obtenerInstitutoPorId($id)
    {
        try {
            $sql  = "SELECT * FROM institucion WHERE IdInstitucion = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            throw new Exception("Error al obtener institución: " . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════
    // EDITAR institución
    // ══════════════════════════════════════════════════════
    public function editarInstituto(array $datos)
    {
        try {
            $sql = "UPDATE institucion SET
                        NombreInstitucion    = :nombre,
                        Nit_Codigo           = :nit,
                        TipoInstitucion      = :tipo,
                        EstadoInstitucion    = :estado,
                        DireccionInstitucion = :direccion
                    WHERE IdInstitucion = :id";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id',        $datos['IdInstitucion'],     PDO::PARAM_INT);
            $stmt->bindParam(':nombre',    $datos['NombreInstitucion'], PDO::PARAM_STR);
            $stmt->bindParam(':nit',       $datos['Nit_Codigo'],        PDO::PARAM_STR);
            $stmt->bindParam(':tipo',      $datos['TipoInstitucion'],   PDO::PARAM_STR);
            $stmt->bindParam(':estado',    $datos['EstadoInstitucion'], PDO::PARAM_STR);
            $direccion = $datos['DireccionInstitucion'] ?? '';
            $stmt->bindParam(':direccion', $direccion, PDO::PARAM_STR);
            $stmt->execute();

            return ['ok' => true, 'message' => 'Institución actualizada correctamente.'];

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['ok' => false, 'message' => 'El NIT/Código ya está registrado en otra institución.'];
            }
            return ['ok' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    // ══════════════════════════════════════════════════════
    // CAMBIAR ESTADO
    // ══════════════════════════════════════════════════════
    public function cambiarEstado($id, $nuevoEstado)
    {
        try {
            $sql  = "UPDATE institucion SET EstadoInstitucion = :estado WHERE IdInstitucion = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id',     $id,          PDO::PARAM_INT);
            $stmt->bindParam(':estado', $nuevoEstado, PDO::PARAM_STR);
            $stmt->execute();

            return ['ok' => true, 'message' => 'Estado cambiado a "' . $nuevoEstado . '" correctamente.'];

        } catch (PDOException $e) {
            return ['ok' => false, 'message' => 'Error al cambiar estado: ' . $e->getMessage()];
        }
    }
}
?>