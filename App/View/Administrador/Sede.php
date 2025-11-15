<?php
require_once __DIR__ . '/../Plantilla/parte_superior.php';
require_once __DIR__ . "/../Controller/Sede_institucion_funcionario_usuario/Controladorsede.php";

$controlador = new ControladorSede();
$instituciones = $controlador->obtenerInstituciones(); // Carga las instituciones al inicio

?>

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

            <form id="formRegistrarSede">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre / Tipo de Sede</label>
                        <input type="text" name="TipoSede" class="form-control border-primary" placeholder="Ej: Sede Norte" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ciudad</label>
                        <input type="text" name="Ciudad" class="form-control border-primary" placeholder="Ej: Bogotá" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Institución Asociada</label>
                    <select name="IdInstitucion" class="form-control border-primary" required>
                        <option value="">Seleccione...</option>

                        <?php foreach ($instituciones as $inst): ?>
                            <option value="<?= $inst['IdInstitucion'] ?>">
                                <?= $inst['NombreInstitucion'] ?>
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

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="../js/javascript/js/sede.js"></script>

<?php require_once __DIR__ . '/../Plantilla/parte_inferior.php'; ?>