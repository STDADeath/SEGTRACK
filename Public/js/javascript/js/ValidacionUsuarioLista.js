const urlControlador = "../../Controller/ControladorusuarioADM.php";

$(document).ready(function () {

    // ── DataTable ──────────────────────────────────────────────
    if ($.fn.DataTable.isDataTable('#tablaUsuarios')) {
        $('#tablaUsuarios').DataTable().destroy();
    }

    $('#tablaUsuarios').DataTable({
        pageLength: 10,
        lengthMenu: [[5, 10, 15, 25, 50], [5, 10, 15, 25, 50]],
        order: [[3, 'desc']],
        columnDefs: [{ targets: [3, 4], orderable: false }],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
        },
        responsive: true
    });

    // ── Abrir modal editar ─────────────────────────────────────
    $(document).on('click', '.btn-editar-rol', function () {
        var id     = $(this).data('id');
        var rol    = $(this).data('rol');
        var nombre = $(this).data('nombre');

        $('#editIdUsuario').val(id);
        $('#editNombreFuncionario').val(nombre);
        $('#editTipoRol').val(rol);

        // ✅ Verificar que Bootstrap esté disponible antes de abrir
        if (typeof $.fn.modal === 'undefined') {
            alert('Error: Bootstrap no está cargado correctamente.');
            return;
        }

        $('#modalEditarRol').modal('show');
    });

    // ── Guardar cambios de rol ─────────────────────────────────
    $('#btnGuardarRol').on('click', function () {

        const idUsuario = $('#editIdUsuario').val();
        const nuevoRol  = $('#editTipoRol').val();

        if (!nuevoRol) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo requerido',
                text: 'Debe seleccionar un rol',
                confirmButtonColor: '#3b82f6'
            });
            return;
        }

        const btn  = $(this);
        const orig = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');

        $.ajax({
            url: urlControlador,
            type: 'POST',
            dataType: 'json',
            data: {
                accion:    'actualizar',
                IdUsuario:  idUsuario,
                tipo_rol:   nuevoRol
            },
            success: function (response) {
                btn.prop('disabled', false).html(orig);

                if (response.success === true) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message,
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        $('#modalEditarRol').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No se pudo actualizar',
                        text: response.message || 'Error al actualizar el rol',
                        confirmButtonColor: '#3b82f6'
                    });
                }
            },
            error: function () {
                btn.prop('disabled', false).html(orig);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor',
                    confirmButtonColor: '#3b82f6'
                });
            }
        });
    });

    // ── Toggle candado estado ──────────────────────────────────
    $(document).on('click', '.btn-toggle-estado', function () {

        var btn          = $(this);
        var id           = btn.data('id');
        var icon         = btn.find('i');
        var estadoActual = btn.data('estado');
        var nuevoEstado  = (estadoActual === 'Activo') ? 'Inactivo' : 'Activo';

        if (!estadoActual) {
            Swal.fire('Error', 'No se pudo leer el estado.', 'error');
            return;
        }

        Swal.fire({
            title: '¿Cambiar estado?',
            text: 'El usuario pasará a: ' + nuevoEstado,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then(function (result) {
            if (!result.isConfirmed) return;

            btn.prop('disabled', true);
            icon.removeClass().addClass('fas fa-spinner fa-spin');

            $.ajax({
                url: urlControlador,
                type: 'POST',
                dataType: 'json',
                data: {
                    accion:    'cambiar_estado',
                    IdUsuario:  id,
                    Estado:     nuevoEstado
                },
                success: function (response) {
                    btn.prop('disabled', false);

                    if (!response || response.success !== true) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'No se pudo cambiar el estado',
                            confirmButtonColor: '#3b82f6'
                        });
                        icon.removeClass().addClass(
                            estadoActual === 'Activo'
                                ? 'fas fa-lock text-warning'
                                : 'fas fa-unlock text-success'
                        );
                        return;
                    }

                    var fila  = $('button.btn-toggle-estado[data-id="' + id + '"]').closest('tr');
                    var badge = fila.find('td:eq(3) span');

                    if (nuevoEstado === 'Activo') {
                        badge.attr('class', 'badge bg-success px-3 py-2')
                             .removeAttr('style').text('Activo');
                        icon.removeClass().addClass('fas fa-lock text-warning');
                        btn.attr('title', 'Desactivar usuario');
                    } else {
                        badge.attr('class', 'badge px-3 py-2')
                             .css('background-color', '#60a5fa').text('Inactivo');
                        icon.removeClass().addClass('fas fa-unlock text-success');
                        btn.attr('title', 'Activar usuario');
                    }

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
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor',
                        confirmButtonColor: '#3b82f6'
                    });
                    console.error('Error AJAX:', xhr.responseText);
                }
            });
        });
    });
});