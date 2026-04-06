<?php
// ======================================================================
// VISTA: SedeLista.php
// ======================================================================

require_once __DIR__ . '/../layouts/parte_superior_Administrador.php';
require_once __DIR__ . '/../../Model/ModeloSede.php';

$modeloSede = new ModeloSede();

// ── Filtros GET saneados ─────────────────────────────────────────────
$tipo        = trim($_GET['tipo']      ?? '');
$ciudad      = trim($_GET['ciudad']    ?? '');
$estado      = trim($_GET['estado']    ?? '');
$institutoId = trim($_GET['instituto'] ?? '');
$sedeId      = trim($_GET['sede']      ?? '');

// ── Datos para tabla ─────────────────────────────────────────────────
$sedes = $modeloSede->obtenerSedesFiltradas($tipo, $ciudad, $estado, $institutoId, $sedeId);

// ── Datos para selects (TODAS sin filtrar) ───────────────────────────
$todasSedes          = $modeloSede->obtenerSedes();
$instituciones_select = $modeloSede->obtenerInstituciones();

$tiposUnicos = array_unique(array_column($todasSedes, 'TipoSede'));
sort($tiposUnicos);

$instituciones = [];
foreach ($todasSedes as $s) {
    $id = (string)$s['IdInstitucion'];
    if (!isset($instituciones[$id])) {
        $instituciones[$id] = $s['NombreInstitucion'];
    }
}
asort($instituciones);

// ── Mapa sedes por institución para el JS (cascada) ──────────────────
$sedesPorInstitucion = [];
foreach ($todasSedes as $s) {
    $idInst = (string)$s['IdInstitucion'];
    $sedesPorInstitucion[$idInst][] = [
        'IdSede'     => (int)$s['IdSede'],
        'NombreSede' => $s['NombreSede']
    ];
}
?>

<div class="container-fluid px-4 py-4">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-building me-2"></i>Sedes Registradas
        </h1>
    </div>

    <!-- ══ CARD FILTROS ══ -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter mr-2"></i>Filtrar Sedes
            </h6>
            <a href="SedeLista.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-broom mr-1"></i>Limpiar filtros
            </a>
        </div>
        <div class="card-body">
            <form method="get">
                <div class="row align-items-end">

                    <!-- Institución -->
                    <div class="col-md-2 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-school mr-1 text-primary"></i>Institución
                        </label>
                        <select name="instituto" id="filtroInstituto" class="form-control">
                            <option value="">Todas</option>
                            <?php foreach ($instituciones as $idI => $nombreI) : ?>
                                <option value="<?= htmlspecialchars((string)$idI) ?>"
                                    <?= ($institutoId === (string)$idI) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($nombreI) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Sede (cascada dinámica) -->
                    <div class="col-md-2 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-building mr-1 text-primary"></i>Sede
                        </label>
                        <select name="sede" id="filtroSede" class="form-control"
                                data-selected="<?= htmlspecialchars($sedeId) ?>">
                            <option value="">Todas</option>
                        </select>
                    </div>

                    <!-- Ciudad -->
                    <div class="col-md-2 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-map-marker-alt mr-1 text-primary"></i>Ciudad
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" name="ciudad" class="form-control"
                                   value="<?= htmlspecialchars($ciudad) ?>"
                                   placeholder="Ej: Bogotá">
                        </div>
                    </div>

                    <!-- Estado -->
                    <div class="col-md-2 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-toggle-on mr-1 text-primary"></i>Estado
                        </label>
                        <select name="estado" class="form-control">
                            <option value="">Todos</option>
                            <option value="Activo"   <?= ($estado === 'Activo')   ? 'selected' : '' ?>>Activo</option>
                            <option value="Inactivo" <?= ($estado === 'Inactivo') ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>

                    <!-- Botón -->
                    <div class="col-md-2 mb-3">
                        <label class="form-label d-block invisible">.</label>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search mr-1"></i>Filtrar
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- ══ CARD TABLA ══ -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                Lista de Sedes (<?= count($sedes) ?>)
            </h6>
        </div>
        <div class="card-body table-responsive">
            <table id="tablaSedes"
                   class="table table-bordered table-hover table-striped align-middle text-center"
                   width="100%">
                <thead class="table-dark">
                    <tr>
                        <th>Tipo de Sede</th>
                        <th>Ciudad</th>
                        <th>Institución</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sedes as $fila) : ?>
                        <tr>
                            <td><?= htmlspecialchars($fila['TipoSede']) ?></td>
                            <td><?= htmlspecialchars($fila['Ciudad']) ?></td>
                            <td><?= htmlspecialchars($fila['NombreInstitucion']) ?></td>
                            <td class="text-center">
                                <?php if ($fila['Estado'] === 'Activo') : ?>
                                    <span id="badge-estado-<?= $fila['IdSede'] ?>"
                                          class="badge bg-success text-white px-3 py-2">Activo</span>
                                <?php else : ?>
                                    <span id="badge-estado-<?= $fila['IdSede'] ?>"
                                          class="badge text-white px-3 py-2"
                                          style="background-color:#60a5fa;">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <!-- Acciones: editar + cambiar estado -->
                            <td>
                                <div class="d-flex gap-1 justify-content-center">
                                    <button class="btn btn-outline-primary btn-sm btn-editar"
                                            title="Editar sede"
                                            data-id="<?= (int)$fila['IdSede'] ?>">
                                        <i class="fas fa-edit text-primary"></i>
                                    </button>

                                    <?php if ($fila['Estado'] === 'Activo') : ?>
                                        <button id="btn-estado-<?= $fila['IdSede'] ?>"
                                                class="btn btn-outline-warning btn-sm btn-estado"
                                                title="Desactivar sede"
                                                data-id="<?= (int)$fila['IdSede'] ?>">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    <?php else : ?>
                                        <button id="btn-estado-<?= $fila['IdSede'] ?>"
                                                class="btn btn-outline-success btn-sm btn-estado"
                                                title="Activar sede"
                                                data-id="<?= (int)$fila['IdSede'] ?>">
                                            <i class="fas fa-unlock"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ══ MODAL EDITAR SEDE ══ -->
<div class="modal fade" id="modalEditarSede" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Editar Sede
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarSede">
                    <input type="hidden" id="editIdSede">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Tipo de Sede <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="editTipoSede" class="form-control border-primary shadow-sm"
                                   maxlength="100" placeholder="Solo letras y espacios">
                            <small class="text-danger" id="errorTipoSede"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Ciudad <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="editCiudad" class="form-control border-primary shadow-sm"
                                   maxlength="100" placeholder="Solo letras y espacios">
                            <small class="text-danger" id="errorCiudad"></small>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">
                                Institución <span class="text-danger">*</span>
                            </label>
                            <select id="editInstitucion" class="form-control border-primary shadow-sm">
                                <option value="">Seleccione una institución</option>
                                <?php foreach ($instituciones_select as $inst) : ?>
                                    <option value="<?= (int)$inst['IdInstitucion'] ?>">
                                        <?= htmlspecialchars($inst['NombreInstitucion']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Los cambios actualizarán la información de esta sede en el sistema.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btnGuardarEdicion">
                    <i class="fas fa-save me-1"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior_Administrador.php'; ?>

<!-- CSS y JS necesarios -->
<link rel="stylesheet" href="../../../Public/vendor/datatables/dataTables.bootstrap4.css">
<link rel="stylesheet" href="../../../Public/css/Tablas.css">

<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../../Public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../../../Public/vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../../Public/js/javascript/js/ValidacionSedeLista.js"></script>
<script >
const urlControladorSede    = "../../Controller/ControladorSede.php";
const SEDES_POR_INSTITUCION = <?= json_encode($sedesPorInstitucion, JSON_UNESCAPED_UNICODE) ?>;
</script>