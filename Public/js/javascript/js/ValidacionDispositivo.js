// ══════════════════════════════════════════════════════════════
// SECCIÓN 1 — FORMULARIO DE REGISTRO (Dispositivos.php)
// ══════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formDispositivo');

    if (!form) return; // Si no estamos en la vista de registro, salir

    // ── Validación en tiempo real — Número Serial ─────────────────────────────
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

        inputSerial.addEventListener('focus', function () {
            if (!document.getElementById('serialHint')) {
                const hint = document.createElement('small');
                hint.id        = 'serialHint';
                hint.className = 'text-muted d-block mt-1';
                hint.innerHTML = '<i class="fas fa-info-circle me-1"></i> Letras, números, guiones (-) y guiones bajos (_). Opcional.';
                this.parentElement.parentElement.appendChild(hint);
            }
        });
    }

    // ── Submit — Validación y envío ───────────────────────────────────────────
    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        const tipo           = document.getElementById('TipoDispositivo').value.trim();
        const otroTipo       = document.querySelector('input[name="OtroTipoDispositivo"]')?.value.trim() || '';
        const marca          = document.getElementById('MarcaDispositivo').value.trim();
        const numeroSerial   = document.getElementById('NumeroSerial').value.trim();
        const idFuncionario  = document.getElementById('IdFuncionario').value.trim();
        const idVisitante    = document.getElementById('IdVisitante').value.trim();
        const tieneVisitante = document.getElementById('TieneVisitante').value;

        const regexTexto  = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s.,-]+$/;
        const regexSerial = /^[a-zA-Z0-9\-_]+$/;

        if (!tipo) {
            Swal.fire({ icon: 'error', title: 'Campo requerido', text: 'Debe seleccionar un tipo de dispositivo', confirmButtonColor: '#e74a3b' });
            return;
        }
        if (tipo === 'Otro' && !otroTipo) {
            Swal.fire({ icon: 'error', title: 'Campo requerido', text: 'Debe especificar el tipo de dispositivo en el campo "Otro"', confirmButtonColor: '#e74a3b' });
            return;
        }
        if (tipo === 'Otro' && !regexTexto.test(otroTipo)) {
            Swal.fire({ icon: 'error', title: 'Caracteres inválidos', text: 'El tipo de dispositivo contiene caracteres inválidos (solo letras, números y .-,)', confirmButtonColor: '#e74a3b' });
            return;
        }
        if (!marca) {
            Swal.fire({ icon: 'error', title: 'Campo requerido', text: 'Debe ingresar la marca del dispositivo', confirmButtonColor: '#e74a3b' });
            return;
        }
        if (!regexTexto.test(marca)) {
            Swal.fire({ icon: 'error', title: 'Caracteres inválidos', text: 'La marca contiene caracteres inválidos. Solo se permiten letras, números y .-,', confirmButtonColor: '#e74a3b' });
            return;
        }
        if (numeroSerial) {
            if (!regexSerial.test(numeroSerial)) {
                Swal.fire({ icon: 'error', title: 'Serial inválido', html: 'El número serial solo puede contener:<br>• Letras (A-Z, a-z)<br>• Números (0-9)<br>• Guiones (-)<br>• Guiones bajos (_)', confirmButtonColor: '#e74a3b' });
                return;
            }
            if (numeroSerial.length > 50) {
                Swal.fire({ icon: 'error', title: 'Serial muy largo', text: 'El número serial no puede exceder 50 caracteres', confirmButtonColor: '#e74a3b' });
                return;
            }
            if (numeroSerial.length < 3) {
                Swal.fire({ icon: 'warning', title: 'Serial muy corto', text: 'El número serial debe tener al menos 3 caracteres', confirmButtonColor: '#f6c23e' });
                return;
            }
        }

        const tieneIdFuncionario = idFuncionario !== '';
        const tieneIdVisitante   = idVisitante   !== '';

        if (tieneVisitante === 'no') {
            if (!tieneIdFuncionario) {
                Swal.fire({ icon: 'error', title: 'Funcionario requerido', text: 'Si el dispositivo pertenece a un funcionario, debe seleccionar uno de la lista', confirmButtonColor: '#e74a3b' });
                return;
            }
            if (tieneIdVisitante) {
                Swal.fire({ icon: 'error', title: 'Selección inválida', text: 'No puede seleccionar un visitante si indica que pertenece a un funcionario', confirmButtonColor: '#e74a3b' });
                return;
            }
        }
        if (tieneVisitante === 'si') {
            if (!tieneIdVisitante) {
                Swal.fire({ icon: 'error', title: 'Visitante requerido', text: 'Si el dispositivo pertenece a un visitante, debe seleccionar uno de la lista', confirmButtonColor: '#e74a3b' });
                return;
            }
            if (tieneIdFuncionario) {
                Swal.fire({ icon: 'error', title: 'Selección inválida', text: 'No puede seleccionar un funcionario si indica que pertenece a un visitante', confirmButtonColor: '#e74a3b' });
                return;
            }
        }

        const tipoFinal = tipo === 'Otro' ? otroTipo : tipo;
        const formData  = new FormData();
        formData.append('accion',           'registrar');
        formData.append('TipoDispositivo',  tipoFinal);
        formData.append('MarcaDispositivo', marca);
        formData.append('NumeroSerial',     numeroSerial);
        formData.append('IdFuncionario',    tieneIdFuncionario ? idFuncionario : '');
        formData.append('IdVisitante',      tieneIdVisitante   ? idVisitante   : '');

        Swal.fire({
            title: 'Procesando...',
            html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br>Validando y registrando dispositivo',
            allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false
        });

        try {
            const response = await fetch('../../Controller/ControladorDispositivo.php', { method: 'POST', body: formData });
            const data     = await response.json();
            Swal.close();

            if (data.success) {
                Swal.fire({
                    icon: 'success', title: '¡Dispositivo registrado!', html: data.message,
                    timer: 3000, timerProgressBar: true, showConfirmButton: true,
                    confirmButtonColor: '#1cc88a', confirmButtonText: 'Entendido'
                }).then(() => {
                    form.reset();
                    document.getElementById('campoOtro').style.display = 'none';
                    document.getElementById('FuncionarioContainer').style.display = 'block';
                    document.getElementById('VisitanteContainer').style.display = 'none';
                    if (inputSerial) inputSerial.classList.remove('is-valid', 'is-invalid');
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'warning', title: 'No se pudo registrar',
                    html: data.message.replace(/\n/g, '<br>'),
                    confirmButtonColor: '#f6c23e', confirmButtonText: 'Entendido',
                    footer: '<small class="text-muted">Revise la información e intente nuevamente</small>'
                });
            }
        } catch (error) {
            Swal.close();
            Swal.fire({ icon: 'error', title: 'Error de conexión', html: 'No se pudo conectar al servidor.<br>Por favor, intente nuevamente.', confirmButtonColor: '#e74a3b' });
        }
    });

    // ── Mostrar/Ocultar campo "Otro" ──────────────────────────────────────────
    const tipoSelect = document.getElementById('TipoDispositivo');
    const campoOtro  = document.getElementById('campoOtro');
    if (tipoSelect && campoOtro) {
        tipoSelect.addEventListener('change', function () {
            campoOtro.style.display = this.value === 'Otro' ? 'block' : 'none';
            if (this.value === 'Otro') campoOtro.querySelector('input').focus();
        });
    }

    // ── Mostrar/Ocultar Funcionario o Visitante ───────────────────────────────
    const tieneVisitanteSelect = document.getElementById('TieneVisitante');
    const funcionarioContainer = document.getElementById('FuncionarioContainer');
    const visitanteContainer   = document.getElementById('VisitanteContainer');
    const selectFuncionario    = document.getElementById('IdFuncionario');
    const selectVisitante      = document.getElementById('IdVisitante');

    if (tieneVisitanteSelect && funcionarioContainer && visitanteContainer) {
        tieneVisitanteSelect.addEventListener('change', function () {
            if (this.value === 'si') {
                funcionarioContainer.style.display = 'none';
                visitanteContainer.style.display   = 'block';
                selectFuncionario.value = '';
                selectVisitante.focus();
            } else {
                funcionarioContainer.style.display = 'block';
                visitanteContainer.style.display   = 'none';
                selectVisitante.value = '';
                selectFuncionario.focus();
            }
        });
    }
});


// ══════════════════════════════════════════════════════════════
// SECCIÓN 2 — LISTA ADMINISTRADOR (DispositivoLista.php)
// ══════════════════════════════════════════════════════════════

$(document).ready(function () {

    // ── DataTable — Administrador ─────────────────────────────────────────────
    if ($('#TablaDispositivo').length) {
        $('#TablaDispositivo').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json' },
            pageLength: 10,
            responsive: true,
            order: [[0, 'desc']]
        });
    }

    // ── DataTable — Supervisor ────────────────────────────────────────────────
    if ($('#TablaDispositivoSupervisor').length) {
        $('#TablaDispositivoSupervisor').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json' },
            pageLength: 10,
            responsive: true,
            order: [[0, 'asc']]
        });
    }

    // ── Validación en tiempo real — Serial en modal editar (solo Administrador) ─
    const editSerialInput = document.getElementById('editNumeroSerial');
    const editMarcaInput  = document.getElementById('editMarcaDispositivo');

    if (editSerialInput && !editSerialInput.hasAttribute('readonly')) {
        editSerialInput.addEventListener('input', function (e) {
            let valor = e.target.value.replace(/[^a-zA-Z0-9\-_]/g, '');
            if (valor.length > 50) valor = valor.substring(0, 50);
            e.target.value = valor;

            if (valor.length === 0) {
                e.target.classList.remove('is-valid', 'is-invalid');
            } else if (valor.length < 3) {
                e.target.classList.add('is-invalid');
                e.target.classList.remove('is-valid');
            } else {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
            }
        });
    }

    if (editMarcaInput && !editMarcaInput.hasAttribute('readonly')) {
        editMarcaInput.addEventListener('input', function (e) {
            const regexTexto = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s.,-]+$/;
            const valor = e.target.value;
            if (valor.length > 0) {
                if (regexTexto.test(valor)) {
                    e.target.classList.remove('is-invalid');
                    e.target.classList.add('is-valid');
                } else {
                    e.target.classList.add('is-invalid');
                    e.target.classList.remove('is-valid');
                }
            } else {
                e.target.classList.remove('is-invalid', 'is-valid');
            }
        });
    }

    // ── Guardar cambios — modal editar ────────────────────────────────────────
    $('#btnGuardarCambiosDispositivo').on('click', function () {
        const tipo   = $('#editTipoDispositivo').val();
        const marca  = $('#editMarcaDispositivo').val().trim();
        const serial = $('#editNumeroSerial').val().trim();

        const regexTexto  = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s.,-]+$/;
        const regexSerial = /^[a-zA-Z0-9\-_]+$/;

        if (!tipo) {
            Swal.fire({ icon: 'warning', title: 'Campo incompleto', text: 'Debe seleccionar un tipo de dispositivo', confirmButtonColor: '#f6c23e' });
            return;
        }

        // Validaciones adicionales solo en la vista de Administrador (marca editable)
        if (editMarcaInput && !editMarcaInput.hasAttribute('readonly')) {
            if (!marca) {
                Swal.fire({ icon: 'warning', title: 'Campo incompleto', text: 'Debe ingresar la marca del dispositivo', confirmButtonColor: '#f6c23e' });
                $('#editMarcaDispositivo').focus();
                return;
            }
            if (!regexTexto.test(marca)) {
                Swal.fire({ icon: 'error', title: 'Caracteres inválidos', text: 'La marca contiene caracteres inválidos. Solo letras, números y .-,', confirmButtonColor: '#e74a3b' });
                $('#editMarcaDispositivo').focus();
                return;
            }
        }

        // Validaciones de serial solo en la vista de Administrador (serial editable)
        if (editSerialInput && !editSerialInput.hasAttribute('readonly')) {
            if (serial) {
                if (!regexSerial.test(serial)) {
                    Swal.fire({ icon: 'error', title: 'Serial inválido', html: 'El número serial solo puede contener:<br>• Letras (A-Z, a-z)<br>• Números (0-9)<br>• Guiones (-)<br>• Guiones bajos (_)', confirmButtonColor: '#e74a3b' });
                    $('#editNumeroSerial').focus();
                    return;
                }
                if (serial.length < 3) {
                    Swal.fire({ icon: 'warning', title: 'Serial muy corto', text: 'El número serial debe tener al menos 3 caracteres', confirmButtonColor: '#f6c23e' });
                    $('#editNumeroSerial').focus();
                    return;
                }
                if (serial.length > 50) {
                    Swal.fire({ icon: 'error', title: 'Serial muy largo', text: 'El número serial no puede exceder 50 caracteres', confirmButtonColor: '#e74a3b' });
                    $('#editNumeroSerial').focus();
                    return;
                }
            }
        }

        $('#modalEditarDispositivo').modal('hide');
        Swal.fire({
            title: 'Guardando cambios...',
            html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br>Por favor espere',
            allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false
        });

        $.ajax({
            url: '../../Controller/ControladorDispositivo.php',
            type: 'POST',
            data: {
                accion:         'actualizar',
                id:             $('#editIdDispositivo').val(),
                tipo:           tipo,
                marca:          marca,
                serial:         serial,
                id_funcionario: $('#editIdFuncionario').val() || null,
                id_visitante:   $('#editIdVisitante').val()   || null
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success', title: '¡Éxito!',
                        html: response.message || 'Dispositivo actualizado correctamente',
                        timer: 3000, timerProgressBar: true,
                        showConfirmButton: true, confirmButtonColor: '#1cc88a'
                    }).then(function () { location.reload(); });
                } else {
                    Swal.fire({
                        icon: 'warning', title: 'No se pudo actualizar',
                        html: response.message.replace(/\n/g, '<br>'),
                        confirmButtonColor: '#f6c23e',
                        footer: '<small class="text-muted">Revise la información e intente nuevamente</small>'
                    });
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor', confirmButtonColor: '#e74a3b' });
            }
        });
    });

    // ── Confirmar cambio de estado — Supervisor ───────────────────────────────
    $('#btnConfirmarCambioEstadoDispositivo').on('click', function () {
        if (!window.dispositivoACambiarEstado) return;

        var nuevoEstado = window.estadoActualDispositivo === 'Activo' ? 'Inactivo' : 'Activo';
        $('#modalCambiarEstadoDispositivo').modal('hide');

        Swal.fire({ title: 'Procesando...', text: 'Por favor espere', allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });

        $.ajax({
            url: '../../Controller/ControladorDispositivo.php',
            type: 'POST',
            data: { accion: 'cambiar_estado', id: window.dispositivoACambiarEstado, estado: nuevoEstado },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success', title: '¡Éxito!',
                        text: response.message || 'Estado cambiado correctamente',
                        timer: 2000, showConfirmButton: false
                    }).then(function () { location.reload(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'No se pudo cambiar el estado' });
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo cambiar el estado del dispositivo' });
            }
        });
    });

    // ── Limpiar variables al cerrar modal de estado — Supervisor ──────────────
    $('#modalCambiarEstadoDispositivo').on('hidden.bs.modal', function () {
        window.dispositivoACambiarEstado = null;
        window.estadoActualDispositivo   = null;
    });

});


// ══════════════════════════════════════════════════════════════
// SECCIÓN 3 — FUNCIONES GLOBALES (Lista + Supervisor)
// ══════════════════════════════════════════════════════════════

// ── Ver QR ────────────────────────────────────────────────────────────────────
function verQRDispositivo(rutaQR, idDispositivo) {
    var rutaCompleta = '/SEGTRACK/Public/' + rutaQR;
    $('#qrDispositivoId').text(idDispositivo);
    $('#qrImagenDispositivo').attr('src', rutaCompleta);
    $('#btnDescargarQRDispositivo').attr('href', rutaCompleta).attr('download', 'QR-Dispositivo-' + idDispositivo + '.png');
    $('#modalVerQRDispositivo').modal('show');
}

// ── Enviar QR por correo ──────────────────────────────────────────────────────
function enviarQRPorCorreo(idDispositivo, correoDestinatario) {
    if (!correoDestinatario || correoDestinatario.trim() === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Sin correo registrado',
            html: 'Este dispositivo no tiene un correo electrónico asociado.<br><small class="text-muted">Ni el funcionario ni el visitante tienen correo registrado.</small>',
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
                allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false
            });

            $.ajax({
                url: '../../Controller/ControladorDispositivo.php',
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

// ── Cargar datos modal editar ─────────────────────────────────────────────────
function cargarDatosEdicionDispositivo(row) {
    $('#editIdDispositivo').val(row.IdDispositivo);
    $('#editTipoDispositivo').val(row.TipoDispositivo);
    $('#editMarcaDispositivo').val(row.MarcaDispositivo).removeClass('is-valid is-invalid');
    $('#editNumeroSerial').val(row.NumeroSerial || '').removeClass('is-valid is-invalid');
    $('#editIdFuncionario').val(row.IdFuncionario || '');
    $('#editIdVisitante').val(row.IdVisitante || '');
    $('#editNombreFuncionario').val(row.NombreFuncionario || '-');
    $('#editNombreVisitante').val(row.NombreVisitante || '-');
    $('#modalEditarDispositivo').modal('show');
}

// ── Confirmar cambio de estado ────────────────────────────────────────────────
function confirmarCambioEstadoDispositivo(id, estado) {
    window.dispositivoACambiarEstado = id;
    window.estadoActualDispositivo   = estado;

    var nuevoEstado = estado === 'Activo' ? 'Inactivo' : 'Activo';
    var accion      = nuevoEstado === 'Activo' ? 'activar' : 'desactivar';
    var colorHeader = nuevoEstado === 'Activo' ? 'bg-success' : 'bg-warning';
    var icono       = nuevoEstado === 'Activo' ? 'fa-lock-open' : 'fa-lock';

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