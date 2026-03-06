<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>
<?php require_once(__DIR__ . "/../../Core/conexion.php"); ?>

<?php
$conexion = new Conexion();
$conn     = $conexion->getConexion();

// ── Filtros dinámicos ────────────────────────────────────────────────────────
$filtros = [];
$params  = [];

$filtros[] = "v.Estado = :estado";
$params[':estado'] = 'Activo';

if (!empty($_GET['tipo'])) {
    $filtros[] = "v.TipoVehiculo = :tipo";
    $params[':tipo'] = $_GET['tipo'];
}
if (!empty($_GET['placa'])) {
    $filtros[] = "v.PlacaVehiculo LIKE :placa";
    $params[':placa'] = '%' . $_GET['placa'] . '%';
}
if (!empty($_GET['tarjeta'])) {
    $filtros[] = "v.TarjetaPropiedad LIKE :tarjeta";
    $params[':tarjeta'] = '%' . $_GET['tarjeta'] . '%';
}
if (!empty($_GET['fecha'])) {
    $filtros[] = "DATE(v.FechaDeVehiculo) = :fecha";
    $params[':fecha'] = $_GET['fecha'];
}
if (!empty($_GET['sede'])) {
    $filtros[] = "v.IdSede = :sede";
    $params[':sede'] = $_GET['sede'];
}
if (!empty($_GET['propietario'])) {
    if ($_GET['propietario'] === 'Funcionario')    $filtros[] = "v.IdFuncionario IS NOT NULL";
    elseif ($_GET['propietario'] === 'Visitante')  $filtros[] = "v.IdVisitante IS NOT NULL";
}

$where = "WHERE " . implode(" AND ", $filtros);

$sql = "SELECT v.*,
               s.TipoSede, s.Ciudad,
               f.NombreFuncionario,
               vis.NombreVisitante
        FROM vehiculo v
        LEFT JOIN sede        s   ON v.IdSede        = s.IdSede
        LEFT JOIN funcionario f   ON v.IdFuncionario = f.IdFuncionario
        LEFT JOIN visitante   vis ON v.IdVisitante   = vis.IdVisitante
        $where
        ORDER BY v.IdVehiculo DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sedes para filtros y modal
$sqlSedes = "SELECT IdSede, TipoSede, Ciudad FROM sede WHERE Estado = 'Activo' ORDER BY TipoSede ASC";
$stmtSedes = $conn->prepare($sqlSedes);
$stmtSedes->execute();
$sedesDisponibles = $stmtSedes->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-car me-2"></i>Vehículos Registrados</h1>
        <a href="./Vehiculo.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
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
                    <input type="text" name="placa" id="placa" class="form-control"
                        value="<?= htmlspecialchars($_GET['placa'] ?? '') ?>" placeholder="Buscar placa">
                </div>
                <div class="col-md-2">
                    <label for="tarjeta" class="form-label">Tarjeta Propiedad</label>
                    <input type="text" name="tarjeta" id="tarjeta" class="form-control"
                        value="<?= htmlspecialchars($_GET['tarjeta'] ?? '') ?>" placeholder="Buscar tarjeta">
                </div>
                <div class="col-md-2">
                    <label for="fecha" class="form-label">Fecha</label>
                    <input type="date" name="fecha" id="fecha" class="form-control"
                        value="<?= htmlspecialchars($_GET['fecha'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label for="sede" class="form-label">Sede</label>
                    <select name="sede" id="sede" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($sedesDisponibles as $sede) : ?>
                            <option value="<?= $sede['IdSede'] ?>"
                                <?= (isset($_GET['sede']) && $_GET['sede'] == $sede['IdSede']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sede['TipoSede']) ?> — <?= htmlspecialchars($sede['Ciudad']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="propietario" class="form-label">Propietario</label>
                    <select name="propietario" id="propietario" class="form-select">
                        <option value="">Todos</option>
                        <option value="Funcionario" <?= (isset($_GET['propietario']) && $_GET['propietario'] == 'Funcionario') ? 'selected' : '' ?>>Funcionario</option>
                        <option value="Visitante"   <?= (isset($_GET['propietario']) && $_GET['propietario'] == 'Visitante')   ? 'selected' : '' ?>>Visitante</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                    <a href="VehiculoLista.php" class="btn btn-secondary">
                        <i class="fas fa-broom me-1"></i> Limpiar
                    </a>
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
                        <th>Fecha Registro</th>
                        <th>Propietario</th>
                        <th>Sede</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && count($result) > 0) : ?>
                        <?php foreach ($result as $row) : ?>
                            <tr id="fila-<?= $row['IdVehiculo'] ?>">
                                <!-- QR -->
                                <td class="text-center">
                                    <?php if (!empty($row['QrVehiculo'])) : ?>
                                        <button type="button" class="btn btn-sm btn-outline-success"
                                                onclick="verQRVehiculo('<?= htmlspecialchars($row['QrVehiculo']) ?>', <?= $row['IdVehiculo'] ?>)"
                                                title="Ver código QR">
                                            <i class="fas fa-qrcode me-1"></i> Ver QR
                                        </button>
                                        <br>
                                        <!-- 🆕 data-id-funcionario para que el JS sepa si es funcionario -->
                                        <button type="button" class="btn btn-sm btn-outline-info mt-1"
                                                onclick="manejarEnvioQR(<?= $row['IdVehiculo'] ?>, '<?= htmlspecialchars($row['PlacaVehiculo']) ?>', <?= !empty($row['IdFuncionario']) ? 'true' : 'false' ?>)"
                                                title="Enviar QR por correo">
                                            <i class="fas fa-envelope me-1"></i> Enviar
                                        </button>
                                    <?php else : ?>
                                        <span class="badge bg-warning text-dark">Sin QR</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['TipoVehiculo']) ?></td>
                                <td><?= htmlspecialchars($row['PlacaVehiculo']) ?></td>
                                <td><?= htmlspecialchars($row['DescripcionVehiculo']) ?></td>
                                <td><?= htmlspecialchars($row['TarjetaPropiedad']) ?></td>
                                <td><?= htmlspecialchars($row['FechaDeVehiculo']) ?></td>
                                <!-- Propietario -->
                                <td>
                                    <?php if (!empty($row['NombreFuncionario'])) : ?>
                                            <?= htmlspecialchars($row['NombreFuncionario']) ?>
                                        </span>
                                    <?php elseif (!empty($row['NombreVisitante'])) : ?>
                                        <span class="badge bg-info text-dark">
                                            <i class="fas fa-user me-1"></i>
                                            <?= htmlspecialchars($row['NombreVisitante']) ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="badge bg-secondary">Sin asignar</span>
                                    <?php endif; ?>
                                </td>
                                <!-- Sede -->
                                <td>
                                    <?php if (!empty($row['TipoSede'])) : ?>
                                        <?= htmlspecialchars($row['TipoSede']) ?> — <?= htmlspecialchars($row['Ciudad']) ?>
                                    <?php else : ?>
                                        <span class="badge bg-secondary">Sin sede</span>
                                    <?php endif; ?>
                                </td>
                                <!-- Acciones -->
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick='cargarDatosEdicionVehiculo(<?= json_encode($row) ?>)'
                                        title="Editar vehículo">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
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

<!-- Modal Ver QR -->
<div class="modal fade" id="modalVerQRVehiculo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-qrcode me-2"></i>Código QR — Vehículo #<span id="qrVehiculoId"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <img id="qrImagenVehiculo" src="" alt="Código QR Vehículo" class="img-fluid"
                    style="max-width:300px;border:2px solid #ddd;padding:10px;border-radius:5px;">
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
<div class="modal fade" id="modalEditarVehiculo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
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
                                <option value="Otro">Otro</option>
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
                            <input type="datetime-local" id="editFechaDeVehiculoDisabled" class="form-control bg-light" disabled>
                        </div>
                    </div>
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
                    <div class="mb-3">
                        <label class="form-label">Propietario <small class="text-muted">(No editable)</small></label>
                        <input type="text" id="editPropietarioDisabled" class="form-control bg-light" disabled>
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
// ── DataTable ─────────────────────────────────────────────────────────────────
$(document).ready(function () {
    $('#TablaVehiculos').DataTable({
        language: { url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json" },
        pageLength: 10,
        responsive: true,
        order: [[0, "desc"]]
    });
});

// ── Ver QR ────────────────────────────────────────────────────────────────────
function verQRVehiculo(rutaQR, idVehiculo) {
    var rutaCompleta = '/SEGTRACK/Public/' + rutaQR;
    $('#qrVehiculoId').text(idVehiculo);
    $('#qrImagenVehiculo').attr('src', rutaCompleta);
    $('#btnDescargarQRVehiculo').attr('href', rutaCompleta).attr('download', 'QR-Vehiculo-' + idVehiculo + '.png');
    $('#modalVerQRVehiculo').modal('show');
}

// ── 🆕 LÓGICA CENTRAL DE ENVÍO ────────────────────────────────────────────────
// Si esFuncionario=true  → enviar directo (el correo lo saca el servidor de la BD)
// Si esFuncionario=false → pedir correo al usuario (es visitante)
function manejarEnvioQR(idVehiculo, placa, esFuncionario) {
    if (esFuncionario) {
        // Confirmación simple antes de enviar al correo registrado
        Swal.fire({
            title: '📧 Enviar Código QR',
            html: `<p>Se enviará el QR al <strong>correo registrado</strong> del funcionario propietario del vehículo:</p>
                <p class="text-primary fw-bold">Placa: ${placa}</p>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="fas fa-paper-plane"></i> Enviar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            reverseButtons: true
        }).then(result => {
            if (result.isConfirmed) enviarQRVehiculo(idVehiculo, '', placa);
        });
    } else {
        // Visitante: pedir correo manualmente
        Swal.fire({
            title: '📧 Enviar Código QR',
            html: `<p class="mb-3">Ingresa el correo donde deseas recibir el QR del vehículo:</p>
                <p class="text-primary fw-bold">Placa: ${placa}</p>
                <input type="email" id="correoInput" class="swal2-input" placeholder="ejemplo@correo.com" style="width:80%;">`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="fas fa-paper-plane"></i> Enviar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            reverseButtons: true,
            preConfirm: () => {
                const correo = document.getElementById('correoInput').value;
                if (!correo) { Swal.showValidationMessage('Por favor ingresa un correo'); return false; }
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) { Swal.showValidationMessage('Correo no válido'); return false; }
                return correo;
            }
        }).then(result => {
            if (result.isConfirmed && result.value) enviarQRVehiculo(idVehiculo, result.value, placa);
        });
    }
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
        url: '../../Controller/ControladorVehiculo.php',
        type: 'POST',
        data: {
            accion: 'enviar_qr',
            id_vehiculo: idVehiculo,
            correo_destinatario: correoDestinatario  // vacío si es funcionario, el servidor lo resuelve
        },
        dataType: 'json',
        timeout: 30000,
        success: function (response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Correo enviado!',
                    html: `<p>${response.message}</p>
                        <small class="text-muted">Placa: <strong>${placa}</strong></small>`,
                    timer: 4000,
                    timerProgressBar: true,
                    confirmButtonColor: '#1cc88a'
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Error al enviar', text: response.message, confirmButtonColor: '#e74a3b' });
            }
        },
        error: function (xhr, status) {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: status === 'timeout' ? 'La solicitud tardó demasiado.' : 'No se pudo conectar con el servidor.',
                confirmButtonColor: '#e74a3b'
            });
        }
    });
}

// ── Cargar datos modal edición ────────────────────────────────────────────────
function cargarDatosEdicionVehiculo(row) {
    $('#editIdVehiculo').val(row.IdVehiculo);
    $('#editTipoVehiculo').val(row.TipoVehiculo);
    $('#editDescripcionVehiculo').val(row.DescripcionVehiculo);
    $('#editIdSede').val(row.IdSede);
    $('#editPlacaVehiculoDisabled').val(row.PlacaVehiculo);
    $('#editTarjetaPropiedadDisabled').val(row.TarjetaPropiedad);

    if (row.NombreFuncionario) {
        $('#editPropietarioDisabled').val('Funcionario: ' + row.NombreFuncionario);
    } else if (row.NombreVisitante) {
        $('#editPropietarioDisabled').val('Visitante: ' + row.NombreVisitante);
    } else {
        $('#editPropietarioDisabled').val('Sin asignar');
    }

    var fechaHora = row.FechaDeVehiculo;
    if (fechaHora) fechaHora = fechaHora.replace(' ', 'T').substring(0, 16);
    $('#editFechaDeVehiculoDisabled').val(fechaHora);

    $('#modalEditarVehiculo').modal('show');
}

// ── Guardar cambios ───────────────────────────────────────────────────────────
$(document).ready(function () {
    $('#btnGuardarCambiosVehiculo').click(function () {
        var id          = $('#editIdVehiculo').val();
        var tipo        = $('#editTipoVehiculo').val();
        var descripcion = $('#editDescripcionVehiculo').val().trim();
        var idsede      = $('#editIdSede').val();
        var regexDesc   = /^[a-zA-Z0-9 .,-]+$/;

        if (!tipo || !idsede) {
            Swal.fire({ icon: 'warning', title: 'Campos incompletos', text: 'Complete el Tipo de Vehículo y la Sede', confirmButtonColor: '#f6c23e' });
            return;
        }
        if (!descripcion || descripcion.length < 5) {
            Swal.fire({ icon: 'warning', title: 'Descripción inválida', text: 'La descripción debe tener al menos 5 caracteres', confirmButtonColor: '#f6c23e' });
            return;
        }
        if (!regexDesc.test(descripcion)) {
            Swal.fire({ icon: 'error', title: 'Caracteres inválidos', text: 'La descripción contiene caracteres no válidos', confirmButtonColor: '#e74a3b' });
            return;
        }

        $('#modalEditarVehiculo').modal('hide');
        Swal.fire({ title: 'Guardando...', html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br>Por favor espere', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false });

        $.ajax({
            url: '../../Controller/ControladorVehiculo.php',
            type: 'POST',
            data: { accion: 'actualizar', id, tipo, descripcion, idsede },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    Swal.fire({ icon: 'success', title: '¡Actualizado!', text: 'Vehículo actualizado correctamente', timer: 2000, timerProgressBar: true, showConfirmButton: true, confirmButtonColor: '#1cc88a' })
                        .then(() => { location.reload(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', html: response.message.replace(/\n/g, '<br>'), confirmButtonColor: '#e74a3b' });
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor' });
            }
        });
    });
});
</script>