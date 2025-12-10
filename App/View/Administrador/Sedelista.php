<?php
// ============================================================
// SedeLista.php
// ============================================================

require_once __DIR__ . '/../layouts/parte_superior_administrador.php';
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
                        <?php
                        require_once __DIR__ . '/../../Core/Conexion.php';

                        try {
                            $conexion = new Conexion();
                            $db = $conexion->getConexion();

                            // Traer también el nombre de la institución
                            $sql = "SELECT s.IdSede, s.TipoSede, s.Ciudad, s.Estado, 
                                           i.NombreInstitucion 
                                    FROM sede s
                                    INNER JOIN institucion i ON i.IdInstitucion = s.IdInstitucion
                                    ORDER BY IdSede DESC";
                            $stmt = $db->prepare($sql);
                            $stmt->execute();

                            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)):
                        ?>
                                <tr data-id="<?php echo $fila['IdSede']; ?>">

                                    <!-- Estado -->
                                    <td>
                                        <?php if ($fila['Estado'] == 'Activo'): ?>
                                            <span class="badge badge-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>

                                    <td><?php echo htmlspecialchars($fila['TipoSede']); ?></td>
                                    <td><?php echo htmlspecialchars($fila['Ciudad']); ?></td>
                                    <td><?php echo htmlspecialchars($fila['NombreInstitucion']); ?></td>

                                    <!-- Acciones -->
                                    <td class="text-center">

                                        <!-- Botón Editar -->
                                        <a href="Sede.php?IdSede=<?php echo urlencode($fila['IdSede']); ?>" 
                                           class="btn btn-warning btn-sm me-1" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <!-- Botón Cambiar Estado -->
                                        <button class="btn btn-sm btn-toggle-estado 
                                                <?php echo ($fila['Estado'] == 'Activo') ? 'btn-secondary' : 'btn-success'; ?>"
                                                data-id="<?php echo htmlspecialchars($fila['IdSede']); ?>"
                                                data-estado-actual="<?php echo htmlspecialchars($fila['Estado']); ?>"
                                                data-nombre="<?php echo htmlspecialchars($fila['TipoSede']); ?>"
                                                title="<?php echo ($fila['Estado'] == 'Activo') ? 'Desactivar' : 'Activar'; ?>">
                                            <i class="fas fa-<?php echo ($fila['Estado'] == 'Activo') ? 'ban' : 'check'; ?>"></i>
                                        </button>

                                    </td>

                                </tr>
                        <?php
                            endwhile;
                        } catch (PDOException $e) {
                            echo '<tr><td colspan="5" class="text-center text-danger">Error al cargar datos: ' 
                                 . htmlspecialchars($e->getMessage()) . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>

</div>

<?php
require_once __DIR__ . '/../layouts/parte_inferior_administrador.php';
?>

<!-- DATATABLES -->
<link rel="stylesheet" href="../../../Public/vendor/datatables/dataTables.bootstrap4.css">
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="../../../Public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../../../Public/vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="../../../Public/js/javascript/js/ValidacionesSedeLista.js"></script>

