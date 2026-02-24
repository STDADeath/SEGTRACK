// ========================================
// LISTA DE SEDES - SEGTRACK
// Maneja: DataTable, Modal Editar con validación
// en tiempo real (rojo/verde), Candado Toggle Estado
// Todas las alertas usan SweetAlert2
// ========================================

console.log("✅ ValidacionSedeLista.js cargado correctamente");

// Lee la URL del controlador definida por PHP en la vista
// Así el JS funciona sin importar desde qué carpeta se sirva
const urlControladorSede = window.urlControladorSede
    || "../../Controller/ControladorSede.php";

$(document).ready(function () {

    // ══════════════════════════════════════════════════════
    // FUNCIONES DE VALIDACIÓN VISUAL (rojo / verde / neutro)
    // Igual que el módulo de instituciones para consistencia
    // ══════════════════════════════════════════════════════

    // Borde VERDE → campo válido
    function marcarValido(campo) {
        campo.css({
            "border":     "2px solid #10b981",
            "box-shadow": "0 0 0 0.25rem rgba(16,185,129,0.25)"
        });
        campo.removeClass('is-invalid');
    }

    // Borde ROJO → campo con error
    function marcarInvalido(campo) {
        campo.css({
            "border":     "2px solid #ef4444",
            "box-shadow": "0 0 0 0.25rem rgba(239,68,68,0.25)"
        });
        campo.addClass('is-invalid');
    }

    // Sin borde → estado neutro (al abrir el modal o resetear)
    function marcarNeutral(campo) {
        campo.css({ "border": "", "box-shadow": "" });
        campo.removeClass('is-invalid');
    }

    // Expresión regular: solo letras (incluyendo tildes y ñ) y espacios
    // Bloquea: números, @, #, /, ., !
    var soloLetras = /^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/;


    // ══════════════════════════════════════════════════════
    // INICIALIZAR DATATABLE
    // Convierte la tabla en interactiva con búsqueda,
    // paginación y textos en español
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
    // Se dispara con cada tecla — bloquea números y símbolos
    // y actualiza el color del borde inmediatamente
    // ══════════════════════════════════════════════════════

    // TIPO DE SEDE: solo letras y espacios, mínimo 3 caracteres
    $(document).on('input', '#editTipoSede', function () {
        let campo = $(this);
        // Elimina cualquier dígito o carácter no permitido en tiempo real
        let valorLimpio = campo.val().replace(/[^a-zA-ZñÑáéíóúÁÉÍÓÚ\s]/g, "");
        campo.val(valorLimpio);

        if (valorLimpio.trim().length >= 3 && soloLetras.test(valorLimpio)) {
            marcarValido(campo);
            $('#errorTipoSede').text(''); // Limpia mensaje de error inline
        } else if (valorLimpio.trim().length === 0) {
            marcarNeutral(campo);        // Vacío → neutro
        } else {
            marcarInvalido(campo);
            $('#errorTipoSede').text('Mínimo 3 letras, sin números ni símbolos.');
        }
    });

    // CIUDAD: solo letras y espacios, mínimo 3 caracteres
    $(document).on('input', '#editCiudad', function () {
        let campo = $(this);
        // Elimina cualquier dígito o carácter no permitido en tiempo real
        let valorLimpio = campo.val().replace(/[^a-zA-ZñÑáéíóúÁÉÍÓÚ\s]/g, "");
        campo.val(valorLimpio);

        if (valorLimpio.trim().length >= 3 && soloLetras.test(valorLimpio)) {
            marcarValido(campo);
            $('#errorCiudad').text(''); // Limpia mensaje de error inline
        } else if (valorLimpio.trim().length === 0) {
            marcarNeutral(campo);       // Vacío → neutro
        } else {
            marcarInvalido(campo);
            $('#errorCiudad').text('Mínimo 3 letras, sin números ni símbolos.');
        }
    });

    // Al cerrar el modal se resetean los estilos visuales
    // para que al abrirlo de nuevo inicie limpio
    $('#modalEditarSede').on('hidden.bs.modal', function () {
        marcarNeutral($('#editTipoSede'));
        marcarNeutral($('#editCiudad'));
        $('#errorTipoSede, #errorCiudad').text('');
    });


    // ══════════════════════════════════════════════════════
    // ABRIR MODAL EDITAR
    // Al hacer clic en el lápiz, hace AJAX para obtener los
    // datos actuales de la sede y los precarga en el modal
    // ══════════════════════════════════════════════════════
    $(document).on('click', '.btn-editar', function () {

        // Resetea estilos antes de abrir para iniciar neutro
        marcarNeutral($('#editTipoSede'));
        marcarNeutral($('#editCiudad'));
        $('#errorTipoSede, #errorCiudad').text('');

        var id = $(this).data('id'); // Lee el IdSede del botón

        // AJAX: pide los datos de esta sede al controlador
        $.ajax({
            url:  urlControladorSede,
            type: 'POST',
            data: { accion: 'obtener_sede', IdSede: id },
            success: function (respuestaRaw) {
                var response;
                try {
                    // Parsea si viene como string, lo usa directo si ya es objeto
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

                // Precarga los campos del modal con los datos de la sede
                $('#editIdSede').val(response.IdSede);
                $('#editTipoSede').val(response.TipoSede);
                $('#editCiudad').val(response.Ciudad);
                $('#editInstitucion').val(response.IdInstitucion);

                // Abre el modal usando el botón trigger nativo de Bootstrap 4
                $('#btnTriggerModal').trigger('click');
            },
            error: function () {
                Swal.fire('Error de conexión',
                    'No se pudo conectar al servidor.', 'error');
            }
        });
    });


    // ══════════════════════════════════════════════════════
    // GUARDAR EDICIÓN DESDE EL MODAL
    // Valida los campos, confirma con SweetAlert2 y envía
    // AJAX al controlador. Cierra el modal manualmente
    // para evitar conflictos con Bootstrap 4.
    // ══════════════════════════════════════════════════════
    $(document).on('click', '#btnGuardarEdicion', function () {

        var tipoSede = $('#editTipoSede').val().trim();
        var ciudad   = $('#editCiudad').val().trim();
        var idSede   = $('#editIdSede').val();
        var idInst   = $('#editInstitucion').val();

        // ── Validación final antes de enviar ──

        var hayError = false;

        // Valida Tipo de Sede
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

        // Valida Ciudad
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

        // Si hay errores detiene el proceso y muestra SweetAlert
        if (hayError) {
            Swal.fire({
                icon: 'error',
                title: 'Campos incorrectos',
                text: 'Por favor corrige los campos marcados en rojo.',
                confirmButtonColor: '#ef4444'
            });
            return;
        }

        // Confirmación SweetAlert2 antes de guardar
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

            // AJAX: envía los datos editados al controlador
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
                        // Error de negocio: sede duplicada u otro
                        Swal.fire({
                            icon: 'warning',
                            title: 'No se pudo guardar',
                            text: response.message || 'Ocurrió un error inesperado.',
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#f59e0b'
                        });
                        return;
                    }

                    // Cierra el modal manualmente (evita conflicto Bootstrap 4 + SweetAlert2)
                    $('#modalEditarSede').removeClass('show').hide();
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();

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
    // No recarga la página: actualiza el badge y el ícono
    // directamente en el DOM para mejor experiencia
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

        // Confirmación diferenciada según la acción
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
            // Spinner mientras procesa la petición
            icon.removeClass().addClass('fas fa-spinner fa-spin');

            $.ajax({
                url:      urlControladorSede,
                type:     'POST',
                dataType: 'json',
                data: { accion: 'cambiarEstado', id: id },
                success: function (response) {

                    btn.prop('disabled', false);

                    if (!response || !response.success) {
                        // Restaura el ícono del candado si falla
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

                    // Actualiza badge e ícono directamente en el DOM sin recargar
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

                    // Éxito con timer
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