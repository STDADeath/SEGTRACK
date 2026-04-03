// ========================================
// LISTA DE FUNCIONARIOS - SEGTRACK FINAL
// ========================================

console.log("✅ FuncionarioLista OK");

const urlControlador = "../../Controller/ControladorFuncionarios.php";

$(document).ready(function () {

    // ========================================
    // DATATABLE
    // ========================================
    $('#tablaFuncionarios').DataTable({
        pageLength: 10,
        order: [[2, "asc"]],
        columnDefs: [{ targets: [0, 9], orderable: false }]
    });

    // ========================================
    // 🔥 CASCADA FILTRO INSTITUTO → SEDE
    // ========================================
    $('select[name="instituto"]').on('change', function () {

        const idInst = parseInt($(this).val()) || 0;
        const $sede = $('select[name="sede"]');

        $sede.empty().append('<option value="">Todas</option>');

        if (idInst === 0) {
            $sede.prop('disabled', true);
            return;
        }

        const sedes = SEDES_POR_INSTITUCION_LISTA[idInst] || [];

        if (sedes.length === 0) {
            $sede.append('<option disabled>Sin sedes</option>');
            $sede.prop('disabled', true);
        } else {
            sedes.forEach(s => {
                $sede.append(`<option value="${s.IdSede}">${s.NombreSede}</option>`);
            });
            $sede.prop('disabled', false);
        }
    });

    // ========================================
    // 🔥 RESTAURAR FILTROS (GET)
    // ========================================
    const institutoActual = $('select[name="instituto"]').val();

    if (institutoActual && parseInt(institutoActual) > 0) {

        $('select[name="instituto"]').trigger('change');

        const sedeActual = $('select[name="sede"]').data('selected');

        if (sedeActual) {
            setTimeout(() => {
                $('select[name="sede"]').val(sedeActual);
            }, 200);
        }
    }

    // ========================================
    // VALIDACIONES
    // ========================================
    $('#editTelefono').on('input', function () {
        this.value = this.value.replace(/\D/g, '');
    });

    $('#editNombre').on('input', function () {
        this.value = this.value.replace(/[^a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]/g, '');
    });

    $('#modalEditar').on('show.bs.modal', function () {
        limpiarValidaciones();
    });

});


// ========================================
// 🔥 MODAL EDICIÓN
// ========================================
function cargarDatosEdicion(id, cargo, nombre, idSede, idInstitucion, nombreInst, telefono, documento, correo) {

    $('#editId').val(id);
    $('#editCargo').val(cargo);
    $('#editNombre').val(nombre);
    $('#editTelefono').val(telefono);
    $('#editDocumento').val(documento);
    $('#editCorreo').val(correo);

    $('#editInstitucionTexto').val(nombreInst);
    $('#editIdInstitucion').val(idInstitucion);

    const select = $('#editSede');
    select.empty();

    const sedes = SEDES_POR_INSTITUCION_LISTA[idInstitucion] || [];

    if (sedes.length === 0) {
        select.append('<option>Sin sedes</option>');
    } else {

        select.append('<option value="">Seleccione sede</option>');

        sedes.forEach(s => {

            const selected = (parseInt(s.IdSede) === parseInt(idSede)) ? 'selected' : '';

            select.append(`
                <option value="${s.IdSede}" ${selected}>
                    ${s.NombreSede}
                </option>
            `);
        });
    }

    $('#modalEditar').modal('show');
}


// ========================================
// 🔥 GUARDAR CAMBIOS
// ========================================
$('#btnGuardarCambios').on('click', function () {

    const id        = $('#editId').val();
    const cargo     = $('#editCargo').val().trim();
    const nombre    = $('#editNombre').val().trim();
    const sede      = $('#editSede').val();
    const telefono  = $('#editTelefono').val().trim();
    const documento = $('#editDocumento').val();
    const correo    = $('#editCorreo').val();

    if (!cargo || !nombre || !sede || !telefono) {
        Swal.fire('Error', 'Completa todos los campos', 'error');
        return;
    }

    $.ajax({
        url: urlControlador,
        type: 'POST',
        dataType: 'json',
        data: {
            accion: 'actualizar',
            IdFuncionario: id,
            CargoFuncionario: cargo,
            NombreFuncionario: nombre,
            IdSede: sede,
            TelefonoFuncionario: telefono,
            DocumentoFuncionario: documento,
            CorreoFuncionario: correo
        },
        success: function (res) {
            if (res.success) {
                Swal.fire('Correcto', res.message, 'success')
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'Fallo en servidor', 'error');
        }
    });
});


// ========================================
// UTILIDADES
// ========================================
function limpiarValidaciones() {
    $('#formEditar .form-control')
        .removeClass('is-invalid is-valid');
}


// ========================================
// QR
// ========================================
function verQR(rutaQR, idFuncionario) {

    if (!rutaQR || rutaQR === 'NULL') {
        Swal.fire('Error', 'No tiene QR', 'error');
        return;
    }

    rutaQR = rutaQR.replace(/^\/?Public\//i, '').replace(/^\/+/, '');

    const base = window.location.origin + '/SEGTRACK';

    const rutaCompleta = base + '/Public/' + rutaQR;

    $('#qrImagen')
        .attr('src', rutaCompleta + '?t=' + new Date().getTime());

    $('#qrFuncionarioId').text(idFuncionario);
    $('#btnDescargarQR').attr('href', rutaCompleta);

    $('#modalVerQR').modal('show');
}


// ========================================
// ENVIAR QR
// ========================================
function enviarQR(idFuncionario) {

    Swal.fire({
        title: '¿Enviar QR?',
        icon: 'question',
        showCancelButton: true
    }).then((r) => {

        if (!r.isConfirmed) return;

        $.ajax({
            url: urlControlador,
            type: 'POST',
            dataType: 'json',
            data: {
                accion: 'enviar_qr',
                IdFuncionario: idFuncionario
            },
            success: function (res) {
                Swal.fire(res.success ? 'OK' : 'Error', res.message, res.success ? 'success' : 'error');
            }
        });
    });
}


// ========================================
// CAMBIAR ESTADO
// ========================================
function cambiarEstado(idFuncionario, estadoActual) {

    const nuevoEstado = estadoActual === 'Activo' ? 'Inactivo' : 'Activo';

    Swal.fire({
        title: '¿Cambiar estado?',
        text: `Pasará a ${nuevoEstado}`,
        icon: 'warning',
        showCancelButton: true
    }).then((r) => {

        if (!r.isConfirmed) return;

        $.ajax({
            url: urlControlador,
            type: 'POST',
            dataType: 'json',
            data: {
                accion: 'cambiar_estado',
                IdFuncionario: idFuncionario,
                Estado: nuevoEstado
            },
            success: function (res) {
                if (res.success) {
                    Swal.fire('OK', res.message, 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }
        });
    });
}