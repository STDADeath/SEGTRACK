<?php
// App/View/Administrador/Sede.php

session_start();

require_once __DIR__ . '/../layouts/parte_superior_administrador.php';
require_once __DIR__ . "/../../Controller/ControladorSede.php"; 

$controlador = new ControladorSede();

// ----------------------------------------------------------------------
// 🚩 LÓGICA AGREGADA PARA MANEJAR REGISTRO Y EDICIÓN
// ----------------------------------------------------------------------
$sedeData = [
    'IdSede' => 0,
    'TipoSede' => '',
    'Ciudad' => '',
    'IdInstitucion' => '' // Se usa para seleccionar la opción en el <select>
];
$esEdicion = false;
$idSede = 0;
$tituloFormulario = 'Registrar Sede';
$textoBoton = 'Registrar Sede';

// 1. Detectar si la página se cargó con un ID para edición
if (isset($_GET['IdSede']) && is_numeric($_GET['IdSede'])) {
    
    $idSede = intval($_GET['IdSede']);
    
    // 2. Obtener datos de la sede usando el controlador
    $data = $controlador->obtenerSedePorId($idSede);

    if ($data) {
        $sedeData = $data; 
        $esEdicion = true;
        $tituloFormulario = 'Editar Sede: ' . htmlspecialchars($data['TipoSede']);
        $textoBoton = 'Actualizar Sede';
    } else {
         // Si el ID existe pero no hay datos, volvemos a modo registro o mostramos error.
         $idSede = 0;
    }
}

// 3. Cargar la lista de instituciones (necesaria para el <select> en ambos modos)
$instituciones = $controlador->obtenerInstituciones(); 
?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-building me-2"></i><?= $tituloFormulario; ?></h1>
        <a href="SedeLista.php" class="btn btn-primary btn-sm">
            <i class="fas fa-list me-1"></i> Ver Sedes
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Formulario de <?= $esEdicion ? 'edición' : 'registro'; ?></h6>
        </div>

        <div class="card-body">
            <form id="formRegistrarSede"> 
                
                <input type="hidden" name="accion" id="accion" value="<?= $esEdicion ? 'editar' : 'registrar'; ?>">
                
                <?php if ($esEdicion): ?>
                    <input type="hidden" name="IdSede" id="IdSede" value="<?= htmlspecialchars($idSede); ?>">
                <?php endif; ?>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label for="TipoSede" class="form-label">Nombre / Tipo de Sede *</label>
                        <input type="text" id="TipoSede" name="TipoSede"
                            maxlength="30"
                            class="form-control border-primary shadow-sm"
                            placeholder="Ej: Sede Norte"
                            value="<?= htmlspecialchars($sedeData['TipoSede']); ?>"> </div>

                    <div class="col-md-6 mb-3">
                        <label for="Ciudad" class="form-label">Ciudad *</label>
                        <input type="text" id="Ciudad" name="Ciudad"
                            maxlength="30"
                            class="form-control border-primary shadow-sm"
                            placeholder="Ej: Bogotá"
                            value="<?= htmlspecialchars($sedeData['Ciudad']); ?>"> </div>

                </div>

                <div class="mb-3">
                    <label for="IdInstitucion" class="form-label">Institución Asociada *</label>
                    <select name="IdInstitucion" id="IdInstitucion"
                        class="form-control border-primary shadow-sm">
                        <option value="">Seleccione...</option>

                        <?php foreach ($instituciones as $inst): ?>
                            <option value="<?= htmlspecialchars($inst['IdInstitucion']) ?>"
                                <?php 
                                        // ⬅️ Seleccionar la institución si estamos en modo edición
                                        if ($esEdicion && $inst['IdInstitucion'] == $sedeData['IdInstitucion']) {
                                            echo 'selected';
                                        }
                                ?>
                            >
                                <?= htmlspecialchars($inst['NombreInstitucion']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="text-end">
                    <button type="submit" id="btnSubmitForm" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> <?= $textoBoton; ?> </button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior_administrador.php'; ?>
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../../Public/js/javascript/js/ValidacionesSede.js"></script>