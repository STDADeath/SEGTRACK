<?php/*
require_once __DIR__ . '/../../Core/conexion.php';

class ModeloSede {
    private $conexion;

    public function __construct() {
        $this->conexion = (new Conexion())->getConexion();
    }

    // Insertar nueva sede
    public function insertar($tipoSede, $ciudad, $idInstitucion) {
        try {
            $sql = "INSERT INTO sede (TipoSede, Ciudad, IdInstitucion)
                    VALUES (:tipoSede, :ciudad, :idInstitucion)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':tipoSede', $tipoSede, PDO::PARAM_STR);
            $stmt->bindParam(':ciudad', $ciudad, PDO::PARAM_STR);
            $stmt->bindParam(':idInstitucion', $idInstitucion, PDO::PARAM_INT);
            $stmt->execute();

            return ['success' => true];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}*/

session_start();
require_once __DIR__ . '/../../Model/sede_institucion_funcionario_usuario/modelosede.php';

$modeloSede = new ModeloSede();

// Registrar nueva sede
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipoSede = trim($_POST['TipoSede'] ?? '');
    $ciudad = trim($_POST['Ciudad'] ?? '');
    $idInstitucion = trim($_POST['IdInstitucion'] ?? '');

    if ($tipoSede === '' || $ciudad === '' || $idInstitucion === '') {
        echo "<script>alert('Por favor llena todos los campos'); window.history.back();</script>";
        exit;
    }

    $resultado = $modeloSede->insertar($tipoSede, $ciudad, $idInstitucion);

    if ($resultado['success']) {
        echo "<script>alert('✅ Sede agregada correctamente'); window.location.href='../../View/sede_institucion_funcionario_usuario/vista_sede.php';</script>";
    } else {
        echo "<script>alert('❌ Error al agregar la sede: {$resultado['error']}'); window.history.back();</script>";
    }
}

?>
