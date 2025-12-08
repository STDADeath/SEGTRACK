<?php
require_once __DIR__ . '/../Model/modelousuarioAdm.php';

class ControladorUsuario {

    private $modelo;

    // Roles permitidos según tu base de datos
    private $roles_validos = ['Supervisor', 'Personal Seguridad', 'Administrador'];

    public function __construct() {
        $this->modelo = new Modelo_Usuario();
    }

    // ================================================================
    // 🔹 MÉTODO PRINCIPAL (recibe todas las peticiones por POST)
    // ================================================================
    public function procesar() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->respuesta(false, "Método no permitido");
        }

        $accion = $_POST['accion'] ?? 'registrar';

        switch ($accion) {

            case "registrar":
                return $this->registrarUsuario();

            case "actualizar_qr":
                return $this->actualizarQR();

            case "listar":
                return $this->listarUsuarios();

            default:
                return $this->respuesta(false, "Acción no válida");
        }
    }

    // ================================================================
    // ✔ REGISTRAR USUARIO
    // ================================================================
    private function registrarUsuario() {

        $tipoRol = trim($_POST['tipo_rol'] ?? '');
        $contrasena = trim($_POST['contrasena'] ?? '');
        $idFuncionario = trim($_POST['id_funcionario'] ?? '');

        if ($tipoRol === '' || $contrasena === '' || $idFuncionario === '') {
            return $this->respuesta(false, "Todos los campos son obligatorios");
        }

        if (!in_array($tipoRol, $this->roles_validos)) {
            return $this->respuesta(false, "Rol no válido");
        }

        if (strlen($contrasena) < 7) {
            return $this->respuesta(false, "La contraseña debe tener mínimo 7 caracteres");
        }

        try {

            // valida si el funcionario ya tiene usuario asignado
            if ($this->modelo->usuarioExiste($idFuncionario)) {
                return $this->respuesta(false, "El funcionario ya tiene un usuario asignado");
            }

            // registra usuario
            $resultado = $this->modelo->registrarUsuario($tipoRol, $contrasena, $idFuncionario);

            if ($resultado) {
                return $this->respuesta(true, "Usuario registrado correctamente");
            } else {
                return $this->respuesta(false, "No se pudo registrar el usuario");
            }

        } catch (Exception $e) {
            return $this->respuesta(false, "Error interno del servidor");
        }
    }

    // ================================================================
    // ✔ ACTUALIZAR QR + ESTADO (Activo/Inactivo)
    // ================================================================
    private function actualizarQR() {

        $idFuncionario = $_POST['id_funcionario'] ?? '';
        $nuevoQR = $_POST['qr'] ?? '';
        $nuevoEstado = $_POST['estado'] ?? '';

        if ($idFuncionario === '') {
            return $this->respuesta(false, "ID de funcionario requerido");
        }

        // Llama al modelo
        $ok = $this->modelo->actualizarQR($idFuncionario, $nuevoQR, $nuevoEstado);

        if ($ok) {
            return $this->respuesta(true, "QR y estado actualizados correctamente");
        }

        return $this->respuesta(false, "Error al actualizar QR");
    }

    // ================================================================
    // ✔ LISTAR USUARIOS (para UsuariosLista.php)
    // ================================================================
    private function listarUsuarios() {

        try {
            $usuarios = $this->modelo->obtenerUsuarios(); // consulta preparada

            return $this->respuesta(true, $usuarios);

        } catch (Exception $e) {
            return $this->respuesta(false, "Error al obtener lista de usuarios");
        }
    }

    // ================================================================
    // ✔ RESPUESTA JSON
    // ================================================================
    private function respuesta($ok, $data) {

        if (ob_get_length()) { ob_clean(); }

        header("Content-Type: application/json; charset=utf-8");

        echo json_encode([
            "ok" => $ok,
            "data" => $data
        ]);

        exit;
    }
}

// Ejecuta el controlador
$controlador = new ControladorUsuario();
$controlador->procesar();
