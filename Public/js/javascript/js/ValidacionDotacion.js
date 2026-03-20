document.addEventListener('DOMContentLoaded', function () {

    const pad = n => String(n).padStart(2, '0');

    // ══════════════════════════════════════════════
    // 1. FECHAS INICIALES
    // ══════════════════════════════════════════════
    const ahora = new Date();
    const fechaMinimaDelDia  = `${ahora.getFullYear()}-${pad(ahora.getMonth()+1)}-${pad(ahora.getDate())}T00:00`;
    const fechaActualConHora = `${ahora.getFullYear()}-${pad(ahora.getMonth()+1)}-${pad(ahora.getDate())}T${pad(ahora.getHours())}:${pad(ahora.getMinutes())}`;

    const inputEntrega    = document.getElementById('FechaEntrega');
    const inputDevolucion = document.getElementById('FechaDevolucion');

    if (inputEntrega) {
        inputEntrega.setAttribute('min', fechaMinimaDelDia);
        inputEntrega.value = fechaActualConHora;
    }
    if (inputDevolucion) {
        inputDevolucion.setAttribute('min', fechaMinimaDelDia);
    }

    // ══════════════════════════════════════════════
    // 2. CARGAR PERSONAL DE SEGURIDAD
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

    cargarPersonalSeguridad();

    // ══════════════════════════════════════════════
    // 3. VALIDACIONES EN TIEMPO REAL
    // ══════════════════════════════════════════════

    if (inputEntrega) {
        inputEntrega.addEventListener('change', function () {
            const sel = new Date(this.value); const hoy = new Date(); hoy.setHours(0,0,0,0);
            if (sel < hoy) {
                Swal.fire({ icon:'warning', title:'Fecha inválida', text:'La fecha de entrega no puede ser anterior al día de hoy', confirmButtonColor:'#f6c23e' });
                this.value = fechaActualConHora; this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
                if (inputDevolucion) inputDevolucion.setAttribute('min', this.value);
            }
        });
    }

    if (inputDevolucion) {
        inputDevolucion.addEventListener('change', function () {
            const dev = new Date(this.value); const ent = new Date(inputEntrega?.value); const hoy = new Date(); hoy.setHours(0,0,0,0);
            if (dev < hoy) {
                Swal.fire({ icon:'warning', title:'Fecha inválida', text:'La fecha de devolución no puede ser anterior al día de hoy', confirmButtonColor:'#f6c23e' });
                this.value = ''; this.classList.add('is-invalid');
            } else if (inputEntrega?.value && dev < ent) {
                Swal.fire({ icon:'warning', title:'Fecha inválida', text:'La fecha de devolución no puede ser anterior a la fecha de entrega', confirmButtonColor:'#f6c23e' });
                this.value = ''; this.classList.add('is-invalid');
            } else { this.classList.remove('is-invalid'); }
        });
    }

    const txtNovedad = document.getElementById('NovedadDotacion');
    if (txtNovedad) {
        txtNovedad.addEventListener('blur', function () {
            const txt = this.value.trim();
            if (txt.length > 0 && txt.length < 10) {
                Swal.fire({ icon:'warning', title:'Texto muy corto', text:'La novedad debe tener al menos 10 caracteres', confirmButtonColor:'#f6c23e' });
                this.classList.add('is-invalid'); this.focus();
            } else { this.classList.remove('is-invalid'); }
        });
    }

    ['EstadoDotacion', 'TipoDotacion', 'IdFuncionario'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('change', function () { this.classList.toggle('is-invalid', !this.value); });
    });

    // ══════════════════════════════════════════════
    // 4. ENVÍO DEL FORMULARIO
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
            const hoy         = new Date(); hoy.setHours(0,0,0,0);

            if (!estado)           { Swal.fire({ icon:'error', title:'Campo requerido', text:'Debe seleccionar el estado',                   confirmButtonColor:'#e74a3b' }); document.getElementById('EstadoDotacion').classList.add('is-invalid'); return; }
            if (!tipo)             { Swal.fire({ icon:'error', title:'Campo requerido', text:'Debe seleccionar el tipo',                    confirmButtonColor:'#e74a3b' }); document.getElementById('TipoDotacion').classList.add('is-invalid'); return; }
            if (novedad.length<10) { Swal.fire({ icon:'error', title:'Novedad muy corta', text:'La novedad debe tener al menos 10 caracteres', confirmButtonColor:'#e74a3b' }); document.getElementById('NovedadDotacion').classList.add('is-invalid'); return; }
            if (!fechaEnt || new Date(fechaEnt) < hoy) { Swal.fire({ icon:'error', title:'Fecha inválida', text:'La fecha de entrega es obligatoria y no puede ser anterior a hoy', confirmButtonColor:'#e74a3b' }); document.getElementById('FechaEntrega').classList.add('is-invalid'); return; }
            if (fechaDev) {
                const dev = new Date(fechaDev); const ent = new Date(fechaEnt);
                if (dev < hoy || dev < ent) { Swal.fire({ icon:'error', title:'Fecha inválida', text:'La fecha de devolución debe ser posterior a la de entrega y a hoy', confirmButtonColor:'#e74a3b' }); document.getElementById('FechaDevolucion').classList.add('is-invalid'); return; }
            }
            if (!funcionario) { Swal.fire({ icon:'error', title:'Campo requerido', text:'Debe seleccionar el personal de seguridad', confirmButtonColor:'#e74a3b' }); document.getElementById('IdFuncionario').classList.add('is-invalid'); return; }

            const btn = form.querySelector('button[type=submit]');
            const original = btn.innerHTML;

            Swal.fire({ title:'Procesando...', html:'<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br>Registrando dotación', allowOutsideClick:false, allowEscapeKey:false, showConfirmButton:false });

            const formData = new FormData(form);
            formData.append('accion', 'registrar');

            try {
                const response = await fetch('../../Controller/ControladorDotacion.php', { method:'POST', body:formData });
                const data = await response.json();
                Swal.close();

                if (data.success) {
                    Swal.fire({ icon:'success', title:'¡Dotación registrada!', text:'La dotación fue guardada correctamente', timer:3000, timerProgressBar:true, showConfirmButton:true, confirmButtonColor:'#1cc88a', confirmButtonText:'Entendido' })
                    .then(() => {
                        form.reset();
                        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                        const n = new Date();
                        if (inputEntrega) inputEntrega.value = `${n.getFullYear()}-${pad(n.getMonth()+1)}-${pad(n.getDate())}T${pad(n.getHours())}:${pad(n.getMinutes())}`;
                        cargarPersonalSeguridad();
                    });
                } else {
                    Swal.fire({ icon:'warning', title:'No se pudo registrar', html:(data.message||'Error').replace(/\n/g,'<br>'), confirmButtonColor:'#f6c23e', confirmButtonText:'Entendido', footer:'<small class="text-muted">Revise la información e intente nuevamente</small>' });
                }
            } catch (error) {
                Swal.close();
                Swal.fire({ icon:'error', title:'Error de conexión', html:'No se pudo conectar al servidor.<br>Intente nuevamente.', confirmButtonColor:'#e74a3b', footer:'<small>Si el problema persiste, contacte al administrador</small>' });
            } finally {
                btn.innerHTML = original; btn.disabled = false;
            }
        });
    }

    // ══════════════════════════════════════════════
    // 5. LISTA — botones de filtro
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
// 6. FUNCIONES DE LA LISTA (fuera del DOMContentLoaded
//    para que sean globales y el DataTable pueda usarlas)
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

function cargarDotaciones() {
    const filtroEstado      = document.getElementById('filtroEstado');
    const filtroTipo        = document.getElementById('filtroTipo');
    const filtroFuncionario = document.getElementById('filtroFuncionario');
    if (!filtroEstado) return;

    fetch('../../Controller/ControladorDotacion.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:    `accion=mostrar&estado=${encodeURIComponent(filtroEstado.value)}&tipo=${encodeURIComponent(filtroTipo.value)}&funcionario=${encodeURIComponent(filtroFuncionario.value)}`
    })
    .then(r => r.json())
    .then(res => {
        const tbody = document.getElementById('cuerpoTabla');
        if (!tbody) return;

        if (tablaDataTable) { tablaDataTable.destroy(); tablaDataTable = null; }

        if (!Array.isArray(res) || res.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5">
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
                <td class="text-start" style="max-width:220px;"><span title="${row.NovedadDotacion??''}">${(row.NovedadDotacion??'').substring(0,60)}${(row.NovedadDotacion??'').length>60?'...':''}</span></td>
                <td class="text-nowrap">${formatFecha(row.FechaEntrega)}</td>
                <td class="text-nowrap">${formatFecha(row.FechaDevolucion)}</td>
                <td><i class="fas fa-user-shield text-primary me-1"></i>${row.NombreFuncionario??'—'}</td>
                <td><span class="badge bg-${row.Estado==='Activo'?'success':'secondary'}">${row.Estado??''}</span></td>
            </tr>
        `).join('');

        const total = res.length;
        document.getElementById('contadorRegistros').textContent = `${total} registro${total!==1?'s':''}`;

        tablaDataTable = $('#tablaDotacionesDT').DataTable({
            language: { url:"https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json" },
            pageLength: 10, responsive: true, order: [[0,"desc"]]
        });
    })
    .catch(err => {
        console.error('Error:', err);
        const tbody = document.getElementById('cuerpoTabla');
        if (tbody) tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error al cargar los datos</td></tr>`;
    });
}