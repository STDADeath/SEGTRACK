// ========================================
// LISTA DE INSTITUCIONES - SEGTRACK
// ========================================

console.log("✅ ValidacionInstitutoLista.js cargado correctamente");

const urlControladorInstituto = "../../Controller/Controladorinstituto.php";

$(document).ready(function () {

    // ══════════════════════════════════════════════════════
    // INICIALIZAR DATATABLE
    // Convierte la tabla en interactiva con búsqueda y paginación
    // ══════════════════════════════════════════════════════
    if ($.fn.DataTable.isDataTable('#tablaInstitutos')) {
        $('#tablaInstitutos').DataTable().destroy();
    }

    $('#tablaInstitutos').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        order: [[0, 'desc']],
        columnDefs: [{ targets: 5, orderable: false }],
        responsive: true
    });


    // ══════════════════════════════════════════════════════
    // VALIDACIÓN EN TIEMPO REAL DEL MODAL
    // Bloquea caracteres no permitidos mientras el usuario escribe
    // ══════════════════════════════════════════════════════

    // Nombre: solo letras y espacios
    $(document).on('input', '#editNombreInstituto', function () {
        let campo = $(this);
        let valorLimpio = campo.val().replace(/[^A-Za-zÁÉÍÓÚÑáéíóúñ ]/g, "");
        campo.val(valorLimpio);

        if (valorLimpio.length >= 3) {
            campo.css({ "border": "2px solid #10b981", "box-shadow": "0 0 0 0.25rem rgba(16,185,129,0.25)" });
        } else {
            campo.css({ "border": "2px solid #ef4444", "box-shadow": "0 0 0 0.25rem rgba(239,68,68,0.25)" });
        }
    });

    // Dirección: letras, números, espacios, guiones, # y comas
    $(document).on('input', '#editDireccionInstituto', function () {
        let campo = $(this);
        let valorLimpio = campo.val().replace(/[^A-Za-zÁÉÍÓÚÑáéíóúñ0-9 \-#,]/g, "");
        campo.val(valorLimpio);

        if (valorLimpio.length === 0) {
            campo.css({ "border": "", "box-shadow": "" });
        } else if (valorLimpio.length >= 5) {
            campo.css({ "border": "2px solid #10b981", "box-shadow": "0 0 0 0.25rem rgba(16,185,129,0.25)" });
        } else {
            campo.css({ "border": "2px solid #ef4444", "box-shadow": "0 0 0 0.25rem rgba(239,68,68,0.25)" });
        }
    });


    // ══════════════════════════════════════════════════════
    // GUARDAR CAMBIOS DESDE EL MODAL
    // Valida, confirma con SweetAlert2 y envía AJAX al controlador
    // ══════════════════════════════════════════════════════
    $(document).on('click', '#btnGuardarInstituto', function () {

        const id        = $('#editIdInstituto').val();
        const nombre    = $('#editNombreInstituto').val().trim();
        const tipo      = $('#editTipoInstituto').val();
        const nit       = $('#editNitInstituto').val().trim();
        const direccion = $('#editDireccionInstituto').val().trim();
        // Lee el estado real guardado en el campo oculto — no lo hardcodeamos
        const estado    = $('#editEstadoInstituto').val();

        const soloLetras    = /^[A-Za-zÁÉÍÓÚÑáéíóúñ ]+$/;
        const soloDireccion = /^[A-Za-zÁÉÍÓÚÑáéíóúñ0-9 \-#,]+$/;

        // Validación nombre
        if (!nombre || nombre.length < 3) {
            Swal.fire({ icon: 'error', title: 'Campo incompleto',
                text: 'El nombre debe tener mínimo 3 caracteres.',
                confirmButtonColor: '#ef4444' });
            return;
        }
        if (!soloLetras.test(nombre)) {
            Swal.fire({ icon: 'error', title: 'Nombre inválido',
                text: 'El nombre solo puede contener letras y espacios.',
                confirmButtonColor: '#ef4444' });
            return;
        }

        // Validación tipo
        if (!tipo) {
            Swal.fire({ icon: 'error', title: 'Campo requerido',
                text: 'Debe seleccionar un tipo de institución.',
                confirmButtonColor: '#ef4444' });
            return;
        }

        // Validación dirección (opcional)
        if (direccion.length > 0) {
            if (direccion.length < 5) {
                Swal.fire({ icon: 'error', title: 'Dirección muy corta',
                    text: 'La dirección debe tener al menos 5 caracteres.',
                    confirmButtonColor: '#ef4444' });
                return;
            }
            if (!soloDireccion.test(direccion)) {
                Swal.fire({ icon: 'error', title: 'Dirección inválida',
                    text: 'Solo permite letras, números, espacios, guiones (-), # y comas.',
                    confirmButtonColor: '#ef4444' });
                return;
            }
        }

        // Confirmación antes de guardar
        Swal.fire({
            title: '¿Actualizar institución?',
            text: 'Los cambios se guardarán en la base de datos.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6c757d'
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
                    accion:               'editar',
                    IdInstitucion:        id,
                    NombreInstitucion:    nombre,
                    TipoInstitucion:      tipo,
                    Nit_Codigo:           nit,
                    DireccionInstitucion: direccion, // '' si está vacío
                    EstadoInstitucion:    estado     // estado real, no hardcodeado
                },
                success: function (response) {
                    btn.prop('disabled', false);
                    btn.html(textoOriginal);

                    if (response.ok) {
                        $('#modalEditarInstituto').modal('hide');
                        Swal.fire({
                            icon: 'success', title: '¡Actualizado!',
                            text: response.message,
                            timer: 1800, showConfirmButton: false, timerProgressBar: true
                        }).then(function () { location.reload(); });
                    } else {
                        Swal.fire({ icon: 'warning', title: 'No se pudo actualizar',
                            text: response.message,
                            confirmButtonText: 'Entendido', confirmButtonColor: '#f59e0b' });
                    }
                },
                error: function (xhr) {
                    btn.prop('disabled', false);
                    btn.html(textoOriginal);
                    console.error("Error servidor:", xhr.responseText);
                    Swal.fire({ icon: 'error', title: 'Error de conexión',
                        text: 'No se pudo conectar al servidor.',
                        confirmButtonColor: '#ef4444' });
                }
            });
        });
    });


    // ══════════════════════════════════════════════════════
    // CANDADO: CAMBIAR ESTADO (Activo ↔ Inactivo)
    // Usa delegación de eventos para funcionar con DataTables.
    // Solo toca EstadoInstitucion, no modifica ningún otro campo.
    // ══════════════════════════════════════════════════════
    $(document).on('click', '.btn-toggle-estado', function () {

        const btn          = $(this);
        const id           = btn.data('id');
        const estadoActual = btn.data('estado');
        const nuevoEstado  = (estadoActual === 'Activo') ? 'Inactivo' : 'Activo';
        const esDesactivar = (nuevoEstado === 'Inactivo');

        Swal.fire({
            title: esDesactivar ? '¿Desactivar institución?' : '¿Activar institución?',
            text: esDesactivar
                ? 'La institución pasará a Inactivo.'
                : 'La institución volverá a estar Activo.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: esDesactivar ? 'Sí, desactivar' : 'Sí, activar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: esDesactivar ? '#f59e0b' : '#10b981',
            cancelButtonColor: '#6c757d'
        }).then(function (result) {

            if (!result.isConfirmed) return;

            btn.prop('disabled', true);
            btn.find('i').removeClass().addClass('fas fa-spinner fa-spin');

            $.ajax({
                url: urlControladorInstituto,
                type: 'POST',
                dataType: 'json',
                data: {
                    accion:            'cambiarEstado',
                    IdInstitucion:     id,
                    EstadoInstitucion: nuevoEstado
                },
                success: function (response) {
                    btn.prop('disabled', false);

                    if (!response.ok) {
                        btn.find('i').removeClass('fas fa-spinner fa-spin')
                           .addClass(estadoActual === 'Activo' ? 'fas fa-lock' : 'fas fa-lock-open');
                        Swal.fire({ icon: 'error', title: 'Error',
                            text: response.message || 'No se pudo cambiar el estado.',
                            confirmButtonColor: '#ef4444' });
                        return;
                    }

                    Swal.fire({
                        icon: 'success', title: '¡Estado actualizado!',
                        text: `La institución ahora está ${nuevoEstado}.`,
                        timer: 1500, showConfirmButton: false, timerProgressBar: true
                    }).then(function () { location.reload(); });
                },
                error: function () {
                    btn.prop('disabled', false);
                    btn.find('i').removeClass('fas fa-spinner fa-spin')
                       .addClass(estadoActual === 'Activo' ? 'fas fa-lock' : 'fas fa-lock-open');
                    Swal.fire({ icon: 'error', title: 'Error de conexión',
                        text: 'No se pudo cambiar el estado.',
                        confirmButtonColor: '#ef4444' });
                }
            });
        });
    });

}); // FIN document.ready


// ══════════════════════════════════════════════════════
// FUNCIÓN GLOBAL: ABRIR MODAL EDITAR
// Fuera del document.ready — debe ser accesible desde onclick="" del PHP.
// Recibe 6 parámetros: id, nombre, nit, tipo, direccion, estado.
// ══════════════════════════════════════════════════════
function abrirModalEditar(id, nombre, nit, tipo, direccion, estado) {

    console.log("Modal → ID:", id, "| Dirección:", direccion, "| Estado:", estado);

    $('#editIdInstituto').val(id);
    $('#editNombreInstituto').val(nombre);
    $('#editNitInstituto').val(nit);
    $('#editTipoInstituto').val(tipo);
    // ✅ Si la dirección es null/undefined/vacío muestra '' en el input
    $('#editDireccionInstituto').val(direccion || "");
    // ✅ Guarda el estado real para que el AJAX no lo pise al guardar
    $('#editEstadoInstituto').val(estado);

    // Resetea estilos de validación visual
    $('#editNombreInstituto').css({ "border": "", "box-shadow": "" });
    $('#editDireccionInstituto').css({ "border": "", "box-shadow": "" });

    $('#modalEditarInstituto').modal('show');
}