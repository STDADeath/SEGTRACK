// Espera que el DOM esté completamente cargado
$(document).ready(function() {

    // INICIALIZA EL DATATABLE DE LA TABLA HTML
    // Este bloque crea el DataTable que se conecta vía Ajax al backend para listar datos
    // ➤ Llama a: controllerAction.php con acción 'listRecords'
    // ➤ Usa método: Vehiculo::listar() desde el modelo
    var dataRecords = $('#recordListing').DataTable({
        "processing": true,     // Muestra "Procesando..." mientras carga
        "serverSide": true,     // Habilita el procesamiento del lado del servidor
        "serverMethod": 'post', // Envia datos vía POST
        "order": [],            // No ordena por defecto

        // Configuración en español
        "language": {
            "lengthMenu": "Mostrar _MENU_ registros",
            "zeroRecords": "No se encontraron resultados",
            "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "infoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sSearch": "Buscar:",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "sProcessing": "Procesando..."
        },

        // Solicita datos a controllerAction.php
        "ajax": {
            url: "controllers/controllerAction.php", // ➤ Va al controlador
            type: "POST",
            data: { action: 'listRecords' },         // ➤ Dispara Vehiculo::listar()
            dataType: "json"
        },

        // Columnas que no se pueden ordenar (botones editar y eliminar)
        "columnDefs": [
            {
                "targets": [0, 6, 7],
                "orderable": false,
            },
        ],

        "pageLength": 10 // Cantidad de registros por página
    });

    // MOSTRAR MODAL PARA AGREGAR NUEVO REGISTRO
    // ➤ Se activa al hacer clic en el botón "Agregar Vehículo"
    // ➤ Prepara el modal para usar acción 'addRecord'
    $('#addRecord').click(function() {
        $('#records').modal('show');                  // Abre el modal
        $('#recordForm')[0].reset();                 // Limpia campos
        $('.modal-title').html("<i class='fa fa-plus'></i> Agregar Vehículo");
        $('#action').val('addRecord');               // Define que es una inserción
        $('#save').val('Guardar');
    });

    // EDITAR REGISTRO EXISTENTE
    // ➤ Cuando se da clic en el botón de clase 'update'
    // ➤ Envia ID al controlador con acción 'getRecord'
    // ➤ Recibe datos y los coloca en el modal
    $("#recordListing").on('click', '.update', function() {
        var id = $(this).attr("id");

        $.ajax({
            url: 'controllers/controllerAction.php', // ➤ Controlador
            method: "POST",
            data: { id: id, action: 'getRecord' },   // ➤ Dispara Vehiculo::obtener()
            dataType: "json",
            success: function(data) {
                $('#records').modal('show');         // Muestra el modal
                // Coloca los datos en los inputs del modal
                $('#id').val(data.IdParqueadero);
                $('#tipoVehiculo').val(data.tipoVehiculo);
                $('#placaVehiculo').val(data.PlacaVehiculo);
                $('#descripcionVehiculo').val(data.DescripcionVehiculo);
                $('#tarjetaPropiedad').val(data.TarjetaPropiedad);
                $('#fechaParqueadero').val(data.FechaParqueadero);
                $('#idSede').val(data.IdSede);

                $('.modal-title').html("<i class='fa fa-edit'></i> Editar Vehículo");
                $('#action').val('updateRecord');    // ➤ Define que es actualización
                $('#save').val('Actualizar');
            }
        });
    });

    // GUARDAR O ACTUALIZAR REGISTRO
    // ➤ Se activa al enviar el formulario del modal
    // ➤ Envia todos los datos del formulario al controlador
    $("#recordForm").on('submit', function(event) {
        event.preventDefault();                      // Evita recarga
        $('#save').attr('disabled', 'disabled');     // Desactiva botón durante envío

        var formData = $(this).serialize();          // Serializa todos los inputs del form

        $.ajax({
            url: "controllers/controllerAction.php", // ➤ Envia a PHP
            method: "POST",
            data: formData,                          // ➤ 'action' puede ser addRecord o updateRecord
            success: function(data) {
                $('#recordForm')[0].reset();         // Limpia el form
                $('#records').modal('hide');         // Oculta el modal
                $('#save').attr('disabled', false);  // Activa botón de nuevo
                dataRecords.ajax.reload();           // Recarga la tabla con los nuevos datos
            }
        });
    });

    // ELIMINAR REGISTRO
    // ➤ Se activa al hacer clic en el botón de clase 'delete'
    // ➤ Solicita confirmación y envía ID al controlador
    $("#recordListing").on('click', '.delete', function() {
        var id = $(this).attr("id");

        if (confirm("¿Está seguro de que desea eliminar este registro?")) {
            $.ajax({
                url: "controllers/controllerAction.php",
                method: "POST",
                data: { id: id, action: 'deleteRecord' }, // ➤ Dispara Vehiculo::eliminar()
                success: function(data) {
                    dataRecords.ajax.reload();           // Recarga tabla
                }
            });
        }
    });

});
