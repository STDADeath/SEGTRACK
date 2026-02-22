<?php 
/**
 * ========================================
 * LISTA DE FUNCIONARIOS - SEGTRACK
 * ========================================
 */
require_once __DIR__ . '/../layouts/parte_superior_administrador.php'; 

// ✅ CORRECCIÓN: $baseQR solo '/qr' para no duplicar 'Public/' en la URL
$baseQR = '/qr';
?>

<style>
    .table-hover tbody tr:hover {
        background-color: #f8f9fc;
        transition: background-color 0.2s;
    }
    .card {
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    /* =============================================
       BOTONES QR: Ver / Enviar
       → Para AGRANDAR: sube font-size y padding
       → Para ACHICAR:  baja font-size y padding
    ============================================= */
    .btn-qr-ver,
    .btn-qr-enviar {
        font-size: 0.72rem;
        padding: 2px 7px;
        line-height: 1.5;
    }

    /* =============================================
       BOTONES ACCIÓN: Lápiz / Candado
       → Para AGRANDAR: sube width, height y font-size
       → Para ACHICAR:  bájalos
       → border-radius: controla cuánto se redondean las esquinas
    ============================================= */
    .btn-accion {
        width: 36px;
        height: 36px;
        padding: 0;
        border-radius: 8px;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .table-striped tbody tr:nth-of-type(odd) { background-color: #f8f9fc; }
    .table-hover tbody tr:hover              { background-color: #f1f3f8; transition: 0.2s ease-in-out; }
    .badge { font-size: 0.85rem; }
    table.dataTable thead .sorting:after,
    table.dataTable thead .sorting:before,
    table.dataTable thead .sorting_asc:after,
    table.dataTable thead .sorting_desc:after { display: none !important; }
</style>

<div class="container-fluid px-4 py-4">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-tie me-2"></i>Funcionarios Registrados
        </h1>
        <a href="./FuncionariosADM.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Funcionario
        </a>
    </div>

<?php
require_once __DIR__ . '/../../Core/conexion.php';
$conexionObj = new Conexion();
$conn = $conexionObj->getConexion();

$sqlSede = "SELECT IdSede, TipoSede FROM sede ORDER BY TipoSede ASC";
$stmtSede = $conn->prepare($sqlSede);
$stmtSede->execute();
$sedes = $stmtSede->fetchAll(PDO::FETCH_ASSOC);
$mapSedes = [];
foreach ($sedes as $s) $mapSedes[$s['IdSede']] = $s['TipoSede'];

$filtros = [];
$params  = [];
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
$sql   = "SELECT * FROM funcionario $where ORDER BY IdFuncionario DESC";
$stmt  = $conn->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- FILTROS -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-filter me-2"></i>Filtrar Funcionarios
        </h6>
    </div>
    <div class="card-body">
        <form method="get" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Cargo</label>
                <input type="text" name="cargo" class="form-control"
                       value="<?= htmlspecialchars($_GET['cargo'] ?? '') ?>" placeholder="Buscar por cargo">
            </div>
            <div class="col-md-3">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control"
                       value="<?= htmlspecialchars($_GET['nombre'] ?? '') ?>" placeholder="Buscar por nombre">
            </div>
            <div class="col-md-2">
                <label class="form-label">Documento</label>
                <input type="text" name="documento" class="form-control"
                       value="<?= htmlspecialchars($_GET['documento'] ?? '') ?>" placeholder="Número">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                <a href="FuncionarioListaADM.php" class="btn btn-secondary"><i class="fas fa-broom"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- TABLA -->
<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h5 class="mb-0 text-primary fw-bold">Lista de Funcionarios (<?= count($result) ?>)</h5>
    </div>
    <div class="card-body table-responsive">
        <table id="tablaFuncionarios" class="table table-hover align-middle" width="100%">
            <thead class="text-white" style="background-color:#5f636e;">
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
                    <tr>

                        <!-- ===== QR: Ver y Enviar AL LADO (d-flex) ===== -->
                        <td>
                            <?php if (!empty($row['QrCodigoFuncionario'])) : ?>
                                <?php
                                /*
                                 * ✅ CORRECCIÓN RUTA QR PARA "Ver":
                                 *
                                 * La BD guarda el QR como:  "qr/Qr_Func/QR-FUNC-109-xxx.png"
                                 * $baseQR = '/qr'
                                 *
                                 * Normalizamos el valor de BD para que arranque con '/'
                                 * y NO empiece ya con '/qr' (evitar doble prefijo).
                                 *
                                 * Resultado esperado: "/qr/Qr_Func/QR-FUNC-109-xxx.png"
                                 * El JS añade: origin + /SEGTRACK + /Public  → URL completa
                                 */
                                $qrBD = $row['QrCodigoFuncionario'];

                                // Si la BD guarda "qr/Qr_Func/..." lo convertimos a "/qr/Qr_Func/..."
                                if (!str_starts_with($qrBD, '/')) {
                                    $qrBD = '/' . $qrBD;
                                }

                                // Si por algún motivo el valor ya trae '/qr/qr/...' lo corregimos
                                // (caso raro, pero seguro)
                                $rutaQR = $qrBD;
                                ?>
                                <div class="d-flex gap-1 align-items-center flex-nowrap">

                                    <!-- VER QR: abre modal con la imagen -->
                                    <button class="btn btn-outline-success btn-qr-ver"
                                            title="Ver código QR"
                                            onclick="verQR('<?= htmlspecialchars($rutaQR) ?>', <?= (int)$row['IdFuncionario'] ?>)">
                                        <i class="fas fa-qrcode me-1"></i>Ver
                                    </button>

                                    <!-- ✅ ENVIAR QR: solo pasa IdFuncionario
                                         El controlador (enviarQRPorCorreo) consulta la BD
                                         y obtiene correo + ruta QR internamente -->
                                    <button class="btn btn-outline-primary btn-qr-enviar"
                                            title="Enviar QR por correo"
                                            onclick="enviarQR(<?= (int)$row['IdFuncionario'] ?>)">
                                        <i class="fas fa-envelope me-1"></i>Enviar
                                    </button>

                                </div>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark" style="font-size:0.7rem;">Sin QR</span>
                            <?php endif; ?>
                        </td>

                        <td><?= htmlspecialchars($row['CargoFuncionario']) ?></td>
                        <td><?= htmlspecialchars($row['NombreFuncionario']) ?></td>
                        <td><?= htmlspecialchars($mapSedes[$row['IdSede']] ?? 'Sin Sede') ?></td>
                        <td><?= htmlspecialchars($row['TelefonoFuncionario']) ?></td>
                        <td><?= htmlspecialchars($row['DocumentoFuncionario']) ?></td>
                        <td><?= htmlspecialchars($row['CorreoFuncionario']) ?></td>

                        <!-- ===== ESTADO: Activo=verde | Inactivo=AZUL ===== -->
                        <td class="text-center">
                            <?php if ($row['Estado'] === 'Activo'): ?>
                                <span id="badge-estado-<?= $row['IdFuncionario'] ?>" class="badge bg-success">
                                    <i class="fas fa-check-circle"></i> Activo
                                </span>
                            <?php else: ?>
                                <span id="badge-estado-<?= $row['IdFuncionario'] ?>" class="badge bg-primary">
                                    <i class="fas fa-lock"></i> Inactivo
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- ===== ACCIONES: lápiz + candado cuadrados ===== -->
                        <td>
                            <div class="d-flex gap-2">

                                <!-- EDITAR -->
                                <button class="btn btn-outline-primary btn-accion"
                                        title="Editar funcionario"
                                        onclick='cargarDatosEdicion(
                                            <?= $row["IdFuncionario"] ?>,
                                            <?= json_encode($row["CargoFuncionario"]) ?>,
                                            <?= json_encode($row["NombreFuncionario"]) ?>,
                                            <?= (int)$row["IdSede"] ?>,
                                            <?= json_encode($row["TelefonoFuncionario"]) ?>,
                                            <?= json_encode($row["DocumentoFuncionario"]) ?>,
                                            <?= json_encode($row["CorreoFuncionario"]) ?>
                                        )'>
                                    <i class="fas fa-edit text-primary"></i>
                                </button>

                                <!-- CANDADO: amarillo=activo (click desactiva) | verde=inactivo (click activa) -->
                                <?php if ($row['Estado'] === 'Activo'): ?>
                                    <button id="btn-estado-<?= $row['IdFuncionario'] ?>"
                                            class="btn btn-outline-warning btn-accion"
                                            title="Desactivar funcionario"
                                            onclick="cambiarEstado(<?= $row['IdFuncionario'] ?>,'Activo')">
                                        <i class="fas fa-lock"></i>
                                    </button>
                                <?php else: ?>
                                    <button id="btn-estado-<?= $row['IdFuncionario'] ?>"
                                            class="btn btn-outline-success btn-accion"
                                            title="Activar funcionario"
                                            onclick="cambiarEstado(<?= $row['IdFuncionario'] ?>,'Inactivo')">
                                        <i class="fas fa-lock-open"></i>
                                    </button>
                                <?php endif; ?>

                            </div>
                        </td>

                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9" class="text-center py-4">No hay funcionarios registrados</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===== MODAL VER QR ===== -->
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

<!-- ===== MODAL EDITAR ===== -->
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Funcionario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditar">
                    <input type="hidden" id="editId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cargo <span class="text-danger">*</span></label>
                            <select id="editCargo" class="form-control">
                                <option value="">Seleccione un cargo</option>
                                <option value="Personal Seguridad">Personal Seguridad</option>
                                <option value="Funcionario">Funcionario</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" id="editNombre" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Sede <span class="text-danger">*</span></label>
                            <select id="editSede" class="form-control">
                                <option value="">Seleccione una sede</option>
                                <?php foreach ($sedes as $s) : ?>
                                    <option value="<?= $s['IdSede'] ?>"><?= htmlspecialchars($s['TipoSede']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Teléfono <span class="text-danger">*</span></label>
                            <input type="tel" id="editTelefono" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Documento</label>
                            <input type="text" id="editDocumento" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" id="editCorreo" class="form-control" readonly>
                    </div>
                    <div class="alert alert-info">
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

<?php require_once __DIR__ . '/../layouts/parte_inferior_administrador.php'; ?>

<!-- CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- JS EN ORDEN CORRECTO -->
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../../Public/js/javascript/js/FuncionarioLista.js"></script>