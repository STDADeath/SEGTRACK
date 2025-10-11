<?php
class Conexion {
    private $host = "localhost";
    private $usuario = "root";
    private $clave = "";
    private $db = "seggtack";
    private $conexion;

    public function __construct() {
        try {
            $this->conexion = new PDO(
                "mysql:host={$this->host};dbname={$this->db};charset=utf8",
                $this->usuario,
                $this->clave
            );
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("❌ Error en la conexión: " . $e->getMessage());
        }
    }

    public function getConexion() {
        return $this->conexion;
    }
}

// ESTAS DOS LÍNEAS SON CRÍTICAS
$conexionObj = new Conexion();
$conexion = $conexionObj->getConexion();
?>