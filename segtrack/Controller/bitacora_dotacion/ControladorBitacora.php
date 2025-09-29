<?php
require_once __DIR__ . "/../../models/Bitacora/BitacoraModel.php";

class BitacoraController {
    private BitacoraModelo $modelo;

    public function __construct($conexion) {
        $this->modelo = new BitacoraModelo($conexion);
    }


    private function campoVacio(array $array, string $campo): bool {
        return !isset($array[$campo]) || trim($array[$campo]) === "";
    }

    private function fechaValida(string $fecha): bool {
        $d = DateTime::createFromFormat('Y-m-d H:i:s', $fecha);
        return $d && $d->format('Y-m-d H:i:s') === $fecha;
    }


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

        foreach ($camposObligatorios as $campo) {
            if ($this->campoVacio($DatosBitacora, $campo)) {
                return ['success' => false, 'message' => "Falta el campo obligatorio: $campo"];
            }
        }

        if (!$this->fechaValida($DatosBitacora['FechaBitacora'])) {
            return ['success' => false, 'message' => "Formato de fecha inválido"];
        }

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


    public function mostrarBitacora(): array {
        return $this->modelo->obtenerTodos();
    }


    public function obtenerPorId(int $IdBitacora): ?array {
        return $this->modelo->obtenerPorId($IdBitacora);
    }

 
    public function actualizar(int $IdBitacora, array $DatosBitacora): array {

        if (isset($DatosBitacora['FechaBitacora']) && !$this->fechaValida($DatosBitacora['FechaBitacora'])) {
            return ['success' => false, 'message' => "Formato de fecha inválido"];
        }

        return $this->modelo->actualizar($IdBitacora, $DatosBitacora);
    }
}
