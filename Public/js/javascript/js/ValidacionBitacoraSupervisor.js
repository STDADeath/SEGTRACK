// ============================================================
// ValidacionBitacoraSupervisor.js
// Unifica: registro de bitácora + lista con columnas Supervisor / Personal Seguridad
// Filtrado por TipoRol desde tabla usuario (no CargoFuncionario de funcionario)
// ============================================================

function esperarDependencias(cb) {
    if (typeof $ !== 'undefined' && typeof Swal !== 'undefined') cb();
    else setTimeout(function () { esperarDependencias(cb); }, 50);
}

esperarDependencias(function () {

    const pad = n => String(n).padStart(2, '0');

    let tablaBitacoraSupervisorDT = null;
    let bitacoraACambiar          = null;
    let estadoActualBitacora      = null;

    // ══════════════════════════════════════════════
    // HELPERS DE FECHA
    // ══════════════════════════════════════════════
    function getFechaActualConHora() {
        const ahora = new Date();
        return `${ahora.getFullYear()}-${pad(ahora.getMonth()+1)}-${pad(ahora.getDate())}T${pad(ahora.getHours())}:${pad(ahora.getMinutes())}`;
    }

    function getAhoraTruncado() {
        const ahora = new Date();
        ahora.setSeconds(0, 0);
        return ahora;
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
    // HELPERS DE CELDAS (lista)
    // Usan row.CargoFuncionario que ahora viene como alias
    // de u.TipoRol desde el modelo (JOIN con tabla usuario)
    // ══════════════════════════════════════════════
    function colorTurnoBit(turno) {
        const c = { 'Jornada mañana': 'warning', 'Jornada tarde': 'info', 'Jornada noche': 'dark' };
        return c[turno] ?? 'secondary';
    }

    function celdaSupervisor(row) {
        // CargoFuncionario es alias de u.TipoRol (valor: 'Supervisor')
        const rol = (row.CargoFuncionario ?? '').trim();
        if (rol === 'Supervisor' && row.NombreFuncionario) {
            return `<span><i class="fas fa-user-tie text-primary mr-1"></i>${row.NombreFuncionario}</span>`;
        }
        return '<span class="text-muted fst-italic">No aplica</span>';
    }

    function celdaPersonalSeguridad(row) {
        // CargoFuncionario es alias de u.TipoRol (valor: 'Personal Seguridad')
        const rol = (row.CargoFuncionario ?? '').trim();
        if (rol === 'Personal Seguridad' && row.NombreFuncionario) {
            return `<span><i class="fas fa-user-shield text-success mr-1"></i>${row.NombreFuncionario}</span>`;
        }
        return '<span class="text-muted fst-italic">No aplica</span>';
    }

    // ══════════════════════════════════════════════
    // BADGE DE ROL (modal editar)
    // Usa u.TipoRol (alias CargoFuncionario) para el badge
    // ══════════════════════════════════════════════
    function buildBadgeRol(tipoRol) {
        const mapa = {
            'Supervisor':        { color: 'primary', icono: 'fa-user-tie',    label: 'Supervisor'        },
            'Personal Seguridad':{ color: 'success', icono: 'fa-user-shield', label: 'Personal Seguridad'},
            'Administrador':     { color: 'danger',  icono: 'fa-user-cog',    label: 'Administrador'     }
        };
        const cfg = mapa[tipoRol] ?? { color: 'secondary', icono: 'fa-user', label: tipoRol || 'Sin rol' };
        return `<span class="badge bg-${cfg.color} mt-1">
                    <i class="fas ${cfg.icono} me-1"></i>${cfg.label}
                </span>`;
    }

    // ══════════════════════════════════════════════
    // FECHA INICIAL (formulario registro)
    // ══════════════════════════════════════════════
    const inputFecha = document.getElementById('FechaBitacora');

    if (inputFecha) {
        inputFecha.setAttribute('min', getFechaActualConHora());
        inputFecha.value = getFechaActualConHora();

        setInterval(function () {
            inputFecha.setAttribute('min', getFechaActualConHora());
        }, 60000);

        inputFecha.addEventListener('change', function () {
            const sel   = new Date(this.value);
            const ahora = getAhoraTruncado();
            if (sel < ahora) {
                Swal.fire({ icon:'warning', title:'Fecha inválida', text:'La fecha no puede ser anterior a la hora actual', confirmButtonColor:'#f6c23e' });
                this.value = getFechaActualConHora();
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }

    // ══════════════════════════════════════════════
    // CARGAR SUPERVISORES (dropdown registro)
    // Acción 'supervisores' → modelo filtra por u.TipoRol = 'Supervisor'
    // ══════════════════════════════════════════════
    function cargarSupervisores() {
        const select = document.getElementById('IdFuncionario');
        const msgDiv = document.getElementById('msgPersonal');
        if (!select) return;

        fetch('/SEGTRACK/App/Controller/ControladorBitacora.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    'accion=supervisores'
        })
        .then(r => r.json())
        .then(res => {
            select.innerHTML = '<option value="" disabled selected>-- Seleccione supervisor --</option>';
            if (!Array.isArray(res) || res.length === 0) {
                select.innerHTML += '<option value="" disabled>Sin supervisores disponibles</option>';
                if (msgDiv) { msgDiv.textContent = 'No hay supervisores activos registrados.'; msgDiv.className = 'form-text text-warning'; }
                return;
            }
            res.forEach(function (item) {
                const opt       = document.createElement('option');
                opt.value       = item.IdFuncionario;
                opt.textContent = item.NombreCompleto;
                select.appendChild(opt);
            });
            if (msgDiv) msgDiv.textContent = '';
        })
        .catch(function () {
            select.innerHTML = '<option value="" disabled selected>Error al cargar supervisores</option>';
            if (msgDiv) { msgDiv.textContent = 'No se pudo conectar con el servidor.'; msgDiv.className = 'form-text text-danger'; }
        });
    }

    if (document.getElementById('IdFuncionario')) {
        cargarSupervisores();
    }

    // ══════════════════════════════════════════════
    // CARGAR VISITANTES
    // ══════════════════════════════════════════════
    function cargarVisitantes() {
        const select = document.getElementById('IdVisitante');
        const msgDiv = document.getElementById('msgVisitante');
        if (!select) return;

        fetch('/SEGTRACK/App/Controller/ControladorBitacora.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    'accion=visitantes'
        })
        .then(r => r.json())
        .then(res => {
            select.innerHTML = '<option value="" disabled selected>-- Seleccione visitante --</option>';
            if (!Array.isArray(res) || res.length === 0) {
                select.innerHTML += '<option value="" disabled>Sin visitantes disponibles</option>';
                if (msgDiv) { msgDiv.textContent = 'No hay visitantes activos registrados.'; msgDiv.className = 'form-text text-warning'; }
                return;
            }
            res.forEach(function (item) {
                const opt       = document.createElement('option');
                opt.value       = item.IdVisitante;
                opt.textContent = item.NombreCompleto;
                select.appendChild(opt);
            });
            if (msgDiv) msgDiv.textContent = '';
        })
        .catch(function () {
            if (msgDiv) { msgDiv.textContent = 'No se pudo cargar visitantes.'; msgDiv.className = 'form-text text-danger'; }
        });
    }

    // ══════════════════════════════════════════════
    // CARGAR DISPOSITIVOS (filtrado por visitante)
    // ══════════════════════════════════════════════
    function cargarDispositivos(idVisitante) {
        const select = document.getElementById('IdDispositivo');
        const msgDiv = document.getElementById('msgDispositivo');
        if (!select) return;

        select.innerHTML = '<option value="" disabled selected>Cargando...</option>';

        const body = idVisitante
            ? `accion=dispositivos&IdVisitante=${encodeURIComponent(idVisitante)}`
            : 'accion=dispositivos';

        fetch('/SEGTRACK/App/Controller/ControladorBitacora.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    body
        })
        .then(r => r.json())
        .then(res => {
            select.innerHTML = '<option value="" disabled selected>-- Seleccione dispositivo --</option>';
            if (!Array.isArray(res) || res.length === 0) {
                select.innerHTML += '<option value="" disabled>Sin dispositivos disponibles</option>';
                if (msgDiv) { msgDiv.textContent = 'No hay dispositivos activos para este visitante.'; msgDiv.className = 'form-text text-warning'; }
                return;
            }
            res.forEach(function (item) {
                const opt       = document.createElement('option');
                opt.value       = item.IdDispositivo;
                opt.textContent = item.NombreCompleto;
                select.appendChild(opt);
            });
            if (msgDiv) msgDiv.textContent = '';
        })
        .catch(function () {
            select.innerHTML = '<option value="" disabled selected>Error al cargar dispositivos</option>';
            if (msgDiv) { msgDiv.textContent = 'No se pudo cargar dispositivos.'; msgDiv.className = 'form-text text-danger'; }
        });
    }

    // ══════════════════════════════════════════════
    // LÓGICA CONDICIONAL: visitante / dispositivo
    // ══════════════════════════════════════════════
    const selTieneVisitante  = document.getElementById('TieneVisitante');
    const selTraeDispositivo = document.getElementById('TraeDispositivo');
    const selVisitante       = document.getElementById('IdVisitante');
    const contVisitante      = document.getElementById('VisitanteContainer');
    const contDispositivo    = document.getElementById('DispositivoContainer');

    if (selTieneVisitante) {
        selTieneVisitante.addEventListener('change', function () {
            if (this.value === 'si') {
                contVisitante.style.display = '';
                cargarVisitantes();
            } else {
                contVisitante.style.display   = 'none';
                contDispositivo.style.display = 'none';
                if (selVisitante)       selVisitante.value       = '';
                if (selTraeDispositivo) selTraeDispositivo.value = '';
                const selDev = document.getElementById('IdDispositivo');
                if (selDev) selDev.value = '';
            }
        });
    }

    if (selTraeDispositivo) {
        selTraeDispositivo.addEventListener('change', function () {
            if (this.value === 'si') {
                contDispositivo.style.display = '';
                cargarDispositivos(selVisitante ? selVisitante.value : null);
            } else {
                contDispositivo.style.display = 'none';
                const selDev = document.getElementById('IdDispositivo');
                if (selDev) selDev.value = '';
            }
        });
    }

    if (selVisitante) {
        selVisitante.addEventListener('change', function () {
            if (selTraeDispositivo && selTraeDispositivo.value === 'si') {
                cargarDispositivos(this.value);
            }
        });
    }

    // ══════════════════════════════════════════════
    // PDF: preview y botón quitar
    // ══════════════════════════════════════════════
    const inputPDF     = document.getElementById('ReportePDF');
    const pdfPreview   = document.getElementById('pdfPreview');
    const pdfNombre    = document.getElementById('pdfNombre');
    const btnQuitarPDF = document.getElementById('btnQuitarPDF');

    if (inputPDF) {
        inputPDF.addEventListener('change', function () {
            const archivo = this.files[0];
            if (!archivo) { if (pdfPreview) pdfPreview.style.display = 'none'; return; }
            if (archivo.type !== 'application/pdf') {
                Swal.fire({ icon:'warning', title:'Archivo inválido', text:'Solo se permiten archivos PDF.', confirmButtonColor:'#f6c23e' });
                this.value = ''; if (pdfPreview) pdfPreview.style.display = 'none'; return;
            }
            if (archivo.size > 5 * 1024 * 1024) {
                Swal.fire({ icon:'warning', title:'Archivo muy grande', text:'El PDF no debe superar los 5 MB.', confirmButtonColor:'#f6c23e' });
                this.value = ''; if (pdfPreview) pdfPreview.style.display = 'none'; return;
            }
            if (pdfNombre)  pdfNombre.textContent    = archivo.name;
            if (pdfPreview) pdfPreview.style.display = '';
        });
    }

    if (btnQuitarPDF) {
        btnQuitarPDF.addEventListener('click', function () {
            if (inputPDF)   inputPDF.value = '';
            if (pdfPreview) pdfPreview.style.display = 'none';
        });
    }

    // ══════════════════════════════════════════════
    // VALIDACIONES EN TIEMPO REAL (registro)
    // ══════════════════════════════════════════════
    const txtNovedad = document.getElementById('NovedadesBitacora');
    if (txtNovedad) {
        txtNovedad.addEventListener('blur', function () {
            const txt = this.value.trim();
            if (txt.length > 0 && txt.length < 10) {
                Swal.fire({ icon:'warning', title:'Texto muy corto', text:'Las novedades deben tener al menos 10 caracteres', confirmButtonColor:'#f6c23e' });
                this.classList.add('is-invalid'); this.focus();
            } else { this.classList.remove('is-invalid'); }
        });
    }

    ['TurnoBitacora', 'IdFuncionario', 'TieneVisitante'].forEach(function (id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('change', function () { this.classList.toggle('is-invalid', !this.value); });
    });

    // ══════════════════════════════════════════════
    // ENVÍO DEL FORMULARIO REGISTRO
    // ══════════════════════════════════════════════
    const form = document.getElementById('formRegistrarBitacoraSupervisor');
    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const turno          = document.getElementById('TurnoBitacora').value;
            const fecha          = document.getElementById('FechaBitacora').value;
            const novedad        = document.getElementById('NovedadesBitacora').value.trim();
            const funcionario    = document.getElementById('IdFuncionario').value;
            const tieneVisitante = document.getElementById('TieneVisitante').value;
            const ahoraSubmit    = getAhoraTruncado();

            if (!turno) {
                Swal.fire({ icon:'error', title:'Campo requerido', text:'Debe seleccionar el turno', confirmButtonColor:'#e74a3b' });
                document.getElementById('TurnoBitacora').classList.add('is-invalid'); return;
            }
            if (!fecha || new Date(fecha) < ahoraSubmit) {
                Swal.fire({ icon:'error', title:'Fecha inválida', text:'La fecha es obligatoria y no puede ser anterior a la hora actual', confirmButtonColor:'#e74a3b' });
                document.getElementById('FechaBitacora').classList.add('is-invalid'); return;
            }
            if (novedad.length < 10) {
                Swal.fire({ icon:'error', title:'Novedades muy cortas', text:'Las novedades deben tener al menos 10 caracteres', confirmButtonColor:'#e74a3b' });
                document.getElementById('NovedadesBitacora').classList.add('is-invalid'); return;
            }
            if (!funcionario) {
                Swal.fire({ icon:'error', title:'Campo requerido', text:'Debe seleccionar el supervisor', confirmButtonColor:'#e74a3b' });
                document.getElementById('IdFuncionario').classList.add('is-invalid'); return;
            }
            if (!tieneVisitante) {
                Swal.fire({ icon:'error', title:'Campo requerido', text:'Debe indicar si hay visitante', confirmButtonColor:'#e74a3b' });
                document.getElementById('TieneVisitante').classList.add('is-invalid'); return;
            }
            if (tieneVisitante === 'si') {
                const visitante = document.getElementById('IdVisitante').value;
                if (!visitante) {
                    Swal.fire({ icon:'error', title:'Campo requerido', text:'Debe seleccionar el visitante', confirmButtonColor:'#e74a3b' });
                    document.getElementById('IdVisitante').classList.add('is-invalid'); return;
                }
                const traeDispositivo = document.getElementById('TraeDispositivo').value;
                if (!traeDispositivo) {
                    Swal.fire({ icon:'error', title:'Campo requerido', text:'Debe indicar si el visitante trae dispositivo', confirmButtonColor:'#e74a3b' });
                    document.getElementById('TraeDispositivo').classList.add('is-invalid'); return;
                }
                if (traeDispositivo === 'si') {
                    const dispositivo = document.getElementById('IdDispositivo').value;
                    if (!dispositivo) {
                        Swal.fire({ icon:'error', title:'Campo requerido', text:'Debe seleccionar el dispositivo', confirmButtonColor:'#e74a3b' });
                        document.getElementById('IdDispositivo').classList.add('is-invalid'); return;
                    }
                }
            }

            const btn      = form.querySelector('button[type=submit]');
            const original = btn.innerHTML;
            btn.disabled   = true;

            Swal.fire({
                title: 'Procesando...',
                html:  '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br>Registrando bitácora',
                allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false
            });

            const formData = new FormData(form);
            formData.append('accion', 'registrar');

            try {
                const response = await fetch('/SEGTRACK/App/Controller/ControladorBitacora.php', { method:'POST', body:formData });
                const data     = await response.json();
                Swal.close();

                if (data.success) {
                    Swal.fire({
                        icon: 'success', title: '¡Bitácora registrada!',
                        text: 'La bitácora fue guardada correctamente',
                        timer: 3000, timerProgressBar: true,
                        showConfirmButton: true, confirmButtonColor: '#1cc88a',
                        confirmButtonText: 'Entendido'
                    }).then(function () {
                        form.reset();
                        document.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
                        if (contVisitante)   contVisitante.style.display   = 'none';
                        if (contDispositivo) contDispositivo.style.display = 'none';
                        if (pdfPreview)      pdfPreview.style.display      = 'none';
                        const nuevaFecha = getFechaActualConHora();
                        if (inputFecha) { inputFecha.setAttribute('min', nuevaFecha); inputFecha.value = nuevaFecha; }
                        cargarSupervisores();
                    });
                } else {
                    Swal.fire({
                        icon: 'warning', title: 'No se pudo registrar',
                        html: (data.message || 'Error').replace(/\n/g, '<br>'),
                        confirmButtonColor: '#f6c23e', confirmButtonText: 'Entendido',
                        footer: '<small class="text-muted">Revise la información e intente nuevamente</small>'
                    });
                }
            } catch (error) {
                Swal.close();
                Swal.fire({
                    icon: 'error', title: 'Error de conexión',
                    html: 'No se pudo conectar al servidor.<br>Intente nuevamente.',
                    confirmButtonColor: '#e74a3b',
                    footer: '<small>Si el problema persiste, contacte al administrador</small>'
                });
            } finally {
                btn.innerHTML = original; btn.disabled = false;
            }
        });
    }

    // ══════════════════════════════════════════════
    // CARGAR BITÁCORAS (lista)
    // ══════════════════════════════════════════════
    function cargarBitacorasSupervisor() {
        const turno       = $('#filtroTurno').val()       || '';
        const fecha       = $('#filtroFecha').val()       || '';
        const funcionario = $('#filtroFuncionario').val() || '';
        const visitante   = $('#filtroVisitante').val()   || '';
        const estado      = $('#filtroEstado').val()      || '';

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
                    tbody.innerHTML = `<tr><td colspan="11" class="text-center py-5">
                        <i class="fas fa-exclamation-circle fa-2x text-muted mb-2 d-block"></i>
                        <span class="text-muted">No hay bitácoras registradas con los filtros seleccionados</span>
                    </td></tr>`;
                    $('#contadorBitacoras').text('0 registros');
                    return;
                }

                tbody.innerHTML = res.map(function (row) {

                    const visitanteHtml = row.NombreVisitante
                        ? `<small><i class="fas fa-user text-success mr-1"></i>${row.NombreVisitante}</small>`
                        : `<span class="badge badge-light border">No aplica</span>`;

                    const dispositivoHtml = row.NombreDispositivo
                        ? `<small><i class="fas fa-laptop text-secondary mr-1"></i>${row.NombreDispositivo}</small>`
                        : `<span class="badge badge-light border">No aplica</span>`;

                    const pdfHtml = row.ReporteBitacora
                        ? `<a href="/SEGTRACK/Public/${row.ReporteBitacora}" target="_blank"
                              class="btn btn-sm btn-outline-danger" title="Ver PDF">
                              <i class="fas fa-file-pdf"></i> Ver
                           </a>`
                        : `<span class="badge badge-light border">Sin PDF</span>`;

                    return `<tr class="${row.Estado === 'Inactivo' ? 'fila-inactiva' : ''}">
                        <td class="fw-bold text-muted">${row.IdBitacora}</td>
                        <td><span class="badge bg-${colorTurnoBit(row.TurnoBitacora)}">${row.TurnoBitacora}</span></td>
                        <td class="text-start" style="max-width:200px;">
                            <span title="${row.NovedadesBitacora ?? ''}">
                                ${(row.NovedadesBitacora ?? '').substring(0, 55)}${(row.NovedadesBitacora ?? '').length > 55 ? '...' : ''}
                            </span>
                        </td>
                        <td class="text-nowrap">${formatFechaBit(row.FechaBitacora)}</td>
                        <td>${celdaSupervisor(row)}</td>
                        <td>${celdaPersonalSeguridad(row)}</td>
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
                    columnDefs: [{ orderable: false, targets: [8, 10] }]
                });
            },
            error: function () {
                const tbody = document.getElementById('cuerpoTablaBitacoraSupervisor');
                if (tbody) tbody.innerHTML = `<tr><td colspan="11" class="text-center py-4 text-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Error al cargar los datos
                </td></tr>`;
                $('#contadorBitacoras').text('Error');
            }
        });
    }

    // ══════════════════════════════════════════════
    // BOTONES FILTRAR / LIMPIAR
    // ══════════════════════════════════════════════
    $('#btnFiltrar').on('click', function () { cargarBitacorasSupervisor(); });

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
    // El badge usa row.CargoFuncionario (alias de u.TipoRol)
    // a través de buildBadgeRol()
    // ══════════════════════════════════════════════
    window.abrirModalEditarBitacora = function (row) {
        $('#editIdBitacora').val(row.IdBitacora);
        $('#editIdBitacoraLabel').text(row.IdBitacora);
        $('#editTurnoBitacora').val(row.TurnoBitacora);
        $('#editFechaBitacora').val(fechaParaInput(row.FechaBitacora));
        $('#editNovedadesBitacora').val(row.NovedadesBitacora ?? '');
        $('#editNombreFuncionarioBit').val(row.NombreFuncionario ?? '');
        $('#editIdFuncionarioBit').val(row.IdFuncionario);
        $('#editNombreVisitanteBit').val(row.NombreVisitante || 'No aplica');
        $('#editIdVisitanteBit').val(row.IdVisitante ?? '');
        $('#editNombreDispositivoBit').val(row.NombreDispositivo || 'No aplica');
        $('#editIdDispositivoBit').val(row.IdDispositivo ?? '');

        // Badge de rol — usa u.TipoRol (alias CargoFuncionario)
        const tipoRol = (row.CargoFuncionario ?? '').trim();
        $('#editCargoBadge').html(buildBadgeRol(tipoRol));

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
        const id      = $('#editIdBitacora').val();
        const turno   = $('#editTurnoBitacora').val();
        const fecha   = $('#editFechaBitacora').val();
        const novedad = $('#editNovedadesBitacora').val().trim();
        const idFun   = $('#editIdFuncionarioBit').val();

        if (!turno || !fecha || !idFun) {
            Swal.fire({ icon: 'warning', title: 'Campos incompletos', text: 'Turno, fecha y funcionario son obligatorios.', confirmButtonColor: '#f6c23e' });
            return;
        }
        if (novedad.length > 0 && novedad.length < 10) {
            Swal.fire({ icon: 'warning', title: 'Novedades muy cortas', text: 'Las novedades deben tener al menos 10 caracteres.', confirmButtonColor: '#f6c23e' });
            return;
        }

        $('#modalEditarBitacora').modal('hide');
        Swal.fire({ title: 'Guardando cambios...', html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>', allowOutsideClick: false, showConfirmButton: false });

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
                IdVisitante:       $('#editIdVisitanteBit').val()  || '',
                IdDispositivo:     $('#editIdDispositivoBit').val() || ''
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

        Swal.fire({ title: 'Procesando...', html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>', allowOutsideClick: false, showConfirmButton: false });

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
    if (document.getElementById('cuerpoTablaBitacoraSupervisor')) {
        cargarBitacorasSupervisor();
    }

}); // fin esperarDependencias