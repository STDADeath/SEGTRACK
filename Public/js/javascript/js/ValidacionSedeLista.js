$(document).ready(function () {

    // ==========================================
    // VALIDACIÓN: solo letras y espacios
    // ==========================================
    var soloLetras = /^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/;

    $(document).on('keypress', '#editTipoSede, #editCiudad', function (e) {
        if (/[0-9]/.test(String.fromCharCode(e.which))) {
            e.preventDefault();
        }
    });

    $(document).on('input', '#editTipoSede, #editCiudad', function () {
        $(this).val($(this).val().replace(/[0-9]/g, ''));
    });

    function limpiarErrores() {
        $('#editTipoSede, #editCiudad').removeClass('is-invalid');
        $('#errorTipoSede, #errorCiudad').text('');
    }

    function mostrarError(campo, mensaje) {
        $('#edit' + campo).addClass('is-invalid');
        $('#error' + campo).text(mensaje);
    }

    function validarCampos(tipoSede, ciudad) {
        limpiarErrores();
        var valido = true;
        if (tipoSede === '') {
            mostrarError('TipoSede', 'El tipo de sede es obligatorio.');
            valido = false;
        } else if (!soloLetras.test(tipoSede)) {
            mostrarError('TipoSede', 'Solo se permiten letras, sin números.');
            valido = false;
        }
        if (ciudad === '') {
            mostrarError('Ciudad', 'La ciudad es obligatoria.');
            valido = false;
        } else if (!soloLetras.test(ciudad)) {
            mostrarError('Ciudad', 'Solo se permiten letras, sin números.');
            valido = false;
        }
        return valido;
    }

    $('#modalEditarSede').on('hidden.bs.modal', function () {
        limpiarErrores();
    });

    // ==========================================
    // DATATABLE
    // ==========================================
    $('#tablaSedes').DataTable({
        ordering: false,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
        responsive: true,
        language: {
            emptyTable:   "No hay sedes registradas",
            info:         "Mostrando _START_ a _END_ de _TOTAL_ sedes",
            infoEmpty:    "Mostrando 0 a 0 de 0 sedes",
            infoFiltered: "(filtrado de _MAX_ sedes)",
            lengthMenu:   "Mostrar _MENU_ sedes",
            search:       "Buscar:",
            zeroRecords:  "No se encontraron resultados",
            paginate: {
                first:    "Primera",
                last:     "Última",
                next:     "Siguiente",
                previous: "Anterior"
            }
        }
    });

    // ==========================================
    // ABRIR MODAL EDITAR
    // ==========================================
    $(document).on('click', '.btn-editar', function () {

        limpiarErrores();
        var id = $(this).data('id');

        $.ajax({
            url: '../../Controller/ControladorSede.php',
            type: 'POST',
            data: { accion: 'obtener_sede', IdSede: id },
            success: function (respuestaRaw) {
                var response;
                try {
                    response = (typeof respuestaRaw === 'string')
                        ? JSON.parse(respuestaRaw) : respuestaRaw;
                } catch (e) {
                    Swal.fire('Error', 'Respuesta inválida del servidor.', 'error');
                    return;
                }

                if (!response) {
                    Swal.fire('Error', 'No se encontró la sede.', 'error');
                    return;
                }

                $('#editIdSede').val(response.IdSede);
                $('#editTipoSede').val(response.TipoSede);
                $('#editCiudad').val(response.Ciudad);
                $('#editInstitucion').val(response.IdInstitucion);

                // Abrir modal con botón trigger nativo Bootstrap 4
                $('#btnTriggerModal').trigger('click');
            },
            error: function () {
                Swal.fire('Error de conexión', 'No se pudo conectar al servidor.', 'error');
            }
        });
    });

    // ==========================================
    // GUARDAR EDICIÓN
    // ✅ CORRECCIÓN: se cierra el modal manualmente via
    //    el backdrop y se muestra la alerta sin depender
    //    del evento hidden.bs.modal
    // ==========================================
    $(document).on('click', '#btnGuardarEdicion', function () {

        var tipoSede = $('#editTipoSede').val().trim();
        var ciudad   = $('#editCiudad').val().trim();
        var idSede   = $('#editIdSede').val();
        var idInst   = $('#editInstitucion').val();

        if (!validarCampos(tipoSede, ciudad)) return;

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Guardando...');

        $.ajax({
            url: '../../Controller/ControladorSede.php',
            type: 'POST',
            data: {
                accion:        'editar',
                IdSede:        idSede,
                TipoSede:      tipoSede,
                Ciudad:        ciudad,
                IdInstitucion: idInst
            },
            success: function (respuestaRaw) {

                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Guardar Cambios');

                var response;
                try {
                    response = (typeof respuestaRaw === 'string')
                        ? JSON.parse(respuestaRaw) : respuestaRaw;
                } catch (e) {
                    Swal.fire('Error del servidor', 'La respuesta no es válida.', 'error');
                    return;
                }

                if (!response || !response.success) {
                    Swal.fire('Error', response.message || 'No se pudo guardar los cambios.', 'error');
                    return;
                }

                // ✅ Cerrar modal quitando clases manualmente (no depende de $.fn.modal)
                $('#modalEditarSede').removeClass('show').hide();
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                // ✅ Mostrar alerta y recargar
                Swal.fire({
                    icon: 'success',
                    title: '¡Sede actualizada!',
                    text: 'Los cambios se guardaron correctamente.',
                    timer: 1800,
                    showConfirmButton: false
                }).then(function () {
                    location.reload();
                });
            },
            error: function () {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Guardar Cambios');
                Swal.fire('Error de conexión', 'No se pudo conectar al servidor.', 'error');
            }
        });
    });

    // ==========================================
    // CAMBIAR ESTADO SEDE
    // ==========================================
    $(document).on('click', '.btn-estado', function () {

        var btn   = $(this);
        var id    = btn.data('id');
        var fila  = btn.closest('tr');
        var badge = fila.find('.estado-badge');
        var icon  = btn.find('i');

        var estadoActual = badge.text().trim();
        var nuevoEstado  = (estadoActual === 'Activo') ? 'Inactivo' : 'Activo';

        Swal.fire({
            title: '¿Cambiar estado?',
            text: 'La sede pasará a estar ' + nuevoEstado,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then(function (result) {

            if (!result.isConfirmed) return;

            btn.prop('disabled', true);
            icon.removeClass().addClass('fas fa-spinner fa-spin');

            $.ajax({
                url: '../../Controller/ControladorSede.php',
                type: 'POST',
                dataType: 'json',
                data: { accion: 'cambiarEstado', id: id },
                success: function (response) {

                    btn.prop('disabled', false);

                    if (!response || !response.success) {
                        Swal.fire('Error', response.message || 'No se pudo cambiar el estado.', 'error');
                        icon.removeClass().addClass('fas fa-sync-alt');
                        return;
                    }

                    if (nuevoEstado === 'Activo') {
                        badge.removeClass()
                             .addClass('badge bg-success px-3 py-2 estado-badge')
                             .css('background-color', '')
                             .text('Activo');
                        icon.removeClass().addClass('fas fa-lock text-warning');
                    } else {
                        badge.removeClass()
                             .addClass('badge px-3 py-2 estado-badge')
                             .css('background-color', '#60a5fa')
                             .text('Inactivo');
                        icon.removeClass().addClass('fas fa-unlock text-success');
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Estado actualizado',
                        text: 'La sede ahora está ' + nuevoEstado,
                        timer: 1800,
                        showConfirmButton: false
                    });
                },
                error: function () {
                    btn.prop('disabled', false);
                    icon.removeClass().addClass('fas fa-sync-alt');
                    Swal.fire('Error', 'No se pudo conectar al servidor.', 'error');
                }
            });
        });
    });

});