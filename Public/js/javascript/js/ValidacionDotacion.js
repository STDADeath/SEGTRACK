document.addEventListener('DOMContentLoaded', function () {

    const pad = n => String(n).padStart(2, '0');

    // ══════════════════════════════════════════════
    // 1. HELPER: fecha actual en tiempo real
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

    // ══════════════════════════════════════════════
    // 2. FECHAS INICIALES
    // ══════════════════════════════════════════════
    const inputEntrega    = document.getElementById('FechaEntrega');
    const inputDevolucion = document.getElementById('FechaDevolucion');

    if (inputEntrega) {
        inputEntrega.setAttribute('min', getFechaActualConHora());
        inputEntrega.value = getFechaActualConHora();
    }
    if (inputDevolucion) {
        inputDevolucion.setAttribute('min', getFechaActualConHora());
    }

    setInterval(function () {
        const nueva = getFechaActualConHora();
        if (inputEntrega)    inputEntrega.setAttribute('min', nueva);
        if (inputDevolucion) inputDevolucion.setAttribute('min', nueva);
    }, 60000);

    // ══════════════════════════════════════════════
    // 3. CARGAR PERSONAL DE SEGURIDAD (dropdown)
    // Filtrado por TipoRol = 'Personal Seguridad'
    // desde tabla usuario via controlador
    // ══════════════════════════════════════════════
    function cargarPersonalSeguridad() {
        const select = document.getElementById('IdFuncionario');
        const msgDiv = document.getElementById('msgFuncionario');
        if (!select) return;

        fetch('../../Controller/ControladorDotacion.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    'accion=personal_seguridad'
        })
        .then(r => r.json())
        .then(res => {
            select.innerHTML = '<option value="" disabled selected>-- Seleccione personal de seguridad --</option>';
            if (!Array.isArray(res) || res.length === 0) {
                select.innerHTML += '<option value="" disabled>Sin personal disponible</option>';
                if (msgDiv) {
                    msgDiv.textContent = 'No hay personal de seguridad activo registrado.';
                    msgDiv.className   = 'form-text text-warning';
                }
                return;
            }
            res.forEach(item => {
                const opt       = document.createElement('option');
                opt.value       = item.IdFuncionario;
                opt.textContent = item.NombreCompleto; // solo el nombre
                select.appendChild(opt);
            });
            if (msgDiv) msgDiv.textContent = '';
        })
        .catch(() => {
            select.innerHTML = '<option value="" disabled selected>Error al cargar personal</option>';
            if (msgDiv) {
                msgDiv.textContent = 'No se pudo conectar con el servidor.';
                msgDiv.className   = 'form-text text-danger';
            }
        });
    }

    cargarPersonalSeguridad();

    // ══════════════════════════════════════════════
    // 4. VALIDACIONES EN TIEMPO REAL
    // ══════════════════════════════════════════════
    if (inputEntrega) {
        inputEntrega.addEventListener('change', function () {
            const sel   = new Date(this.value);
            const ahora = getAhoraTruncado();
            if (sel < ahora) {
                Swal.fire({ icon:'warning', title:'Fecha inválida', text:'La fecha de entrega no puede ser anterior a la hora actual', confirmButtonColor:'#f6c23e' });
                this.value = getFechaActualConHora();
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
                if (inputDevolucion) inputDevolucion.setAttribute('min', this.value);
            }
        });
    }

    if (inputDevolucion) {
        inputDevolucion.addEventListener('change', function () {
            const dev   = new Date(this.value);
            const ent   = new Date(inputEntrega?.value);
            const ahora = getAhoraTruncado();
            if (dev < ahora) {
                Swal.fire({ icon:'warning', title:'Fecha inválida', text:'La fecha de devolución no puede ser anterior a la hora actual', confirmButtonColor:'#f6c23e' });
                this.value = '';
                this.classList.add('is-invalid');
            } else if (inputEntrega?.value && dev < ent) {
                Swal.fire({ icon:'warning', title:'Fecha inválida', text:'La fecha de devolución no puede ser anterior a la fecha de entrega', confirmButtonColor:'#f6c23e' });
                this.value = '';
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }

    const txtNovedad = document.getElementById('NovedadDotacion');
    if (txtNovedad) {
        txtNovedad.addEventListener('blur', function () {
            const txt = this.value.trim();
            if (txt.length > 0 && txt.length < 10) {
                Swal.fire({ icon:'warning', title:'Texto muy corto', text:'La novedad debe tener al menos 10 caracteres', confirmButtonColor:'#f6c23e' });
                this.classList.add('is-invalid');
                this.focus();
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }

    ['EstadoDotacion', 'TipoDotacion', 'IdFuncionario'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('change', function () {
            this.classList.toggle('is-invalid', !this.value);
        });
    });

    // ══════════════════════════════════════════════
    // 5. ENVÍO DEL FORMULARIO
    // ══════════════════════════════════════════════
    const form = document.getElementById('formIngresarDotacion');
    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const estado      = document.getElementById('EstadoDotacion').value;
            const tipo        = document.getElementById('TipoDotacion').value;
            const novedad     = document.getElementById('NovedadDotacion').value.trim();
            const fechaEnt    = document.getElementById('FechaEntrega').value;
            const fechaDev    = document.getElementById('FechaDevolucion')?.value;
            const funcionario = document.getElementById('IdFuncionario').value;
            const ahoraSubmit = getAhoraTruncado();

            if (!estado) {
                Swal.fire({ icon:'error', title:'Campo requerido', text:'Debe seleccionar el estado', confirmButtonColor:'#e74a3b' });
                document.getElementById('EstadoDotacion').classList.add('is-invalid'); return;
            }
            if (!tipo) {
                Swal.fire({ icon:'error', title:'Campo requerido', text:'Debe seleccionar el tipo', confirmButtonColor:'#e74a3b' });
                document.getElementById('TipoDotacion').classList.add('is-invalid'); return;
            }
            if (novedad.length < 10) {
                Swal.fire({ icon:'error', title:'Novedad muy corta', text:'La novedad debe tener al menos 10 caracteres', confirmButtonColor:'#e74a3b' });
                document.getElementById('NovedadDotacion').classList.add('is-invalid'); return;
            }
            if (!fechaEnt || new Date(fechaEnt) < ahoraSubmit) {
                Swal.fire({ icon:'error', title:'Fecha inválida', text:'La fecha de entrega es obligatoria y no puede ser anterior a la hora actual', confirmButtonColor:'#e74a3b' });
                document.getElementById('FechaEntrega').classList.add('is-invalid'); return;
            }
            if (fechaDev) {
                const dev = new Date(fechaDev);
                const ent = new Date(fechaEnt);
                if (dev < ahoraSubmit || dev < ent) {
                    Swal.fire({ icon:'error', title:'Fecha inválida', text:'La fecha de devolución debe ser posterior a la de entrega y a la hora actual', confirmButtonColor:'#e74a3b' });
                    document.getElementById('FechaDevolucion').classList.add('is-invalid'); return;
                }
            }
            if (!funcionario) {
                Swal.fire({ icon:'error', title:'Campo requerido', text:'Debe seleccionar el personal de seguridad', confirmButtonColor:'#e74a3b' });
                document.getElementById('IdFuncionario').classList.add('is-invalid'); return;
            }

            const btn      = form.querySelector('button[type=submit]');
            const original = btn.innerHTML;
            btn.disabled   = true;

            Swal.fire({
                title: 'Procesando...',
                html:  '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br>Registrando dotación',
                allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false
            });

            const formData = new FormData(form);
            formData.append('accion', 'registrar');

            try {
                const response = await fetch('../../Controller/ControladorDotacion.php', { method:'POST', body:formData });
                const data     = await response.json();
                Swal.close();

                if (data.success) {
                    Swal.fire({
                        icon: 'success', title: '¡Dotación registrada!',
                        text: 'La dotación fue guardada correctamente',
                        timer: 3000, timerProgressBar: true,
                        showConfirmButton: true, confirmButtonColor: '#1cc88a', confirmButtonText: 'Entendido'
                    }).then(() => {
                        form.reset();
                        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                        const nuevaFecha = getFechaActualConHora();
                        if (inputEntrega)    { inputEntrega.setAttribute('min', nuevaFecha); inputEntrega.value = nuevaFecha; }
                        if (inputDevolucion) { inputDevolucion.setAttribute('min', nuevaFecha); inputDevolucion.value = ''; }
                        cargarPersonalSeguridad();
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
                btn.innerHTML = original;
                btn.disabled  = false;
            }
        });
    }

    // ══════════════════════════════════════════════
    // 6. LISTA — botones de filtro
    // ══════════════════════════════════════════════
    const btnFiltrar = document.getElementById('btnFiltrar');
    const btnLimpiar = document.getElementById('btnLimpiar');

    if (btnFiltrar) btnFiltrar.addEventListener('click', cargarDotaciones);
    if (btnLimpiar) btnLimpiar.addEventListener('click', function () {
        document.getElementById('filtroEstado').value      = '';
        document.getElementById('filtroTipo').value        = '';
        document.getElementById('filtroFuncionario').value = '';
        cargarDotaciones();
    });

    if (document.getElementById('filtroEstado')) cargarDotaciones();

}); // fin DOMContentLoaded

// ══════════════════════════════════════════════
// 7. FUNCIONES DE LA LISTA
// ══════════════════════════════════════════════
let tablaDataTable = null;

function colorEstado(estado) {
    const c = { 'Buen estado':'success', 'Regular':'warning', 'Dañado':'danger' };
    return c[estado] ?? 'secondary';
}

function colorTipo(tipo) {
    const c = { 'Uniforme':'info text-dark', 'Equipo':'primary', 'Herramienta':'warning text-dark', 'Otro':'secondary' };
    return c[tipo] ?? 'secondary';
}

function formatFecha(fecha) {
    if (!fecha) return '<span class="badge bg-info text-white">Sin fecha</span>';
    const d = new Date(fecha);
    return `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()} ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`;
}

// CargoFuncionario = alias de u.TipoRol desde el modelo
function celdaSupervisor(row) {
    if (row.CargoFuncionario === 'Supervisor' && row.NombreFuncionario) {
        return `<i class="fas fa-user-tie text-primary me-1"></i>${row.NombreFuncionario}`;
    }
    return '<span class="text-muted fst-italic">No aplica</span>';
}

function celdaPersonalSeguridad(row) {
    if (row.CargoFuncionario === 'Personal Seguridad' && row.NombreFuncionario) {
        return `<i class="fas fa-user-shield text-info me-1"></i>${row.NombreFuncionario}`;
    }
    return '<span class="text-muted fst-italic">No aplica</span>';
}

function cargarDotaciones() {
    const filtroEstado      = document.getElementById('filtroEstado');
    const filtroTipo        = document.getElementById('filtroTipo');
    const filtroFuncionario = document.getElementById('filtroFuncionario');
    if (!filtroEstado) return;

    fetch('../../Controller/ControladorDotacion.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:    `accion=mostrar`
            + `&estado=${encodeURIComponent(filtroEstado.value)}`
            + `&tipo=${encodeURIComponent(filtroTipo.value)}`
            + `&funcionario=${encodeURIComponent(filtroFuncionario.value)}`
            + `&estadoReg=Activo`
    })
    .then(r => r.json())
    .then(res => {
        const tbody = document.getElementById('cuerpoTabla');
        if (!tbody) return;

        if (tablaDataTable) { tablaDataTable.destroy(); tablaDataTable = null; }

        if (!Array.isArray(res) || res.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" class="text-center py-5">
                <i class="fas fa-exclamation-circle fa-2x text-muted mb-2 d-block"></i>
                <span class="text-muted">No hay dotaciones registradas</span>
            </td></tr>`;
            document.getElementById('contadorRegistros').textContent = '0 registros';
            return;
        }

        tbody.innerHTML = res.map(row => `
            <tr>
                <td class="fw-bold text-muted">${row.IdDotacion}</td>
                <td><span class="badge bg-${colorEstado(row.EstadoDotacion)}">${row.EstadoDotacion}</span></td>
                <td><span class="badge bg-${colorTipo(row.TipoDotacion)}">${row.TipoDotacion}</span></td>
                <td class="text-start" style="max-width:220px;">
                    <span title="${row.NovedadDotacion ?? ''}">${(row.NovedadDotacion ?? '').substring(0,60)}${(row.NovedadDotacion ?? '').length > 60 ? '...' : ''}</span>
                </td>
                <td class="text-nowrap">${formatFecha(row.FechaEntrega)}</td>
                <td class="text-nowrap">${formatFecha(row.FechaDevolucion)}</td>
                <td>${celdaSupervisor(row)}</td>
                <td>${celdaPersonalSeguridad(row)}</td>
                <td><span class="badge bg-${row.Estado === 'Activo' ? 'success' : 'secondary'}">${row.Estado ?? ''}</span></td>
            </tr>
        `).join('');

        const total = res.length;
        document.getElementById('contadorRegistros').textContent = `${total} registro${total !== 1 ? 's' : ''}`;

        tablaDataTable = $('#tablaDotacionesDT').DataTable({
            language: { url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json" },
            pageLength: 10, responsive: true, order: [[0, "desc"]]
        });
    })
    .catch(err => {
        console.error('Error:', err);
        const tbody = document.getElementById('cuerpoTabla');
        if (tbody) tbody.innerHTML = `<tr><td colspan="9" class="text-center py-4 text-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>Error al cargar los datos
        </td></tr>`;
    });
}