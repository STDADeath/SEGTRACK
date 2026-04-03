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

// ── FIX: cada condición en su propio elemento del array ──────────────────────
if (!empty($_GET['propietario'])) {
    if ($_GET['propietario'] === 'Funcionario') {
        $filtros[] = "v.IdFuncionario IS NOT NULL";
        $filtros[] = "v.IdVisitante IS NULL";
    } elseif ($_GET['propietario'] === 'Visitante') {
        $filtros[] = "v.IdVisitante IS NOT NULL";
        $filtros[] = "v.IdFuncionario IS NULL";
    }
}

$where = count($filtros) > 0 ? "WHERE " . implode(" AND ", $filtros) : "";

$sql = "SELECT v.*,
               s.TipoSede, s.Ciudad,
               f.NombreFuncionario,
               f.CorreoFuncionario,
               vis.NombreVisitante,
               vis.CorreoVisitante
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
$sqlSedes  = "SELECT IdSede, TipoSede, Ciudad FROM sede WHERE Estado = 'Activo' ORDER BY TipoSede ASC";
$stmtSedes = $conn->prepare($sqlSedes);
$stmtSedes->execute();
$sedesDisponibles = $stmtSedes->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-car me-2"></i>Vehículos Registrados</h1>
        <a href="./VehiSuperRegis.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
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

                    <div class="col-md-2 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-car mr-1 text-primary"></i>Tipo
                        </label>
                        <select name="tipo" id="tipo" class="form-control">
                            <option value="">Todos</option>
                            <option value="Bicicleta" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Bicicleta') ? 'selected' : '' ?>> Bicicleta</option>
                            <option value="Moto"      <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Moto')      ? 'selected' : '' ?>> Moto</option>
                            <option value="Carro"     <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Carro')     ? 'selected' : '' ?>> Carro</option>
                            <option value="Otro"      <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Otro')      ? 'selected' : '' ?>> Otro</option>
                        </select>
                    </div>

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

                    <div class="col-md-2 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-user mr-1 text-primary"></i>Propietario
                        </label>
                        <select name="propietario" id="propietario" class="form-control">
                            <option value="">Todos</option>
                            <option value="Funcionario" <?= (isset($_GET['propietario']) && $_GET['propietario'] == 'Funcionario') ? 'selected' : '' ?>>Funcionario</option>
                            <option value="Visitante"   <?= (isset($_GET['propietario']) && $_GET['propietario'] == 'Visitante')   ? 'selected' : '' ?>>Visitante</option>
                        </select>
                    </div>

                    <div class="col-md-1 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-toggle-on mr-1 text-primary"></i>Estado
                        </label>
                        <select name="estado" id="estado" class="form-control">
                            <option value="">Todos</option>
                            <option value="Activo"   <?= (isset($_GET['estado']) && $_GET['estado'] == 'Activo')   ? 'selected' : '' ?>>Activo</option>
                            <option value="Inactivo" <?= (isset($_GET['estado']) && $_GET['estado'] == 'Inactivo') ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>

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
                        <th>Funcionario</th>
                        <th>Visitante</th>
                        <th>Sede</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && count($result) > 0) : ?>
                        <?php foreach ($result as $row) : ?>
                            <?php
                            $correoDisponible = !empty($row['CorreoFuncionario'] ?? '')
                                ? $row['CorreoFuncionario']
                                : ($row['CorreoVisitante'] ?? '');
                            $tieneCorreo = !empty($correoDisponible);
                            ?>
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
                                        <button type="button"
                                                class="btn btn-sm btn-outline-info mt-1 <?= !$tieneCorreo ? 'disabled' : '' ?>"
                                                onclick="manejarEnvioQR(<?= $row['IdVehiculo'] ?>, '<?= htmlspecialchars($row['PlacaVehiculo']) ?>', <?= !empty($row['IdFuncionario']) ? 'true' : 'false' ?>, '<?= htmlspecialchars($correoDisponible) ?>')"
                                                title="<?= $tieneCorreo ? 'Enviar QR por correo' : 'No hay correo registrado' ?>"
                                                <?= !$tieneCorreo ? 'disabled' : '' ?>>
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

                                <!-- Funcionario -->
                                <td>
                                    <?php if (!empty($row['NombreFuncionario'])) : ?>
                                        <?= htmlspecialchars($row['NombreFuncionario']) ?>
                                    <?php else : ?>
                                        <span class="badge bg-info text-white">No aplica</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Visitante -->
                                <td>
                                    <?php if (!empty($row['NombreVisitante'])) : ?>
                                        <?= htmlspecialchars($row['NombreVisitante']) ?>
                                    <?php else : ?>
                                        <span class="badge bg-info text-white">No aplica</span>
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
                            <td colspan="11" class="text-center py-4">
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
                    <!-- ✅ Campos Funcionario y Visitante separados -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Funcionario <small class="text-muted">(No editable)</small></label>
                            <input type="text" id="editFuncionarioDisabled" class="form-control bg-light" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Visitante <small class="text-muted">(No editable)</small></label>
                            <input type="text" id="editVisitanteDisabled" class="form-control bg-light" disabled>
                        </div>
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

<?php require_once __DIR__ . '/../layouts/parte_inferior_supervisor.php'; ?>

<script src="/SEGTRACK/Public/js/javascript/js/ValidacionVehiculo.js"></script>