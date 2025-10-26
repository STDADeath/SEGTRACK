<?php
require_once __DIR__ . '/../../model/sede_institucion_funcionario_usuario/modelofuncionario.php';

class ControladorFuncionario {
    private $modelo;

    public function __construct() {
        $this->modelo = new ModeloFuncionario();
    }

    // =====================================================
    // ✅ Método principal para manejar las acciones POST
    // =====================================================
    public function manejarSolicitud() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accion = $_POST['accion'] ?? 'insertar'; // Valor por defecto

            $datos = [
                'NombreFuncionario'       => $_POST['NombreFuncionario'] ?? '',
                'DocumentoFuncionario'    => $_POST['DocumentoFuncionario'] ?? '',
                'CorreoFuncionario'       => $_POST['CorreoFuncionario'] ?? '',
                'TelefonoFuncionario'     => $_POST['TelefonoFuncionario'] ?? '',
                'CargoFuncionario'        => $_POST['CargoFuncionario'] ?? '',
                'IdSede'                  => $_POST['IdSede'] ?? ''
            ];

            switch ($accion) {
                case 'insertar':
                    $resultado = $this->modelo->insertarFuncionario($datos);
                    break;

                case 'actualizar':
                    $id = $_POST['IdFuncionario'] ?? null;
                    $resultado = $this->modelo->actualizarFuncionario($id, $datos);
                    break;

                case 'eliminar':
                    $id = $_POST['IdFuncionario'] ?? null;
                    $resultado = $this->modelo->eliminarFuncionario($id);
                    break;

                case 'filtrar':
                    $id = $_POST['IdFuncionario'] ?? null;
                    $resultado = $this->modelo->filtrarFuncionarioPorId($id);
                    break;

                default:
                    $resultado = ['error' => '⚠️ Acción no reconocida.'];
                    break;
            }

            // ✅ Mostrar resultado con SweetAlert2
            $this->mostrarAlerta($resultado);
        }
    }

    // =====================================================
    // ✅ Método para listar funcionarios (GET)
    // =====================================================
    public function mostrarFuncionarios() {
        $funcionarios = $this->modelo->listarFuncionarios();

        if (isset($funcionarios['error'])) {
            echo "<p style='color:red;'>" . $funcionarios['error'] . "</p>";
        } elseif (empty($funcionarios)) {
            echo "<p style='color:gray;'>⚠️ No hay funcionarios registrados aún.</p>";
        } else {
            echo "
            <style>
                table {
                    border-collapse: collapse;
                    width: 100%;
                    max-width: 900px;
                    margin: 30px auto;
                    border-radius: 10px;
                    overflow: hidden;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                    background: white;
                }
                th {
                    background-color: #4a69bd;
                    color: white;
                    padding: 10px;
                    text-align: center;
                }
                td {
                    padding: 8px;
                    text-align: center;
                    border-bottom: 1px solid #ddd;
                }
                tr:hover {
                    background-color: #f1f1f1;
                }
                caption {
                    caption-side: top;
                    font-weight: bold;
                    margin-bottom: 10px;
                    font-size: 18px;
                }
            </style>";

            echo "<table>
                    <caption>📋 Lista de Funcionarios Registrados</caption>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Documento</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Cargo</th>
                            <th>Sede</th>
                        </tr>
                    </thead>
                    <tbody>";
            foreach ($funcionarios as $f) {
                echo "<tr>
                        <td>{$f['IdFuncionario']}</td>
                        <td>{$f['NombreFuncionario']}</td>
                        <td>{$f['DocumentoFuncionario']}</td>
                        <td>{$f['CorreoFuncionario']}</td>
                        <td>{$f['TelefonoFuncionario']}</td>
                        <td>{$f['CargoFuncionario']}</td>
                        <td>{$f['IdSede']}</td>
                    </tr>";
            }
            echo "</tbody></table>";
        }
    }

    // =====================================================
    // ✅ Método para mostrar alertas con SweetAlert2
    // =====================================================
    private function mostrarAlerta($resultado) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";

        $icon = isset($resultado['error']) ? 'error' : 'success';
        $mensaje = isset($resultado['error']) ? $resultado['error'] : $resultado['mensaje'];

        echo "<script>
            Swal.fire({
                icon: '$icon',
                title: 'Resultado',
                html: `$mensaje`,
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#7066e0'
            });
        </script>";
    }
}

// =====================================================
// ✅ Ejecución automática del controlador
// =====================================================
$controlador = new ControladorFuncionario();

// Si viene una solicitud POST (insertar, eliminar, etc.)
$controlador->manejarSolicitud();

// Si vienes desde una vista y quieres listar los funcionarios
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controlador->mostrarFuncionarios();
}
?>
