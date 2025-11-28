<?php require_once __DIR__ . '/../layouts/parte_superior_supervisor.php'; ?>
<?php require_once(__DIR__ . "/../../Core/conexion.php");?>

<?php
$conexionObj = new Conexion();
$conn = $conexionObj->getConexion();

// Construcción de filtros dinámicos
$filtros = [];
$params = [];

if (!empty($_GET['tipo'])) {
    $filtros[] = "TipoDispositivo = :tipo";
    $params[':tipo'] = $_GET['tipo'];
}
if (!empty($_GET['marca'])) {
    $filtros[] = "MarcaDispositivo LIKE :marca";
    $params[':marca'] = '%' . $_GET['marca'] . '%';
}
if (!empty($_GET['funcionario'])) {
    $filtros[] = "IdFuncionario = :funcionario";
    $params[':funcionario'] = $_GET['funcionario'];
}
if (!empty($_GET['visitante'])) {
    $filtros[] = "IdVisitante = :visitante";
    $params[':visitante'] = $_GET['visitante'];
}
if (!empty($_GET['estado'])) {
    $filtros[] = "Estado = :estado";
    $params[':estado'] = $_GET['estado'];
}

$where = "";
if (count($filtros) > 0) {
    $where = "WHERE " . implode(" AND ", $filtros);
}

$sql = "SELECT * FROM dispositivo $where ORDER BY 
        CASE WHEN Estado = 'Activo' THEN 1 ELSE 2 END, 
        IdDispositivo DESC";
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
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Filtrar Dispositivos</h6>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-2">
                    <label for="tipo" class="form-label">Tipo de Dispositivo</label>
                    <select name="tipo" id="tipo" class="form-select">
                        <option value="">Todos</option>
                        <option value="Portatil" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Portatil') ? 'selected' : '' ?>>Portátil</option>
                        <option value="Tablet" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Tablet') ? 'selected' : '' ?>>Tablet</option>
                        <option value="Computador" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Computador') ? 'selected' : '' ?>>Computador</option>
                        <option value="Otro" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Otro') ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="marca" class="form-label">Marca</label>
                    <input type="text" name="marca" id="marca" class="form-control" value="<?= $_GET['marca'] ?? '' ?>" placeholder="Buscar por marca">
                </div>
                <div class="col-md-2">
                    <label for="funcionario" class="form-label">ID Funcionario</label>
                    <input type="text" name="funcionario" id="funcionario" class="form-control" value="<?= $_GET['funcionario'] ?? '' ?>" placeholder="ID">
                </div>
                <div class="col-md-2">
                    <label for="visitante" class="form-label">ID Visitante</label>
                    <input type="text" name="visitante" id="visitante" class="form-control" value="<?= $_GET['visitante'] ?? '' ?>" placeholder="ID">
                </div>
                <div class="col-md-2">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="Activo" <?= (isset($_GET['estado']) && $_GET['estado'] == 'Activo') ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= (isset($_GET['estado']) && $_GET['estado'] == 'Inactivo') ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i> Filtrar</button>
                    <a href="DispositivoSupervisor.php" class="btn btn-secondary"><i class="fas fa-broom me-1"></i> Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Dispositivos</h6>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>QR</th>
                        <th>Tipo</th>
                        <th>Marca</th>
                        <th>ID Funcionario</th>
                        <th>ID Visitante</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && count($result) > 0) : ?>
                        <?php foreach ($result as $row) : ?>
                            <tr id="fila-<?php echo $row['IdDispositivo']; ?>" class="<?php echo $row['Estado'] === 'Inactivo' ? 'fila-inactiva' : ''; ?>">
                                <td><?php echo $row['IdDispositivo']; ?></td>
                                <td class="text-center">
                                    <?php if (!empty($row['QrDispositivo'])) : ?>
                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                onclick="verQRDispositivo('<?php echo htmlspecialchars($row['QrDispositivo']); ?>', <?php echo $row['IdDispositivo']; ?>)"
                                                title="Ver código QR">
                                            <i class="fas fa-qrcode me-1"></i> Ver QR
                                        </button>
                                    <?php else : ?>
                                        <span class="badge badge-warning">Sin QR</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $row['TipoDispositivo']; ?></td>
                                <td><?php echo $row['MarcaDispositivo']; ?></td>
                                <td><?php echo $row['IdFuncionario'] ?? '-'; ?></td>
                                <td><?php echo $row['IdVisitante'] ?? '-'; ?></td>
                                <td class="text-center">
                                    <?php if ($row['Estado'] === 'Activo') : ?>
                                        <span class="badge badge-success badge-estado">Activo</span>
                                    <?php else : ?>
                                        <span class="badge badge-secondary badge-estado">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick='cargarDatosEdicionDispositivo(<?php echo json_encode($row); ?>)'
                                            title="Editar dispositivo" data-toggle="modal" data-target="#modalEditarDispositivo">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm <?php echo $row['Estado'] === 'Activo' ? 'btn-outline-warning' : 'btn-outline-success'; ?>" 
                                                onclick="confirmarCambioEstadoDispositivo(<?php echo $row['IdDispositivo']; ?>, '<?php echo $row['Estado']; ?>')"
                                                title="<?php echo $row['Estado'] === 'Activo' ? 'Desactivar' : 'Activar'; ?> dispositivo">
                                            <i class="fas <?php echo $row['Estado'] === 'Activo' ? 'fa-lock' : 'fa-lock-open'; ?>"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-exclamation-circle fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No hay dispositivos registrados con los filtros seleccionados</p>
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
                            <label class="form-label">Tipo Dispositivo</label>
                            <select id="editTipoDispositivo" class="form-control" name="tipo" required>
                                <option value="">-- Seleccione un tipo --</option>
                                <option value="Portatil">Portátil</option>
                                <option value="Tablet">Tablet</option>
                                <option value="Computador">Computador</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Marca</label>
                            <input type="text" id="editMarcaDispositivo" class="form-control" name="marca" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ID Funcionario</label>
                            <input type="number" id="editIdFuncionario" class="form-control" name="id_funcionario" min="1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ID Visitante</label>
                            <input type="number" id="editIdVisitante" class="form-control" name="id_visitante" min="1">
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

<!-- Modal Confirmar Cambio de Estado -->
<div class="modal fade" id="modalCambiarEstadoDispositivo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" id="headerCambioEstadoDispositivo">
                <h5 class="modal-title" id="tituloCambioEstadoDispositivo"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div class="toggle-container">
                    <label class="btn-lock" id="toggleEstadoVisualDispositivo">
                        <svg width="36" height="40" viewBox="0 0 36 40">
                            <path class="lockb" d="M27 27C27 34.1797 21.1797 40 14 40C6.8203 40 1 34.1797 1 27C1 19.8203 6.8203 14 14 14C21.1797 14 27 19.8203 27 27ZM15.6298 26.5191C16.4544 25.9845 17 25.056 17 24C17 22.3431 15.6569 21 14 21C12.3431 21 11 22.3431 11 24C11 25.056 11.5456 25.9845 12.3702 26.5191L11 32H17L15.6298 26.5191Z"></path>
                            <path class="lock" d="M6 21V10C6 5.58172 9.58172 2 14 2V2C18.4183 2 22 5.58172 22 10V21"></path>
                            <path class="bling" d="M29 20L31 22"></path>
                            <path class="bling" d="M31.5 15H34.5"></path>
                            <path class="bling" d="M29 10L31 8"></path>
                        </svg>
                    </label>
                </div>
                <p id="mensajeCambioEstadoDispositivo" class="mb-3 mt-2" style="font-size: 1.1rem;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarCambioEstadoDispositivo">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts de jQuery y Bootstrap (ANTES de cerrar el layout) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Esperar a que jQuery esté completamente cargado
$(document).ready(function() {
    console.log('jQuery cargado - DispositivoSupervisor');
    
    let dispositivoIdAEditar = null;
    let dispositivoACambiarEstado = null;
    let estadoActualDispositivo = null;

    // Función para mostrar QR del dispositivo
    window.verQRDispositivo = function(rutaQR, idDispositivo) {
        var rutaCompleta = '/SEGTRACK/Public/' + rutaQR;
        
        console.log('Ruta QR completa:', rutaCompleta);
        
        $('#qrDispositivoId').text(idDispositivo);
        $('#qrImagenDispositivo').attr('src', rutaCompleta);
        $('#btnDescargarQRDispositivo').attr('href', rutaCompleta).attr('download', 'QR-Dispositivo-' + idDispositivo + '.png');
        
        $('#modalVerQRDispositivo').modal('show');
    };

    // Cargar datos en el modal de edición
    window.cargarDatosEdicionDispositivo = function(row) {
        console.log('Cargando datos para editar:', row);
        
        dispositivoIdAEditar = row.IdDispositivo;
        
        $('#editIdDispositivo').val(row.IdDispositivo);
        $('#editTipoDispositivo').val(row.TipoDispositivo);
        $('#editMarcaDispositivo').val(row.MarcaDispositivo);
        $('#editIdFuncionario').val(row.IdFuncionario || '');
        $('#editIdVisitante').val(row.IdVisitante || '');
        
        $('#modalEditarDispositivo').modal('show');
    };

    // Confirmar cambio de estado
    window.confirmarCambioEstadoDispositivo = function(id, estado) {
        console.log('confirmarCambioEstado llamado:', {id, estado});
        
        dispositivoACambiarEstado = id;
        estadoActualDispositivo = estado;
        
        const nuevoEstado = estado === 'Activo' ? 'Inactivo' : 'Activo';
        const accion = nuevoEstado === 'Activo' ? 'activar' : 'desactivar';
        const colorHeader = nuevoEstado === 'Activo' ? 'bg-success' : 'bg-warning';
        
        // Configurar el header del modal
        $('#headerCambioEstadoDispositivo').removeClass('bg-success bg-warning').addClass(colorHeader + ' text-white');
        $('#tituloCambioEstadoDispositivo').html('<i class="fas fa-' + (nuevoEstado === 'Activo' ? 'lock-open' : 'lock') + ' me-2"></i>' + accion.charAt(0).toUpperCase() + accion.slice(1) + ' Dispositivo');
        $('#mensajeCambioEstadoDispositivo').html('¿Está seguro que desea <strong>' + accion + '</strong> este dispositivo?');
        
        // Mostrar modal
        $('#modalCambiarEstadoDispositivo').modal('show');
        
        // Configurar el toggle visual después de mostrar el modal
        setTimeout(function() {
            const toggleLabel = document.getElementById('toggleEstadoVisualDispositivo');
            if (toggleLabel) {
                if (nuevoEstado === 'Activo') {
                    toggleLabel.classList.add('activo');
                } else {
                    toggleLabel.classList.remove('activo');
                }
            }
        }, 100);
    };

    // Botón confirmar cambio de estado
    $('#btnConfirmarCambioEstadoDispositivo').on('click', function() {
        console.log('Confirmar cambio de estado clickeado');
        
        if (!dispositivoACambiarEstado) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se ha seleccionado ningún dispositivo'
            });
            return;
        }
        
        const nuevoEstado = estadoActualDispositivo === 'Activo' ? 'Inactivo' : 'Activo';
        
        console.log('Enviando petición AJAX:', {
            id: dispositivoACambiarEstado,
            estado: nuevoEstado
        });
        
        // Cerrar el modal personalizado
        $('#modalCambiarEstadoDispositivo').modal('hide');
        
        // Mostrar loading de SweetAlert2
        Swal.fire({
            title: 'Procesando...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: '../../Controller/ControladorDispositivo.php',
            type: 'POST',
            data: {
                accion: 'cambiar_estado',
                id: dispositivoACambiarEstado,
                estado: nuevoEstado
            },
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta recibida:', response);
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message || 'Dispositivo ' + (nuevoEstado === 'Activo' ? 'activado' : 'desactivado') + ' correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'No se pudo cambiar el estado del dispositivo'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', {
                    xhr: xhr,
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo cambiar el estado del dispositivo'
                });
            }
        });
    });

    // Botón guardar cambios de edición
    $('#btnGuardarCambiosDispositivo').on('click', function() {
        var formData = {
            accion: 'actualizar',
            id: $('#editIdDispositivo').val(),
            tipo: $('#editTipoDispositivo').val(),
            marca: $('#editMarcaDispositivo').val(),
            id_funcionario: $('#editIdFuncionario').val() || null,
            id_visitante: $('#editIdVisitante').val() || null
        };

        console.log('Enviando datos:', formData);

        // Validar campos obligatorios
        if (!formData.tipo || !formData.marca) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Por favor, complete todos los campos obligatorios (Tipo y Marca)'
            });
            return;
        }

        // Cerrar modal de edición
        $('#modalEditarDispositivo').modal('hide');
        
        // Mostrar loading de SweetAlert2
        Swal.fire({
            title: 'Guardando...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
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
                        text: 'Dispositivo actualizado correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'No se pudo actualizar el dispositivo'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.log('Error:', xhr.responseText);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor'
                });
            }
        });
    });

    console.log('Todos los event listeners configurados correctamente');
});
</script>

<?php require_once __DIR__ . '/../layouts/parte_inferior_supervisor.php'; ?>