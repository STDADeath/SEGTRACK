document.addEventListener('DOMContentLoaded', function () {

    const pad = n => String(n).padStart(2, '0');

    // ══════════════════════════════════════════════
    // 1. FECHA INICIAL
    // ══════════════════════════════════════════════
    const ahora = new Date();
    const fechaMinimaDelDia  = `${ahora.getFullYear()}-${pad(ahora.getMonth()+1)}-${pad(ahora.getDate())}T00:00`;
    const fechaActualConHora = `${ahora.getFullYear()}-${pad(ahora.getMonth()+1)}-${pad(ahora.getDate())}T${pad(ahora.getHours())}:${pad(ahora.getMinutes())}`;

    const inputFecha = document.getElementById('FechaBitacora');
    if (inputFecha) {   
        inputFecha.setAttribute('min', fechaMinimaDelDia);
        inputFecha.value = fechaActualConHora;
    }

    // ══════════════════════════════════════════════
    // 2. CARGAR PERSONAL DE SEGURIDAD
    // ══════════════════════════════════════════════
    function cargarPersonalSeguridad() {
        const select = document.getElementById('IdFuncionario');
        const msgDiv = document.getElementById('msgPersonal');
        if (!select) return;

        fetch('../../Controller/ControladorBitacora.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    'accion=personal_seguridad'
        })
        .then(r => r.json())
        .then(res => {
            select.innerHTML = '<option value="" disabled selected>-- Seleccione personal --</option>';
            if (!Array.isArray(res) || res.length === 0) {
                select.innerHTML += '<option value="" disabled>Sin personal disponible</option>';
                if (msgDiv) { msgDiv.textContent = 'No hay personal de seguridad activo registrado.'; msgDiv.className = 'form-text text-warning'; }
                return;
            }
            res.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.IdFuncionario;
                opt.textContent = item.NombreCompleto;
                select.appendChild(opt);
            });
            if (msgDiv) msgDiv.textContent = '';
        })
        .catch(() => {
            select.innerHTML = '<option value="" disabled selected>Error al cargar personal</option>';
            if (msgDiv) { msgDiv.textContent = 'No se pudo conectar con el servidor.'; msgDiv.className = 'form-text text-danger'; }
        });
    }

    // ══════════════════════════════════════════════
    // 3. CARGAR VISITANTES
    // ══════════════════════════════════════════════
    function cargarVisitantes() {
        const select = document.getElementById('IdVisitante');
        const msgDiv = document.getElementById('msgVisitante');
        if (!select) return;

        fetch('../../Controller/ControladorBitacora.php', {
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
            res.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.IdVisitante;
                opt.textContent = item.NombreCompleto;
                select.appendChild(opt);
            });
            if (msgDiv) msgDiv.textContent = '';
        })
        .catch(() => {
            select.innerHTML = '<option value="" disabled selected>Error al cargar visitantes</option>';
            if (msgDiv) { msgDiv.textContent = 'No se pudo conectar con el servidor.'; msgDiv.className = 'form-text text-danger'; }
        });
    }

    // ══════════════════════════════════════════════
    // 4. CARGAR DISPOSITIVOS
    // ══════════════════════════════════════════════
    function cargarDispositivos(idVisitante = '') {
        const select = document.getElementById('IdDispositivo');
        const msgDiv = document.getElementById('msgDispositivo');
        if (!select) return;

        select.innerHTML = '<option value="" disabled selected>Cargando...</option>';

        fetch('../../Controller/ControladorBitacora.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    `accion=dispositivos&IdVisitante=${idVisitante}`
        })
        .then(r => r.json())
        .then(res => {
            select.innerHTML = '<option value="" disabled selected>-- Seleccione dispositivo --</option>';
            if (!Array.isArray(res) || res.length === 0) {
                select.innerHTML += '<option value="" disabled>Sin dispositivos disponibles</option>';
                if (msgDiv) { msgDiv.textContent = 'No hay dispositivos activos para este visitante.'; msgDiv.className = 'form-text text-warning'; }
                return;
            }
            res.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.IdDispositivo;
                opt.textContent = item.NombreCompleto;
                select.appendChild(opt);
            });
            if (msgDiv) msgDiv.textContent = '';
        })
        .catch(() => {
            select.innerHTML = '<option value="" disabled selected>Error al cargar dispositivos</option>';
            if (msgDiv) { msgDiv.textContent = 'No se pudo conectar con el servidor.'; msgDiv.className = 'form-text text-danger'; }
        });
    }

    // Cuando cambia el visitante → recargar dispositivos
    const selIdVisitante = document.getElementById('IdVisitante');
    if (selIdVisitante) {
        selIdVisitante.addEventListener('change', function () {
            this.classList.toggle('is-invalid', !this.value);
            if (document.getElementById('TraeDispositivo')?.value === 'si') {
                cargarDispositivos(this.value);
            }
        });
    }

    cargarPersonalSeguridad();
    cargarVisitantes();

    // ══════════════════════════════════════════════
    // 5. VALIDACIONES EN TIEMPO REAL
    // ══════════════════════════════════════════════
    if (inputFecha) {
        inputFecha.addEventListener('change', function () {
            const seleccionada = new Date(this.value);
            const inicioHoy = new Date(); inicioHoy.setHours(0, 0, 0, 0);
            if (seleccionada < inicioHoy) {
                Swal.fire({ icon: 'warning', title: 'Fecha inválida', text: 'No puede seleccionar una fecha anterior al día de hoy', confirmButtonColor: '#f6c23e' });
                this.value = fechaActualConHora;
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }

    const txtNovedades = document.getElementById('NovedadesBitacora');
    if (txtNovedades) {
        txtNovedades.addEventListener('blur', function () {
            const txt = this.value.trim();
            if (txt.length > 0 && txt.length < 10) {
                Swal.fire({ icon: 'warning', title: 'Texto muy corto', text: 'Las novedades deben tener al menos 10 caracteres', confirmButtonColor: '#f6c23e' });
                this.classList.add('is-invalid');
                this.focus();
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }

    ['TurnoBitacora', 'TieneVisitante', 'IdFuncionario'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('change', function () {
            this.classList.toggle('is-invalid', !this.value);
        });
    });

    const inputPDF = document.getElementById('ReportePDF');
    if (inputPDF) {
        inputPDF.addEventListener('change', function () {
            const preview  = document.getElementById('pdfPreview');
            const nombreEl = document.getElementById('pdfNombre');
            if (preview) preview.style.display = 'none';
            this.classList.remove('is-invalid');

            const archivo = this.files[0];
            if (!archivo) return;

            if (archivo.type !== 'application/pdf') {
                Swal.fire({ icon: 'error', title: 'Archivo inválido', text: 'Solo se permiten archivos en formato PDF', confirmButtonColor: '#e74a3b' });
                this.value = ''; this.classList.add('is-invalid'); return;
            }
            if (archivo.size > 5 * 1024 * 1024) {
                Swal.fire({ icon: 'error', title: 'Archivo muy grande', text: 'El PDF no debe superar los 5 MB', confirmButtonColor: '#e74a3b' });
                this.value = ''; this.classList.add('is-invalid'); return;
            }

            if (nombreEl) nombreEl.textContent = archivo.name;
            if (preview)  preview.style.display = 'block';
        });
    }

    const btnQuitarPDF = document.getElementById('btnQuitarPDF');
    if (btnQuitarPDF && inputPDF) {
        btnQuitarPDF.addEventListener('click', function () {
            inputPDF.value = '';
            const preview = document.getElementById('pdfPreview');
            if (preview) preview.style.display = 'none';
            inputPDF.classList.remove('is-invalid');
        });
    }

    // ══════════════════════════════════════════════
    // 6. CAMPOS CONDICIONALES (visitante / dispositivo)
    // ══════════════════════════════════════════════
    const selVisitante = document.getElementById('TieneVisitante');
    if (selVisitante) {
        selVisitante.addEventListener('change', function () {
            const tieneVisitante  = this.value === 'si';
            const contVisitante   = document.getElementById('VisitanteContainer');
            const contDispositivo = document.getElementById('DispositivoContainer');
            const selVisit        = document.getElementById('IdVisitante');
            const selDispositivo  = document.getElementById('TraeDispositivo');
            const inpDispositivo  = document.getElementById('IdDispositivo');

            if (contVisitante) contVisitante.style.display = tieneVisitante ? 'flex' : 'none';

            if (!tieneVisitante) {
                if (selVisit)       { selVisit.value = '';       selVisit.removeAttribute('required');       selVisit.classList.remove('is-invalid'); }
                if (selDispositivo) { selDispositivo.value = ''; selDispositivo.removeAttribute('required'); }
                if (contDispositivo)  contDispositivo.style.display = 'none';
                if (inpDispositivo) { inpDispositivo.value = '';  inpDispositivo.removeAttribute('required'); inpDispositivo.classList.remove('is-invalid'); }
            } else {
                if (selVisit)       selVisit.setAttribute('required', true);
                if (selDispositivo) selDispositivo.setAttribute('required', true);
            }
        });
    }

    const selTraeDispositivo = document.getElementById('TraeDispositivo');
    if (selTraeDispositivo) {
        selTraeDispositivo.addEventListener('change', function () {
            const trae            = this.value === 'si';
            const contDispositivo = document.getElementById('DispositivoContainer');
            const inpDispositivo  = document.getElementById('IdDispositivo');

            if (contDispositivo) contDispositivo.style.display = trae ? 'flex' : 'none';

            if (!trae) {
                if (inpDispositivo) { inpDispositivo.value = ''; inpDispositivo.removeAttribute('required'); inpDispositivo.classList.remove('is-invalid'); }
            } else {
                if (inpDispositivo) inpDispositivo.setAttribute('required', true);
                const idVisitante = document.getElementById('IdVisitante')?.value || '';
                cargarDispositivos(idVisitante);
            }
        });
    }

    // ══════════════════════════════════════════════
    // 7. ENVÍO DEL FORMULARIO DE REGISTRO
    // ══════════════════════════════════════════════
    const form = document.getElementById('formRegistrarBitacora');
    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const turno       = document.getElementById('TurnoBitacora').value;
            const fecha       = document.getElementById('FechaBitacora').value;
            const novedades   = document.getElementById('NovedadesBitacora').value.trim();
            const funcionario = document.getElementById('IdFuncionario').value;
            const tieneVisit  = document.getElementById('TieneVisitante').value;
            const idVisitante = document.getElementById('IdVisitante')?.value;
            const traeDisp    = document.getElementById('TraeDispositivo')?.value;
            const idDisp      = document.getElementById('IdDispositivo')?.value;
            const archivoPDF  = document.getElementById('ReportePDF')?.files[0];

            if (!turno) {
                Swal.fire({ icon: 'error', title: 'Campo requerido', text: 'Debe seleccionar un turno', confirmButtonColor: '#e74a3b' });
                document.getElementById('TurnoBitacora').classList.add('is-invalid'); return;
            }

            const fechaSeleccionada = new Date(fecha);
            const inicioHoy = new Date(); inicioHoy.setHours(0, 0, 0, 0);
            if (fechaSeleccionada < inicioHoy) {
                Swal.fire({ icon: 'error', title: 'Fecha inválida', text: 'La fecha no puede ser anterior al día de hoy', confirmButtonColor: '#e74a3b' });
                document.getElementById('FechaBitacora').classList.add('is-invalid'); return;
            }

            if (novedades.length < 10) {
                Swal.fire({ icon: 'error', title: 'Novedades muy cortas', text: 'Las novedades deben tener al menos 10 caracteres', confirmButtonColor: '#e74a3b' });
                document.getElementById('NovedadesBitacora').classList.add('is-invalid'); return;
            }

            if (!funcionario) {
                Swal.fire({ icon: 'error', title: 'Campo requerido', text: 'Debe seleccionar el personal de seguridad', confirmButtonColor: '#e74a3b' });
                document.getElementById('IdFuncionario').classList.add('is-invalid'); return;
            }

            if (!tieneVisit) {
                Swal.fire({ icon: 'error', title: 'Campo requerido', text: 'Debe indicar si hay visitante', confirmButtonColor: '#e74a3b' });
                document.getElementById('TieneVisitante').classList.add('is-invalid'); return;
            }

            if (tieneVisit === 'si') {
                if (!idVisitante) {
                    Swal.fire({ icon: 'error', title: 'Campo requerido', text: 'Debe seleccionar un visitante', confirmButtonColor: '#e74a3b' });
                    document.getElementById('IdVisitante').classList.add('is-invalid'); return;
                }
                if (!traeDisp) {
                    Swal.fire({ icon: 'error', title: 'Campo requerido', text: 'Debe indicar si el visitante trae dispositivo', confirmButtonColor: '#e74a3b' });
                    document.getElementById('TraeDispositivo').classList.add('is-invalid'); return;
                }
                if (traeDisp === 'si' && !idDisp) {
                    Swal.fire({ icon: 'error', title: 'Campo requerido', text: 'Debe seleccionar un dispositivo', confirmButtonColor: '#e74a3b' });
                    document.getElementById('IdDispositivo').classList.add('is-invalid'); return;
                }
            }

            if (archivoPDF) {
                if (archivoPDF.type !== 'application/pdf') {
                    Swal.fire({ icon: 'error', title: 'Archivo inválido', text: 'El archivo adjunto debe ser un PDF', confirmButtonColor: '#e74a3b' }); return;
                }
                if (archivoPDF.size > 5 * 1024 * 1024) {
                    Swal.fire({ icon: 'error', title: 'Archivo muy grande', text: 'El PDF no debe superar los 5 MB', confirmButtonColor: '#e74a3b' }); return;
                }
            }

            const btn      = form.querySelector('button[type=submit]');
            const original = btn.innerHTML;

            Swal.fire({
                title: 'Procesando...',
                html:  '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br>Registrando bitácora',
                allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false
            });

            const formData = new FormData(form);
            formData.append('accion', 'registrar');

            try {
                const response = await fetch('../../Controller/ControladorBitacora.php', { method: 'POST', body: formData });
                const data = await response.json();
                Swal.close();

                if (data.success) {
                    Swal.fire({
                        icon: 'success', title: '¡Bitácora registrada!', text: 'La bitácora fue guardada correctamente',
                        timer: 3000, timerProgressBar: true, showConfirmButton: true,
                        confirmButtonColor: '#1cc88a', confirmButtonText: 'Entendido'
                    }).then(() => {
                        form.reset();
                        ['VisitanteContainer', 'DispositivoContainer', 'pdfPreview'].forEach(id => {
                            const el = document.getElementById(id);
                            if (el) el.style.display = 'none';
                        });
                        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                        const n = new Date();
                        if (inputFecha) inputFecha.value = `${n.getFullYear()}-${pad(n.getMonth()+1)}-${pad(n.getDate())}T${pad(n.getHours())}:${pad(n.getMinutes())}`;
                        cargarPersonalSeguridad();
                        cargarVisitantes();
                    });
                } else {
                    Swal.fire({
                        icon: 'warning', title: 'No se pudo registrar',
                        html: (data.message || 'Error al registrar').replace(/\n/g, '<br>'),
                        confirmButtonColor: '#f6c23e', confirmButtonText: 'Entendido',
                        footer: '<small class="text-muted">Revise la información e intente nuevamente</small>'
                    });
                }
            } catch (error) {
                Swal.close();
                Swal.fire({
                    icon: 'error', title: 'Error de conexión',
                    html: 'No se pudo conectar al servidor.<br>Por favor, intente nuevamente.',
                    confirmButtonColor: '#e74a3b',
                    footer: '<small>Si el problema persiste, contacte al administrador</small>'
                });
            } finally {
                btn.innerHTML = original; btn.disabled = false;
            }
        });
    }

    // ══════════════════════════════════════════════
    // 8. LISTA — botones de filtro
    // ══════════════════════════════════════════════
    const btnFiltrar = document.getElementById('btnFiltrar');
    const btnLimpiar = document.getElementById('btnLimpiar');

    if (btnFiltrar) btnFiltrar.addEventListener('click', cargarBitacoras);
    if (btnLimpiar) btnLimpiar.addEventListener('click', function () {
        document.getElementById('filtroTurno').value       = '';
        document.getElementById('filtroFecha').value       = '';
        document.getElementById('filtroFuncionario').value = '';
        cargarBitacoras();
    });

    if (document.getElementById('filtroTurno')) cargarBitacoras();

}); // fin DOMContentLoaded

// ══════════════════════════════════════════════
// 9. FUNCIONES GLOBALES DE LA LISTA
// ══════════════════════════════════════════════
let tablaBitacorasDT = null;

function colorTurno(turno) {
    const c = { 'Jornada mañana': 'warning', 'Jornada tarde': 'info', 'Jornada noche': 'dark' };
    return c[turno] ?? 'secondary';
}

function formatFechaBitacora(fecha) {
    if (!fecha) return '—';
    const d = new Date(fecha);
    return `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()} ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`;
}

function cargarBitacoras() {
    const filtroTurno       = document.getElementById('filtroTurno');
    const filtroFecha       = document.getElementById('filtroFecha');
    const filtroFuncionario = document.getElementById('filtroFuncionario');
    if (!filtroTurno) return;

    fetch('../../Controller/ControladorBitacora.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:    `accion=mostrar&turno=${encodeURIComponent(filtroTurno.value)}&fecha=${encodeURIComponent(filtroFecha.value)}&funcionario=${encodeURIComponent(filtroFuncionario.value)}`
    })
    .then(r => r.json())
    .then(res => {
        const tbody = document.getElementById('cuerpoTablaBitacoras');
        if (!tbody) return;

        if (tablaBitacorasDT) { tablaBitacorasDT.destroy(); tablaBitacorasDT = null; }

        if (!Array.isArray(res) || res.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" class="text-center py-5">
                <i class="fas fa-exclamation-circle fa-2x text-muted mb-2 d-block"></i>
                <span class="text-muted">No hay bitácoras registradas con los filtros seleccionados</span>
            </td></tr>`;
            document.getElementById('contadorRegistros').textContent = '0 registros';
            return;
        }

        tbody.innerHTML = res.map(row => `
            <tr>
                <td class="fw-bold text-muted">${row.IdBitacora}</td>
                <td><span class="badge bg-${colorTurno(row.TurnoBitacora)}">${row.TurnoBitacora}</span></td>
                <td class="text-start" style="max-width:240px;">
                    <span title="${row.NovedadesBitacora ?? ''}">${(row.NovedadesBitacora ?? '').substring(0, 70)}${(row.NovedadesBitacora ?? '').length > 70 ? '...' : ''}</span>
                </td>
                <td class="text-nowrap">${formatFechaBitacora(row.FechaBitacora)}</td>
                <td><i class="fas fa-user-shield text-primary me-1"></i>${row.NombreFuncionario ?? '—'}</td>
                <td>
                    ${row.NombreVisitante
                        ? `<small><i class="fas fa-user text-success me-1"></i>${row.NombreVisitante}</small>`
                        : `<span class="badge bg-info text-white">No aplica</span>`}
                </td>
                <td>
                    ${row.NombreDispositivo
                        ? `<small><i class="fas fa-laptop text-secondary me-1"></i>${row.NombreDispositivo}</small>`
                        : `<span class="badge bg-info text-white">No aplica</span>`}
                </td>
                <td>
                    ${row.ReporteBitacora
                        ? `<a href="../../../Public/${row.ReporteBitacora}" target="_blank" class="btn btn-sm btn-outline-danger" title="Ver PDF"><i class="fas fa-file-pdf"></i> Ver</a>`
                        : `<span class="badge bg-info text-white">Sin PDF</span>`}
                </td>
                <td><span class="badge bg-${row.Estado === 'Activo' ? 'success' : 'secondary'}">${row.Estado ?? ''}</span></td>
            </tr>
        `).join('');

        const total = res.length;
        document.getElementById('contadorRegistros').textContent = `${total} registro${total !== 1 ? 's' : ''}`;

        tablaBitacorasDT = $('#tablaBitacorasDT').DataTable({
            language: { url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json" },
            pageLength: 10,
            responsive: true,
            order: [[0, "desc"]],
            columnDefs: [{ orderable: false, targets: [7] }]
        });
    })
    .catch(err => {
        console.error('Error:', err);
        const tbody = document.getElementById('cuerpoTablaBitacoras');
        if (tbody) tbody.innerHTML = `<tr><td colspan="9" class="text-center py-4 text-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>Error al cargar los datos
        </td></tr>`;
    });
}