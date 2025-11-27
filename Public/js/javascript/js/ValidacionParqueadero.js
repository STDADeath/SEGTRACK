// ============================================
// 📌 VARIABLE GLOBAL PARA ELIMINAR VEHÍCULOS
// ============================================
let vehiculoIdAEliminar = null;

// ===========================================
// 📌 VALIDACIÓN Y REGISTRO DE VEHÍCULO
// ===========================================
document.addEventListener('DOMContentLoaded', function () {
    // Buscar el formulario en la página
    const form = document.querySelector('form');

    // Si existe el formulario, agregar evento submit
    if (form) {
        form.addEventListener('submit', function (event) {
            // Prevenir el envío normal del formulario
            event.preventDefault();

            // ========================================
            // 📌 OBTENER VALORES DE LOS CAMPOS
            // ========================================
            const tipo = document.getElementById('TipoVehiculo').value.trim();
            const placa = document.getElementById('PlacaVehiculo').value.trim();
            const descripcion = document.getElementById('DescripcionVehiculo').value.trim();
            const tarjeta = document.getElementById('TarjetaPropiedad').value.trim();
            const idSede = document.getElementById('IdSede').value.trim();

            // ========================================
            // 📌 EXPRESIONES REGULARES PARA VALIDACIÓN
            // ========================================
            // Solo letras, números, espacios y guiones
            const regexPlacaTarjeta = /^[a-zA-Z0-9\s-]*$/;
            // Letras, números, espacios, puntos, comas y guiones
            const regexDescripcion = /^[a-zA-Z0-9\s.,-]*$/;
            // Solo números
            const regexIdSede = /^\d+$/;

            // ========================================
            // 📌 VALIDACIONES DE CAMPOS
            // ========================================
            
            // Validar que se haya seleccionado un tipo de vehículo
            if (!tipo) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo obligatorio',
                    text: 'Por favor seleccione el tipo de vehículo.'
                });
                return; // Detener ejecución
            }

            // Validar que se haya ingresado una placa
            if (!placa) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo obligatorio',
                    text: 'Por favor ingrese la placa del vehículo.'
                });
                return;
            }

            // Validar formato de la placa
            if (!regexPlacaTarjeta.test(placa)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El campo Placa solo puede contener letras, números, espacios y guiones.'
                });
                return;
            }

            // Validar formato de descripción (si tiene contenido)
            if (descripcion.length > 0 && !regexDescripcion.test(descripcion)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El campo Descripción contiene caracteres no válidos.'
                });
                return;
            }

            // Validar formato de tarjeta de propiedad (si tiene contenido)
            if (tarjeta.length > 0 && !regexPlacaTarjeta.test(tarjeta)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El campo Tarjeta de Propiedad solo puede contener letras, números, espacios y guiones.'
                });
                return;
            }

            // Validar que se haya ingresado ID de Sede
            if (!idSede) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo obligatorio',
                    text: 'Por favor ingrese el ID de Sede.'
                });
                return;
            }

            // Validar que ID de Sede sea numérico
            if (!regexIdSede.test(idSede)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El campo ID de Sede solo puede contener números.'
                });
                return;
            }

            // ========================================
            // 📌 MOSTRAR LOADING MIENTRAS SE PROCESA
            // ========================================
            Swal.fire({
                title: 'Registrando vehículo...',
                text: 'Por favor espere',
                allowOutsideClick: false, // No permitir cerrar haciendo clic afuera
                didOpen: () => {
                    Swal.showLoading(); // Mostrar spinner de carga
                }
            });

            // ========================================
            // 📌 PREPARAR Y ENVIAR DATOS AL SERVIDOR
            // ========================================
            // Crear FormData con todos los datos del formulario
            const formData = new FormData(form);
            // Agregar la acción que debe ejecutar el controlador
            formData.append('accion', 'registrar');
            // ⚠️ IMPORTANTE: NO enviamos FechaParqueadero
            // La fecha se establece automáticamente en el servidor
            
            // URL del controlador PHP
            const url = "../../Controller/ControladorParqueadero.php";

            // Enviar datos usando Fetch API
            fetch(url, {
                method: "POST",
                body: formData
            })
            .then(response => response.json()) // Convertir respuesta a JSON
            .then(data => {
                // Mostrar respuesta en consola para debugging
                console.log("Respuesta del servidor:", data);

                // ========================================
                // 📌 PROCESAR RESPUESTA DEL SERVIDOR
                // ========================================
                if (data.success) {
                    // Si el registro fue exitoso
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: data.message || 'Vehículo registrado correctamente.',
                        timer: 2000, // Auto-cerrar después de 2 segundos
                        showConfirmButton: false
                    }).then(() => {
                        // Redirigir a la lista de vehículos
                        window.location.href = './Vehiculolista.php';
                    });
                } else {
                    // Si hubo un error en el registro
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al registrar',
                        text: data.message || 'No se pudo registrar el vehículo.'
                    });
                }
            })
            .catch(error => {
                // Manejar errores de red o del servidor
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
// 📌 FUNCIÓN PARA ACTUALIZAR FECHA/HORA EN TIEMPO REAL
// ============================================
function actualizarFechaHora() {
    // Buscar el campo donde se muestra la fecha
    const campoFecha = document.getElementById('FechaParqueaderoDisplay');
    
    // Si el campo existe en la página
    if (campoFecha) {
        // Obtener fecha y hora actual
        const ahora = new Date();
        
        // Opciones de formato para la fecha
        const opciones = { 
            year: 'numeric',      // Año completo (2025)
            month: 'long',        // Mes completo (noviembre)
            day: 'numeric',       // Día (27)
            hour: '2-digit',      // Hora con 2 dígitos (14)
            minute: '2-digit',    // Minutos con 2 dígitos (30)
            second: '2-digit',    // Segundos con 2 dígitos (45)
            hour12: false         // Formato 24 horas
        };
        
        // Formatear fecha según opciones y configuración regional de Colombia
        const fechaFormateada = ahora.toLocaleString('es-CO', opciones);
        
        // Actualizar el valor del campo con la fecha formateada
        campoFecha.value = fechaFormateada;
    }
}

// ========================================
// 📌 INICIAR ACTUALIZACIÓN AUTOMÁTICA DE FECHA
// ========================================
// Si existe el campo de fecha en la página
if (document.getElementById('FechaParqueaderoDisplay')) {
    // Actualizar cada 1000ms (1 segundo)
    setInterval(actualizarFechaHora, 1000);
    // Llamar inmediatamente para no esperar 1 segundo
    actualizarFechaHora();
}

// ============================================
// 📌 FUNCIONES GLOBALES PARA EDICIÓN
// ============================================

/**
 * Cargar datos en el modal de edición
 * @param {Object} row - Objeto con los datos del vehículo
 */
function cargarDatosEdicionVehiculo(row) {
    // Campos editables
    $('#editIdVehiculo').val(row.IdParqueadero);
    $('#editTipoVehiculo').val(row.TipoVehiculo);
    $('#editDescripcionVehiculo').val(row.DescripcionVehiculo);
    $('#editIdSede').val(row.IdSede);

    // Campos de solo lectura (disabled)
    $('#editPlacaVehiculoDisabled').val(row.PlacaVehiculo);
    $('#editTarjetaPropiedadDisabled').val(row.TarjetaPropiedad);

    // Formatear fecha para input datetime-local
    let fechaHora = row.FechaParqueadero;
    if (fechaHora) {
        // Convertir "2025-11-27 14:30:00" a "2025-11-27T14:30"
        fechaHora = fechaHora.replace(' ', 'T').substring(0, 16);
    }
    $('#editFechaParqueaderoDisabled').val(fechaHora);
}

/**
 * Confirmar eliminación de vehículo
 * @param {number} id - ID del vehículo a eliminar
 */
function confirmarEliminacionVehiculo(id) {
    // Guardar ID del vehículo a eliminar
    vehiculoIdAEliminar = id;
    // Mostrar modal de confirmación
    $('#confirmarEliminarModalVehiculo').modal('show');
}

// ============================================
// 📌 EVENTOS CON JQUERY (Edición y Eliminación)
// ============================================
$(document).ready(function() {

    // ========================================
    // 📌 EVENTO: Confirmar eliminación
    // ========================================
    $('#btnConfirmarEliminarVehiculo').click(function() {
        // Validar que hay un ID seleccionado
        if (!vehiculoIdAEliminar) return;

        console.log('Eliminando vehículo ID:', vehiculoIdAEliminar);

        // Mostrar loading
        Swal.fire({
            title: 'Eliminando...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Enviar petición AJAX para eliminar (cambiar estado a Inactivo)
        $.ajax({
            url: '../../Controller/ControladorParqueadero.php',
            type: 'POST',
            data: {
                accion: 'eliminar',  // Acción en el controlador
                id: vehiculoIdAEliminar
            },
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta eliminación:', response);
                
                // Cerrar modal de confirmación
                $('#confirmarEliminarModalVehiculo').modal('hide');
                
                // Verificar si la eliminación fue exitosa
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Eliminado!',
                        text: 'Vehículo eliminado correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Ocultar y eliminar la fila de la tabla con animación
                        $('#fila-' + vehiculoIdAEliminar).fadeOut(400, function() {
                            $(this).remove();
                        });
                    });
                } else {
                    // Mostrar error
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Error al eliminar el vehículo'
                    });
                }
            },
            error: function(xhr, status, error) {
                // Manejar errores de conexión
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

    // ========================================
    // 📌 EVENTO: Guardar cambios de edición
    // ========================================
    $('#btnGuardarCambiosVehiculo').click(function() {
        // Obtener valores de los campos del modal
        const id = $('#editIdVehiculo').val();
        const tipo = $('#editTipoVehiculo').val();
        const descripcion = $('#editDescripcionVehiculo').val();
        const idsede = $('#editIdSede').val();

        console.log('Actualizando - ID:', id, 'Tipo:', tipo, 'Descripción:', descripcion, 'Sede:', idsede);

        // ========================================
        // 📌 VALIDAR CAMPOS OBLIGATORIOS
        // ========================================
        if (!id || !tipo || !idsede) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Complete todos los campos obligatorios: Tipo de Vehículo e ID Sede'
            });
            return;
        }

        // Validar formato de descripción
        const regexDescripcion = /^[a-zA-Z0-9\s.,-]*$/;
        if (descripcion && !regexDescripcion.test(descripcion)) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La descripción contiene caracteres no válidos'
            });
            return;
        }

        // Cerrar modal de edición
        $('#modalEditarVehiculo').modal('hide');

        // Mostrar loading
        Swal.fire({
            title: 'Guardando...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // ========================================
        // 📌 ENVIAR DATOS DE ACTUALIZACIÓN
        // ========================================
        $.ajax({
            url: '../../Controller/ControladorParqueadero.php',
            type: 'POST',
            data: {
                accion: 'actualizar',  // Acción en el controlador
                id: id,
                tipo: tipo,
                descripcion: descripcion,
                idsede: idsede
            },
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta actualización:', response);
                
                // Verificar si la actualización fue exitosa
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'Vehículo actualizado correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Recargar la página para mostrar cambios
                        location.reload();
                    });
                } else {
                    // Mostrar error
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Error al actualizar el vehículo'
                    });
                }
            },
            error: function(xhr, status, error) {
                // Manejar errores de conexión
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

}); // Fin de $(document).ready()