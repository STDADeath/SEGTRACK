<?php
/**
 * VISTA: LISTA DE INSTITUCIONES
 */
require_once __DIR__ . '/../layouts/parte_superior_administrador.php';
require_once __DIR__ . '/../../Core/Conexion.php';
?>

<div class="container-fluid px-4 py-4">

    <!-- TÍTULO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary">
            <i class="fas fa-school me-2"></i>Lista de Instituciones
        </h4>
        <a href="Instituto.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Registrar
        </a>
    </div>

    <!-- CARD -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaInstitutos"
                       class="table table-bordered table-hover table-striped align-middle text-center"
                       width="100%">

                    <thead class="table-dark">
                        <tr>
                            <th>Nombre</th>
                            <th>NIT</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        try {
                            $conexion = new Conexion();
                            $db       = $conexion->getConexion();
                            $sql      = "SELECT * FROM institucion ORDER BY IdInstitucion DESC";
                            $stmt     = $db->prepare($sql);
                            $stmt->execute();

                            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)):
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($fila['NombreInstitucion']); ?></td>
                                <td><?= htmlspecialchars($fila['Nit_Codigo']); ?></td>
                                <td><?= htmlspecialchars($fila['TipoInstitucion']); ?></td>

                                <!-- ESTADO -->
                                <td>
                                    <?php if ($fila['EstadoInstitucion'] === 'Activo'): ?>
                                        <span class="badge bg-success px-3 py-2">Activo</span>
                                    <?php else: ?>
                                        <span class="badge px-3 py-2"
                                              style="background-color:#60a5fa;">Inactivo</span>
                                    <?php endif; ?>
                                </td>

                                <!-- ACCIONES -->
                                <td>
                                    <!-- EDITAR -->
                                    <a href="Instituto.php?IdInstitucion=<?= $fila['IdInstitucion']; ?>"
                                       class="btn btn-outline-primary btn-sm rounded-3"
                                       style="width:40px;height:40px;display:inline-flex;align-items:center;justify-content:center;"
                                       title="Editar">
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>

                                    <!-- TOGGLE ESTADO -->
                                    <!-- ✅ Se agrega data-estado para leerlo directo sin buscar el badge -->
                                    <button class="btn btn-sm btn-toggle-estado ms-1"
                                            style="width:40px;height:40px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;border:1px solid #d4af37;background:#fff8dc;"
                                            data-id="<?= $fila['IdInstitucion']; ?>"
                                            data-estado="<?= $fila['EstadoInstitucion']; ?>"
                                            title="<?= $fila['EstadoInstitucion'] === 'Activo' ? 'Desactivar' : 'Activar'; ?>">
                                        <?php if ($fila['EstadoInstitucion'] === 'Activo'): ?>
                                            <i class="fas fa-lock text-warning"></i>
                                        <?php else: ?>
                                            <i class="fas fa-unlock text-success"></i>
                                        <?php endif; ?>
                                    </button>
                                </td>
                            </tr>
                        <?php
                            endwhile;
                        } catch (PDOException $e) {
                            echo '<tr><td colspan="5" class="text-danger text-center">
                                    Error al cargar los datos
                                  </td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior_administrador.php'; ?>

<!-- CSS -->
<link rel="stylesheet" href="../../../Public/vendor/datatables/dataTables.bootstrap4.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- JS -->
<script src="../../../Public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../../../Public/vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="../../../Public/js/javascript/js/ValidacionInstitutoLista.js"></script>


<style>
.table-striped tbody tr:nth-of-type(odd)  { background-color: #f8f9fc; }
.table-hover   tbody tr:hover             { background-color: #f1f3f8; transition: 0.2s ease-in-out; }
.badge { font-size: 0.85rem; }
table.dataTable thead .sorting:after,
table.dataTable thead .sorting:before,
table.dataTable thead .sorting_asc:after,
table.dataTable thead .sorting_desc:after { display: none !important; }
</style>