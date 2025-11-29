<?php
// App/Model/modeloinstituto.php

// Ruta a la conexión (sube a /App, baja a /Core)
require_once __DIR__ . '/../Core/conexion.php';

class ModeloInstituto
{
    private $conexion;

    public function __construct()
    {
        // Usamos un try-catch para capturar el fallo de conexión
        try {
            $conexionObj = new Conexion(); 
            $this->conexion = $conexionObj->getConexion();
        } catch (\PDOException $e) {
            // Este catch nunca se disparará si die() está en Conexion.php
            // Pero es buena práctica.
            throw new Exception("Fallo al inicializar el modelo. Verifique la clase Conexion.");
        }
    }

    // ** FUNCIÓN CLAVE DE INSERCIÓN **
    public function insertarInstituto(array $datos)
    {
        try {
            $sql = "INSERT INTO institucion (NombreInstitucion, Nit_Codigo, TipoInstitucion, EstadoInstitucion) 
                    VALUES (:nombre, :nit, :tipo, :estado)";
            $stmt = $this->conexion->prepare($sql);
            
            $stmt->bindParam(':nombre', $datos['NombreInstitucion']);
            $stmt->bindParam(':nit', $datos['Nit_Codigo']);
            $stmt->bindParam(':tipo', $datos['TipoInstitucion']);
            $stmt->bindParam(':estado', $datos['EstadoInstitucion']);
            
            $stmt->execute();

            return ['error' => false, 'mensaje' => 'Institución "' . $datos['NombreInstitucion'] . '" registrada con éxito.'];

        } catch (PDOException $e) {
            // Manejar errores de SQL como duplicados
            if ($e->getCode() == 23000) { 
                 return ['error' => true, 'mensaje' => 'Error: El NIT/Código ' . $datos['Nit_Codigo'] . ' ya se encuentra registrado.'];
            }
            return ['error' => true, 'mensaje' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    } 
    
    // ... (El resto de tus funciones de búsqueda, listar, eliminar) ...
}
?>