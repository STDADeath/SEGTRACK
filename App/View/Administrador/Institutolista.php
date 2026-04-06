<?php
// ======================================================================
// VISTA: ListaInstituciones.php (CORREGIDA)
// ======================================================================

require_once __DIR__ . '/../layouts/parte_superior_administrador.php';
require_once __DIR__ . '/../../Model/modeloinstituto.php';

$modeloInstituto = new ModeloInstituto();

// ── Filtros GET saneados ─────────────────────────────────────────────────────
$nombre    = trim($_GET['nombre']    ?? '');
$tipo      = trim($_GET['tipo']      ?? '');
$estado    = trim($_GET['estado']    ?? '');
$direccion = trim($_GET['direccion'] ?? '');

// ── Datos para tabla: consulta filtrada en el modelo ────────────────────────
$institutos = $modeloInstituto->listarInstitutosFiltrados($nombre, $tipo, $estado, $direccion);

// ── Datos para selects: TODOS sin filtrar ───────────────────────────────────
$todosInstitutos = $modeloInstituto->listarInstitutos();
$tiposUnicos     = array_unique(array_column($todosInstitutos, 'TipoInstitucion'));
sort($tiposUnicos);
?>

<div class="container-fluid px-4 py-4">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-school me-2"></i>Instituciones Registradas
        </h1>
        <a href="Instituto.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Registrar
        </a>
    </div>

    <!-- ══ CARD FILTROS ══ -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter mr-2"></i>Filtrar Instituciones
            </h6>
            <a href="InstitutoLista.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-broom mr-1"></i>Limpiar filtros
            </a>
        </div>
        <div class="card-body">
            <form method="get">
                <div class="row align-items-end">

                    <!-- Nombre -->
                    <div class="col-md-2 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-school mr-1 text-primary"></i>Nombre
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" name="nombre" class="form-control"
                                   value="<?= htmlspecialchars($nombre) ?>"
                                   placeholder="Ej: Colegio Nacional">
                        </div>
                    </div>

                    <!-- Tipo -->
                    <div class="col-md-2 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-tags mr-1 text-primary"></i>Tipo
                        </label>
                        <select name="tipo" class="form-control">
                            <option value="">Todos</option>
                            <?php foreach ($tiposUnicos as $t) : ?>
                                <option value="<?= htmlspecialchars($t) ?>"
                                    <?= ($tipo === $t) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Dirección -->
                    <div class="col-md-2 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-map-marker-alt mr-1 text-primary"></i>Dirección
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" name="direccion" class="form-control"
                                   value="<?= htmlspecialchars($direccion) ?>"
                                   placeholder="Ej: Calle 10">
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
                Lista de Instituciones (<?= count($institutos) ?>)
            </h6>
        </div>
        <div class="card-body table-responsive">
            <table id="tablaInstitutos"
                   class="table table-bordered table-hover table-striped align-middle text-center"
                   width="100%">
                <thead class="table-dark">
                    <tr>
                        <th class="text-start">Nombre</th>
                        <th>NIT</th>
                        <th>Tipo</th>
                        <th>Dirección</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($institutos as $fila) : ?>
                        <tr>
                            <td class="text-start"><?= htmlspecialchars($fila['NombreInstitucion']) ?></td>
                            <td><?= htmlspecialchars($fila['Nit_Codigo']) ?></td>
                            <td><?= htmlspecialchars($fila['TipoInstitucion']) ?></td>
                            <td>
                                <?php $dir = trim($fila['DireccionInstitucion'] ?? ''); ?>
                                <?= $dir !== ''
                                    ? htmlspecialchars($dir)
                                    : '<span class="badge bg-secondary text-white">Sin dirección</span>' ?>
                            </td>
                            <td class="text-center">
                                <?php if ($fila['EstadoInstitucion'] === 'Activo') : ?>
                                    <span id="badge-estado-<?= $fila['IdInstitucion'] ?>"
                                          class="badge bg-success text-white px-3 py-2">Activo</span>
                                <?php else : ?>
                                    <span id="badge-estado-<?= $fila['IdInstitucion'] ?>"
                                          class="badge bg-primary text-white px-3 py-2">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <!-- Acciones: editar + candado -->
                            <td>
                                <div class="d-flex gap-2 justify-content-center">

                                    <!-- EDITAR (ahora con atributos data-* y clase específica) -->
                                    <button class="btn btn-outline-primary btn-sm btn-editar-instituto"
                                            data-id-editar="<?= $fila['IdInstitucion'] ?>"
                                            data-nombre="<?= htmlspecialchars($fila['NombreInstitucion']) ?>"
                                            data-nit="<?= htmlspecialchars($fila['Nit_Codigo']) ?>"
                                            data-tipo="<?= htmlspecialchars($fila['TipoInstitucion']) ?>"
                                            data-direccion="<?= htmlspecialchars(trim($fila['DireccionInstitucion'] ?? '')) ?>"
                                            data-estado="<?= $fila['EstadoInstitucion'] ?>"
                                            title="Editar institución">
                                        <i class="fas fa-edit text-primary"></i>
                                    </button>

                                    <!-- CANDADO (cambiar estado) -->
                                    <?php if ($fila['EstadoInstitucion'] === 'Activo') : ?>
                                        <button id="btn-estado-<?= $fila['IdInstitucion'] ?>"
                                                class="btn btn-outline-warning btn-sm btn-toggle-estado"
                                                title="Desactivar institución"
                                                data-id="<?= $fila['IdInstitucion'] ?>"
                                                data-estado="<?= $fila['EstadoInstitucion'] ?>">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    <?php else : ?>
                                        <button id="btn-estado-<?= $fila['IdInstitucion'] ?>"
                                                class="btn btn-outline-success btn-sm btn-toggle-estado"
                                                title="Activar institución"
                                                data-id="<?= $fila['IdInstitucion'] ?>"
                                                data-estado="<?= $fila['EstadoInstitucion'] ?>">
                                            <i class="fas fa-lock-open"></i>
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


<!-- ══ MODAL EDITAR INSTITUCIÓN ══ -->
<div class="modal fade" id="modalEditarInstituto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Editar Institución
                </h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formEditarInstituto">

                    <input type="hidden" id="editIdInstituto">
                    <input type="hidden" id="editEstadoInstituto">

                    <div class="row g-3 mb-3">

                        <!-- Nombre -->
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">
                                Nombre de la Institución <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="editNombreInstituto"
                                   class="form-control border-primary shadow-sm"
                                   maxlength="100"
                                   placeholder="Solo letras y espacios">
                        </div>

                        <!-- Tipo -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Tipo <span class="text-danger">*</span>
                            </label>
                            <select id="editTipoInstituto" class="form-control border-primary shadow-sm">
                                <option value="">Seleccione un tipo</option>
                                <option value="Universidad">Universidad</option>
                                <option value="Colegio">Colegio</option>
                                <option value="Empresa">Empresa</option>
                                <option value="ONG">ONG</option>
                                <option value="Hospital">Hospital</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                    </div>

                    <div class="row g-3 mb-3">

                        <!-- NIT: solo lectura -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">NIT</label>
                            <input type="text" id="editNitInstituto"
                                   class="form-control"
                                   style="background-color:#e9ecef; cursor:not-allowed; color:#495057;"
                                   readonly tabindex="-1">
                            <small class="text-muted">
                                <i class="fas fa-lock me-1"></i>No editable
                            </small>
                        </div>

                        <!-- Dirección -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Dirección <small class="text-muted">(opcional)</small>
                            </label>
                            <input type="text" id="editDireccionInstituto"
                                   class="form-control border-primary shadow-sm"
                                   maxlength="150"
                                   placeholder="Ej: Calle 45 # 23-10">
                            <small class="text-muted">
                                Letras, números, espacios, guiones, # y comas.
                            </small>
                        </div>

                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        El NIT no puede modificarse. Si necesitas cambiarlo, registra una nueva institución.
                    </div>

                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm"
                        data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary btn-sm"
                        id="btnGuardarInstituto">
                    <i class="fas fa-save me-1"></i> Guardar Cambios
                </button>
            </div>

        </div>
    </div>
</div>


<?php require_once __DIR__ . '/../layouts/parte_inferior_administrador.php'; ?>

<!-- CSS -->
<link rel="stylesheet" href="../../../Public/vendor/datatables/dataTables.bootstrap4.css">
<link rel="stylesheet" href="../../../Public/css/Tablas.css">

<!-- JS -->
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../../Public/js/javascript/js/ValidacionListainstitutos.js"></script>