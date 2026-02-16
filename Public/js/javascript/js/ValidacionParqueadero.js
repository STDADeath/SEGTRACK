// ============================================
// 🔌 VARIABLE GLOBAL
// ============================================
let vehiculoIdAEliminar = null;

// ============================================
// 🔌 CONFIGURAR CAMPO DE FECHA
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    const campoFecha = document.getElementById('FechaParqueadero');
    
    if (campoFecha) {
        // Obtener fecha y hora actual
        const ahora = new Date();
        
        // Formatear a YYYY-MM-DDTHH:MM (formato requerido por datetime-local)
        const year = ahora.getFullYear();
        const mes = String(ahora.getMonth() + 1).padStart(2, '0');
        const dia = String(ahora.getDate()).padStart(2, '0');
        const horas = String(ahora.getHours()).padStart(2, '0');
        const minutos = String(ahora.getMinutes()).padStart(2, '0');
        
        const fechaHoraActual = `${year}-${mes}-${dia}T${horas}:${minutos}`;
        
        // Establecer valor por defecto (hora actual)
        campoFecha.value = fechaHoraActual;
        
        // Establecer fecha mínima (inicio del día actual)
        const fechaMinima = `${year}-${mes}-${dia}T00:00`;
        campoFecha.min = fechaMinima;
        
        // Establecer fecha máxima (fin del día actual)
        const fechaMaxima = `${year}-${mes}-${dia}T23:59`;
        campoFecha.max = fechaMaxima;
        
        // Hacer el campo de solo lectura para evitar edición manual
        campoFecha.readOnly = true;
        
        // Agregar evento para actualizar la hora automáticamente cada minuto
        setInterval(function() {
            const nuevaHora = new Date();
            const nuevaHoraFormateada = `${year}-${mes}-${dia}T${String(nuevaHora.getHours()).padStart(2, '0')}:${String(nuevaHora.getMinutes()).padStart(2, '0')}`;
            campoFecha.value = nuevaHoraFormateada;
        }, 60000); // Actualizar cada 60 segundos
    }

    // 🆕 VALIDACIÓN EN TIEMPO REAL DE PLACA
    const inputPlaca = document.getElementById('PlacaVehiculo');
    if (inputPlaca) {
        inputPlaca.addEventListener('input', function(e) {
            let valor = e.target.value;
            // Convertir a mayúsculas y eliminar caracteres no permitidos
            valor = valor.toUpperCase().replace(/[^A-Z0-9\s-]/g, '');
            e.target.value = valor;
            
            // Validar longitud
            if (valor.length > 9) {
                e.target.value = valor.substring(0, 9);
                e.target.classList.add('is-invalid');
            } else if (valor.length >= 3 && valor.length <= 9) {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
            } else if (valor.length > 0 && valor.length < 3) {
                e.target.classList.add('is-invalid');
                e.target.classList.remove('is-valid');
            } else {
                e.target.classList.remove('is-invalid', 'is-valid');
            }
        });
    }

    // 🆕 VALIDACIÓN EN TIEMPO REAL DE TARJETA DE PROPIEDAD
    const inputTarjeta = document.getElementById('TarjetaPropiedad');
    if (inputTarjeta) {
        inputTarjeta.addEventListener('input', function(e) {
            let valor = e.target.value;
            // Eliminar caracteres no permitidos
            valor = valor.replace(/[^a-zA-Z0-9\s-]/g, '');
            e.target.value = valor;
            
            if (valor.length > 0) {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
            } else {
                e.target.classList.remove('is-valid', 'is-invalid');
            }
        });
    }
});

// ===========================================
// 🔌 VALIDACIÓN Y REGISTRO DE VEHÍCULO
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');

    if (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            // Obtenemos los valores
            const tipoVehiculo = document.getElementById('TipoVehiculo').value.trim();
            const placaRaw = document.getElementById('PlacaVehiculo').value;
            const descripcionRaw = document.getElementById('DescripcionVehiculo').value;
            const tarjetaRaw = document.getElementById('TarjetaPropiedad').value;
            const idSede = document.getElementById('IdSede').value.trim();

            // Aplicar trim
            const placa = placaRaw.trim().toUpperCase();
            const descripcion = descripcionRaw.trim();
            const tarjeta = tarjetaRaw.trim().toUpperCase();

            // Expresiones regulares
            const regexPlacaTarjeta = /^[a-zA-Z0-9\s-]+$/;
            const regexDescripcion = /^[a-zA-Z0-9\s.,-]+$/;
            const regexIdSede = /^\d+$/;

            // ⚠️ VALIDACIÓN 1: Tipo de vehículo
            if (!tipoVehiculo || tipoVehiculo === '') {
                Swal.fire({
                    icon: 'error',
                    title: 'Campo obligatorio',
                    text: 'Debe seleccionar un tipo de vehículo',
                    confirmButtonColor: '#e74a3b'
                });
                return;
            }

            // ⚠️ VALIDACIÓN 2: PLACA
            if (!placa || placa === '' || placa.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Campo obligatorio',
                    text: 'La placa del vehículo es obligatoria',
                    confirmButtonColor: '#e74a3b'
                });
                return;
            }

            if (placa.length < 3) {
                Swal.fire({
                    icon: 'error',
                    title: 'Placa muy corta',
                    text: 'La placa debe tener al menos 3 caracteres',
                    confirmButtonColor: '#e74a3b'
                });
                return;
            }

            if (placa.length > 9) {
                Swal.fire({
                    icon: 'error',
                    title: 'Placa muy larga',
                    text: 'La placa no puede tener más de 9 caracteres',
                    confirmButtonColor: '#e74a3b'
                });
                return;
            }

            if (!regexPlacaTarjeta.test(placa)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Caracteres inválidos',
                    html: 'La placa solo puede contener:<br>• Letras (A-Z)<br>• Números (0-9)<br>• Espacios<br>• Guiones (-)',
                    confirmButtonColor: '#e74a3b'
                });
                return;
            }

            // ⚠️ VALIDACIÓN 3: DESCRIPCIÓN
            if (!descripcion || descripcion === '' || descripcion.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Campo obligatorio',
                    text: 'La descripción del vehículo es obligatoria',
                    confirmButtonColor: '#e74a3b'
                });
                return;
            }

            if (descripcion.length < 5) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Descripción muy corta',
                    text: 'La descripción debe tener al menos 5 caracteres',
                    confirmButtonColor: '#f6c23e'
                });
                return;
            }

            if (!regexDescripcion.test(descripcion)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Caracteres inválidos',
                    text: 'La descripción contiene caracteres no válidos',
                    confirmButtonColor: '#e74a3b'
                });
                return;
            }

            // ⚠️ VALIDACIÓN 4: TARJETA DE PROPIEDAD
            if (!tarjeta || tarjeta === '' || tarjeta.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Campo obligatorio',
                    text: 'La tarjeta de propiedad es obligatoria',
                    confirmButtonColor: '#e74a3b'
                });
                return;
            }

            if (!regexPlacaTarjeta.test(tarjeta)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Caracteres inválidos',
                    html: 'La tarjeta de propiedad solo puede contener:<br>• Letras<br>• Números<br>• Espacios<br>• Guiones',
                    confirmButtonColor: '#e74a3b'
                });
                return;
            }

            // ⚠️ VALIDACIÓN 5: ID DE SEDE
            if (!regexIdSede.test(idSede)) {
                Swal.fire({
                    icon: 'error',
                    title: 'ID de Sede inválido',
                    text: 'El ID de Sede solo puede contener números',
                    confirmButtonColor: '#e74a3b'
                });
                return;
            }

            // Preparar FormData
            const formData = new FormData(form);
            formData.delete('FechaParqueadero'); // El servidor genera la fecha
            formData.append('accion', 'registrar');
            const url = "../../Controller/ControladorParqueadero.php";

            // Mostrar loading
            Swal.fire({
                title: 'Registrando vehículo...',
                html: '<i class="fas fa-spinner fa-spin fa-3x text-success mb-3"></i><br>Validando y guardando datos',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });

            fetch(url, {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log("Respuesta del servidor:", data);

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
                        // Limpiar clases de validación
                        if (inputPlaca) inputPlaca.classList.remove('is-valid', 'is-invalid');
                        if (inputTarjeta) inputTarjeta.classList.remove('is-valid', 'is-invalid');
                        location.reload();
                    });
                } else {
                    // ⚠️ ERROR DEL SERVIDOR (duplicados, etc.)
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
            .catch(error => {
                console.error("Error en la solicitud:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    html: 'Ocurrió un problema al enviar los datos al servidor.<br>Por favor, intente nuevamente.',
                    confirmButtonColor: '#e74a3b',
                    footer: '<small>Si el problema persiste, contacte al administrador</small>'
                });
            });
        });
    }
});

// ============================================
// 🔌 FUNCIONES GLOBALES
// ============================================

// Cargar datos en el modal de edición
function cargarDatosEdicionVehiculo(row) {
    $('#editIdVehiculo').val(row.IdParqueadero);
    $('#editTipoVehiculo').val(row.TipoVehiculo);
    $('#editDescripcionVehiculo').val(row.DescripcionVehiculo);
    $('#editIdSede').val(row.IdSede);

    $('#editPlacaVehiculoDisabled').val(row.PlacaVehiculo);
    $('#editTarjetaPropiedadDisabled').val(row.TarjetaPropiedad);

    let fechaHora = row.FechaParqueadero;
    if (fechaHora) {
        fechaHora = fechaHora.replace(' ', 'T').substring(0, 16);
    }
    $('#editFechaParqueaderoDisabled').val(fechaHora);
}

// Confirmar eliminación
function confirmarEliminacionVehiculo(id) {
    vehiculoIdAEliminar = id;
    $('#confirmarEliminarModalVehiculo').modal('show');
}

// ============================================
// 🔌 EVENTOS CON JQUERY
// ============================================

$(document).ready(function() {

    // Botón confirmar eliminación
    $('#btnConfirmarEliminarVehiculo').click(function() {
        if (!vehiculoIdAEliminar) return;

        console.log('Eliminando vehículo ID:', vehiculoIdAEliminar);

        $.ajax({
            url: '../../Controller/ControladorParqueadero.php',
            type: 'POST',
            data: {
                accion: 'eliminar',
                id: vehiculoIdAEliminar
            },
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta eliminación:', response);
                $('#confirmarEliminarModalVehiculo').modal('hide');
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
                        text: '✅ Vehículo eliminado correctamente'
                    }).then(() => {
                        $('#fila-' + vehiculoIdAEliminar).fadeOut(400, function() {
                            $(this).remove();
                        });
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: '❌ Error: ' + response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en AJAX:', status, error);
                console.error('Respuesta:', xhr.responseText);
                $('#confirmarEliminarModalVehiculo').modal('hide');
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: '❌ Error al intentar eliminar el vehículo'
                });
            }
        });
    });

    // Botón guardar cambios
    $('#btnGuardarCambiosVehiculo').click(function() {
        const id = $('#editIdVehiculo').val();
        const tipo = $('#editTipoVehiculo').val();
        const descripcion = $('#editDescripcionVehiculo').val().trim();
        const idsede = $('#editIdSede').val().trim();

        console.log('Actualizando - ID:', id, 'Tipo:', tipo, 'Descripción:', descripcion, 'Sede:', idsede);

        // Expresiones regulares
        const regexDescripcion = /^[a-zA-Z0-9\s.,-]+$/;

        // Validaciones
        if (!id || !tipo || !idsede) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: '⚠️ Complete todos los campos obligatorios: Tipo de Vehículo e ID Sede',
                confirmButtonColor: '#f6c23e'
            });
            return;
        }

        if (!descripcion || descripcion.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo obligatorio',
                text: '⚠️ El campo Descripción es obligatorio',
                confirmButtonColor: '#f6c23e'
            });
            return;
        }

        if (descripcion.length < 5) {
            Swal.fire({
                icon: 'warning',
                title: 'Descripción muy corta',
                text: 'La descripción debe tener al menos 5 caracteres',
                confirmButtonColor: '#f6c23e'
            });
            return;
        }

        if (!regexDescripcion.test(descripcion)) {
            Swal.fire({
                icon: 'error',
                title: 'Caracteres inválidos',
                text: 'La descripción contiene caracteres no válidos',
                confirmButtonColor: '#e74a3b'
            });
            return;
        }

        $('#modalEditarVehiculo').modal('hide');

        Swal.fire({
            title: 'Guardando cambios...',
            html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br>Por favor espere',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });

        $.ajax({
            url: '../../Controller/ControladorParqueadero.php',
            type: 'POST',
            data: {
                accion: 'actualizar',
                id: id,
                tipo: tipo,
                descripcion: descripcion,
                idsede: idsede
            },
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta actualización:', response);
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizado!',
                        text: 'Vehículo actualizado correctamente',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: true,
                        confirmButtonColor: '#1cc88a'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: response.message.replace(/\n/g, '<br>'),
                        confirmButtonColor: '#e74a3b'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en AJAX:', status, error);
                console.error('Respuesta:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: '❌ Error al intentar actualizar el vehículo',
                    confirmButtonColor: '#e74a3b'
                });
            }
        });
    });

}); // Fin de $(document).ready()