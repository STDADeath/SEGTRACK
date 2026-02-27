<?php
/**
 * VISTA: LISTA DE SEDES (SOLO VISUALIZACIÓN)
 * Muestra datos y permite filtrado con DataTables.
 */

require_once __DIR__ . '/../layouts/parte_superior.php';
require_once __DIR__ . '/../../Model/modelosede.php';

$modeloSede = new ModeloSede();
$sedes      = $modeloSede->obtenerSedes();
?>

<div class="container-fluid px-4 py-4">

    <!-- ENCABEZADO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary">
            <i class="fas fa-building me-2"></i>Lista de Sedes
        </h4>
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
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($sedes)): ?>
                            <?php foreach ($sedes as $fila): ?>
                                <tr>
                                    <td><?= htmlspecialchars($fila['TipoSede']); ?></td>
                                    <td><?= htmlspecialchars($fila['Ciudad']); ?></td>
                                    <td><?= htmlspecialchars($fila['NombreInstitucion']); ?></td>

                                    <td>
                                        <?php if ($fila['Estado'] === 'Activo'): ?>
                                            <span class="badge bg-success text-white">
                                                Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="badge text-white" style="background-color:#60a5fa;">
                                                Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No hay sedes registradas</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                </table>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>

<!-- CSS DataTables -->
<link rel="stylesheet"
      href="../../../Public/vendor/datatables/dataTables.bootstrap4.css">

<!-- JS -->
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="../../../Public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../../../Public/vendor/datatables/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    $('#tablaSedes').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        },
        pageLength: 10,
        order: [],
        columnDefs: [
            { orderable: false, targets: 3 } // evita ordenar por Estado si quieres
        ]
    });
});
</script>