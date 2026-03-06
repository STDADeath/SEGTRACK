<?php
// ==========================================================
// MODELO: modeloinstituto.php
// Capa Modelo (MVC): toda interacción con la base de datos.
// ==========================================================

require_once __DIR__ . '/../Core/conexion.php';

class ModeloInstituto
{
    private $conexion;

    // Establece la conexión PDO al instanciar el modelo
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
    // Recibe array con todos los campos del formulario.
    // DireccionInstitucion es obligatoria en BD (No nulo),
    // si viene vacía se guarda string vacío '' en lugar de NULL.
    // Detecta NIT duplicado con código PDO 23000.
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
            // ✅ FIX: como la BD es No nulo, nunca enviamos NULL
            // Si viene vacío o null desde el controlador, guardamos string vacío ''
            $direccion = $datos['DireccionInstitucion'] ?? '';
            $stmt->bindParam(':direccion', $direccion, PDO::PARAM_STR);

            $stmt->execute();

            return [
                'ok'      => true,
                'message' => 'Institución "' . $datos['NombreInstitucion'] . '" registrada con éxito.'
            ];

        } catch (PDOException $e) {
            // Código 23000 = violación UNIQUE (NIT duplicado)
            if ($e->getCode() == 23000) {
                return [
                    'ok'      => false,
                    'message' => 'El NIT/Código "' . $datos['Nit_Codigo'] . '" ya está registrado. Verifica el número.'
                ];
            }
            return ['ok' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }


    // ══════════════════════════════════════════════════════
    // LISTAR todas las instituciones
    // Retorna todos los campos incluyendo DireccionInstitucion
    // para que la tabla de la vista lo muestre correctamente.
    // ══════════════════════════════════════════════════════
    public function listarInstitutos()
    {
        try {
            // Se incluye DireccionInstitucion en el SELECT explícitamente
            $sql = "SELECT 
                        IdInstitucion,
                        EstadoInstitucion,
                        NombreInstitucion,
                        Nit_Codigo,
                        TipoInstitucion,
                        DireccionInstitucion
                    FROM institucion
                    ORDER BY IdInstitucion DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            throw new Exception("Error al listar instituciones: " . $e->getMessage());
        }
    }


    // ══════════════════════════════════════════════════════
    // OBTENER una institución por ID
    // Retorna todos los campos del registro o false si no existe.
    // Usado para precargar datos en formulario/modal de edición.
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
    // EDITAR institución existente
    // Actualiza todos los campos incluida DireccionInstitucion.
    // Este era el método que no guardaba la dirección porque
    // le faltaba el campo en el UPDATE y manejaba mal el NULL.
    // ══════════════════════════════════════════════════════
    public function editarInstituto(array $datos)
    {
        try {
            // DireccionInstitucion incluida en el SET del UPDATE
            $sql = "UPDATE institucion SET 
                        NombreInstitucion    = :nombre,
                        Nit_Codigo           = :nit,
                        TipoInstitucion      = :tipo,
                        EstadoInstitucion    = :estado,
                        DireccionInstitucion = :direccion
                    WHERE IdInstitucion = :id";

            $stmt = $this->conexion->prepare($sql);

            $stmt->bindParam(':id',     $datos['IdInstitucion'],     PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $datos['NombreInstitucion'], PDO::PARAM_STR);
            $stmt->bindParam(':nit',    $datos['Nit_Codigo'],        PDO::PARAM_STR);
            $stmt->bindParam(':tipo',   $datos['TipoInstitucion'],   PDO::PARAM_STR);
            $stmt->bindParam(':estado', $datos['EstadoInstitucion'], PDO::PARAM_STR);
            // ✅ FIX: BD es No nulo → nunca enviamos NULL, usamos string vacío ''
            $direccion = $datos['DireccionInstitucion'] ?? '';
            $stmt->bindParam(':direccion', $direccion, PDO::PARAM_STR);

            $stmt->execute();

            return [
                'ok'      => true,
                'message' => 'Institución actualizada correctamente.'
            ];

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return [
                    'ok'      => false,
                    'message' => 'El NIT/Código ya está registrado en otra institución.'
                ];
            }
            return [
                'ok'      => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }


    // ══════════════════════════════════════════════════════
    // CAMBIAR ESTADO (toggle desde el candado de la lista)
    // Solo actualiza EstadoInstitucion, no toca ningún otro campo.
    // ══════════════════════════════════════════════════════
    public function cambiarEstado($id, $nuevoEstado)
    {
        try {
            $sql  = "UPDATE institucion SET EstadoInstitucion = :estado WHERE IdInstitucion = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id',     $id,          PDO::PARAM_INT);
            $stmt->bindParam(':estado', $nuevoEstado, PDO::PARAM_STR);
            $stmt->execute();

            return [
                'ok'      => true,
                'message' => 'Estado cambiado a "' . $nuevoEstado . '" correctamente.'
            ];

        } catch (PDOException $e) {
            return [
                'ok'      => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ];
        }
    }
}
?>