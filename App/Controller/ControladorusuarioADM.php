<?php
require_once __DIR__ . '/../Model/modelousuarioAdm.php';

class ControladorUsuario {

    private $modelo;

    // Roles válidos permitidos (ENUM)
    private $roles_validos = ['Supervisor', 'Personal Seguridad', 'Administrador'];

    public function __construct() {
        $this->modelo = new Modelo_Usuario();
    }

    public function registrar() {

        // Evitar que pase GET o algo diferente
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->respuesta(false, "Método no permitido");
        }

        // ===============================
        // Capturar variables
        // ===============================
        $tipoRol = trim($_POST['tipo_rol'] ?? '');
        $contrasena = trim($_POST['contrasena'] ?? '');
        $idFuncionario = trim($_POST['id_funcionario'] ?? '');

        // ===============================
        // Validaciones del Backend
        // ===============================
        if ($tipoRol === '' || $contrasena === '' || $idFuncionario === '') {
            return $this->respuesta(false, "Todos los campos son obligatorios");
        }

        // Validar ENUM
        if (!in_array($tipoRol, $this->roles_validos)) {
            return $this->respuesta(false, "Rol no válido");
        }

        // Validar longitud de contraseña
        if (strlen($contrasena) < 7) {
            return $this->respuesta(false, "La contraseña debe tener mínimo 7 caracteres");
        }

        try {
            // Verificar si el funcionario ya tiene usuario
            if ($this->modelo->usuarioExiste($idFuncionario)) {
                return $this->respuesta(false, "El funcionario ya tiene un usuario asignado");
            }

            // Registrar usuario
            $resultado = $this->modelo->registrarUsuario($tipoRol, $contrasena, $idFuncionario);

            if ($resultado) {
                return $this->respuesta(true, "Usuario registrado correctamente");
            } else {
                return $this->respuesta(false, "No se pudo registrar el usuario");
            }

        } catch (Exception $e) {
            // Error interno
            return $this->respuesta(false, "Error interno del servidor.");
        }
    }

    // ===============================
    // RESPUESTA JSON CENTRALIZADA
    // ===============================
    private function respuesta($ok, $mensaje) {
        // Asegurar que no se genere salida previa
        if (ob_get_length()) { ob_clean(); }

        header("Content-Type: application/json; charset=utf-8");
        echo json_encode([
            "ok" => $ok,
            "mensaje" => $mensaje
        ]);
        exit;
    }
}

// Ejecutar controlador
$controlador = new ControladorUsuario();
$controlador->registrar();
