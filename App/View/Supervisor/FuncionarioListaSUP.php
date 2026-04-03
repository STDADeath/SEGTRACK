<?php  
/**
 * ========================================
 * LISTA DE FUNCIONARIOS - SEGTRACK (ADMIN)
 * ✅ Candado para cambiar estado agregado en acciones
 * ========================================
 */
require_once __DIR__ . '/../layouts/parte_superior_Supervisor.php'; 
?>
<div class="container-fluid px-4 py-4">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-tie me-2"></i>Funcionarios Registrados
        </h1>
        <a href="Funcionario.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Funcionario
        </a>
    </div>

<?php
require_once __DIR__ . '/../../Core/conexion.php';
require_once __DIR__ . "/../../Controller/ControladorSede.php";

$conexionObj = new Conexion();
$conn        = $conexionObj->getConexion();

$controladorSede = new ControladorSede();

// ── Sedes activas para SEDES_POR_INSTITUCION_LISTA ──
$sql = "SELECT IdSede, TipoSede, IdInstitucion FROM sede";
$stmt = $conn->prepare($sql);
$stmt->execute();
$sedesActivas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── JOIN sede + institución para mapas de apoyo ──
$sqlSedeCompleta = "
    SELECT s.IdSede, s.TipoSede, s.IdInstitucion, i.NombreInstitucion
    FROM sede s
    JOIN institucion i ON s.IdInstitucion = i.IdInstitucion
    ORDER BY s.TipoSede ASC
";
$stmtSedeCompleta = $conn->prepare($sqlSedeCompleta);
$stmtSedeCompleta->execute();
$sedesCompletas = $stmtSedeCompleta->fetchAll(PDO::FETCH_ASSOC);

$mapSedes         = [];
$mapInstPorSede   = [];
$mapInstIdPorSede = [];
$instituciones    = [];
$sedesFiltro      = [];

foreach ($sedesCompletas as $s) {
    $mapSedes[$s['IdSede']]             = $s['TipoSede'];
    $mapInstPorSede[$s['IdSede']]       = $s['NombreInstitucion'];
    $mapInstIdPorSede[$s['IdSede']]     = $s['IdInstitucion'];
    $instituciones[$s['IdInstitucion']] = $s['NombreInstitucion'];
    $sedesFiltro[$s['IdSede']]          = $s['TipoSede'];
}

// ── Filtros GET ──
$filtros   = [];
$params    = [];
$filtros[] = "1 = 1";

if (!empty($_GET['nombre'])) {
    $filtros[] = "f.NombreFuncionario LIKE :nombre";
    $params[':nombre'] = '%' . $_GET['nombre'] . '%';
}
if (!empty($_GET['documento'])) {
    $filtros[] = "f.DocumentoFuncionario LIKE :documento";
    $params[':documento'] = '%' . $_GET['documento'] . '%';
}
if (!empty($_GET['correo'])) {
    $filtros[] = "f.CorreoFuncionario LIKE :correo";
    $params[':correo'] = '%' . $_GET['correo'] . '%';
}
if (!empty($_GET['sede'])) {
    $filtros[] = "f.IdSede = :sede";
    $params[':sede'] = $_GET['sede'];
}
if (!empty($_GET['instituto'])) {
    $filtros[] = "s.IdInstitucion = :instituto";
    $params[':instituto'] = $_GET['instituto'];
}

$where = "WHERE " . implode(" AND ", $filtros);

$sql = "SELECT f.*, s.IdInstitucion
        FROM funcionario f
        LEFT JOIN sede s ON f.IdSede = s.IdSede
        $where
        ORDER BY f.IdFuncionario DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- FILTROS -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-light d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-filter mr-2"></i>Filtrar Funcionarios
        </h6>
        <a href="FuncionarioListaSUP.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-broom mr-1"></i>Limpiar filtros
        </a>
    </div>
    <div class="card-body">
        <form method="get">
            <div class="row align-items-end">

                <!-- Nombre -->
                <div class="col-md-2 mb-3">
                    <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                        <i class="fas fa-user mr-1 text-primary"></i>Nombre
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" name="nombre" class="form-control"
                               value="<?= htmlspecialchars($_GET['nombre'] ?? '') ?>"
                               placeholder="Buscar nombre">
                    </div>
                </div>

                <!-- Documento -->
                <div class="col-md-2 mb-3">
                    <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                        <i class="fas fa-id-card mr-1 text-primary"></i>Documento
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" name="documento" class="form-control"
                               value="<?= htmlspecialchars($_GET['documento'] ?? '') ?>"
                               placeholder="Número">
                    </div>
                </div>

                <!-- Correo -->
                <div class="col-md-2 mb-3">
                    <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                        <i class="fas fa-envelope mr-1 text-primary"></i>Correo
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" name="correo" class="form-control"
                               value="<?= htmlspecialchars($_GET['correo'] ?? '') ?>"
                               placeholder="Buscar correo">
                    </div>
                </div>

                <!-- Institución -->
                <div class="col-md-2 mb-3">
                    <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                        <i class="fas fa-school mr-1 text-primary"></i>Institución
                    </label>
                    <select name="instituto" class="form-control">
                        <option value="">Todas</option>
                        <?php foreach ($instituciones as $idI => $nombreI) : ?>
                            <option value="<?= $idI ?>"
                                <?= (isset($_GET['instituto']) && $_GET['instituto'] == $idI) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($nombreI) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Sede — arranca con todas, JS filtra al elegir institución -->
                <div class="col-md-2 mb-3">
                    <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                        <i class="fas fa-building mr-1 text-primary"></i>Sede
                    </label>
                    <select name="sede" class="form-control">
                        <option value="">Todas</option>
                        <?php foreach ($sedesFiltro as $idS => $nombreS) : ?>
                            <option value="<?= $idS ?>"
                                <?= (isset($_GET['sede']) && $_GET['sede'] == $idS) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($nombreS) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Botón Filtrar -->
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

<!-- TABLA -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-light">
        <h6 class="m-0 font-weight-bold text-primary">
            Lista de Funcionarios (<?= count($result) ?>)
        </h6>
    </div>
    <div class="card-body table-responsive">
        <table id="tablaFuncionarios"
               class="table table-bordered table-hover table-striped align-middle text-center"
               width="100%">
            <thead class="table-dark">
                <tr>
                    <th>QR</th>
                    <th>Cargo</th>
                    <th>Nombre</th>
                    <th>Institución</th>
                    <th>Sede</th>
                    <th>Teléfono</th>
                    <th>Documento</th>
                    <th>Correo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($result)) : ?>
                <?php foreach ($result as $row) : ?>
                <?php
                    $idFunc     = (int)$row['IdFuncionario'];
                    $idSede     = (int)$row['IdSede'];
                    $idInst     = (int)($mapInstIdPorSede[$idSede] ?? 0);
                    $nombreInst = $mapInstPorSede[$idSede] ?? '';
                    $nombreSede = $mapSedes[$idSede]        ?? 'Sin Sede';
                    $qrBD       = $row['QrCodigoFuncionario'] ?? '';
                    $rutaQR     = (!empty($qrBD) && !str_starts_with($qrBD, '/'))
                                    ? '/' . $qrBD : $qrBD;
                    $estado     = $row['Estado'];
                ?>
                <tr>

                    <!-- QR -->
                    <td>
                        <?php if (!empty($rutaQR)) : ?>
                            <div class="d-flex gap-1 align-items-center flex-nowrap justify-content-center">
                                <button class="btn btn-outline-success btn-sm"
                                        title="Ver código QR"
                                        onclick="verQR('<?= htmlspecialchars($rutaQR) ?>', <?= $idFunc ?>)">
                                    <i class="fas fa-qrcode me-1"></i>Ver
                                </button>
                                <button class="btn btn-outline-primary btn-sm"
                                        title="Enviar QR por correo"
                                        onclick="enviarQR(<?= $idFunc ?>)">
                                    <i class="fas fa-envelope me-1"></i>Enviar
                                </button>
                            </div>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark" style="font-size:0.7rem;">Sin QR</span>
                        <?php endif; ?>
                    </td>

                    <td><?= htmlspecialchars($row['CargoFuncionario']) ?></td>
                    <td><?= htmlspecialchars($row['NombreFuncionario']) ?></td>
                    <td><?= htmlspecialchars($nombreInst ?: 'Sin Institución') ?></td>
                    <td><?= htmlspecialchars($nombreSede) ?></td>
                    <td><?= htmlspecialchars($row['TelefonoFuncionario']) ?></td>
                    <td><?= htmlspecialchars($row['DocumentoFuncionario']) ?></td>
                    <td><?= htmlspecialchars($row['CorreoFuncionario']) ?></td>

              
         <!-- Estado -->
                <td class="text-center">
                    <?php if ($estado === 'Activo'): ?>
                        <span class="badge bg-success text-white px-3 py-2">Activo</span>
                    <?php else: ?>
                        <span class="badge bg-primary text-white px-3 py-2">Inactivo</span>
                    <?php endif; ?>
                </td>

                    <!-- ✅ ACCIONES: Editar + Candado estado -->

                <td>
                    <div class="d-flex gap-1 justify-content-center">

                        <!-- Editar — siempre azul sin importar el estado -->
                        <button class="btn btn-outline-primary btn-sm"
                                title="Editar funcionario"
                                onclick='cargarDatosEdicion(
                                    <?= $idFunc                                      ?>,
                                    <?= json_encode($row["CargoFuncionario"])         ?>,
                                    <?= json_encode($row["NombreFuncionario"])        ?>,
                                    <?= $idSede                                      ?>,
                                    <?= $idInst                                      ?>,
                                    <?= json_encode($nombreInst)                     ?>,
                                    <?= json_encode($row["TelefonoFuncionario"])     ?>,
                                    <?= json_encode($row["DocumentoFuncionario"])    ?>,
                                    <?= json_encode($row["CorreoFuncionario"])       ?>
                                )'>
                            <i class="fas fa-edit"></i>
                        </button>

                        <!-- Candado: Activo = verde / Inactivo = amarillo -->
                        <?php if ($estado === 'Activo'): ?>
                            <button class="btn btn-sm btn-success"
                                    title="Desactivar funcionario"
                                    onclick="cambiarEstado(<?= $idFunc ?>, 'Activo')">
                                <i class="fas fa-lock-open text-white"></i>
                            </button>
                        <?php else: ?>
                            <button class="btn btn-sm btn-warning"
                                    title="Activar funcionario"
                                    onclick="cambiarEstado(<?= $idFunc ?>, 'Inactivo')">
                                <i class="fas fa-lock text-white"></i>
                            </button>
                        <?php endif; ?>

                    </div>
                </td>

                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="text-center py-4">
                        <i class="fas fa-exclamation-circle fa-2x text-muted mb-2 d-block"></i>
                        <p class="text-muted mb-0">No hay funcionarios registrados con los filtros seleccionados</p>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<!-- MODAL VER QR -->
<div class="modal fade" id="modalVerQR" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-qrcode me-2"></i>Código QR — Funcionario #<span id="qrFuncionarioId"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img id="qrImagen" src="" alt="Código QR"
                     class="img-fluid rounded shadow" style="max-width:350px;">
            </div>
            <div class="modal-footer">
                <a id="btnDescargarQR" class="btn btn-success btn-sm" download>
                    <i class="fas fa-download me-1"></i> Descargar
                </a>
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>


<!-- MODAL EDITAR -->
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Editar Funcionario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formEditar" novalidate>

                    <input type="hidden" id="editId">
                    <input type="hidden" id="editIdInstitucion">

                    <!-- FILA 1: Cargo | Nombre -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Cargo <span class="text-danger">*</span>
                            </label>
                            <select id="editCargo" class="form-control border-primary shadow-sm">
                                <option value="">Seleccione un cargo</option>
                                <option value="Personal Seguridad">Personal Seguridad</option>
                                <option value="Funcionario">Funcionario</option>
                            </select>
                            <div class="invalid-feedback">Seleccione un cargo válido.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Nombre Completo <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="editNombre"
                                   class="form-control border-primary shadow-sm"
                                   placeholder="Ej: Juan Pérez">
                            <div class="invalid-feedback">Mínimo 3 letras. Solo letras y espacios.</div>
                        </div>
                    </div>

                    <!-- FILA 2: Institución | Sede | Teléfono -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Institución</label>
                            <input type="text" id="editInstitucionTexto"
                                   class="form-control"
                                   style="background-color:#e9ecef; cursor:not-allowed; color:#495057;"
                                   readonly tabindex="-1">
                            <small class="text-muted">
                                <i class="fas fa-lock me-1"></i>No editable
                            </small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Sede <span class="text-danger">*</span>
                            </label>
                            <select id="editSede" class="form-control border-primary shadow-sm">
                                <option value="">Cargando sedes...</option>
                            </select>
                            <div class="invalid-feedback">Seleccione una sede.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Teléfono <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="editTelefono"
                                   class="form-control border-primary shadow-sm"
                                   maxlength="10"
                                   placeholder="Ej: 3001234567"
                                   inputmode="numeric">
                            <div class="invalid-feedback">Exactamente 10 dígitos numéricos.</div>
                        </div>
                    </div>

                    <!-- FILA 3: Documento | Correo -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Documento</label>
                            <input type="text" id="editDocumento"
                                   class="form-control"
                                   style="background-color:#e9ecef; cursor:not-allowed; color:#495057;"
                                   readonly tabindex="-1">
                            <small class="text-muted">
                                <i class="fas fa-lock me-1"></i>No editable
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Correo Electrónico</label>
                            <input type="email" id="editCorreo"
                                   class="form-control"
                                   style="background-color:#e9ecef; cursor:not-allowed; color:#495057;"
                                   readonly tabindex="-1">
                            <small class="text-muted">
                                <i class="fas fa-lock me-1"></i>No editable
                            </small>
                        </div>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Al actualizar se regenerará automáticamente el código QR
                    </div>

                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btnGuardarCambios">
                    <i class="fas fa-save me-1"></i> Guardar Cambios
                </button>
            </div>

        </div>
    </div>
</div>


<?php require_once __DIR__ . '/../layouts/parte_inferior_Supervisor.php'; ?>

<!-- CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../../Public/css/Tablas.css">

<!-- ✅ JSON ANTES del JS — obligatorio para que la cascada funcione -->
<script>
    const SEDES_POR_INSTITUCION_LISTA = <?php
        $sedesPorInstLista = [];
        foreach ($sedesActivas as $sede) {
            $idInst = (int)$sede['IdInstitucion'];
            $sedesPorInstLista[$idInst][] = [
                'IdSede'     => $sede['IdSede'],
                'NombreSede' => $sede['TipoSede']
            ];
        }
        echo json_encode($sedesPorInstLista, JSON_UNESCAPED_UNICODE);
    ?>;
</script>

<!-- JS EN ORDEN CORRECTO -->
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../../Public/js/javascript/js/FuncionariosLista.js"></script>