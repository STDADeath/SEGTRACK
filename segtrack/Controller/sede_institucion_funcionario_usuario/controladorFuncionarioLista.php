<?php
require_once __DIR__ . "/../../model/sede_institucion_funcionario_usuario/modeloFuncionarioLista.php";

class FuncionarioController {
    private $model;

    public function __construct() {
        $this->model = new FuncionarioModel();
    }

    public function registrarFuncionario($data) {
        return $this->model->registrar(
            $data['nombre'],
            $data['telefono'],
            $data['correo'],
            $data['documento'],
            $data['cargo'],
            $data['sede']
        );
    }

    public function obtenerFuncionarios() {
        return $this->model->listar();
    }

    public function obtenerFuncionario($id) {
        return $this->model->obtener($id);
    }

    public function actualizarFuncionario($data) {
        return $this->model->actualizar(
            $data['id'],
            $data['nombre'],
            $data['telefono'],
            $data['correo'],
            $data['documento'],
            $data['cargo'],
            $data['sede']
        );
    }

    public function eliminarFuncionario($id) {
        return $this->model->eliminar($id);
    }
}
?>
