console.log("✅ UsuariosLista.js cargado");

const urlControlador = "../../Controller/ControladorusuarioADM.php";

// ============================================
// INICIALIZAR DATATABLE (igual que DispositivoLista)
// ============================================
$(document).ready(function () {

    $('#tablaUsuarios').DataTable({
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        },
        pageLength: 10,
        responsive: true,
        order: [[3, "desc"]],           // Activos primero
        columnDefs: [
            { targets: [3, 4], orderable: false }
        ]
    });

    console.log("✅ DataTable inicializado");

    // ============================================
    // GUARDAR CAMBIOS DE ROL
    // ============================================
    $('#btnGuardarRol').on('click', function () {

        const idUsuario = $('#editIdUsuario').val();
        const nuevoRol  = $('#editTipoRol').val();

        if (!nuevoRol) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo requerido',
                text: 'Debe seleccionar un rol',
                confirmButtonColor: '#f6c23e'
            });
            return;
        }

        const btn  = $(this);
        const orig = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');

        $.ajax({
            url:      urlControlador,
            type:     'POST',
            dataType: 'json',
            data: {
                accion:    'actualizar',
                IdUsuario:  idUsuario,
                tipo_rol:   nuevoRol
            },
            success: function (response) {

                btn.prop('disabled', false).html(orig);
                console.log("✓ Respuesta actualizar:", response);

                if (response.success === true) {
                    Swal.fire({
                        icon:  'success',
                        title: '¡Éxito!',
                        text:   response.message,
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        $('#modalEditarRol').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon:  'warning',
                        title: 'No se pudo actualizar',
                        text:   response.message || 'Error al actualizar el rol',
                        confirmButtonColor: '#f6c23e'
                    });
                }
            },
            error: function (xhr, status, error) {
                btn.prop('disabled', false).html(orig);
                console.error("❌ Error AJAX actualizar:", xhr.responseText);
                Swal.fire({
                    icon:  'error',
                    title: 'Error de conexión',
                    text:  'No se pudo conectar con el servidor',
                    confirmButtonColor: '#e74a3b'
                });
            }
        });
    });

});

// ============================================
// CAMBIAR ESTADO (clic en badge)
// ============================================
function cambiarEstado(idUsuario, estadoActual) {

    console.log("=== CAMBIAR ESTADO ===", idUsuario, estadoActual);

    const nuevoEstado = (estadoActual === 'Activo') ? 'Inactivo' : 'Activo';

    Swal.fire({
        title: '¿Cambiar estado?',
        text:  `El usuario pasará a estar ${nuevoEstado}`,
        icon:  'warning',
        showCancelButton:   true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor:  '#d33',
        confirmButtonText:  'Sí, cambiar',
        cancelButtonText:   'Cancelar',
        reverseButtons: true
    }).then((result) => {

        if (!result.isConfirmed) return;

        // Loading igual que DispositivoLista
        Swal.fire({
            title: 'Actualizando...',
            html:  '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br>Por favor espere',
            allowOutsideClick: false,
            allowEscapeKey:    false,
            showConfirmButton:  false
        });

        $.ajax({
            url:      urlControlador,
            type:     'POST',
            dataType: 'json',
            data: {
                accion:    'cambiar_estado',
                IdUsuario:  idUsuario,
                Estado:     nuevoEstado
            },
            success: function (response) {

                console.log("✓ Respuesta cambiar estado:", response);

                if (response.success === true) {
                    Swal.fire({
                        icon:  'success',
                        title: '¡Listo!',
                        text:   response.message,
                        timer: 1500,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon:  'warning',
                        title: 'No se pudo cambiar',
                        text:   response.message || 'Error al cambiar el estado',
                        confirmButtonColor: '#f6c23e'
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error("❌ Error AJAX cambiar estado:", xhr.responseText);
                Swal.fire({
                    icon:  'error',
                    title: 'Error de conexión',
                    text:  'No se pudo conectar con el servidor',
                    confirmButtonColor: '#e74a3b',
                    footer: '<small>Revisa la consola del navegador (F12)</small>'
                });
            }
        });
    });
}

// ============================================
// ABRIR MODAL EDITAR ROL
// ============================================
function editarRol(idUsuario, rolActual, nombreFuncionario) {
    console.log("=== EDITAR ROL ===", idUsuario, rolActual);
    $('#editIdUsuario').val(idUsuario);
    $('#editNombreFuncionario').text(nombreFuncionario);
    $('#editTipoRol').val(rolActual);
    $('#modalEditarRol').modal('show');
}

console.log("✅ Funciones listas: cambiarEstado, editarRol");