<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-boxes me-2"></i>Lista de Dotaciones</h1>
        <a href="./Dotaciones.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nueva Dotación
        </a>
    </div>

    <?php
     require_once __DIR__ . "../../../Core/conexion.php";
    $conexionObj = new Conexion();
    $conexion = $conexionObj->getConexion();

    $filtros = [];
    $params = [];
    
    if (!empty($_GET['estado'])) {
        $filtros[] = "EstadoDotacion = :estado";
        $params[':estado'] = $_GET['estado'];
    }
    if (!empty($_GET['tipo'])) {
        $filtros[] = "TipoDotacion = :tipo";
        $params[':tipo'] = $_GET['tipo'];
    }
    if (!empty($_GET['funcionario'])) {
        $filtros[] = "IdFuncionario = :funcionario";
        $params[':funcionario'] = $_GET['funcionario'];
    }

    $where = "";
    if (count($filtros) > 0) {
        $where = "WHERE " . implode(" AND ", $filtros);
    }

    $sql = "SELECT * FROM dotacion $where ORDER BY IdDotacion DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $dotaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Filtrar Dotaciones</h6>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="Buen estado" <?= (isset($_GET['estado']) && $_GET['estado'] == 'Buen estado') ? 'selected' : '' ?>>Buen estado</option>
                        <option value="Regular" <?= (isset($_GET['estado']) && $_GET['estado'] == 'Regular') ? 'selected' : '' ?>>Regular</option>
                        <option value="Dañado" <?= (isset($_GET['estado']) && $_GET['estado'] == 'Dañado') ? 'selected' : '' ?>>Dañado</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="tipo" class="form-label">Tipo</label>
                    <select name="tipo" id="tipo" class="form-select">
                        <option value="">Todos</option>
                        <option value="Uniforme" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Uniforme') ? 'selected' : '' ?>>Uniforme</option>
                        <option value="Equipo" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Equipo') ? 'selected' : '' ?>>Equipo</option>
                        <option value="Herramienta" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Herramienta') ? 'selected' : '' ?>>Herramienta</option>
                        <option value="Otro" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Otro') ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="funcionario" class="form-label">ID Funcionario</label>
                    <input type="text" name="funcionario" id="funcionario" class="form-control" value="<?= $_GET['funcionario'] ?? '' ?>" placeholder="ID Funcionario">
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i> Aplicar Filtro</button>
                    <a href="DotacionLista.php" class="btn btn-secondary"><i class="fas fa-broom me-1"></i> Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Dotaciones</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaDotacionesDT" class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Estado</th>
                            <th>Tipo</th>
                            <th>Novedad</th>
                            <th>Fecha Entrega</th>
                            <th>Fecha Devolución</th>
                            <th>ID Funcionario</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($dotaciones && count($dotaciones) > 0): ?>
                            <?php foreach ($dotaciones as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['IdDotacion']) ?></td>
                                    <td><?= htmlspecialchars($row['EstadoDotacion']) ?></td>
                                    <td><?= htmlspecialchars($row['TipoDotacion']) ?></td>
                                    <td><?= htmlspecialchars($row['NovedadDotacion']) ?></td>
                                    <td><?= htmlspecialchars($row['FechaEntrega']) ?></td>
                                    <td><?= htmlspecialchars($row['FechaDevolucion']) ?></td>
                                    <td><?= htmlspecialchars($row['IdFuncionario']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-exclamation-circle fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">No hay dotaciones registradas</p>
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

<!-- Script para activar DataTable -->
<script>
    $(document).ready(function () {
        $('#tablaDotacionesDT').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
            },
            pageLength: 10,
            responsive: true,
            order: [[0, "desc"]]
        });
    });
</script>
