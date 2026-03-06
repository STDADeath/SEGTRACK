<?php
/**
 * VISTA: LISTA DE SEDES
 * Capa Vista (MVC): solo presenta datos, sin lógica de negocio.
 * Instancia el modelo directamente para cargar datos iniciales.
 */
require_once __DIR__ . '/../layouts/parte_superior_administrador.php';
require_once __DIR__ . '/../../Model/modelosede.php';

// Instancia del modelo para obtener sedes e instituciones al cargar la página
$modeloSede = new ModeloSede();
$sedes      = $modeloSede->obtenerSedes();
?>

<div class="container-fluid px-4 py-4">

    <!-- ENCABEZADO: título + botón registrar nueva sede -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary">
            <i class="fas fa-building me-2"></i>Lista de Sedes
        </h4>
        <a href="Sede.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Registrar
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">

                <!-- DataTables la inicializa por id="tablaSedes" en el JS -->
                <table id="tablaSedes"
                       class="table table-bordered table-hover table-striped align-middle text-center"
                       width="100%">

                    <thead class="table-dark">
                        <tr>
                            <th>Tipo</th>
                            <th>Ciudad</th>
                            <th>Institución</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($sedes as $fila): ?>
                        <tr>
                            <!-- htmlspecialchars previene XSS en todos los campos mostrados -->
                            <td><?= htmlspecialchars($fila['TipoSede']); ?></td>
                            <td><?= htmlspecialchars($fila['Ciudad']); ?></td>
                            <td><?= htmlspecialchars($fila['NombreInstitucion']); ?></td>

                            <!-- ESTADO: badge verde Activo / azul Inactivo con texto blanco -->
                            <td>
                                <?php if ($fila['Estado'] === 'Activo'): ?>
                                    <span class="badge bg-success text-white estado-badge">
                                        Activo
                                    </span>
                                <?php else: ?>
                                    <span class="badge text-white estado-badge"
                                          style="background-color:#60a5fa;">
                                        Inactivo
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- ACCIONES: botón editar + candado cambiar estado -->
                            <td>
                                <div class="d-flex justify-content-center align-items-center"
                                     style="gap:6px;">

                                    <!-- EDITAR: abre modal con los datos de esta fila via AJAX -->
                                    <button class="btn btn-sm btn-editar"
                                            style="width:45px;height:45px;
                                                   display:inline-flex;
                                                   align-items:center;
                                                   justify-content:center;
                                                   border-radius:8px;
                                                   border:1.5px solid #3b82f6;
                                                   background:#e0edff;"
                                            data-id="<?= $fila['IdSede']; ?>"
                                            title="Editar sede">
                                        <i class="fas fa-edit text-primary"
                                           style="font-size:16px;"></i>
                                    </button>

                                    <!-- CANDADO: amarillo (desactivar) si Activo,
                                                 verde (activar) si Inactivo -->
                                    <button class="btn btn-sm btn-estado"
                                            style="width:45px;height:45px;
                                                   display:inline-flex;
                                                   align-items:center;
                                                   justify-content:center;
                                                   border-radius:8px;
                                                   border:1.5px solid #d4af37;
                                                   background:#fff8dc;"
                                            data-id="<?= $fila['IdSede']; ?>"
                                            title="Cambiar Estado">
                                        <?php if ($fila['Estado'] === 'Activo'): ?>
                                            <i class="fas fa-lock text-warning"
                                               style="font-size:16px;"></i>
                                        <?php else: ?>
                                            <i class="fas fa-unlock text-success"
                                               style="font-size:16px;"></i>
                                        <?php endif; ?>
                                    </button>

                                </div>
                            </td>

                        </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>

            </div>
        </div>
    </div>
</div>

<!-- Botón oculto trigger para abrir modal Bootstrap 4 programáticamente -->
<button id="btnTriggerModal"
        data-toggle="modal"
        data-target="#modalEditarSede"
        style="display:none;"></button>

<!-- ══════════════════════════════════════════════════════
     MODAL EDITAR SEDE
     Se abre desde el JS al hacer clic en btn-editar.
     Los campos se precargan via AJAX con los datos de la fila.
══════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalEditarSede" tabindex="-1"
     role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <!-- Header azul igual al módulo de instituciones -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-2"></i>Editar Sede
                </h5>
                <button type="button" class="close text-white"
                        data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <!-- ID oculto para identificar la sede a actualizar en el AJAX -->
                <input type="hidden" id="editIdSede">

                <!-- Campo: Tipo de Sede — solo letras, validado en tiempo real -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Tipo de Sede <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="editTipoSede"
                           class="form-control"
                           maxlength="30"
                           placeholder="Ej: Principal"
                           autocomplete="off">
                    <!-- Mensaje de error inline que se activa desde el JS -->
                    <div class="invalid-feedback" id="errorTipoSede"></div>
                    <small class="text-muted">Solo letras y espacios, sin números.</small>
                </div>

                <!-- Campo: Ciudad — solo letras, validado en tiempo real -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Ciudad <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="editCiudad"
                           class="form-control"
                           maxlength="30"
                           placeholder="Ej: Bogotá"
                           autocomplete="off">
                    <!-- Mensaje de error inline que se activa desde el JS -->
                    <div class="invalid-feedback" id="errorCiudad"></div>
                    <small class="text-muted">Solo letras y espacios, sin números.</small>
                </div>

                <!-- Campo: Institución — select cargado desde el modelo PHP -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Institución <span class="text-danger">*</span>
                    </label>
                    <select id="editInstitucion" class="form-control">
                        <?php
                        // Carga las instituciones activas para el select del modal
                        $instituciones = $modeloSede->obtenerInstituciones();
                        foreach ($instituciones as $inst): ?>
                            <option value="<?= $inst['IdInstitucion']; ?>">
                                <?= htmlspecialchars($inst['NombreInstitucion']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <div class="modal-footer d-flex flex-row justify-content-end">
                <!-- Cancelar: cierra modal sin guardar -->
                <button type="button" class="btn btn-secondary mr-2"
                        data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cancelar
                </button>
                <!-- Guardar: dispara el AJAX de edición en el JS -->
                <button type="button" class="btn btn-primary"
                        id="btnGuardarEdicion">
                    <i class="fas fa-save mr-1"></i>Guardar Cambios
                </button>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior_administrador.php'; ?>

<!-- CSS DataTables con Bootstrap 4 -->
<link rel="stylesheet"
      href="../../../Public/vendor/datatables/dataTables.bootstrap4.css">

<!-- jQuery: base para DataTables y scripts personalizados -->
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<!-- DataTables core -->
<script src="../../../Public/vendor/datatables/jquery.dataTables.min.js"></script>
<!-- DataTables integración Bootstrap 4 -->
<script src="../../../Public/vendor/datatables/dataTables.bootstrap4.min.js"></script>
<!-- SweetAlert2: alertas modales elegantes -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Variable global: PHP calcula la ruta al controlador desde su ubicación
     El JS la lee sin importar desde qué carpeta se cargue el archivo .js -->
<script>
    window.urlControladorSede = "<?= '../../Controller/ControladorSede.php' ?>";
</script>

<!-- Script principal de la lista — siempre al final -->
<script src="../../../Public/js/javascript/js/ValidacionSedeLista.js"></script>