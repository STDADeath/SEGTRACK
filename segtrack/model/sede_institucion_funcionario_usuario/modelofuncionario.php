<?php

class Modelofuncionario {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function RegistrarFuncionario(string $Cargo, string $Nombre, int $Sede, int $Telefono, int $Documento, string $Correo): array {
        try {
            if (!$this->conexion) {
                return ['success' => false, 'error' => 'Conexión a la base de datos no disponible'];
            }

            $sql = "INSERT INTO funcionario
                    (CargoFuncionario, NombreFuncionario, IdSede, TelefonoFuncionario, DocumentoFuncionario, CorreoFuncionario)
                    VALUES (:Cargo, :Nombre, :Sede, :Telefono, :Documento, :Correo)";

          $stmt = $this -> conexion -> prepare($sql);
          $resultado = $stmt -> execute([
            ':cargo' => $Cargo,
            ':Nombre' =>$nombre,
            ':Sede' => $Sede,
            ':Telefono' => $Telefono,
            ':Documento' => $Documento,
            ':Correo' => $Correo

          ]);
          if ($resultado) {
            return ['success' => true, 'id' => $this->conexion->lastInsertId()];
          } else {
                 $errorInfo = $stmt->errorinfo();
                 return['success' => true, 'error' => $errorInfo[2] ?? 'error desconocido al insertar '];
           }
          } catch(PDOException $e){
           return ['success' => false, 'error' => $e->getMessage()];
          }
    }

        public function ActuaizarQR(int $IdFuncionario, string $rutaQR): array {
            try {
                if(!$this->conexion){
                    return ['success' => false, 'error' => 'conexion a la base de datos no disponible'];
                }

                $sql = "UPDATE funcionario SET QrCodigoFuncionario = :qr WHERE  IdFuncionario = :id";
                $stmt = $this->conexion->prepare($sql);
                $resultado = $stmt ->execute([
                    ':qr'=> $rutaQR,
                    'id' => $IdFuncionario
                ]);
            }
        }
}

?>
