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
        $sql = "SELECT TipoDispositivo AS tipo_dispositivos, 
                       COUNT(*) AS cantidad_Dispositivos
                FROM dispositivo 
                GROUP BY TipoDispositivo";
        return $this->obtenerDatos($sql);
    }

    public function DispositivosTotal() {
        $sql = "SELECT COUNT(*) AS total_dispositivos FROM dispositivo";
        return $this->obtenerTotal($sql, 'total_dispositivos');
    }

    // =============================
    // 🚗 VEHÍCULOS
    // =============================

    // ✅ CORREGIDO — usa tabla vehiculo, no parqueadero
    public function VehiculosPorTipo() {
        $sql = "SELECT TipoVehiculo AS tipo_vehiculos, 
                       COUNT(*) AS cantidad_Vehiculos
                FROM vehiculo 
                GROUP BY TipoVehiculo";
        return $this->obtenerDatos($sql);
    }

    // ✅ CORREGIDO — vehiculo tiene IdSede
    public function VehiculosPorSede() {
        $sql = "SELECT s.TipoSede AS sede,
                       v.TipoVehiculo AS tipo_vehiculo,
                       COUNT(*) AS cantidad
                FROM vehiculo v
                INNER JOIN sede s ON s.IdSede = v.IdSede
                GROUP BY s.TipoSede, v.TipoVehiculo
                ORDER BY s.TipoSede";
        return $this->obtenerDatos($sql);
    }

    // ✅ CORREGIDO — cuenta desde tabla vehiculo
    public function ParqueaderoTotal() {
        $sql = "SELECT COUNT(*) AS total_vehiculos FROM vehiculo";
        return $this->obtenerTotal($sql, 'total_vehiculos');
    }

    // =============================
    // 📦 DOTACIÓN
    // =============================
    public function DotacionTotal() {
        $sql  = "SELECT COUNT(*) AS total_dotacion FROM dotacion";
        $stmt = $this->conexion->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data['total_dotacion'];
    }

    public function DotacionPorTipo() {
        $sql = "SELECT TipoDotacion AS tipo_dotaciones, 
                       COUNT(*) AS cantidad_dotaciones
                FROM dotacion 
                GROUP BY TipoDotacion";
        return $this->obtenerDatos($sql);
    }

    public function DotacionPorEstado() {
        $sql = "SELECT EstadoDotacion AS estado_dotaciones, 
                       COUNT(*) AS cantidad_estado_dotaciones
                FROM dotacion 
                GROUP BY EstadoDotacion";
        return $this->obtenerDatos($sql);
    }

    public function DotacionesPorMes() {
        $sql = "SELECT DATE_FORMAT(FechaEntrega, '%b %Y') AS mes,
                       COUNT(*) AS cantidad
                FROM dotacion
                WHERE FechaEntrega IS NOT NULL
                GROUP BY DATE_FORMAT(FechaEntrega, '%Y-%m')
                ORDER BY MIN(FechaEntrega) ASC";
        return $this->obtenerDatos($sql);
    }

    public function DotacionesPorDevolucionMes() {
        $sql = "SELECT DATE_FORMAT(FechaDevolucion, '%b %Y') AS mes,
                       COUNT(*) AS cantidad
                FROM dotacion
                WHERE FechaDevolucion IS NOT NULL
                GROUP BY DATE_FORMAT(FechaDevolucion, '%Y-%m')
                ORDER BY MIN(FechaDevolucion) ASC";
        return $this->obtenerDatos($sql);
    }

    // =============================
    // 👤 FUNCIONARIOS
    // =============================
    public function FuncionariosTotal() {
        $sql = "SELECT COUNT(*) AS total_funcionarios FROM funcionario";
        return $this->obtenerTotal($sql, 'total_funcionarios');
    }

    // ✅ NUEVO — agrupa por CargoFuncionario
    public function FuncionariosPorCargo() {
        $sql = "SELECT CargoFuncionario, COUNT(*) AS total
                FROM funcionario
                GROUP BY CargoFuncionario
                ORDER BY total DESC";
        return $this->obtenerDatos($sql);
    }

    // =============================
    // 👥 VISITANTES
    // =============================
    public function TotalVisitante() {
        $sql = "SELECT COUNT(*) AS total_visitantes FROM visitante";
        return $this->obtenerTotal($sql, 'total_visitantes');
    }

    // ✅ CORREGIDO — visitante no tiene fecha, usamos ingreso
    // que registra entradas/salidas con FechaIngreso
    public function VisitantesPorMes() {
        $sql = "SELECT DATE_FORMAT(FechaIngreso, '%b %Y') AS mes,
                       COUNT(*) AS total
                FROM ingreso
                WHERE FechaIngreso IS NOT NULL
                GROUP BY DATE_FORMAT(FechaIngreso, '%Y-%m')
                ORDER BY MIN(FechaIngreso) ASC
                LIMIT 12";
        return $this->obtenerDatos($sql);
    }

    // ✅ NUEVO — ingresos por tipo (Entrada/Salida)
    public function IngresosPorTipo() {
        $sql = "SELECT TipoMovimiento AS tipo,
                       COUNT(*) AS total
                FROM ingreso
                GROUP BY TipoMovimiento";
        return $this->obtenerDatos($sql);
    }

    // ✅ NUEVO — funcionarios por sede
    public function FuncionariosPorSede() {
        $sql = "SELECT s.TipoSede AS sede,
                       COUNT(*) AS total
                FROM funcionario f
                INNER JOIN sede s ON s.IdSede = f.IdSede
                GROUP BY s.TipoSede
                ORDER BY total DESC";
        return $this->obtenerDatos($sql);
    }

    // =============================
    // 📝 BITÁCORA
    // =============================
    public function TotalBitacora() {
        $sql = "SELECT COUNT(*) AS total_bitacora FROM bitacora";
        return $this->obtenerTotal($sql, 'total_bitacora');
    }

    public function BitacoraPorTurno() {
        $sql = "SELECT TurnoBitacora AS turno_bitacoras, 
                       COUNT(*) AS cantidad_bitacoras_turno
                FROM bitacora 
                GROUP BY TurnoBitacora";
        return $this->obtenerDatos($sql);
    }

    public function BitacoraPorMes() {
        $sql = "SELECT DATE_FORMAT(FechaBitacora, '%b %Y') AS mes, 
                       COUNT(*) AS cantidad
                FROM bitacora 
                WHERE FechaBitacora IS NOT NULL
                GROUP BY DATE_FORMAT(FechaBitacora, '%Y-%m')
                ORDER BY MIN(FechaBitacora) ASC";
        return $this->obtenerDatos($sql);
    }

    // =============================
    // 🧩 AUXILIARES
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