<?php
// App/Model/modeloinstituto.php

require_once __DIR__ . '/../Core/conexion.php';

class ModeloInstituto
{
    private $conexion;

    public function __construct()
    {
        try {
            $conexionObj = new Conexion(); 
            $this->conexion = $conexionObj->getConexion();
        } catch (\PDOException $e) {
            throw new Exception("Fallo al inicializar el modelo.");
        }
    }

    // INSERTAR
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

            return [
                'ok' => true, 
                'message' => 'Institución "' . $datos['NombreInstitucion'] . '" registrada con éxito.'
            ];

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { 
                return [
                    'ok' => false, 
                    'message' => 'El NIT/Código ' . $datos['Nit_Codigo'] . ' ya existe.'
                ];
            }
            return ['ok' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // LISTAR
    public function listarInstitutos()
    {
        try {
            $sql = "SELECT IdInstitucion, EstadoInstitucion, NombreInstitucion, 
                           Nit_Codigo, TipoInstitucion 
                    FROM institucion 
                    ORDER BY IdInstitucion DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al listar: " . $e->getMessage());
        }
    }

    // EDITAR
    public function editarInstituto(array $datos)
    {
        try {
            $sql = "UPDATE institucion SET 
                    NombreInstitucion = :nombre,
                    Nit_Codigo = :nit,
                    TipoInstitucion = :tipo,
                    EstadoInstitucion = :estado
                    WHERE IdInstitucion = :id";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $datos['IdInstitucion'], PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $datos['NombreInstitucion']);
            $stmt->bindParam(':nit', $datos['Nit_Codigo']);
            $stmt->bindParam(':tipo', $datos['TipoInstitucion']);
            $stmt->bindParam(':estado', $datos['EstadoInstitucion']);
            
            $stmt->execute();
            
            return [
                'ok' => true, 
                'message' => 'Institución actualizada correctamente.'
            ];
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return [
                    'ok' => false, 
                    'message' => 'El NIT/Código ya existe en otra institución.'
                ];
            }
            return [
                'ok' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    // OBTENER UNA INSTITUCIÓN POR ID
    public function obtenerInstitutoPorId($id)
    {
        try {
            $sql = "SELECT * FROM institucion WHERE IdInstitucion = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener institución: " . $e->getMessage());
        }
    }

    // CAMBIAR SOLO EL ESTADO (para toggle rápido)
    public function cambiarEstado($id, $nuevoEstado)
    {
        try {
            $sql = "UPDATE institucion SET EstadoInstitucion = :estado WHERE IdInstitucion = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':estado', $nuevoEstado);
            $stmt->execute();
            
            return [
                'ok' => true, 
                'message' => 'Estado cambiado a ' . $nuevoEstado . ' correctamente.'
            ];
        } catch (PDOException $e) {
            return [
                'ok' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}
?>