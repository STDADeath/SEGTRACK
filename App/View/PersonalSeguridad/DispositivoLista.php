<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>
<?php require_once(__DIR__ . "/../../Core/conexion.php");?>

<?php
$conexion = new Conexion();
$conn = $conexion->getConexion();

// Construcción de filtros dinámicos
$filtros = [];
$params = [];

// FILTRO OBLIGATORIO: Solo mostrar dispositivos activos
$filtros[] = "d.Estado = :estado";
$params[':estado'] = 'Activo';

if (!empty($_GET['tipo'])) {
    $filtros[] = "d.TipoDispositivo = :tipo";
    $params[':tipo'] = $_GET['tipo'];
}
if (!empty($_GET['marca'])) {
    $filtros[] = "d.MarcaDispositivo LIKE :marca";
    $params[':marca'] = '%' . $_GET['marca'] . '%';
}
if (!empty($_GET['funcionario'])) {
    $filtros[] = "d.IdFuncionario = :funcionario";
    $params[':funcionario'] = $_GET['funcionario'];
}
if (!empty($_GET['visitante'])) {
    $filtros[] = "d.IdVisitante = :visitante";
    $params[':visitante'] = $_GET['visitante'];
}

// 🆕 FILTRO POR NÚMERO SERIAL
if (!empty($_GET['serial'])) {
    $filtros[] = "d.NumeroSerial LIKE :serial";
    $params[':serial'] = '%' . $_GET['serial'] . '%';
}

$where = "WHERE " . implode(" AND ", $filtros);

// Query CON NumeroSerial
$sql = "SELECT 
            d.*,
            f.NombreFuncionario,
            f.CorreoFuncionario,
            v.NombreVisitante
        FROM dispositivo d
        LEFT JOIN funcionario f ON d.IdFuncionario = f.IdFuncionario
        LEFT JOIN visitante v ON d.IdVisitante = v.IdVisitante
        $where 
        ORDER BY d.IdDispositivo DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-laptop me-2"></i>Dispositivos Registrados</h1>
        <a href="./Dispositivos.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Dispositivo
        </a>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
    <div class="card-header py-3 bg-light d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-filter mr-2"></i>Filtrar Dispositivos
        </h6>
        <a href="Dispositivolista.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-broom mr-1"></i>Limpiar filtros
        </a>
    </div>
        <div class="card-body">
            <form method="get">
                <div class="row align-items-end">

                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-laptop mr-1 text-primary"></i>Tipo
                        </label>
                        <select name="tipo" id="tipo" class="form-control">
                            <option value="">Todos</option>
                            <option value="Portatil"   <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Portatil')   ? 'selected' : '' ?>>💻 Portátil</option>
                            <option value="Tablet"     <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Tablet')     ? 'selected' : '' ?>>📱 Tablet</option>
                            <option value="Computador" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Computador') ? 'selected' : '' ?>>🖥️ Computador</option>
                            <option value="Otro"       <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Otro')       ? 'selected' : '' ?>>📦 Otro</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-tag mr-1 text-primary"></i>Marca
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" name="marca" id="marca" class="form-control"
                                value="<?= htmlspecialchars($_GET['marca'] ?? '') ?>"
                                placeholder="Buscar por marca">
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-barcode mr-1 text-primary"></i>Número Serial
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" name="serial" id="serial" class="form-control"
                                value="<?= htmlspecialchars($_GET['serial'] ?? '') ?>"
                                placeholder="Buscar por serial">
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label d-block invisible">.</label>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search mr-1"></i>Filtrar
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Dispositivos Activos</h6>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle text-center" id="TablaDispositivo">
                <thead class="table-dark">
                    <tr>
                        <th>QR</th>
                        <th>Tipo</th>
                        <th>Marca</th>
                        <th>Número Serial</th> <!-- 🆕 NUEVA COLUMNA -->
                        <th>Funcionario</th>
                        <th>Visitante</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && count($result) > 0) : ?>
                        <?php foreach ($result as $row) : ?>
                            <?php 
                            // Solo funcionarios tienen correo por ahora
                            $correoDisponible = $row['CorreoFuncionario'] ?? '';
                            $tieneCorreo = !empty($correoDisponible);
                            ?>
                            <tr id="fila-<?php echo $row['IdDispositivo']; ?>">
                                <td class="text-center">
                                    <?php if (!empty($row['QrDispositivo'])) : ?>
                                        <button type="button" class="btn btn-sm btn-outline-success mb-1" 
                                                onclick="verQRDispositivo('<?php echo htmlspecialchars($row['QrDispositivo']); ?>', <?php echo $row['IdDispositivo']; ?>)"
                                                title="Ver código QR">
                                            <i class="fas fa-qrcode me-1"></i> Ver
                                        </button>
                                        <!-- 🆕 BOTÓN ENVIAR QR -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-info <?php echo !$tieneCorreo ? 'disabled' : ''; ?>" 
                                                onclick="enviarQRPorCorreo(<?php echo $row['IdDispositivo']; ?>, '<?php echo htmlspecialchars($correoDisponible); ?>')"
                                                title="<?php echo $tieneCorreo ? 'Enviar QR por correo' : 'Solo funcionarios pueden recibir correo'; ?>"
                                                <?php echo !$tieneCorreo ? 'disabled' : ''; ?>>
                                            <i class="fas fa-envelope me-1"></i> Enviar
                                        </button>
                                    <?php else : ?>
                                        <span class="badge badge-warning">Sin QR</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $row['TipoDispositivo']; ?></td>
                                <td><?php echo $row['MarcaDispositivo']; ?></td>
                                
                                <!-- 🆕 MOSTRAR NÚMERO SERIAL -->
                                <td>
                                    <?php if (!empty($row['NumeroSerial'])) : ?>
                                        <?php echo $row['NumeroSerial']; ?>
                                    <?php else : ?>
                                        <span class="badge bg-info text-white">No tiene número serial</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td>
                                    <?php if (!empty($row['NombreFuncionario'])) : ?>
                                        <?php echo $row['NombreFuncionario']; ?>
                                    <?php else : ?>
                                        <span class="badge bg-info text-white">No aplica</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['NombreVisitante'])) : ?>
                                        <?php echo $row['NombreVisitante']; ?>
                                    <?php else : ?>
                                        <span class="badge bg-info text-white">No aplica</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick='cargarDatosEdicionDispositivo(<?php echo json_encode($row); ?>)'
                                            title="Editar dispositivo" data-toggle="modal" data-target="#modalEditarDispositivo">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-exclamation-circle fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No hay dispositivos activos registrados con los filtros seleccionados</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para visualizar QR del Dispositivo -->
<div class="modal fade" id="modalVerQRDispositivo" tabindex="-1" role="dialog" aria-labelledby="modalVerQRDispositivoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalVerQRDispositivoLabel">
                    <i class="fas fa-qrcode me-2"></i>Código QR - Dispositivo #<span id="qrDispositivoId"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="qrImagenDispositivo" src="" alt="Código QR Dispositivo" class="img-fluid" style="max-width: 300px; border: 2px solid #ddd; padding: 10px; border-radius: 5px;">
                <p class="text-muted mt-3">Escanea este código con tu dispositivo móvil</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <a id="btnDescargarQRDispositivo" href="#" class="btn btn-success" download>
                    <i class="fas fa-download me-1"></i> Descargar QR
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Dispositivo -->
<div class="modal fade" id="modalEditarDispositivo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Editar Dispositivo</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formEditarDispositivo">
                    <input type="hidden" id="editIdDispositivo" name="id">
                    <input type="hidden" id="editAccion" name="accion" value="actualizar">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo Dispositivo <span class="text-danger">*</span></label>
                            <select id="editTipoDispositivo" class="form-control" name="tipo" required>
                                <option value="">-- Seleccione un tipo --</option>
                                <option value="Portatil">Portátil</option>
                                <option value="Tablet">Tablet</option>
                                <option value="Computador">Computador</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Marca <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                <input type="text" id="editMarcaDispositivo" class="form-control" name="marca" required placeholder="Ej: HP, Dell, Lenovo">
                            </div>
                            <small class="text-muted">Puede modificar la marca del dispositivo</small>
                        </div>
                    </div>

                    <!-- 🆕 NÚMERO SERIAL EN MODAL EDITAR -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Número Serial</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                <input type="text" id="editNumeroSerial" class="form-control" name="serial" placeholder="Ej: SN123456789" maxlength="50">
                            </div>
                            <small class="text-muted">Campo opcional - Solo letras, números, guiones (-) y guiones bajos (_)</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Funcionario <small class="text-muted">(Solo lectura)</small></label>
                            <input type="text" id="editNombreFuncionario" class="form-control bg-light" readonly>
                            <input type="hidden" id="editIdFuncionario" name="id_funcionario">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Visitante <small class="text-muted">(Solo lectura)</small></label>
                            <input type="text" id="editNombreVisitante" class="form-control bg-light" readonly>
                            <input type="hidden" id="editIdVisitante" name="id_visitante">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" id="btnGuardarCambiosDispositivo">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>

<script>
// ============================================
// 🔥 ZONA DATATABLES - Activación de DataTable
// ============================================
$(document).ready(function() {
    $('#TablaDispositivo').DataTable({
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        },
        pageLength: 10,
        responsive: true,
        order: [[0, "desc"]]
    });

    // 🆕 VALIDACIÓN EN TIEMPO REAL DEL SERIAL EN MODAL EDITAR
    const editSerialInput = document.getElementById('editNumeroSerial');
    const editMarcaInput = document.getElementById('editMarcaDispositivo');
    
    if (editSerialInput) {
        editSerialInput.addEventListener('input', function(e) {
            let valor = e.target.value;
            // Remover caracteres no permitidos automáticamente
            valor = valor.replace(/[^a-zA-Z0-9\-_]/g, '');
            e.target.value = valor;
            
            // Validar longitud
            if (valor.length > 50) {
                e.target.value = valor.substring(0, 50);
                e.target.classList.add('is-invalid');
            } else if (valor.length > 0 && valor.length >= 3) {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
            } else if (valor.length > 0 && valor.length < 3) {
                e.target.classList.add('is-invalid');
                e.target.classList.remove('is-valid');
            } else {
                e.target.classList.remove('is-invalid', 'is-valid');
            }
        });
    }

    // 🆕 VALIDACIÓN EN TIEMPO REAL DE LA MARCA EN MODAL EDITAR
    if (editMarcaInput) {
        editMarcaInput.addEventListener('input', function(e) {
            const regexTexto = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s.,-]+$/;
            let valor = e.target.value;
            
            if (valor.length > 0) {
                if (regexTexto.test(valor)) {
                    e.target.classList.remove('is-invalid');
                    e.target.classList.add('is-valid');
                } else {
                    e.target.classList.add('is-invalid');
                    e.target.classList.remove('is-valid');
                }
            } else {
                e.target.classList.remove('is-invalid', 'is-valid');
            }
        });
    }
});

// ============================================
// Función para mostrar QR del dispositivo
// ============================================
function verQRDispositivo(rutaQR, idDispositivo) {
    var rutaCompleta = '/SEGTRACK/Public/' + rutaQR;
    
    console.log('Ruta QR completa:', rutaCompleta);
    
    $('#qrDispositivoId').text(idDispositivo);
    $('#qrImagenDispositivo').attr('src', rutaCompleta);
    $('#btnDescargarQRDispositivo').attr('href', rutaCompleta).attr('download', 'QR-Dispositivo-' + idDispositivo + '.png');
    
    $('#modalVerQRDispositivo').modal('show');
}

// ============================================
// 🆕 FUNCIÓN PARA ENVIAR QR POR CORREO
// ============================================
function enviarQRPorCorreo(idDispositivo, correoDestinatario) {
    console.log('📧 Enviando QR - ID:', idDispositivo, 'Correo:', correoDestinatario);
    
    // Validar que exista un correo
    if (!correoDestinatario || correoDestinatario.trim() === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Sin correo registrado',
            html: 'Este dispositivo no tiene un correo electrónico asociado.<br><small class="text-muted">Por ahora solo funcionarios pueden recibir correos.</small>',
            confirmButtonColor: '#3085d6'
        });
        return;
    }

    // Confirmar el envío
    Swal.fire({
        title: '📧 ¿Enviar código QR?',
        html: `Se enviará el código QR al correo:<br><br><strong class="text-primary">${correoDestinatario}</strong>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '<i class="fas fa-paper-plane"></i> Sí, enviar',
        cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Enviando correo...',
                html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br>Por favor espere',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });

            // Realizar petición AJAX al MISMO controlador de dispositivos
            $.ajax({
                url: '../../Controller/ControladorDispositivo.php',
                type: 'POST',
                data: {
                    accion: 'enviar_qr',
                    id_dispositivo: idDispositivo
                },
                dataType: 'json',
                timeout: 30000,
                success: function(response) {
                    console.log('✓ Respuesta:', response);
                    
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Correo enviado!',
                            html: `<p>${response.message}</p>`,
                            timer: 4000,
                            timerProgressBar: true,
                            confirmButtonColor: '#1cc88a'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al enviar',
                            text: response.message,
                            confirmButtonColor: '#e74a3b'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error AJAX:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    
                    let errorMsg = 'No se pudo conectar con el servidor.';
                    
                    if (xhr.status === 404) {
                        errorMsg = 'El archivo ControladorEnviarQR.php no existe. Verifica la ruta.';
                    } else if (status === 'timeout') {
                        errorMsg = 'La solicitud tardó demasiado tiempo.';
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: errorMsg,
                        confirmButtonColor: '#e74a3b',
                        footer: '<small>Revisa la consola del navegador (F12) para más detalles</small>'
                    });
                }
            });
        }
    });
}

// ============================================
// Cargar datos en el modal de edición
// ============================================
function cargarDatosEdicionDispositivo(row) {
    console.log('Cargando datos para editar:', row);
    
    $('#editIdDispositivo').val(row.IdDispositivo);
    $('#editTipoDispositivo').val(row.TipoDispositivo);
    $('#editMarcaDispositivo').val(row.MarcaDispositivo);
    $('#editNumeroSerial').val(row.NumeroSerial || ''); // 🆕 CARGAR SERIAL
    
    // IDs ocultos
    $('#editIdFuncionario').val(row.IdFuncionario || '');
    $('#editIdVisitante').val(row.IdVisitante || '');
    
    // Mostrar nombres en campos de texto
    $('#editNombreFuncionario').val(row.NombreFuncionario || '-');
    $('#editNombreVisitante').val(row.NombreVisitante || '-');
    
    $('#modalEditarDispositivo').modal('show');
}

// ============================================
// Botón guardar cambios
// ============================================
$(document).ready(function() {
    $('#btnGuardarCambiosDispositivo').click(function() {
        // 🆕 VALIDACIONES MEJORADAS
        const tipo = $('#editTipoDispositivo').val();
        const marca = $('#editMarcaDispositivo').val().trim();
        const serial = $('#editNumeroSerial').val().trim();
        
        // Expresiones regulares
        const regexTexto = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s.,-]+$/;
        const regexSerial = /^[a-zA-Z0-9\-_]+$/;

        // Validar tipo
        if (!tipo) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo incompleto',
                text: 'Debe seleccionar un tipo de dispositivo',
                confirmButtonColor: '#f6c23e'
            });
            return;
        }

        // Validar marca
        if (!marca) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo incompleto',
                text: 'Debe ingresar la marca del dispositivo',
                confirmButtonColor: '#f6c23e'
            });
            $('#editMarcaDispositivo').focus();
            return;
        }

        if (!regexTexto.test(marca)) {
            Swal.fire({
                icon: 'error',
                title: 'Caracteres inválidos',
                text: 'La marca contiene caracteres inválidos. Solo se permiten letras, números y .-,',
                confirmButtonColor: '#e74a3b'
            });
            $('#editMarcaDispositivo').focus();
            return;
        }

        // 🆕 VALIDAR NÚMERO SERIAL
        if (serial) {
            if (!regexSerial.test(serial)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Serial inválido',
                    html: 'El número serial solo puede contener:<br>' +
                        '• Letras (A-Z, a-z)<br>' +
                        '• Números (0-9)<br>' +
                        '• Guiones (-)<br>' +
                        '• Guiones bajos (_)',
                    confirmButtonColor: '#e74a3b'
                });
                $('#editNumeroSerial').focus();
                return;
            }

            if (serial.length < 3) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Serial muy corto',
                    text: 'El número serial debe tener al menos 3 caracteres',
                    confirmButtonColor: '#f6c23e'
                });
                $('#editNumeroSerial').focus();
                return;
            }

            if (serial.length > 50) {
                Swal.fire({
                    icon: 'error',
                    title: 'Serial muy largo',
                    text: 'El número serial no puede exceder 50 caracteres',
                    confirmButtonColor: '#e74a3b'
                });
                $('#editNumeroSerial').focus();
                return;
            }
        }

        var formData = {
            accion: 'actualizar',
            id: $('#editIdDispositivo').val(),
            tipo: tipo,
            marca: marca,
            serial: serial,
            id_funcionario: $('#editIdFuncionario').val(),
            id_visitante: $('#editIdVisitante').val()
        };

        console.log('Enviando datos:', formData);

        $('#modalEditarDispositivo').modal('hide');

        Swal.fire({
            title: 'Guardando cambios...',
            html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br>Por favor espere',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });

        $.ajax({
            url: '../../Controller/ControladorDispositivo.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta:', response);
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        html: response.message || 'Dispositivo actualizado correctamente',
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: true,
                        confirmButtonColor: '#1cc88a'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No se pudo actualizar',
                        html: response.message.replace(/\n/g, '<br>') || 'Error al actualizar el dispositivo',
                        confirmButtonColor: '#f6c23e',
                        footer: '<small class="text-muted">Revise la información e intente nuevamente</small>'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.log('Error:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor',
                    confirmButtonColor: '#e74a3b',
                    footer: '<small>Si el problema persiste, contacte al administrador</small>'
                });
            }
        });
    });
});
</script>