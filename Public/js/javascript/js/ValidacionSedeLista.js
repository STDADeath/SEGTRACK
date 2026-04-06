// ========================================
// LISTA DE SEDES - SEGTRACK
// Maneja: DataTable, Modal Editar con validación
// en tiempo real (rojo/verde), Candado Toggle Estado
// Todas las alertas usan SweetAlert2
// CORREGIDO PARA BOOTSTRAP 5
// ========================================

console.log("✅ ValidacionSedeLista.js cargado correctamente");

// Lee la URL del controlador definida por PHP en la vista
const urlControladorSede = window.urlControladorSede
    || "../../Controller/ControladorSede.php";

$(document).ready(function () {

    // ══════════════════════════════════════════════════════
    // FUNCIONES DE VALIDACIÓN VISUAL (rojo / verde / neutro)
    // ══════════════════════════════════════════════════════

    function marcarValido(campo) {
        campo.css({
            "border":     "2px solid #10b981",
            "box-shadow": "0 0 0 0.25rem rgba(16,185,129,0.25)"
        });
        campo.removeClass('is-invalid');
    }

    function marcarInvalido(campo) {
        campo.css({
            "border":     "2px solid #ef4444",
            "box-shadow": "0 0 0 0.25rem rgba(239,68,68,0.25)"
        });
        campo.addClass('is-invalid');
    }

    function marcarNeutral(campo) {
        campo.css({ "border": "", "box-shadow": "" });
        campo.removeClass('is-invalid');
    }

    var soloLetras = /^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/;

    // ══════════════════════════════════════════════════════
    // INICIALIZAR DATATABLE
    // ══════════════════════════════════════════════════════
    $('#tablaSedes').DataTable({
        ordering:   false,
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

    // ══════════════════════════════════════════════════════
    // VALIDACIÓN EN TIEMPO REAL DEL MODAL
    // ══════════════════════════════════════════════════════

    $(document).on('input', '#editTipoSede', function () {
        let campo = $(this);
        let valorLimpio = campo.val().replace(/[^a-zA-ZñÑáéíóúÁÉÍÓÚ\s]/g, "");
        campo.val(valorLimpio);

        if (valorLimpio.trim().length >= 3 && soloLetras.test(valorLimpio)) {
            marcarValido(campo);
            $('#errorTipoSede').text('');
        } else if (valorLimpio.trim().length === 0) {
            marcarNeutral(campo);
        } else {
            marcarInvalido(campo);
            $('#errorTipoSede').text('Mínimo 3 letras, sin números ni símbolos.');
        }
    });

    $(document).on('input', '#editCiudad', function () {
        let campo = $(this);
        let valorLimpio = campo.val().replace(/[^a-zA-ZñÑáéíóúÁÉÍÓÚ\s]/g, "");
        campo.val(valorLimpio);

        if (valorLimpio.trim().length >= 3 && soloLetras.test(valorLimpio)) {
            marcarValido(campo);
            $('#errorCiudad').text('');
        } else if (valorLimpio.trim().length === 0) {
            marcarNeutral(campo);
        } else {
            marcarInvalido(campo);
            $('#errorCiudad').text('Mínimo 3 letras, sin números ni símbolos.');
        }
    });

    // Al cerrar el modal se resetean los estilos visuales
    $('#modalEditarSede').on('hidden.bs.modal', function () {
        marcarNeutral($('#editTipoSede'));
        marcarNeutral($('#editCiudad'));
        $('#errorTipoSede, #errorCiudad').text('');
    });

    // ══════════════════════════════════════════════════════
    // ABRIR MODAL EDITAR (CORREGIDO PARA BOOTSTRAP 5)
    // ══════════════════════════════════════════════════════
    $(document).on('click', '.btn-editar', function () {

        marcarNeutral($('#editTipoSede'));
        marcarNeutral($('#editCiudad'));
        $('#errorTipoSede, #errorCiudad').text('');

        var id = $(this).data('id');

        $.ajax({
            url:  urlControladorSede,
            type: 'POST',
            data: { accion: 'obtener_sede', IdSede: id },
            success: function (respuestaRaw) {
                var response;
                try {
                    response = (typeof respuestaRaw === 'string')
                        ? JSON.parse(respuestaRaw)
                        : respuestaRaw;
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

                // ✅ API de Bootstrap 5 para abrir el modal
                const modalElement = document.getElementById('modalEditarSede');
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            },
            error: function () {
                Swal.fire('Error de conexión',
                    'No se pudo conectar al servidor.', 'error');
            }
        });
    });

    // ══════════════════════════════════════════════════════
    // GUARDAR EDICIÓN DESDE EL MODAL
    // ══════════════════════════════════════════════════════
    $(document).on('click', '#btnGuardarEdicion', function () {

        var tipoSede = $('#editTipoSede').val().trim();
        var ciudad   = $('#editCiudad').val().trim();
        var idSede   = $('#editIdSede').val();
        var idInst   = $('#editInstitucion').val();

        var hayError = false;

        if (tipoSede === '') {
            marcarInvalido($('#editTipoSede'));
            $('#errorTipoSede').text('El tipo de sede es obligatorio.');
            hayError = true;
        } else if (!soloLetras.test(tipoSede) || tipoSede.length < 3) {
            marcarInvalido($('#editTipoSede'));
            $('#errorTipoSede').text('Mínimo 3 letras, sin números ni símbolos.');
            hayError = true;
        } else {
            marcarValido($('#editTipoSede'));
            $('#errorTipoSede').text('');
        }

        if (ciudad === '') {
            marcarInvalido($('#editCiudad'));
            $('#errorCiudad').text('La ciudad es obligatoria.');
            hayError = true;
        } else if (!soloLetras.test(ciudad) || ciudad.length < 3) {
            marcarInvalido($('#editCiudad'));
            $('#errorCiudad').text('Mínimo 3 letras, sin números ni símbolos.');
            hayError = true;
        } else {
            marcarValido($('#editCiudad'));
            $('#errorCiudad').text('');
        }

        if (hayError) {
            Swal.fire({
                icon: 'error',
                title: 'Campos incorrectos',
                text: 'Por favor corrige los campos marcados en rojo.',
                confirmButtonColor: '#ef4444'
            });
            return;
        }

        Swal.fire({
            title: '¿Actualizar sede?',
            text: 'Los cambios se guardarán en la base de datos.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6c757d'
        }).then(function (result) {

            if (!result.isConfirmed) return;

            var btn = $('#btnGuardarEdicion');
            var textoOriginal = btn.html();
            btn.prop('disabled', true)
               .html('<i class="fas fa-spinner fa-spin me-1"></i>Guardando...');

            $.ajax({
                url:  urlControladorSede,
                type: 'POST',
                data: {
                    accion:        'editar',
                    IdSede:        idSede,
                    TipoSede:      tipoSede,
                    Ciudad:        ciudad,
                    IdInstitucion: idInst
                },
                success: function (respuestaRaw) {

                    btn.prop('disabled', false).html(textoOriginal);

                    var response;
                    try {
                        response = (typeof respuestaRaw === 'string')
                            ? JSON.parse(respuestaRaw)
                            : respuestaRaw;
                    } catch (e) {
                        Swal.fire('Error del servidor',
                            'La respuesta no es válida.', 'error');
                        return;
                    }

                    if (!response || !response.success) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No se pudo guardar',
                            text: response.message || 'Ocurrió un error inesperado.',
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#f59e0b'
                        });
                        return;
                    }

                    // ✅ CERRAR MODAL CORRECTAMENTE CON BOOTSTRAP 5
                    const modalElement = document.getElementById('modalEditarSede');
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) modal.hide();

                    // Éxito: alerta con timer y recarga
                    Swal.fire({
                        icon: 'success',
                        title: '¡Sede actualizada!',
                        text: 'Los cambios se guardaron correctamente.',
                        timer: 1800,
                        showConfirmButton: false,
                        timerProgressBar: true
                    }).then(function () {
                        location.reload();
                    });
                },
                error: function () {
                    btn.prop('disabled', false).html(textoOriginal);
                    Swal.fire('Error de conexión',
                        'No se pudo conectar al servidor.', 'error');
                }
            });
        });
    });

    // ══════════════════════════════════════════════════════
    // CANDADO: CAMBIAR ESTADO (Activo ↔ Inactivo)
    // ══════════════════════════════════════════════════════
    $(document).on('click', '.btn-estado', function () {

        var btn          = $(this);
        var id           = btn.data('id');
        var fila         = btn.closest('tr');
        var badge        = fila.find('.estado-badge');
        var icon         = btn.find('i');
        var estadoActual = badge.text().trim();
        var nuevoEstado  = (estadoActual === 'Activo') ? 'Inactivo' : 'Activo';
        var esDesactivar = (nuevoEstado === 'Inactivo');

        Swal.fire({
            title: esDesactivar ? '¿Desactivar sede?' : '¿Activar sede?',
            text: esDesactivar
                ? 'La sede pasará a Inactivo.'
                : 'La sede volverá a estar Activo.',
            icon: 'warning',
            showCancelButton:  true,
            confirmButtonText: esDesactivar ? 'Sí, desactivar' : 'Sí, activar',
            cancelButtonText:  'Cancelar',
            confirmButtonColor: esDesactivar ? '#f59e0b' : '#10b981',
            cancelButtonColor:  '#6c757d',
            reverseButtons: true
        }).then(function (result) {

            if (!result.isConfirmed) return;

            btn.prop('disabled', true);
            icon.removeClass().addClass('fas fa-spinner fa-spin');

            $.ajax({
                url:      urlControladorSede,
                type:     'POST',
                dataType: 'json',
                data: { accion: 'cambiarEstado', id: id },
                success: function (response) {

                    btn.prop('disabled', false);

                    if (!response || !response.success) {
                        icon.removeClass().addClass(
                            estadoActual === 'Activo'
                                ? 'fas fa-lock text-warning'
                                : 'fas fa-unlock text-success'
                        );
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'No se pudo cambiar el estado.',
                            confirmButtonColor: '#ef4444'
                        });
                        return;
                    }

                    if (nuevoEstado === 'Activo') {
                        badge.removeClass()
                             .addClass('badge bg-success text-white px-3 py-2 estado-badge')
                             .css('background-color', '')
                             .text('Activo');
                        icon.removeClass()
                            .addClass('fas fa-lock text-warning');
                    } else {
                        badge.removeClass()
                             .addClass('badge text-white px-3 py-2 estado-badge')
                             .css('background-color', '#60a5fa')
                             .text('Inactivo');
                        icon.removeClass()
                            .addClass('fas fa-unlock text-success');
                    }

                    Swal.fire({
                        icon: 'success',
                        title: '¡Estado actualizado!',
                        text: 'La sede ahora está ' + nuevoEstado + '.',
                        timer: 1500,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                },
                error: function () {
                    btn.prop('disabled', false);
                    icon.removeClass().addClass(
                        estadoActual === 'Activo'
                            ? 'fas fa-lock text-warning'
                            : 'fas fa-unlock text-success'
                    );
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo cambiar el estado.',
                        confirmButtonColor: '#ef4444'
                    });
                }
            });
        });
    });

}); // FIN document.ready