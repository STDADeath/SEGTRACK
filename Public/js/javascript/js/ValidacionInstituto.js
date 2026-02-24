$(document).ready(function () {
    console.log('=== SISTEMA DE REGISTRO/EDICIÓN DE INSTITUTO INICIADO ===');

    // ===== FUNCIONES DE VALIDACIÓN VISUAL =====

    // Borde ROJO → campo con error de validación
    function marcarInvalido(campo) {
        campo.css("border", "2px solid #ef4444");
        campo.css("box-shadow", "0 0 0 0.25rem rgba(239, 68, 68, 0.25)");
    }

    // Borde VERDE → campo válido
    function marcarValido(campo) {
        campo.css("border", "2px solid #10b981");
        campo.css("box-shadow", "0 0 0 0.25rem rgba(16, 185, 129, 0.25)");
    }

    // Sin borde → estado neutro inicial
    function marcarNeutral(campo) {
        campo.css("border", "");
        campo.css("box-shadow", "");
    }

    // ===== DETECTAR MODO EDICIÓN =====
    // Existe input hidden con name="IdInstitucion" solo en edición
    var modoEdicion = $('input[name="IdInstitucion"]').length > 0;

    // ===== INICIALIZACIÓN VISUAL =====
    // Solo el select de Tipo inicia neutro (se eliminó el select de Estado)
    function inicializarValidacion() {
        marcarNeutral($("#TipoInstitucion"));
        marcarNeutral($("#DireccionInstitucion")); // Dirección siempre neutral al inicio
    }

    inicializarValidacion();

    // En edición los campos de texto inician en rojo para forzar revisión
    if (modoEdicion) {
        console.log('=== MODO EDICIÓN DETECTADO ===');
        setTimeout(function () {
            marcarInvalido($("#NombreInstitucion"));
            marcarInvalido($("#Nit_Codigo"));
            // Dirección NO se marca en rojo: es opcional
        }, 100);
    }

    // ===== VALIDACIÓN EN TIEMPO REAL =====

    // 1. NOMBRE: solo letras (incluye tildes y ñ) y espacios, mínimo 3 caracteres
    $("#NombreInstitucion").on("input", function () {
        let campo = $(this);
        // Elimina automáticamente cualquier carácter NO permitido
        let valorLimpio = campo.val().replace(/[^A-Za-zÁÉÍÓÚÑáéíóúñ ]/g, "");
        campo.val(valorLimpio);

        if (valorLimpio.length >= 3 && /^[A-Za-zÁÉÍÓÚÑáéíóúñ ]+$/.test(valorLimpio)) {
            marcarValido(campo);
        } else {
            marcarInvalido(campo);
        }
    });

    // 2. NIT: solo dígitos, exactamente 10 caracteres
    $("#Nit_Codigo").on("input", function () {
        let campo = $(this);
        let valorLimpio = campo.val().replace(/\D/g, "").substring(0, 10); // Solo dígitos, máx 10
        campo.val(valorLimpio);

        if (valorLimpio.length === 10) {
            marcarValido(campo);
        } else {
            marcarInvalido(campo);
        }
    });

    // 3. TIPO: válido si el usuario seleccionó algo distinto al placeholder
    $("#TipoInstitucion").on("change", function () {
        let campo = $(this);
        campo.val() !== "" ? marcarValido(campo) : marcarInvalido(campo);
    });

    // 4. DIRECCIÓN (opcional)
    // Permite: letras, números, espacios, guiones, # y comas
    // Bloquea: @, ., /, \, !, ?, % y cualquier otro símbolo
    $("#DireccionInstitucion").on("input", function () {
        let campo = $(this);
        // Elimina cualquier carácter no válido para una dirección física
        let valorLimpio = campo.val().replace(/[^A-Za-zÁÉÍÓÚÑáéíóúñ0-9 \-#,]/g, "");
        campo.val(valorLimpio);

        if (valorLimpio.length === 0) {
            marcarNeutral(campo);        // Vacío → neutro (es opcional, no es error)
        } else if (valorLimpio.length >= 5) {
            marcarValido(campo);         // 5+ caracteres → verde
        } else {
            marcarInvalido(campo);       // Muy poco contenido → rojo
        }
    });

    // ===== ENVÍO DEL FORMULARIO (AJAX con SweetAlert2) =====
    $("#formInstituto").submit(function (e) {
        e.preventDefault(); // Evita recarga de página

        const nombre    = $("#NombreInstitucion");
        const nit       = $("#Nit_Codigo");
        const tipo      = $("#TipoInstitucion");
        const direccion = $("#DireccionInstitucion");

        let errores = []; // Acumula mensajes de error

        // Validación nombre
        if (nombre.val().length < 3 || !/^[A-Za-zÁÉÍÓÚÑáéíóúñ ]+$/.test(nombre.val())) {
            errores.push("• El nombre debe contener solo letras (mínimo 3 caracteres).");
            marcarInvalido(nombre);
        } else {
            marcarValido(nombre);
        }

        // Validación NIT
        if (nit.val().length !== 10) {
            errores.push("• El NIT debe tener exactamente 10 números.");
            marcarInvalido(nit);
        } else {
            marcarValido(nit);
        }

        // Validación Tipo
        if (tipo.val() === "") {
            errores.push("• Debe seleccionar un tipo de institución.");
            marcarInvalido(tipo);
        } else {
            marcarValido(tipo);
        }

        // Validación Dirección (solo si escribió algo)
        let valorDir = direccion.val().trim();
        if (valorDir.length > 0) {
            if (valorDir.length < 5 || !/^[A-Za-zÁÉÍÓÚÑáéíóúñ0-9 \-#,]+$/.test(valorDir)) {
                errores.push("• La dirección solo permite letras, números, guiones, # y comas (mínimo 5 caracteres).");
                marcarInvalido(direccion);
            } else {
                marcarValido(direccion);
            }
        } else {
            marcarNeutral(direccion); // Vacío es aceptado
        }

        // Si hay errores de validación → SweetAlert2 de error
        if (errores.length > 0) {
            Swal.fire({
                icon: "error",
                title: "Campos incorrectos",
                html: "<div style='text-align:left'>" + errores.join("<br>") + "</div>",
                confirmButtonText: "Corregir",
                confirmButtonColor: "#ef4444"
            });
            return; // Detiene el envío
        }

        // ─── PROCESO AJAX ───
        var esEdicion    = $('input[name="IdInstitucion"]').length > 0;
        var tituloAccion = esEdicion ? 'Actualizando institución...' : 'Registrando institución...';
        var tituloExito  = esEdicion ? '¡Institución Actualizada!'  : '¡Institución Registrada!';

        // SweetAlert2 de carga mientras procesa la petición
        Swal.fire({
            title: tituloAccion,
            html: 'Por favor espere...',
            allowOutsideClick: false, // No se puede cerrar haciendo clic afuera
            didOpen: () => Swal.showLoading()
        });

        // Deshabilita el botón para evitar doble envío
        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        btn.prop('disabled', true);

        $.ajax({
            url: '../../Controller/Controladorinstituto.php',
            type: "POST",
            data: $(this).serialize(), // Serializa TODOS los inputs incluyendo DireccionInstitucion
            dataType: 'json',

            success: function (response) {
                Swal.close();

                if (response.ok === true) {
                    // Éxito: SweetAlert2 verde
                    Swal.fire({
                        icon: "success",
                        title: tituloExito,
                        text: response.message,
                        confirmButtonText: "OK",
                        confirmButtonColor: "#10b981"
                    }).then(() => {
                        if (esEdicion) {
                            window.location.href = 'InstitutoLista.php'; // Redirige tras editar
                        } else {
                            $("#formInstituto")[0].reset(); // Limpia formulario tras registrar
                            inicializarValidacion();        // Resetea estilos
                        }
                    });
                } else {
                    // El servidor rechazó: NIT duplicado u otro error de negocio
                    // SweetAlert2 de advertencia para datos repetidos
                    Swal.fire({
                        icon: "warning",
                        title: "No se pudo guardar",
                        text: response.message || 'Ocurrió un error inesperado.',
                        confirmButtonText: "Entendido",
                        confirmButtonColor: "#f59e0b"
                    });
                }
            },
            error: function (xhr) {
                Swal.close();
                if (!esEdicion) inicializarValidacion();

                // SweetAlert2 de error de conexión
                let responseMessage = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : xhr.responseText;

                Swal.fire({
                    icon: "error",
                    title: "Error de conexión",
                    html: `<p>No se pudo conectar con el servidor.</p>
                           <p><small>Detalle: ${responseMessage.substring(0, 120)}</small></p>
                           <small>Código HTTP: ${xhr.status}</small>`,
                    confirmButtonText: "OK",
                    confirmButtonColor: "#ef4444"
                });
            },
            complete: function () {
                // Restaura el botón siempre (éxito o error)
                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });
    });
});