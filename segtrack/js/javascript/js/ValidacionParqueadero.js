// ============================================
// 📌 VARIABLE GLOBAL
// ============================================
let vehiculoIdAEliminar = null;

// ============================================
// 📌 VALIDACIÓN Y REGISTRO DE VEHÍCULO
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');

    if (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            // Obtenemos los valores
            const placa = document.getElementById('PlacaVehiculo').value.trim();
            const descripcion = document.getElementById('DescripcionVehiculo').value.trim();
            const tarjeta = document.getElementById('TarjetaPropiedad').value.trim();
            const idSede = document.getElementById('IdSede').value.trim();

            // Expresiones regulares
            const regexPlacaTarjeta = /^[a-zA-Z0-9\s-]*$/;
            const regexDescripcion = /^[a-zA-Z0-9\s.,-]*$/;
            const regexIdSede = /^\d+$/;

            // Validaciones
            if (!regexPlacaTarjeta.test(placa)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El campo Placa solo puede contener letras, números, espacios y guiones.'
                });
                return;
            }

            if (!regexDescripcion.test(descripcion)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El campo Descripción contiene caracteres no válidos.'
                });
                return;
            }

            if (tarjeta.length > 0 && !regexPlacaTarjeta.test(tarjeta)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El campo Tarjeta de Propiedad solo puede contener letras, números, espacios y guiones.'
                });
                return;
            }

            if (!regexIdSede.test(idSede)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El campo ID de Sede solo puede contener números.'
                });
                return;
            }

            // Si pasa validaciones, enviar con fetch
            const formData = new FormData(form);
            formData.append('accion', 'registrar');
            const url = "../Controller/parqueadero_dispositivo/ControladorParqueadero.php";

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
                        text: data.message || 'El vehículo fue agregado correctamente.'
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
// 📌 FUNCIONES GLOBALES
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
// 📌 EVENTOS CON JQUERY
// ============================================

$(document).ready(function() {

    // Botón confirmar eliminación
    $('#btnConfirmarEliminarVehiculo').click(function() {
        if (!vehiculoIdAEliminar) return;

        console.log('Eliminando vehículo ID:', vehiculoIdAEliminar);

        $.ajax({
            url: '../Controller/parqueadero_dispositivo/ControladorParqueadero.php',
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

        // Validar campos
        if (!id || !tipo || !idsede) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: '⚠️ Complete todos los campos obligatorios: Tipo de Vehículo e ID Sede'
            });
            return;
        }

        // Validar que la descripción sea válida
        const regexDescripcion = /^[a-zA-Z0-9\s.,-]*$/;
        if (descripcion && !regexDescripcion.test(descripcion)) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La descripción contiene caracteres no válidos'
            });
            return;
        }

        $.ajax({
            url: '../Controller/parqueadero_dispositivo/ControladorParqueadero.php',
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