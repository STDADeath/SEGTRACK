// ========================================
// LISTA DE INSTITUCIONES - SEGTRACK
// Compatible con Bootstrap 4 (SB Admin 2)
// ========================================

console.log("✅ ValidacionInstitutoLista.js cargado correctamente");

const urlControladorInstituto = "../../Controller/Controladorinstituto.php";

$(document).ready(function () {

    // =====================================================
    // 1️⃣ INICIALIZAR DATATABLE
    // =====================================================
    if ($.fn.DataTable.isDataTable('#tablaInstitutos')) {
        $('#tablaInstitutos').DataTable().destroy();
    }

    $('#tablaInstitutos').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        order: [[0, 'desc']],
        columnDefs: [{ targets: 4, orderable: false }],
        responsive: true
    });

    // =====================================================
    // 2️⃣ GUARDAR CAMBIOS (EDITAR)
    // =====================================================
    $(document).on('click', '#btnGuardarInstituto', function () {

        const id     = $('#editIdInstituto').val();
        const nombre = $('#editNombreInstituto').val().trim();
        const tipo   = $('#editTipoInstituto').val();
        const nit    = $('#editNitInstituto').val().trim();

        if (!nombre || !tipo) {
            Swal.fire('Error', 'Nombre y Tipo son obligatorios', 'error');
            return;
        }

        Swal.fire({
            title: '¿Actualizar institución?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {

            if (!result.isConfirmed) return;

            const btn = $('#btnGuardarInstituto');
            const textoOriginal = btn.html();

            btn.prop('disabled', true);
            btn.html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

            $.ajax({
                url: urlControladorInstituto,
                type: 'POST',
                dataType: 'json',
                data: {
                    accion: 'editar',
                    IdInstitucion: id,
                    NombreInstitucion: nombre,
                    TipoInstitucion: tipo,
                    Nit_Codigo: nit,
                    EstadoInstitucion: 'Activo'
                },
                success: function (response) {

                    btn.prop('disabled', false);
                    btn.html(textoOriginal);

                    if (response.ok) {

                        $('#modalEditarInstituto').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Actualizado',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function () {
                            location.reload();
                        });

                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function (xhr) {
                    btn.prop('disabled', false);
                    btn.html(textoOriginal);
                    console.error("Error servidor:", xhr.responseText);
                    Swal.fire('Error', 'No se pudo conectar al servidor', 'error');
                }
            });

        });
    });

    // =====================================================
    // 3️⃣ CAMBIAR ESTADO
    // =====================================================
    $(document).on('click', '.btn-toggle-estado', function () {

        const btn = $(this);
        const id = btn.data('id');
        const estadoActual = btn.data('estado');
        const nuevoEstado = (estadoActual === 'Activo') ? 'Inactivo' : 'Activo';

        Swal.fire({
            title: '¿Cambiar estado?',
            text: 'La institución pasará a ' + nuevoEstado,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {

            if (!result.isConfirmed) return;

            btn.prop('disabled', true);
            btn.find('i').removeClass().addClass('fas fa-spinner fa-spin');

            $.ajax({
                url: urlControladorInstituto,
                type: 'POST',
                dataType: 'json',
                data: {
                    accion: 'cambiarEstado',
                    IdInstitucion: id,
                    EstadoInstitucion: nuevoEstado
                },
                success: function (response) {

                    btn.prop('disabled', false);

                    if (!response.ok) {
                        Swal.fire('Error', response.message || 'No se pudo cambiar el estado', 'error');
                        return;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Estado actualizado',
                        text: response.message || ('La institución ahora está ' + nuevoEstado),
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function () {
                        location.reload();
                    });
                },
                error: function () {
                    btn.prop('disabled', false);
                    Swal.fire('Error', 'No se pudo cambiar estado', 'error');
                }
            });

        });
    });

}); // 🔥 FIN document.ready // 🔥 FIN document.ready



// =====================================================
// 4️⃣ FUNCIÓN GLOBAL ABRIR MODAL EDITAR
// 👇 ESTA VA FUERA DEL document.ready
// =====================================================
function abrirModalEditar(id, nombre, nit, tipo) {

    console.log("Abriendo modal:", id);

    $('#editIdInstituto').val(id);
    $('#editNombreInstituto').val(nombre);
    $('#editNitInstituto').val(nit);
    $('#editTipoInstituto').val(tipo);

    $('#modalEditarInstituto').modal('show');
}