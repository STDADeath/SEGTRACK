<?php
class ModeloDashboard {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }


    public function dispositivosPorTipo() {
        $sql = "SELECT TipoDispositivo AS tipo_dispositivos, COUNT(*) AS cantidad_Dispositivos  FROM dispositivo GROUP BY TipoDispositivo";
        $result = $this->conexion->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
        $data[] = $row;
        }
        return $data;
    }


    public function DispositivosTotal() {
        $sql = "SELECT COUNT(*) AS total_dispositivos FROM dispositivo";
        $result = $this->conexion->query($sql);
        $data = $result->fetch_assoc();
        return $data['total_dispositivos'];
    }

    public function FuncionariosTotal() {
        $sql = "SELECT COUNT(*) AS total_funcionarios FROM funcionario";
        $result = $this->conexion->query($sql);
        $data = $result->fetch_assoc();
        return $data['total_funcionarios'];
    }


    public function TotalVisitante() {
        $sql = "SELECT COUNT(*) AS total_visitantes FROM visitante";
        $result = $this->conexion->query($sql);
        $data = $result->fetch_assoc();
        return $data['total_visitantes'];
    }
}
?>
