<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>
<?php require_once(__DIR__ . "/../../Core/conexion.php");?>

<?php
$conexion = new Conexion();
$conn = $conexion->getConexion();

// Construcción de filtros dinámicos
$filtros = [];
$params = [];

// FILTRO OBLIGATORIO: Solo mostrar vehículos activos
$filtros[] = "p.Estado = :estado";
$params[':estado'] = 'Activo';

if (!empty($_GET['tipo'])) {
    $filtros[] = "p.TipoVehiculo = :tipo";
    $params[':tipo'] = $_GET['tipo'];
}
if (!empty($_GET['placa'])) {
    $filtros[] = "p.PlacaVehiculo LIKE :placa";
    $params[':placa'] = '%' . $_GET['placa'] . '%';
}
if (!empty($_GET['tarjeta'])) {
    $filtros[] = "p.TarjetaPropiedad LIKE :tarjeta";
    $params[':tarjeta'] = '%' . $_GET['tarjeta'] . '%';
}
if (!empty($_GET['fecha'])) {
    $filtros[] = "DATE(p.FechaParqueadero) = :fecha";
    $params[':fecha'] = $_GET['fecha'];
}
if (!empty($_GET['sede'])) {
    $filtros[] = "p.IdSede = :sede";
    $params[':sede'] = $_GET['sede'];
}

$where = "WHERE " . implode(" AND ", $filtros);

$sql = "SELECT p.*, s.TipoSede, s.Ciudad
        FROM Parqueadero p
        LEFT JOIN sede s ON p.IdSede = s.IdSede
        $where 
        ORDER BY p.IdParqueadero DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cargar sedes activas para filtro y modal
$sqlSedes = "SELECT IdSede, TipoSede, Ciudad FROM sede WHERE Estado = 'Activo' ORDER BY TipoSede ASC";
$stmtSedes = $conn->prepare($sqlSedes);
$stmtSedes->execute();
$sedesDisponibles = $stmtSedes->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-car me-2"></i>Vehículos Registrados</h1>
        <a href="./Parqueadero.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Vehículo
        </a>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Filtrar Vehículos</h6>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-2">
                    <label for="tipo" class="form-label">Tipo de Vehículo</label>
                    <select name="tipo" id="tipo" class="form-select">
                        <option value="">Todos</option>
                        <option value="Bicicleta" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Bicicleta') ? 'selected' : '' ?>>Bicicleta</option>
                        <option value="Moto"      <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Moto')      ? 'selected' : '' ?>>Moto</option>
                        <option value="Carro"     <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Carro')     ? 'selected' : '' ?>>Carro</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="placa" class="form-label">Placa</label>
                    <input type="text" name="placa" id="placa" class="form-control" value="<?= htmlspecialchars($_GET['placa'] ?? '') ?>" placeholder="Buscar placa">
                </div>
                <div class="col-md-2">
                    <label for="tarjeta" class="form-label">Tarjeta Propiedad</label>
                    <input type="text" name="tarjeta" id="tarjeta" class="form-control" value="<?= htmlspecialchars($_GET['tarjeta'] ?? '') ?>" placeholder="Buscar tarjeta">
                </div>
                <div class="col-md-2">
                    <label for="fecha" class="form-label">Fecha</label>
                    <input type="date" name="fecha" id="fecha" class="form-control" value="<?= htmlspecialchars($_GET['fecha'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label for="sede" class="form-label">Sede</label>
                    <select name="sede" id="sede" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($sedesDisponibles as $sede) : ?>
                            <option value="<?= $sede['IdSede'] ?>" <?= (isset($_GET['sede']) && $_GET['sede'] == $sede['IdSede']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sede['TipoSede']) ?> — <?= htmlspecialchars($sede['Ciudad']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-1 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i></button>
                    <a href="VehiculoSupervisor.php" class="btn btn-secondary"><i class="fas fa-broom"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Vehículos Activos</h6>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle text-center" id="TablaVehiculos">
                <thead class="table-dark">
                    <tr>
                        <th>QR</th>
                        <th>Tipo Vehículo</th>
                        <th>Placa</th>
                        <th>Descripción</th>
                        <th>Tarjeta Propiedad</th>
                        <th>Fecha Parqueadero</th>
                        <th>Sede</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && count($result) > 0) : ?>
                        <?php foreach ($result as $row) : ?>
                            <tr id="fila-<?php echo $row['IdParqueadero']; ?>">
                                <td class="text-center">
                                    <?php if (!empty($row['QrVehiculo'])) : ?>
                                        <button type="button" class="btn btn-sm btn-outline-success"
                                                onclick="verQRVehiculo('<?php echo htmlspecialchars($row['QrVehiculo']); ?>', <?php echo $row['IdParqueadero']; ?>)"
                                                title="Ver código QR">
                                            <i class="fas fa-qrcode me-1"></i> Ver QR
                                        </button>
                                        <br>
                                        <button type="button" class="btn btn-sm btn-outline-info mt-1"
                                                onclick="solicitarCorreoYEnviarQR(<?php echo $row['IdParqueadero']; ?>, '<?php echo htmlspecialchars($row['PlacaVehiculo']); ?>')"
                                                title="Enviar QR por correo">
                                            <i class="fas fa-envelope me-1"></i> Enviar
                                        </button>
                                    <?php else : ?>
                                        <span class="badge badge-warning">Sin QR</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['TipoVehiculo']); ?></td>
                                <td><?php echo htmlspecialchars($row['PlacaVehiculo']); ?></td>
                                <td><?php echo htmlspecialchars($row['DescripcionVehiculo']); ?></td>
                                <td><?php echo htmlspecialchars($row['TarjetaPropiedad']); ?></td>
                                <td><?php echo htmlspecialchars($row['FechaParqueadero']); ?></td>
                                <td>
                                    <?php if (!empty($row['TipoSede'])) : ?>
                                        <?php echo htmlspecialchars($row['TipoSede']); ?> — <?php echo htmlspecialchars($row['Ciudad']); ?>
                                    <?php else : ?>
                                        <span class="badge bg-secondary">Sin sede</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick='cargarDatosEdicionVehiculo(<?php echo json_encode($row); ?>)'
                                            title="Editar vehículo">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-exclamation-circle fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No hay vehículos activos registrados con los filtros seleccionados</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para visualizar QR de Vehículo -->
<div class="modal fade" id="modalVerQRVehiculo" tabindex="-1" role="dialog" aria-labelledby="modalVerQRVehiculoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalVerQRVehiculoLabel">
                    <i class="fas fa-qrcode me-2"></i>Código QR - Vehículo #<span id="qrVehiculoId"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="qrImagenVehiculo" src="" alt="Código QR Vehículo" class="img-fluid" style="max-width: 300px; border: 2px solid #ddd; padding: 10px; border-radius: 5px;">
                <p class="text-muted mt-3">Escanea este código con tu dispositivo móvil</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <a id="btnDescargarQRVehiculo" href="#" class="btn btn-success" download>
                    <i class="fas fa-download me-1"></i> Descargar QR
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Vehículo -->
<div class="modal fade" id="modalEditarVehiculo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Editar Vehículo</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formEditarVehiculo">
                    <input type="hidden" id="editIdVehiculo" name="id">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo Vehículo</label>
                            <select id="editTipoVehiculo" class="form-control" name="tipo" required>
                                <option value="">-- Seleccione un tipo --</option>
                                <option value="Bicicleta">Bicicleta</option>
                                <option value="Moto">Moto</option>
                                <option value="Carro">Carro</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Placa <small class="text-muted">(No editable)</small></label>
                            <input type="text" id="editPlacaVehiculoDisabled" class="form-control bg-light" disabled>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea id="editDescripcionVehiculo" class="form-control" name="descripcion" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tarjeta Propiedad <small class="text-muted">(No editable)</small></label>
                            <input type="text" id="editTarjetaPropiedadDisabled" class="form-control bg-light" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha <small class="text-muted">(No editable)</small></label>
                            <input type="datetime-local" id="editFechaParqueaderoDisabled" class="form-control bg-light" disabled>
                        </div>
                    </div>

                    <!-- SELECT de sede en lugar del input numérico -->
                    <div class="mb-3">
                        <label class="form-label">Sede</label>
                        <select id="editIdSede" class="form-control" name="idsede" required>
                            <option value="">-- Seleccione una sede --</option>
                            <?php foreach ($sedesDisponibles as $sede) : ?>
                                <option value="<?= $sede['IdSede'] ?>">
                                    <?= htmlspecialchars($sede['TipoSede']) ?> — <?= htmlspecialchars($sede['Ciudad']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" id="btnGuardarCambiosVehiculo">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>

<script>
// ── DataTable ────────────────────────────────────────────────────────────────
$(document).ready(function() {
    $('#TablaVehiculos').DataTable({
        language: { url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json" },
        pageLength: 10,
        responsive: true,
        order: [[0, "desc"]]
    });
});

// ── Ver QR ───────────────────────────────────────────────────────────────────
function verQRVehiculo(rutaQR, idVehiculo) {
    var rutaCompleta = '/SEGTRACK/Public/' + rutaQR;
    $('#qrVehiculoId').text(idVehiculo);
    $('#qrImagenVehiculo').attr('src', rutaCompleta);
    $('#btnDescargarQRVehiculo').attr('href', rutaCompleta).attr('download', 'QR-Vehiculo-' + idVehiculo + '.png');
    $('#modalVerQRVehiculo').modal('show');
}

// ── Solicitar correo y enviar QR ─────────────────────────────────────────────
function solicitarCorreoYEnviarQR(idVehiculo, placa) {
    Swal.fire({
        title: '📧 Enviar Código QR',
        html: `
            <p class="mb-3">Ingresa el correo electrónico donde deseas recibir el código QR del vehículo:</p>
            <p class="text-primary"><strong>Placa: ${placa}</strong></p>
            <input type="email" id="correoInput" class="swal2-input" placeholder="ejemplo@correo.com" style="width: 80%;">
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '<i class="fas fa-paper-plane"></i> Enviar',
        cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
        reverseButtons: true,
        preConfirm: () => {
            const correo = document.getElementById('correoInput').value;
            if (!correo) {
                Swal.showValidationMessage('Por favor ingresa un correo electrónico');
                return false;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
                Swal.showValidationMessage('Por favor ingresa un correo electrónico válido');
                return false;
            }
            return correo;
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            enviarQRVehiculo(idVehiculo, result.value, placa);
        }
    });
}

function enviarQRVehiculo(idVehiculo, correoDestinatario, placa) {
    Swal.fire({
        title: 'Enviando correo...',
        html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br>Por favor espere',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false
    });

    $.ajax({
        url: '../../Controller/ControladorParqueadero.php',
        type: 'POST',
        data: { accion: 'enviar_qr', id_vehiculo: idVehiculo, correo_destinatario: correoDestinatario },
        dataType: 'json',
        timeout: 30000,
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Correo enviado!',
                    html: `<p>${response.message}</p><small class="text-muted">QR del vehículo con placa <strong>${placa}</strong> enviado</small>`,
                    timer: 4000,
                    timerProgressBar: true,
                    confirmButtonColor: '#1cc88a'
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Error al enviar', text: response.message, confirmButtonColor: '#e74a3b' });
            }
        },
        error: function(xhr, status) {
            let errorMsg = status === 'timeout' ? 'La solicitud tardó demasiado tiempo.' : 'No se pudo conectar con el servidor.';
            Swal.fire({ icon: 'error', title: 'Error de conexión', text: errorMsg, confirmButtonColor: '#e74a3b' });
        }
    });
}

// ── Cargar datos modal edición ────────────────────────────────────────────────
function cargarDatosEdicionVehiculo(row) {
    $('#editIdVehiculo').val(row.IdParqueadero);
    $('#editTipoVehiculo').val(row.TipoVehiculo);
    $('#editDescripcionVehiculo').val(row.DescripcionVehiculo);
    $('#editIdSede').val(row.IdSede);          // preselecciona la sede actual
    $('#editPlacaVehiculoDisabled').val(row.PlacaVehiculo);
    $('#editTarjetaPropiedadDisabled').val(row.TarjetaPropiedad);

    var fechaHora = row.FechaParqueadero;
    if (fechaHora) fechaHora = fechaHora.replace(' ', 'T').substring(0, 16);
    $('#editFechaParqueaderoDisabled').val(fechaHora);

    $('#modalEditarVehiculo').modal('show');
}

// ── Guardar cambios ───────────────────────────────────────────────────────────
$(document).ready(function() {
    $('#btnGuardarCambiosVehiculo').click(function() {
        var id          = $('#editIdVehiculo').val();
        var tipo        = $('#editTipoVehiculo').val();
        var descripcion = $('#editDescripcionVehiculo').val().trim();
        var idsede      = $('#editIdSede').val();

        if (!tipo || !idsede) {
            Swal.fire({ icon: 'warning', title: 'Campos incompletos', text: 'Complete todos los campos obligatorios', confirmButtonColor: '#f6c23e' });
            return;
        }
        if (!descripcion || descripcion.length < 5) {
            Swal.fire({ icon: 'warning', title: 'Descripción inválida', text: 'La descripción debe tener al menos 5 caracteres', confirmButtonColor: '#f6c23e' });
            return;
        }

        $('#modalEditarVehiculo').modal('hide');
        Swal.fire({ title: 'Guardando...', text: 'Por favor espere', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        $.ajax({
            url: '../../Controller/ControladorParqueadero.php',
            type: 'POST',
            data: { accion: 'actualizar', id, tipo, descripcion, idsede },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({ icon: 'success', title: '¡Éxito!', text: 'Vehículo actualizado correctamente', timer: 2000, showConfirmButton: false })
                        .then(() => { location.reload(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'Error al actualizar el vehículo' });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor' });
            }
        });
    });
});
</script>