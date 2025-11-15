<<<<<<< HEAD
<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>
<?php require_once(__DIR__ . "/../../Core/conexion.php");?>

<?php
$conexion = new Conexion();
$conn = $conexion->getConexion();

// Construcción de filtros dinámicos
$filtros = [];
$params = [];

// FILTRO OBLIGATORIO: Solo mostrar dispositivos activos
$filtros[] = "d.Estado = :estado";
$params[':estado'] = 'Activo';

if (!empty($_GET['tipo'])) {
    $filtros[] = "d.TipoDispositivo = :tipo";
    $params[':tipo'] = $_GET['tipo'];
}
if (!empty($_GET['marca'])) {
    $filtros[] = "d.MarcaDispositivo LIKE :marca";
    $params[':marca'] = '%' . $_GET['marca'] . '%';
}
if (!empty($_GET['funcionario'])) {
    $filtros[] = "d.IdFuncionario = :funcionario";
    $params[':funcionario'] = $_GET['funcionario'];
}
if (!empty($_GET['visitante'])) {
    $filtros[] = "d.IdVisitante = :visitante";
    $params[':visitante'] = $_GET['visitante'];
}

$where = "WHERE " . implode(" AND ", $filtros);

$sql = "SELECT 
            d.*,
            f.NombreFuncionario,
            v.NombreVisitante
        FROM dispositivo d
        LEFT JOIN funcionario f ON d.IdFuncionario = f.IdFuncionario
        LEFT JOIN visitante v ON d.IdVisitante = v.IdVisitante
        $where 
        ORDER BY d.IdDispositivo DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
=======
<?php require_once __DIR__ . '/../Plantilla/parte_superior.php'; ?>
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-laptop me-2"></i>Dispositivos Registrados</h1>
<<<<<<< HEAD
        <a href="./Dispositivos.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
=======
        <a href="../View/Dispositivos.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
            <i class="fas fa-plus me-1"></i> Nuevo Dispositivo
        </a>
    </div>

<<<<<<< HEAD
=======
    <?php
    require_once __DIR__ . "/../Core/conexion.php";
    $conexionObj = new Conexion();
    $conn = $conexionObj->getConexion();

    // Construcción de filtros dinámicos
    $filtros = [];
    $params = [];

    if (!empty($_GET['tipo'])) {
        $filtros[] = "TipoDispositivo = :tipo";
        $params[':tipo'] = $_GET['tipo'];
    }
    if (!empty($_GET['marca'])) {
        $filtros[] = "MarcaDispositivo LIKE :marca";
        $params[':marca'] = '%' . $_GET['marca'] . '%';
    }
    if (!empty($_GET['funcionario'])) {
        $filtros[] = "IdFuncionario = :funcionario";
        $params[':funcionario'] = $_GET['funcionario'];
    }
    if (!empty($_GET['visitante'])) {
        $filtros[] = "IdVisitante = :visitante";
        $params[':visitante'] = $_GET['visitante'];
    }

    $where = "";
    if (count($filtros) > 0) {
        $where = "WHERE " . implode(" AND ", $filtros);
    }

    $sql = "SELECT * FROM dispositivo $where ORDER BY IdDispositivo DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Filtrar Dispositivos</h6>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label for="tipo" class="form-label">Tipo de Dispositivo</label>
                    <select name="tipo" id="tipo" class="form-select">
                        <option value="">Todos</option>
                        <option value="Portatil" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Portatil') ? 'selected' : '' ?>>Portátil</option>
                        <option value="Tablet" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Tablet') ? 'selected' : '' ?>>Tablet</option>
                        <option value="Computador" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Computador') ? 'selected' : '' ?>>Computador</option>
                        <option value="Otro" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Otro') ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="marca" class="form-label">Marca</label>
                    <input type="text" name="marca" id="marca" class="form-control" value="<?= $_GET['marca'] ?? '' ?>" placeholder="Buscar por marca">
                </div>
                <div class="col-md-2">
                    <label for="funcionario" class="form-label">ID Funcionario</label>
                    <input type="text" name="funcionario" id="funcionario" class="form-control" value="<?= $_GET['funcionario'] ?? '' ?>" placeholder="ID">
                </div>
                <div class="col-md-2">
                    <label for="visitante" class="form-label">ID Visitante</label>
                    <input type="text" name="visitante" id="visitante" class="form-control" value="<?= $_GET['visitante'] ?? '' ?>" placeholder="ID">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i> Filtrar</button>
                    <a href="Dispositivolista.php" class="btn btn-secondary"><i class="fas fa-broom me-1"></i> Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
<<<<<<< HEAD
            <h6 class="m-0 font-weight-bold text-primary">Lista de Dispositivos Activos</h6>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle text-center" id="TablaDispositivo">
                <thead class="table-dark">
                    <tr>
                        <th>QR</th>
                        <th>Tipo</th>
                        <th>Marca</th>
                        <th>Funcionario</th>
                        <th>Visitante</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && count($result) > 0) : ?>
                        <?php foreach ($result as $row) : ?>
                            <tr id="fila-<?php echo $row['IdDispositivo']; ?>">
                                <td class="text-center">
                                    <?php if (!empty($row['QrDispositivo'])) : ?>
                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                onclick="verQRDispositivo('<?php echo htmlspecialchars($row['QrDispositivo']); ?>', <?php echo $row['IdDispositivo']; ?>)"
                                                title="Ver código QR">
                                            <i class="fas fa-qrcode me-1"></i> Ver QR
                                        </button>
                                    <?php else : ?>
                                        <span class="badge badge-warning">Sin QR</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $row['TipoDispositivo']; ?></td>
                                <td><?php echo $row['MarcaDispositivo']; ?></td>
                                <td>
                                    <?php if (!empty($row['NombreFuncionario'])) : ?>
                                            <?php echo $row['NombreFuncionario']; ?>
                                        </span>
                                        
                                    <?php else : ?>
                                        <span class="badge bg-info text-white">No aplica</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['NombreVisitante'])) : ?>
                                            <?php echo $row['NombreVisitante']; ?>
                                        </span>
                                        
                                    <?php else : ?>
                                        <span class="badge bg-info text-white">No aplica</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick='cargarDatosEdicionDispositivo(<?php echo json_encode($row); ?>)'
                                            title="Editar dispositivo" data-toggle="modal" data-target="#modalEditarDispositivo">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-exclamation-circle fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No hay dispositivos activos registrados con los filtros seleccionados</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
=======
            <h6 class="m-0 font-weight-bold text-primary">Lista de Dispositivos</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>QR</th>
                            <th>Tipo</th>
                            <th>Marca</th>
                            <th>ID Funcionario</th>
                            <th>ID Visitante</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && count($result) > 0) : ?>
                            <?php foreach ($result as $row) : ?>
                                <tr id="fila-<?php echo $row['IdDispositivo']; ?>">
                                    <td><?php echo $row['IdDispositivo']; ?></td>
                                    <td class="text-center">
                                        <?php if ($row['QrDispositivo']) : ?>
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="verQR('<?php echo htmlspecialchars($row['QrDispositivo']); ?>', <?php echo $row['IdDispositivo']; ?>)"
                                                    title="Ver código QR">
                                                <i class="fas fa-qrcode me-1"></i> Ver QR
                                            </button>
                                        <?php else : ?>
                                            <span class="badge badge-warning">Sin QR</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $row['TipoDispositivo']; ?></td>
                                    <td><?php echo $row['MarcaDispositivo']; ?></td>
                                    <td><?php echo $row['IdFuncionario'] ?? '-'; ?></td>
                                    <td><?php echo $row['IdVisitante'] ?? '-'; ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="cargarDatosEdicion(<?php echo $row['IdDispositivo']; ?>, '<?php echo htmlspecialchars($row['QrDispositivo'] ?? ''); ?>', '<?php echo htmlspecialchars($row['TipoDispositivo']); ?>', '<?php echo htmlspecialchars($row['MarcaDispositivo']); ?>', <?php echo $row['IdFuncionario'] ?? 'null'; ?>, <?php echo $row['IdVisitante'] ?? 'null'; ?>)"
                                                title="Editar dispositivo" data-toggle="modal" data-target="#modalEditar">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-exclamation-circle fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">No hay dispositivos registrados con los filtros seleccionados</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
        </div>
    </div>
</div>

<<<<<<< HEAD
<!-- Modal para visualizar QR del Dispositivo -->
<div class="modal fade" id="modalVerQRDispositivo" tabindex="-1" role="dialog" aria-labelledby="modalVerQRDispositivoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalVerQRDispositivoLabel">
=======
<!-- ✅ Modal para visualizar QR -->
<div class="modal fade" id="modalVerQR" tabindex="-1" role="dialog" aria-labelledby="modalVerQRLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalVerQRLabel">
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
                    <i class="fas fa-qrcode me-2"></i>Código QR - Dispositivo #<span id="qrDispositivoId"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
<<<<<<< HEAD
                <img id="qrImagenDispositivo" src="" alt="Código QR Dispositivo" class="img-fluid" style="max-width: 300px; border: 2px solid #ddd; padding: 10px; border-radius: 5px;">
=======
                <img id="qrImagen" src="" alt="Código QR" class="img-fluid" style="max-width: 300px; border: 2px solid #ddd; padding: 10px; border-radius: 5px;">
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
                <p class="text-muted mt-3">Escanea este código con tu dispositivo móvil</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
<<<<<<< HEAD
                <a id="btnDescargarQRDispositivo" href="#" class="btn btn-success" download>
=======
                <a id="btnDescargarQR" href="#" class="btn btn-success" download>
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
                    <i class="fas fa-download me-1"></i> Descargar QR
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Dispositivo -->
<<<<<<< HEAD
<div class="modal fade" id="modalEditarDispositivo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Editar Dispositivo</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formEditarDispositivo">
                    <input type="hidden" id="editIdDispositivo" name="id">
                    <input type="hidden" id="editAccion" name="accion" value="actualizar">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo Dispositivo</label>
                            <select id="editTipoDispositivo" class="form-control" name="tipo" required>
=======
<div class="modal fade" id="modalEditar" tabindex="-1" role="dialog" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEditarLabel">Editar Dispositivo</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditar">
                    <input type="hidden" id="editId" name="id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editTipo" class="form-label">Tipo de Dispositivo</label>
                            <select id="editTipo" class="form-control" name="tipo" required>
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
                                <option value="">-- Seleccione un tipo --</option>
                                <option value="Portatil">Portátil</option>
                                <option value="Tablet">Tablet</option>
                                <option value="Computador">Computador</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
<<<<<<< HEAD
                            <label class="form-label">Marca <small class="text-muted">(Solo lectura)</small></label>
                            <input type="text" id="editMarcaDispositivo" class="form-control bg-light" name="marca" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Funcionario <small class="text-muted">(Solo lectura)</small></label>
                            <input type="text" id="editNombreFuncionario" class="form-control bg-light" readonly>
                            <input type="hidden" id="editIdFuncionario" name="id_funcionario">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Visitante <small class="text-muted">(Solo lectura)</small></label>
                            <input type="text" id="editNombreVisitante" class="form-control bg-light" readonly>
                            <input type="hidden" id="editIdVisitante" name="id_visitante">
=======
                            <label for="editMarca" class="form-label">Marca</label>
                            <input type="text" id="editMarca" class="form-control" name="marca" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editFuncionario" class="form-label">ID Funcionario</label>
                            <input type="number" id="editFuncionario" class="form-control" name="IdFuncionario">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editVisitante" class="form-label">ID Visitante</label>
                            <input type="number" id="editVisitante" class="form-control" name="IdVisitante">
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
<<<<<<< HEAD
                <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" id="btnGuardarCambiosDispositivo">Guardar Cambios</button>
=======
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarCambios">Guardar Cambios</button>
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
            </div>
        </div>
    </div>
</div>

<<<<<<< HEAD
<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>

<script>
// ============================================
// 🔥 ZONA DATATABLES - Activación de DataTable
// ============================================
$(document).ready(function() {
    $('#TablaDispositivo').DataTable({
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        },
        pageLength: 10,
        responsive: true,
        order: [[0, "desc"]]
    });
});

// ============================================
// Función para mostrar QR del dispositivo
// ============================================
function verQRDispositivo(rutaQR, idDispositivo) {
    var rutaCompleta = '/SEGTRACK/Public/' + rutaQR;
    
    console.log('Ruta QR completa:', rutaCompleta);
    
    $('#qrDispositivoId').text(idDispositivo);
    $('#qrImagenDispositivo').attr('src', rutaCompleta);
    $('#btnDescargarQRDispositivo').attr('href', rutaCompleta).attr('download', 'QR-Dispositivo-' + idDispositivo + '.png');
    
    $('#modalVerQRDispositivo').modal('show');
}

// ============================================
// Cargar datos en el modal de edición
// ============================================
function cargarDatosEdicionDispositivo(row) {
    console.log('Cargando datos para editar:', row);
    
    $('#editIdDispositivo').val(row.IdDispositivo);
    $('#editTipoDispositivo').val(row.TipoDispositivo);
    $('#editMarcaDispositivo').val(row.MarcaDispositivo);
    
    // IDs ocultos
    $('#editIdFuncionario').val(row.IdFuncionario || '');
    $('#editIdVisitante').val(row.IdVisitante || '');
    
    // Mostrar nombres en campos de texto
    $('#editNombreFuncionario').val(row.NombreFuncionario || '-');
    $('#editNombreVisitante').val(row.NombreVisitante || '-');
    
    $('#modalEditarDispositivo').modal('show');
}

// ============================================
// Botón guardar cambios
// ============================================
$(document).ready(function() {
    $('#btnGuardarCambiosDispositivo').click(function() {
        var formData = {
            accion: 'actualizar',
            id: $('#editIdDispositivo').val(),
            tipo: $('#editTipoDispositivo').val(),
            marca: $('#editMarcaDispositivo').val(),
            id_funcionario: $('#editIdFuncionario').val(),
            id_visitante: $('#editIdVisitante').val()
        };

        console.log('Enviando datos:', formData);

        // Validar campo obligatorio (solo Tipo)
        if (!formData.tipo) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo incompleto',
                text: 'Debe seleccionar un tipo de dispositivo'
            });
            return;
        }

        // Cerrar modal
        $('#modalEditarDispositivo').modal('hide');

        // Mostrar loading
        Swal.fire({
            title: 'Guardando...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '../../Controller/ControladorDispositivo.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta:', response);
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'Dispositivo actualizado correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Error al actualizar el dispositivo'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.log('Error:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor'
                });
            }
        });
    });
});
</script>
=======
<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../js/javascript/demo/sb-admin-2.min.js"></script>

<script>
let dispositivoIdAEditar = null;

// ✅ Función para mostrar QR
function verQR(rutaQR, idDispositivo) {
    const rutaCompleta = '../' + rutaQR;
    
    $('#qrDispositivoId').text(idDispositivo);
    $('#qrImagen').attr('src', rutaCompleta);
    $('#btnDescargarQR').attr('href', rutaCompleta).attr('download', 'QR-Dispositivo-' + idDispositivo + '.png');
    
    $('#modalVerQR').modal('show');
}

function cargarDatosEdicion(id, qr, tipo, marca, idFuncionario, idVisitante) {
    dispositivoIdAEditar = id;
    $('#editId').val(id);
    $('#editTipo').val(tipo);
    $('#editMarca').val(marca);
    $('#editFuncionario').val(idFuncionario);
    $('#editVisitante').val(idVisitante);
}

$('#btnGuardarCambios').click(function() {
    const formData = {
        accion: 'actualizar',
        id: $('#editId').val(),
        tipo: $('#editTipo').val(),
        marca: $('#editMarca').val(),
        id_funcionario: $('#editFuncionario').val(),
        id_visitante: $('#editVisitante').val()
    };
    if (!formData.tipo || !formData.marca) {
        alert('Por favor, complete todos los campos obligatorios');
        return;
    }
    $.ajax({
        url: '../Controller/parqueadero_dispositivo/ControladorDispositivo.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            $('#modalEditar').modal('hide');
            if (response.success) {
                alert('Dispositivo actualizado correctamente');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            $('#modalEditar').modal('hide');
            alert('Error al intentar actualizar el dispositivo');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../Plantilla/parte_inferior.php'; ?>
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
