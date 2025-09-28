<?php
/*
class Conexion {
    private $host = "localhost";
    private $usuario = "root";
    private $clave = "";
    private $db = "dbsegtrack";
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
*/

// Definimos la clase Conexion (Programación Orientada a Objetos)
class Conexion {
    // Atributos privados con los datos de la base de datos
    private $host = "localhost";   // Servidor de la BD
    private $usuario = "root";     // Usuario de la BD
    private $clave = "";           // Contraseña del usuario
    private $db = "segtrack21";    // Nombre de la base de datos
    private $conexion;             // Aquí se guardará el objeto PDO

    // Constructor: se ejecuta automáticamente cuando se crea un objeto de la clase
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
