<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-friends me-2"></i>Lista de Visitantes</h1>
        <a href="./Visitante.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Visitante
        </a>
    </div>

    <?php
    require_once __DIR__ . "../../../Core/conexion.php";
    $conexionObj = new Conexion();
    $conexion = $conexionObj->getConexion();

    $filtros = [];
    $params = [];


    if (!empty($_GET['identificacion'])) {
        $filtros[] = "IdentificacionVisitante LIKE :identificacion";
        $params[':identificacion'] = "%" . $_GET['identificacion'] . "%";
    }

    if (!empty($_GET['nombre'])) {
        $filtros[] = "NombreVisitante LIKE :nombre";
        $params[':nombre'] = "%" . $_GET['nombre'] . "%";
    }

    $where = "";
    if (count($filtros) > 0) {
        $where = "WHERE " . implode(" AND ", $filtros);
    }

    $sql = "SELECT * FROM visitante $where ORDER BY IdVisitante DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $visitantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>


    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Filtrar Visitantes</h6>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label for="identificacion" class="form-label">Identificación</label>
                    <input type="text" name="identificacion" id="identificacion" class="form-control" 
                           value="<?= $_GET['identificacion'] ?? '' ?>" placeholder="Número de identificación">
                </div>
                <div class="col-md-4">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" 
                           value="<?= $_GET['nombre'] ?? '' ?>" placeholder="Nombre del visitante">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i> Aplicar Filtro
                    </button>
                    <a href="VisitanteLista.php" class="btn btn-secondary">
                        <i class="fas fa-broom me-1"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Visitantes</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Identificación</th>
                            <th>Nombre</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($visitantes && count($visitantes) > 0): ?>
                            <?php foreach ($visitantes as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['IdVisitante']) ?></td>
                                    <td><?= htmlspecialchars($row['IdentificacionVisitante']) ?></td>
                                    <td><?= htmlspecialchars($row['NombreVisitante']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center py-4">
                                    <i class="fas fa-exclamation-circle fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">No hay visitantes registrados</p>
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
