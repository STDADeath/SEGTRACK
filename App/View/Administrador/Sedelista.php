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
                            <th>Estado</th> <!-- PENÚLTIMA -->
                            <th>Acciones</th> <!-- ÚLTIMA -->
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($sedes as $fila): ?>
                        <tr>

                            <!-- TIPO -->
                            <td><?= htmlspecialchars($fila['TipoSede']); ?></td>

                            <!-- CIUDAD -->
                            <td><?= htmlspecialchars($fila['Ciudad']); ?></td>

                            <!-- INSTITUCIÓN -->
                            <td><?= htmlspecialchars($fila['NombreInstitucion']); ?></td>

                            <!-- ESTADO -->
                            <td>
                                <?php if ($fila['Estado'] === 'Activo'): ?>
                                    <span class="badge bg-success px-3 py-2">
                                        Activo
                                    </span>
                                <?php else: ?>
                                    <span class="badge px-3 py-2"
                                          style="background-color:#60a5fa;">
                                        Inactivo
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- ACCIONES -->
                            <td>

                                <!-- BOTÓN EDITAR CUADRADO -->
                                <a href="Sede.php?IdSede=<?= $fila['IdSede']; ?>"
                                   class="btn btn-outline-primary btn-sm rounded-3"
                                   style="width:40px; height:40px; display:inline-flex; align-items:center; justify-content:center;"
                                   title="Editar">

                                    <i class="fas fa-pen-to-square"></i>
                                </a>

                                <!-- BOTÓN CAMBIAR ESTADO CUADRADO -->
                                <button class="btn btn-outline-dark btn-sm rounded-3 btn-toggle-estado ms-1"
                                        style="width:40px; height:40px; display:inline-flex; align-items:center; justify-content:center;"
                                        data-id="<?= $fila['IdSede']; ?>"
                                        data-estado-actual="<?= $fila['Estado']; ?>"
                                        title="Cambiar Estado">

                                    <i class="fas fa-sync-alt"></i>

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

<link rel="stylesheet" href="../../../Public/vendor/datatables/dataTables.bootstrap4.css">
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="../../../Public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../../../Public/vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="../../../Public/js/javascript/js/ValidacionSedeLista.js"></script>
