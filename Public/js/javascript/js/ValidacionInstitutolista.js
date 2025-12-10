
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