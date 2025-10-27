<?php
class ModeloDashboard {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // 🔹 Gráfica: Dispositivos por tipo
    public function DispositivosPorTipo() {
        $sql = "SELECT TipoDispositivo AS tipo_dispositivos, COUNT(*) AS cantidad_Dispositivos  
                FROM dispositivo GROUP BY TipoDispositivo";
        $stmt = $this->conexion->query($sql);
        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }
        return $data;
    }

    public function DispositivosTotal() {
        $sql = "SELECT COUNT(*) AS total_dispositivos FROM dispositivo";
        $stmt = $this->conexion->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data['total_dispositivos'];
    }

    public function FuncionariosTotal() {
        $sql = "SELECT COUNT(*) AS total_funcionarios FROM funcionario";
        $stmt = $this->conexion->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data['total_funcionarios'];
    }

    public function TotalVisitante() {
        $sql = "SELECT COUNT(*) AS total_visitantes FROM visitante";
        $stmt = $this->conexion->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data['total_visitantes'];
    }

    public function VehiculosPorTipo() {
        $sql = "SELECT TipoVehiculo AS tipo_vehiculos, COUNT(*) AS cantidad_Vehiculos 
                FROM parqueadero GROUP BY TipoVehiculo";
        $stmt = $this->conexion->query($sql);
        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }
        return $data;
    }

    public function ParqueaderoTotal() {
        $sql = "SELECT COUNT(*) AS total_vehiculos FROM parqueadero";
        $stmt = $this->conexion->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data['total_vehiculos'];
    }

    // 🔹 NUEVO: Vehículos por sede
    public function vehiculosPorSede() {
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
        $stmt = $this->conexion->query($sql);
        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }
        return $data;
    }
}
?>
