<?php require_once __DIR__ . '/../Plantilla/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-users me-2"></i>Lista de Funcionarios</h1>
        <a href="Funcionario_Registrar.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Funcionario
        </a>
    </div>

    <?php
    require_once "../Core/conexion.php";
    $conexionObj = new Conexion();
    $conexion = $conexionObj->getConexion();

    $filtros = [];
    $params = [];

    if (!empty($_GET['nombre'])) {
        $filtros[] = "NombreFuncionario LIKE :nombre";
        $params[':nombre'] = "%" . $_GET['nombre'] . "%";
    }
    if (!empty($_GET['cargo'])) {
        $filtros[] = "CargoFuncionaro LIKE :cargo";
        $params[':cargo'] = "%" . $_GET['cargo'] . "%";
    }
    if (!empty($_GET['sede'])) {
        $filtros[] = "IdSede = :sede";
        $params[':sede'] = $_GET['sede'];
    }

    $where = "";
    if (count($filtros) > 0) {
        $where = "WHERE " . implode(" AND ", $filtros);
    }

    $sql = "SELECT * FROM funcionario $where ORDER BY IdFuncionario DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Filtrar Funcionarios</h6>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Buscar por nombre" value="<?= $_GET['nombre'] ?? '' ?>">
                </div>
                <div class="col-md-4">
                    <label for="cargo" class="form-label">Cargo</label>
                    <input type="text" name="cargo" id="cargo" class="form-control" placeholder="Buscar por cargo" value="<?= $_GET['cargo'] ?? '' ?>">
                </div>
                <div class="col-md-2">
                    <label for="sede" class="form-label">ID Sede</label>
                    <input type="number" name="sede" id="sede" class="form-control" value="<?= $_GET['sede'] ?? '' ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i> Filtrar</button>
                    <a href="FuncionarioLista.php" class="btn btn-secondary"><i class="fas fa-broom me-1"></i> Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Funcionarios Registrados</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Cargo</th>
                            <th>Documento</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Sede</th>
                            <th>QR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($funcionarios && count($funcionarios) > 0): ?>
                            <?php foreach ($funcionarios as $f): ?>
                                <tr>
                                    <td><?= $f['IdFuncionario'] ?></td>
                                    <td><?= $f['NombreFuncionario'] ?></td>
                                    <td><?= $f['CargoFuncionario'] ?></td>
                                    <td><?= $f['DocumentoFuncionario'] ?></td>
                                    <td><?= $f['TelefonoFuncionario'] ?></td>
                                    <td><?= $f['CorreoFuncionario'] ?></td>
                                    <td><?= $f['IdSede'] ?></td>
                                    <td>
                                        <?php if (!empty($f['QrCodigoFuncionario'])): ?>
                                            <img src="../<?= htmlspecialchars($f['QrCodigoFuncionario']) ?>" alt="QR" width="80">
                                        <?php else: ?>
                                            <span class="text-muted">No generado</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-exclamation-circle fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">No hay funcionarios registrados</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../Plantilla/parte_inferior.php'; ?>
