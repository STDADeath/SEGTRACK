<?php
// App/View/Administrador/Sede.php

session_start();

require_once __DIR__ . '/../layouts/parte_superior.php';
// ATENCIÓN: Esta inclusión es SOLO para cargar las Instituciones en el <select>
// La acción de REGISTRO POST (submit) se maneja directamente desde AJAX en ValidacionesSede.js
require_once __DIR__ . "/../../Controller/ControladorSede.php"; 

$controlador = new ControladorSede();
$instituciones = $controlador->obtenerInstituciones();
?>

<!-- Nota: Ya no se requieren las clases .input-valid/.label-valid en esta vista, 
porque el JS ahora aplicará el estilo directamente (inline). -->

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-building me-2"></i>Registrar Sede</h1>
        <a href="SedeLista.php" class="btn btn-primary btn-sm">
            <i class="fas fa-list me-1"></i> Ver Sedes
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Formulario de registro</h6>
        </div>

        <div class="card-body">
            <!-- El método POST se omite, el envío es 100% AJAX -->
            <form id="formRegistrarSede"> 

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label for="TipoSede" class="form-label">Nombre / Tipo de Sede *</label>
                        <input type="text" id="TipoSede" name="TipoSede"
                            maxlength="30"
                            class="form-control border-primary shadow-sm"
                            placeholder="Ej: Sede Norte">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="Ciudad" class="form-label">Ciudad *</label>
                        <input type="text" id="Ciudad" name="Ciudad"
                            maxlength="30"
                            class="form-control border-primary shadow-sm"
                            placeholder="Ej: Bogotá">
                    </div>

                </div>

                <div class="mb-3">
                    <label for="IdInstitucion" class="form-label">Institución Asociada *</label>
                    <select name="IdInstitucion" id="IdInstitucion"
                        class="form-control border-primary shadow-sm">
                        <option value="">Seleccione...</option>

                        <?php foreach ($instituciones as $inst): ?>
                            <option value="<?= htmlspecialchars($inst['IdInstitucion']) ?>">
                                <?= htmlspecialchars($inst['NombreInstitucion']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Registrar Sede
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../../Public/js/javascript/js/ValidacionesSede.js"></script>

