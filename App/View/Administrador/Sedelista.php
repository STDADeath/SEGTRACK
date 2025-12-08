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

<script>
$(document).ready(function() {

    // Inicializar DataTable igual que Institución
    var tabla = $('#tablaSedes').DataTable({
        "language": {
            "emptyTable": "No hay datos disponibles",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "lengthMenu": "Mostrar _MENU_ registros",
            "search": "Buscar:",
            "zeroRecords": "No se encontraron registros",
            "paginate": {
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        "pageLength": 10
    });

    // BOTÓN CAMBIAR ESTADO
    $('#tablaSedes tbody').on('click', '.btn-toggle-estado', function(e) {
        e.preventDefault();

        var $btn = $(this);
        var id = $btn.data('id');
        var estadoActual = $btn.data('estado-actual');
        var nombre = $btn.data('nombre');
        var nuevoEstado = (estadoActual === 'Activo') ? 'Inactivo' : 'Activo';

        if (!confirm("¿Confirmas cambiar el estado de la sede '" + nombre + "' a " + nuevoEstado + "?")) {
            return;
        }

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: "../../Controller/ControladorSede.php",
            method: "POST",
            data: {
                accion: "cambiarEstado",
                IdSede: id,
                Estado: nuevoEstado
            },
            dataType: "json",
            success: function(response) {

                if (response.ok) {

                    var $row = $btn.closest('tr');
                    var $badge = $row.find("td:first span");

                    if (nuevoEstado === "Activo") {
                        $badge.removeClass("badge-secondary").addClass("badge-success").text("Activo");
                        $btn.removeClass("btn-success").addClass("btn-secondary").html('<i class="fas fa-ban"></i>');
                    } else {
                        $badge.removeClass("badge-success").addClass("badge-secondary").text("Inactivo");
                        $btn.removeClass("btn-secondary").addClass("btn-success").html('<i class="fas fa-check"></i>');
                    }

                    $btn.data("estado-actual", nuevoEstado);
                    alert(response.message);

                } else {
                    alert(response.message);
                }

                $btn.prop('disabled', false);
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("Error en la comunicación con el servidor.");
                $btn.prop('disabled', false);
            }
        });
    });

});
</script>
