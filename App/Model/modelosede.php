<?php
// App/Model/ModeloSede.php

require_once __DIR__ . "/../Core/Conexion.php";

class ModeloSede {

    private $conexion;

    public function __construct() {
        $conexionObj = new Conexion();
        $this->conexion = $conexionObj->getConexion();
    }

    // ======================================================
    // 🔹 REGISTRAR SEDE 
    // ======================================================
    public function registrarSede($tipoSede, $ciudad, $idInstitucion) {
        try {
            $sql = "INSERT INTO sede (TipoSede, Ciudad, IdInstitucion)
                     VALUES (:tipo, :ciudad, :institucion)";
            $stmt = $this->conexion->prepare($sql);

            $stmt->bindParam(":tipo", $tipoSede);
            $stmt->bindParam(":ciudad", $ciudad);
            $stmt->bindParam(":institucion", $idInstitucion, PDO::PARAM_INT); 

            $stmt->execute();
            
            return ['success' => true]; 

        } catch (PDOException $e) {
            // Código 23000: Violación de restricción de integridad (ej: Llave Foránea no existe)
            if ($e->getCode() == 23000) {
                return ['success' => false, 'message' => 'Error de integridad: La Institución seleccionada no existe.'];
            }
            error_log("Error PDO al registrar sede: " . $e->getMessage()); 
            return ['success' => false, 'message' => 'Error de base de datos inesperado al registrar la sede.'];
        }
    }

    // ======================================================
    // 🔹 EDITAR SEDE
    // ======================================================
    public function editarSede($idSede, $tipoSede, $ciudad, $idInstitucion) {
        try {
            $sql = "UPDATE sede 
                    SET TipoSede = :tipo, 
                        Ciudad = :ciudad, 
                        IdInstitucion = :institucion
                    WHERE IdSede = :id";
            
            $stmt = $this->conexion->prepare($sql);

            $stmt->bindParam(":tipo", $tipoSede);
            $stmt->bindParam(":ciudad", $ciudad);
            $stmt->bindParam(":institucion", $idInstitucion, PDO::PARAM_INT);
            $stmt->bindParam(":id", $idSede, PDO::PARAM_INT);

            $stmt->execute();
            
            return ['success' => true];

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['success' => false, 'message' => 'Error de integridad: La Institución seleccionada no existe.'];
            }
            error_log("Error PDO al editar sede: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos inesperado al editar la sede.'];
        }
    }

    // ======================================================
    // 🔹 OBTENER TODAS LAS INSTITUCIONES 
    // ======================================================
    public function obtenerInstituciones() {
        try {
            $sql = "SELECT IdInstitucion, NombreInstitucion FROM institucion";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error al obtener instituciones: " . $e->getMessage());
            return [];
        }
    }

    // ======================================================
    // 🔹 OBTENER TODAS LAS SEDES
    // ======================================================
    public function obtenerSedes() {
        try {
            $query = "SELECT IdSede, TipoSede as NombreSede, Ciudad 
                      FROM sede 
                      ORDER BY TipoSede ASC";
            
            $stmt = $this->conexion->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al obtener sedes: " . $e->getMessage());
            return [];
        }
    }
}