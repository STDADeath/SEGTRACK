// ============================================================
// ValidacionVisitanteSupervisor.js
// Unifica: registro de visitante + lista con filtros AJAX,
// modal editar y modal cambio de estado.
// ============================================================

function esperarDependenciasVis(cb) {
    if (typeof $ !== 'undefined' && typeof Swal !== 'undefined') cb();
    else setTimeout(function () { esperarDependenciasVis(cb); }, 50);
}

esperarDependenciasVis(function () {

    const CONTROLLER = '/SEGTRACK/App/Controller/ControladorVisitante.php';

    let tablaVisitanteDT      = null;
    let visitanteACambiar     = null;   // ← guarda IdVisitante (número)
    let estadoActualVisitante = null;   // ← guarda estado actual ('Activo'/'Inactivo')

    // ══════════════════════════════════════════════
    // HELPERS VISUALES
    // ══════════════════════════════════════════════
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

    // ══════════════════════════════════════════════
    // CARGAR INSTITUCIONES (reutilizable)
    // ══════════════════════════════════════════════
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
                        const sel = valorSeleccionado && String(inst.IdInstitucion) === String(valorSeleccionado) ? 'selected' : '';
                        $select.append(`<option value="${inst.IdInstitucion}" ${sel}>${inst.NombreInstitucion}</option>`);
                    });
                    if (valorSeleccionado) $select.trigger('change');
                }
            }
        });
    }

    // ══════════════════════════════════════════════
    // CARGAR SEDES (cascada institución → sede)
    // ══════════════════════════════════════════════
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
                        const sel = valorSede && String(sede.IdSede) === String(valorSede) ? 'selected' : '';
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

    // ══════════════════════════════════════════════
    // INSTITUCIONES EN FILTRO DE LISTA
    // ══════════════════════════════════════════════
    function cargarInstitucionesFiltro() {
        const $select = $('#filtroInstitucion');
        if (!$select.length) return;
        $.ajax({
            url: CONTROLLER, type: 'POST',
            data: { accion: 'obtener_instituciones' },
            dataType: 'json',
            success: function (res) {
                if (Array.isArray(res)) {
                    res.forEach(function (inst) {
                        $select.append(`<option value="${inst.IdInstitucion}">${inst.NombreInstitucion}</option>`);
                    });
                }
            }
        });
    }

    // ══════════════════════════════════════════════
    // ── SECCIÓN REGISTRO ──────────────────────────
    // ══════════════════════════════════════════════
    const formReg = document.getElementById('formRegistrarVisitanteSupervisor');

    if (formReg) {

        const duplicados = { identificacion: false, correo: false };

        cargarInstituciones('#IdInstitucion', null);

        $('#IdInstitucion').on('change', function () {
            if ($(this).val()) {
                marcarOk('#IdInstitucion');
                cargarSedes($(this).val(), '#IdSede', '#spinnerSede', null);
            } else {
                limpiarMarca('#IdInstitucion');
                $('#IdSede').html('<option value="">Primero seleccione una institución...</option>').prop('disabled', true);
                limpiarMarca('#IdSede');
            }
        });

        $('#IdSede').on('change', function () {
            $(this).val() ? marcarOk(this) : limpiarMarca(this);
        });

        $('#IdentificacionVisitante').on('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 11);
            limpiarMarca(this);
            duplicados.identificacion = false;
        });

        $('#IdentificacionVisitante').on('blur', function () {
            const val = $(this).val().trim();
            if (!val) return;
            if (!/^\d{6,11}$/.test(val)) {
                marcarError(this, 'Ingrese solo números (6 a 11 dígitos).');
                duplicados.identificacion = true;
                return;
            }
            $.ajax({
                url: CONTROLLER, type: 'POST',
                data: { accion: 'verificar', IdentificacionVisitante: val },
                dataType: 'json',
                success: function (res) {
                    if (res.duplicado && res.campo === 'identificacion') {
                        marcarError('#IdentificacionVisitante', '⚠ Esta identificación ya está registrada.');
                        duplicados.identificacion = true;
                    } else {
                        marcarOk('#IdentificacionVisitante');
                        duplicados.identificacion = false;
                    }
                }
            });
        });

        $('#NombreVisitante').on('input', function () {
            this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s]/g, '');
            limpiarMarca(this);
        });

        $('#NombreVisitante').on('blur', function () {
            const val = $(this).val().trim();
            if (!val) return;
            /^[a-zA-ZÀ-ÿ\s]{3,100}$/.test(val)
                ? marcarOk(this)
                : marcarError(this, 'Solo letras, mínimo 3 caracteres.');
        });

        $('#CorreoVisitante').on('blur', function () {
            const val = $(this).val().trim();
            limpiarMarca(this);
            duplicados.correo = false;
            if (!val) return;
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(val)) {
                marcarError(this, 'Ingrese un correo válido.');
                duplicados.correo = true;
                return;
            }
            $.ajax({
                url: CONTROLLER, type: 'POST',
                data: { accion: 'verificar', IdentificacionVisitante: '', CorreoVisitante: val },
                dataType: 'json',
                success: function (res) {
                    if (res.duplicado && res.campo === 'correo') {
                        marcarError('#CorreoVisitante', '⚠ Este correo ya está registrado.');
                        duplicados.correo = true;
                    } else {
                        marcarOk('#CorreoVisitante');
                        duplicados.correo = false;
                    }
                }
            });
        });

        $(formReg).on('submit', function (e) {
            e.preventDefault();

            const id     = $('#IdentificacionVisitante').val().trim();
            const nombre = $('#NombreVisitante').val().trim();
            const correo = $('#CorreoVisitante').val().trim();
            const idInst = $('#IdInstitucion').val();
            const idSede = $('#IdSede').val();

            if (duplicados.identificacion) {
                Swal.fire({ icon: 'error', title: 'Identificación duplicada', text: 'Ya existe un visitante con esa identificación.', confirmButtonColor: '#e74a3b' });
                return;
            }
            if (duplicados.correo) {
                Swal.fire({ icon: 'error', title: 'Correo duplicado', text: 'Ya existe un visitante con ese correo.', confirmButtonColor: '#e74a3b' });
                return;
            }
            if (!/^\d{6,11}$/.test(id)) {
                Swal.fire({ icon: 'error', title: 'Identificación inválida', text: 'Ingrese solo números (6 a 11 dígitos).', confirmButtonColor: '#e74a3b' });
                return;
            }
            if (!/^[a-zA-ZÀ-ÿ\s]{3,100}$/.test(nombre)) {
                Swal.fire({ icon: 'error', title: 'Nombre inválido', text: 'El nombre solo debe contener letras (mínimo 3 caracteres).', confirmButtonColor: '#e74a3b' });
                return;
            }
            if (correo && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(correo)) {
                Swal.fire({ icon: 'error', title: 'Correo inválido', text: 'Ingrese un correo electrónico válido.', confirmButtonColor: '#e74a3b' });
                return;
            }
            if (!idInst) {
                Swal.fire({ icon: 'warning', title: 'Institución requerida', text: 'Debe seleccionar una institución.', confirmButtonColor: '#f6c23e' });
                return;
            }
            if (!idSede) {
                Swal.fire({ icon: 'warning', title: 'Sede requerida', text: 'Debe seleccionar una sede.', confirmButtonColor: '#f6c23e' });
                return;
            }

            const $btn     = $('#btnRegistrarVisitante');
            const original = $btn.html();
            $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Procesando...').prop('disabled', true);

            Swal.fire({
                title: 'Procesando...',
                html:  '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br>Registrando visitante',
                allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false
            });

            $.ajax({
                url:      CONTROLLER,
                type:     'POST',
                data:     $(formReg).serialize() + '&accion=registrar',
                dataType: 'json',
                success: function (res) {
                    Swal.close();
                    if (res.success) {
                        Swal.fire({
                            icon: 'success', title: '¡Visitante registrado!', text: res.message,
                            timer: 3000, timerProgressBar: true,
                            showConfirmButton: true, confirmButtonColor: '#1cc88a', confirmButtonText: 'Entendido'
                        }).then(function () {
                            formReg.reset();
                            $('#IdSede').html('<option value="">Primero seleccione una institución...</option>').prop('disabled', true);
                            $('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
                            $('.invalid-feedback').remove();
                            duplicados.identificacion = false;
                            duplicados.correo         = false;
                        });
                    } else {
                        Swal.fire({ icon: 'warning', title: 'No se pudo registrar', html: (res.message || 'Error').replace(/\n/g, '<br>'), confirmButtonColor: '#f6c23e' });
                    }
                },
                error: function () {
                    Swal.close();
                    Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor.', confirmButtonColor: '#e74a3b' });
                },
                complete: function () {
                    $btn.html(original).prop('disabled', false);
                }
            });
        });

    } // fin registro

    // ══════════════════════════════════════════════
    // ── SECCIÓN LISTA ─────────────────────────────
    // ══════════════════════════════════════════════
    const tablaBody = document.getElementById('cuerpoTablaVisitanteSupervisor');

    if (tablaBody) {

        cargarInstitucionesFiltro();

        // ── CARGAR VISITANTES ──────────────────────
        function cargarVisitantesSupervisor() {
            const identificacion = $('#filtroIdentificacion').val() || '';
            const nombre         = $('#filtroNombre').val()         || '';
            const idInstitucion  = $('#filtroInstitucion').val()    || '';
            const estado         = $('#filtroEstado').val()         || '';

            $.ajax({
                url:  CONTROLLER,
                type: 'POST',
                data: {
                    accion:        'mostrar',
                    identificacion: identificacion,
                    nombre:         nombre,
                    idInstitucion:  idInstitucion,
                    estado:         estado
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

                        // ── Correo ──
                        const correoHtml = row.CorreoVisitante
                            ? `<a href="mailto:${row.CorreoVisitante}"><small>${row.CorreoVisitante}</small></a>`
                            : `<span class="badge bg-info text-white">No aplica</span>`;

                        // ── Institución ──
                        const instHtml = row.NombreInstitucion
                            ? `<small><i class="fas fa-university text-secondary mr-1"></i>${row.NombreInstitucion}</small>`
                            : `<span class="text-muted">—</span>`;

                        // ── Sede ──
                        const sedeHtml = row.TipoSede
                            ? `<span class="badge bg-light text-dark border">
                                   <i class="fas fa-map-marker-alt mr-1 text-danger"></i>
                                   ${row.TipoSede}${row.Ciudad ? ' · ' + row.Ciudad : ''}
                               </span>`
                            : `<span class="text-muted">—</span>`;

                        // ── Estado badge ──
                        const estadoBadge = row.Estado === 'Activo'
                            ? `<span class="badge bg-success">Activo</span>`
                            : `<span class="badge bg-secondary">Inactivo</span>`;

                        // ── Botón estado: amarillo si Activo (para desactivar), verde si Inactivo (para activar) ──
                        const btnEstadoClass = row.Estado === 'Activo' ? 'btn-outline-warning' : 'btn-outline-success';
                        const btnEstadoIcon  = row.Estado === 'Activo' ? 'fa-lock'             : 'fa-lock-open';
                        const btnEstadoTitle = row.Estado === 'Activo' ? 'Desactivar visitante' : 'Activar visitante';

                        // IMPORTANTE: IdVisitante como número entero en el onclick
                        const idVisitante = parseInt(row.IdVisitante, 10);
                        const estadoEsc   = row.Estado.replace(/'/g, "\\'");

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
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick='abrirModalEditarVisitante(${JSON.stringify(row)})'
                                        title="Editar visitante"
                                        data-toggle="modal" data-target="#modalEditarVisitante">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button"
                                        class="btn btn-sm ${btnEstadoClass}"
                                        onclick="confirmarCambioEstadoVisitante(${idVisitante}, '${estadoEsc}')"
                                        title="${btnEstadoTitle}">
                                        <i class="fas ${btnEstadoIcon}"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                    }).join(''));

                    const total = res.length;
                    $('#contadorVisitantes').text(total + ' registro' + (total !== 1 ? 's' : ''));

                    tablaVisitanteDT = $('#TablaVisitanteSupervisor').DataTable({
                        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json' },
                        pageLength: 10,
                        responsive: true,
                        order: [[0, 'desc']],
                        columnDefs: [{ orderable: false, targets: [7] }]
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

        // ── FILTRAR / LIMPIAR ──────────────────────
        $('#btnFiltrar').on('click', function () { cargarVisitantesSupervisor(); });

        $('#btnLimpiar').on('click', function () {
            $('#filtroIdentificacion').val('');
            $('#filtroNombre').val('');
            $('#filtroInstitucion').val('');
            $('#filtroEstado').val('');
            cargarVisitantesSupervisor();
        });

        // ══════════════════════════════════════════════
        // MODAL EDITAR
        // ══════════════════════════════════════════════
        window.abrirModalEditarVisitante = function (row) {
            $('#editIdVisitante').val(row.IdVisitante);
            $('#editIdVisitanteLabel').text(row.IdVisitante);

            // Identificación: solo lectura
            $('#editIdentificacionVisitante').val(row.IdentificacionVisitante);

            // Nombre: EDITABLE — sin readonly, sin bg-light
            $('#editNombreVisitante').val(row.NombreVisitante).prop('readonly', false).removeClass('bg-light');

            // Correo
            $('#editCorreoVisitante').val(row.CorreoVisitante || '');

            // Limpiar marcas previas
            ['#editNombreVisitante', '#editCorreoVisitante',
             '#editIdInstitucion',   '#editIdSede'].forEach(limpiarMarca);

            // Guardar IdSede para preseleccionar después de cargar sedes
            $('#editIdInstitucion').data('idSede', row.IdSede);

            // Cargar instituciones y disparar change (que carga sedes)
            cargarInstituciones('#editIdInstitucion', row.IdInstitucion);
        };

        // Cascada institución → sede en modal editar
        $('#editIdInstitucion').on('change', function () {
            const idInst = $(this).val();
            const idSede = $(this).data('idSede') || null;
            $(this).removeData('idSede'); // consumir solo una vez

            if (idInst) {
                marcarOk('#editIdInstitucion');
                cargarSedes(idInst, '#editIdSede', '#editSpinnerSede', idSede);
            } else {
                limpiarMarca('#editIdInstitucion');
                $('#editIdSede')
                    .html('<option value="">Primero seleccione una institución...</option>')
                    .prop('disabled', true);
                limpiarMarca('#editIdSede');
            }
        });

        $('#editIdSede').on('change', function () {
            $(this).val() ? marcarOk(this) : limpiarMarca(this);
        });

        // Solo letras en nombre del modal
        $('#editNombreVisitante').on('input', function () {
            this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s]/g, '');
            limpiarMarca(this);
        });

        // Guardar edición
        $('#btnGuardarEdicionVisitante').on('click', function () {
            const id             = $('#editIdVisitante').val();
            const identificacion = $('#editIdentificacionVisitante').val().trim();
            const nombre         = $('#editNombreVisitante').val().trim();
            const correo         = $('#editCorreoVisitante').val().trim();
            const idInstitucion  = $('#editIdInstitucion').val();
            const idSede         = $('#editIdSede').val();

            if (!/^[a-zA-ZÀ-ÿ\s]{3,100}$/.test(nombre)) {
                marcarError('#editNombreVisitante', 'Solo letras, mínimo 3 caracteres.');
                return;
            }
            if (correo && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(correo)) {
                marcarError('#editCorreoVisitante', 'Ingrese un correo válido.');
                return;
            }
            if (!idInstitucion) {
                marcarError('#editIdInstitucion', 'Debe seleccionar una institución.');
                return;
            }
            if (!idSede) {
                marcarError('#editIdSede', 'Debe seleccionar una sede.');
                return;
            }

            $('#modalEditarVisitante').modal('hide');

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
                        }).then(function () { cargarVisitantesSupervisor(); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'No se pudo actualizar', text: r.message || 'Error desconocido', confirmButtonColor: '#e74a3b' });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#e74a3b' });
                }
            });
        });

        // Limpiar modal al cerrar
        $('#modalEditarVisitante').on('hidden.bs.modal', function () {
            ['#editNombreVisitante', '#editCorreoVisitante',
             '#editIdInstitucion',   '#editIdSede'].forEach(limpiarMarca);
            $('#editIdInstitucion').removeData('idSede');
        });

        // ══════════════════════════════════════════════
        // CAMBIO DE ESTADO
        // ══════════════════════════════════════════════
        window.confirmarCambioEstadoVisitante = function (id, estado) {
            // Guardar en variables del closure (NO en el DOM)
            visitanteACambiar     = id;
            estadoActualVisitante = estado;

            const nuevo  = estado === 'Activo' ? 'Inactivo' : 'Activo';
            const accion = nuevo  === 'Activo' ? 'activar'  : 'desactivar';
            const color  = nuevo  === 'Activo' ? 'bg-success' : 'bg-warning';
            const icono  = nuevo  === 'Activo' ? 'fa-lock-open' : 'fa-lock';

            $('#headerCambioEstadoVisitante')
                .removeClass('bg-success bg-warning')
                .addClass(color + ' text-white');

            $('#tituloCambioEstadoVisitante').html(
                `<i class="fas ${icono} mr-2"></i>` +
                accion.charAt(0).toUpperCase() + accion.slice(1) + ' Visitante'
            );

            $('#mensajeCambioEstadoVisitante').html(
                `¿Está seguro que desea <strong>${accion}</strong> este visitante?`
            );

            setTimeout(function () {
                const toggle = document.getElementById('toggleEstadoVisualVisitante');
                if (toggle) {
                    nuevo === 'Activo'
                        ? toggle.classList.add('activo')
                        : toggle.classList.remove('activo');
                }
            }, 100);

            $('#modalCambiarEstadoVisitante').modal('show');
        };

        $('#btnConfirmarCambioEstadoVisitante').on('click', function () {
            // Usar las variables del closure, no atributos del DOM
            if (visitanteACambiar === null) return;

            const idParaEnviar = visitanteACambiar;
            const nuevo        = estadoActualVisitante === 'Activo' ? 'Inactivo' : 'Activo';

            $('#modalCambiarEstadoVisitante').modal('hide');

            Swal.fire({
                title: 'Procesando...',
                html:  '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>',
                allowOutsideClick: false, showConfirmButton: false
            });

            $.ajax({
                url:  CONTROLLER,
                type: 'POST',
                data: {
                    accion:      'cambiar_estado',
                    IdVisitante: idParaEnviar,   // número entero
                    nuevoEstado: nuevo
                },
                dataType: 'json',
                success: function (r) {
                    if (r.success) {
                        Swal.fire({
                            icon: 'success', title: '¡Éxito!', text: r.message,
                            timer: 2000, timerProgressBar: true, showConfirmButton: false
                        }).then(function () { cargarVisitantesSupervisor(); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: r.message, confirmButtonColor: '#e74a3b' });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#e74a3b' });
                }
            });
        });

        // Resetear variables al cerrar modal
        $('#modalCambiarEstadoVisitante').on('hidden.bs.modal', function () {
            visitanteACambiar     = null;
            estadoActualVisitante = null;
        });

        // ── CARGA INICIAL ──────────────────────────
        cargarVisitantesSupervisor();

    } // fin lista

}); // fin esperarDependenciasVis