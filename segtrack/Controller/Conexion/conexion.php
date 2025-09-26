<?php
/*class Conexion {
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
}*/
public function __construct() {
        try {
            // Crear la conexión con PDO
            $this->conexion = new PDO(
                "mysql:host={$this->host};dbname={$this->db};charset=utf8", // cadena de conexión
                $this->usuario,   // usuario de la BD
                $this->clave      // contraseña
            );

            // Configuramos PDO para que lance excepciones si ocurre un error
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            // Si ocurre un error, se captura aquí y se muestra un mensaje
            die("❌ Error en la conexión: " . $e->getMessage());
        }
    }

    // Método público para obtener la conexión y usarla en otras clases/archivos
    public function getConexion() {
        return $this->conexion;
    }
}
?>
