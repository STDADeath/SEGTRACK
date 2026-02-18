$(document).ready(function () {

    // ==============================
    // INICIALIZAR DATATABLE
    // ==============================
    $('#tablaSedes').DataTable({

        language: {
            emptyTable: "No hay datos disponibles",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros)",
            lengthMenu: "Mostrar _MENU_ registros",
            search: "Buscar:",
            zeroRecords: "No se encontraron registros",
            paginate: {
                first: "Primera",
                last: "Última",
                next: "Siguiente",
                previous: "Anterior"
            }
        },

        pageLength: 10,
        lengthMenu: [[5,10,25,50,100],[5,10,25,50,100]],
        responsive: true,

        order: [[0, "asc"]],

        columnDefs: [
            {
                targets: 4,
                orderable: false
            }
        ]
    });


    // ==============================
    // CAMBIAR ESTADO
    // ==============================
    $('#tablaSedes').on('click', '.btn-toggle-estado', function () {

        let btn = $(this);
        let id = btn.data('id');
        let fila = btn.closest('tr');
        let badge = fila.find('td:eq(3) span');

        if (!confirm("¿Desea cambiar el estado de esta sede?")) return;

        btn.prop('disabled', true)
           .html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: '../../Controller/ControladorSede.php',
            method: 'POST',
            dataType: 'json',
            data: {
                accion: 'cambiarEstado',
                id: id
            },

            success: function (response) {

                if (response.success) {

                    if (badge.text().trim() === "Activo") {

                        badge.removeClass()
                              .addClass('badge px-3 py-2')
                              .css('background-color','#60a5fa')
                              .text('Inactivo');

                    } else {

                        badge.removeClass()
                              .addClass('badge bg-success px-3 py-2')
                              .text('Activo');
                    }

                } else {
                    alert(response.message);
                }

                btn.prop('disabled', false)
                   .html('<i class="fas fa-sync-alt"></i>');
            },

            error: function () {
                alert("Error de conexión.");
                btn.prop('disabled', false)
                   .html('<i class="fas fa-sync-alt"></i>');
            }
        });

    });

});
