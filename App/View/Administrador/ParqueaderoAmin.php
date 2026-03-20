<?php require_once __DIR__ . '/../layouts/parte_superior_administrador.php'; ?>
<?php require_once(__DIR__ . "/../../Core/conexion.php"); ?>
<?php require_once(__DIR__ . "/../../Model/ModeloParqueadero.php"); ?>

<?php
$conexionObj = new Conexion();
$conn        = $conexionObj->getConexion();
$modelo      = new ModeloParqueadero($conn);

// Sedes activas
$sqlSedes = "SELECT IdSede, TipoSede, Ciudad FROM sede WHERE Estado = 'Activo' ORDER BY TipoSede ASC";
$stmtS    = $conn->prepare($sqlSedes);
$stmtS->execute();
$sedes = $stmtS->fetchAll(PDO::FETCH_ASSOC);

// Todos los parqueaderos con resumen de ocupación
$parqueaderos = $modelo->obtenerTodos();

// IDs de sedes que ya tienen parqueadero (para deshabilitarlas en el form crear)
$sedesConParqueadero = array_column($parqueaderos, 'IdSede');
?>

<div class="container-fluid px-4 py-4">

    <!-- ── Header ──────────────────────────────────────────────────────────── -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-parking me-2"></i>Administrar Parqueadero
        </h1>
        <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"
                data-toggle="modal" data-target="#modalCrearParqueadero">
            <i class="fas fa-plus me-1"></i> Configurar Nueva Sede
        </button>
    </div>

    <!-- ── Tarjetas resumen por sede ───────────────────────────────────────── -->
    <?php if (count($parqueaderos) > 0) : ?>
        <div class="row mb-4">
            <?php foreach ($parqueaderos as $p) :
                $pct        = $p['CantidadParqueadero'] > 0
                            ? round(($p['EspaciosOcupados'] / $p['CantidadParqueadero']) * 100) : 0;
                $colorBarra = $pct >= 90 ? 'bg-danger' : ($pct >= 60 ? 'bg-warning' : 'bg-success');
                $resumen    = $modelo->obtenerResumenEspacios((int)$p['IdParqueadero']);
                $resMap     = [];
                foreach ($resumen as $r) $resMap[$r['TipoVehiculo']] = $r;
            ?>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card shadow h-100">
                        <!-- Cabecera de tarjeta -->
                        <div class="card-header d-flex justify-content-between align-items-center py-2
                            <?= $p['Estado'] === 'Activo' ? 'bg-primary' : 'bg-secondary' ?> text-white">
                            <span class="fw-bold">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?= htmlspecialchars($p['TipoSede']) ?> — <?= htmlspecialchars($p['Ciudad']) ?>
                            </span>
                            <span class="badge <?= $p['Estado'] === 'Activo' ? 'badge-success' : 'badge-secondary' ?> badge-estado">
                                <?= $p['Estado'] ?>
                            </span>
                        </div>

                        <div class="card-body">
                            <!-- Barra de ocupación -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">Ocupación general</small>
                                    <small class="fw-bold"><?= $pct ?>%</small>
                                </div>
                                <div class="progress" style="height:10px;">
                                    <div class="progress-bar <?= $colorBarra ?>" style="width:<?= $pct ?>%"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-success">
                                        <i class="fas fa-circle me-1" style="font-size:8px;"></i><?= $p['EspaciosLibres'] ?> libres
                                    </small>
                                    <small class="text-danger">
                                        <i class="fas fa-circle me-1" style="font-size:8px;"></i><?= $p['EspaciosOcupados'] ?> ocupados
                                    </small>
                                    <small class="text-muted">Total: <?= $p['CantidadParqueadero'] ?></small>
                                </div>
                            </div>

                            <!-- Desglose por tipo de vehículo -->
                            <div class="row text-center g-2">
                                <div class="col-4">
                                    <div class="border rounded p-2">
                                        <i class="fas fa-car fa-lg text-primary mb-1 d-block"></i>
                                        <span class="fw-bold"><?= $p['CantidadCarros'] ?></span>
                                        <small class="text-muted d-block">Carros</small>
                                        <?php if (isset($resMap['Carro'])) : ?>
                                            <small class="text-success"><?= $resMap['Carro']['Libres'] ?> libres</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded p-2">
                                        <i class="fas fa-motorcycle fa-lg text-warning mb-1 d-block"></i>
                                        <span class="fw-bold"><?= $p['CantidadMotos'] ?></span>
                                        <small class="text-muted d-block">Motos</small>
                                        <?php if (isset($resMap['Moto'])) : ?>
                                            <small class="text-success"><?= $resMap['Moto']['Libres'] ?> libres</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded p-2">
                                        <i class="fas fa-bicycle fa-lg text-success mb-1 d-block"></i>
                                        <span class="fw-bold"><?= $p['CantidadBicicletas'] ?></span>
                                        <small class="text-muted d-block">Bicis</small>
                                        <?php if (isset($resMap['Bicicleta'])) : ?>
                                            <small class="text-success"><?= $resMap['Bicicleta']['Libres'] ?> libres</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer de tarjeta con acciones -->
                        <div class="card-footer bg-light d-flex justify-content-between align-items-center py-2">
                            <small class="text-muted">ID: #<?= $p['IdParqueadero'] ?></small>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-primary"
                                        onclick='abrirModalEditar(<?= json_encode($p) ?>)'
                                        title="Editar espacios">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-info"
                                        onclick="verEspacios(<?= $p['IdParqueadero'] ?>, '<?= htmlspecialchars($p['TipoSede']) ?> - <?= htmlspecialchars($p['Ciudad']) ?>')"
                                        title="Ver espacios">
                                    <i class="fas fa-th"></i>
                                </button>
                                <button class="btn btn-sm <?= $p['Estado'] === 'Activo' ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                                        onclick="confirmarCambioEstado(<?= $p['IdParqueadero'] ?>, '<?= $p['Estado'] ?>')"
                                        title="<?= $p['Estado'] === 'Activo' ? 'Desactivar' : 'Activar' ?>">
                                    <i class="fas <?= $p['Estado'] === 'Activo' ? 'fa-lock' : 'fa-lock-open' ?>"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle me-2"></i>
            No hay parqueaderos configurados. Haga clic en <strong>Configurar Nueva Sede</strong> para comenzar.
        </div>
    <?php endif; ?>

    <!-- ── Tabla resumen general ────────────────────────────────────────────── -->
    <?php if (count($parqueaderos) > 0) : ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Resumen General de Parqueaderos</h6>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle text-center"
                   id="TablaParqueaderos">
                <thead class="table-dark">
                    <tr>
                        <th>Sede</th>
                        <th><i class="fas fa-car me-1"></i>Carros</th>
                        <th><i class="fas fa-motorcycle me-1"></i>Motos</th>
                        <th><i class="fas fa-bicycle me-1"></i>Bicicletas</th>
                        <th>Total</th>
                        <th>Libres</th>
                        <th>Ocupados</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($parqueaderos as $p) : ?>
                        <tr id="fila-<?= $p['IdParqueadero'] ?>">
                            <td class="text-start">
                                <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                <?= htmlspecialchars($p['TipoSede']) ?> — <?= htmlspecialchars($p['Ciudad']) ?>
                            </td>
                            <td><?= $p['CantidadCarros'] ?></td>
                            <td><?= $p['CantidadMotos'] ?></td>
                            <td><?= $p['CantidadBicicletas'] ?></td>
                            <td><strong><?= $p['CantidadParqueadero'] ?></strong></td>
                            <td><span class="badge badge-success"><?= $p['EspaciosLibres'] ?></span></td>
                            <td><span class="badge badge-danger"><?= $p['EspaciosOcupados'] ?></span></td>
                            <td>
                                <span class="badge <?= $p['Estado'] === 'Activo' ? 'badge-success badge-estado' : 'badge-secondary badge-estado' ?>">
                                    <?= $p['Estado'] ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary"
                                            onclick='abrirModalEditar(<?= json_encode($p) ?>)'
                                            title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info"
                                            onclick="verEspacios(<?= $p['IdParqueadero'] ?>, '<?= htmlspecialchars($p['TipoSede']) ?> - <?= htmlspecialchars($p['Ciudad']) ?>')"
                                            title="Ver espacios">
                                        <i class="fas fa-th"></i>
                                    </button>
                                    <button class="btn btn-sm <?= $p['Estado'] === 'Activo' ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                                            onclick="confirmarCambioEstado(<?= $p['IdParqueadero'] ?>, '<?= $p['Estado'] ?>')"
                                            title="<?= $p['Estado'] === 'Activo' ? 'Desactivar' : 'Activar' ?>">
                                        <i class="fas <?= $p['Estado'] === 'Activo' ? 'fa-lock' : 'fa-lock-open' ?>"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- ══ MODAL CREAR PARQUEADERO ═══════════════════════════════════════════════ -->
<div class="modal fade" id="modalCrearParqueadero" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Configurar Parqueadero — Nueva Sede
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formCrearParqueadero">

                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Sede <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                            <select class="form-select" name="IdSede" id="crearIdSede" required>
                                <option value="">Seleccione una sede...</option>
                                <?php foreach ($sedes as $s) : ?>
                                    <option value="<?= $s['IdSede'] ?>"
                                        <?= in_array($s['IdSede'], $sedesConParqueadero)
                                            ? 'disabled title="Esta sede ya tiene parqueadero configurado"' : '' ?>>
                                        <?= htmlspecialchars($s['TipoSede']) ?> — <?= htmlspecialchars($s['Ciudad']) ?>
                                        <?= in_array($s['IdSede'], $sedesConParqueadero) ? ' (Ya configurada)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="invalid-feedback">Debe seleccionar una sede</div>
                    </div>

                    <div class="alert alert-info py-2 mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Defina cuántos espacios hay para cada tipo de vehículo. El total se calculará automáticamente.
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-car text-primary me-1"></i>Espacios para Carros
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-car"></i></span>
                                <input type="number" class="form-control contador-tipo"
                                       name="Carros" id="crearCarros" min="0" value="0" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-motorcycle text-warning me-1"></i>Espacios para Motos
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-motorcycle"></i></span>
                                <input type="number" class="form-control contador-tipo"
                                       name="Motos" id="crearMotos" min="0" value="0" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-bicycle text-success me-1"></i>Espacios para Bicicletas
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-bicycle"></i></span>
                                <input type="number" class="form-control contador-tipo"
                                       name="Bicis" id="crearBicis" min="0" value="0" placeholder="0">
                            </div>
                        </div>
                    </div>

                    <div class="card bg-light border-primary">
                        <div class="card-body py-2 d-flex align-items-center justify-content-between">
                            <span class="fw-bold text-primary">
                                <i class="fas fa-calculator me-2"></i>Total de espacios:
                            </span>
                            <span id="crearTotal" class="fw-bold fs-4 text-primary">0</span>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button class="btn btn-primary" id="btnCrearParqueadero">
                    <i class="fas fa-save me-1"></i>Crear Parqueadero
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL EDITAR PARQUEADERO ══════════════════════════════════════════════ -->
<div class="modal fade" id="modalEditarParqueadero" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white" style="background:linear-gradient(135deg,#4e73df 0%,#224abe 100%);">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-2"></i>Editar Espacios — <span id="editNombreSede"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formEditarParqueadero">
                    <input type="hidden" id="editIdParqueadero" name="id">

                    <div class="alert alert-warning py-2 mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Solo se pueden <strong>eliminar espacios libres</strong>. Los ocupados se conservan siempre.
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-car text-primary me-1"></i>Espacios para Carros
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-car"></i></span>
                                <input type="number" class="form-control contador-editar"
                                       name="Carros" id="editCarros" min="0" value="0">
                            </div>
                            <small class="text-muted" id="editCarrosInfo"></small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-motorcycle text-warning me-1"></i>Espacios para Motos
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-motorcycle"></i></span>
                                <input type="number" class="form-control contador-editar"
                                       name="Motos" id="editMotos" min="0" value="0">
                            </div>
                            <small class="text-muted" id="editMotosInfo"></small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-bicycle text-success me-1"></i>Espacios para Bicicletas
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-bicycle"></i></span>
                                <input type="number" class="form-control contador-editar"
                                       name="Bicis" id="editBicis" min="0" value="0">
                            </div>
                            <small class="text-muted" id="editBicisInfo"></small>
                        </div>
                    </div>

                    <div class="card bg-light border-warning">
                        <div class="card-body py-2 d-flex align-items-center justify-content-between">
                            <span class="fw-bold text-warning">
                                <i class="fas fa-calculator me-2"></i>Nuevo total de espacios:
                            </span>
                            <span id="editTotal" class="fw-bold fs-4 text-warning">0</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button class="btn btn-warning" id="btnEditarParqueadero">
                    <i class="fas fa-save me-1"></i>Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL VER ESPACIOS ════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalVerEspacios" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header text-white" style="background:linear-gradient(135deg,#4e73df 0%,#224abe 100%);">
                <h5 class="modal-title">
                    <i class="fas fa-th mr-2"></i>Espacios — <span id="verEspaciosNombreSede"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="verEspaciosContenido">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-info"></i>
                    <p class="mt-2 text-muted">Cargando espacios...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL CAMBIO ESTADO ═══════════════════════════════════════════════════ -->
<div class="modal fade" id="modalCambiarEstado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="headerCambioEstado">
                <h5 class="modal-title" id="tituloCambioEstado"></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center">
                <div class="toggle-container">
                    <label class="btn-lock" id="toggleEstadoVisualParqueadero">
                        <svg width="36" height="40" viewBox="0 0 36 40">
                            <path class="lockb" d="M27 27C27 34.1797 21.1797 40 14 40C6.8203 40 1 34.1797 1 27C1 19.8203 6.8203 14 14 14C21.1797 14 27 19.8203 27 27ZM15.6298 26.5191C16.4544 25.9845 17 25.056 17 24C17 22.3431 15.6569 21 14 21C12.3431 21 11 22.3431 11 24C11 25.056 11.5456 25.9845 12.3702 26.5191L11 32H17L15.6298 26.5191Z"></path>
                            <path class="lock"  d="M6 21V10C6 5.58172 9.58172 2 14 2V2C18.4183 2 22 5.58172 22 10V21"></path>
                            <path class="bling" d="M29 20L31 22"></path>
                            <path class="bling" d="M31.5 15H34.5"></path>
                            <path class="bling" d="M29 10L31 8"></path>
                        </svg>
                    </label>
                </div>
                <p id="mensajeCambioEstado" class="mb-3 mt-2" style="font-size:1.1rem;"></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button class="btn btn-primary" id="btnConfirmarEstado">
                    <i class="fas fa-check me-1"></i>Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="/SEGTRACK/Public/js/javascript/js/ValidacionParqueadero.js"></script>

<?php require_once __DIR__ . '/../layouts/parte_inferior_administrador.php'; ?>