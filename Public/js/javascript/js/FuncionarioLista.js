
// ========================================
// FUNCIÓN PARA VER EL CÓDIGO QR
// ========================================
function verQR(rutaQR, idFuncionario) {
    const rutaCompleta = '../../../Public/' + rutaQR;
    $('#qrFuncionarioId').text(idFuncionario);
    $('#qrImagen').attr('src', rutaCompleta);
    $('#btnDescargarQR').attr('href', rutaCompleta);
    $('#modalVerQR').modal('show');
}

// ========================================
// FUNCIÓN PARA CARGAR DATOS EN EL MODAL DE EDICIÓN
// ========================================
function cargarDatosEdicion(id, cargo, nombre, sede, telefono, documento, correo) {
    $('#editId').val(id);
    $('#editCargo').val(cargo);
    $('#editNombre').val(nombre);
    // Sede es el IdSede: seteamos el select y forzamos .change() para que se aplique
    $('#editSede').val(sede).change();
    $('#editTelefono').val(telefono);
    $('#editDocumento').val(documento);
    $('#editCorreo').val(correo);
    // Abrir modal (por si se llama sin data-toggle)
    $('#modalEditar').modal('show');
}

// ========================================
// FUNCIÓN: CAMBIAR ESTADO DEL FUNCIONARIO
// ========================================
function cambiarEstado(idFuncionario, estadoActual) {
    const nuevoEstado = estadoActual === 'Activo' ? 'Inactivo' : 'Activo';

    if (!confirm(`¿Está seguro que desea cambiar el estado a "${nuevoEstado}"?`)) {
        return;
    }

    $.ajax({
        url: '../../Controller/ControladorFuncionarios.php',
        type: 'POST',
        data: {
            accion: 'cambiar_estado',
            id: idFuncionario,
            estado: nuevoEstado
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Estado cambiado correctamente');
                const badgeEstado = $('#badge-estado-' + idFuncionario);
                if (nuevoEstado === 'Activo') {
                    badgeEstado.removeClass('bg-danger').addClass('bg-success').text('Activo');
                } else {
                    badgeEstado.removeClass('bg-success').addClass('bg-danger').text('Inactivo');
                }
                const botonCambiar = badgeEstado.siblings('button');
                botonCambiar.attr('onclick', `cambiarEstado(${idFuncionario}, '${nuevoEstado}')`);
            } else {
                alert('Error: ' + (response.message || 'No se pudo cambiar el estado'));
            }
        },
        error: function(xhr, status, error) {
            alert('Error al cambiar el estado. Por favor, intente nuevamente.');
            console.error('Error AJAX:', error);
        }
    });
}

// ========================================
// EVENTO: GUARDAR CAMBIOS DE EDICIÓN CON VALIDACIÓN
// ========================================
$('#btnGuardarCambios').click(function () {

    const id = $('#editId').val();
    const cargo = $('#editCargo').val().trim();
    const nombre = $('#editNombre').val().trim();
    const sede = $('#editSede').val().trim();
    const telefono = $('#editTelefono').val().trim();
    const documento = $('#editDocumento').val().trim();
    const correo = $('#editCorreo').val().trim();

    if (!cargo) { alert('Por favor seleccione un cargo'); $('#editCargo').focus(); return; }
    if (!nombre) { alert('Por favor ingrese el nombre del funcionario'); $('#editNombre').focus(); return; }
    if (!sede) { alert('Por favor ingrese la sede'); $('#editSede').focus(); return; }
    if (!telefono) { alert('Por favor ingrese el teléfono'); $('#editTelefono').focus(); return; }
    if (!documento) { alert('Por favor ingrese el documento'); $('#editDocumento').focus(); return; }
    if (!correo) { alert('Por favor ingrese el correo electrónico'); $('#editCorreo').focus(); return; }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(correo)) { alert('Por favor ingrese un correo electrónico válido'); $('#editCorreo').focus(); return; }

    if (!confirm('¿Está seguro que desea actualizar los datos de este funcionario?')) { return; }

    const formData = {
        accion: "actualizar",
        id: id,
        cargo: cargo,
        nombre: nombre,
        sede: sede,
        telefono: telefono,
        documento: documento,
        correo: correo
    };

    const btnGuardar = $('#btnGuardarCambios');
    const textoOriginal = btnGuardar.html();
    btnGuardar.prop('disabled', true);
    btnGuardar.html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    $.ajax({
        url: '../../Controller/ControladorFuncionarios.php',
        type: 'POST',
        data: formData,
        dataType: 'json',

        success: function(response) {
            btnGuardar.prop('disabled', false);
            btnGuardar.html(textoOriginal);
            $('#modalEditar').modal('hide');

            if (response.success) {
                // Si la actualización fue exitosa, intentamos solicitar regenerar el QR
                // Si tu controlador implementó la acción 'actualizar_qr', esto regenerará el QR y devolverá la ruta.
                $.post('../../Controller/ControladorFuncionarios.php', { accion: 'actualizar_qr', id: id }, function(resQR) {
                    // resQR puede no existir si no implementaste la acción; por eso validamos.
                    if (resQR && resQR.success) {
                        // opcional: mostrar mensaje o actualizar vista del QR
                        console.log('QR regenerado:', resQR.ruta_qr);
                        alert('Funcionario actualizado correctamente y QR regenerado.');
                        location.reload();
                    } else {
                        // La actualización principal ya fue exitosa; QR no regenerado (o acción no disponible)
                        alert('Funcionario actualizado correctamente.');
                        location.reload();
                    }
                }, 'json').fail(function() {
                    // Si falla el request de actualizar_qr (acción no encontrada u otro error)
                    alert('Funcionario actualizado correctamente.');
                    location.reload();
                });

            } else {
                alert('Error al actualizar: ' + (response.message || 'Error desconocido'));
            }
        },

        error: function(xhr, status, error) {
            btnGuardar.prop('disabled', false);
            btnGuardar.html(textoOriginal);
            alert("Error al actualizar. Por favor, intente nuevamente.");
            console.error('Error AJAX:', error);
            console.error('Respuesta del servidor:', xhr.responseText);
        }
    });
});