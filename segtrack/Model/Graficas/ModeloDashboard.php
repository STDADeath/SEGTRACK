<?php
class ModeloDashboard {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // =============================
    // 📊 DISPOSITIVOS
    // =============================
    public function DispositivosPorTipo() {
        $sql = "SELECT TipoDispositivo AS tipo_dispositivos, COUNT(*) AS cantidad_Dispositivos  
                FROM dispositivo GROUP BY TipoDispositivo";
        return $this->obtenerDatos($sql);
    }

    public function DispositivosTotal() {
        $sql = "SELECT COUNT(*) AS total_dispositivos FROM dispositivo";
        return $this->obtenerTotal($sql, 'total_dispositivos');
    }

    // =============================
    // 🚗 VEHÍCULOS
    // =============================
    public function VehiculosPorTipo() {
        $sql = "SELECT TipoVehiculo AS tipo_vehiculos, COUNT(*) AS cantidad_Vehiculos 
                FROM parqueadero GROUP BY TipoVehiculo";
        return $this->obtenerDatos($sql);
    }

    public function VehiculosPorSede() {
        $sql = "
            SELECT 
                s.NombreSede AS sede,
                p.TipoVehiculo AS tipo_vehiculo,
                COUNT(*) AS cantidad
            FROM parqueadero p
            INNER JOIN sede s ON s.IdSede = p.IdSede
            GROUP BY s.NombreSede, p.TipoVehiculo
            ORDER BY s.NombreSede;
        ";
        return $this->obtenerDatos($sql);
    }

    public function ParqueaderoTotal() {
        $sql = "SELECT COUNT(*) AS total_vehiculos FROM parqueadero";
        return $this->obtenerTotal($sql, 'total_vehiculos');
    }

    // =============================
    // 📦 DOTACIÓN
    // =============================
    public function DotacionTotal() {
        $sql = "SELECT COUNT(*) AS total_dotacion FROM dotacion";
        $stmt = $this->conexion->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data['total_dotacion'];
    }

    public function DotacionPorTipo() {
        $sql = "SELECT TipoDotacion AS tipo_dotaciones, COUNT(*) AS cantidad_dotaciones  
                FROM dotacion GROUP BY TipoDotacion";
        return $this->obtenerDatos($sql);
    }

    public function DotacionPorEstado() {
        $sql = "SELECT EstadoDotacion AS estado_dotaciones, COUNT(*) AS cantidad_estado_dotaciones
                FROM dotacion GROUP BY EstadoDotacion";
        return $this->obtenerDatos($sql);
    }

    public function DotacionesPorMes() {
        $sql = "
            SELECT 
                DATE_FORMAT(FechaEntrega, '%Y-%m') AS mes,
                COUNT(*) AS cantidad
            FROM dotacion
            WHERE FechaEntrega IS NOT NULL
            GROUP BY mes
            ORDER BY mes ASC
        ";
        return $this->obtenerDatos($sql);
    }

    public function DotacionesPorDevolucionMes() {
        $sql = "
            SELECT 
                DATE_FORMAT(FechaDevolucion, '%Y-%m') AS mes,
                COUNT(*) AS cantidad
            FROM dotacion
            WHERE FechaDevolucion IS NOT NULL
            GROUP BY mes
            ORDER BY mes ASC
        ";
        return $this->obtenerDatos($sql);
    }

    // =============================
    // 👤 FUNCIONARIOS / VISITANTES
    // =============================
    public function FuncionariosTotal() {
        $sql = "SELECT COUNT(*) AS total_funcionarios FROM funcionario";
        return $this->obtenerTotal($sql, 'total_funcionarios');
    }

    public function TotalVisitante() {
        $sql = "SELECT COUNT(*) AS total_visitantes FROM visitante";
        return $this->obtenerTotal($sql, 'total_visitantes');
    }

    // =============================
    // 📝 BITÁCORA
    // =============================
    public function TotalBitacora() {
        $sql = "SELECT COUNT(*) AS total_bitacora FROM bitacora";
        return $this->obtenerTotal($sql, 'total_bitacora');
    }

    public function BitacoraPorTurno() {
        $sql = "SELECT TurnoBitacora AS turno_bitacoras, COUNT(*) AS cantidad_bitacoras_turno
                FROM bitacora
                GROUP BY TurnoBitacora";
        return $this->obtenerDatos($sql);
    }

    public function BitacoraPorMes() {
        $sql = "
            SELECT 
                DATE_FORMAT(FechaBitacora, '%Y-%m') AS mes,
                COUNT(*) AS cantidad
            FROM bitacora
            WHERE FechaBitacora IS NOT NULL
            GROUP BY mes
            ORDER BY mes ASC
        ";
        return $this->obtenerDatos($sql);
    }

    // =============================
    // 🧩 MÉTODOS AUXILIARES
    // =============================
    private function obtenerDatos($sql) {
        $stmt = $this->conexion->query($sql);
        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }
        return $data;
    }

    private function obtenerTotal($sql, $campo) {
        $stmt = $this->conexion->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data[$campo];
    }
}
?>
