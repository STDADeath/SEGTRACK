// ============================================================
// ValidacionVisitanteSupervisor.js
// ============================================================

function esperarDependencias(cb) {
    if (typeof $ !== 'undefined' && typeof Swal !== 'undefined') cb();
    else setTimeout(function () { esperarDependencias(cb); }, 50);
}

esperarDependencias(function () {

    let tablaVisitanteSupervisorDT = null;
    let visitanteACambiar          = null;
    let estadoActualVisitante      = null;

    // ══════════════════════════════════════════════
    // CARGAR VISITANTES
    // ══════════════════════════════════════════════
    function cargarVisitantesSupervisor() {
        const identificacion = $('#filtroIdentificacion').val() || '';
        const nombre         = $('#filtroNombre').val()         || '';
        const estado         = $('#filtroEstado').val()         || '';

        $.ajax({
            url:  '/SEGTRACK/App/Controller/ControladorVisitante.php',
            type: 'POST',
            data: {
                accion:         'mostrar',
                identificacion: identificacion,
                nombre:         nombre,
                estado:         estado
            },
            dataType: 'json',
            success: function (res) {
                const tbody = document.getElementById('cuerpoTablaVisitanteSupervisor');
                if (!tbody) return;

                if (tablaVisitanteSupervisorDT) {
                    tablaVisitanteSupervisorDT.destroy();
                    tablaVisitanteSupervisorDT = null;
                }

                if (!Array.isArray(res) || res.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="5" class="text-center py-5">
                        <i class="fas fa-exclamation-circle fa-2x text-muted mb-2 d-block"></i>
                        <span class="text-muted">No hay visitantes registrados con los filtros seleccionados</span>
                    </td></tr>`;
                    $('#contadorVisitantes').text('0 registros');
                    return;
                }

                tbody.innerHTML = res.map(function (row) {
                    const correoHtml = row.CorreoVisitante
                        ? `<a href="mailto:${row.CorreoVisitante}">${row.CorreoVisitante}</a>`
                        : `<span class="badge badge-info">No aplica</span>`;

                    return `<tr class="${row.Estado === 'Inactivo' ? 'fila-inactiva' : ''}">
                        <td class="fw-bold">${row.IdentificacionVisitante}</td>
                        <td>
                            <i class="fas fa-user text-primary mr-1"></i>
                            ${row.NombreVisitante}
                        </td>
                        <td>${correoHtml}</td>
                        <td>
                            <span class="badge bg-${row.Estado === 'Activo' ? 'success' : 'secondary'}">
                                ${row.Estado}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick='abrirModalEditarVisitante(${JSON.stringify(row)})'
                                    title="Editar visitante"
                                    data-toggle="modal" data-target="#modalEditarVisitante">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button"
                                    class="btn btn-sm ${row.Estado === 'Activo' ? 'btn-outline-warning' : 'btn-outline-success'}"
                                    onclick="confirmarCambioEstadoVisitante(${row.IdVisitante}, '${row.Estado}')"
                                    title="${row.Estado === 'Activo' ? 'Desactivar' : 'Activar'} visitante">
                                    <i class="fas ${row.Estado === 'Activo' ? 'fa-lock' : 'fa-lock-open'}"></i>
                                </button>
                            </div>
                        </td>
                    </tr>`;
                }).join('');

                const total = res.length;
                $('#contadorVisitantes').text(total + ' registro' + (total !== 1 ? 's' : ''));

                tablaVisitanteSupervisorDT = $('#TablaVisitanteSupervisor').DataTable({
                    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json' },
                    pageLength: 10,
                    responsive: true,
                    order: [[0, 'desc']],
                    columnDefs: [{ orderable: false, targets: [4] }]
                });
            },
            error: function () {
                const tbody = document.getElementById('cuerpoTablaVisitanteSupervisor');
                if (tbody) tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Error al cargar los datos
                </td></tr>`;
                $('#contadorVisitantes').text('Error');
            }
        });
    }

    // ══════════════════════════════════════════════
    // BOTONES FILTRAR / LIMPIAR
    // ══════════════════════════════════════════════
    $('#btnFiltrar').on('click', function () {
        cargarVisitantesSupervisor();
    });

    $('#btnLimpiar').on('click', function () {
        $('#filtroIdentificacion').val('');
        $('#filtroNombre').val('');
        $('#filtroEstado').val('');
        cargarVisitantesSupervisor();
    });

    // ══════════════════════════════════════════════
    // MODAL EDITAR
    // ══════════════════════════════════════════════
    window.abrirModalEditarVisitante = function (row) {
        $('#editIdVisitante').val(row.IdVisitante);
        $('#editIdVisitanteLabel').text(row.IdentificacionVisitante);
        $('#editIdentificacionVisitante').val(row.IdentificacionVisitante);
        $('#editNombreVisitante').val(row.NombreVisitante);
        $('#editCorreoVisitante').val(row.CorreoVisitante ?? '');
    };

    $('#btnGuardarEdicionVisitante').on('click', function () {
        const id     = $('#editIdVisitante').val();
        const nombre = $('#editNombreVisitante').val().trim();
        const correo = $('#editCorreoVisitante').val().trim();

        if (!nombre) {
            Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'El nombre del visitante es obligatorio.', confirmButtonColor: '#f6c23e' });
            return;
        }

        if (!/^[a-zA-ZÀ-ÿ\s]{3,100}$/.test(nombre)) {
            Swal.fire({ icon: 'warning', title: 'Nombre inválido', text: 'El nombre solo debe contener letras (mínimo 3 caracteres).', confirmButtonColor: '#f6c23e' });
            return;
        }

        if (correo && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(correo)) {
            Swal.fire({ icon: 'warning', title: 'Correo inválido', text: 'Ingrese un correo electrónico válido.', confirmButtonColor: '#f6c23e' });
            return;
        }

        $('#modalEditarVisitante').modal('hide');

        Swal.fire({
            title: 'Guardando cambios...',
            html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>',
            allowOutsideClick: false, showConfirmButton: false
        });

        $.ajax({
            url:  '/SEGTRACK/App/Controller/ControladorVisitante.php',
            type: 'POST',
            data: {
                accion:                  'actualizar',
                IdVisitante:             id,
                IdentificacionVisitante: $('#editIdentificacionVisitante').val(),
                NombreVisitante:         nombre,
                CorreoVisitante:         correo
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
                    Swal.fire({ icon: 'error', title: 'No se pudo actualizar', text: r.message || r.error || 'Error desconocido', confirmButtonColor: '#e74a3b' });
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
    window.confirmarCambioEstadoVisitante = function (id, estado) {
        visitanteACambiar     = id;
        estadoActualVisitante = estado;

        const nuevo  = estado === 'Activo' ? 'Inactivo' : 'Activo';
        const accion = nuevo  === 'Activo' ? 'activar'  : 'desactivar';
        const color  = nuevo  === 'Activo' ? 'bg-success' : 'bg-warning';
        const icono  = nuevo  === 'Activo' ? 'fa-lock-open' : 'fa-lock';

        $('#headerCambioEstadoVisitante').removeClass('bg-success bg-warning').addClass(color + ' text-white');
        $('#tituloCambioEstadoVisitante').html('<i class="fas ' + icono + ' mr-2"></i>' + accion.charAt(0).toUpperCase() + accion.slice(1) + ' Visitante');
        $('#mensajeCambioEstadoVisitante').html('¿Está seguro que desea <strong>' + accion + '</strong> este visitante?');

        setTimeout(function () {
            const toggle = document.getElementById('toggleEstadoVisualVisitante');
            if (toggle) {
                nuevo === 'Activo' ? toggle.classList.add('activo') : toggle.classList.remove('activo');
            }
        }, 100);

        $('#modalCambiarEstadoVisitante').modal('show');
    };

    $('#btnConfirmarCambioEstadoVisitante').on('click', function () {
        if (!visitanteACambiar) return;

        const nuevo = estadoActualVisitante === 'Activo' ? 'Inactivo' : 'Activo';
        $('#modalCambiarEstadoVisitante').modal('hide');

        Swal.fire({
            title: 'Procesando...',
            html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>',
            allowOutsideClick: false, showConfirmButton: false
        });

        $.ajax({
            url:  '/SEGTRACK/App/Controller/ControladorVisitante.php',
            type: 'POST',
            data: { accion: 'cambiar_estado', IdVisitante: visitanteACambiar, nuevoEstado: nuevo },
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

    $('#modalCambiarEstadoVisitante').on('hidden.bs.modal', function () {
        visitanteACambiar     = null;
        estadoActualVisitante = null;
    });

    // ══════════════════════════════════════════════
    // CARGA INICIAL
    // ══════════════════════════════════════════════
    cargarVisitantesSupervisor();

}); // fin esperarDependencias