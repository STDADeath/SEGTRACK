// ============================================
// 🔌 VARIABLE GLOBAL
// ============================================
let vehiculoIdAEliminar = null;

// ============================================
// 🔌 CONFIGURAR CAMPO DE FECHA
// ============================================
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

    // ── Validación en tiempo real: PLACA (máx 7) ─────────────────────────────
    // ✅ FIX: BD tiene varchar(7), se baja el límite de 9 a 7
    const inputPlaca = document.getElementById('PlacaVehiculo');
    if (inputPlaca) {
        inputPlaca.addEventListener('input', function (e) {
            let valor = e.target.value.toUpperCase().replace(/[^A-Z0-9 -]/g, '');
            if (valor.length > 7) valor = valor.substring(0, 7);
            e.target.value = valor;

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

    // ── Validación en tiempo real: TARJETA (mín 11, máx 20) ──────────────────
    const inputTarjeta = document.getElementById('TarjetaPropiedad');
    if (inputTarjeta) {
        inputTarjeta.addEventListener('input', function (e) {
            let valor = e.target.value.replace(/[^a-zA-Z0-9 -]/g, '');
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
});

// ============================================
// 🔌 VALIDACIÓN Y REGISTRO DE VEHÍCULO
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');

    if (form) {
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

            // ✅ FIX: regex sin \s — usa espacio literal y guión
            const regexPlacaTarjeta = /^[a-zA-Z0-9 -]+$/;
            const regexDescripcion  = /^[a-zA-Z0-9 .,-]+$/;
            const regexIdSede       = /^\d+$/;

            // 1. Tipo de vehículo
            if (!tipoVehiculo) {
                Swal.fire({ icon: 'error', title: 'Campo obligatorio', text: 'Debe seleccionar un tipo de vehículo', confirmButtonColor: '#e74a3b' });
                return;
            }

            // 2. Placa — ✅ FIX: máximo ahora es 7 (varchar(7) en BD)
            if (!placa) {
                Swal.fire({ icon: 'error', title: 'Campo obligatorio', text: 'La placa del vehículo es obligatoria', confirmButtonColor: '#e74a3b' });
                return;
            }
            if (placa.length < 3) {
                Swal.fire({ icon: 'error', title: 'Placa muy corta', text: 'La placa debe tener al menos 3 caracteres', confirmButtonColor: '#e74a3b' });
                return;
            }
            if (placa.length > 7) {
                Swal.fire({ icon: 'error', title: 'Placa muy larga', text: 'La placa no puede tener más de 7 caracteres', confirmButtonColor: '#e74a3b' });
                return;
            }
            if (!regexPlacaTarjeta.test(placa)) {
                Swal.fire({ icon: 'error', title: 'Caracteres inválidos', html: 'La placa solo puede contener:<br>• Letras (A-Z)<br>• Números (0-9)<br>• Espacios<br>• Guiones (-)', confirmButtonColor: '#e74a3b' });
                return;
            }

            // 3. Descripción
            if (!descripcion) {
                Swal.fire({ icon: 'error', title: 'Campo obligatorio', text: 'La descripción del vehículo es obligatoria', confirmButtonColor: '#e74a3b' });
                return;
            }
            if (descripcion.length < 5) {
                Swal.fire({ icon: 'warning', title: 'Descripción muy corta', text: 'La descripción debe tener al menos 5 caracteres', confirmButtonColor: '#f6c23e' });
                return;
            }
            if (!regexDescripcion.test(descripcion)) {
                Swal.fire({ icon: 'error', title: 'Caracteres inválidos', text: 'La descripción contiene caracteres no válidos', confirmButtonColor: '#e74a3b' });
                return;
            }

            // 4. Tarjeta de propiedad
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
                Swal.fire({ icon: 'error', title: 'Caracteres inválidos', html: 'La tarjeta solo puede contener:<br>• Letras<br>• Números<br>• Espacios<br>• Guiones', confirmButtonColor: '#e74a3b' });
                return;
            }

            // 5. Sede
            if (!regexIdSede.test(idSede)) {
                Swal.fire({ icon: 'error', title: 'ID de Sede inválido', text: 'Debe seleccionar una sede válida', confirmButtonColor: '#e74a3b' });
                return;
            }

            // 6. Tipo de persona
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

            // ── Envío ──────────────────────────────────────────────────────────
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
    }
});

// ============================================
// 🔌 FUNCIONES GLOBALES
// ============================================
function cargarDatosEdicionVehiculo(row) {
    // ✅ FIX: usa jQuery dentro de una función que se llama cuando jQuery ya está cargado
    $('#editIdVehiculo').val(row.IdVehiculo);
    $('#editTipoVehiculo').val(row.TipoVehiculo);
    $('#editDescripcionVehiculo').val(row.DescripcionVehiculo);
    $('#editIdSede').val(row.IdSede);
    $('#editPlacaVehiculoDisabled').val(row.PlacaVehiculo);
    $('#editTarjetaPropiedadDisabled').val(row.TarjetaPropiedad);

    let fechaHora = row.FechaDeVehiculo;
    if (fechaHora) fechaHora = fechaHora.replace(' ', 'T').substring(0, 16);
    $('#editFechaDeVehiculoDisabled').val(fechaHora);
}

function confirmarEliminacionVehiculo(id) {
    vehiculoIdAEliminar = id;
    $('#confirmarEliminarModalVehiculo').modal('show');
}

// ============================================
// 🔌 EVENTOS JQUERY
// ✅ FIX: todo el código jQuery dentro de $(document).ready para garantizar
//    que jQuery esté cargado antes de ejecutarse (corrige "$ is not defined")
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    // Esperar a que jQuery esté disponible
    function esperarJQuery(callback) {
        if (typeof $ !== 'undefined') {
            callback();
        } else {
            setTimeout(function () { esperarJQuery(callback); }, 50);
        }
    }

    esperarJQuery(function () {

        $('#btnConfirmarEliminarVehiculo').on('click', function () {
            if (!vehiculoIdAEliminar) return;

            $.ajax({
                url: '../../Controller/ControladorVehiculo.php',
                type: 'POST',
                data: { accion: 'eliminar', id: vehiculoIdAEliminar },
                dataType: 'json',
                success: function (response) {
                    $('#confirmarEliminarModalVehiculo').modal('hide');
                    if (response.success) {
                        Swal.fire({ icon: 'success', title: 'Eliminado', text: '✅ Vehículo eliminado correctamente' })
                            .then(() => { $('#fila-' + vehiculoIdAEliminar).fadeOut(400, function () { $(this).remove(); }); });
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

        $('#btnGuardarCambiosVehiculo').on('click', function () {
            const id          = $('#editIdVehiculo').val();
            const tipo        = $('#editTipoVehiculo').val();
            const descripcion = $('#editDescripcionVehiculo').val().trim();
            const idsede      = $('#editIdSede').val().trim();
            // ✅ FIX: regex sin \s
            const regexDescripcion = /^[a-zA-Z0-9 .,-]+$/;

            if (!id || !tipo || !idsede) {
                Swal.fire({ icon: 'warning', title: 'Campos incompletos', text: 'Complete el Tipo de Vehículo y la Sede', confirmButtonColor: '#f6c23e' });
                return;
            }
            if (!descripcion || descripcion.length < 5) {
                Swal.fire({ icon: 'warning', title: 'Descripción inválida', text: 'La descripción debe tener al menos 5 caracteres', confirmButtonColor: '#f6c23e' });
                return;
            }
            if (!regexDescripcion.test(descripcion)) {
                Swal.fire({ icon: 'error', title: 'Caracteres inválidos', text: 'La descripción contiene caracteres no válidos', confirmButtonColor: '#e74a3b' });
                return;
            }

            $('#modalEditarVehiculo').modal('hide');
            Swal.fire({ title: 'Guardando cambios...', html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br>Por favor espere', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false });

            $.ajax({
                url: '../../Controller/ControladorVehiculo.php',
                type: 'POST',
                data: { accion: 'actualizar', id, tipo, descripcion, idsede },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        Swal.fire({ icon: 'success', title: '¡Actualizado!', text: 'Vehículo actualizado correctamente', timer: 2000, timerProgressBar: true, showConfirmButton: true, confirmButtonColor: '#1cc88a' })
                            .then(() => { location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', html: response.message.replace(/\n/g, '<br>'), confirmButtonColor: '#e74a3b' });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error de conexión', text: '❌ Error al intentar actualizar el vehículo', confirmButtonColor: '#e74a3b' });
                }
            });
        });

    }); // fin esperarJQuery
})