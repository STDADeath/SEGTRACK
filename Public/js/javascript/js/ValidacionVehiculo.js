// ============================================================
// ValidacionVehiculo.js
// ============================================================


// ══════════════════════════════════════════════════════════════
// SECCIÓN 1 — CONFIGURAR CAMPO DE FECHA Y VALIDACIONES EN TIEMPO REAL
// ══════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', function () {
    const campoFecha = document.getElementById('FechaDeVehiculo');

    if (campoFecha) {
        const ahora   = new Date();
        const year    = ahora.getFullYear();
        const mes     = String(ahora.getMonth() + 1).padStart(2, '0');
        const dia     = String(ahora.getDate()).padStart(2, '0');
        const horas   = String(ahora.getHours()).padStart(2, '0');
        const minutos = String(ahora.getMinutes()).padStart(2, '0');

        const fechaHoraActual = `${year}-${mes}-${dia}T${horas}:${minutos}`;
        campoFecha.value      = fechaHoraActual;
        campoFecha.min        = `${year}-${mes}-${dia}T00:00`;
        campoFecha.max        = `${year}-${mes}-${dia}T23:59`;
        campoFecha.readOnly   = true;

        setInterval(function () {
            const n = new Date();
            campoFecha.value = `${year}-${mes}-${dia}T${String(n.getHours()).padStart(2, '0')}:${String(n.getMinutes()).padStart(2, '0')}`;
        }, 60000);
    }

    // ── Validación en tiempo real: PLACA ─────────────────────────────────────
    const inputPlaca = document.getElementById('PlacaVehiculo');
    if (inputPlaca) {
        inputPlaca.addEventListener('input', function (e) {
            let valor = e.target.value.toUpperCase().replace(/[^a-zA-Z\u00C0-\u024F0-9 \-]/gi, '');
            if (valor.length > 7) valor = valor.substring(0, 7);
            e.target.value = valor;

            const tipoVehiculo = document.getElementById('TipoVehiculo')?.value || '';
            if (tipoVehiculo === 'Bicicleta' && (valor === '' || valor === 'N-A' || valor === 'N A')) {
                e.target.classList.remove('is-valid', 'is-invalid');
                return;
            }

            if (valor.length === 0) {
                e.target.classList.remove('is-valid', 'is-invalid');
            } else if (valor.length >= 3) {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
            } else {
                e.target.classList.remove('is-valid');
                e.target.classList.add('is-invalid');
            }
        });
    }

    // ── Validación en tiempo real: TARJETA ────────────────────────────────────
    const inputTarjeta = document.getElementById('TarjetaPropiedad');
    if (inputTarjeta) {
        inputTarjeta.addEventListener('input', function (e) {
            let valor = e.target.value.replace(/[^a-zA-Z\u00C0-\u024F0-9 \-]/g, '');
            if (valor.length > 20) valor = valor.substring(0, 20);
            e.target.value = valor;

            if (valor.length === 0) {
                e.target.classList.remove('is-valid', 'is-invalid');
            } else if (valor.length >= 11 && valor.length <= 20) {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
            } else {
                e.target.classList.remove('is-valid');
                e.target.classList.add('is-invalid');
            }
        });
    }

    // ── Autocompletar placa cuando se selecciona Bicicleta ───────────────────
    const selectTipo = document.getElementById('TipoVehiculo');
    if (selectTipo && inputPlaca) {
        selectTipo.addEventListener('change', function () {
            if (this.value === 'Bicicleta') {
                inputPlaca.value        = 'N-A';
                inputPlaca.readOnly     = true;
                inputPlaca.style.cursor = 'not-allowed';
                inputPlaca.classList.add('bg-light');
                inputPlaca.classList.remove('is-invalid');
                inputPlaca.classList.add('is-valid');
            } else {
                if (inputPlaca.readOnly) {
                    inputPlaca.value = '';
                    inputPlaca.classList.remove('is-valid', 'is-invalid');
                }
                inputPlaca.readOnly     = false;
                inputPlaca.style.cursor = '';
                inputPlaca.classList.remove('bg-light');
            }
        });
    }
});


// ══════════════════════════════════════════════════════════════
// SECCIÓN 2 — FORMULARIO DE REGISTRO (Vehiculo.php)
// ══════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');

    if (!form) return;

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const tipoVehiculo  = document.getElementById('TipoVehiculo').value.trim();
        const placa         = document.getElementById('PlacaVehiculo').value.trim().toUpperCase();
        const descripcion   = document.getElementById('DescripcionVehiculo').value.trim();
        const tarjeta       = document.getElementById('TarjetaPropiedad').value.trim().toUpperCase();
        const idSede        = document.getElementById('IdSede').value.trim();
        const tipoPersona   = document.getElementById('TipoPersona').value.trim();
        const idFuncionario = document.getElementById('IdFuncionario')?.value.trim() || '';
        const idVisitante   = document.getElementById('IdVisitante')?.value.trim()  || '';

        const regexPlacaTarjeta = /^[a-zA-Z\u00C0-\u024F0-9 \-]+$/;
        const regexDescripcion  = /^[a-zA-Z\u00C0-\u024F0-9 .,\-]+$/;
        const regexIdSede       = /^\d+$/;

        if (!tipoVehiculo) {
            Swal.fire({ icon: 'error', title: 'Campo obligatorio', text: 'Debe seleccionar un tipo de vehículo', confirmButtonColor: '#e74a3b' });
            return;
        }

        if (!placa) {
            Swal.fire({ icon: 'error', title: 'Campo obligatorio', text: 'La placa del vehículo es obligatoria', confirmButtonColor: '#e74a3b' });
            return;
        }
        if (tipoVehiculo !== 'Bicicleta') {
            if (placa.length < 3) {
                Swal.fire({ icon: 'error', title: 'Placa muy corta', text: 'La placa debe tener al menos 3 caracteres', confirmButtonColor: '#e74a3b' });
                return;
            }
            if (placa.length > 7) {
                Swal.fire({ icon: 'error', title: 'Placa muy larga', text: 'La placa no puede tener más de 7 caracteres', confirmButtonColor: '#e74a3b' });
                return;
            }
        }
        if (!regexPlacaTarjeta.test(placa)) {
            Swal.fire({ icon: 'error', title: 'Caracteres inválidos', html: 'La placa solo puede contener:<br>• Letras (incluyendo tildes y ñ)<br>• Números (0-9)<br>• Espacios<br>• Guiones (-)', confirmButtonColor: '#e74a3b' });
            return;
        }

        if (!descripcion) {
            Swal.fire({ icon: 'error', title: 'Campo obligatorio', text: 'La descripción del vehículo es obligatoria', confirmButtonColor: '#e74a3b' });
            return;
        }
        if (descripcion.length < 5) {
            Swal.fire({ icon: 'warning', title: 'Descripción muy corta', text: 'La descripción debe tener al menos 5 caracteres', confirmButtonColor: '#f6c23e' });
            return;
        }
        if (!regexDescripcion.test(descripcion)) {
            Swal.fire({ icon: 'error', title: 'Caracteres inválidos', text: 'La descripción contiene caracteres no válidos. Use solo letras, números, espacios, puntos, comas y guiones.', confirmButtonColor: '#e74a3b' });
            return;
        }

        if (!tarjeta) {
            Swal.fire({ icon: 'error', title: 'Campo obligatorio', text: 'La tarjeta de propiedad es obligatoria', confirmButtonColor: '#e74a3b' });
            return;
        }
        if (tarjeta.length < 11) {
            Swal.fire({ icon: 'error', title: 'Tarjeta muy corta', text: 'La tarjeta de propiedad debe tener al menos 11 caracteres', confirmButtonColor: '#e74a3b' });
            return;
        }
        if (tarjeta.length > 20) {
            Swal.fire({ icon: 'error', title: 'Tarjeta muy larga', text: 'La tarjeta de propiedad no puede superar los 20 caracteres', confirmButtonColor: '#e74a3b' });
            return;
        }
        if (!regexPlacaTarjeta.test(tarjeta)) {
            Swal.fire({ icon: 'error', title: 'Caracteres inválidos', html: 'La tarjeta solo puede contener:<br>• Letras (incluyendo tildes y ñ)<br>• Números<br>• Espacios<br>• Guiones', confirmButtonColor: '#e74a3b' });
            return;
        }

        if (!regexIdSede.test(idSede)) {
            Swal.fire({ icon: 'error', title: 'ID de Sede inválido', text: 'Debe seleccionar una sede válida', confirmButtonColor: '#e74a3b' });
            return;
        }
        if (!tipoPersona) {
            Swal.fire({ icon: 'error', title: 'Campo obligatorio', text: 'Debe seleccionar si el vehículo es de un Funcionario o Visitante', confirmButtonColor: '#e74a3b' });
            return;
        }
        if (tipoPersona === 'Funcionario' && !idFuncionario) {
            Swal.fire({ icon: 'error', title: 'Campo obligatorio', text: 'Debe seleccionar el funcionario al que pertenece el vehículo', confirmButtonColor: '#e74a3b' });
            return;
        }
        if (tipoPersona === 'Visitante' && !idVisitante) {
            Swal.fire({ icon: 'error', title: 'Campo obligatorio', text: 'Debe seleccionar el visitante al que pertenece el vehículo', confirmButtonColor: '#e74a3b' });
            return;
        }

        // ── Envío ─────────────────────────────────────────────────────────────
        const formData = new FormData(form);
        formData.delete('FechaDeVehiculo');
        formData.append('accion', 'registrar');

        Swal.fire({
            title: 'Registrando vehículo...',
            html: '<i class="fas fa-spinner fa-spin fa-3x text-success mb-3"></i><br>Validando y guardando datos',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });

        fetch('../../Controller/ControladorVehiculo.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Vehículo registrado!',
                        html: data.message || 'El vehículo fue agregado correctamente.',
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: true,
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#1cc88a'
                    }).then(() => {
                        form.reset();
                        document.getElementById('PlacaVehiculo')?.classList.remove('is-valid', 'is-invalid');
                        document.getElementById('TarjetaPropiedad')?.classList.remove('is-valid', 'is-invalid');
                        const ip = document.getElementById('PlacaVehiculo');
                        if (ip) { ip.readOnly = false; ip.style.cursor = ''; ip.classList.remove('bg-light'); }
                        document.getElementById('divFuncionario')?.classList.add('d-none');
                        document.getElementById('divVisitante')?.classList.add('d-none');
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
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    html: 'Ocurrió un problema al enviar los datos.<br>Por favor, intente nuevamente.',
                    confirmButtonColor: '#e74a3b'
                });
            });
    });
});


// ══════════════════════════════════════════════════════════════
// SECCIÓN 3 — INICIALIZACIÓN jQuery (Lista + Supervisor)
// ══════════════════════════════════════════════════════════════

$(document).ready(function () {

    // ── DataTable — Administrador ─────────────────────────────────────────────
    if ($('#TablaVehiculos').length) {
        if ($.fn.DataTable.isDataTable('#TablaVehiculos')) {
            $('#TablaVehiculos').DataTable().destroy();
        }
        $('#TablaVehiculos').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json' },
            pageLength: 10,
            responsive: true,
            order: [[0, 'desc']],
            // Desactivar búsqueda interna para no interferir con filtros PHP
            searching: false,
            orderClasses: false
        });
    }

    // ── DataTable — Supervisor ────────────────────────────────────────────────
    if ($('#TablaVehiculoSupervisor').length) {
        if ($.fn.DataTable.isDataTable('#TablaVehiculoSupervisor')) {
            $('#TablaVehiculoSupervisor').DataTable().destroy();
        }
        $('#TablaVehiculoSupervisor').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json' },
            pageLength: 10,
            responsive: true,
            order: [[0, 'asc']],
            // Desactivar búsqueda interna para no interferir con filtros PHP
            searching: false,
            orderClasses: false
        });
    }

    // ── Guardar cambios — modal editar ────────────────────────────────────────
    $('#btnGuardarCambiosVehiculo').on('click', function () {
        const id          = $('#editIdVehiculo').val();
        const tipo        = $('#editTipoVehiculo').val();
        const descripcion = $('#editDescripcionVehiculo').val().trim();
        const idsede      = $('#editIdSede').val();
        const regexDesc   = /^[a-zA-Z\u00C0-\u024F0-9 .,\-]+$/;

        if (!id || !tipo || !idsede) {
            Swal.fire({ icon: 'warning', title: 'Campos incompletos', text: 'Complete el Tipo de Vehículo y la Sede', confirmButtonColor: '#f6c23e' });
            return;
        }
        if (!descripcion || descripcion.length < 5) {
            Swal.fire({ icon: 'warning', title: 'Descripción inválida', text: 'La descripción debe tener al menos 5 caracteres', confirmButtonColor: '#f6c23e' });
            return;
        }
        if (!regexDesc.test(descripcion)) {
            Swal.fire({ icon: 'error', title: 'Caracteres inválidos', text: 'La descripción contiene caracteres no válidos', confirmButtonColor: '#e74a3b' });
            return;
        }

        $('#modalEditarVehiculo').modal('hide');
        Swal.fire({
            title: 'Guardando cambios...',
            html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br>Por favor espere',
            allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false
        });

        $.ajax({
            url: '../../Controller/ControladorVehiculo.php',
            type: 'POST',
            data: { accion: 'actualizar', id, tipo, descripcion, idsede },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success', title: '¡Actualizado!',
                        text: 'Vehículo actualizado correctamente',
                        timer: 2000, timerProgressBar: true,
                        showConfirmButton: true, confirmButtonColor: '#1cc88a'
                    }).then(function () { location.reload(); });
                } else {
                    Swal.fire({
                        icon: 'error', title: 'Error',
                        html: response.message.replace(/\n/g, '<br>'),
                        confirmButtonColor: '#e74a3b'
                    });
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor', confirmButtonColor: '#e74a3b' });
            }
        });
    });

    $('#modalEditarVehiculo').on('hidden.bs.modal', function () {
        $('#btnGuardarCambiosVehiculo').prop('disabled', false).html('Guardar Cambios');
    });

    // ── Confirmar cambio de estado — Supervisor ───────────────────────────────
    $('#btnConfirmarCambioEstadoVehiculo').on('click', function () {
        if (!window.vehiculoACambiarEstado) return;

        const nuevoEstado = window.estadoActualVehiculo === 'Activo' ? 'Inactivo' : 'Activo';
        $('#modalCambiarEstadoVehiculo').modal('hide');
        Swal.fire({ title: 'Procesando...', text: 'Por favor espere', allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });

        $.ajax({
            url: '../../Controller/ControladorVehiculo.php',
            type: 'POST',
            data: { accion: 'cambiar_estado', id: window.vehiculoACambiarEstado, estado: nuevoEstado },
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
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo cambiar el estado del vehículo' });
            }
        });
    });

    $('#modalCambiarEstadoVehiculo').on('hidden.bs.modal', function () {
        window.vehiculoACambiarEstado = null;
        window.estadoActualVehiculo   = null;
        $('#btnConfirmarCambioEstadoVehiculo').prop('disabled', false).html('Confirmar');
    });

    // ── Confirmar eliminación ─────────────────────────────────────────────────
    $('#btnConfirmarEliminarVehiculo').on('click', function () {
        if (!window.vehiculoIdAEliminar) return;

        $.ajax({
            url: '../../Controller/ControladorVehiculo.php',
            type: 'POST',
            data: { accion: 'eliminar', id: window.vehiculoIdAEliminar },
            dataType: 'json',
            success: function (response) {
                $('#confirmarEliminarModalVehiculo').modal('hide');
                if (response.success) {
                    Swal.fire({ icon: 'success', title: 'Eliminado', text: '✅ Vehículo eliminado correctamente' })
                        .then(function () {
                            $('#fila-' + window.vehiculoIdAEliminar).fadeOut(400, function () { $(this).remove(); });
                        });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: '❌ Error: ' + response.message });
                }
            },
            error: function () {
                $('#confirmarEliminarModalVehiculo').modal('hide');
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: '❌ Error al intentar eliminar el vehículo' });
            }
        });
    });

});


// ══════════════════════════════════════════════════════════════
// SECCIÓN 4 — FUNCIONES GLOBALES (Lista + Supervisor)
// ══════════════════════════════════════════════════════════════

function verQRVehiculo(rutaQR, idVehiculo) {
    var rutaCompleta = '/SEGTRACK/Public/' + rutaQR;
    $('#qrVehiculoId').text(idVehiculo);
    $('#qrImagenVehiculo').attr('src', rutaCompleta);
    $('#btnDescargarQRVehiculo').attr('href', rutaCompleta).attr('download', 'QR-Vehiculo-' + idVehiculo + '.png');
    $('#modalVerQRVehiculo').modal('show');
}

function manejarEnvioQR(idVehiculo, placa, esFuncionario, correoVisitante) {
    if (esFuncionario) {
        Swal.fire({
            title: '📧 Enviar Código QR',
            html: `<p>Se enviará el QR al <strong>correo registrado</strong> del funcionario propietario del vehículo:</p>
                   <p class="text-primary fw-bold">Placa: ${placa}</p>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="fas fa-paper-plane"></i> Enviar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            reverseButtons: true
        }).then(function (result) {
            if (result.isConfirmed) enviarQRVehiculo(idVehiculo, '', placa);
        });
    } else if (correoVisitante) {
        Swal.fire({
            title: '📧 Enviar Código QR',
            html: `<p>Se enviará el QR al <strong>correo registrado</strong> del visitante:</p>
                   <p class="text-primary fw-bold">${correoVisitante}</p>
                   <small class="text-muted">Placa: ${placa}</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="fas fa-paper-plane"></i> Enviar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            reverseButtons: true
        }).then(function (result) {
            if (result.isConfirmed) enviarQRVehiculo(idVehiculo, '', placa);
        });
    } else {
        Swal.fire({
            title: '📧 Enviar Código QR',
            html: `<p class="mb-3">El visitante no tiene correo registrado. Ingresa el correo destinatario:</p>
                   <p class="text-primary fw-bold">Placa: ${placa}</p>
                   <input type="email" id="correoInput" class="swal2-input" placeholder="ejemplo@correo.com" style="width:80%;">`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="fas fa-paper-plane"></i> Enviar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            reverseButtons: true,
            preConfirm: function () {
                const correo = document.getElementById('correoInput').value;
                if (!correo) { Swal.showValidationMessage('Por favor ingresa un correo'); return false; }
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) { Swal.showValidationMessage('Correo no válido'); return false; }
                return correo;
            }
        }).then(function (result) {
            if (result.isConfirmed && result.value) enviarQRVehiculo(idVehiculo, result.value, placa);
        });
    }
}

function enviarQRVehiculo(idVehiculo, correoDestinatario, placa) {
    Swal.fire({
        title: 'Enviando correo...',
        html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br>Por favor espere',
        allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false
    });

    $.ajax({
        url: '../../Controller/ControladorVehiculo.php',
        type: 'POST',
        data: { accion: 'enviar_qr', id_vehiculo: idVehiculo, correo_destinatario: correoDestinatario },
        dataType: 'json',
        timeout: 30000,
        success: function (response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Correo enviado!',
                    html: `<p>${response.message}</p><small class="text-muted">Placa: <strong>${placa}</strong></small>`,
                    timer: 4000,
                    timerProgressBar: true,
                    confirmButtonColor: '#1cc88a'
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Error al enviar', text: response.message, confirmButtonColor: '#e74a3b' });
            }
        },
        error: function (xhr, status) {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: status === 'timeout' ? 'La solicitud tardó demasiado.' : 'No se pudo conectar con el servidor.',
                confirmButtonColor: '#e74a3b'
            });
        }
    });
}

function cargarDatosEdicionVehiculo(row) {
    $('#editIdVehiculo').val(row.IdVehiculo);
    $('#editTipoVehiculo').val(row.TipoVehiculo);
    $('#editDescripcionVehiculo').val(row.DescripcionVehiculo);
    $('#editIdSede').val(row.IdSede);
    $('#editPlacaVehiculoDisabled').val(row.PlacaVehiculo);
    $('#editTarjetaPropiedadDisabled').val(row.TarjetaPropiedad);

    if (row.NombreFuncionario) {
        $('#editPropietarioDisabled').val('Funcionario: ' + row.NombreFuncionario);
    } else if (row.NombreVisitante) {
        $('#editPropietarioDisabled').val('Visitante: ' + row.NombreVisitante);
    } else {
        $('#editPropietarioDisabled').val('Sin asignar');
    }

    var fechaHora = row.FechaDeVehiculo;
    if (fechaHora) fechaHora = fechaHora.replace(' ', 'T').substring(0, 16);
    $('#editFechaDeVehiculoDisabled').val(fechaHora);

    $('#modalEditarVehiculo').modal('show');
}

function confirmarCambioEstadoVehiculo(id, estado) {
    window.vehiculoACambiarEstado = id;
    window.estadoActualVehiculo   = estado;

    var nuevoEstado = estado === 'Activo' ? 'Inactivo' : 'Activo';
    var accion      = nuevoEstado === 'Activo' ? 'activar' : 'desactivar';
    var colorHeader = nuevoEstado === 'Activo' ? 'bg-success' : 'bg-warning';
    var icono       = nuevoEstado === 'Activo' ? 'fa-lock-open' : 'fa-lock';

    $('#headerCambioEstadoVehiculo').removeClass('bg-success bg-warning').addClass(colorHeader + ' text-white');
    $('#tituloCambioEstadoVehiculo').html('<i class="fas ' + icono + ' me-2"></i>' + accion.charAt(0).toUpperCase() + accion.slice(1) + ' Vehículo');
    $('#mensajeCambioEstadoVehiculo').html('¿Está seguro que desea <strong>' + accion + '</strong> este vehículo?');
    $('#modalCambiarEstadoVehiculo').modal('show');

    setTimeout(function () {
        var toggleLabel = document.getElementById('toggleEstadoVisualVehiculo');
        if (toggleLabel) {
            nuevoEstado === 'Activo' ? toggleLabel.classList.add('activo') : toggleLabel.classList.remove('activo');
        }
    }, 100);
}

function confirmarEliminacionVehiculo(id) {
    window.vehiculoIdAEliminar = id;
    $('#confirmarEliminarModalVehiculo').modal('show');
}