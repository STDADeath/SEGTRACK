$(function () {
    // ==================== CONFIGURACIÓN INICIAL ====================
    // Establece fecha y hora mínima (inicio del día de hoy) para el campo FechaBitacora
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
    
    $('#FechaBitacora').attr('min', fechaMinimaDelDia);
    $('#FechaBitacora').val(fechaActualConHora); // Establece la fecha y hora actual como valor por defecto

    // ==================== VALIDACIONES EN TIEMPO REAL ====================
    
    // Validación del campo de fecha
    $('#FechaBitacora').on('change', function() {
        const fechaSeleccionada = new Date($(this).val());
        const inicioDeHoy = new Date();
        inicioDeHoy.setHours(0, 0, 0, 0); // Establece a las 00:00:00 de hoy
        
        if (fechaSeleccionada < inicioDeHoy) {
            alert('⚠️ No puede seleccionar una fecha anterior al día de hoy');
            $(this).val(fechaActualConHora);
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Validación de campos numéricos (solo números positivos)
    $('#IdFuncionario, #IdIngreso, #IdVisitante, #IdDispositivo').on('input', function() {
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

    // Validación de texto (Novedades) - mínimo 10 caracteres
    $('#NovedadesBitacora').on('blur', function() {
        const texto = $(this).val().trim();
        
        if (texto.length > 0 && texto.length < 10) {
            alert('⚠️ Las novedades deben tener al menos 10 caracteres');
            $(this).addClass('is-invalid');
            $(this).focus();
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Validación de selects (que no queden en la opción por defecto)
    $('#TurnoBitacora, #TieneVisitante').on('change', function() {
        if ($(this).val() === '' || $(this).val() === null) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // ==================== LÓGICA DE CAMPOS CONDICIONALES ====================
    
    // Manejo de campo "Tiene Visitante"
    $("#TieneVisitante").change(function () {
        const tieneVisitante = $(this).val() === "si";
        $("#VisitanteContainer").toggle(tieneVisitante);
        
        // Limpiar y ajustar campos de visitante
        if (!tieneVisitante) {
            $('#IdVisitante').val('').removeAttr('required').removeClass('is-invalid');
            $('#TraeDispositivo').val('').removeAttr('required');
            $("#DispositivoContainer").hide();
            $('#IdDispositivo').val('').removeAttr('required').removeClass('is-invalid');
        } else {
            $('#IdVisitante').attr('required', 'required');
            $('#TraeDispositivo').attr('required', 'required');
        }
    });

    // Manejo de campo "Trae Dispositivo"
    $("#TraeDispositivo").change(function () {
        const traeDispositivo = $(this).val() === "si";
        $("#DispositivoContainer").toggle(traeDispositivo);
        
        // Limpiar y ajustar campo de dispositivo
        if (!traeDispositivo) {
            $('#IdDispositivo').val('').removeAttr('required').removeClass('is-invalid');
        } else {
            $('#IdDispositivo').attr('required', 'required');
        }
    });

    // ==================== ENVÍO Y VALIDACIÓN DEL FORMULARIO ====================
    
    $("#formRegistrarBitacora").submit(function (e) {
        e.preventDefault(); // Evita que la página recargue
        
        let errores = [];
        
        // Validar Turno
        if ($('#TurnoBitacora').val() === '' || $('#TurnoBitacora').val() === null) {
            errores.push('Debe seleccionar un turno');
            $('#TurnoBitacora').addClass('is-invalid');
        }
        
        // Validar Fecha
        const fechaSeleccionada = new Date($('#FechaBitacora').val());
        const inicioDeHoy = new Date();
        inicioDeHoy.setHours(0, 0, 0, 0); // Establece a las 00:00:00 de hoy
        
        if (fechaSeleccionada < inicioDeHoy) {
            errores.push('La fecha no puede ser anterior al día de hoy');
            $('#FechaBitacora').addClass('is-invalid');
        }
        
        // Validar Novedades
        const novedades = $('#NovedadesBitacora').val().trim();
        if (novedades.length < 10) {
            errores.push('Las novedades deben tener al menos 10 caracteres');
            $('#NovedadesBitacora').addClass('is-invalid');
        }
        
        // Validar ID Funcionario
        const idFuncionario = parseInt($('#IdFuncionario').val());
        if (!idFuncionario || idFuncionario <= 0) {
            errores.push('El ID del funcionario debe ser un número válido mayor a 0');
            $('#IdFuncionario').addClass('is-invalid');
        }
        
        // Validar ID Ingreso
        const idIngreso = parseInt($('#IdIngreso').val());
        if (!idIngreso || idIngreso <= 0) {
            errores.push('El ID de ingreso debe ser un número válido mayor a 0');
            $('#IdIngreso').addClass('is-invalid');
        }
        
        // Validar Visitante
        if ($('#TieneVisitante').val() === '' || $('#TieneVisitante').val() === null) {
            errores.push('Debe indicar si hay visitante');
            $('#TieneVisitante').addClass('is-invalid');
        }
        
        // Si hay visitante, validar campos relacionados
        if ($('#TieneVisitante').val() === 'si') {
            const idVisitante = parseInt($('#IdVisitante').val());
            if (!idVisitante || idVisitante <= 0) {
                errores.push('El ID del visitante debe ser un número válido mayor a 0');
                $('#IdVisitante').addClass('is-invalid');
            }
            
            if ($('#TraeDispositivo').val() === '' || $('#TraeDispositivo').val() === null) {
                errores.push('Debe indicar si el visitante trae dispositivo');
                $('#TraeDispositivo').addClass('is-invalid');
            }
            
            // Si trae dispositivo, validar ID
            if ($('#TraeDispositivo').val() === 'si') {
                const idDispositivo = parseInt($('#IdDispositivo').val());
                if (!idDispositivo || idDispositivo <= 0) {
                    errores.push('El ID del dispositivo debe ser un número válido mayor a 0');
                    $('#IdDispositivo').addClass('is-invalid');
                }
            }
        }
        
        // Si hay errores, mostrarlos y detener el envío
        if (errores.length > 0) {
            alert('⚠️ Por favor corrija los siguientes errores:\n\n• ' + errores.join('\n• '));
            return false;
        }
        
        // Si todo está bien, proceder con el envío
        const btn = $(this).find('button[type=submit]');
        const original = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i> Procesando...')
           .prop("disabled", true);

        // Envío de datos al controlador usando POST
        $.post(
            "../../Controller/ControladorBitacora.php",
            $(this).serialize() + "&accion=registrar", // Datos del formulario
            function (res) { // Respuesta del servidor
                console.log("Respuesta:", res);

                if (res.success) {
                    alert("✅ Bitácora registrada con éxito");

                    // Limpia el formulario y oculta secciones
                    $("#formRegistrarBitacora")[0].reset();
                    $("#VisitanteContainer, #DispositivoContainer").hide();
                    
                    // Restablece la fecha al valor actual
                    const ahoraActualizado = new Date();
                    const nuevoValor = `${ahoraActualizado.getFullYear()}-${String(ahoraActualizado.getMonth() + 1).padStart(2, '0')}-${String(ahoraActualizado.getDate()).padStart(2, '0')}T${String(ahoraActualizado.getHours()).padStart(2, '0')}:${String(ahoraActualizado.getMinutes()).padStart(2, '0')}`;
                    $('#FechaBitacora').val(nuevoValor);
                    
                    // Limpia clases de validación
                    $('.is-invalid').removeClass('is-invalid');
                } else {
                    // Mensaje de error recibido desde PHP
                    alert("❌ " + (res.message || "Error al registrar"));
                }
            },
            "json" // Indica que la respuesta debe ser JSON
        )
        .fail(() => alert("❌ Error de conexión con el servidor")) // Error de red
        .always(() => {
            // Se restaura el botón sin importar si falló o funcionó
            btn.html(original).prop("disabled", false);
        });
    });

});