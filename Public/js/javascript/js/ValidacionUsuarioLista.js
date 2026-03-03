
    // ========================================
// LISTA DE USUARIOS - SEGTRACK
// ========================================

console.log("✅ ValidacionUsuarioLista.js cargado correctamente");

const urlControlador = "../../Controller/ControladorusuarioADM.php";

$(document).ready(function () {

    // ══════════════════════════════════════════════════════
    // INICIALIZAR DATATABLE
    // ══════════════════════════════════════════════════════
    if ($.fn.DataTable.isDataTable('#tablaUsuarios')) {
        $('#tablaUsuarios').DataTable().destroy();
    }

    $('#tablaUsuarios').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        order: [[0, 'desc']],
        columnDefs: [{ targets: [3, 4], orderable: false }],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
        },
        responsive: true
    });


    // ══════════════════════════════════════════════════════
    // ABRIR MODAL EDITAR ROL
    // ══════════════════════════════════════════════════════
    $(document).on('click', '.btn-editar-rol', function () {
        const id     = $(this).data('id');
        const rol    = $(this).data('rol');
        const nombre = $(this).data('nombre');

        $('#editIdUsuario').val(id);
        $('#editNombreFuncionario').val(nombre);
        $('#editTipoRol').val(rol);

        $('#modalEditarRol').modal('show');
    });


    // ══════════════════════════════════════════════════════
    // GUARDAR CAMBIOS DE ROL
    // ══════════════════════════════════════════════════════
    $(document).on('click', '#btnGuardarRol', function () {

        const idUsuario = $('#editIdUsuario').val();
        const nuevoRol  = $('#editTipoRol').val();

        if (!nuevoRol) {
            Swal.fire({
                icon: 'error',
                title: 'Campo requerido',
                text: 'Debe seleccionar un rol.',
                confirmButtonColor: '#ef4444'
            });
            return;
        }

        Swal.fire({
            title: '¿Actualizar rol?',
            text: 'El rol del usuario será modificado.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6c757d'
        }).then(function (result) {

            if (!result.isConfirmed) return;

            const btn  = $('#btnGuardarRol');
            const orig = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');

            $.ajax({
                url: urlControlador,
                type: 'POST',
                dataType: 'json',
                data: {
                    accion:    'actualizar',
                    IdUsuario:  idUsuario,
                    tipo_rol:   nuevoRol
                },
                success: function (response) {
                    btn.prop('disabled', false).html(orig);

                    if (response.success === true) {
                        $('#modalEditarRol').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: response.message,
                            timer: 1800,
                            showConfirmButton: false,
                            timerProgressBar: true
                        }).then(function () { location.reload(); });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No se pudo actualizar',
                            text: response.message || 'Error al actualizar el rol.',
                            confirmButtonColor: '#f59e0b'
                        });
                    }
                },
                error: function (xhr) {
                    btn.prop('disabled', false).html(orig);
                    console.error("Error servidor:", xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo conectar al servidor.',
                        confirmButtonColor: '#ef4444'
                    });
                }
            });
        });
    });


    // ══════════════════════════════════════════════════════
    // CANDADO: CAMBIAR ESTADO (Activo ↔ Inactivo)
    // Usa delegación de eventos para funcionar con DataTables.
    // ══════════════════════════════════════════════════════
    $(document).on('click', '.btn-toggle-estado', function () {

        const btn          = $(this);
        const id           = btn.data('id');
        const estadoActual = btn.data('estado');
        const nuevoEstado  = (estadoActual === 'Activo') ? 'Inactivo' : 'Activo';
        const esDesactivar = (nuevoEstado === 'Inactivo');

        Swal.fire({
            title: esDesactivar ? '¿Desactivar usuario?' : '¿Activar usuario?',
            text: esDesactivar
                ? 'El usuario pasará a Inactivo.'
                : 'El usuario volverá a estar Activo.',
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
                url: urlControlador,
                type: 'POST',
                dataType: 'json',
                data: {
                    accion:    'cambiar_estado',
                    IdUsuario:  id,
                    Estado:     nuevoEstado
                },
                success: function (response) {
                    btn.prop('disabled', false);

                    if (!response || response.success !== true) {
                        // Restaurar ícono original si falla
                        btn.find('i').removeClass().addClass(
                            estadoActual === 'Activo' ? 'fas fa-lock' : 'fas fa-lock-open'
                        );
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'No se pudo cambiar el estado.',
                            confirmButtonColor: '#ef4444'
                        });
                        return;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: '¡Estado actualizado!',
                        text: `El usuario ahora está ${nuevoEstado}.`,
                        timer: 1500,
                        showConfirmButton: false,
                        timerProgressBar: true
                    }).then(function () { location.reload(); });
                },
                error: function () {
                    btn.prop('disabled', false);
                    btn.find('i').removeClass().addClass(
                        estadoActual === 'Activo' ? 'fas fa-lock' : 'fas fa-lock-open'
                    );
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo cambiar el estado.',
                        confirmButtonColor: '#ef4444'
                    });
                }
            });
        });
    });

}); // FIN document.ready