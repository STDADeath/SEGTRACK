<?php
require_once __DIR__ . '/../Conexion/conexion.php';
require_once __DIR__ . '/../model/Funcionario.php';

class FuncionarioController {
    private $modelo;

    public function __construct() {
        $conexion = (new Conexion())->getConexion();
        $this->modelo = new Funcionario($conexion);
    }

    // Validaciones
    private function validarDatos(array $datos, bool $esActualizacion = false): ?string {
        // Validar campos vacíos
        if (empty($datos['NombreFuncionario'])) return 'El nombre es obligatorio';
        if (empty($datos['DocumentoFuncionario'])) return 'El documento es obligatorio';
        if (empty($datos['TelefonoFuncionario'])) return 'El teléfono es obligatorio';
        if (empty($datos['CorreoFuncionario'])) return 'El correo es obligatorio';
        if (empty($datos['CargoFuncionario'])) return 'El cargo es obligatorio';
        if (empty($datos['IdSede'])) return 'La sede es obligatoria';

        // Validar longitudes
        if (strlen($datos['NombreFuncionario']) > 30) {
            return 'El nombre no debe superar 30 caracteres';
        }
        if (strlen($datos['DocumentoFuncionario']) > 12) {
            return 'El documento no debe superar 12 dígitos';
        }
        if (strlen($datos['TelefonoFuncionario']) != 10) {
            return 'El teléfono debe tener exactamente 10 dígitos';
        }
        if (strlen($datos['CorreoFuncionario']) > 80) {
            return 'El correo no debe superar 80 caracteres';
        }

        // Validar formatos
        if (!ctype_digit($datos['DocumentoFuncionario'])) {
            return 'El documento debe contener solo números';
        }
        if (!ctype_digit($datos['TelefonoFuncionario'])) {
            return 'El teléfono debe contener solo números';
        }
        if (!filter_var($datos['CorreoFuncionario'], FILTER_VALIDATE_EMAIL)) {
            return 'El correo electrónico no es válido';
        }

        return null;
    }

    public function insertar(array $datos): array {
        try {
            // Validar datos
            $error = $this->validarDatos($datos);
            if ($error) {
                return ['success' => false, 'error' => $error];
            }

            // Validar duplicados
            if ($this->modelo->existeDocumento($datos['DocumentoFuncionario'])) {
                return ['success' => false, 'error' => "Ya existe un funcionario con el documento {$datos['DocumentoFuncionario']}"];
            }
            if ($this->modelo->existeCorreo($datos['CorreoFuncionario'])) {
                return ['success' => false, 'error' => "Ya existe un funcionario con el correo {$datos['CorreoFuncionario']}"];
            }

            // Validar sede
            if (!$this->modelo->existeSede($datos['IdSede'])) {
                return ['success' => false, 'error' => 'La sede seleccionada no existe'];
            }

            // Insertar
            $resultado = $this->modelo->insertar($datos);

            if ($resultado['success']) {
                $funcionario = $this->modelo->obtenerPorId($resultado['id']);
                return [
                    'success' => true,
                    'message' => 'Funcionario registrado correctamente',
                    'data' => $funcionario
                ];
            }

            return $resultado;

        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error del servidor: ' . $e->getMessage()];
        }
    }

    public function actualizar(int $id, array $datos): array {
        try {
            // Verificar existencia
            $funcionario = $this->modelo->obtenerPorId($id);
            if (!$funcionario) {
                return ['success' => false, 'error' => 'El funcionario no existe'];
            }

            // Validar datos
            $error = $this->validarDatos($datos, true);
            if ($error) {
                return ['success' => false, 'error' => $error];
            }

            // Validar duplicados
            if ($this->modelo->existeDocumento($datos['DocumentoFuncionario'], $id)) {
                return ['success' => false, 'error' => "Ya existe otro funcionario con el documento {$datos['DocumentoFuncionario']}"];
            }
            if ($this->modelo->existeCorreo($datos['CorreoFuncionario'], $id)) {
                return ['success' => false, 'error' => "Ya existe otro funcionario con el correo {$datos['CorreoFuncionario']}"];
            }

            // Validar sede
            if (!$this->modelo->existeSede($datos['IdSede'])) {
                return ['success' => false, 'error' => 'La sede seleccionada no existe'];
            }

            // Actualizar
            $resultado = $this->modelo->actualizar($id, $datos);

            if ($resultado['success']) {
                $funcionarioActualizado = $this->modelo->obtenerPorId($id);
                return [
                    'success' => true,
                    'message' => 'Funcionario actualizado correctamente',
                    'data' => $funcionarioActualizado
                ];
            }

            return $resultado;

        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error del servidor: ' . $e->getMessage()];
        }
    }

    public function eliminar(int $id): array {
        try {
            // Verificar existencia
            $funcionario = $this->modelo->obtenerPorId($id);
            if (!$funcionario) {
                return ['success' => false, 'error' => 'El funcionario no existe'];
            }

            // Verificar relaciones
            $relacion = $this->modelo->tieneRelaciones($id);
            if ($relacion) {
                return ['success' => false, 'error' => "No se puede eliminar. El funcionario tiene registros en $relacion"];
            }

            // Eliminar
            $resultado = $this->modelo->eliminar($id);

            if ($resultado['success']) {
                return [
                    'success' => true,
                    'message' => 'Funcionario eliminado correctamente'
                ];
            }

            return $resultado;

        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error del servidor: ' . $e->getMessage()];
        }
    }

    public function obtenerTodos(): array {
        try {
            $funcionarios = $this->modelo->obtenerTodos();
            return [
                'success' => true,
                'data' => $funcionarios
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error al obtener funcionarios: ' . $e->getMessage()];
        }
    }

    public function obtenerPorId(int $id): array {
        try {
            $funcionario = $this->modelo->obtenerPorId($id);
            if ($funcionario) {
                return [
                    'success' => true,
                    'data' => $funcionario
                ];
            }
            return ['success' => false, 'error' => 'Funcionario no encontrado'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error al obtener funcionario: ' . $e->getMessage()];
        }
    }
}
?>