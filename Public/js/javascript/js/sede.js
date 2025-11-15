$(document).ready(function () {

    $("#formRegistrarSede").submit(function (e) {
        e.preventDefault();

        $.ajax({
            url: "../Controller/Sede_institucion_funcionario_usuario/Controladorsede.php",
            type: "POST",
            // Se añaden los datos del formulario y la acción 'registrar'
            data: $(this).serialize() + "&accion=registrar", 
            dataType: "json", // Esperamos una respuesta JSON

            success: function (resp) {
                // Si el controlador responde con {"success": true, ...}
                if (resp.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Registro exitoso",
                        text: resp.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $("#formRegistrarSede")[0].reset();
                } else {
                    // Si el controlador responde con {"success": false, ...}
                    Swal.fire({
                        icon: "error",
                        title: "Error de Validación", // Título ajustado para errores de negocio
                        text: resp.message
                    });
                }
            },

            error: function (jqXHR, textStatus, errorThrown) {
                // ESTA FUNCIÓN SE EJECUTA CUANDO NO HAY CONEXIÓN O EL PHP DEVUELVE UN ERROR 500
                console.error("Error AJAX: ", textStatus, errorThrown, jqXHR.responseText);
                Swal.fire({
                    icon: "warning",
                    title: "Error del Servidor",
                    text: "No se pudo conectar con el servidor o hubo un error interno. Revisa la consola para más detalles."
                });
            }
        });

    });

});