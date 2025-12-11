// Este es el código único y corregido para la lista de sedes
// Archivo: Public/js/javascript/js/ValidacionesSedeLista.js (o donde esté el DataTables)

$(document).ready(function() {

    // ==========================================================
    // 1. INICIALIZACIÓN DE DATATABLES (CORRECCIÓN CLAVE)
    // Se inicializa SÓLO si no se ha hecho antes.
    // ==========================================================
    var tabla;
    
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#tablaSedes')) {
        tabla = $('#tablaSedes').DataTable({
            "language": {
                // Configuración en español para DataTables
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            },
            "order": [
                [0, "asc"]
            ], // Ordenar por la primera columna (Estado) por defecto
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true
        });
    } else {
         // Si ya estaba inicializada, obtenemos la instancia para el futuro
         tabla = $('#tablaSedes').DataTable();
    }


    // ==========================================================
    // 2. LÓGICA DE CAMBIO DE ESTADO (Activar/Desactivar)
    // ==========================================================
    // Usamos la delegación de eventos sobre la tabla
    $('#tablaSedes tbody').on('click', '.btn-toggle-estado', function(e) {
        e.preventDefault();

        var $btn = $(this); // Capturar el botón clickeado
        var idSede = $btn.data('id');
        var estadoActual = $btn.data('estado-actual');
        var nombreSede = $btn.data('nombre');

        // Determinar el nuevo estado y el texto del mensaje de confirmación
        var nuevoEstado = (estadoActual === 'Activo') ? 'Inactivo' : 'Activo';
        var mensaje = (estadoActual === 'Activo') ?
            "¿Está seguro de **desactivar** la sede " + nombreSede + "?" :
            "¿Está seguro de **activar** la sede " + nombreSede + "?";

        // Usamos la variable global Swal (SweetAlert2)
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Confirmar Cambio de Estado',
                html: mensaje,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, cambiar estado',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Llamar a la función que ejecuta el AJAX
                    realizarCambioEstado(idSede, estadoActual, nuevoEstado, $btn);
                }
            });
        } else {
            // Fallback: Si no hay SweetAlert2, usar la alerta nativa del navegador
            if (confirm("Desea cambiar el estado a " + nuevoEstado + " para la sede " + nombreSede + "?")) {
                realizarCambioEstado(idSede, estadoActual, nuevoEstado, $btn);
            }
        }
    });


    // Función separada para manejar la lógica AJAX y la actualización de la fila
    function realizarCambioEstado(idSede, estadoActual, nuevoEstado, $btn) {
        
        // Guardamos el HTML del botón para restaurarlo en caso de error
        var iconActual = $btn.html(); 
        // Deshabilitar botón y mostrar spinner mientras espera la respuesta
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        // Ajustar la ruta si es necesario (asumiendo que '../../Controller/' es correcto desde la ubicación JS)
        const ajaxURL = '../../Controller/ControladorSede.php'; 

        $.ajax({
            url: ajaxURL,
            type: 'POST',
            dataType: 'json', 
            data: {
                accion: 'cambiarEstado', // Identificador para el SWITCH en PHP
                id: idSede, // El controlador espera el ID como 'id'
            },
            success: function(response) {
                
                if (response && response.success === true) {
                    
                    // --- 1. ACTUALIZAR LA FILA EN DataTables sin recargar la página ---
                    // Obtenemos la fila de DataTables para manipulación
                    var $row = $btn.closest('tr');
                    
                    // 2. ACTUALIZAR VISUALMENTE EL BADGE (Columna 0)
                    var $badge = $row.find('td:eq(0) span.badge'); 
                    
                    // 3. ACTUALIZAR EL BOTÓN DE ACCIÓN
                    if (nuevoEstado === 'Activo') {
                        // Cambiar Badge a Activo (verde)
                        $badge.removeClass('badge-secondary').addClass('badge-success').text('Activo');
                        // Cambiar Botón a Desactivar (secondary, icono ban)
                        $btn.removeClass('btn-success').addClass('btn-secondary')
                            .attr('title', 'Desactivar')
                            .data('estado-actual', 'Activo') 
                            .html('<i class="fas fa-ban"></i>');
                    } else {
                        // Cambiar Badge a Inactivo (gris)
                        $badge.removeClass('badge-success').addClass('badge-secondary').text('Inactivo');
                        // Cambiar Botón a Activar (success, icono check)
                        $btn.removeClass('btn-secondary').addClass('btn-success')
                            .attr('title', 'Activar')
                            .data('estado-actual', 'Inactivo') 
                            .html('<i class="fas fa-check"></i>');
                    }

                    // Notificación de éxito
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('¡Actualizado!', response.message, 'success');
                    }
                    
                    // Si usa la variable 'tabla' de DataTables (si es necesaria)
                    // tabla.row($row).invalidate().draw(false); 

                } else {
                    // Muestra mensaje de error desde el backend
                    var errorMessage = response.message || 'Hubo un error inesperado al cambiar el estado.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', errorMessage, 'error');
                    }
                    // Restaurar ícono en caso de error
                    $btn.html(iconActual); 
                }
                
                // Habilitar botón, independientemente del resultado
                $btn.prop('disabled', false);

            },
            error: function(xhr, status, error) {
                // Manejo de error de conexión 
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error de Conexión', 'No se pudo conectar con el servidor o la ruta es incorrecta.', 'error');
                } else {
                    alert('Error de comunicación con el servidor.');
                }
                
                // Restaurar ícono original
                $btn.prop('disabled', false).html(iconActual);
            }
        });
    }

});