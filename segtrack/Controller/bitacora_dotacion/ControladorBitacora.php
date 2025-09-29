<?php
require_once __DIR__ . "/../../models/Bitacora/BitacoraModel.php";

class BitacoraController {
    private BitacoraModelo $modelo;

    public function __construct($conexion) {
        $this->modelo = new BitacoraModelo($conexion);
    }

    /**
     * Verifica si un campo está vacío
     */
    private function campoVacio(array $array, string $campo): bool {
        return !isset($array[$campo]) || trim($array[$campo]) === "";
    }

    /**
     * Valida que la fecha tenga el formato correcto
     */
    private function fechaValida(string $fecha): bool {
        $d = DateTime::createFromFormat('Y-m-d H:i:s', $fecha);
        return $d && $d->format('Y-m-d H:i:s') === $fecha;
    }

    /**
     * Registrar una nueva bitácora
     */
    public function registrarBitacora(array $DatosBitacora): array {
        $camposObligatorios = [
            'TurnoBitacora',
            'NovedadesBitacora',
            'FechaBitacora',
            'IdFuncionario',
            'IdIngreso',
            'IdDispositivo', 
            'IdVisitante'
        ];

        // Validar campos obligatorios
        foreach ($camposObligatorios as $campo) {
            if ($this->campoVacio($DatosBitacora, $campo)) {
                return ['success' => false, 'message' => "Falta el campo obligatorio: $campo"];
            }
        }

        // Validar fecha
        if (!$this->fechaValida($DatosBitacora['FechaBitacora'])) {
            return ['success' => false, 'message' => "Formato de fecha inválido"];
        }

        // Insertar usando el modelo
        $resultado = $this->modelo->insertar($DatosBitacora);

        if ($resultado['success']) {
            return [
                'success' => true,
                'message' => 'Bitácora registrada correctamente',
                'data' => ['IdBitacora' => $resultado['id']]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'No se pudo registrar la bitácora',
                'error' => $resultado['error'] ?? null
            ];
        }
    }

    /**
     * Obtener todas las bitácoras
     */
    public function mostrarBitacora(): array {
        return $this->modelo->obtenerTodos();
    }

    /**
     * Obtener bitácora por ID
     */
    public function obtenerPorId(int $IdBitacora): ?array {
        return $this->modelo->obtenerPorId($IdBitacora);
    }

    /**
     * Actualizar bitácora
     */
    public function actualizar(int $IdBitacora, array $DatosBitacora): array {
        // Validar fecha si se envía
        if (isset($DatosBitacora['FechaBitacora']) && !$this->fechaValida($DatosBitacora['FechaBitacora'])) {
            return ['success' => false, 'message' => "Formato de fecha inválido"];
        }

        return $this->modelo->actualizar($IdBitacora, $DatosBitacora);
    }
}
