<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-friends me-2"></i>Lista de Visitantes
        </h1>
        <a href="./Visitante.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Visitante
        </a>
    </div>

    <?php
    require_once __DIR__ . "../../../Core/conexion.php";
    $conexionObj = new Conexion();
    $conexion    = $conexionObj->getConexion();

    $filtros = [];
    $params  = [];

    if (!empty($_GET['identificacion'])) {
        $filtros[]                 = "v.IdentificacionVisitante LIKE :identificacion";
        $params[':identificacion'] = "%" . $_GET['identificacion'] . "%";
    }
    if (!empty($_GET['nombre'])) {
        $filtros[]         = "v.NombreVisitante LIKE :nombre";
        $params[':nombre'] = "%" . $_GET['nombre'] . "%";
    }
    if (!empty($_GET['estado'])) {
        $filtros[]         = "v.Estado = :estado";
        $params[':estado'] = $_GET['estado'];
    }
    if (!empty($_GET['idInstitucion'])) {
        $filtros[]                = "s.IdInstitucion = :idInstitucion";
        $params[':idInstitucion'] = (int)$_GET['idInstitucion'];
    }

    $where = count($filtros) > 0 ? "WHERE " . implode(" AND ", $filtros) : "";

    $sql  = "SELECT v.*, s.TipoSede, s.Ciudad, i.NombreInstitucion
             FROM visitante v
             LEFT JOIN sede s ON v.IdSede = s.IdSede
             LEFT JOIN institucion i ON s.IdInstitucion = i.IdInstitucion
             $where
             ORDER BY v.IdVisitante DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $visitantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Instituciones para el filtro
    $stmtInst      = $conexion->query("SELECT IdInstitucion, NombreInstitucion FROM institucion WHERE EstadoInstitucion = 'Activo' ORDER BY NombreInstitucion");
    $instituciones = $stmtInst->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!-- ── Filtros ──────────────────────────────────── -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Filtrar Visitantes</h6>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label for="identificacion" class="form-label">Identificación</label>
                    <input type="text" name="identificacion" id="identificacion" class="form-control"
                           value="<?= htmlspecialchars($_GET['identificacion'] ?? '') ?>"
                           placeholder="Número de identificación">
                </div>
                <div class="col-md-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-control"
                           value="<?= htmlspecialchars($_GET['nombre'] ?? '') ?>"
                           placeholder="Nombre del visitante">
                </div>
                <div class="col-md-2">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="Activo"   <?= ($_GET['estado'] ?? '') === 'Activo'   ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= ($_GET['estado'] ?? '') === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="idInstitucion" class="form-label">Institución</label>
                    <select name="idInstitucion" id="idInstitucion" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($instituciones as $inst): ?>
                            <option value="<?= $inst['IdInstitucion'] ?>"
                                <?= ($_GET['idInstitucion'] ?? '') == $inst['IdInstitucion'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($inst['NombreInstitucion']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                    <a href="VisitanteLista.php" class="btn btn-secondary">
                        <i class="fas fa-broom me-1"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Tabla ────────────────────────────────────── -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Visitantes</h6>
            <span class="badge bg-primary">
                <?= count($visitantes) ?> registro<?= count($visitantes) !== 1 ? 's' : '' ?>
            </span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaVisitantesDT"
                       class="table table-bordered table-hover table-striped align-middle text-center"
                       width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th>Identificación</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Institución</th>
                            <th>Sede</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($visitantes && count($visitantes) > 0): ?>
                            <?php foreach ($visitantes as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['IdentificacionVisitante']) ?></td>
                                    <td>
                                        <i class="fas fa-user text-primary me-1"></i>
                                        <?= htmlspecialchars($row['NombreVisitante']) ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['CorreoVisitante'])): ?>
                                            <a href="mailto:<?= htmlspecialchars($row['CorreoVisitante']) ?>">
                                                <?= htmlspecialchars($row['CorreoVisitante']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-info text-white">No aplica</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['NombreInstitucion'])): ?>
                                            <i class="fas fa-university text-secondary me-1"></i>
                                            <?= htmlspecialchars($row['NombreInstitucion']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['TipoSede'])): ?>
                                            <span class="badge bg-light text-dark border">
                                                <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                                <?= htmlspecialchars($row['TipoSede']) ?>
                                                <?= !empty($row['Ciudad']) ? '· ' . htmlspecialchars($row['Ciudad']) : '' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $row['Estado'] === 'Activo' ? 'success' : 'secondary' ?>">
                                            <?= htmlspecialchars($row['Estado']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-exclamation-circle fa-2x text-muted mb-2 d-block"></i>
                                    <span class="text-muted">No hay visitantes registrados</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>

<script>
$(document).ready(function () {
    $('#tablaVisitantesDT').DataTable({
        language: { url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json" },
        pageLength: 10,
        responsive: true,
        order: [[0, "desc"]],
        columnDefs: [{ orderable: false, targets: [2] }]
    });
});
</script>