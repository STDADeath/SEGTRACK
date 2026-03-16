<?php require_once __DIR__ . '/../layouts/parte_superior_supervisor.php'; ?>
<?php require_once(__DIR__ . "/../../Core/conexion.php"); ?>

<?php
$conexionObj = new Conexion();
$conn        = $conexionObj->getConexion();

// ── Filtros dinámicos ────────────────────────────────────────────────────────
$filtros = [];
$params  = [];

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
if (!empty($_GET['estado'])) {
    $filtros[] = "v.Estado = :estado";
    $params[':estado'] = $_GET['estado'];
}
if (!empty($_GET['propietario'])) {
    if ($_GET['propietario'] === 'Funcionario')   $filtros[] = "v.IdFuncionario IS NOT NULL";
    elseif ($_GET['propietario'] === 'Visitante') $filtros[] = "v.IdVisitante IS NOT NULL";
}

$where = count($filtros) > 0 ? "WHERE " . implode(" AND ", $filtros) : "";

$sql = "SELECT v.*,
               s.TipoSede, s.Ciudad,
               f.NombreFuncionario,
               vis.NombreVisitante
        FROM vehiculo v
        LEFT JOIN sede        s   ON v.IdSede        = s.IdSede
        LEFT JOIN funcionario f   ON v.IdFuncionario = f.IdFuncionario
        LEFT JOIN visitante   vis ON v.IdVisitante   = vis.IdVisitante
        $where
        ORDER BY
            CASE WHEN v.Estado = 'Activo' THEN 1 ELSE 2 END,
            v.IdVehiculo DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sedes activas para filtro y modal
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
    <div class="card-header py-3 bg-light d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-filter mr-2"></i>Filtrar Vehículos
        </h6>
        <a href="VehiculoSupervisor.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-broom mr-1"></i>Limpiar filtros
        </a>
    </div>
    <div class="card-body">
        <form method="get">
            <div class="row align-items-end">

                <!-- Tipo de Vehículo -->
                <div class="col-md-2 mb-3">
                    <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                        <i class="fas fa-car mr-1 text-primary"></i>Tipo
                    </label>
                    <select name="tipo" id="tipo" class="form-control">
                        <option value="">Todos</option>
                        <option value="Bicicleta" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Bicicleta') ? 'selected' : '' ?>>🚲 Bicicleta</option>
                        <option value="Moto"      <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Moto')      ? 'selected' : '' ?>>🏍️ Moto</option>
                        <option value="Carro"     <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Carro')     ? 'selected' : '' ?>>🚗 Carro</option>
                    </select>
                </div>

                <!-- Placa -->
                <div class="col-md-2 mb-3">
                    <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                        <i class="fas fa-id-card mr-1 text-primary"></i>Placa
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" name="placa" id="placa" class="form-control"
                               value="<?= htmlspecialchars($_GET['placa'] ?? '') ?>"
                               placeholder="Ej: ABC123"
                               style="text-transform:uppercase;">
                    </div>
                </div>

                <!-- Tarjeta Propiedad -->
                <div class="col-md-2 mb-3">
                    <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                        <i class="fas fa-address-card mr-1 text-primary"></i>Tarjeta Propiedad
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" name="tarjeta" id="tarjeta" class="form-control"
                               value="<?= htmlspecialchars($_GET['tarjeta'] ?? '') ?>"
                               placeholder="Buscar tarjeta">
                    </div>
                </div>

                <!-- Sede -->
                <div class="col-md-2 mb-3">
                    <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                        <i class="fas fa-building mr-1 text-primary"></i>Sede
                    </label>
                    <select name="sede" id="sede" class="form-control">
                        <option value="">Todas</option>
                        <?php foreach ($sedesDisponibles as $sede) : ?>
                            <option value="<?= $sede['IdSede'] ?>"
                                <?= (isset($_GET['sede']) && $_GET['sede'] == $sede['IdSede']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sede['TipoSede']) ?> — <?= htmlspecialchars($sede['Ciudad']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Propietario -->
                <div class="col-md-2 mb-3">
                    <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                        <i class="fas fa-user mr-1 text-primary"></i>Propietario
                    </label>
                    <select name="propietario" id="propietario" class="form-control">
                        <option value="">Todos</option>
                        <option value="Funcionario" <?= (isset($_GET['propietario']) && $_GET['propietario'] == 'Funcionario') ? 'selected' : '' ?>>
                            👔 Funcionario
                        </option>
                        <option value="Visitante" <?= (isset($_GET['propietario']) && $_GET['propietario'] == 'Visitante') ? 'selected' : '' ?>>
                            🧑 Visitante
                        </option>
                    </select>
                </div>

                <!-- Estado -->
                <div class="col-md-1 mb-3">
                    <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                        <i class="fas fa-toggle-on mr-1 text-primary"></i>Estado
                    </label>
                    <select name="estado" id="estado" class="form-control">
                        <option value="">Todos</option>
                        <option value="Activo"   <?= (isset($_GET['estado']) && $_GET['estado'] == 'Activo')   ? 'selected' : '' ?>>✅ Activo</option>
                        <option value="Inactivo" <?= (isset($_GET['estado']) && $_GET['estado'] == 'Inactivo') ? 'selected' : '' ?>>❌ Inactivo</option>
                    </select>
                </div>

                <!-- Botón filtrar -->
                <div class="col-md-1 mb-3">
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
            <h6 class="m-0 font-weight-bold text-primary">Lista de Vehículos</h6>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle text-center" id="TablaVehiculoSupervisor">
                <thead class="table-dark">
                    <tr>
                        <th>QR</th>
                        <th>Tipo Vehículo</th>
                        <th>Placa</th>
                        <th>Descripción</th>
                        <th>Tarjeta Propiedad</th>
                        <th>Fecha</th>
                        <th>Propietario</th>
                        <th>Sede</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && count($result) > 0) : ?>
                        <?php foreach ($result as $row) : ?>
                            <tr id="fila-<?= $row['IdVehiculo'] ?>"
                                class="<?= $row['Estado'] === 'Inactivo' ? 'fila-inactiva' : '' ?>">

                                <!-- QR + Enviar -->
                                <td class="text-center">
                                    <?php if (!empty($row['QrVehiculo'])) : ?>
                                        <button type="button" class="btn btn-sm btn-outline-success"
                                                onclick="verQRVehiculo('<?= htmlspecialchars($row['QrVehiculo']) ?>', <?= $row['IdVehiculo'] ?>)"
                                                title="Ver código QR">
                                            <i class="fas fa-qrcode me-1"></i> Ver QR
                                        </button>
                                        <br>
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
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Estado -->
                                <td class="text-center">
                                    <?php if ($row['Estado'] === 'Activo') : ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else : ?>
                                        <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Acciones -->
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick='cargarDatosEdicionVehiculo(<?= json_encode($row) ?>)'
                                            title="Editar vehículo">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button"
                                            class="btn btn-sm <?= $row['Estado'] === 'Activo' ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                                            onclick="confirmarCambioEstadoVehiculo(<?= $row['IdVehiculo'] ?>, '<?= $row['Estado'] ?>')"
                                            title="<?= $row['Estado'] === 'Activo' ? 'Desactivar' : 'Activar' ?> vehículo">
                                            <i class="fas <?= $row['Estado'] === 'Activo' ? 'fa-lock' : 'fa-lock-open' ?>"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="fas fa-exclamation-circle fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No hay vehículos registrados con los filtros seleccionados</p>
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

<!-- Modal Confirmar Cambio de Estado -->
<div class="modal fade" id="modalCambiarEstadoVehiculo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="headerCambioEstadoVehiculo">
                <h5 class="modal-title" id="tituloCambioEstadoVehiculo"></h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <div class="toggle-container">
                    <label class="btn-lock" id="toggleEstadoVisualVehiculo">
                        <svg width="36" height="40" viewBox="0 0 36 40">
                            <path class="lockb" d="M27 27C27 34.1797 21.1797 40 14 40C6.8203 40 1 34.1797 1 27C1 19.8203 6.8203 14 14 14C21.1797 14 27 19.8203 27 27ZM15.6298 26.5191C16.4544 25.9845 17 25.056 17 24C17 22.3431 15.6569 21 14 21C12.3431 21 11 22.3431 11 24C11 25.056 11.5456 25.9845 12.3702 26.5191L11 32H17L15.6298 26.5191Z"></path>
                            <path class="lock"  d="M6 21V10C6 5.58172 9.58172 2 14 2V2C18.4183 2 22 5.58172 22 10V21"></path>
                            <path class="bling" d="M29 20L31 22"></path>
                            <path class="bling" d="M31.5 15H34.5"></path>
                            <path class="bling" d="M29 10L31 8"></path>
                        </svg>
                    </label>
                </div>
                <p id="mensajeCambioEstadoVehiculo" class="mb-3 mt-2" style="font-size:1.1rem;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarCambioEstadoVehiculo">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {

    let vehiculoACambiarEstado = null;
    let estadoActualVehiculo   = null;

    // ── Ver QR ────────────────────────────────────────────────────────────────
    window.verQRVehiculo = function (rutaQR, idVehiculo) {
        var rutaCompleta = '/SEGTRACK/Public/' + rutaQR;
        $('#qrVehiculoId').text(idVehiculo);
        $('#qrImagenVehiculo').attr('src', rutaCompleta);
        $('#btnDescargarQRVehiculo').attr('href', rutaCompleta).attr('download', 'QR-Vehiculo-' + idVehiculo + '.png');
        $('#modalVerQRVehiculo').modal('show');
    };

    // ── Enviar QR: funcionario = automático / visitante = pide correo ─────────
    window.manejarEnvioQR = function (idVehiculo, placa, esFuncionario) {
        if (esFuncionario) {
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
    };

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
            data: { accion: 'enviar_qr', id_vehiculo: idVehiculo, correo_destinatario: correoDestinatario },
            dataType: 'json',
            timeout: 30000,
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Correo enviado!',
                        html: `<p>${response.message}</p><small class="text-muted">Placa: <strong>${placa}</strong></small>`,
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

    // ── Cargar datos modal edición ────────────────────────────────────────────
    window.cargarDatosEdicionVehiculo = function (row) {
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
    };

    // ── Confirmar cambio de estado ────────────────────────────────────────────
    window.confirmarCambioEstadoVehiculo = function (id, estado) {
        vehiculoACambiarEstado = id;
        estadoActualVehiculo   = estado;

        const nuevoEstado = estado === 'Activo' ? 'Inactivo' : 'Activo';
        const accion      = nuevoEstado === 'Activo' ? 'activar' : 'desactivar';
        const colorHeader = nuevoEstado === 'Activo' ? 'bg-success' : 'bg-warning';
        const icono       = nuevoEstado === 'Activo' ? 'fa-lock-open' : 'fa-lock';

        $('#headerCambioEstadoVehiculo').removeClass('bg-success bg-warning').addClass(colorHeader + ' text-white');
        $('#tituloCambioEstadoVehiculo').html('<i class="fas ' + icono + ' me-2"></i>' + accion.charAt(0).toUpperCase() + accion.slice(1) + ' Vehículo');
        $('#mensajeCambioEstadoVehiculo').html('¿Está seguro que desea <strong>' + accion + '</strong> este vehículo?');

        $('#modalCambiarEstadoVehiculo').modal('show');

        setTimeout(function () {
            const t = document.getElementById('toggleEstadoVisualVehiculo');
            if (t) nuevoEstado === 'Activo' ? t.classList.add('activo') : t.classList.remove('activo');
        }, 100);
    };

    // ── Ejecutar cambio de estado ─────────────────────────────────────────────
    $('#btnConfirmarCambioEstadoVehiculo').on('click', function () {
        if (!vehiculoACambiarEstado) return;

        const nuevoEstado = estadoActualVehiculo === 'Activo' ? 'Inactivo' : 'Activo';
        $('#modalCambiarEstadoVehiculo').modal('hide');
        Swal.fire({ title: 'Procesando...', text: 'Por favor espere', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        $.ajax({
            url: '../../Controller/ControladorVehiculo.php',
            type: 'POST',
            data: { accion: 'cambiar_estado', id: vehiculoACambiarEstado, estado: nuevoEstado },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.message || 'Estado cambiado correctamente', timer: 2000, showConfirmButton: false })
                        .then(() => { location.reload(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'No se pudo cambiar el estado' });
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo cambiar el estado del vehículo' });
            }
        });
    });

    $('#modalCambiarEstadoVehiculo').on('hidden.bs.modal', function () {
        $('#btnConfirmarCambioEstadoVehiculo').prop('disabled', false).html('Confirmar');
    });

    // ── Guardar cambios edición ───────────────────────────────────────────────
    $('#btnGuardarCambiosVehiculo').on('click', function () {
        const id          = $('#editIdVehiculo').val();
        const tipo        = $('#editTipoVehiculo').val();
        const descripcion = $('#editDescripcionVehiculo').val().trim();
        const idsede      = $('#editIdSede').val();
        const regexDesc   = /^[a-zA-Z0-9 .,-]+$/;

        if (!id || !tipo || !idsede) {
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
        Swal.fire({ title: 'Guardando...', text: 'Por favor espere', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        $.ajax({
            url: '../../Controller/ControladorVehiculo.php',
            type: 'POST',
            data: { accion: 'actualizar', id, tipo, descripcion, idsede },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    Swal.fire({ icon: 'success', title: '¡Actualizado!', text: 'Vehículo actualizado correctamente', timer: 2000, showConfirmButton: false })
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

    $('#modalEditarVehiculo').on('hidden.bs.modal', function () {
        $('#btnGuardarCambiosVehiculo').prop('disabled', false).html('Guardar Cambios');
    });

    // ── DataTable ─────────────────────────────────────────────────────────────
    $('#TablaVehiculoSupervisor').DataTable({
        language: { url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json" },
        pageLength: 10,
        responsive: true,
        order: [[0, "asc"]]
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/parte_inferior_supervisor.php'; ?>