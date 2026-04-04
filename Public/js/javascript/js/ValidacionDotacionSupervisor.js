// ============================================================
// ValidacionDotacionSupervisor.js
// ============================================================

function esperarDependencias(cb) {
    if (typeof $ !== 'undefined' && typeof Swal !== 'undefined') cb();
    else setTimeout(function () { esperarDependencias(cb); }, 50);
}

esperarDependencias(function () {

    const pad = n => String(n).padStart(2, '0');

    let tablaDotacionSupervisorDT = null;
    let dotacionACambiar          = null;
    let estadoActualDotacion      = null;

    // ══════════════════════════════════════════════
    // HELPER: fecha actual en tiempo real (string para inputs)
    // ══════════════════════════════════════════════
    function getFechaActualConHora() {
        const ahora = new Date();
        return `${ahora.getFullYear()}-${pad(ahora.getMonth()+1)}-${pad(ahora.getDate())}T${pad(ahora.getHours())}:${pad(ahora.getMinutes())}`;
    }

    // ══════════════════════════════════════════════
    // HELPER: "ahora" truncado a minutos para comparaciones
    // FIX: evita que los segundos transcurridos invaliden
    //      una fecha que el input muestra como igual a la actual
    // ══════════════════════════════════════════════
    function getAhoraTruncado() {
        const ahora = new Date();
        ahora.setSeconds(0, 0);
        return ahora;
    }

    // ══════════════════════════════════════════════
    // FECHAS INICIALES (formulario ingreso)
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

    // Actualizar min cada minuto para que no quede desactualizado
    setInterval(function () {
        const nueva = getFechaActualConHora();
        if (inputEntrega)    inputEntrega.setAttribute('min', nueva);
        if (inputDevolucion) inputDevolucion.setAttribute('min', nueva);
    }, 60000);

    // ══════════════════════════════════════════════
    // CARGAR SUPERVISORES (dropdown del formulario)
    // ══════════════════════════════════════════════
    function cargarSupervisores() {
        const select = document.getElementById('IdFuncionario');
        const msgDiv = document.getElementById('msgFuncionario');
        if (!select) return;

        fetch('/SEGTRACK/App/Controller/ControladorDotacion.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    'accion=funcionarios'
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
    // VALIDACIONES EN TIEMPO REAL (formulario ingreso)
    // FIX: se usa getAhoraTruncado() en todas las comparaciones
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

    ['EstadoDotacion', 'TipoDotacion', 'IdFuncionario'].forEach(function (id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('change', function () { this.classList.toggle('is-invalid', !this.value); });
    });

    // ══════════════════════════════════════════════
    // ENVÍO DEL FORMULARIO INGRESO
    // FIX: ahoraSubmit usa getAhoraTruncado() para comparar por minuto
    // ══════════════════════════════════════════════
    const form = document.getElementById('formIngresarDotacionSupervisor');
    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const estado      = document.getElementById('EstadoDotacion').value;
            const tipo        = document.getElementById('TipoDotacion').value;
            const novedad     = document.getElementById('NovedadDotacion').value.trim();
            const fechaEnt    = document.getElementById('FechaEntrega').value;
            const fechaDev    = document.getElementById('FechaDevolucion')?.value;
            const funcionario = document.getElementById('IdFuncionario').value;
            const ahoraSubmit = getAhoraTruncado(); // FIX: truncado a minutos

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
                const dev = new Date(fechaDev); const ent = new Date(fechaEnt);
                if (dev < ahoraSubmit || dev < ent) {
                    Swal.fire({ icon:'error', title:'Fecha inválida', text:'La fecha de devolución debe ser posterior a la de entrega y a la hora actual', confirmButtonColor:'#e74a3b' });
                    document.getElementById('FechaDevolucion').classList.add('is-invalid'); return;
                }
            }
            if (!funcionario) {
                Swal.fire({ icon:'error', title:'Campo requerido', text:'Debe seleccionar el supervisor', confirmButtonColor:'#e74a3b' });
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
                const response = await fetch('/SEGTRACK/App/Controller/ControladorDotacion.php', { method:'POST', body:formData });
                const data     = await response.json();
                Swal.close();

                if (data.success) {
                    Swal.fire({
                        icon: 'success', title: '¡Dotación registrada!',
                        text: 'La dotación fue guardada correctamente',
                        timer: 3000, timerProgressBar: true,
                        showConfirmButton: true, confirmButtonColor: '#1cc88a',
                        confirmButtonText: 'Entendido'
                    }).then(function () {
                        form.reset();
                        document.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
                        const nuevaFecha = getFechaActualConHora();
                        if (inputEntrega) {
                            inputEntrega.setAttribute('min', nuevaFecha);
                            inputEntrega.value = nuevaFecha;
                        }
                        if (inputDevolucion) {
                            inputDevolucion.setAttribute('min', nuevaFecha);
                            inputDevolucion.value = '';
                        }
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
    // HELPERS (lista)
    // ══════════════════════════════════════════════
    function colorEstadoDot(estado) {
        const c = { 'Buen estado': 'success', 'Regular': 'warning', 'Dañado': 'danger' };
        return c[estado] ?? 'secondary';
    }

    function colorTipoDot(tipo) {
        const c = { 'Uniforme': 'info text-dark', 'Equipo': 'primary', 'Herramienta': 'warning text-dark', 'Otro': 'secondary' };
        return c[tipo] ?? 'secondary';
    }

    function formatFechaDot(fecha) {
        if (!fecha) return '<span class="badge bg-info text-white">Sin fecha</span>';
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
    // CARGAR DOTACIONES (lista)
    // ══════════════════════════════════════════════
    function cargarDotacionesSupervisor() {
        const filtroEstado         = $('#filtroEstado').val()         || '';
        const filtroTipo           = $('#filtroTipo').val()           || '';
        const filtroFuncionario    = $('#filtroFuncionario').val()    || '';
        const filtroEstadoRegistro = $('#filtroEstadoRegistro').val() || '';

        $.ajax({
            url:  '/SEGTRACK/App/Controller/ControladorDotacion.php',
            type: 'POST',
            data: {
                accion:      'mostrar',
                estado:      filtroEstado,
                tipo:        filtroTipo,
                funcionario: filtroFuncionario,
                estadoReg:   filtroEstadoRegistro
            },
            dataType: 'json',
            success: function (res) {
                const tbody = document.getElementById('cuerpoTablaDotacionSupervisor');
                if (!tbody) return;

                if (tablaDotacionSupervisorDT) {
                    tablaDotacionSupervisorDT.destroy();
                    tablaDotacionSupervisorDT = null;
                }

                if (!Array.isArray(res) || res.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="9" class="text-center py-5">
                        <i class="fas fa-exclamation-circle fa-2x text-muted mb-2 d-block"></i>
                        <span class="text-muted">No hay dotaciones registradas con los filtros seleccionados</span>
                    </td></tr>`;
                    $('#contadorDotaciones').text('0 registros');
                    return;
                }

                tbody.innerHTML = res.map(function (row) {
                    return `<tr class="${row.Estado === 'Inactivo' ? 'fila-inactiva' : ''}">
                        <td class="fw-bold text-muted">${row.IdDotacion}</td>
                        <td><span class="badge bg-${colorEstadoDot(row.EstadoDotacion)}">${row.EstadoDotacion}</span></td>
                        <td><span class="badge bg-${colorTipoDot(row.TipoDotacion)}">${row.TipoDotacion}</span></td>
                        <td class="text-start" style="max-width:220px;">
                            <span title="${row.NovedadDotacion ?? ''}">
                                ${(row.NovedadDotacion ?? '').substring(0, 60)}${(row.NovedadDotacion ?? '').length > 60 ? '...' : ''}
                            </span>
                        </td>
                        <td class="text-nowrap">${formatFechaDot(row.FechaEntrega)}</td>
                        <td class="text-nowrap">${formatFechaDot(row.FechaDevolucion)}</td>
                        <td>
                            <i class="fas fa-user-shield text-primary mr-1"></i>
                            ${row.NombreFuncionario ?? '—'}
                        </td>
                        <td>
                            <span class="badge bg-${row.Estado === 'Activo' ? 'success' : 'secondary'}">
                                ${row.Estado ?? ''}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick='abrirModalEditarDotacion(${JSON.stringify(row)})'
                                    title="Editar dotación"
                                    data-toggle="modal" data-target="#modalEditarDotacion">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button"
                                    class="btn btn-sm ${row.Estado === 'Activo' ? 'btn-outline-warning' : 'btn-outline-success'}"
                                    onclick="confirmarCambioEstadoDotacion(${row.IdDotacion}, '${row.Estado}')"
                                    title="${row.Estado === 'Activo' ? 'Desactivar' : 'Activar'} dotación">
                                    <i class="fas ${row.Estado === 'Activo' ? 'fa-lock' : 'fa-lock-open'}"></i>
                                </button>
                            </div>
                        </td>
                    </tr>`;
                }).join('');

                const total = res.length;
                $('#contadorDotaciones').text(total + ' registro' + (total !== 1 ? 's' : ''));

                tablaDotacionSupervisorDT = $('#TablaDotacionSupervisor').DataTable({
                    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json' },
                    pageLength: 10,
                    responsive: true,
                    order: [[0, 'desc']],
                    columnDefs: [{ orderable: false, targets: [8] }]
                });
            },
            error: function () {
                const tbody = document.getElementById('cuerpoTablaDotacionSupervisor');
                if (tbody) tbody.innerHTML = `<tr><td colspan="9" class="text-center py-4 text-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Error al cargar los datos
                </td></tr>`;
                $('#contadorDotaciones').text('Error');
            }
        });
    }

    // ══════════════════════════════════════════════
    // BOTONES FILTRAR / LIMPIAR
    // ══════════════════════════════════════════════
    $('#btnFiltrar').on('click', function () { cargarDotacionesSupervisor(); });

    $('#btnLimpiar').on('click', function () {
        $('#filtroEstado').val('');
        $('#filtroTipo').val('');
        $('#filtroFuncionario').val('');
        $('#filtroEstadoRegistro').val('');
        cargarDotacionesSupervisor();
    });

    // ══════════════════════════════════════════════
    // MODAL EDITAR
    // ══════════════════════════════════════════════
    window.abrirModalEditarDotacion = function (row) {
        $('#editIdDotacion').val(row.IdDotacion);
        $('#editIdDotacionLabel').text(row.IdDotacion);
        $('#editEstadoDotacion').val(row.EstadoDotacion);
        $('#editTipoDotacion').val(row.TipoDotacion);
        $('#editNovedadDotacion').val(row.NovedadDotacion ?? '');
        $('#editFechaEntrega').val(fechaParaInput(row.FechaEntrega));
        $('#editFechaDevolucion').val(fechaParaInput(row.FechaDevolucion));
        $('#editNombreFuncionario').val(row.NombreFuncionario ?? '');
        $('#editIdFuncionario').val(row.IdFuncionario);
    };

    $('#btnGuardarEdicionDotacion').on('click', function () {
        const id       = $('#editIdDotacion').val();
        const estado   = $('#editEstadoDotacion').val();
        const tipo     = $('#editTipoDotacion').val();
        const novedad  = $('#editNovedadDotacion').val().trim();
        const fechaEnt = $('#editFechaEntrega').val();
        const fechaDev = $('#editFechaDevolucion').val();
        const idFun    = $('#editIdFuncionario').val();

        if (!estado || !tipo || !fechaEnt || !idFun) {
            Swal.fire({ icon: 'warning', title: 'Campos incompletos', text: 'Complete todos los campos obligatorios.', confirmButtonColor: '#f6c23e' });
            return;
        }
        if (novedad.length > 0 && novedad.length < 10) {
            Swal.fire({ icon: 'warning', title: 'Novedad muy corta', text: 'La novedad debe tener al menos 10 caracteres.', confirmButtonColor: '#f6c23e' });
            return;
        }

        $('#modalEditarDotacion').modal('hide');
        Swal.fire({ title: 'Guardando cambios...', html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>', allowOutsideClick: false, showConfirmButton: false });

        $.ajax({
            url:  '/SEGTRACK/App/Controller/ControladorDotacion.php',
            type: 'POST',
            data: {
                accion:          'actualizar',
                IdDotacion:      id,
                EstadoDotacion:  estado,
                TipoDotacion:    tipo,
                NovedadDotacion: novedad,
                FechaEntrega:    fechaEnt,
                FechaDevolucion: fechaDev,
                IdFuncionario:   idFun
            },
            dataType: 'json',
            success: function (r) {
                if (r.success) {
                    Swal.fire({
                        icon: 'success', title: '¡Actualizado!',
                        text: 'La dotación fue actualizada correctamente.',
                        timer: 2500, timerProgressBar: true, showConfirmButton: false
                    }).then(function () { cargarDotacionesSupervisor(); });
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
    window.confirmarCambioEstadoDotacion = function (id, estado) {
        dotacionACambiar     = id;
        estadoActualDotacion = estado;

        const nuevo  = estado === 'Activo' ? 'Inactivo' : 'Activo';
        const accion = nuevo  === 'Activo' ? 'activar'  : 'desactivar';
        const color  = nuevo  === 'Activo' ? 'bg-success' : 'bg-warning';
        const icono  = nuevo  === 'Activo' ? 'fa-lock-open' : 'fa-lock';

        $('#headerCambioEstadoDotacion').removeClass('bg-success bg-warning').addClass(color + ' text-white');
        $('#tituloCambioEstadoDotacion').html('<i class="fas ' + icono + ' mr-2"></i>' + accion.charAt(0).toUpperCase() + accion.slice(1) + ' Dotación');
        $('#mensajeCambioEstadoDotacion').html('¿Está seguro que desea <strong>' + accion + '</strong> esta dotación?');

        setTimeout(function () {
            const toggle = document.getElementById('toggleEstadoVisualDotacion');
            if (toggle) {
                nuevo === 'Activo' ? toggle.classList.add('activo') : toggle.classList.remove('activo');
            }
        }, 100);

        $('#modalCambiarEstadoDotacion').modal('show');
    };

    $('#btnConfirmarCambioEstadoDotacion').on('click', function () {
        if (!dotacionACambiar) return;

        const nuevo = estadoActualDotacion === 'Activo' ? 'Inactivo' : 'Activo';
        $('#modalCambiarEstadoDotacion').modal('hide');

        Swal.fire({ title: 'Procesando...', html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>', allowOutsideClick: false, showConfirmButton: false });

        $.ajax({
            url:  '/SEGTRACK/App/Controller/ControladorDotacion.php',
            type: 'POST',
            data: { accion: 'cambiar_estado', IdDotacion: dotacionACambiar, nuevoEstado: nuevo },
            dataType: 'json',
            success: function (r) {
                if (r.success) {
                    Swal.fire({
                        icon: 'success', title: '¡Éxito!', text: r.message,
                        timer: 2000, timerProgressBar: true, showConfirmButton: false
                    }).then(function () { cargarDotacionesSupervisor(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: r.message, confirmButtonColor: '#e74a3b' });
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#e74a3b' });
            }
        });
    });

    $('#modalCambiarEstadoDotacion').on('hidden.bs.modal', function () {
        dotacionACambiar     = null;
        estadoActualDotacion = null;
    });

    // ══════════════════════════════════════════════
    // CARGA INICIAL
    // Solo carga la tabla si el elemento existe (página lista)
    // ══════════════════════════════════════════════
    if (document.getElementById('cuerpoTablaDotacionSupervisor')) {
        cargarDotacionesSupervisor();
    }

}); // fin esperarDependencias