<?php
/**
 * VISTA: ESTADO DE INSTITUCIONES (SOLO VISUALIZACIÓN)
 * Muestra nombre, tipo y estado de cada institución.
 * Sin acciones de edición ni cambio de estado.
 */

require_once __DIR__ . '/../layouts/parte_superior_supervisor.php';
require_once __DIR__ . '/../../Model/modeloinstituto.php';

$modeloInstituto = new ModeloInstituto();
$institutos      = $modeloInstituto->listarInstitutos();
?>

<div class="container-fluid px-4 py-4">

    <!-- ENCABEZADO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary">
            <i class="fas fa-school me-2"></i>Estado de Instituciones
        </h4>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">

                <table id="tablaEstadoInstitutos"
                       class="table table-bordered table-hover table-striped align-middle text-center"
                       width="100%">

                    <thead class="table-dark">
                        <tr>
                            <th>Nombre</th>
                            <th>NIT</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($institutos)): ?>
                            <?php foreach ($institutos as $fila): ?>
                                <tr>
                                    <td class="text-start"><?= htmlspecialchars($fila['NombreInstitucion']); ?></td>
                                    <td><?= htmlspecialchars($fila['Nit_Codigo']); ?></td>
                                    <td><?= htmlspecialchars($fila['TipoInstitucion']); ?></td>
                                    <td>
                                        <?php if ($fila['EstadoInstitucion'] === 'Activo'): ?>
                                            <span class="badge bg-success text-white">
                                                </i>Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="badge text-white" style="background-color:#60a5fa;">
                                                </i>Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-muted fst-italic">
                                    No hay instituciones registradas
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                </table>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior_supervisor.php'; ?>

<!-- CSS DataTables -->
<link rel="stylesheet"
      href="../../../Public/vendor/datatables/dataTables.bootstrap4.css">
<link rel="stylesheet" href="../../../Public/css/Tablas.css"> <!-- RUTA RELATIVA -->
<!-- JS -->
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="../../../Public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../../../Public/vendor/datatables/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function () {
    $('#tablaEstadoInstitutos').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        },
        pageLength: 10,
        order: [[3, 'asc']], // Ordena por Estado por defecto (Activo primero)
        columnDefs: [
            { orderable: false, targets: [3] } // La columna Estado no se ordena por click
        ]
    });
});
</script>