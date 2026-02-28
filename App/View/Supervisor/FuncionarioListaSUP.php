<?php
/**
 * VISTA: LISTA DE FUNCIONARIOS - SUPERVISOR (Solo lectura)
 */
require_once __DIR__ . '/../layouts/parte_superior_supervisor.php';

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

<div class="container-fluid px-4 py-4">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-tie me-2"></i>Funcionarios Registrados
        </h1>
    </div>

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
                           value="<?= htmlspecialchars($_GET['cargo'] ?? '') ?>"
                           placeholder="Buscar por cargo">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control"
                           value="<?= htmlspecialchars($_GET['nombre'] ?? '') ?>"
                           placeholder="Buscar por nombre">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Documento</label>
                    <input type="text" name="documento" class="form-control"
                           value="<?= htmlspecialchars($_GET['documento'] ?? '') ?>"
                           placeholder="Número">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <a href="FuncionarioListaSUP.php" class="btn btn-secondary">
                        <i class="fas fa-broom"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- TABLA -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0 text-primary fw-bold">
                Lista de Funcionarios (<?= count($result) ?>)
            </h5>
        </div>
        <div class="card-body table-responsive">
            <table id="tablaFuncionarios" class="table table-hover align-middle" width="100%">
                <thead class="text-white" style="background-color:#5f636e;">
                    <tr>
                        <th>Cargo</th>
                        <th>Nombre</th>
                        <th>Sede</th>
                        <th>Teléfono</th>
                        <th>Documento</th>
                        <th>Correo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result): ?>
                        <?php foreach ($result as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['CargoFuncionario']) ?></td>
                                <td><?= htmlspecialchars($row['NombreFuncionario']) ?></td>
                                <td><?= htmlspecialchars($mapSedes[$row['IdSede']] ?? 'Sin Sede') ?></td>
                                <td><?= htmlspecialchars($row['TelefonoFuncionario']) ?></td>
                                <td><?= htmlspecialchars($row['DocumentoFuncionario']) ?></td>
                                <td><?= htmlspecialchars($row['CorreoFuncionario']) ?></td>
                                <td class="text-center">
                                    <?php if ($row['Estado'] === 'Activo'): ?>
                                        <span class="badge bg-success text-white">
                                            </i>Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-primary text-white">
                                            </i>Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                No hay funcionarios registrados
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior_supervisor.php'; ?>

<!-- CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="../../../Public/css/Tablas.css">

<!-- JS -->
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#tablaFuncionarios').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        },
        pageLength: 10,
        order: []
    });
});
</script>