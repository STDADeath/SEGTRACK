$(document).ready(function () {

    // ==========================================
    // DATATABLE PROFESIONAL
    // ==========================================
   $('#tablaSedes').DataTable({

    ordering: false, // 🔥 DESACTIVA LAS FLECHITAS
    pageLength: 10,
    lengthMenu: [[5,10,25,50,100],[5,10,25,50,100]],
    responsive: true,

    language: {
        emptyTable: "No hay sedes registradas",
        info: "Mostrando _START_ a _END_ de _TOTAL_ sedes",
        infoEmpty: "Mostrando 0 a 0 de 0 sedes",
        infoFiltered: "(filtrado de _MAX_ sedes)",
        lengthMenu: "Mostrar _MENU_ sedes",
        search: "Buscar:",
        zeroRecords: "No se encontraron resultados",
        paginate: {
            
            first: "Primera",
            last: "Última",
            next: "Siguiente",
            previous: "Anterior"
        }
    }
});


// ==========================================
// CAMBIAR ESTADO CON ALERTA BONITA Y ICONO DINÁMICO
// ==========================================
$('#tablaSedes').on('click', '.btn-estado', function () {

    let btn = $(this);
    let id = btn.data('id');
    let fila = btn.closest('tr');
    let badge = fila.find('.estado-badge');
    let icon = btn.find('i');

    const estadoActual = badge.text().trim();
    const nuevoEstado = (estadoActual === "Activo") ? "Inactivo" : "Activo";

    Swal.fire({
        title: `¿Cambiar estado de la sede?`,
        text: `La sede pasará a estar ${nuevoEstado}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (!result.isConfirmed) return;

        // Animación de carga
        btn.prop('disabled', true);
        icon.removeClass().addClass('fas fa-spinner fa-spin');

        $.ajax({
            url: '../../Controller/ControladorSede.php',
            method: 'POST',
            dataType: 'json',
            data: {
                accion: 'cambiarEstado',
                id: id
            },
            success: function (response) {

                btn.prop('disabled', false);

                if (!response.success) {
                    Swal.fire('Error', response.message, 'error');
                    icon.removeClass().addClass('fas fa-sync-alt');
                    return;
                }

                // Actualizar badge e icono según nuevo estado
                if (nuevoEstado === "Activo") {
                    badge.removeClass()
                         .addClass('badge bg-success px-3 py-2 estado-badge')
                         .text('Activo');

                    icon.removeClass()
                        .addClass('fas fa-lock-open text-success')
                        .attr('title','Desactivar sede');

                } else {
                    badge.removeClass()
                         .addClass('badge px-3 py-2 estado-badge')
                         .css('background-color','#60a5fa')
                         .text('Inactivo');

                    icon.removeClass()
                        .addClass('fas fa-lock text-danger')
                        .attr('title','Activar sede');
                }

                Swal.fire({
                    icon: 'success',
                    title: `Estado actualizado`,
                    text: `La sede ahora está ${nuevoEstado}`,
                    timer: 1800,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
            },
            error: function () {
                btn.prop('disabled', false);
                icon.removeClass().addClass('fas fa-sync-alt');
                Swal.fire('Error', 'No se pudo conectar al servidor', 'error');
            }
        });
    });

});

});