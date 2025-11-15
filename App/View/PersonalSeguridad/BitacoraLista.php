<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-list me-2"></i>Lista de Bitácoras</h1>
        <a href="./Bitacora.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nueva Bitácora
        </a>
    </div>

    <?php
    require_once __DIR__ . "../../../Core/conexion.php";
    $conexionObj = new Conexion();
    $conexion = $conexionObj->getConexion();


    $filtros = [];
    $params = [];

    if (!empty($_GET['turno'])) {
        $filtros[] = "TurnoBitacora = :turno";
        $params[':turno'] = $_GET['turno'];
    }
    if (!empty($_GET['fecha'])) {
        $filtros[] = "DATE(FechaBitacora) = :fecha";
        $params[':fecha'] = $_GET['fecha'];
    }
    if (!empty($_GET['funcionario'])) {
        $filtros[] = "IdFuncionario = :funcionario";
        $params[':funcionario'] = $_GET['funcionario'];
    }

    $where = "";
    if (count($filtros) > 0) {
        $where = "WHERE " . implode(" AND ", $filtros);
    }

    $sql = "SELECT * FROM bitacora $where ORDER BY IdBitacora DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $bitacoras = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Filtrar Bitácoras</h6>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label for="turno" class="form-label">Turno</label>
                    <select name="turno" id="turno" class="form-select">
                        <option value="">Todos</option>
                        <option value="Jornada mañana" <?= (isset($_GET['turno']) && $_GET['turno'] == 'Jornada mañana') ? 'selected' : '' ?>>Jornada mañana</option>
                        <option value="Jornada tarde" <?= (isset($_GET['turno']) && $_GET['turno'] == 'Jornada tarde') ? 'selected' : '' ?>>Jornada tarde</option>
                        <option value="Jornada noche" <?= (isset($_GET['turno']) && $_GET['turno'] == 'Jornada noche') ? 'selected' : '' ?>>Jornada noche</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="fecha" class="form-label">Fecha</label>
                    <input type="date" name="fecha" id="fecha" class="form-control" value="<?= $_GET['fecha'] ?? '' ?>">
                </div>
                <div class="col-md-3">
                    <label for="funcionario" class="form-label">ID Funcionario</label>
                    <input type="text" name="funcionario" id="funcionario" class="form-control" value="<?= $_GET['funcionario'] ?? '' ?>" placeholder="ID Funcionario">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i> Aplicar Filtro</button>
                    <a href="BitacoraLista.php" class="btn btn-secondary"><i class="fas fa-broom me-1"></i> Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Bitácoras</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Turno</th>
                            <th>Novedades</th>
                            <th>Fecha</th>
                            <th>ID Funcionario</th>
                            <th>ID Ingreso</th>
                            <th>ID Dispositivo</th>
                            <th>ID Visitante</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($bitacoras && count($bitacoras) > 0): ?>
                            <?php foreach ($bitacoras as $row): ?>
                                <tr>
                                    <td><?= $row['IdBitacora'] ?></td>
                                    <td><?= $row['TurnoBitacora'] ?></td>
                                    <td><?= $row['NovedadesBitacora'] ?></td>
                                    <td><?= $row['FechaBitacora'] ?></td>
                                    <td><?= $row['IdFuncionario'] ?></td>
                                    <td><?= $row['IdIngreso'] ?></td>
                                    <td><?= $row['IdDispositivo'] ?></td>
                                    <td><?= $row['IdVisitante'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-exclamation-circle fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">No hay bitácoras registradas</p>
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
