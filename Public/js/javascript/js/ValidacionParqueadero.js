// ============================================
// 📌 VARIABLE GLOBAL
// ============================================
let vehiculoIdAEliminar = null;

// ============================================
// 📌 VERIFICACIÓN DE JQUERY
// ============================================
if (typeof jQuery === 'undefined') {
    console.error('jQuery no está cargado. Cargando dinámicamente...');
    
    // Cargar jQuery dinámicamente
    const script = document.createElement('script');
    script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
    script.onload = function() {
        console.log('jQuery cargado dinámicamente');
        // Re-ejecutar funciones que dependen de jQuery
        inicializarEventosJQuery();
    };
    document.head.appendChild(script);
} else {
    // jQuery ya está cargado, ejecutar normalmente
    $(document).ready(inicializarEventosJQuery);
}

// ===========================================
// 📌 VALIDACIÓN Y REGISTRO DE VEHÍCULO
// ===========================================
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');

    if (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            // Obtener valores
            const tipo = document.getElementById('TipoVehiculo').value.trim();
            const placa = document.getElementById('PlacaVehiculo').value.trim();
            const descripcion = document.getElementById('DescripcionVehiculo').value.trim();
            const tarjeta = document.getElementById('TarjetaPropiedad').value.trim();
            const idSede = document.getElementById('IdSede').value.trim();

            // Expresiones regulares
            const regexPlacaTarjeta = /^[a-zA-Z0-9\s-]*$/;
            const regexDescripcion = /^[a-zA-Z0-9\s.,-]*$/;
            const regexIdSede = /^\d+$/;

            // Validaciones
            if (!tipo) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo obligatorio',
                    text: 'Por favor seleccione el tipo de vehículo.'
                });
                return;
            }

            if (!placa) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo obligatorio',
                    text: 'Por favor ingrese la placa del vehículo.'
                });
                return;
            }

            if (!regexPlacaTarjeta.test(placa)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El campo Placa solo puede contener letras, números, espacios y guiones.'
                });
                return;
            }

            if (descripcion.length > 0 && !regexDescripcion.test(descripcion)) {
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

            if (!idSede) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo obligatorio',
                    text: 'Por favor ingrese el ID de Sede.'
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

            // Mostrar loading
            Swal.fire({
                title: 'Registrando vehículo...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Preparar datos
            const formData = new FormData(form);
            formData.append('accion', 'registrar');
            
            const url = "../../Controller/ControladorParqueadero.php";

            // Enviar con fetch
            fetch(url, {
                method: "POST",
                body: formData
            })
            .then(response => {
                // Primero verificar el estado HTTP
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Verificar que la respuesta sea JSON válido
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.includes("application/json")) {
                    return response.json();
                } else {
                    // Si no es JSON, leer como texto para debug
                    return response.text().then(text => {
                        console.error('Respuesta no JSON recibida:', text);
                        throw new Error('El servidor respondió con formato incorrecto');
                    });
                }
            })
            .then(data => {
                console.log("Respuesta del servidor:", data);

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: data.message || 'Vehículo registrado correctamente.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = './Vehiculolista.php';
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
                console.error("Error completo en la solicitud:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'Error: ' + error.message
                });
            });
        });
    }
});

// ============================================
// 📌 FUNCIÓN PARA ACTUALIZAR FECHA/HORA
// ============================================
function actualizarFechaHora() {
    const campoFecha = document.getElementById('FechaParqueadero');
    
    if (campoFecha) {
        const ahora = new Date();
        
        const opciones = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric', 
            hour: '2-digit', 
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        };
        
        const fechaFormateada = ahora.toLocaleString('es-CO', opciones);
        campoFecha.value = fechaFormateada;
    }
}

// Iniciar actualización si existe el campo
if (document.getElementById('FechaParqueadero')) {
    setInterval(actualizarFechaHora, 1000);
    actualizarFechaHora();
}

// ============================================
// 📌 FUNCIONES GLOBALES
// ============================================
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

function confirmarEliminacionVehiculo(id) {
    vehiculoIdAEliminar = id;
    $('#confirmarEliminarModalVehiculo').modal('show');
}

// ============================================
// 📌 EVENTOS CON JQUERY
// ============================================
function inicializarEventosJQuery() {
    console.log('Inicializando eventos jQuery...');

    $('#btnConfirmarEliminarVehiculo').click(function() {
        if (!vehiculoIdAEliminar) {
            console.error('No hay ID de vehículo para eliminar');
            return;
        }

        console.log('Eliminando vehículo ID:', vehiculoIdAEliminar);

        Swal.fire({
            title: 'Eliminando...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

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
                        title: '¡Eliminado!',
                        text: 'Vehículo eliminado correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        $('#fila-' + vehiculoIdAEliminar).fadeOut(400, function() {
                            $(this).remove();
                        });
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Error al eliminar el vehículo'
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
                    text: 'Error al intentar eliminar el vehículo'
                });
            }
        });
    });

    $('#btnGuardarCambiosVehiculo').click(function() {
        const id = $('#editIdVehiculo').val();
        const tipo = $('#editTipoVehiculo').val();
        const descripcion = $('#editDescripcionVehiculo').val();
        const idsede = $('#editIdSede').val();

        console.log('Actualizando - ID:', id, 'Tipo:', tipo, 'Descripción:', descripcion, 'Sede:', idsede);

        if (!id || !tipo || !idsede) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Complete todos los campos obligatorios: Tipo de Vehículo e ID Sede'
            });
            return;
        }

        const regexDescripcion = /^[a-zA-Z0-9\s.,-]*$/;
        if (descripcion && !regexDescripcion.test(descripcion)) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La descripción contiene caracteres no válidos'
            });
            return;
        }

        $('#modalEditarVehiculo').modal('hide');

        Swal.fire({
            title: 'Guardando...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
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
                        title: '¡Éxito!',
                        text: 'Vehículo actualizado correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Error al actualizar el vehículo'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en AJAX:', status, error);
                console.error('Respuesta:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'Error al intentar actualizar el vehículo'
                });
            }
        });
    });
}