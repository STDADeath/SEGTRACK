<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>
<?php require_once __DIR__ . "../../../Core/conexion.php"; ?>

<?php
$conexionObj = new Conexion();
$conexion    = $conexionObj->getConexion();

$filtros = [];
$params  = [];

if (!empty($_GET['turno'])) {
    $filtros[]        = "b.TurnoBitacora = :turno";
    $params[':turno'] = $_GET['turno'];
}
if (!empty($_GET['fecha'])) {
    $filtros[]        = "DATE(b.FechaBitacora) = :fecha";
    $params[':fecha'] = $_GET['fecha'];
}
if (!empty($_GET['funcionario'])) {
    $filtros[]              = "f.NombreFuncionario LIKE :funcionario";
    $params[':funcionario'] = '%' . $_GET['funcionario'] . '%';
}

$where = count($filtros) > 0 ? "WHERE " . implode(" AND ", $filtros) : "";

// JOIN con visitante y dispositivo para traer nombres
$sql = "SELECT b.IdBitacora,
               b.TurnoBitacora,
               b.NovedadesBitacora,
               b.FechaBitacora,
               b.ReporteBitacora,
               b.Estado,
               f.NombreFuncionario,
               v.NombreVisitante,
               CONCAT(d.TipoDispositivo, ' - ', d.MarcaDispositivo) AS NombreDispositivo
        FROM   bitacora b
        LEFT JOIN funcionario f  ON f.IdFuncionario = b.IdFuncionario
        LEFT JOIN visitante   v  ON v.IdVisitante   = b.IdVisitante
        LEFT JOIN dispositivo d  ON d.IdDispositivo = b.IdDispositivo
        $where
        ORDER  BY b.IdBitacora DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$bitacoras = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid px-4 py-4">

    <!-- Título -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-book me-2"></i> Lista de Bitácoras
        </h1>
        <a href="./Bitacora.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nueva Bitácora
        </a>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-1"></i> Filtrar Bitácoras
            </h6>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">

                <div class="col-md-3">
                    <label for="turno" class="form-label">Turno</label>
                    <select name="turno" id="turno" class="form-select">
                        <option value="">Todos</option>
                        <option value="Jornada mañana" <?= ($_GET['turno'] ?? '') === 'Jornada mañana' ? 'selected' : '' ?>>Jornada mañana</option>
                        <option value="Jornada tarde"  <?= ($_GET['turno'] ?? '') === 'Jornada tarde'  ? 'selected' : '' ?>>Jornada tarde</option>
                        <option value="Jornada noche"  <?= ($_GET['turno'] ?? '') === 'Jornada noche'  ? 'selected' : '' ?>>Jornada noche</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="fecha" class="form-label">Fecha</label>
                    <input type="date" id="fecha" name="fecha"
                           class="form-control" value="<?= htmlspecialchars($_GET['fecha'] ?? '') ?>">
                </div>

                <div class="col-md-3">
                    <label for="funcionario" class="form-label">Personal de Seguridad</label>
                    <input type="text" id="funcionario" name="funcionario"
                           class="form-control" placeholder="Buscar por nombre..."
                           value="<?= htmlspecialchars($_GET['funcionario'] ?? '') ?>">
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                    <a href="BitacoraLista.php" class="btn btn-secondary">
                        <i class="fas fa-broom me-1"></i> Limpiar
                    </a>
                </div>

            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Bitácoras Registradas</h6>
            <span class="badge bg-primary">
                <?= count($bitacoras) ?> registro<?= count($bitacoras) !== 1 ? 's' : '' ?>
            </span>
        </div>
        <div class="card-body table-responsive">
            <table id="tablaBitacorasDT"
                   class="table table-bordered table-hover table-striped align-middle text-center"
                   width="100%" cellspacing="0">

                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Turno</th>
                        <th>Novedades</th>
                        <th>Fecha y Hora</th>
                        <th>Personal Seguridad</th>
                        <th>Visitante</th>
                        <th>Dispositivo</th>
                        <th>PDF</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($bitacoras)): ?>
                        <?php foreach ($bitacoras as $row): ?>
                            <tr>

                                <!-- # -->
                                <td class="fw-bold text-muted"><?= $row['IdBitacora'] ?></td>

                                <!-- Turno -->
                                <td>
                                    <?php
                                    $colores = [
                                        'Jornada mañana' => 'warning',
                                        'Jornada tarde'  => 'info',
                                        'Jornada noche'  => 'dark',
                                    ];
                                    $color = $colores[$row['TurnoBitacora']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>">
                                        <?= htmlspecialchars($row['TurnoBitacora']) ?>
                                    </span>
                                </td>

                                <!-- Novedades recortadas con tooltip -->
                                <td class="text-start" style="max-width:240px;">
                                    <span title="<?= htmlspecialchars($row['NovedadesBitacora']) ?>">
                                        <?= htmlspecialchars(mb_strimwidth($row['NovedadesBitacora'], 0, 70, '...')) ?>
                                    </span>
                                </td>

                                <!-- Fecha -->
                                <td class="text-nowrap">
                                    <?= date('d/m/Y H:i', strtotime($row['FechaBitacora'])) ?>
                                </td>

                                <!-- Personal de seguridad -->
                                <td>
                                    <i class="fas fa-user-shield text-primary me-1"></i>
                                    <?= htmlspecialchars($row['NombreFuncionario'] ?? '—') ?>
                                </td>

                                <!-- Visitante (nombre) -->
                                <td>
                                    <?php if (!empty($row['NombreVisitante'])): ?>
                                        <small>
                                            <i class="fas fa-user text-success me-1"></i>
                                            <?= htmlspecialchars($row['NombreVisitante']) ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="badge bg-info text-white">No aplica</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Dispositivo (tipo - marca) -->
                                <td>
                                    <?php if (!empty($row['NombreDispositivo'])): ?>
                                        <small>
                                            <i class="fas fa-laptop text-secondary me-1"></i>
                                            <?= htmlspecialchars($row['NombreDispositivo']) ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="badge bg-info text-white">No aplica</span>
                                    <?php endif; ?>
                                </td>

                                <!-- PDF -->
                                <td>
                                    <?php if (!empty($row['ReporteBitacora'])): ?>
                                        <a href="../../../Public/<?= htmlspecialchars($row['ReporteBitacora']) ?>"
                                           target="_blank"
                                           class="btn btn-sm btn-outline-danger"
                                           title="Ver PDF adjunto">
                                            <i class="fas fa-file-pdf"></i> Ver
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-info text-white">Sin PDF</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Estado -->
                                <td>
                                    <?php if (($row['Estado'] ?? '') === 'Activo'): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-exclamation-circle fa-2x text-muted mb-2 d-block"></i>
                                <span class="text-muted">No hay bitácoras registradas con los filtros seleccionados</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>

            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>

<script>
$(document).ready(function () {
    $('#tablaBitacorasDT').DataTable({
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        },
        pageLength: 10,
        responsive: true,
        order:      [[0, "desc"]],
        columnDefs: [
            { orderable: false, targets: [7] } // PDF no ordenable
        ]
    });
});
</script>