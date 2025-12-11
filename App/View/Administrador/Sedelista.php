<?php
// ============================================================
// SedeLista.php
// Vista para el listado y gestión de Sedes.
// ============================================================

// Se incluye la parte superior de la plantilla del administrador.
require_once __DIR__ . '/../layouts/parte_superior_administrador.php';
// Se incluye el archivo de conexión a la base de datos (Core).
require_once __DIR__ . '/../../Core/Conexion.php';
// Se incluye el modelo para interactuar con la tabla de Sedes.
require_once __DIR__ . '/../../Model/modelosede.php';

// Se instancia el modelo de Sede.
$modeloSede = new ModeloSede();
// Se obtienen todas las sedes registradas para mostrarlas en la tabla.
$sedes = $modeloSede->obtenerSedes();
?>

<div class="container-fluid px-4 py-4">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-building me-2"></i>Listado de Sedes</h1>
        <a href="./Sede.php" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Registrar Sede
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Listado de Sedes</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">

                <table id="tablaSedes" class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Estado</th>
                            <th>Tipo de Sede</th>
                            <th>Ciudad</th>
                            <th>Institución</th>
                            <th style="width:140px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sedes as $fila): ?>
                        <tr data-id="<?= $fila['IdSede']; ?>">

                            <td>
                                <?php if ($fila['Estado'] == 'Activo'): ?>
                                    <span class="badge badge-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>

                            <td><?= htmlspecialchars($fila['TipoSede']); ?></td>
                            <td><?= htmlspecialchars($fila['Ciudad']); ?></td>
                            <td><?= htmlspecialchars($fila['NombreInstitucion']); ?></td>

                            <td class="text-center">
                                <a href="Sede.php?IdSede=<?= urlencode($fila['IdSede']); ?>" 
                                   class="btn btn-warning btn-sm me-1 btn-editar" 
                                   title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <button class="btn btn-sm btn-toggle-estado 
                                        <?= ($fila['Estado'] == 'Activo') ? 'btn-secondary' : 'btn-success'; ?>"
                                        data-id="<?= $fila['IdSede']; ?>"
                                        data-estado-actual="<?= $fila['Estado']; ?>"
                                        data-nombre="<?= htmlspecialchars($fila['TipoSede']); ?>"
                                        title="<?= ($fila['Estado'] == 'Activo') ? 'Desactivar' : 'Activar'; ?>">
                                    <i class="fas fa-<?= ($fila['Estado'] == 'Activo') ? 'ban' : 'check'; ?>"></i>
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
