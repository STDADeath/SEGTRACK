document.addEventListener('DOMContentLoaded', function () {

    // ══════════════════════════════════════════════
    // 1. VALIDACIONES EN TIEMPO REAL — FORMULARIO
    // ══════════════════════════════════════════════

    const inputId     = document.getElementById('IdentificacionVisitante');
    const inputNombre = document.getElementById('NombreVisitante');
    const inputCorreo = document.getElementById('CorreoVisitante');

    if (inputNombre) {
        inputNombre.addEventListener('input', function () {
            this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s]/g, '');
        });
    }

    if (inputId) {
        inputId.addEventListener('input', function () {
            let v = this.value;
            if (/^\d+$/.test(v)) {
                this.value = v.replace(/\D/g, '');
            } else {
                this.value = v.replace(/[^a-zA-Z0-9\-]/g, '');
            }
            this.classList.remove('is-invalid');
        });
    }

    if (inputCorreo) {
        inputCorreo.addEventListener('blur', function () {
            const val = this.value.trim();
            if (val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }

    ['IdentificacionVisitante', 'NombreVisitante'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('change', function () {
            this.classList.toggle('is-invalid', !this.value.trim());
        });
    });

    // ══════════════════════════════════════════════
    // 2. ENVÍO DEL FORMULARIO
    // ══════════════════════════════════════════════
    const form = document.getElementById('formRegistrarVisitante');
    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const idVal     = (inputId?.value ?? '').trim();
            const nombre    = (inputNombre?.value ?? '').trim();
            const correo    = (inputCorreo?.value ?? '').trim();

            // Validar identificación
            const esCC = /^\d{6,10}$/.test(idVal);
            const esCE = /^[A-Za-z0-9\-]{4,20}$/.test(idVal);
            if (!esCC && !esCE) {
                Swal.fire({ icon:'error', title:'Identificación inválida', text:'CC: 6-10 dígitos · CE: 4-20 caracteres alfanuméricos', confirmButtonColor:'#e74a3b' });
                inputId?.classList.add('is-invalid'); return;
            }

            // Validar nombre
            if (!/^[a-zA-ZÀ-ÿ\s]{3,100}$/.test(nombre)) {
                Swal.fire({ icon:'error', title:'Nombre inválido', text:'El nombre solo debe contener letras (mínimo 3 caracteres)', confirmButtonColor:'#e74a3b' });
                inputNombre?.classList.add('is-invalid'); return;
            }

            // Validar correo si se ingresó
            if (correo && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
                Swal.fire({ icon:'error', title:'Correo inválido', text:'Ingrese un correo electrónico válido', confirmButtonColor:'#e74a3b' });
                inputCorreo?.classList.add('is-invalid'); return;
            }

            const btn      = form.querySelector('button[type=submit]');
            const original = btn.innerHTML;

            Swal.fire({
                title: 'Procesando...',
                html:  '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br>Registrando visitante',
                allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false
            });

            const formData = new FormData(form);
            formData.append('accion', 'registrar');

            try {
                const response = await fetch('../../Controller/ControladorVisitante.php', { method:'POST', body:formData });
                const data     = await response.json();
                Swal.close();

                if (data.success) {
                    Swal.fire({
                        icon:'success', title:'¡Visitante registrado!', text:'El visitante fue guardado correctamente',
                        timer:3000, timerProgressBar:true, showConfirmButton:true,
                        confirmButtonColor:'#1cc88a', confirmButtonText:'Entendido'
                    }).then(() => {
                        form.reset();
                        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                    });
                } else {
                    Swal.fire({ icon:'warning', title:'No se pudo registrar', html:(data.message||'Error').replace(/\n/g,'<br>'), confirmButtonColor:'#f6c23e', confirmButtonText:'Entendido', footer:'<small class="text-muted">Revise la información e intente nuevamente</small>' });
                }
            } catch (error) {
                Swal.close();
                Swal.fire({ icon:'error', title:'Error de conexión', html:'No se pudo conectar al servidor.<br>Intente nuevamente.', confirmButtonColor:'#e74a3b' });
            } finally {
                btn.innerHTML = original; btn.disabled = false;
            }
        });
    }

    // ══════════════════════════════════════════════
    // 3. BOTONES DE FILTRO — solo si estamos en lista
    // ══════════════════════════════════════════════
    const btnFiltrar = document.getElementById('btnFiltrar');
    const btnLimpiar = document.getElementById('btnLimpiar');

    if (btnFiltrar) btnFiltrar.addEventListener('click', cargarVisitantes);
    if (btnLimpiar) btnLimpiar.addEventListener('click', function () {
        document.getElementById('filtroIdentificacion').value = '';
        document.getElementById('filtroNombre').value         = '';
        document.getElementById('filtroEstado').value         = '';
        cargarVisitantes();
    });

    if (document.getElementById('filtroEstado')) cargarVisitantes();

}); // fin DOMContentLoaded

// ══════════════════════════════════════════════
// 4. CARGA DE LISTA POR AJAX
// ══════════════════════════════════════════════
let tablaVisitantes = null;

function cargarVisitantes() {
    const filtroId     = document.getElementById('filtroIdentificacion');
    const filtroNombre = document.getElementById('filtroNombre');
    const filtroEstado = document.getElementById('filtroEstado');
    if (!filtroId) return;

    fetch('../../Controller/ControladorVisitante.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:    `accion=mostrar&identificacion=${encodeURIComponent(filtroId.value)}&nombre=${encodeURIComponent(filtroNombre.value)}&estado=${encodeURIComponent(filtroEstado.value)}`
    })
    .then(r => r.json())
    .then(res => {
        if (tablaVisitantes) { tablaVisitantes.destroy(); tablaVisitantes = null; }

        // Limpiar tbody antes de inicializar
        const tbody = document.getElementById('cuerpoTabla');
        if (tbody) tbody.innerHTML = '';

        const contador = document.getElementById('contadorRegistros');

        if (!Array.isArray(res) || res.length === 0) {
            if (contador) contador.textContent = '0 registros';
            tablaVisitantes = $('#tablaVisitantesDT').DataTable({
                language: { url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json" },
                pageLength: 10, responsive: true, order: [[0, "desc"]]
            });
            return;
        }

        if (contador) contador.textContent = `${res.length} registro${res.length !== 1 ? 's' : ''}`;

        tablaVisitantes = $('#tablaVisitantesDT').DataTable({
            language: { url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json" },
            pageLength: 10,
            responsive: true,
            order: [[0, "desc"]],
            data: res,
            columns: [
                { data: 'IdVisitante',             className: 'fw-bold text-muted' },
                { data: 'IdentificacionVisitante'  },
                { data: 'NombreVisitante',          render: d => `<i class="fas fa-user text-primary me-1"></i>${d}` },
                { data: 'CorreoVisitante',          render: d => d ? `<a href="mailto:${d}">${d}</a>` : '<span class="badge bg-info text-white">No aplica</span>' },
                { data: 'Estado',                   render: d => `<span class="badge bg-${d === 'Activo' ? 'success' : 'secondary'}">${d}</span>` }
            ]
        });
    })
    .catch(err => {
        console.error('Error:', err);
        const tbody = document.getElementById('cuerpoTabla');
        if (tbody) tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>Error al cargar los datos
        </td></tr>`;
    });
}