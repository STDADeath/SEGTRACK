// ========================================
// LISTA DE FUNCIONARIOS - SEGTRACK
// ========================================

console.log("✅ FuncionarioListaADM cargado");

// URL del controlador
const urlControlador = "../../Controller/ControladorFuncionarios.php";


$(document).ready(function () {

    // ==============================
    // INICIALIZAR DATATABLE
    // ==============================
    $('#tablaFuncionarios').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [[2, "asc"]],
        columnDefs: [
            { targets: [0, 8], orderable: false }
        ],
        language: {
            processing:   "Procesando...",
            lengthMenu:   "Mostrar _MENU_ registros",
            zeroRecords:  "No se encontraron resultados",
            emptyTable:   "Ningún dato disponible en esta tabla",
            info:         "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty:    "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            search:       "Buscar:",
            paginate: {
                first:    "Primero",
                last:     "Último",
                next:     "Siguiente",
                previous: "Anterior"
            }
        }
    });

    // ==============================
    // GUARDAR CAMBIOS EDICIÓN
    // ==============================
    $('#btnGuardarCambios').on('click', function () {

        const id        = $('#editId').val();
        const cargo     = $('#editCargo').val().trim();
        const nombre    = $('#editNombre').val().trim();
        const sede      = $('#editSede').val();
        const telefono  = $('#editTelefono').val().trim();
        const documento = $('#editDocumento').val().trim();
        const correo    = $('#editCorreo').val().trim();

        if (!cargo || !nombre || !sede || !telefono) {
            Swal.fire('Error', 'Todos los campos son obligatorios', 'error');
            return;
        }

        Swal.fire({
            title: '¿Actualizar funcionario?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText:  'Cancelar'
        }).then((result) => {

            if (!result.isConfirmed) return;

            const btn           = $('#btnGuardarCambios');
            const textoOriginal = btn.html();

            btn.prop('disabled', true);
            btn.html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

            $.ajax({
                url:      urlControlador,
                type:     'POST',
                dataType: 'json',
                data: {
                    accion:               "actualizar",
                    IdFuncionario:        id,
                    CargoFuncionario:     cargo,
                    NombreFuncionario:    nombre,
                    IdSede:               sede,
                    TelefonoFuncionario:  telefono,
                    DocumentoFuncionario: documento,
                    CorreoFuncionario:    correo
                },
                success: function (response) {
                    btn.prop('disabled', false);
                    btn.html(textoOriginal);

                    if (response.success) {
                        Swal.fire('Correcto', response.message, 'success')
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function (xhr) {
                    btn.prop('disabled', false);
                    btn.html(textoOriginal);
                    console.error("Error AJAX:", xhr.responseText);
                    Swal.fire('Error', 'No se pudo actualizar', 'error');
                }
            });

        });
    });

});


// ========================================
// ✅ VER QR — RUTA CORREGIDA
// ========================================
function verQR(rutaQR, idFuncionario) {

    if (!rutaQR) {
        Swal.fire('Error', 'Este funcionario no tiene código QR', 'error');
        return;
    }

    /*
     * rutaQR llega del PHP ya normalizado como: "/qr/Qr_Func/QR-FUNC-109-xxx.png"
     * La imagen física está en:   SEGTRACK/Public/qr/Qr_Func/QR-FUNC-109-xxx.png
     * La URL pública debe ser:    http://localhost/SEGTRACK/Public/qr/Qr_Func/QR-FUNC-109-xxx.png
     *
     * Detectamos el segmento raíz del proyecto (SEGTRACK) desde el pathname actual.
     */
    const pathname = window.location.pathname;   // /SEGTRACK/App/View/Administrador/FuncionarioListaADM.php
    const partes   = pathname.split('/');         // ["","SEGTRACK","App","View","Administrador","..."]
    const idx      = partes.indexOf('SEGTRACK');

    // base = "/SEGTRACK"  (o "" si el proyecto está en la raíz del servidor)
    const base = (idx !== -1) ? '/' + partes.slice(1, idx + 1).join('/') : '';

    // URL completa: http://localhost/SEGTRACK/Public/qr/Qr_Func/QR-FUNC-109-xxx.png
    const rutaCompleta = window.location.origin + base + '/Public' + rutaQR;

    console.log("📂 Base proyecto:", window.location.origin + base);
    console.log("🖼️  QR URL final:", rutaCompleta);

    $('#qrFuncionarioId').text(idFuncionario);
    $('#qrImagen').attr('src', rutaCompleta);
    $('#btnDescargarQR').attr('href', rutaCompleta);

    $('#modalVerQR').modal('show');
}


// ========================================
// ✅ ENVIAR QR POR CORREO — CORREGIDO
//
//==========================================
function enviarQR(idFuncionario) {

    Swal.fire({
        title: '¿Enviar QR por correo?',
        text:  'Se enviará el código QR al correo registrado del funcionario.',
        icon:  'question',
        showCancelButton:   true,
        confirmButtonText:  'Sí, enviar',
        cancelButtonText:   'Cancelar',
        confirmButtonColor: '#4e73df',
        cancelButtonColor:  '#858796'
    }).then((result) => {

        if (!result.isConfirmed) return;

        // Spinner mientras se procesa
        Swal.fire({
            title:             'Enviando correo...',
            text:              'Por favor espera un momento',
            allowOutsideClick: false,
            allowEscapeKey:    false,
            didOpen:           () => Swal.showLoading()
        });

        $.ajax({
            url:      urlControlador,
            type:     'POST',
            dataType: 'json',
            data: {
                accion:        'enviar_qr',      // ← case 'enviar_qr' en el controlador
                IdFuncionario: idFuncionario     // ← el controlador busca todo en la BD
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title:  'Enviado',
                        text:   response.message,
                        icon:  'success'
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function (xhr) {
                console.error("Error enviar QR:", xhr.responseText);
                Swal.fire('Error', 'No se pudo enviar el correo. Revisa la consola.', 'error');
            }
        });

    });
}


// ========================================
// CARGAR DATOS EN MODAL EDICIÓN
// ========================================
function cargarDatosEdicion(id, cargo, nombre, sede, telefono, documento, correo) {

    $('#editId').val(id);
    $('#editCargo').val(cargo);
    $('#editNombre').val(nombre);
    $('#editSede').val(parseInt(sede));
    $('#editTelefono').val(telefono);
    $('#editDocumento').val(documento).prop('readonly', true);
    $('#editCorreo').val(correo).prop('readonly', true);

    $('#modalEditar').modal('show');
}


// ========================================
// CAMBIAR ESTADO
// ========================================
function cambiarEstado(idFuncionario, estadoActual) {

    const nuevoEstado = (estadoActual === 'Activo') ? 'Inactivo' : 'Activo';

    Swal.fire({
        title: '¿Cambiar estado?',
        text:  `El funcionario pasará a estar ${nuevoEstado}`,
        icon:  'warning',
        showCancelButton:  true,
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText:  'Cancelar'
    }).then((result) => {

        if (!result.isConfirmed) return;

        $.ajax({
            url:      urlControlador,
            type:     'POST',
            dataType: 'json',
            data: {
                accion:        'cambiar_estado',
                IdFuncionario: idFuncionario,
                Estado:        nuevoEstado
            },
            success: function (response) {
                console.log("Respuesta cambiar estado:", response);
                if (response.success) {
                    Swal.fire('Correcto', response.message, 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function (xhr) {
                console.error("Error estado:", xhr.responseText);
                Swal.fire('Error', 'No se pudo cambiar el estado', 'error');
            }
        });

    });
}