<?php
// File: ../../Controller/ControladorFuncionarios.php

// -------------------------------------------------------------
// Controlador completo con integración de QR:
// - Al registrar: genera QR y actualiza BD (tu lógica original)
// - Al actualizar: borra QR anterior (si existe), genera nuevo y actualiza BD
// - Incluye acción opcional 'actualizar_qr' para regenerar QR individualmente
// -------------------------------------------------------------

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Logs de errores en Debug_Func
ini_set('error_log', __DIR__ . '/Debug_Func/error_log.txt');

ob_start();

// Cabeceras JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Ruta del log de debug (texto plano)
$ruta_debug_log = __DIR__ . '/Debug_Func/debug_log.txt';

// Asegurar que existe carpeta Debug_Func
if (!file_exists(__DIR__ . '/Debug_Func')) {
    mkdir(__DIR__ . '/Debug_Func', 0777, true);
}

// Registrar inicio de petición
file_put_contents($ruta_debug_log, "\n" . date('Y-m-d H:i:s') . " === INICIO DE PETICIÓN ===\n", FILE_APPEND);

try {
    // Guardar POST recibido
    file_put_contents($ruta_debug_log, "POST recibido:\n" . json_encode($_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

    // =======================================================
    // CARGAR CONEXIÓN
    // =======================================================
    $ruta_conexion = __DIR__ . '/../Core/conexion.php';
    if (!file_exists($ruta_conexion)) {
        throw new Exception("Archivo de conexión no encontrado: $ruta_conexion");
    }
    require_once $ruta_conexion;
    file_put_contents($ruta_debug_log, "Conexión cargada\n", FILE_APPEND);

    $conexionObj = new Conexion();
    $conexion = $conexionObj->getConexion();

    if (!$conexion || !($conexion instanceof PDO)) {
        throw new Exception("La conexión PDO no es válida o nula");
    }
    file_put_contents($ruta_debug_log, "Conexión verificada como instancia de PDO\n", FILE_APPEND);

    // =======================================================
    // CARGAR LIBRERÍA QR Y MODELO
    // =======================================================
    $ruta_qrlib = __DIR__ . '/../Libs/phpqrcode/qrlib.php';
    if (!file_exists($ruta_qrlib)) {
        throw new Exception("Librería phpqrcode no encontrada: $ruta_qrlib");
    }
    require_once $ruta_qrlib;
    file_put_contents($ruta_debug_log, "Librería QR cargada\n", FILE_APPEND);

    $ruta_modelo = __DIR__ . "/../Model/ModeloFuncionarios.php";
    if (!file_exists($ruta_modelo)) {
        throw new Exception("Modelo no encontrado en: " . $ruta_modelo);
    }
    require_once $ruta_modelo;
    file_put_contents($ruta_debug_log, "Modelo cargado: $ruta_modelo\n", FILE_APPEND);

    // =======================================================
    // CLASE CONTROLADOR
    // =======================================================
    class ControladorFuncionario {
        private $modelo;
        private $log;

        public function __construct(PDO $conexion) {
            $this->modelo = new ModeloFuncionario($conexion);
            $this->log = __DIR__ . '/Debug_Func/debug_log.txt';
        }

        // Escribir en log propio de la clase
        private function log($msg) {
            file_put_contents($this->log, date('Y-m-d H:i:s') . " - $msg\n", FILE_APPEND);
        }

        // Verifica campo vacío
        private function campoVacio($campo): bool {
            return !isset($campo) || $campo === '' || trim($campo) === '';
        }

        // -------------------------------------------------------
        // Generar QR (usa la librería phpqrcode) y devuelve la ruta relativa para BD
        // ✅ SOLUCIÓN: Genera QR en memoria y guarda solo donde debe
        // -------------------------------------------------------
        private function generarQR(int $idFuncionario, string $nombre, string $documento): ?string {
            try {
                $this->log("Generando QR para funcionario ID: $idFuncionario");

                // Ruta base del proyecto (sube 2 niveles desde Controller)
                $rutaBase = dirname(dirname(__DIR__));
                $rutaFisica = $rutaBase . '/Public/qr/QR_Func';

                $this->log("Ruta base del proyecto: $rutaBase");
                $this->log("Ruta física completa QR: $rutaFisica");

                // Crear carpeta si no existe
                if (!is_dir($rutaFisica)) {
                    if (!mkdir($rutaFisica, 0777, true)) {
                        throw new Exception("No se pudo crear la carpeta QR: $rutaFisica");
                    }
                    chmod($rutaFisica, 0777);
                    $this->log("Carpeta QR creada con permisos 777: $rutaFisica");
                } else {
                    $this->log("Carpeta QR ya existe: $rutaFisica");
                }

                // Verificar permisos de escritura
                if (!is_writable($rutaFisica)) {
                    throw new Exception("La carpeta QR no es escribible: $rutaFisica");
                }

                // Nombre único del archivo
                $nombreArchivo = "QR-FUNC-" . $idFuncionario . "-" . uniqid() . ".png";
                $rutaCompletaFisica = $rutaFisica . DIRECTORY_SEPARATOR . $nombreArchivo;

                // Contenido del QR
                $contenidoQR = "ID: $idFuncionario\nNombre: $nombre\nDocumento: $documento";

                $this->log("Intentando crear QR en ruta absoluta: $rutaCompletaFisica");

                // ⬇️ SOLUCIÓN: Generar QR en memoria y luego guardar manualmente ⬇️
                ob_start();
                QRcode::png($contenidoQR, false, QR_ECLEVEL_H, 10, 2);
                $imageData = ob_get_contents();
                ob_end_clean();
                
                // Guardar el contenido SOLO en la ubicación deseada
                file_put_contents($rutaCompletaFisica, $imageData);
                $this->log("QR guardado manualmente en: $rutaCompletaFisica");
                // ⬆️ FIN DE LA SOLUCIÓN ⬆️

                // Verificar creación del archivo
                if (!file_exists($rutaCompletaFisica)) {
                    throw new Exception("El archivo QR no se creó en: $rutaCompletaFisica");
                }

                $this->log("✓ QR generado exitosamente en: $rutaCompletaFisica");
                $this->log("✓ Tamaño del archivo: " . filesize($rutaCompletaFisica) . " bytes");

                // Ruta relativa que se guardará en BD (coincide con tus usos anteriores)
                $rutaRelativa = "qr/QR_Func/" . $nombreArchivo;
                $this->log("Ruta relativa para BD: $rutaRelativa");

                return $rutaRelativa;

            } catch (Exception $e) {
                $this->log("ERROR al generar QR: " . $e->getMessage());
                return null;
            }
        }

        // -------------------------------------------------------
        // Eliminar archivo QR anterior (si existe). Recibe ruta relativa guardada en BD.
        // -------------------------------------------------------
        private function eliminarQRAnterior(?string $rutaRelativa): void {
            if (empty($rutaRelativa)) {
                $this->log("No hay ruta QR anterior para eliminar.");
                return;
            }

            // Construir ruta absoluta al archivo en Public/
            $rutaBase = dirname(dirname(__DIR__));
            $rutaFisica = $rutaBase . '/Public/' . ltrim($rutaRelativa, '/');

            if (file_exists($rutaFisica) && is_file($rutaFisica)) {
                try {
                    unlink($rutaFisica);
                    $this->log("QR anterior eliminado: $rutaFisica");
                } catch (Exception $ex) {
                    $this->log("No se pudo eliminar QR anterior ($rutaFisica): " . $ex->getMessage());
                }
            } else {
                $this->log("QR anterior no encontrado o ya eliminado: $rutaFisica");
            }
        }

        // -------------------------------------------------------
        // Registrar funcionario (tu lógica original + generar QR)
        // -------------------------------------------------------
        public function registrarFuncionario(array $datos): array {
            $this->log("registrarFuncionario llamado");

            $cargo = trim($datos['CargoFuncionario'] ?? '');
            $nombre = trim($datos['NombreFuncionario'] ?? '');
            $correo = trim($datos['CorreoFuncionario'] ?? '');
            $sede = (int)($datos['IdSede'] ?? 0);
            $telefono = (int)($datos['TelefonoFuncionario'] ?? 0);
            $documento = (int)($datos['DocumentoFuncionario'] ?? 0);

            if ($this->campoVacio($cargo) || $cargo == 0) return ['success' => false, 'message' => 'Falta el Cargo o es inválido.'];
            if ($this->campoVacio($nombre)) return ['success' => false, 'message' => 'Falta el Nombre.'];
            if ($sede <= 0) return ['success' => false, 'message' => 'Falta la Sede o es inválida.'];
            if ($documento <= 0) return ['success' => false, 'message' => 'Falta el Documento o es inválido.'];

            try {
                $this->log("Llamando a RegistrarFuncionario en el modelo");
                $resultado = $this->modelo->RegistrarFuncionario($cargo, $nombre, $sede, $telefono, $documento, $correo);
                $this->log("Resultado del modelo (BD): " . json_encode($resultado, JSON_UNESCAPED_UNICODE));

                if ($resultado['success']) {
                    $idFuncionario = $resultado['id'];
                    $this->log("Registro exitoso, ID: $idFuncionario. Generando QR...");

                    $rutaQR = $this->generarQR($idFuncionario, $nombre, (string)$documento);

                    if ($rutaQR) {
                        $this->modelo->ActualizarQrFuncionario($idFuncionario, $rutaQR);
                        $this->log("QR actualizado en BD con ruta: $rutaQR");
                    } else {
                        $this->log("ADVERTENCIA: No se pudo generar el QR, continuando con el registro.");
                    }

                    return [
                        "success" => true,
                        "message" => "Funcionario registrado correctamente con ID: " . $idFuncionario .
                                    ($rutaQR ? ". QR generado y guardado en Public/qr/QR_Func/" : ". ADVERTENCIA: No se pudo generar el QR."),
                        "data" => ["IdFuncionario" => $idFuncionario, "QrCodigoFuncionario" => $rutaQR]
                    ];
                } else {
                    $errorMsg = $resultado['error'] ?? 'Error desconocido al registrar en la Base de Datos.';
                    $this->log("ERROR en BD: $errorMsg");
                    return ['success' => false, 'message' => 'Error en BD: ' . $errorMsg];
                }
            } catch (Exception $e) {
                $this->log("EXCEPCIÓN: " . $e->getMessage());
                return ['success' => false, 'message' => 'Error del Controlador: ' . $e->getMessage()];
            }
        }

        // -------------------------------------------------------
        // Actualizar funcionario (AHORA: borra QR viejo, genera nuevo y actualiza BD)
        // -------------------------------------------------------
        public function actualizarFuncionario(int $id, array $datos): array {
            $this->log("actualizarFuncionario llamado para ID: $id");

            if ($id <= 0) {
                return ['success' => false, 'message' => 'ID de funcionario no válido'];
            }

            try {
                // 1) Obtener ruta QR anterior (si existe)
                $rutaQRAntigua = $this->modelo->obtenerQR($id);
                $this->log("QR antiguo obtenido: " . ($rutaQRAntigua ?? 'NULL'));

                // 2) Actualizar datos de funcionario en BD (nombre, cargo, sede, etc.)
                $resultado = $this->modelo->actualizar($id, $datos);
                $this->log("Resultado actualización (modelo): " . json_encode($resultado, JSON_UNESCAPED_UNICODE));

                if (!($resultado['success'] ?? false)) {
                    // Si la actualización falló, retornar el error
                    $this->log("Error al actualizar datos, no se tocará QR.");
                    return [
                        'success' => false,
                        'message' => $resultado['error'] ?? 'Error al actualizar los datos del funcionario'
                    ];
                }

                // 3) Eliminar QR anterior del disco (si existía)
                if (!empty($rutaQRAntigua)) {
                    $this->eliminarQRAnterior($rutaQRAntigua);
                } else {
                    $this->log("No existía QR anterior o estaba vacío, se generará uno nuevo de todas formas.");
                }

                // 4) Generar nuevo QR con los datos actualizados
                //    Asegúrate de que $datos contenga NombreFuncionario y DocumentoFuncionario
                $nombreParaQR = $datos['NombreFuncionario'] ?? '';
                $documentoParaQR = (string)($datos['DocumentoFuncionario'] ?? '');

                $rutaQRNueva = $this->generarQR($id, $nombreParaQR, $documentoParaQR);

                if ($rutaQRNueva) {
                    // 5) Guardar nueva ruta QR en BD
                    $this->modelo->ActualizarQrFuncionario($id, $rutaQRNueva);
                    $this->log("QR nuevo guardado en BD: $rutaQRNueva");
                } else {
                    $this->log("Error: No se pudo generar el QR nuevo después de la actualización.");
                }

                return [
                    'success' => true,
                    'message' => 'Funcionario actualizado correctamente y QR regenerado',
                    'ruta_qr' => $rutaQRNueva ?? null,
                    'rows_affected' => $resultado['rows'] ?? 0
                ];

            } catch (Exception $e) {
                $this->log("EXCEPCIÓN en actualización: " . $e->getMessage());
                return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        // -------------------------------------------------------
        // Cambiar estado del funcionario (tu lógica original)
        // -------------------------------------------------------
        public function cambiarEstado(int $id, string $nuevoEstado): array {
            $this->log("Cambiando estado del funcionario ID: $id a: $nuevoEstado");

            if ($id <= 0) {
                return ['success' => false, 'message' => 'ID de funcionario inválido'];
            }

            if ($nuevoEstado !== 'Activo' && $nuevoEstado !== 'Inactivo') {
                return ['success' => false, 'message' => 'Estado inválido. Solo se permite "Activo" o "Inactivo"'];
            }

            try {
                $resultado = $this->modelo->cambiarEstado($id, $nuevoEstado);
                $this->log("Resultado cambio de estado: " . json_encode($resultado, JSON_UNESCAPED_UNICODE));

                if ($resultado['success']) {
                    return [
                        'success' => true,
                        'message' => "Estado cambiado a '$nuevoEstado' correctamente",
                        'nuevo_estado' => $nuevoEstado
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => $resultado['error'] ?? 'Error al cambiar el estado'
                    ];
                }

            } catch (Exception $e) {
                $this->log("EXCEPCIÓN al cambiar estado: " . $e->getMessage());
                return [
                    'success' => false,
                    'message' => 'Error del servidor: ' . $e->getMessage()
                ];
            }
        }

        // -------------------------------------------------------
        // Acción extra: regenerar QR (solo QR) — opcional
        // -------------------------------------------------------
        public function actualizarQrSolo(int $id): array {
            $this->log("actualizarQrSolo llamado para ID: $id");
            try {
                $datosFuncionario = $this->modelo->obtenerPorId($id);
                if (!$datosFuncionario) {
                    return ['success' => false, 'message' => 'Funcionario no encontrado'];
                }

                // Eliminar QR anterior
                $rutaQRAntigua = $datosFuncionario['QrCodigoFuncionario'] ?? null;
                if ($rutaQRAntigua) {
                    $this->eliminarQRAnterior($rutaQRAntigua);
                }

                // Generar nuevo QR con datos actuales
                $rutaQRNueva = $this->generarQR($id, $datosFuncionario['NombreFuncionario'] ?? '', (string)($datosFuncionario['DocumentoFuncionario'] ?? ''));

                if ($rutaQRNueva) {
                    $this->modelo->ActualizarQrFuncionario($id, $rutaQRNueva);
                    return ['success' => true, 'ruta_qr' => $rutaQRNueva];
                } else {
                    return ['success' => false, 'message' => 'No se pudo generar el QR'];
                }

            } catch (Exception $e) {
                $this->log("EXCEPCIÓN actualizarQrSolo: " . $e->getMessage());
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }
    } // end class ControladorFuncionario

    // =======================================================
    // PROCESAR ACCIONES POST (tu flujo original, ampliado)
    // =======================================================
    $controlador = new ControladorFuncionario($conexion);
    $accion = $_POST['accion'] ?? 'registrar';

    file_put_contents($ruta_debug_log, "Acción a ejecutar: $accion\n", FILE_APPEND);

    if ($accion === 'registrar') {
        $resultado = $controlador->registrarFuncionario($_POST);

    } elseif ($accion === 'actualizar') {
        // Actualizar datos + regenerar QR (punto clave solicitado)
        $id = (int)($_POST['id'] ?? 0);

        if ($id > 0) {
            $datos = [
                'CargoFuncionario' => $_POST['cargo'] ?? null,
                'NombreFuncionario' => $_POST['nombre'] ?? null,
                'IdSede' => $_POST['sede'] ?? null,
                'TelefonoFuncionario' => $_POST['telefono'] ?? null,
                'DocumentoFuncionario' => $_POST['documento'] ?? null,
                'CorreoFuncionario' => $_POST['correo'] ?? null
            ];

            $resultado = $controlador->actualizarFuncionario($id, $datos);
        } else {
            $resultado = ['success' => false, 'message' => 'ID de funcionario no válido para actualizar'];
        }

    } elseif ($accion === 'cambiar_estado') {
        $id = (int)($_POST['id'] ?? 0);
        $nuevoEstado = trim($_POST['estado'] ?? '');

        if ($id > 0 && !empty($nuevoEstado)) {
            $resultado = $controlador->cambiarEstado($id, $nuevoEstado);
        } else {
            $resultado = ['success' => false, 'message' => 'Faltan datos requeridos (ID o Estado)'];
        }

    } elseif ($accion === 'actualizar_qr') {
        // Acción opcional: regenerar SOLO el QR cuando el frontend lo pida.
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $resultado = $controlador->actualizarQrSolo($id);
        } else {
            $resultado = ['success' => false, 'message' => 'ID inválido para actualizar QR'];
        }

    } else {
        $resultado = ['success' => false, 'message' => 'Acción no reconocida'];
    }

    // Registrar resultado final en log
    file_put_contents($ruta_debug_log, "Respuesta final: " . json_encode($resultado, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

    // Limpiar buffer y devolver JSON
    ob_end_clean();
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Manejo de excepciones centralizado (manteniendo tu estilo)
    if (ob_get_level() > 0) {
        ob_end_clean();
    }

    $error = $e->getMessage();
    file_put_contents($ruta_debug_log, "ERROR FINAL: $error\n", FILE_APPEND);

    echo json_encode([
        'success' => false,
        'message' => 'Error fatal del servidor: ' . $error,
        'error_details' => $error
    ], JSON_UNESCAPED_UNICODE);
}

exit;