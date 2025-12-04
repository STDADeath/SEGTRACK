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
});

// ===========================================
// 🔌 VALIDACIÓN Y REGISTRO DE VEHÍCULO
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');

    if (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            // Obtenemos los valores SIN TRIM INICIAL para validar espacios vacíos correctamente
            const placaRaw = document.getElementById('PlacaVehiculo').value;
            const descripcionRaw = document.getElementById('DescripcionVehiculo').value;
            const tarjetaRaw = document.getElementById('TarjetaPropiedad').value;
            const idSede = document.getElementById('IdSede').value.trim();
            const fechaParqueadero = document.getElementById('FechaParqueadero').value;

            // Aplicar trim después de validar que existan
            const placa = placaRaw.trim();
            const descripcion = descripcionRaw.trim();
            const tarjeta = tarjetaRaw.trim();

            // ⚠️ VALIDACIÓN 1: CAMPOS OBLIGATORIOS
            // Verificar que todos los campos requeridos tengan datos
            if (!placa || placa === '' || placa.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo obligatorio',
                    text: '⚠️ El campo Placa del Vehículo es obligatorio y no puede estar vacío.'
                });
                return;
            }

            if (!descripcion || descripcion === '' || descripcion.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo obligatorio',
                    text: '⚠️ El campo Descripción del Vehículo es obligatorio y no puede estar vacío.'
                });
                return;
            }

            if (!tarjeta || tarjeta === '' || tarjeta.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo obligatorio',
                    text: '⚠️ El campo Tarjeta de Propiedad es obligatorio y no puede estar vacío.'
                });
                return;
            }

            // ⚠️ VALIDACIÓN 2: LONGITUD DE PLACA (máximo 9 caracteres)
            // Validación tanto mínima como máxima para placas
            if (placa.length < 3) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '❌ El campo Placa debe tener al menos 3 caracteres.'
                });
                return;
            }

            if (placa.length > 9) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '❌ El campo Placa no puede tener más de 9 caracteres.'
                });
                return;
            }

            // Expresiones regulares
            const regexPlacaTarjeta = /^[a-zA-Z0-9\s-]*$/;
            const regexDescripcion = /^[a-zA-Z0-9\s.,-]*$/;
            const regexIdSede = /^\d+$/;

            // ⚠️ VALIDACIÓN 3: FORMATO DE PLACA
            // Solo letras, números, espacios y guiones
            if (!regexPlacaTarjeta.test(placa)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El campo Placa solo puede contener letras, números, espacios y guiones.'
                });
                return;
            }

            // ⚠️ VALIDACIÓN 4: FORMATO DE DESCRIPCIÓN
            // Permitir letras, números, espacios, puntos, comas y guiones
            if (!regexDescripcion.test(descripcion)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El campo Descripción contiene caracteres no válidos.'
                });
                return;
            }

            // ⚠️ VALIDACIÓN 5: FORMATO DE TARJETA DE PROPIEDAD
            // Ya validamos que no esté vacío, ahora validamos el formato
            if (!regexPlacaTarjeta.test(tarjeta)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El campo Tarjeta de Propiedad solo puede contener letras, números, espacios y guiones.'
                });
                return;
            }

            // ⚠️ VALIDACIÓN 6: ID DE SEDE
            // Debe ser solo números
            if (!regexIdSede.test(idSede)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El campo ID de Sede solo puede contener números.'
                });
                return;
            }

            // ⚠️ VALIDACIÓN 7: YA NO VALIDAMOS FECHA EN CLIENTE
            // La validación de fecha la hará el servidor con su propia zona horaria
            // Esto evita problemas de diferencia de zonas horarias entre cliente y servidor

            // ⚠️ CORRECCIÓN: Enviar fecha en formato que el servidor pueda validar correctamente
            // En lugar de enviar la fecha formateada, enviamos solo la acción
            // y dejamos que el servidor genere la fecha con su zona horaria
            const ahoraExacto = new Date();
            
            // NO enviamos la fecha, el servidor la generará automáticamente
            const fechaHoraFinal = null;

            // Preparar FormData SIN fecha (el servidor la generará)
            const formData = new FormData(form);
            formData.delete('FechaParqueadero'); // Eliminar la fecha del cliente
            formData.append('accion', 'registrar');
            const url = "../../Controller/ControladorParqueadero.php";

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
                        title: 'Vehículo registrado',
                        text: data.message || 'El vehículo fue agregado correctamente.',
                        showConfirmButton: true,
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        form.reset();
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al registrar',
                        text: data.message || 'No se pudo registrar el vehículo.'
                    });
                }
            })
            .catch(error => {
                console.error("Error en la solicitud:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'Ocurrió un problema al enviar los datos al servidor.'
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
        const descripcion = $('#editDescripcionVehiculo').val();
        const idsede = $('#editIdSede').val();

        console.log('Actualizando - ID:', id, 'Tipo:', tipo, 'Descripción:', descripcion, 'Sede:', idsede);

        // ⚠️ VALIDACIÓN EN EDICIÓN: CAMPOS OBLIGATORIOS
        if (!id || !tipo || !idsede) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: '⚠️ Complete todos los campos obligatorios: Tipo de Vehículo e ID Sede'
            });
            return;
        }

        // ⚠️ VALIDACIÓN EN EDICIÓN: DESCRIPCIÓN OBLIGATORIA
        if (!descripcion || descripcion.trim().length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo obligatorio',
                text: '⚠️ El campo Descripción es obligatorio'
            });
            return;
        }

        // ⚠️ VALIDACIÓN EN EDICIÓN: FORMATO DE DESCRIPCIÓN
        const regexDescripcion = /^[a-zA-Z0-9\s.,-]*$/;
        if (!regexDescripcion.test(descripcion)) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La descripción contiene caracteres no válidos'
            });
            return;
        }

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
                $('#modalEditarVehiculo').modal('hide');
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Actualizado',
                        text: '✅ Vehículo actualizado correctamente'
                    }).then(() => {
                        location.reload();
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
                $('#modalEditarVehiculo').modal('hide');
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: '❌ Error al intentar actualizar el vehículo'
                });
            }
        });
    });

}); // Fin de $(document).ready()