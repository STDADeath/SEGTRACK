<?php
class Conexion {
    private $host = "localhost";
    private $usuario = "root";
    private $clave = "";
    private $db = "seggtack";
    private $conexion;

    public function __construct() {
        $this->conexion = new mysqli($this->host, $this->usuario, $this->clave, $this->db);

        if ($this->conexion->connect_error) {
            die("❌ Error en la conexión: " . $this->conexion->connect_error);
        }
    }

    public function getConexion() {
        return $this->conexion; //
    }
}
?>
