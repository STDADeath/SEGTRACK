<?php
// ============================================================
// InstitutoLista.php
// ============================================================

require_once __DIR__ . '/../layouts/parte_superior.php';
?>

<div class="container-fluid px-4 py-4">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-school me-2"></i>Instituciones Registradas</h1>
        <a href="./Instituto.php" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Registrar Institución
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Listado de Instituciones</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">

                <table id="tablaInstitutos" class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Estado</th>
                            <th>Nombre</th>
                            <th>NIT / Código</th>
                            <th>Tipo</th>
                            <th style="width:140px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        require_once __DIR__ . '/../../Core/Conexion.php';

                        try {
                            $conexion = new Conexion();
                            $db = $conexion->getConexion();

                            $sql = "SELECT IdInstitucion, EstadoInstitucion, NombreInstitucion, Nit_Codigo, TipoInstitucion 
                                    FROM institucion 
                                    ORDER BY IdInstitucion DESC";
                            $stmt = $db->prepare($sql);
                            $stmt->execute();

                            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)):
                        ?>
                                <tr data-id="<?php echo $fila['IdInstitucion']; ?>">
                                    <td>
                                        <?php if ($fila['EstadoInstitucion'] == 'Activo'): ?>
                                            <span class="badge badge-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($fila['NombreInstitucion']); ?></td>
                                    <td><?php echo htmlspecialchars($fila['Nit_Codigo']); ?></td>
                                    <td><?php echo htmlspecialchars($fila['TipoInstitucion']); ?></td>

                                    <td class="text-center">
                                        <!-- Botón Editar -->
                                        <a href="Instituto.php?IdInstitucion=<?php echo urlencode($fila['IdInstitucion']); ?>" 
                                           class="btn btn-warning btn-sm me-1" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <!-- Botón Cambiar Estado -->
                                        <button class="btn btn-sm btn-toggle-estado <?php echo ($fila['EstadoInstitucion'] == 'Activo') ? 'btn-secondary' : 'btn-success'; ?>" 
                                                data-id="<?php echo htmlspecialchars($fila['IdInstitucion']); ?>"
                                                data-estado-actual="<?php echo htmlspecialchars($fila['EstadoInstitucion']); ?>"
                                                data-nombre="<?php echo htmlspecialchars($fila['NombreInstitucion']); ?>" 
                                                title="<?php echo ($fila['EstadoInstitucion'] == 'Activo') ? 'Desactivar' : 'Activar'; ?>">
                                            <i class="fas fa-<?php echo ($fila['EstadoInstitucion'] == 'Activo') ? 'ban' : 'check'; ?>"></i>
                                        </button>
                                    </td>
                                </tr>
                        <?php
                            endwhile;
                        } catch (PDOException $e) {
                            echo '<tr><td colspan="5" class="text-center text-danger">Error al cargar datos: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>

</div>

<?php
require_once __DIR__ . '/../layouts/parte_inferior.php';
?>

<link rel="stylesheet" href="../../../Public/vendor/datatables/dataTables.bootstrap4.css">
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="../../../Public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../../../Public/vendor/datatables/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {

    // Inicializar DataTable
    var tabla = $('#tablaInstitutos').DataTable({
        "language": {
            "emptyTable": "No hay datos disponibles",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros)",
            "lengthMenu": "Mostrar _MENU_ registros",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "No se encontraron registros",
            "paginate": {
                "first": "Primera",
                "last": "Última",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        "pageLength": 10,
        "lengthMenu": [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
        "searching": true,
        "ordering": true,
        "responsive": true,
        "order": [[1, 'asc']]
    });

    // Manejo del botón CAMBIAR ESTADO
    $('#tablaInstitutos tbody').on('click', '.btn-toggle-estado', function(e) {
        e.preventDefault();

        var $btn = $(this);
        var id = $btn.data('id');
        var estadoActual = $btn.data('estado-actual');
        var nombre = $btn.data('nombre');
        var nuevoEstado = (estadoActual === 'Activo') ? 'Inactivo' : 'Activo';

        var mensaje = (nuevoEstado === 'Inactivo') 
            ? '¿Desea DESACTIVAR la institución "' + nombre + '"?' 
            : '¿Desea ACTIVAR la institución "' + nombre + '"?';

        if (!confirm(mensaje)) {
            return;
        }

        // Deshabilitar botón
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: '../../Controller/Controladorinstituto.php',
            method: 'POST',
            data: { 
                accion: 'cambiarEstado', 
                IdInstitucion: id,
                EstadoInstitucion: nuevoEstado
            },
            dataType: 'json',
            success: function(response) {
                if (response && response.ok === true) {
                    // Actualizar la fila sin recargar
                    var $row = $btn.closest('tr');
                    var $badge = $row.find('td:first span');

                    if (nuevoEstado === 'Activo') {
                        $badge.removeClass('badge-secondary').addClass('badge-success').text('Activo');
                        $btn.removeClass('btn-success').addClass('btn-secondary')
                            .attr('title', 'Desactivar')
                            .html('<i class="fas fa-ban"></i>');
                    } else {
                        $badge.removeClass('badge-success').addClass('badge-secondary').text('Inactivo');
                        $btn.removeClass('btn-secondary').addClass('btn-success')
                            .attr('title', 'Activar')
                            .html('<i class="fas fa-check"></i>');
                    }

                    $btn.data('estado-actual', nuevoEstado);
                    alert(response.message);
                } else {
                    alert(response.message || 'No se pudo cambiar el estado.');
                }
                
                $btn.prop('disabled', false);
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', error);
                console.error('Respuesta:', xhr.responseText);
                alert('Error de comunicación con el servidor.');
                $btn.prop('disabled', false).html('<i class="fas fa-ban"></i>');
            }
        });
    });

});
</script>