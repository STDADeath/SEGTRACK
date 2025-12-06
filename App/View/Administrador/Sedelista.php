<?php require_once __DIR__ . '/../layouts/parte_superior_administrador.php'; ?>

<div class="container-fluid px-4 py-4">

    <!-- ENCABEZADO -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-building me-2"></i>Lista de Sedes
        </h1>
        <a href="./Sede.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nueva Sede
        </a>
    </div>

    <!-- ==============================
         📌 CARGAR DATOS
    ================================= -->
    <?php
    require_once __DIR__ . "../../../Core/conexion.php";
    require_once __DIR__ . "../../../Controller/ControladorSede.php";

    $conexionObj = new Conexion();
    $conn = $conexionObj->getConexion();

    $controlador = new ControladorSede();

    // Consulta de todas las sedes con su institución
    $sql = "SELECT sede.IdSede, sede.TipoSede, sede.Ciudad, sede.IdInstitucion,
            institucion.NombreInstitucion
            FROM sede 
            LEFT JOIN institucion ON sede.IdInstitucion = institucion.IdInstitucion
            ORDER BY sede.IdSede DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $listaInstituciones = $controlador->obtenerInstituciones();
    ?>

    <!-- ==============================
         📋 TABLA SIN ID (usando data-id)
    ================================= -->
    <div class="card shadow mb-4">

        <div class="card-header bg-light py-3">
            <h6 class="m-0 font-weight-bold text-primary">Listado de Sedes Registradas</h6>
        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table id="tablaSedes" class="table table-bordered table-hover" style="width:100%">

                    <thead class="thead-dark">
                        <tr>
                            <th>Tipo</th>
                            <th>Ciudad</th>
                            <th>Institución</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($result): ?>
                            <?php foreach ($result as $row): ?>
                                <tr data-id="<?= $row['IdSede']; ?>" data-institucion="<?= $row['IdInstitucion']; ?>">

                                    <td><?= htmlspecialchars($row['TipoSede']); ?></td>
                                    <td><?= htmlspecialchars($row['Ciudad']); ?></td>
                                    <td><?= $row['NombreInstitucion'] ?? 'Sin institución'; ?></td>

                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary btn-editar">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>

</div>

<!-- ====================================
     ✨ MODAL EDITAR SEDE
====================================== -->
<div class="modal fade" id="modalEditar" tabindex="-1" role="dialog">

    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Editar Sede</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="formEditar">

                    <!-- ID oculto -->
                    <input type="hidden" id="editId">

                    <div class="mb-3">
                        <label class="form-label">Tipo de Sede</label>
                        <input type="text" id="editTipo" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ciudad</label>
                        <input type="text" id="editCiudad" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Institución</label>
                        <select id="editInstitucion" class="form-control">
                            <?php foreach ($listaInstituciones as $inst): ?>
                                <option value="<?= $inst['IdInstitucion']; ?>">
                                    <?= $inst['NombreInstitucion']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" id="btnGuardar">Guardar Cambios</button>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior_administrador.php'; ?>


<!-- ==============================
     📦 DATATABLES CSS
============================== -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">

<!-- ==============================
     📦 DATATABLES JS
============================== -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
/* ==========================================================
   📌 INICIALIZAR DATATABLES (SIN PDF NI EXCEL)
========================================================== */
$(document).ready(function() {
    $('#tablaSedes').DataTable({
        responsive: true,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        dom: 'Bfrtip',
        buttons: [
            { extend: 'copy', className: 'btn btn-secondary btn-sm', text: 'Copiar' },
            { extend: 'print', className: 'btn btn-info btn-sm', text: 'Imprimir' }
        ],
        pageLength: 10,
        lengthMenu: [[5,10,25,50,-1],[5,10,25,50,'Todos']],
    });
});

/* ==========================================================
   📌 Cargar datos al hacer clic en Editar
========================================================== */
$(document).on('click', '.btn-editar', function () {

    const fila = $(this).closest('tr');
    const id = fila.data('id');

    const tipo = fila.find('td:eq(0)').text();
    const ciudad = fila.find('td:eq(1)').text();
    const institucion = fila.data('institucion');

    $('#editId').val(id);
    $('#editTipo').val(tipo);
    $('#editCiudad').val(ciudad);
    $('#editInstitucion').val(institucion);

    $('#modalEditar').modal('show');
});

/* ==========================================================
   📌 Guardar cambios por AJAX
========================================================== */
$('#btnGuardar').click(function () {

    const data = {
        accion: 'editar',
        IdSede: $('#editId').val(),
        TipoSede: $('#editTipo').val(),
        Ciudad: $('#editCiudad').val(),
        IdInstitucion: $('#editInstitucion').val()
    };

    $.ajax({
        url: '../../Controller/ControladorSede.php',
        type: 'POST',
        data: data,
        dataType: 'json',

        success: function (response) {
            $('#modalEditar').modal('hide');

            Swal.fire({
                icon: response.success ? 'success' : 'error',
                title: response.success ? '¡Éxito!' : 'Error',
                text: response.message,
            }).then(() => { location.reload(); });
        },

        error: function () {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error en la solicitud',
            });
        }
    });
});
</script>
