<?php

class ModeloIngresoDispositivo {
    private $pdo; // Almacena la conexión a la base de datos

    // Constructor: se ejecuta automáticamente al crear el objeto del modelo
    public function __construct() {
        require_once __DIR__ . '/../Core/conexion.php';
        // Carga la clase Conexion para acceder a la BD

        $conexionObj = new Conexion();
        $this->pdo = $conexionObj->getConexion();
        // Se obtiene la conexión PDO desde la clase Conexion

        if (!$this->pdo) {
            die("ERROR: La conexión no se inicializó correctamente.");
        }
    }


    // Busca un dispositivo usando el contenido del código QR.
    // El QR trae un formato como: "Serial: NS56789\nTipo: Portátil\nMarca: Dell"

    public function buscarDispositivoPorQr($qrCodigo) {

        // Expresión regular que extrae el valor después de "Serial:"
        if (preg_match('/Serial:\s*(.+)/i', $qrCodigo, $match)) {
            $serial = trim($match[1]); // Se obtiene el NumeroSerial
        } else {
            // Si el QR no tiene formato correcto, se retorna false
            return false;
        }

        // Consulta SQL para obtener los datos del dispositivo por su serial
        $sql = "SELECT d.*,
                       COALESCE(f.NombreFuncionario, 'Sin asignar') AS NombreFuncionario,
                       COALESCE(f.CargoFuncionario,  'Sin asignar') AS CargoFuncionario,
                       f.IdSede
                FROM dispositivo d
                LEFT JOIN funcionario f ON d.IdFuncionario = f.IdFuncionario
                WHERE d.NumeroSerial = ?
                  AND d.Estado = 'Activo'";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$serial]);

        // Retorna un arreglo asociativo con los datos, o false si no existe
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // Registra un ingreso o salida del dispositivo en la base de datos
    // Retorna true si se insertó correctamente, false en caso contrario

    public function registrarIngreso($idDispositivo, $idFuncionario = null, $idSede = null, $tipoMovimiento = 'Entrada') {

        $sql = "INSERT INTO ingreso (TipoMovimiento, FechaIngreso, IdSede, IdFuncionario, IdDispositivo)
                VALUES (?, NOW(), ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);

        // Se ejecuta la consulta con los parámetros enviados
        return $stmt->execute([$tipoMovimiento, $idSede, $idFuncionario, $idDispositivo]);
    }


    // Lista todos los movimientos de dispositivos registrados.
    // Se utiliza JOIN para unir ingreso con dispositivo y funcionario.

    public function listarIngresos() {

        $sql = "SELECT
                    i.IdIngreso,
                    d.TipoDispositivo,
                    d.MarcaDispositivo,
                    d.NumeroSerial,
                    COALESCE(f.NombreFuncionario, 'Sin asignar') AS NombreFuncionario,
                    i.TipoMovimiento,
                    i.FechaIngreso
                FROM ingreso i
                INNER JOIN dispositivo d ON i.IdDispositivo = d.IdDispositivo
                LEFT  JOIN funcionario f ON i.IdFuncionario = f.IdFuncionario
                WHERE i.IdDispositivo IS NOT NULL
                ORDER BY i.IdIngreso DESC"; // Se muestran del más reciente al más antiguo

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        // Se retorna un arreglo con todos los registros
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>