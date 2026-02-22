<?php
/**
 * VISTA: LISTA DE INSTITUCIONES
 */
require_once __DIR__ . '/../layouts/parte_superior_administrador.php';
require_once __DIR__ . '/../../Core/Conexion.php';
?>
<div class="container-fluid px-4 py-4">

    <!-- TÍTULO -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-school me-2"></i>Instituciones Registradas
        </h1>
        <a href="Instituto.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Registrar
        </a>
    </div>

    <!-- CARD TABLA -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0 text-primary fw-bold">Lista de Instituciones</h5>
        </div>
        <div class="card-body table-responsive">
            <table id="tablaInstitutos"
                   class="table table-hover align-middle"
                   width="100%">

                <thead class="text-white" style="background-color:#5f636e;">
                    <tr>
                        <th>Nombre</th>
                        <th>NIT</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    try {
                        $conexion = new Conexion();
                        $db       = $conexion->getConexion();
                        $sql      = "SELECT * FROM institucion ORDER BY IdInstitucion DESC";
                        $stmt     = $db->prepare($sql);
                        $stmt->execute();

                        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)):
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($fila['NombreInstitucion']); ?></td>
                            <td><?= htmlspecialchars($fila['Nit_Codigo']); ?></td>
                            <td><?= htmlspecialchars($fila['TipoInstitucion']); ?></td>

                            <!-- ESTADO -->
                            <td class="text-center">
                                <?php if ($fila['EstadoInstitucion'] === 'Activo'): ?>
                                    <span id="badge-estado-<?= $fila['IdInstitucion'] ?>"
                                          class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Activo
                                    </span>
                                <?php else: ?>
                                    <span id="badge-estado-<?= $fila['IdInstitucion'] ?>"
                                          class="badge bg-primary">
                                        <i class="fas fa-lock"></i> Inactivo
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- ACCIONES -->
                            <td>
                                <div class="d-flex gap-2">

                                    <!-- EDITAR — abre modal igual que funcionarios -->
                                    <button class="btn btn-outline-primary btn-accion"
                                            title="Editar institución"
                                            onclick='abrirModalEditar(
                                                <?= $fila["IdInstitucion"] ?>,
                                                <?= json_encode($fila["NombreInstitucion"]) ?>,
                                                <?= json_encode($fila["Nit_Codigo"]) ?>,
                                                <?= json_encode($fila["TipoInstitucion"]) ?>
                                            )'>
                                        <i class="fas fa-edit text-primary"></i>
                                    </button>

                                    <!-- TOGGLE ESTADO — candado igual que funcionarios -->
                                    <?php if ($fila['EstadoInstitucion'] === 'Activo'): ?>
                                        <button id="btn-estado-<?= $fila['IdInstitucion'] ?>"
                                                class="btn btn-outline-warning btn-accion btn-toggle-estado"
                                                title="Desactivar institución"
                                                data-id="<?= $fila['IdInstitucion'] ?>"
                                                data-estado="<?= $fila['EstadoInstitucion'] ?>">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    <?php else: ?>
                                        <button id="btn-estado-<?= $fila['IdInstitucion'] ?>"
                                                class="btn btn-outline-success btn-accion btn-toggle-estado"
                                                title="Activar institución"
                                                data-id="<?= $fila['IdInstitucion'] ?>"
                                                data-estado="<?= $fila['EstadoInstitucion'] ?>">
                                            <i class="fas fa-lock-open"></i>
                                        </button>
                                    <?php endif; ?>

                                </div>
                            </td>
                        </tr>
                    <?php
                        endwhile;
                    } catch (PDOException $e) {
                        echo '<tr><td colspan="5" class="text-center py-4 text-danger">
                                Error al cargar los datos
                              </td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════
     MODAL EDITAR INSTITUCIÓN
     Idéntico al modal de funcionarios
═══════════════════════════════════════════════ -->
<div class="modal fade" id="modalEditarInstituto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!-- Header azul igual que funcionarios -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Editar Institución
                </h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formEditarInstituto">
                    <input type="hidden" id="editIdInstituto">

                    <div class="row">
                        <!-- Nombre -->
                        <div class="col-md-8 mb-3">
                            <label class="form-label">
                                Nombre de la Institución <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="editNombreInstituto"
                                   class="form-control" required
                                   placeholder="Nombre completo">
                        </div>

                        <!-- Tipo -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                Tipo <span class="text-danger">*</span>
                            </label>
                            <select id="editTipoInstituto" class="form-control" required>
                                <option value="">Seleccione un tipo</option>
                                <option value="Universidad">Universidad</option>
                                <option value="Colegio">Colegio</option>
                                <option value="Empresa">Empresa</option>
                                <option value="ONG">ONG</option>
                                <option value="Hospital">Hospital</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <!-- NIT (solo lectura como Documento en funcionarios) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIT / Código</label>
                            <input type="text" id="editNitInstituto"
                                   class="form-control" readonly>
                        </div>
                    </div>

                    <!-- Alerta informativa igual que funcionarios -->
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        El NIT no puede modificarse. Si necesitas cambiarlo, registra una nueva institución.
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm"
                        data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary btn-sm"
                        id="btnGuardarInstituto">
                    <i class="fas fa-save me-1"></i> Guardar Cambios
                </button>
            </div>

        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/parte_inferior_administrador.php'; ?>
<!-- JS -->
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- TU JS SIEMPRE AL FINAL -->
<script src="../../../Public/js/javascript/js/ValidacionListainstituto.js"></script>

