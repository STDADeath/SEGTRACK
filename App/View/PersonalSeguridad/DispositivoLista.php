<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<style>
/* Fondo oscuro del modal */
.modal-backdrop-custom {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1040;
}

.modal.show {
    z-index: 1050;
}
</style>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-laptop me-2"></i>Dispositivos Registrados</h1>
        <a href="./Dispositivos.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Dispositivo
        </a>
    </div>

    <?php
    require_once __DIR__ . "/../../Core/conexion.php";
    $conexionObj = new Conexion();
    $conn = $conexionObj->getConexion();

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
                                            <button type="button" class="btn btn-sm btn-outline-success btn-ver-qr"
                                                    data-ruta="<?php echo htmlspecialchars($row['QrDispositivo']); ?>"
                                                    data-id="<?php echo $row['IdDispositivo']; ?>"
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
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-editar"
                                                data-id="<?php echo $row['IdDispositivo']; ?>"
                                                data-tipo="<?php echo htmlspecialchars($row['TipoDispositivo']); ?>"
                                                data-marca="<?php echo htmlspecialchars($row['MarcaDispositivo']); ?>"
                                                data-funcionario="<?php echo $row['IdFuncionario'] ?? ''; ?>"
                                                data-visitante="<?php echo $row['IdVisitante'] ?? ''; ?>"
                                                title="Editar dispositivo">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-exclamation-circle fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">No hay dispositivos registrados</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para visualizar QR -->
<div class="modal fade" id="modalVerQR" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-qrcode me-2"></i>Código QR - Dispositivo #<span id="qrDispositivoId"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="qrImagen" src="" alt="Código QR" class="img-fluid" style="max-width: 300px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <a id="btnDescargarQR" href="#" class="btn btn-success" download>
                    <i class="fas fa-download me-1"></i> Descargar
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Dispositivo -->
<div class="modal fade" id="modalEditar" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Editar Dispositivo #<span id="editIdDisplay"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editId">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="editTipo" class="form-label">Tipo de Dispositivo</label>
                        <select id="editTipo" class="form-control" required>
                            <option value="">-- Seleccione --</option>
                            <option value="Portatil">Portátil</option>
                            <option value="Tablet">Tablet</option>
                            <option value="Computador">Computador</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="editMarca" class="form-label">Marca</label>
                        <input type="text" id="editMarca" class="form-control" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="editFuncionario" class="form-label">ID Funcionario</label>
                        <input type="number" id="editFuncionario" class="form-control" min="1">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="editVisitante" class="form-label">ID Visitante</label>
                        <input type="number" id="editVisitante" class="form-control" min="1">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarCambios">
                    <i class="fas fa-save me-1"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>

<!-- SCRIPT AL FINAL, DESPUÉS DE CARGAR jQuery y Bootstrap -->
<script>
(function() {
    'use strict';
    
    // Esperar a que todo esté cargado
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM cargado');
        
        // Función para abrir modal con fondo
        function abrirModal(modalId) {
            var modal = document.getElementById(modalId);
            
            // Crear backdrop
            var backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop-custom';
            backdrop.id = 'backdrop-' + modalId;
            document.body.appendChild(backdrop);
            
            // Mostrar modal
            modal.classList.add('show');
            modal.style.display = 'block';
            document.body.classList.add('modal-open');
        }
        
        // Función para cerrar modal
        function cerrarModal(modalId) {
            var modal = document.getElementById(modalId);
            var backdrop = document.getElementById('backdrop-' + modalId);
            
            modal.classList.remove('show');
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
            
            if (backdrop) {
                backdrop.remove();
            }
        }
        
        // ========== VER QR ==========
        var botonesQR = document.querySelectorAll('.btn-ver-qr');
        botonesQR.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var ruta = this.getAttribute('data-ruta');
                var id = this.getAttribute('data-id');
                var rutaCompleta = '/SEGTRACK/Public/' + ruta;
                
                document.getElementById('qrDispositivoId').textContent = id;
                document.getElementById('qrImagen').src = rutaCompleta;
                document.getElementById('btnDescargarQR').href = rutaCompleta;
                
                abrirModal('modalVerQR');
            });
        });
        
        // ========== EDITAR ==========
        var botonesEditar = document.querySelectorAll('.btn-editar');
        console.log('Botones editar encontrados:', botonesEditar.length);
        
        botonesEditar.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.getAttribute('data-id');
                var tipo = this.getAttribute('data-tipo');
                var marca = this.getAttribute('data-marca');
                var funcionario = this.getAttribute('data-funcionario');
                var visitante = this.getAttribute('data-visitante');
                
                console.log('=== Editando dispositivo ===');
                console.log('ID:', id);
                console.log('Tipo:', tipo);
                console.log('Marca:', marca);
                
                document.getElementById('editId').value = id;
                document.getElementById('editIdDisplay').textContent = id;
                document.getElementById('editTipo').value = tipo;
                document.getElementById('editMarca').value = marca;
                document.getElementById('editFuncionario').value = funcionario || '';
                document.getElementById('editVisitante').value = visitante || '';
                
                abrirModal('modalEditar');
            });
        });
        
        // ========== CERRAR MODALES ==========
        var botonesCerrar = document.querySelectorAll('[data-dismiss="modal"]');
        botonesCerrar.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var modal = this.closest('.modal');
                cerrarModal(modal.id);
            });
        });
        
        // ========== GUARDAR CAMBIOS ==========
        var btnGuardar = document.getElementById('btnGuardarCambios');
        if (btnGuardar) {
            btnGuardar.addEventListener('click', function() {
                var id = document.getElementById('editId').value;
                var tipo = document.getElementById('editTipo').value;
                var marca = document.getElementById('editMarca').value;
                var funcionario = document.getElementById('editFuncionario').value;
                var visitante = document.getElementById('editVisitante').value;
                
                console.log('=== Guardando ===');
                console.log('ID:', id);
                console.log('Tipo:', tipo);
                console.log('Marca:', marca);
                console.log('Funcionario:', funcionario);
                console.log('Visitante:', visitante);
                
                if (!id) {
                    alert('Error: ID no válido');
                    return;
                }
                if (!tipo) {
                    alert('Seleccione un tipo de dispositivo');
                    return;
                }
                if (!marca || marca.trim() === '') {
                    alert('Ingrese la marca del dispositivo');
                    return;
                }
                
                // Deshabilitar botón
                btnGuardar.disabled = true;
                btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                
                // Crear FormData
                var formData = new FormData();
                formData.append('accion', 'actualizar');
                formData.append('id', id);
                formData.append('tipo', tipo);
                formData.append('marca', marca);
                formData.append('id_funcionario', funcionario);
                formData.append('id_visitante', visitante);
                
                // Enviar
                fetch('../../Controller/ControladorDispositivo.php', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    console.log('Respuesta:', data);
                    
                    btnGuardar.disabled = false;
                    btnGuardar.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Cambios';
                    
                    if (data.success) {
                        alert('Dispositivo actualizado correctamente');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Error desconocido'));
                    }
                })
                .catch(function(error) {
                    console.log('Error:', error);
                    btnGuardar.disabled = false;
                    btnGuardar.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Cambios';
                    alert('Error de conexión');
                });
            });
        }
    });
})();
</script>