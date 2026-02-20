$(document).ready(function () {

    // ── DataTable: inicializar UNA sola vez ────────────────────
    var tabla;

    if ($.fn.DataTable.isDataTable('#tablaInstitutos')) {
        // Si ya existe (doble carga), destruir y reinicializar
        $('#tablaInstitutos').DataTable().destroy();
    }

    tabla = $('#tablaInstitutos').DataTable({
        pageLength: 10,
        lengthMenu: [[5, 10, 15, 25, 50], [5, 10, 15, 25, 50]],
        order: [[0, 'asc']],
        columnDefs: [{ targets: 4, orderable: false }],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
        },
        responsive: true
    });

    // ── Toggle Estado ──────────────────────────────────────────
    // ✅ Delegar en document para que funcione con DataTable paginado
    $(document).on('click', '.btn-toggle-estado', function () {

        var btn          = $(this);
        var id           = btn.data('id');
        var icon         = btn.find('i');

        // ✅ Leer estado desde data-estado del botón (no del DOM de DataTable)
        var estadoActual = btn.data('estado');
        var nuevoEstado  = (estadoActual === 'Activo') ? 'Inactivo' : 'Activo';

        if (estadoActual !== 'Activo' && estadoActual !== 'Inactivo') {
            Swal.fire('Error', 'No se pudo leer el estado actual.', 'error');
            return;
        }

        Swal.fire({
            title: '¿Cambiar estado?',
            text: 'La institución pasará a: ' + nuevoEstado,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then(function (result) {
            if (!result.isConfirmed) return;

            // Spinner
            btn.prop('disabled', true);
            icon.removeClass().addClass('fas fa-spinner fa-spin');

            $.ajax({
                url: '../../Controller/Controladorinstituto.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    accion: 'cambiarEstado',
                    IdInstitucion: id,
                    EstadoInstitucion: nuevoEstado
                },
                success: function (response) {

                    // ✅ Siempre rehabilitar botón
                    btn.prop('disabled', false);

                    if (!response || !response.ok) {
                        Swal.fire('Error', response.message || 'No se pudo cambiar el estado', 'error');
                        icon.removeClass().addClass(
                            estadoActual === 'Activo'
                                ? 'fas fa-lock text-warning'
                                : 'fas fa-unlock text-success'
                        );
                        return;
                    }

                    // ✅ Buscar el badge en la fila correcta usando data-id
                    var fila  = $('[data-id="' + id + '"]').closest('tr');
                    var badge = fila.find('td:eq(3) span');

                    if (nuevoEstado === 'Activo') {
                        badge.attr('class', 'badge bg-success px-3 py-2')
                             .removeAttr('style')
                             .text('Activo');
                        icon.removeClass().addClass('fas fa-lock text-warning');
                        btn.attr('title', 'Desactivar');
                    } else {
                        badge.attr('class', 'badge px-3 py-2')
                             .css('background-color', '#60a5fa')
                             .text('Inactivo');
                        icon.removeClass().addClass('fas fa-unlock text-success');
                        btn.attr('title', 'Activar');
                    }

                    // ✅ Actualizar data-estado para el próximo click
                    btn.data('estado', nuevoEstado);

                    Swal.fire({
                        icon: 'success',
                        title: 'Estado actualizado',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                },
                error: function (xhr) {
                    btn.prop('disabled', false);
                    icon.removeClass().addClass(
                        estadoActual === 'Activo'
                            ? 'fas fa-lock text-warning'
                            : 'fas fa-unlock text-success'
                    );
                    Swal.fire('Error', 'No se pudo conectar al servidor', 'error');
                    console.error('Error AJAX:', xhr.responseText);
                }
            });
        });
    });
});