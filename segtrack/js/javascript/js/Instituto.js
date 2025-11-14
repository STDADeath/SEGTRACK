$(document).ready(function () {

    // ===== VALIDACIÓN DE CAMPOS EN TIEMPO REAL =====

    function marcarInvalido(campo) {
        campo.css("border", "2px solid #ef4444"); // rojo
    }

    function marcarValido(campo) {
        campo.css("border", "2px solid #10b981"); // verde
    }

    // 1. Nombre: Solo acepta letras y elimina cualquier otro carácter.
    $("#NombreInstitucion").on("input", function () {
        let campo = $(this);
        let valor = campo.val();
        // Regex que solo permite letras (mayúsculas, minúsculas, tildes, ñ) y espacios.
        const soloLetrasRegex = /^[A-Za-zÁÉÍÓÚÑáéíóúñ ]+$/;
        
        // 🔥 CORRECCIÓN CLAVE: Eliminar caracteres no permitidos
        let valorLimpio = valor.replace(/[^A-Za-zÁÉÍÓÚÑáéíóúñ ]/g, ""); 
        campo.val(valorLimpio);

        if (soloLetrasRegex.test(valorLimpio) && valorLimpio.length > 0) {
            marcarValido(campo);
        } else {
            marcarInvalido(campo);
        }
    });

    // 2. NIT: Solo acepta 10 números, se pone verde solo al llegar a 10.
    $("#Nit_Codigo").on("input", function () {
        let valor = $(this).val().replace(/\D/g, "");
        $(this).val(valor.substring(0, 10));

        if (valor.length === 10) {
            marcarValido($(this)); // Se pone verde
        } else {
            marcarInvalido($(this)); // Se pone rojo
        }
    });

    // Selects (Tipo y Estado) - Se mantienen igual
    $("#TipoInstitucion, #EstadoInstitucion").on("change", function () {
        if ($(this).val() !== "") {
            marcarValido($(this));
        } else {
            marcarInvalido($(this));
        }
    });

    // ========= ENVÍO DEL FORMULARIO ==========
    $("#formInstituto").submit(function (e) {
        e.preventDefault();

        const nombre = $("#NombreInstitucion");
        const nit = $("#Nit_Codigo");
        const tipo = $("#TipoInstitucion");
        const estado = $("#EstadoInstitucion");

        let errores = [];

        // VALIDACIONES FINALES
        if (!/^[A-Za-zÁÉÍÓÚÑáéíóúñ ]+$/.test(nombre.val()) || nombre.val().trim() === "") {
            errores.push("El nombre solo puede contener letras y no puede estar vacío.");
            marcarInvalido(nombre);
        }

        if (nit.val().length !== 10) {
            errores.push("El NIT debe tener exactamente 10 números.");
            marcarInvalido(nit);
        }

        if (tipo.val() === "") {
            errores.push("Debe seleccionar un tipo de institución.");
            marcarInvalido(tipo);
        }

        if (estado.val() === "") {
            errores.push("Debe seleccionar el estado de la institución.");
            marcarInvalido(estado);
        }

        // Si hay errores, mostrar alerta SweetAlert2
        if (errores.length > 0) {
            Swal.fire({
                icon: "error",
                title: "Campos inválidos",
                html: errores.join("<br>"),
                confirmButtonColor: "#ef4444",
            });
            return;
        }

        // BOTÓN DE CARGA
        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
        btn.prop('disabled', true);

        // Enviar por AJAX
        $.ajax({
            url: $(this).attr('action'),
            type: "POST",
            data: $(this).serialize(),
            // Se sugiere usar JSON, si el backend lo permite
            // dataType: "json", 
            
            success: function (data) {
                console.log("Respuesta del servidor:", data);

                // Comprobación de éxito basada en texto (la que tenías)
                if (data.includes("✅") || data.includes("correctamente")) {
                    Swal.fire({
                        icon: "success",
                        title: "Registro exitoso",
                        // 🔥 CAMBIO: Eliminando el texto crudo 'data' de la alerta
                        text: 'La institución ha sido registrada correctamente.', 
                        confirmButtonColor: "#10b981"
                    });

                    $("#formInstituto")[0].reset();
                    // Restablece el borde a un color neutro
                    $("input, select").css("border", "2px solid #d1d3e2"); 
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        // Si no fue exitoso, muestra el mensaje de error del servidor
                        text: data, 
                        confirmButtonColor: "#ef4444"
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: "warning",
                    title: "Error de conexión",
                    text: "No se pudo contactar con el servidor",
                    confirmButtonColor: "#f59e0b"
                });
            },
            complete: function () {
                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });
    });
});