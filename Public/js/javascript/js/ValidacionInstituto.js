$(document).ready(function () {
    console.log('=== SISTEMA DE REGISTRO/EDICIÓN DE INSTITUTO INICIADO ===');
    
    // ===== FUNCIONES DE VALIDACIÓN VISUAL INLINE =====
    
    function marcarInvalido(campo) {
        campo.css("border", "2px solid #ef4444");
        campo.css("box-shadow", "0 0 0 0.25rem rgba(239, 68, 68, 0.25)");
    }

    function marcarValido(campo) {
        campo.css("border", "2px solid #10b981");
        campo.css("box-shadow", "0 0 0 0.25rem rgba(16, 185, 129, 0.25)");
    }

    function marcarNeutral(campo) {
        campo.css("border", ""); 
        campo.css("box-shadow", "");
    }
    
    // ===== DETECTAR MODO EDICIÓN =====
    var modoEdicion = $('input[name="IdInstitucion"]').length > 0;
    
    // ===== FUNCIÓN DE INICIALIZACIÓN VISUAL =====
    // Fuerza a que los campos de selección inicien neutrales.
    function inicializarValidacion() {
        marcarNeutral($("#TipoInstitucion"));
        marcarNeutral($("#EstadoInstitucion"));
    }

    // Ejecutar la inicialización al cargar la página
    inicializarValidacion();

    // ===== SI ESTAMOS EN MODO EDICIÓN, MARCAR CAMPOS EN ROJO AL INICIO =====
    if (modoEdicion) {
        console.log('=== MODO EDICIÓN DETECTADO ===');
        setTimeout(function() {
            marcarInvalido($("#NombreInstitucion"));
            marcarInvalido($("#Nit_Codigo"));
        }, 100);
    }


    // ===== VALIDACIÓN EN TIEMPO REAL (VERDE/ROJO INMEDIATO) =====

    // 1. NOMBRE DE INSTITUCIÓN
    $("#NombreInstitucion").on("input", function () {
        let campo = $(this);
        let valor = campo.val();
        
        // La validación en tiempo real para campos de texto es correcta (inician neutral)
        let valorLimpio = valor.replace(/[^A-Za-zÁÉÍÓÚÑáéíóúñ ]/g, "");
        campo.val(valorLimpio);

        if (valorLimpio.length >= 3 && /^[A-Za-zÁÉÍÓÚÑáéíóúñ ]+$/.test(valorLimpio)) {
            marcarValido(campo);
        } else {
            marcarInvalido(campo);
        }
    });

    // 2. NIT/CÓDIGO
    $("#Nit_Codigo").on("input", function () {
        let campo = $(this);
        let valor = campo.val();
        
        let valorLimpio = valor.replace(/\D/g, "");
        valorLimpio = valorLimpio.substring(0, 10);
        campo.val(valorLimpio);

        if (valorLimpio.length === 10) {
            marcarValido(campo);
        } else {
            marcarInvalido(campo);
        }
    });

    // 3. TIPO DE INSTITUCIÓN
    $("#TipoInstitucion").on("change", function () {
        let campo = $(this);
        if (campo.val() !== "") {
            marcarValido(campo);
        } else {
            marcarInvalido(campo);
        }
    });

    // 4. ESTADO
    $("#EstadoInstitucion").on("change", function () {
        let campo = $(this);
        if (campo.val() !== "") {
            marcarValido(campo);
        } else {
            marcarInvalido(campo);
        }
    });

    
    // ===== FUNCIÓN DE ENVÍO DE REGISTRO/EDICIÓN (AJAX) =====
    $("#formInstituto").submit(function (e) {
        e.preventDefault();

        const nombre = $("#NombreInstitucion");
        const nit = $("#Nit_Codigo");
        const tipo = $("#TipoInstitucion");

        let errores = [];

        // Forzamos las validaciones finales
        if (nombre.val().length < 3 || !/^[A-Za-zÁÉÍÓÚÑáéíóúñ ]+$/.test(nombre.val())) {
            errores.push("• El nombre debe contener solo letras (mínimo 3 caracteres).");
            marcarInvalido(nombre);
        } else {
            marcarValido(nombre);
        }

        if (nit.val().length !== 10) {
            errores.push("• El NIT debe tener exactamente 10 números.");
            marcarInvalido(nit);
        } else {
            marcarValido(nit);
        }

        if (tipo.val() === "") {
            errores.push("• Debe seleccionar un tipo de institución.");
            marcarInvalido(tipo);
        } else {
            marcarValido(tipo);
        }

        // Estado (aunque tiene valor por defecto, lo validamos)
        if ($("#EstadoInstitucion").val() === "") {
            errores.push("• Debe seleccionar un estado.");
            marcarInvalido($("#EstadoInstitucion"));
        } else {
            marcarValido($("#EstadoInstitucion"));
        }


        if (errores.length > 0) {
            Swal.fire({
                icon: "error",
                title: "Error de validación",
                html: "<div style='text-align: left;'>" + errores.join("<br>") + "</div>",
                confirmButtonText: "OK",
                confirmButtonColor: "#ef4444",
            });
            return;
        }
        
        // --- PROCESO AJAX ---
        
        // Detectar si es edición o registro
        var esEdicion = $('input[name="IdInstitucion"]').length > 0;
        var tituloAccion = esEdicion ? 'Actualizando institución...' : 'Registrando institución...';
        var tituloExito = esEdicion ? '¡Actualización Exitosa!' : '¡Registro Exitoso!';
        
        Swal.fire({ 
            title: tituloAccion,
            html: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        btn.prop('disabled', true); 

        $.ajax({
            url: '../../Controller/Controladorinstituto.php', 
            type: "POST",
            data: $(this).serialize(),
            dataType: 'json', 
            
            success: function (response) {
                Swal.close(); 
                if (response.ok === true) {
                    Swal.fire({
                        icon: "success",
                        title: tituloExito,
                        text: response.message, 
                        confirmButtonText: "OK",
                        confirmButtonColor: "#10b981"
                    }).then(() => {
                        if (esEdicion) {
                            // Si es edición, redirigir a la lista
                            window.location.href = 'InstitutoLista.php';
                        } else {
                            // Si es registro, limpiar formulario
                            $("#formInstituto")[0].reset();
                            // Limpiar estilos después del éxito
                            inicializarValidacion(); // Vuelve a dejarlos neutrales
                        }
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error en " + (esEdicion ? "la Actualización" : "el Registro"),
                        text: response.message || 'Ocurrió un error inesperado al guardar.', 
                        confirmButtonText: "OK",
                        confirmButtonColor: "#ef4444"
                    });
                }
            },
            error: function (xhr) {
                Swal.close(); 
                
                // Limpiar estilos en caso de error (solo en registro)
                if (!esEdicion) {
                    inicializarValidacion(); // Vuelve a dejarlos neutrales
                }

                let mensaje = `Error de conexión con el servidor. Revisar logs de PHP.`;
                let responseMessage = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : xhr.responseText;
                
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    html: `<p>${mensaje}</p><p>Detalle: ${responseMessage.substring(0, 100)}...</p><small>Código: ${xhr.status}</small>`,
                    confirmButtonText: "OK",
                    confirmButtonColor: "#ef4444"
                });
            },
            complete: function () {
                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });
    });
});