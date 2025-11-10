<?php
/**
 * Modelo_Sede
 * Maneja las operaciones CRUD sobre la tabla 'sede'.
 */
require_once __DIR__ . '/../../Core/conexion.php';

class Modelo_Sede {
    private $pdo;

    /**
     * Constructor: inicializa la conexión a la base de datos.
     */
    public function __construct() {
        $conexion = new Conexion();
        $this->pdo = $conexion->getConexion();
    }

    /**
     * Registra una nueva sede en la base de datos.
     *
     * @param string $tipo_sede       Tipo de sede (Principal, Secundaria, etc.)
     * @param string $ciudad          Ciudad donde está ubicada la sede
     * @param int    $id_institucion  ID de la institución asociada
     * @param string $estado_sede     Estado de la sede (Activa/Inactiva)
     * @return array ['error' => bool, 'mensaje' => string]
     */
    public function registrarSede($tipo_sede, $ciudad, $id_institucion, $estado_sede) {
        $sql = "INSERT INTO sede (TipoSede, Ciudad, IdInstitucion, EstadoSede)
                VALUES (:tipo_sede, :ciudad, :id_institucion, :estado_sede)";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':tipo_sede', $tipo_sede, PDO::PARAM_STR);
            $stmt->bindParam(':ciudad', $ciudad, PDO::PARAM_STR);
            $stmt->bindParam(':id_institucion', $id_institucion, PDO::PARAM_INT);
            $stmt->bindParam(':estado_sede', $estado_sede, PDO::PARAM_STR);

            $stmt->execute();

            return [
                'error' => false,
                'mensaje' => '✅ Sede registrada correctamente.'
            ];

        } catch (PDOException $e) {
            return [
                'error' => true,
                'mensaje' => '❌ Error al registrar la sede: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene todas las sedes con su institución asociada.
     */
    public function obtenerSedes() {
        $sql = "SELECT 
                    s.IdSede, 
                    s.TipoSede, 
                    s.Ciudad, 
                    s.EstadoSede, 
                    i.NombreInstitucion
                FROM sede s
                INNER JOIN institucion i ON s.IdInstitucion = i.IdInstitucion
                ORDER BY s.IdSede DESC";

        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [
                'error' => true,
                'mensaje' => '❌ Error al obtener las sedes: ' . $e->getMessage()
            ];
        }
    }
}
?>
