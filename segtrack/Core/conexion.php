<?php
class Conexion {
    private $host = "localhost";
    private $usuario = "root";
    private $clave = "";
    private $db = "seggtrack"; // ✅ CORREGIDO: seggtrack (no seggtack)
    private $conexion;

    public function __construct() {
        try {
            $this->conexion = new PDO(
                "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4",
                $this->usuario,
                $this->clave,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
        } catch (PDOException $e) {
            error_log("Error de conexión BD: " . $e->getMessage());
            throw new Exception("Error al conectar con la base de datos. Verifica las credenciales.");
        }
    }

    public function getConexion() {
        return $this->conexion;
    }
}

// Crear instancia global
$conexionObj = new Conexion();
$conexion = $conexionObj->getConexion();