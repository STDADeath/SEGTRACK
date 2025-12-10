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

            // Verificar si ya existe una sede con ese tipo en esa ciudad
            $checkSql = "SELECT IdSede FROM sede WHERE TipoSede = :tipo AND Ciudad = :ciudad LIMIT 1";
            $check = $this->conexion->prepare($checkSql);
            $check->execute([
                ":tipo" => $tipoSede,
                ":ciudad" => $ciudad
            ]);

            if ($check->fetch()) {
                return ['success' => false, 'message' => '❌ Ya existe una sede con ese nombre en esa ciudad.'];
            }

            $sql = "INSERT INTO sede (TipoSede, Ciudad, IdInstitucion, Estado)
                    VALUES (:tipo, :ciudad, :institucion, 'Activo')";

            $stmt = $this->conexion->prepare($sql);

            $stmt->bindParam(":tipo", $tipoSede);
            $stmt->bindParam(":ciudad", $ciudad);
            $stmt->bindParam(":institucion", $idInstitucion, PDO::PARAM_INT);

            $stmt->execute();
            return ['success' => true];

        } catch (PDOException $e) {

            if ($e->getCode() == 23000) {
                return ['success' => false, 'message' => 'La institución seleccionada no existe.'];
            }

            error_log("Error PDO al registrar sede: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error inesperado al registrar.'];
        }
    }

    // ======================================================
    // 🔹 EDITAR SEDE
    // ======================================================
    public function editarSede($idSede, $tipoSede, $ciudad, $idInstitucion) {
        try {

            $sql = "UPDATE sede SET 
                        TipoSede = :tipo,
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

            error_log("Error PDO al editar sede: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error inesperado al editar.'];
        }
    }

    // ======================================================
    // 🔹 OBTENER SEDE POR ID  (NECESARIO PARA EDITAR)
    // ======================================================
    public function obtenerSedePorId($idSede) {
        try {

            $sql = "SELECT IdSede, TipoSede, Ciudad, IdInstitucion, Estado
                    FROM sede 
                    WHERE IdSede = :id
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(":id", $idSede, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error al obtener sede: " . $e->getMessage());
            return null;
        }
    }

    // ======================================================
    // 🔹 CAMBIAR ESTADO (ACTIVO / INACTIVO)
    // ======================================================
    public function cambiarEstado($idSede) {
        try {

            // Primero obtener el estado actual
            $sqlSelect = "SELECT Estado FROM sede WHERE IdSede = :id LIMIT 1";
            $stmt = $this->conexion->prepare($sqlSelect);
            $stmt->bindParam(":id", $idSede, PDO::PARAM_INT);
            $stmt->execute();

            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$data) return false;

            $nuevoEstado = ($data["Estado"] === "Activo") ? "Inactivo" : "Activo";

            // Luego actualizarlo
            $sqlUpdate = "UPDATE sede SET Estado = :estado WHERE IdSede = :id";

            $update = $this->conexion->prepare($sqlUpdate);
            return $update->execute([
                ":estado" => $nuevoEstado,
                ":id" => $idSede
            ]);

        } catch (PDOException $e) {
            error_log("Error al cambiar estado de sede: " . $e->getMessage());
            return false;
        }
    }

    // ======================================================
    // 🔹 OBTENER INSTITUCIONES 
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
            error_log("Error al obtener instituciones: " . $e->getMessage());
            return [];
        }
    }

    // ======================================================
    // 🔹 OBTENER TODAS LAS SEDES (LISTA)
    // ======================================================
    public function obtenerSedes() {
        try {

            $query = "SELECT 
                        s.IdSede,
                        s.TipoSede AS NombreSede,
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
            error_log("Error al obtener sedes: " . $e->getMessage());
            return [];
        }
    }
}
