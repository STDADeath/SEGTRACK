<?php require_once __DIR__ . '/../Plantilla/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-list me-2"></i>Lista de Bitácoras</h1>
        <a href="../view/Bitacora_Registrar.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nueva Bitácora
        </a>
    </div>

    <?php
    require_once "../controller/Conexion/conexion.php";
    $conexion = new Conexion();
    $conn = $conexion->getConexion();

    $sql = "SELECT * FROM bitacora ORDER BY IdBitacora DESC";
    $result = $conn->query($sql);
    ?>

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
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['IdBitacora']; ?></td>
                                    <td><?php echo $row['TurnoBitacora']; ?></td>
                                    <td><?php echo $row['NovedadesBitacora']; ?></td>
                                    <td><?php echo $row['FechaBitacora']; ?></td>
                                    <td><?php echo $row['IdFuncionario']; ?></td>
                                    <td><?php echo $row['IdIngreso']; ?></td>
                                    <td><?php echo $row['IdDispositivo']; ?></td>
                                    <td><?php echo $row['IdVisitante']; ?></td>
                                </tr>
                            <?php endwhile; ?>
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

<?php require_once __DIR__ . '/../Plantilla/parte_inferior.php'; ?>

