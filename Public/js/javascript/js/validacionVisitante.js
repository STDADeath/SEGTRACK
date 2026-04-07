// ============================================================
// validacionVisitante.js
// Registro de visitante + Lista con filtros AJAX + Modal editar
// ============================================================

function esperarDependenciasVisitante(cb) {
    if (typeof $ !== 'undefined' && typeof Swal !== 'undefined') cb();
    else setTimeout(function () { esperarDependenciasVisitante(cb); }, 50);
}

esperarDependenciasVisitante(function () {

    const CONTROLLER = '/SEGTRACK/App/Controller/ControladorVisitante.php';

    let tablaVisitanteDT = null;

    // ══════════════════════════════════════════════════════════
    // HELPERS VISUALES
    // ══════════════════════════════════════════════════════════
    function marcarError(selector, mensaje) {
        const $el = $(selector);
        $el.removeClass('is-valid').addClass('is-invalid');
        $el.siblings('.invalid-feedback').remove();
        $el.after(`<div class="invalid-feedback d-block">${mensaje}</div>`);
    }

    function marcarOk(selector) {
        $(selector).removeClass('is-invalid').addClass('is-valid')
                   .siblings('.invalid-feedback').remove();
    }

    function limpiarMarca(selector) {
        $(selector).removeClass('is-valid is-invalid')
                   .siblings('.invalid-feedback').remove();
    }

    // ══════════════════════════════════════════════════════════
    // CARGAR INSTITUCIONES (reutilizable: registro y modal)
    // ══════════════════════════════════════════════════════════
    function cargarInstituciones(selectId, valorSeleccionado) {
        const $select = $(selectId);
        if (!$select.length) return;

        $.ajax({
            url:      CONTROLLER,
            type:     'POST',
            data:     { accion: 'obtener_instituciones' },
            dataType: 'json',
            success: function (res) {
                $select.html('<option value="">Seleccione institución...</option>');
                if (Array.isArray(res) && res.length > 0) {
                    res.forEach(function (inst) {
                        const sel = valorSeleccionado &&
                                    String(inst.IdInstitucion) === String(valorSeleccionado)
                                    ? 'selected' : '';
                        $select.append(`<option value="${inst.IdInstitucion}" ${sel}>${inst.NombreInstitucion}</option>`);
                    });
                    if (valorSeleccionado) $select.trigger('change');
                }
            },
            error: function () {
                $select.html('<option value="">Error al cargar instituciones</option>');
            }
        });
    }

    // ══════════════════════════════════════════════════════════
    // CARGAR SEDES (cascada institución → sede, reutilizable)
    // ══════════════════════════════════════════════════════════
    function cargarSedes(idInstitucion, sedeSelectId, spinnerDivId, valorSede) {
        const $selectSede = $(sedeSelectId);
        const $spinner    = $(spinnerDivId);

        if (!$selectSede.length) return;

        $selectSede.html('<option value="">Seleccione una sede...</option>').prop('disabled', true);
        limpiarMarca(sedeSelectId);

        if (!idInstitucion) {
            $selectSede.html('<option value="">Primero seleccione una institución...</option>');
            return;
        }

        $spinner.removeClass('d-none');

        $.ajax({
            url:      CONTROLLER,
            type:     'POST',
            data:     { accion: 'obtener_sedes', IdInstitucion: idInstitucion },
            dataType: 'json',
            success: function (res) {
                $spinner.addClass('d-none');
                if (res.success && res.sedes && res.sedes.length > 0) {
                    $selectSede.prop('disabled', false);
                    res.sedes.forEach(function (sede) {
                        const sel = valorSede && String(sede.IdSede) === String(valorSede)
                                    ? 'selected' : '';
                        $selectSede.append(`<option value="${sede.IdSede}" ${sel}>${sede.TipoSede} – ${sede.Ciudad}</option>`);
                    });
                    if (valorSede) marcarOk(sedeSelectId);
                } else {
                    $selectSede.html('<option value="">No hay sedes disponibles</option>');
                    marcarError(sedeSelectId, 'Esta institución no tiene sedes activas.');
                }
            },
            error: function () {
                $spinner.addClass('d-none');
                marcarError(sedeSelectId, 'No se pudieron cargar las sedes.');
            }
        });
    }

    // ══════════════════════════════════════════════════════════
    // BLOQUE: FORMULARIO DE REGISTRO
    // Solo se activa si existe #formRegistrarVisitante en la página
    // ══════════════════════════════════════════════════════════
    if ($('#formRegistrarVisitante').length) {

        // Cargar instituciones al iniciar
        cargarInstituciones('#IdInstitucion');

        // Cascada institución → sede (formulario registro)
        $('#IdInstitucion').on('change', function () {
            const idInst = $(this).val();

            if (idInst) {
                marcarOk('#IdInstitucion');
                cargarSedes(idInst, '#IdSede', '#spinnerSede', null);
            } else {
                limpiarMarca('#IdInstitucion');
                $('#IdSede')
                    .html('<option value="">Primero seleccione una institución...</option>')
                    .prop('disabled', true);
                limpiarMarca('#IdSede');
            }
        });

        $('#IdSede').on('change', function () {
            $(this).val() ? marcarOk(this) : limpiarMarca(this);
        });

        // Validaciones en tiempo real
        $('#IdentificacionVisitante').on('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
            limpiarMarca(this);
        });

        $('#NombreVisitante').on('input', function () {
            this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s]/g, '');
            limpiarMarca(this);
        });

        $('#CorreoVisitante').on('input', function () { limpiarMarca(this); });

        // Submit registro
        $('#formRegistrarVisitante').on('submit', function (e) {
            e.preventDefault();

            const identificacion = $('#IdentificacionVisitante').val().trim();
            const nombre         = $('#NombreVisitante').val().trim();
            const correo         = $('#CorreoVisitante').val().trim();
            const idInstitucion  = $('#IdInstitucion').val();
            const idSede         = $('#IdSede').val();

            let valido = true;

            if (!/^\d{6,11}$/.test(identificacion)) {
                marcarError('#IdentificacionVisitante', 'Solo números (6 a 11 dígitos).');
                valido = false;
            } else { marcarOk('#IdentificacionVisitante'); }

            if (!/^[a-zA-ZÀ-ÿ\s]{3,100}$/.test(nombre)) {
                marcarError('#NombreVisitante', 'Mínimo 3 letras, solo caracteres válidos.');
                valido = false;
            } else { marcarOk('#NombreVisitante'); }

            if (correo && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(correo)) {
                marcarError('#CorreoVisitante', 'Ingrese un correo válido.');
                valido = false;
            } else { marcarOk('#CorreoVisitante'); }

            if (!idInstitucion) {
                marcarError('#IdInstitucion', 'Debe seleccionar una institución.');
                valido = false;
            } else { marcarOk('#IdInstitucion'); }

            if (!idSede) {
                marcarError('#IdSede', 'Debe seleccionar una sede.');
                valido = false;
            } else { marcarOk('#IdSede'); }

            if (!valido) return;

            Swal.fire({
                title: 'Registrando visitante...',
                html:  '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>',
                allowOutsideClick: false,
                showConfirmButton: false
            });

            $.ajax({
                url:  CONTROLLER,
                type: 'POST',
                data: {
                    accion:                  'registrar',
                    IdentificacionVisitante: identificacion,
                    NombreVisitante:         nombre,
                    CorreoVisitante:         correo,
                    IdSede:                  idSede
                },
                dataType: 'json',
                success: function (r) {
                    if (r.success) {
                        Swal.fire({
                            icon: 'success', title: '¡Registrado!',
                            text: 'El visitante fue registrado correctamente.',
                            timer: 2500, timerProgressBar: true, showConfirmButton: false
                        }).then(function () { limpiarFormularioRegistro(); });
                    } else {
                        Swal.fire({
                            icon: 'error', title: 'No se pudo registrar',
                            text: r.message || 'Error desconocido.',
                            confirmButtonColor: '#e74a3b'
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error', title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor.',
                        confirmButtonColor: '#e74a3b'
                    });
                }
            });
        });

        function limpiarFormularioRegistro() {
            $('#formRegistrarVisitante')[0].reset();
            ['#IdentificacionVisitante', '#NombreVisitante',
             '#CorreoVisitante', '#IdInstitucion', '#IdSede'].forEach(limpiarMarca);
            $('#IdSede')
                .html('<option value="">Primero seleccione una institución...</option>')
                .prop('disabled', true);
            cargarInstituciones('#IdInstitucion');
        }

    } // fin bloque registro

    // ══════════════════════════════════════════════════════════
    // BLOQUE: LISTA + MODAL EDITAR (rol seguridad)
    // Solo se activa si existe #cuerpoTablaVisitanteSeguridad
    // ══════════════════════════════════════════════════════════
    const tablaBody = document.getElementById('cuerpoTablaVisitanteSeguridad');

    if (tablaBody) {

        // ── Cargar tabla con filtros ──────────────────────────
        function cargarVisitantesSeguridad() {
            const identificacion = $('#filtroIdentificacion').val() || '';
            const nombre         = $('#filtroNombre').val()         || '';
            const estado         = $('#filtroEstado').val()         || '';

            $.ajax({
                url:  CONTROLLER,
                type: 'POST',
                data: {
                    accion:         'mostrar',
                    identificacion:  identificacion,
                    nombre:          nombre,
                    estado:          estado
                },
                dataType: 'json',
                success: function (res) {

                    if (tablaVisitanteDT) {
                        tablaVisitanteDT.destroy();
                        tablaVisitanteDT = null;
                    }

                    if (!Array.isArray(res) || res.length === 0) {
                        $(tablaBody).html(`<tr><td colspan="8" class="text-center py-5">
                            <i class="fas fa-exclamation-circle fa-2x text-muted mb-2 d-block"></i>
                            <span class="text-muted">No hay visitantes con los filtros seleccionados</span>
                        </td></tr>`);
                        $('#contadorVisitantes').text('0 registros');
                        return;
                    }

                    $(tablaBody).html(res.map(function (row) {

                        const correoHtml = row.CorreoVisitante
                            ? `<a href="mailto:${row.CorreoVisitante}"><small>${row.CorreoVisitante}</small></a>`
                            : `<span class="badge bg-info text-white">No aplica</span>`;

                        const instHtml = row.NombreInstitucion
                            ? `<small><i class="fas fa-university text-secondary mr-1"></i>${row.NombreInstitucion}</small>`
                            : `<span class="text-muted">—</span>`;

                        const sedeHtml = row.TipoSede
                            ? `<span class="badge bg-light text-dark border">
                                   <i class="fas fa-map-marker-alt mr-1 text-danger"></i>
                                   ${row.TipoSede}${row.Ciudad ? ' · ' + row.Ciudad : ''}
                               </span>`
                            : `<span class="text-muted">—</span>`;

                        const estadoBadge = row.Estado === 'Activo'
                            ? `<span class="badge bg-success">Activo</span>`
                            : `<span class="badge bg-secondary">Inactivo</span>`;

                        return `<tr class="${row.Estado === 'Inactivo' ? 'fila-inactiva' : ''}">
                            <td class="fw-bold text-muted">${row.IdVisitante}</td>
                            <td>${row.IdentificacionVisitante}</td>
                            <td>
                                <i class="fas fa-user text-primary mr-1"></i>
                                ${row.NombreVisitante}
                            </td>
                            <td>${correoHtml}</td>
                            <td>${instHtml}</td>
                            <td>${sedeHtml}</td>
                            <td>${estadoBadge}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick='abrirModalEditarVisitanteSeg(${JSON.stringify(row)})'
                                    title="Editar visitante"
                                    data-toggle="modal" data-target="#modalEditarVisitanteSeguridad">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>`;
                    }).join(''));

                    const total = res.length;
                    $('#contadorVisitantes').text(total + ' registro' + (total !== 1 ? 's' : ''));

                    tablaVisitanteDT = $('#TablaVisitanteSeguridad').DataTable({
                        language:    { url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json' },
                        pageLength:  10,
                        responsive:  true,
                        order:       [[0, 'desc']],
                        columnDefs:  [{ orderable: false, targets: [7] }]
                    });
                },
                error: function () {
                    $(tablaBody).html(`<tr><td colspan="8" class="text-center py-4 text-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Error al cargar los datos
                    </td></tr>`);
                    $('#contadorVisitantes').text('Error');
                }
            });
        }

        // Botones filtrar / limpiar
        $('#btnFiltrar').on('click', function () { cargarVisitantesSeguridad(); });

        $('#btnLimpiar').on('click', function () {
            $('#filtroIdentificacion').val('');
            $('#filtroNombre').val('');
            $('#filtroEstado').val('');
            cargarVisitantesSeguridad();
        });

        // ── Modal editar ──────────────────────────────────────
        window.abrirModalEditarVisitanteSeg = function (row) {
            $('#editIdVisitanteSeg').val(row.IdVisitante);
            $('#editIdVisitanteLabelSeg').text(row.IdVisitante);
            $('#editIdentificacionVisitanteSeg').val(row.IdentificacionVisitante);
            $('#editNombreVisitanteSeg').val(row.NombreVisitante).prop('readonly', false).removeClass('bg-light');
            $('#editCorreoVisitanteSeg').val(row.CorreoVisitante || '');

            ['#editNombreVisitanteSeg', '#editCorreoVisitanteSeg',
             '#editIdInstitucionSeg',   '#editIdSedeSeg'].forEach(limpiarMarca);

            $('#editIdInstitucionSeg').data('idSede', row.IdSede);
            cargarInstituciones('#editIdInstitucionSeg', row.IdInstitucion);
        };

        // Cascada institución → sede en modal editar
        $('#editIdInstitucionSeg').on('change', function () {
            const idInst = $(this).val();
            const idSede = $(this).data('idSede') || null;
            $(this).removeData('idSede');

            if (idInst) {
                marcarOk('#editIdInstitucionSeg');
                cargarSedes(idInst, '#editIdSedeSeg', '#editSpinnerSedeSeg', idSede);
            } else {
                limpiarMarca('#editIdInstitucionSeg');
                $('#editIdSedeSeg')
                    .html('<option value="">Primero seleccione una institución...</option>')
                    .prop('disabled', true);
                limpiarMarca('#editIdSedeSeg');
            }
        });

        $('#editIdSedeSeg').on('change', function () {
            $(this).val() ? marcarOk(this) : limpiarMarca(this);
        });

        $('#editNombreVisitanteSeg').on('input', function () {
            this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s]/g, '');
            limpiarMarca(this);
        });

        // Guardar edición
        $('#btnGuardarEdicionVisitanteSeg').on('click', function () {
            const id            = $('#editIdVisitanteSeg').val();
            const identificacion = $('#editIdentificacionVisitanteSeg').val().trim();
            const nombre        = $('#editNombreVisitanteSeg').val().trim();
            const correo        = $('#editCorreoVisitanteSeg').val().trim();
            const idInstitucion = $('#editIdInstitucionSeg').val();
            const idSede        = $('#editIdSedeSeg').val();

            if (!/^[a-zA-ZÀ-ÿ\s]{3,100}$/.test(nombre)) {
                marcarError('#editNombreVisitanteSeg', 'Solo letras, mínimo 3 caracteres.');
                return;
            }
            if (correo && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(correo)) {
                marcarError('#editCorreoVisitanteSeg', 'Ingrese un correo válido.');
                return;
            }
            if (!idInstitucion) {
                marcarError('#editIdInstitucionSeg', 'Debe seleccionar una institución.');
                return;
            }
            if (!idSede) {
                marcarError('#editIdSedeSeg', 'Debe seleccionar una sede.');
                return;
            }

            $('#modalEditarVisitanteSeguridad').modal('hide');

            Swal.fire({
                title: 'Guardando cambios...',
                html:  '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>',
                allowOutsideClick: false, showConfirmButton: false
            });

            $.ajax({
                url:  CONTROLLER,
                type: 'POST',
                data: {
                    accion:                  'actualizar',
                    IdVisitante:             id,
                    IdentificacionVisitante: identificacion,
                    NombreVisitante:         nombre,
                    CorreoVisitante:         correo,
                    IdSede:                  idSede
                },
                dataType: 'json',
                success: function (r) {
                    if (r.success) {
                        Swal.fire({
                            icon: 'success', title: '¡Actualizado!',
                            text: 'El visitante fue actualizado correctamente.',
                            timer: 2500, timerProgressBar: true, showConfirmButton: false
                        }).then(function () { cargarVisitantesSeguridad(); });
                    } else {
                        Swal.fire({
                            icon: 'error', title: 'No se pudo actualizar',
                            text: r.message || 'Error desconocido',
                            confirmButtonColor: '#e74a3b'
                        });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#e74a3b' });
                }
            });
        });

        // Limpiar modal al cerrar
        $('#modalEditarVisitanteSeguridad').on('hidden.bs.modal', function () {
            ['#editNombreVisitanteSeg', '#editCorreoVisitanteSeg',
             '#editIdInstitucionSeg',   '#editIdSedeSeg'].forEach(limpiarMarca);
            $('#editIdInstitucionSeg').removeData('idSede');
        });

        // Carga inicial de la tabla
        cargarVisitantesSeguridad();

    } // fin bloque lista/seguridad

}); // fin esperarDependenciasVisitante