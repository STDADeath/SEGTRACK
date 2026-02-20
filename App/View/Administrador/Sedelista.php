<?php
require_once __DIR__ . '/../layouts/parte_superior_administrador.php';
require_once __DIR__ . '/../../Model/modelosede.php';

$modeloSede = new ModeloSede();
$sedes = $modeloSede->obtenerSedes();
?>

<div class="container-fluid px-4 py-4">

    <!-- TÍTULO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary">
            <i class="fas fa-building me-2"></i>Lista de Sedes
        </h4>

        <a href="Sede.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Registrar
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">

                <table id="tablaSedes"
                       class="table table-bordered table-hover table-striped align-middle text-center"
                       width="100%">

                    <thead class="table-dark">
                        <tr>
                            <th>Tipo</th>
                            <th>Ciudad</th>
                            <th>Institución</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($sedes as $fila): ?>
                        <tr>

                            <td><?= htmlspecialchars($fila['TipoSede']); ?></td>
                            <td><?= htmlspecialchars($fila['Ciudad']); ?></td>
                            <td><?= htmlspecialchars($fila['NombreInstitucion']); ?></td>

                            <!-- ESTADO -->
                            <td>
                                <?php if ($fila['Estado'] === 'Activo'): ?>
                                    <span class="badge bg-success px-3 py-2 estado-badge">
                                        Activo
                                    </span>
                                <?php else: ?>
                                    <span class="badge px-3 py-2 estado-badge"
                                          style="background-color:#60a5fa;">
                                        Inactivo
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- ACCIONES -->
                            <td>

                                <!-- BOTÓN EDITAR -->
                                <a href="Sede.php?IdSede=<?= $fila['IdSede']; ?>"
                                   class="btn btn-outline-primary btn-sm"
                                   style="width:38px;height:38px;
                                          display:inline-flex;
                                          align-items:center;
                                          justify-content:center;
                                          border-radius:6px;"
                                   title="Editar">
                                    <i class="fas fa-pen"></i>
                                </a>

                                <!-- BOTÓN ESTADO -->
                               <button class="btn btn-sm btn-estado ms-1"
                                        style="width:38px;height:38px;
                                            display:inline-flex;
                                            align-items:center;
                                            justify-content:center;
                                            border-radius:6px;
                                            border:1px solid #d4af37;
                                            background:#fff8dc;"
                                        data-id="<?= $fila['IdSede']; ?>"
                                        title="Cambiar Estado">

                                    <?php if ($fila['Estado'] === 'Activo'): ?>
                                        <i class="fas fa-lock text-warning"></i>
                                    <?php else: ?>
                                        <i class="fas fa-unlock text-success"></i>
                                    <?php endif; ?>

                                </button>


                            </td>

                        </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior_administrador.php'; ?>

<!-- DATATABLES -->
<link rel="stylesheet" href="../../../Public/vendor/datatables/dataTables.bootstrap4.css">
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="../../../Public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../../../Public/vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="../../../Public/js/javascript/js/ValidacionSedeLista.js"></script>

<style>
.table-striped tbody tr:nth-of-type(odd)  { background-color: #f8f9fc; }
.table-hover   tbody tr:hover             { background-color: #f1f3f8; transition: 0.2s ease-in-out; }
.badge { font-size: 0.85rem; }
table.dataTable thead .sorting:after,
table.dataTable thead .sorting:before,
table.dataTable thead .sorting_asc:after,
table.dataTable thead .sorting_desc:after { display: none !important; }
</style>
