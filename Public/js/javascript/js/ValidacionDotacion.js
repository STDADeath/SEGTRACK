$(document).ready(function () {
    // ==================== CONFIGURACIÓN INICIAL ====================
    // Establece fecha y hora mínima (inicio del día de hoy)
    const ahora = new Date();
    const año = ahora.getFullYear();
    const mes = String(ahora.getMonth() + 1).padStart(2, '0');
    const dia = String(ahora.getDate()).padStart(2, '0');
    const hora = String(ahora.getHours()).padStart(2, '0');
    const minutos = String(ahora.getMinutes()).padStart(2, '0');
    
    // Fecha mínima: inicio del día de hoy (00:00)
    const fechaMinimaDelDia = `${año}-${mes}-${dia}T00:00`;
    // Fecha actual con hora actual
    const fechaActualConHora = `${año}-${mes}-${dia}T${hora}:${minutos}`;
    
    // Configurar fechas mínimas
    $('#FechaEntrega').attr('min', fechaMinimaDelDia);
    $('#FechaEntrega').val(fechaActualConHora);
    $('#FechaDevolucion').attr('min', fechaMinimaDelDia);

    // ==================== VALIDACIONES EN TIEMPO REAL ====================
    
    // Validación de Fecha de Entrega
    $('#FechaEntrega').on('change', function() {
        const fechaSeleccionada = new Date($(this).val());
        const inicioDeHoy = new Date();
        inicioDeHoy.setHours(0, 0, 0, 0);
        
        if (fechaSeleccionada < inicioDeHoy) {
            alert('⚠️ La fecha de entrega no puede ser anterior al día de hoy');
            $(this).val(fechaActualConHora);
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
            // Actualizar fecha mínima de devolución según fecha de entrega
            $('#FechaDevolucion').attr('min', $(this).val());
        }
    });

    // Validación de Fecha de Devolución
    $('#FechaDevolucion').on('change', function() {
        const fechaDevolucion = new Date($(this).val());
        const fechaEntrega = new Date($('#FechaEntrega').val());
        const inicioDeHoy = new Date();
        inicioDeHoy.setHours(0, 0, 0, 0);
        
        if (fechaDevolucion < inicioDeHoy) {
            alert('⚠️ La fecha de devolución no puede ser anterior al día de hoy');
            $(this).val('');
            $(this).addClass('is-invalid');
        } else if (fechaDevolucion < fechaEntrega) {
            alert('⚠️ La fecha de devolución no puede ser anterior a la fecha de entrega');
            $(this).val('');
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Validación de campo numérico (ID Funcionario)
    $('#IdFuncionario').on('input', function() {
        const valor = $(this).val();
        // Remueve caracteres no numéricos
        $(this).val(valor.replace(/[^0-9]/g, ''));
        
        // Valida que sea mayor a 0
        if (valor && parseInt(valor) <= 0) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Validación de texto (Novedad) - mínimo 10 caracteres
    $('#NovedadDotacion').on('blur', function() {
        const texto = $(this).val().trim();
        
        if (texto.length > 0 && texto.length < 10) {
            alert('⚠️ La novedad debe tener al menos 10 caracteres');
            $(this).addClass('is-invalid');
            $(this).focus();
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Validación de selects
    $('#EstadoDotacion, #TipoDotacion').on('change', function() {
        if ($(this).val() === '' || $(this).val() === null) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // ==================== ENVÍO Y VALIDACIÓN DEL FORMULARIO ====================
    
    $("#formIngresarDotacion").submit(function (e) {
        e.preventDefault(); // Evita que la página recargue
        
        let errores = [];
        
        // Validar Estado de Dotación
        if ($('#EstadoDotacion').val() === '' || $('#EstadoDotacion').val() === null) {
            errores.push('Debe seleccionar el estado de la dotación');
            $('#EstadoDotacion').addClass('is-invalid');
        }
        
        // Validar Tipo de Dotación
        if ($('#TipoDotacion').val() === '' || $('#TipoDotacion').val() === null) {
            errores.push('Debe seleccionar el tipo de dotación');
            $('#TipoDotacion').addClass('is-invalid');
        }
        
        // Validar Novedad
        const novedad = $('#NovedadDotacion').val().trim();
        if (novedad.length < 10) {
            errores.push('La novedad debe tener al menos 10 caracteres');
            $('#NovedadDotacion').addClass('is-invalid');
        }
        
        // Validar Fecha de Entrega
        const fechaEntrega = new Date($('#FechaEntrega').val());
        const inicioDeHoy = new Date();
        inicioDeHoy.setHours(0, 0, 0, 0);
        
        if (!$('#FechaEntrega').val()) {
            errores.push('Debe seleccionar la fecha de entrega');
            $('#FechaEntrega').addClass('is-invalid');
        } else if (fechaEntrega < inicioDeHoy) {
            errores.push('La fecha de entrega no puede ser anterior al día de hoy');
            $('#FechaEntrega').addClass('is-invalid');
        }
        
        // Validar Fecha de Devolución (si existe)
        if ($('#FechaDevolucion').val()) {
            const fechaDevolucion = new Date($('#FechaDevolucion').val());
            
            if (fechaDevolucion < inicioDeHoy) {
                errores.push('La fecha de devolución no puede ser anterior al día de hoy');
                $('#FechaDevolucion').addClass('is-invalid');
            } else if (fechaDevolucion < fechaEntrega) {
                errores.push('La fecha de devolución no puede ser anterior a la fecha de entrega');
                $('#FechaDevolucion').addClass('is-invalid');
            }
        }
        
        // Validar ID Funcionario
        const idFuncionario = parseInt($('#IdFuncionario').val());
        if (!idFuncionario || idFuncionario <= 0) {
            errores.push('El ID del funcionario debe ser un número válido mayor a 0');
            $('#IdFuncionario').addClass('is-invalid');
        }
        
        // Si hay errores, mostrarlos y detener el envío
        if (errores.length > 0) {
            alert('⚠️ Por favor corrija los siguientes errores:\n\n• ' + errores.join('\n• '));
            return false;
        }

        // Si todo está bien, proceder con el envío
        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Procesando...');
        btn.prop('disabled', true);

        // Envía los datos al controlador usando AJAX
        $.ajax({
            url: "../../Controller/ControladorDotacion.php",
            type: "POST",
            data: $(this).serialize() + "&accion=registrar",
            dataType: "json",
            success: function (response) {
                console.log("Respuesta del servidor:", response);

                if (response.success) {
                    alert("✅ " + (response.message || "Dotación registrada con éxito"));

                    // Limpia el formulario después de registrar
                    $("#formIngresarDotacion")[0].reset();
                    
                    // Restablece las fechas
                    const ahoraActualizado = new Date();
                    const nuevoValor = `${ahoraActualizado.getFullYear()}-${String(ahoraActualizado.getMonth() + 1).padStart(2, '0')}-${String(ahoraActualizado.getDate()).padStart(2, '0')}T${String(ahoraActualizado.getHours()).padStart(2, '0')}:${String(ahoraActualizado.getMinutes()).padStart(2, '0')}`;
                    $('#FechaEntrega').val(nuevoValor);
                    
                    // Limpia clases de validación
                    $('.is-invalid').removeClass('is-invalid');
                } else {
                    let errorMsg = "❌ " + (response.message || "Error al registrar la dotación");
                    if (response.error) {
                        errorMsg += "\nDetalles: " + response.error;
                    }
                    alert(errorMsg);
                }
            },
            error: function (xhr, status, error) {
                console.error("Error AJAX:", error);
                console.log("Estado:", status);
                console.log("Respuesta completa del servidor:", xhr.responseText);

                let errorMessage = "Error de conexión con el servidor";
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response && response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    // Ignora errores de parseo
                }
                alert("❌ " + errorMessage);
            },
            complete: function () {
                // Restaura el botón
                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });
    });

});