<?php 
/**
 * ========================================
 * LISTA DE FUNCIONARIOS - SEGTRACK
 * ========================================
 * Vista de la tabla de funcionarios con filtros
 */

require_once __DIR__ . '/../layouts/parte_superior.php'; 
?>

<!-- CSS personalizado para la tabla -->
<style>
    /* Botones de QR */
    .btn-qr {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .btn-qr:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        color: white;
    }
    
    .badge-estado {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.85rem;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fc;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .card {
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    /* Zebra */
.table-striped tbody tr:nth-of-type(odd) {
    background-color: #f8f9fc;
}

/* Hover suave */
.table-hover tbody tr:hover {
    background-color: #f1f3f8;
    transition: 0.2s ease-in-out;
}

/* Tamaño badge */
.badge {
    font-size: 0.85rem;
}

/* 🔥 Quitar flechas de ordenamiento */
table.dataTable thead .sorting:after,
table.dataTable thead .sorting:before,
table.dataTable thead .sorting_asc:after,
table.dataTable thead .sorting_desc:after {
    display: none !important;
}
</style>

<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-tie me-2"></i>Funcionarios Registrados
        </h1>
        <a href="./Funcionario.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Funcionario
        </a>
    </div>

<?php
require_once __DIR__ . '/../../Core/conexion.php';
$conexionObj = new Conexion();
$conn = $conexionObj->getConexion();

// SEDES
$sqlSede = "SELECT IdSede, TipoSede FROM sede ORDER BY TipoSede ASC";
$stmtSede = $conn->prepare($sqlSede);
$stmtSede->execute();
$sedes = $stmtSede->fetchAll(PDO::FETCH_ASSOC);
$mapSedes = [];
foreach ($sedes as $s) $mapSedes[$s['IdSede']] = $s['TipoSede'];

// FILTROS
$filtros = [];
$params = [];
if (!empty($_GET['cargo'])) {
    $filtros[] = "CargoFuncionario LIKE :cargo";
    $params[':cargo'] = '%' . $_GET['cargo'] . '%';
}
if (!empty($_GET['nombre'])) {
    $filtros[] = "NombreFuncionario LIKE :nombre";
    $params[':nombre'] = '%' . $_GET['nombre'] . '%';
}
if (!empty($_GET['estado'])) {
    $filtros[] = "Estado = :estado";
    $params[':estado'] = $_GET['estado'];
}
if (!empty($_GET['documento'])) {
    $filtros[] = "DocumentoFuncionario LIKE :documento";
    $params[':documento'] = '%' . $_GET['documento'] . '%';
}

$where = count($filtros) ? "WHERE " . implode(" AND ", $filtros) : "";
$sql = "SELECT * FROM funcionario $where ORDER BY IdFuncionario DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- TARJETA FILTROS -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-filter me-2"></i>Filtrar Funcionarios
        </h6>
    </div>
    <div class="card-body">
        <form method="get" class="row g-3">
            <div class="col-md-3">
                <label for="cargo" class="form-label">Cargo</label>
                <input type="text" name="cargo" id="cargo" class="form-control" 
                       value="<?= htmlspecialchars($_GET['cargo'] ?? '') ?>" placeholder="Buscar por cargo">
            </div>
            <div class="col-md-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control" 
                       value="<?= htmlspecialchars($_GET['nombre'] ?? '') ?>" placeholder="Buscar por nombre">
            </div>
            <div class="col-md-2">
                <label for="documento" class="form-label">Documento</label>
                <input type="text" name="documento" id="documento" class="form-control" 
                       value="<?= htmlspecialchars($_GET['documento'] ?? '') ?>" placeholder="Número">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>
                </button>
                <a href="FuncionarioLista.php" class="btn btn-secondary">
                    <i class="fas fa-broom"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- TARJETA TABLA -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-table me-2"></i>Lista de Funcionarios (<?= count($result) ?>)
        </h6>
    </div>
    <div class="card-body table-responsive">
        <table id="tablaFuncionarios" class="table table-hover table-bordered align-middle" width="100%">
            <thead class="table-dark text-center">
                <tr>
                    <th>QR</th>
                    <th>Cargo</th>
                    <th>Nombre</th>
                    <th>Sede</th>
                    <th>Teléfono</th>
                    <th>Documento</th>
                    <th>Correo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result) : ?>
                <?php foreach ($result as $row) : ?>
                    <tr id="fila-<?= $row['IdFuncionario'] ?>">
                        <!-- QR -->
                        <td class="text-center">
                            <?php if (!empty($row['QrCodigoFuncionario'])) : ?>
                                <button type="button" class="btn btn-sm btn-qr"
                                    onclick="verQR('<?= trim($row['QrCodigoFuncionario']) ?>', <?= (int)$row['IdFuncionario'] ?>)"
                                    title="Ver código QR">
                                    <i class="fas fa-qrcode"></i> Ver
                                </button>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Sin QR</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['CargoFuncionario']) ?></td>
                        <td><strong><?= htmlspecialchars($row['NombreFuncionario']) ?></strong></td>
                        <td><?= htmlspecialchars($mapSedes[$row['IdSede']] ?? 'Sin Sede') ?></td>
                        <td><?= htmlspecialchars($row['TelefonoFuncionario']) ?></td>
                        <td><?= htmlspecialchars($row['DocumentoFuncionario']) ?></td>
                        <td><?= htmlspecialchars($row['CorreoFuncionario']) ?></td>
                        <td class="text-center">
                            <?php $activo = $row['Estado'] === 'Activo'; ?>
                            <span class="badge <?= $activo ? 'bg-success' : 'bg-danger' ?> badge-estado" 
                                  id="badge-estado-<?= $row['IdFuncionario'] ?>">
                                <i class="fas <?= $activo ? 'fa-check-circle' : 'fa-times-circle' ?>"></i> <?= $row['Estado'] ?>
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                onclick='cambiarEstado(<?= $row["IdFuncionario"] ?>, "<?= $row["Estado"] ?>")'>
                                <i class="fas fa-exchange-alt"></i>
                            </button>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-primary"
                                onclick='cargarDatosEdicion(
                                    <?= $row["IdFuncionario"] ?>,
                                    <?= json_encode($row["CargoFuncionario"]) ?>,
                                    <?= json_encode($row["NombreFuncionario"]) ?>,
                                    <?= (int)$row["IdSede"] ?>,
                                    <?= json_encode($row["TelefonoFuncionario"]) ?>,
                                    <?= json_encode($row["DocumentoFuncionario"]) ?>,
                                    <?= json_encode($row["CorreoFuncionario"]) ?>
                                )'>
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="fas fa-exclamation-circle fa-3x text-muted mb-3 d-block"></i>
                        <p class="text-muted h5">No hay funcionarios registrados</p>
                        <a href="./Funcionario.php" class="btn btn-primary mt-3">
                            <i class="fas fa-plus me-2"></i>Registrar Primer Funcionario
                        </a>
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
                    <i class="fas fa-qrcode me-2"></i>Código QR - Funcionario #<span id="qrFuncionarioId"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img id="qrImagen" src="" class="img-fluid rounded shadow" style="max-width:350px;">
            </div>
            <div class="modal-footer">
                <a id="btnDescargarQR" class="btn btn-success" download>
                    <i class="fas fa-download me-1"></i> Descargar
                </a>
                <button class="btn btn-secondary" data-bs-dismiss="modal">
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
                <form id="formEditar">
                <input type="hidden" id="editId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editCargo" class="form-label">Cargo <span class="text-danger">*</span></label>
                            <select id="editCargo" class="form-control">
                                <option value="">Seleccione un cargo</option>
                                <option value="Personal Seguridad">Personal Seguridad</option>
                                <option value="Funcionario">Funcionario</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editNombre" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" id="editNombre" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="editSede" class="form-label">Sede <span class="text-danger">*</span></label>
                            <select id="editSede" class="form-control">
                                <option value="">Seleccione una sede</option>
                                <?php foreach ($sedes as $s) : ?>
                                    <option value="<?= $s['IdSede'] ?>"><?= htmlspecialchars($s['TipoSede']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="editTelefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                            <input type="tel" id="editTelefono" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="editDocumento" class="form-label">Documento <span class="text-danger">*</span></label>
                            <input type="text" id="editDocumento" class="form-control" required readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editCorreo" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" id="editCorreo" class="form-control" required readonly>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Al actualizar se regenerará automáticamente el código QR
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="btnGuardarCambios">
                    <i class="fas fa-save me-1"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>


<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../../Public/js/javascript/js/FuncionarioLista.js"></script>