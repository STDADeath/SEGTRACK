$(document).ready(function () {

    // Inicializar DataTable igual que Institución
    var tabla = $('#tablaSedes').DataTable({
        "language": {
            "emptyTable": "No hay datos disponibles",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "lengthMenu": "Mostrar _MENU_ registros",
            "search": "Buscar:",
            "zeroRecords": "No se encontraron registros",
            "paginate": {
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        "pageLength": 10
    });

    // BOTÓN CAMBIAR ESTADO
    $('#tablaSedes tbody').on('click', '.btn-toggle-estado', function (e) {
        e.preventDefault();

        var $btn = $(this);
        var id = $btn.data('id');
        var estadoActual = $btn.data('estado-actual');
        var nombre = $btn.data('nombre');

        // Nuevo estado
        var nuevoEstado = (estadoActual === 'Activo') ? 'Inactivo' : 'Activo';

        if (!confirm("¿Confirmas cambiar el estado de la sede '" + nombre + "' a " + nuevoEstado + "?")) {
            return;
        }

        // Spinner
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: "../../Controller/ControladorSede.php",
            method: "POST",
            data: {
                accion: "cambiarEstado",
                IdSede: id,
                Estado: nuevoEstado
            },
            dataType: "json",
            success: function (response) {

                if (response.ok) {

                    var $row = $btn.closest('tr');
                    var $badge = $row.find("td:first span");

                    // Actualizar badge
                    if (nuevoEstado === "Activo") {
                        $badge.removeClass("badge-secondary").addClass("badge-success").text("Activo");
                        $btn.removeClass("btn-success").addClass("btn-secondary").html('<i class="fas fa-ban"></i>');
                    } else {
                        $badge.removeClass("badge-success").addClass("badge-secondary").text("Inactivo");
                        $btn.removeClass("btn-secondary").addClass("btn-success").html('<i class="fas fa-check"></i>');
                    }

                    // Guardar nuevo estado en el botón
                    $btn.data("estado-actual", nuevoEstado);

                    alert(response.message);

                } else {
                    alert(response.message || "Error al actualizar el estado.");
                }

                $btn.prop('disabled', false);
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                alert("Error en la comunicación con el servidor.");
                $btn.prop('disabled', false);
            }
        });
    });

});
