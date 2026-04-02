// ============================================================
// ValidacionBitacoraSupervisor.js
// ============================================================

function esperarDependencias(cb) {
    if (typeof $ !== 'undefined' && typeof Swal !== 'undefined') cb();
    else setTimeout(function () { esperarDependencias(cb); }, 50);
}

esperarDependencias(function () {

    let tablaBitacoraSupervisorDT = null;
    let bitacoraACambiar          = null;
    let estadoActualBitacora      = null;

    // ══════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════
    function colorTurnoBit(turno) {
        const c = { 'Jornada mañana': 'warning', 'Jornada tarde': 'info', 'Jornada noche': 'dark' };
        return c[turno] ?? 'secondary';
    }

    function formatFechaBit(fecha) {
        if (!fecha) return '—';
        const d = new Date(fecha);
        return String(d.getDate()).padStart(2,'0') + '/' +
               String(d.getMonth()+1).padStart(2,'0') + '/' +
               d.getFullYear() + ' ' +
               String(d.getHours()).padStart(2,'0') + ':' +
               String(d.getMinutes()).padStart(2,'0');
    }

    function fechaParaInput(fecha) {
        if (!fecha) return '';
        const d = new Date(fecha);
        return d.getFullYear() + '-' +
               String(d.getMonth()+1).padStart(2,'0') + '-' +
               String(d.getDate()).padStart(2,'0') + 'T' +
               String(d.getHours()).padStart(2,'0') + ':' +
               String(d.getMinutes()).padStart(2,'0');
    }

    // ══════════════════════════════════════════════
    // CARGAR BITÁCORAS
    // ══════════════════════════════════════════════
    function cargarBitacorasSupervisor() {
        const turno      = $('#filtroTurno').val()       || '';
        const fecha      = $('#filtroFecha').val()       || '';
        const funcionario = $('#filtroFuncionario').val() || '';
        const visitante  = $('#filtroVisitante').val()   || '';
        const estado     = $('#filtroEstado').val()      || '';

        $.ajax({
            url:  '/SEGTRACK/App/Controller/ControladorBitacora.php',
            type: 'POST',
            data: {
                accion:      'mostrar',
                turno:       turno,
                fecha:       fecha,
                funcionario: funcionario,
                visitante:   visitante,
                estadoReg:   estado
            },
            dataType: 'json',
            success: function (res) {
                const tbody = document.getElementById('cuerpoTablaBitacoraSupervisor');
                if (!tbody) return;

                if (tablaBitacoraSupervisorDT) {
                    tablaBitacoraSupervisorDT.destroy();
                    tablaBitacoraSupervisorDT = null;
                }

                if (!Array.isArray(res) || res.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="10" class="text-center py-5">
                        <i class="fas fa-exclamation-circle fa-2x text-muted mb-2 d-block"></i>
                        <span class="text-muted">No hay bitácoras registradas con los filtros seleccionados</span>
                    </td></tr>`;
                    $('#contadorBitacoras').text('0 registros');
                    return;
                }

                tbody.innerHTML = res.map(function (row) {
                    const visitanteHtml = row.NombreVisitante
                        ? `<small><i class="fas fa-user text-success mr-1"></i>${row.NombreVisitante}</small>`
                        : `<span class="badge badge-info">No aplica</span>`;

                    const dispositivoHtml = row.NombreDispositivo
                        ? `<small><i class="fas fa-laptop text-secondary mr-1"></i>${row.NombreDispositivo}</small>`
                        : `<span class="badge badge-info">No aplica</span>`;

                    const pdfHtml = row.ReporteBitacora
                        ? `<a href="/SEGTRACK/Public/${row.ReporteBitacora}" target="_blank"
                              class="btn btn-sm btn-outline-danger" title="Ver PDF">
                              <i class="fas fa-file-pdf"></i> Ver
                           </a>`
                        : `<span class="badge badge-info">Sin PDF</span>`;

                    return `<tr class="${row.Estado === 'Inactivo' ? 'fila-inactiva' : ''}">
                        <td class="fw-bold text-muted">${row.IdBitacora}</td>
                        <td><span class="badge bg-${colorTurnoBit(row.TurnoBitacora)}">${row.TurnoBitacora}</span></td>
                        <td class="text-start" style="max-width:220px;">
                            <span title="${row.NovedadesBitacora ?? ''}">
                                ${(row.NovedadesBitacora ?? '').substring(0, 60)}${(row.NovedadesBitacora ?? '').length > 60 ? '...' : ''}
                            </span>
                        </td>
                        <td class="text-nowrap">${formatFechaBit(row.FechaBitacora)}</td>
                        <td>
                            <i class="fas fa-user-shield text-primary mr-1"></i>
                            ${row.NombreFuncionario ?? '—'}
                        </td>
                        <td>${visitanteHtml}</td>
                        <td>${dispositivoHtml}</td>
                        <td>${pdfHtml}</td>
                        <td>
                            <span class="badge bg-${row.Estado === 'Activo' ? 'success' : 'secondary'}">
                                ${row.Estado ?? ''}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick='abrirModalEditarBitacora(${JSON.stringify(row)})'
                                    title="Editar bitácora"
                                    data-toggle="modal" data-target="#modalEditarBitacora">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button"
                                    class="btn btn-sm ${row.Estado === 'Activo' ? 'btn-outline-warning' : 'btn-outline-success'}"
                                    onclick="confirmarCambioEstadoBitacora(${row.IdBitacora}, '${row.Estado}')"
                                    title="${row.Estado === 'Activo' ? 'Desactivar' : 'Activar'} bitácora">
                                    <i class="fas ${row.Estado === 'Activo' ? 'fa-lock' : 'fa-lock-open'}"></i>
                                </button>
                            </div>
                        </td>
                    </tr>`;
                }).join('');

                const total = res.length;
                $('#contadorBitacoras').text(total + ' registro' + (total !== 1 ? 's' : ''));

                tablaBitacoraSupervisorDT = $('#TablaBitacoraSupervisor').DataTable({
                    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json' },
                    pageLength: 10,
                    responsive: true,
                    order: [[0, 'desc']],
                    columnDefs: [{ orderable: false, targets: [7, 9] }]
                });
            },
            error: function () {
                const tbody = document.getElementById('cuerpoTablaBitacoraSupervisor');
                if (tbody) tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Error al cargar los datos
                </td></tr>`;
                $('#contadorBitacoras').text('Error');
            }
        });
    }

    // ══════════════════════════════════════════════
    // BOTONES FILTRAR / LIMPIAR
    // ══════════════════════════════════════════════
    $('#btnFiltrar').on('click', function () {
        cargarBitacorasSupervisor();
    });

    $('#btnLimpiar').on('click', function () {
        $('#filtroTurno').val('');
        $('#filtroFecha').val('');
        $('#filtroFuncionario').val('');
        $('#filtroVisitante').val('');
        $('#filtroEstado').val('');
        cargarBitacorasSupervisor();
    });

    // ══════════════════════════════════════════════
    // MODAL EDITAR
    // ══════════════════════════════════════════════
    window.abrirModalEditarBitacora = function (row) {
        $('#editIdBitacora').val(row.IdBitacora);
        $('#editIdBitacoraLabel').text(row.IdBitacora);
        $('#editTurnoBitacora').val(row.TurnoBitacora);
        $('#editFechaBitacora').val(fechaParaInput(row.FechaBitacora));
        $('#editNovedadesBitacora').val(row.NovedadesBitacora ?? '');
        $('#editNombreFuncionarioBit').val(row.NombreFuncionario ?? '');
        $('#editIdFuncionarioBit').val(row.IdFuncionario);
        $('#editNombreVisitanteBit').val(row.NombreVisitante ?? 'No aplica');
        $('#editIdVisitanteBit').val(row.IdVisitante ?? '');
        $('#editNombreDispositivoBit').val(row.NombreDispositivo ?? 'No aplica');
        $('#editIdDispositivoBit').val(row.IdDispositivo ?? '');

        if (row.ReporteBitacora) {
            $('#editPdfActual').html(
                `<a href="/SEGTRACK/Public/${row.ReporteBitacora}" target="_blank"
                    class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-file-pdf mr-1"></i>Ver PDF actual
                 </a>`
            );
        } else {
            $('#editPdfActual').html('<span class="text-muted">Sin PDF adjunto</span>');
        }
    };

    $('#btnGuardarEdicionBitacora').on('click', function () {
        const id       = $('#editIdBitacora').val();
        const turno    = $('#editTurnoBitacora').val();
        const fecha    = $('#editFechaBitacora').val();
        const novedad  = $('#editNovedadesBitacora').val().trim();
        const idFun    = $('#editIdFuncionarioBit').val();

        if (!turno || !fecha || !idFun) {
            Swal.fire({ icon: 'warning', title: 'Campos incompletos', text: 'Turno, fecha y funcionario son obligatorios.', confirmButtonColor: '#f6c23e' });
            return;
        }

        if (novedad.length > 0 && novedad.length < 10) {
            Swal.fire({ icon: 'warning', title: 'Novedades muy cortas', text: 'Las novedades deben tener al menos 10 caracteres.', confirmButtonColor: '#f6c23e' });
            return;
        }

        $('#modalEditarBitacora').modal('hide');

        Swal.fire({
            title: 'Guardando cambios...',
            html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>',
            allowOutsideClick: false, showConfirmButton: false
        });

        $.ajax({
            url:  '/SEGTRACK/App/Controller/ControladorBitacora.php',
            type: 'POST',
            data: {
                accion:            'actualizar',
                IdBitacora:        id,
                TurnoBitacora:     turno,
                FechaBitacora:     fecha,
                NovedadesBitacora: novedad,
                IdFuncionario:     idFun,
                IdVisitante:       $('#editIdVisitanteBit').val()   || '',
                IdDispositivo:     $('#editIdDispositivoBit').val()  || ''
            },
            dataType: 'json',
            success: function (r) {
                if (r.success) {
                    Swal.fire({
                        icon: 'success', title: '¡Actualizado!',
                        text: 'La bitácora fue actualizada correctamente.',
                        timer: 2500, timerProgressBar: true, showConfirmButton: false
                    }).then(function () { cargarBitacorasSupervisor(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'No se pudo actualizar', text: r.message || 'Error desconocido', confirmButtonColor: '#e74a3b' });
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#e74a3b' });
            }
        });
    });

    // ══════════════════════════════════════════════
    // CAMBIO DE ESTADO
    // ══════════════════════════════════════════════
    window.confirmarCambioEstadoBitacora = function (id, estado) {
        bitacoraACambiar     = id;
        estadoActualBitacora = estado;

        const nuevo  = estado === 'Activo' ? 'Inactivo' : 'Activo';
        const accion = nuevo  === 'Activo' ? 'activar'  : 'desactivar';
        const color  = nuevo  === 'Activo' ? 'bg-success' : 'bg-warning';
        const icono  = nuevo  === 'Activo' ? 'fa-lock-open' : 'fa-lock';

        $('#headerCambioEstadoBitacora').removeClass('bg-success bg-warning').addClass(color + ' text-white');
        $('#tituloCambioEstadoBitacora').html('<i class="fas ' + icono + ' mr-2"></i>' + accion.charAt(0).toUpperCase() + accion.slice(1) + ' Bitácora');
        $('#mensajeCambioEstadoBitacora').html('¿Está seguro que desea <strong>' + accion + '</strong> esta bitácora?');

        setTimeout(function () {
            const toggle = document.getElementById('toggleEstadoVisualBitacora');
            if (toggle) {
                nuevo === 'Activo' ? toggle.classList.add('activo') : toggle.classList.remove('activo');
            }
        }, 100);

        $('#modalCambiarEstadoBitacora').modal('show');
    };

    $('#btnConfirmarCambioEstadoBitacora').on('click', function () {
        if (!bitacoraACambiar) return;

        const nuevo = estadoActualBitacora === 'Activo' ? 'Inactivo' : 'Activo';
        $('#modalCambiarEstadoBitacora').modal('hide');

        Swal.fire({
            title: 'Procesando...',
            html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>',
            allowOutsideClick: false, showConfirmButton: false
        });

        $.ajax({
            url:  '/SEGTRACK/App/Controller/ControladorBitacora.php',
            type: 'POST',
            data: { accion: 'cambiar_estado', IdBitacora: bitacoraACambiar, nuevoEstado: nuevo },
            dataType: 'json',
            success: function (r) {
                if (r.success) {
                    Swal.fire({
                        icon: 'success', title: '¡Éxito!', text: r.message,
                        timer: 2000, timerProgressBar: true, showConfirmButton: false
                    }).then(function () { cargarBitacorasSupervisor(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: r.message, confirmButtonColor: '#e74a3b' });
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#e74a3b' });
            }
        });
    });

    $('#modalCambiarEstadoBitacora').on('hidden.bs.modal', function () {
        bitacoraACambiar     = null;
        estadoActualBitacora = null;
    });

    // ══════════════════════════════════════════════
    // CARGA INICIAL
    // ══════════════════════════════════════════════
    cargarBitacorasSupervisor();

}); // fin esperarDependencias