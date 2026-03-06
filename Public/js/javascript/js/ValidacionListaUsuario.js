// Archivo: Public/js/javascript/js/ValidacionesUsuarioLista.js

$(document).ready(function() {

    // ==========================================================
    // 1. INICIALIZACIÓN DE DATATABLES
    // ==========================================================
    var tabla = $('#tablaUsuarios').DataTable({
        "language": {
            // Configuración en español para DataTables
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        },
        "order": [
            [0, "desc"]
        ], // Ordenar por ID descendente por defecto
        "paging": true,
        "lengthChange": true, // Control de cantidad de filas
        "searching": true, // Barra de búsqueda (filtrado)
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true
    });


    // ==========================================================
    // 2. LÓGICA DE CAMBIO DE ESTADO (Activar/Desactivar) - Vía AJAX
    // ==========================================================
    // Usamos la delegación de eventos sobre la tabla para asegurar que funciona 
    // con la paginación y el filtro de DataTables.
    $('#tablaUsuarios tbody').on('click', '.btn-toggle-estado', function(e) {
        e.preventDefault(); // Impedir la acción por defecto del enlace

        var $btn = $(this); // Capturar el botón clickeado
        var idUsuario = $btn.data('id');
        var estadoActual = $btn.data('estado-actual');
        var nombreFuncionario = $btn.data('funcionario'); // Obtenemos el nombre

        // Determinar el nuevo estado y el texto del mensaje de confirmación
        var nuevoEstado = (estadoActual === 'Activo') ? 'Inactivo' : 'Activo';
        var mensaje = (estadoActual === 'Activo') ?
            "¿Está seguro de **desactivar** el usuario de **" + nombreFuncionario + "**?" :
            "¿Está seguro de **activar** el usuario de **" + nombreFuncionario + "**?";

        // Usamos la variable global Swal (SweetAlert2)
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Confirmar Cambio de Estado',
                html: mensaje,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, ' + (nuevoEstado === 'Activo' ? 'activar' : 'desactivar'),
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    realizarCambioEstado(idUsuario, estadoActual, nuevoEstado, $btn, tabla);
                }
            });
        }
    });


    // Función separada que realiza la llamada AJAX y la manipulación del DOM
    function realizarCambioEstado(idUsuario, estadoActual, nuevoEstado, $btn, tabla) {
        
        var iconActual = $btn.html(); 
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        // RUTA CORRECTA: Apunta a tu ControladorusuarioADM.php
        const ajaxURL = '../../Controller/ControladorusuarioADM.php';

        $.ajax({
            url: ajaxURL,
            type: 'POST',
            dataType: 'json', 
            data: {
                accion: 'cambiarEstado', // Acción definida en el Controlador
                id: idUsuario, // Parámetro que espera el controlador
            },
            success: function(response) {
                
                if (response && response.ok === true) {
                    
                    // --- MANIPULACIÓN DEL DOM (Actualización visual sin recargar) ---
                    var $row = $btn.closest('tr');
                    
                    // La columna del Estado es la 3 (0-indexado)
                    var $badgeCell = $row.find('td:eq(3)'); 
                    
                    // 1. Actualizar el BADGE
                    $badgeCell.empty(); // Limpiamos el contenido
                    var newBadge = (nuevoEstado === 'Activo') ? 
                        '<span class="badge bg-success px-2 py-1 estado-badge">Activo</span>' :
                        '<span class="badge bg-danger px-2 py-1 estado-badge">Inactivo</span>';
                    $badgeCell.html(newBadge);
                    
                    // 2. Actualizar el BOTÓN de acción (solo actualizamos el data-estado-actual)
                    $btn.data('estado-actual', nuevoEstado); 
                    $btn.html('<i class="fas fa-sync-alt"></i>'); // Restauramos el ícono de sincronización

                    // Notificación de éxito
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('¡Actualizado!', response.data, 'success');
                    }
                    
                    // Forzar a DataTables a redibujar la fila con los nuevos datos del DOM
                    tabla.row($row).invalidate().draw(false); 

                } else {
                    // Muestra mensaje de error del backend
                    var errorMessage = response.data || 'Hubo un error inesperado al cambiar el estado.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', errorMessage, 'error');
                    }
                    // Restaurar ícono en caso de error
                    $btn.html(iconActual); 
                }
                
                // Habilitar botón
                $btn.prop('disabled', false);

            },
            error: function(xhr, status, error) {
                // Manejo de error de conexión (404, 500, etc.)
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error de Conexión', 'No se pudo conectar con el servidor: ' + status, 'error');
                }
                
                // Restaurar ícono original
                $btn.prop('disabled', false).html(iconActual);
            }
        });
    }
});