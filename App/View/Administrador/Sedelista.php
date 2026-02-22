<?php
require_once __DIR__ . '/../layouts/parte_superior_administrador.php';
require_once __DIR__ . '/../../Model/modelosede.php';

$modeloSede = new ModeloSede();
$sedes = $modeloSede->obtenerSedes();
?>

<div class="container-fluid px-4 py-4">

    <!-- TÍTULO -->
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
                            <td><?= htmlspecialchars($fila['TipoSede']); ?></td>
                            <td><?= htmlspecialchars($fila['Ciudad']); ?></td>
                            <td><?= htmlspecialchars($fila['NombreInstitucion']); ?></td>

                            <!-- ESTADO -->
                            <td>
                                <?php if ($fila['Estado'] === 'Activo'): ?>
                                    <span class="badge bg-success px-3 py-2 estado-badge">Activo</span>
                                <?php else: ?>
                                    <span class="badge px-3 py-2 estado-badge" style="background-color:#60a5fa;">Inactivo</span>
                                <?php endif; ?>
                            </td>

                            <!-- ACCIONES -->
                            <td>
                                <div class="d-flex justify-content-center align-items-center" style="gap:6px;">

                                    <!-- BOTÓN EDITAR -->
                                    <button class="btn btn-sm btn-editar"
                                            style="width:45px;height:45px;
                                                   display:inline-flex;
                                                   align-items:center;
                                                   justify-content:center;
                                                   border-radius:8px;
                                                   border:1.5px solid #3b82f6;
                                                   background:#e0edff;"
                                            data-id="<?= $fila['IdSede']; ?>"
                                            title="Editar">
                                        <i class="fas fa-edit text-primary" style="font-size:16px;"></i>
                                    </button>

                                    <!-- BOTÓN ESTADO -->
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
                                            <i class="fas fa-lock text-warning" style="font-size:16px;"></i>
                                        <?php else: ?>
                                            <i class="fas fa-unlock text-success" style="font-size:16px;"></i>
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

<!-- Botón oculto para abrir modal con Bootstrap 4 nativo -->
<button id="btnTriggerModal"
        data-toggle="modal"
        data-target="#modalEditarSede"
        style="display:none;"></button>

<!-- MODAL EDITAR SEDE -->
<div class="modal fade" id="modalEditarSede" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
            <i class="fas fa-edit mr-2"></i>Editar Sede
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">

        <input type="hidden" id="editIdSede">

        <div class="mb-3">
            <label class="form-label fw-semibold">Tipo de Sede</label>
            <input type="text"
                   id="editTipoSede"
                   class="form-control"
                   maxlength="30"
                   placeholder="Ej: Principal"
                   autocomplete="off">
            <div class="invalid-feedback" id="errorTipoSede"></div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Ciudad</label>
            <input type="text"
                   id="editCiudad"
                   class="form-control"
                   maxlength="30"
                   placeholder="Ej: Bogotá"
                   autocomplete="off">
            <div class="invalid-feedback" id="errorCiudad"></div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Institución</label>
            <select id="editInstitucion" class="form-control">
                <?php
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
        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i>Cancelar
        </button>
        <button type="button" class="btn btn-primary" id="btnGuardarEdicion">
            <i class="fas fa-save mr-1"></i>Guardar Cambios
        </button>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior_administrador.php'; ?>

<!-- DATATABLES -->
<link rel="stylesheet" href="../../../Public/vendor/datatables/dataTables.bootstrap4.css">
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="../../../Public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../../../Public/vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../../Public/js/javascript/js/ValidacionSedeLista.js"></script>