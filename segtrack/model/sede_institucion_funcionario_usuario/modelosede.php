<?php
require_once __DIR__ . "/../../Core/Conexion.php";

class Modelo_Sede {

    private $conexion;

    public function __construct() {
        // Se obtiene la conexión PDO desde la clase Conexion
        $this->conexion = (new Conexion())->getConexion();
    }

    // ============================================================
    // ✔ 1. OBTENER INSTITUCIONES PARA EL SELECT
    // ============================================================
    public function obtenerInstituciones() {
        $sql = "SELECT IdInstitucion, NombreInstitucion 
                FROM institucion
                ORDER BY NombreInstitucion ASC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================================================
    // ✔ 2. REGISTRAR NUEVA SEDE
    // ============================================================
    public function insertarSede($tipoSede, $ciudad, $idInstitucion) {
        $sql = "INSERT INTO sede (TipoSede, Ciudad, IdInstitucion) 
                VALUES (:tipo, :ciudad, :idInst)";

        $stmt = $this->conexion->prepare($sql);

        // Bind de parámetros
        $stmt->bindParam(":tipo", $tipoSede);
        $stmt->bindParam(":ciudad", $ciudad);
        $stmt->bindParam(":idInst", $idInstitucion);

        // Retorna true en éxito o false en error (debido a la configuración de PDO)
        return $stmt->execute(); 
    }

    // ... (Otros métodos: obtenerTodasLasSedes, obtenerSedePorId, actualizarSede, eliminarSede)
}