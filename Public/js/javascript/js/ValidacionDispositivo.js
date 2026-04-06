// ══════════════════════════════════════════════════════════════
// VALIDACIONES Y CASCADA PARA DISPOSITIVOS
// ══════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formDispositivo');
    if (!form) return;

    // ══════════════════════════════════════════════════════════════
    // FUNCIONES DE CARGA PARA LA CASCADA
    // ══════════════════════════════════════════════════════════════

    function cargarInstituciones() {
        const select = document.getElementById('IdInstitucion');
        if (!select) return;

        select.innerHTML = '<option value="" disabled selected>Cargando instituciones...</option>';

        fetch('/SEGTRACK/App/Controller/ControladorDispositivo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'accion=obtener_instituciones'
        })
        .then(r => r.json())
        .then(res => {
            select.innerHTML = '<option value="" disabled selected>-- Seleccione institución --</option>';
            if (!Array.isArray(res) || res.length === 0) {
                select.innerHTML += '<option value="" disabled>Sin instituciones disponibles</option>';
                return;
            }
            res.forEach(inst => {
                const opt = document.createElement('option');
                opt.value = inst.IdInstitucion;
                opt.textContent = inst.NombreInstitucion;
                select.appendChild(opt);
            });
        })
        .catch(() => {
            select.innerHTML = '<option value="" disabled selected>Error al cargar instituciones</option>';
        });
    }

    function cargarSedesPorInstitucion(idInstitucion) {
        const selectSede = document.getElementById('IdSede');
        const selectFunc = document.getElementById('IdFuncionario');
        const selectVisit = document.getElementById('IdVisitante');
        
        if (!selectSede) return;
        
        selectSede.innerHTML = '<option value="" disabled selected>Cargando sedes...</option>';
        selectSede.disabled = true;
        
        if (selectFunc) {
            selectFunc.innerHTML = '<option value="" disabled selected>Primero seleccione una sede...</option>';
            selectFunc.disabled = true;
        }
        if (selectVisit) {
            selectVisit.innerHTML = '<option value="" disabled selected>Primero seleccione una sede...</option>';
            selectVisit.disabled = true;
        }
        
        if (!idInstitucion) {
            selectSede.innerHTML = '<option value="" disabled selected>Primero seleccione una institución...</option>';
            return;
        }
        
        fetch('/SEGTRACK/App/Controller/ControladorDispositivo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `accion=obtener_sedes_por_institucion&id_institucion=${idInstitucion}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.sedes && data.sedes.length > 0) {
                selectSede.innerHTML = '<option value="" disabled selected>-- Seleccione una sede --</option>';
                data.sedes.forEach(sede => {
                    const opt = document.createElement('option');
                    opt.value = sede.IdSede;
                    opt.textContent = `${sede.TipoSede} — ${sede.Ciudad}`;
                    selectSede.appendChild(opt);
                });
                selectSede.disabled = false;
                selectSede.classList.add('is-valid');
            } else {
                selectSede.innerHTML = '<option value="" disabled selected>No hay sedes activas</option>';
            }
        })
        .catch(() => {
            selectSede.innerHTML = '<option value="" disabled selected>Error al cargar sedes</option>';
        });
    }

    function cargarFuncionariosPorSede(idSede) {
        const select = document.getElementById('IdFuncionario');
        if (!select) return;
        
        select.innerHTML = '<option value="" disabled selected>Cargando funcionarios...</option>';
        select.disabled = true;
        
        if (!idSede) {
            select.innerHTML = '<option value="" disabled selected>Primero seleccione una sede...</option>';
            return;
        }
        
        fetch('/SEGTRACK/App/Controller/ControladorDispositivo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `accion=obtener_funcionarios_por_sede&id_sede=${idSede}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.funcionarios && data.funcionarios.length > 0) {
                select.innerHTML = '<option value="" disabled selected>-- Seleccione funcionario --</option>';
                data.funcionarios.forEach(f => {
                    const opt = document.createElement('option');
                    opt.value = f.IdFuncionario;
                    opt.textContent = f.NombreFuncionario;
                    select.appendChild(opt);
                });
                select.disabled = false;
            } else {
                select.innerHTML = '<option value="" disabled selected>No hay funcionarios en esta sede</option>';
            }
        })
        .catch(() => {
            select.innerHTML = '<option value="" disabled selected>Error al cargar funcionarios</option>';
        });
    }

    function cargarVisitantesPorSede(idSede) {
        const select = document.getElementById('IdVisitante');
        if (!select) return;
        
        select.innerHTML = '<option value="" disabled selected>Cargando visitantes...</option>';
        select.disabled = true;
        
        if (!idSede) {
            select.innerHTML = '<option value="" disabled selected>Primero seleccione una sede...</option>';
            return;
        }
        
        fetch('/SEGTRACK/App/Controller/ControladorDispositivo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `accion=obtener_visitantes_por_sede&id_sede=${idSede}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.visitantes && data.visitantes.length > 0) {
                select.innerHTML = '<option value="" disabled selected>-- Seleccione visitante --</option>';
                data.visitantes.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v.IdVisitante;
                    opt.textContent = v.NombreVisitante;
                    select.appendChild(opt);
                });
                select.disabled = false;
            } else {
                select.innerHTML = '<option value="" disabled selected>No hay visitantes en esta sede</option>';
            }
        })
        .catch(() => {
            select.innerHTML = '<option value="" disabled selected>Error al cargar visitantes</option>';
        });
    }

    // ══════════════════════════════════════════════════════════════
    // EVENTOS DE CASCADA
    // ══════════════════════════════════════════════════════════════

    const selectInstitucion = document.getElementById('IdInstitucion');
    if (selectInstitucion) {
        selectInstitucion.addEventListener('change', function() {
            const idInstitucion = this.value;
            cargarSedesPorInstitucion(idInstitucion);
        });
    }

    const selectSede = document.getElementById('IdSede');
    if (selectSede) {
        selectSede.addEventListener('change', function() {
            const idSede = this.value;
            cargarFuncionariosPorSede(idSede);
            cargarVisitantesPorSede(idSede);
            
            const tieneVisitante = document.getElementById('TieneVisitante');
            const selectFunc = document.getElementById('IdFuncionario');
            const selectVisit = document.getElementById('IdVisitante');
            
            if (tieneVisitante && tieneVisitante.value === 'no') {
                if (selectFunc) selectFunc.value = '';
            } else {
                if (selectVisit) selectVisit.value = '';
            }
        });
    }

    cargarInstituciones();

    // ══════════════════════════════════════════════════════════════
    // VALIDACIONES EN TIEMPO REAL
    // ══════════════════════════════════════════════════════════════

    const inputSerial = document.getElementById('NumeroSerial');
    if (inputSerial) {
        inputSerial.addEventListener('input', function (e) {
            let valor = e.target.value.replace(/[^a-zA-Z0-9\-_]/g, '');
            if (valor.length > 50) valor = valor.substring(0, 50);
            e.target.value = valor;

            if (valor.length === 0) {
                e.target.classList.remove('is-valid', 'is-invalid');
            } else if (valor.length < 3) {
                e.target.classList.remove('is-valid');
                e.target.classList.add('is-invalid');
            } else {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
            }
        });
    }

    const tipoSelect = document.getElementById('TipoDispositivo');
    const campoOtro = document.getElementById('campoOtro');
    if (tipoSelect && campoOtro) {
        tipoSelect.addEventListener('change', function () {
            campoOtro.style.display = this.value === 'Otro' ? 'block' : 'none';
            if (this.value === 'Otro') campoOtro.querySelector('input').focus();
        });
    }

    const tieneVisitanteSelect = document.getElementById('TieneVisitante');
    const funcionarioContainer = document.getElementById('FuncionarioContainer');
    const visitanteContainer = document.getElementById('VisitanteContainer');
    const selectFunc = document.getElementById('IdFuncionario');
    const selectVisit = document.getElementById('IdVisitante');

    if (tieneVisitanteSelect && funcionarioContainer && visitanteContainer) {
        tieneVisitanteSelect.addEventListener('change', function () {
            if (this.value === 'si') {
                funcionarioContainer.style.display = 'none';
                visitanteContainer.style.display = 'block';
                if (selectFunc) {
                    selectFunc.value = '';
                    selectFunc.removeAttribute('required');
                }
                if (selectVisit) {
                    selectVisit.setAttribute('required', true);
                    if (selectSede && selectSede.value) {
                        cargarVisitantesPorSede(selectSede.value);
                    }
                }
            } else {
                funcionarioContainer.style.display = 'block';
                visitanteContainer.style.display = 'none';
                if (selectVisit) {
                    selectVisit.value = '';
                    selectVisit.removeAttribute('required');
                }
                if (selectFunc) {
                    selectFunc.setAttribute('required', true);
                    if (selectSede && selectSede.value) {
                        cargarFuncionariosPorSede(selectSede.value);
                    }
                }
            }
        });
    }

    // ══════════════════════════════════════════════════════════════
    // ENVÍO DEL FORMULARIO
    // ══════════════════════════════════════════════════════════════

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        const tipo = document.getElementById('TipoDispositivo').value.trim();
        const otroTipo = document.querySelector('input[name="OtroTipoDispositivo"]')?.value.trim() || '';
        const marca = document.getElementById('MarcaDispositivo').value.trim();
        const numeroSerial = document.getElementById('NumeroSerial').value.trim();
        const idFuncionario = document.getElementById('IdFuncionario').value;
        const idVisitante = document.getElementById('IdVisitante').value;
        const tieneVisitante = document.getElementById('TieneVisitante').value;

        const regexTexto = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s.,-]+$/;
        const regexSerial = /^[a-zA-Z0-9\-_]+$/;

        if (!tipo) {
            Swal.fire({ icon: 'error', title: 'Campo requerido', text: 'Debe seleccionar un tipo de dispositivo', confirmButtonColor: '#e74a3b' });
            return;
        }
        if (tipo === 'Otro' && !otroTipo) {
            Swal.fire({ icon: 'error', title: 'Campo requerido', text: 'Debe especificar el tipo de dispositivo en el campo "Otro"', confirmButtonColor: '#e74a3b' });
            return;
        }
        if (!marca) {
            Swal.fire({ icon: 'error', title: 'Campo requerido', text: 'Debe ingresar la marca del dispositivo', confirmButtonColor: '#e74a3b' });
            return;
        }
        if (!regexTexto.test(marca)) {
            Swal.fire({ icon: 'error', title: 'Caracteres inválidos', text: 'La marca contiene caracteres inválidos.', confirmButtonColor: '#e74a3b' });
            return;
        }
        if (numeroSerial) {
            if (!regexSerial.test(numeroSerial)) {
                Swal.fire({ icon: 'error', title: 'Serial inválido', html: 'El número serial solo puede contener:<br>• Letras (A-Z, a-z)<br>• Números (0-9)<br>• Guiones (-)<br>• Guiones bajos (_)', confirmButtonColor: '#e74a3b' });
                return;
            }
            if (numeroSerial.length < 3) {
                Swal.fire({ icon: 'warning', title: 'Serial muy corto', text: 'El número serial debe tener al menos 3 caracteres', confirmButtonColor: '#f6c23e' });
                return;
            }
        }

        const idInstitucion = document.getElementById('IdInstitucion').value;
        const idSede = document.getElementById('IdSede').value;

        if (!idInstitucion) {
            Swal.fire({ icon: 'error', title: 'Campo requerido', text: 'Debe seleccionar una institución', confirmButtonColor: '#e74a3b' });
            document.getElementById('IdInstitucion').focus();
            return;
        }
        if (!idSede) {
            Swal.fire({ icon: 'error', title: 'Campo requerido', text: 'Debe seleccionar una sede', confirmButtonColor: '#e74a3b' });
            document.getElementById('IdSede').focus();
            return;
        }

        if (tieneVisitante === 'no') {
            if (!idFuncionario) {
                Swal.fire({ icon: 'error', title: 'Funcionario requerido', text: 'Debe seleccionar un funcionario', confirmButtonColor: '#e74a3b' });
                return;
            }
        } else {
            if (!idVisitante) {
                Swal.fire({ icon: 'error', title: 'Visitante requerido', text: 'Debe seleccionar un visitante', confirmButtonColor: '#e74a3b' });
                return;
            }
        }

        const tipoFinal = tipo === 'Otro' ? otroTipo : tipo;
        const formData = new FormData();
        formData.append('accion', 'registrar');
        formData.append('TipoDispositivo', tipoFinal);
        formData.append('MarcaDispositivo', marca);
        formData.append('NumeroSerial', numeroSerial);
        formData.append('IdFuncionario', idFuncionario || '');
        formData.append('IdVisitante', idVisitante || '');

        Swal.fire({
            title: 'Procesando...',
            html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br>Validando y registrando dispositivo',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });

        try {
            const response = await fetch('/SEGTRACK/App/Controller/ControladorDispositivo.php', { method: 'POST', body: formData });
            const data = await response.json();
            Swal.close();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Dispositivo registrado!',
                    html: data.message,
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: true,
                    confirmButtonColor: '#1cc88a',
                    confirmButtonText: 'Entendido'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'No se pudo registrar',
                    html: data.message.replace(/\n/g, '<br>'),
                    confirmButtonColor: '#f6c23e',
                    confirmButtonText: 'Entendido',
                    footer: '<small class="text-muted">Revise la información e intente nuevamente</small>'
                });
            }
        } catch (error) {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                html: 'No se pudo conectar al servidor.<br>Por favor, intente nuevamente.',
                confirmButtonColor: '#e74a3b'
            });
        }
    });
});

// ══════════════════════════════════════════════════════════════
// FUNCIONES GLOBALES PARA LISTA Y SUPERVISOR
// ══════════════════════════════════════════════════════════════

function verQRDispositivo(rutaQR, idDispositivo) {
    var rutaCompleta = '/SEGTRACK/Public/' + rutaQR;
    document.getElementById('qrDispositivoId').textContent = idDispositivo;
    document.getElementById('qrImagenDispositivo').src = rutaCompleta;
    document.getElementById('btnDescargarQRDispositivo').href = rutaCompleta;
    document.getElementById('btnDescargarQRDispositivo').download = 'QR-Dispositivo-' + idDispositivo + '.png';
    $('#modalVerQRDispositivo').modal('show');
}

function enviarQRPorCorreo(idDispositivo, correoDestinatario) {
    if (!correoDestinatario || correoDestinatario.trim() === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Sin correo registrado',
            html: 'Este dispositivo no tiene un correo electrónico asociado.',
            confirmButtonColor: '#3085d6'
        });
        return;
    }

    Swal.fire({
        title: '📧 ¿Enviar código QR?',
        html: 'Se enviará el código QR al correo:<br><br><strong class="text-primary">' + correoDestinatario + '</strong>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '<i class="fas fa-paper-plane"></i> Sí, enviar',
        cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
        reverseButtons: true
    }).then(function (result) {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Enviando correo...',
                html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br>Por favor espere',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });

            $.ajax({
                url: '/SEGTRACK/App/Controller/ControladorDispositivo.php',
                type: 'POST',
                data: { accion: 'enviar_qr', id_dispositivo: idDispositivo },
                dataType: 'json',
                timeout: 30000,
                success: function (response) {
                    if (response.success) {
                        Swal.fire({ icon: 'success', title: '¡Correo enviado!', html: '<p>' + response.message + '</p>', timer: 4000, timerProgressBar: true, confirmButtonColor: '#1cc88a' });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error al enviar', text: response.message, confirmButtonColor: '#e74a3b' });
                    }
                },
                error: function (xhr, status) {
                    var errorMsg = status === 'timeout' ? 'La solicitud tardó demasiado tiempo.' : 'No se pudo conectar con el servidor.';
                    Swal.fire({ icon: 'error', title: 'Error de conexión', text: errorMsg, confirmButtonColor: '#e74a3b' });
                }
            });
        }
    });
}

function cargarDatosEdicionDispositivo(row) {
    document.getElementById('editIdDispositivo').value = row.IdDispositivo;
    document.getElementById('editTipoDispositivo').value = row.TipoDispositivo;
    document.getElementById('editMarcaDispositivo').value = row.MarcaDispositivo;
    document.getElementById('editNumeroSerial').value = row.NumeroSerial || '';
    document.getElementById('editIdFuncionario').value = row.IdFuncionario || '';
    document.getElementById('editIdVisitante').value = row.IdVisitante || '';
    
    var nombrePropietario = row.NombreFuncionario || row.NombreVisitante || 'No asignado';
    var tipoPropietario = row.NombreFuncionario ? 'Funcionario' : (row.NombreVisitante ? 'Visitante' : 'Sin propietario');
    document.getElementById('editNombrePropietario').value = tipoPropietario + ': ' + nombrePropietario;
    document.getElementById('editNombreInstitucion').value = row.NombreInstitucion || 'Sin institución asignada';
    document.getElementById('editTipoSede').value = row.TipoSede || 'Sin sede asignada';
    
    $('#modalEditarDispositivo').modal('show');
}

function confirmarCambioEstadoDispositivo(id, estado) {
    window.dispositivoACambiarEstado = id;
    window.estadoActualDispositivo = estado;

    var nuevoEstado = estado === 'Activo' ? 'Inactivo' : 'Activo';
    var accion = nuevoEstado === 'Activo' ? 'activar' : 'desactivar';
    var colorHeader = nuevoEstado === 'Activo' ? 'bg-success' : 'bg-warning';
    var icono = nuevoEstado === 'Activo' ? 'fa-lock-open' : 'fa-lock';

    $('#headerCambioEstadoDispositivo').removeClass('bg-success bg-warning').addClass(colorHeader + ' text-white');
    $('#tituloCambioEstadoDispositivo').html('<i class="fas ' + icono + ' mr-2"></i>' + accion.charAt(0).toUpperCase() + accion.slice(1) + ' Dispositivo');
    $('#mensajeCambioEstadoDispositivo').html('¿Está seguro que desea <strong>' + accion + '</strong> este dispositivo?');
    $('#modalCambiarEstadoDispositivo').modal('show');

    setTimeout(function () {
        var toggleLabel = document.getElementById('toggleEstadoVisualDispositivo');
        if (toggleLabel) {
            if (nuevoEstado === 'Activo') {
                toggleLabel.classList.add('activo');
            } else {
                toggleLabel.classList.remove('activo');
            }
        }
    }, 100);
}

// ══════════════════════════════════════════════════════════════
// EVENTOS PARA MODALES
// ══════════════════════════════════════════════════════════════

$(document).ready(function() {
    // Evento para guardar cambios en edición
    $(document).on('click', '#btnGuardarCambiosDispositivo', function() {
        var formData = {
            accion: 'actualizar',
            id: $('#editIdDispositivo').val(),
            tipo: $('#editTipoDispositivo').val(),
            marca: $('#editMarcaDispositivo').val(),
            serial: $('#editNumeroSerial').val(),
            id_funcionario: $('#editIdFuncionario').val() || null,
            id_visitante: $('#editIdVisitante').val() || null
        };
        
        Swal.fire({ title: 'Guardando...', text: 'Por favor espere', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        
        $.ajax({
            url: '/SEGTRACK/App/Controller/ControladorDispositivo.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.message, timer: 2000 }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo actualizar el dispositivo.' });
            }
        });
    });

    // Evento para confirmar cambio de estado
    $(document).on('click', '#btnConfirmarCambioEstadoDispositivo', function() {
        if (!window.dispositivoACambiarEstado) return;

        var nuevoEstado = window.estadoActualDispositivo === 'Activo' ? 'Inactivo' : 'Activo';
        $('#modalCambiarEstadoDispositivo').modal('hide');

        Swal.fire({ title: 'Procesando...', text: 'Por favor espere', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        $.ajax({
            url: '/SEGTRACK/App/Controller/ControladorDispositivo.php',
            type: 'POST',
            data: { accion: 'cambiar_estado', id: window.dispositivoACambiarEstado, estado: nuevoEstado },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.message, timer: 2000 }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo cambiar el estado del dispositivo.' });
            }
        });
    });

    // Carga dinámica de sedes en los filtros
    $(document).on('change', '#filtroInstitucion', function() {
        var idInstitucion = $(this).val();
        var $selectSede = $('#filtroSede');
        
        if (!idInstitucion) {
            location.reload();
            return;
        }
        
        $selectSede.html('<option value="">Cargando sedes...</option>');
        
        $.ajax({
            url: '/SEGTRACK/App/Controller/ControladorDispositivo.php',
            type: 'POST',
            data: {
                accion: 'obtener_sedes_por_institucion',
                id_institucion: idInstitucion
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.sedes && response.sedes.length > 0) {
                    var options = '<option value="">Todas</option>';
                    $.each(response.sedes, function(index, sede) {
                        options += '<option value="' + sede.IdSede + '">' + sede.TipoSede + ' — ' + sede.Ciudad + '</option>';
                    });
                    $selectSede.html(options);
                } else {
                    $selectSede.html('<option value="">No hay sedes disponibles</option>');
                }
            },
            error: function() {
                $selectSede.html('<option value="">Error al cargar sedes</option>');
            }
        });
    });
});