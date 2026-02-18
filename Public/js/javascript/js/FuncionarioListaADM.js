// ========================================
// LISTA DE FUNCIONARIOS - SEGTRACK
// ========================================

console.log("✅ FuncionarioLista.js cargado");

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
            processing:     "Procesando...",
            lengthMenu:     "Mostrar _MENU_ registros",
            zeroRecords:    "No se encontraron resultados",
            emptyTable:     "Ningún dato disponible en esta tabla",
            info:           "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty:      "Mostrando 0 a 0 de 0 registros",
            infoFiltered:   "(filtrado de _MAX_ registros totales)",
            search:         "Buscar:",
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
            confirmButtonText: 'Sí, actualizar'
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
// MOSTRAR QR - VERSIÓN CORREGIDA
// ========================================
/*
 * SOLO reemplaza la función verQR() en tu FuncionarioLista.js
 * No toques nada más del archivo
 */

function verQR(rutaQR, idFuncionario) {

    if (!rutaQR) {
        Swal.fire('Error', 'Este funcionario no tiene código QR', 'error');
        return;
    }

    // rutaQR viene de la BD como: "qr/Qr_Func/QR-FUNC-109-6994d2df338f3.png"
    // window.location.pathname es: "/SEGTRACK/App/View/Administrador/FuncionarioListaADM.php"
    
    const pathname = window.location.pathname;  // "/SEGTRACK/App/View/..."
    const partes   = pathname.split('/');        // ["", "SEGTRACK", "App", "View", ...]
    
    // Encuentra dónde está "SEGTRACK"
    const idx = partes.indexOf('SEGTRACK');
    
    let base;
    if (idx !== -1) {
        // Construye solo hasta SEGTRACK: "/SEGTRACK"
        base = '/' + partes.slice(1, idx + 1).join('/');
    } else {
        // Fallback: asume que está en la raíz
        base = '';
    }
    
    // Construye URL completa
    const rutaCompleta = window.location.origin + base + '/Public/' + rutaQR;
    
    console.log("✅ FuncionarioLista.js cargado");
    console.log("📂 Base URL:", window.location.origin + base);
    console.log("🖼️  Ruta QR completa:", rutaCompleta);
    
    $('#qrFuncionarioId').text(idFuncionario);
    $('#qrImagen').attr('src', rutaCompleta);
    $('#btnDescargarQR').attr('href', rutaCompleta);
    
    $('#modalVerQR').modal('show');
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

    // Bloqueados (no se editan)
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
        confirmButtonText: 'Sí, cambiar'
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