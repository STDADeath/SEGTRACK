<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>
<?php require_once(__DIR__ . "/../../Core/conexion.php"); ?>
<?php require_once(__DIR__ . "/../../Model/ModeloParqueadero.php"); ?>

<?php
$conexionObj = new Conexion();
$conn        = $conexionObj->getConexion();
$modelo      = new ModeloParqueadero($conn);

// Obtener instituciones que tienen parqueadero activo
$instituciones = $modelo->obtenerInstitucionesConParqueadero();

// Obtener todas las sedes con parqueadero activo (para respaldo)
$sedes = $modelo->obtenerSedesConParqueadero();
?>

<div class="container-fluid px-4 py-4">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-parking me-2"></i>Control de Parqueadero
        </h1>
        <span class="badge badge-primary" id="badgeSedeSel" style="font-size:0.9rem;display:none;">
            <i class="fas fa-map-marker-alt me-1"></i><span id="textSedeSel"></span>
        </span>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-building me-2"></i>Seleccione su Institución y Sede
            </h6>
        </div>
        <div class="card-body">
            <?php if (count($instituciones) === 0) : ?>
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No hay instituciones con parqueadero activo configurado. Contacte al administrador.
                </div>
            <?php else : ?>
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Institución</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-university"></i></span>
                            <select class="form-select" id="selectInstitucion">
                                <option value="">-- Seleccione una institución --</option>
                                <?php foreach ($instituciones as $inst) : ?>
                                    <option value="<?= $inst['IdInstitucion'] ?>">
                                        <?= htmlspecialchars($inst['NombreInstitucion']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Sede donde se encuentra</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                            <select class="form-select" id="selectSede" disabled>
                                <option value="">Primero seleccione una institución...</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" id="btnCargarSede" disabled>
                            <i class="fas fa-search me-1"></i> Ver Parqueadero
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-secondary w-100" id="btnRefrescar" style="display:none;">
                            <i class="fas fa-sync-alt me-1"></i> Actualizar
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="contenidoParqueadero" style="display:none;">
        <div class="row mb-4" id="tarjetasResumen"></div>
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-light d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-th me-2"></i>Mapa de Espacios
                </h6>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge badge-success"><i class="fas fa-circle me-1"></i>Libre</span>
                    <span class="badge badge-danger"><i class="fas fa-circle me-1"></i>Ocupado</span>
                </div>
            </div>
            <div class="card-body" id="gridEspacios">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2 text-muted">Cargando espacios...</p>
                </div>
            </div>
        </div>
    </div>

    <div id="estadoInicial" class="text-center py-5">
        <i class="fas fa-parking fa-4x text-muted mb-3"></i>
        <p class="text-muted fs-5">Seleccione una institución y sede para ver los espacios disponibles</p>
    </div>

</div>

<!-- MODAL OCUPAR ESPACIO -->
<div class="modal fade" id="modalOcuparEspacio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-car mr-2"></i>Asignar Vehículo — Espacio #<span id="ocuparNumEspacio"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="ocuparIdEspacio">
                <input type="hidden" id="ocuparTipoVehiculo">

                <div class="alert alert-warning py-2 mb-3">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Use esta opción cuando el escáner no esté disponible.
                </div>

                <div class="alert alert-info py-2 mb-3" id="ocuparTipoInfo">
                    <i class="fas fa-info-circle mr-2"></i>
                    Este espacio es para: <strong id="ocuparTipoLabel"></strong>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-bold">
                        Seleccione el vehículo <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text" id="ocuparIconoTipo">
                            <i class="fas fa-car"></i>
                        </span>
                        <select class="form-control" id="selectVehiculo">
                            <option value="">-- Cargando vehículos... --</option>
                        </select>
                    </div>
                    <small class="text-muted mt-1 d-block" id="ocuparSelectInfo"></small>
                </div>

                <div id="ocuparDetalleVehiculo" class="card border-info" style="display:none;">
                    <div class="card-body py-2">
                        <div class="row text-center">
                            <div class="col-6 border-right">
                                <small class="text-muted d-block">Propietario</small>
                                <strong id="detallePropietario" class="text-primary" style="font-size:0.85rem;"></strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Identificador</small>
                                <strong id="detalleIdentificador" class="text-dark" style="font-size:0.85rem;"></strong>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cancelar
                </button>
                <button class="btn btn-warning" id="btnConfirmarOcupar" disabled>
                    <i class="fas fa-check mr-1"></i>Asignar Espacio
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL LIBERAR ESPACIO -->
<div class="modal fade" id="modalLiberarEspacio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-door-open me-2"></i>Liberar Espacio #<span id="liberarNumEspacio"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center">
                <div class="toggle-container">
                    <label class="btn-lock activo" id="toggleLiberarEspacio">
                        <svg width="36" height="40" viewBox="0 0 36 40">
                            <path class="lockb" d="M27 27C27 34.1797 21.1797 40 14 40C6.8203 40 1 34.1797 1 27C1 19.8203 6.8203 14 14 14C21.1797 14 27 19.8203 27 27ZM15.6298 26.5191C16.4544 25.9845 17 25.056 17 24C17 22.3431 15.6569 21 14 21C12.3431 21 11 22.3431 11 24C11 25.056 11.5456 25.9845 12.3702 26.5191L11 32H17L15.6298 26.5191Z"></path>
                            <path class="lock"  d="M6 21V10C6 5.58172 9.58172 2 14 2V2C18.4183 2 22 5.58172 22 10V21"></path>
                            <path class="bling" d="M29 20L31 22"></path>
                            <path class="bling" d="M31.5 15H34.5"></path>
                            <path class="bling" d="M29 10L31 8"></path>
                        </svg>
                    </label>
                </div>
                <input type="hidden" id="liberarIdEspacio">
                <p class="mt-3 mb-1">
                    Vehículo: <strong id="liberarPlaca" class="text-danger"></strong>
                </p>
                <p class="text-muted mb-0">¿Confirma que el vehículo ha salido del parqueadero?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button class="btn btn-success" id="btnConfirmarLiberar">
                    <i class="fas fa-check me-1"></i>Confirmar Salida
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const sedesPorInstitucion = {};

<?php foreach ($instituciones as $inst) : ?>
sedesPorInstitucion[<?= $inst['IdInstitucion'] ?>] = <?= json_encode($modelo->obtenerSedesPorInstitucion($inst['IdInstitucion'])) ?>;
<?php endforeach; ?>

const selectInstitucion = document.getElementById('selectInstitucion');
const selectSede = document.getElementById('selectSede');
const btnCargarSede = document.getElementById('btnCargarSede');

function cargarSedesPorInstitucion() {
    const idInstitucion = selectInstitucion.value;
    
    if (!idInstitucion) {
        selectSede.innerHTML = '<option value="">Primero seleccione una institución...</option>';
        selectSede.disabled = true;
        btnCargarSede.disabled = true;
        return;
    }
    
    const sedes = sedesPorInstitucion[idInstitucion] || [];
    
    if (sedes.length === 0) {
        selectSede.innerHTML = '<option value="">No hay sedes disponibles para esta institución</option>';
        selectSede.disabled = true;
        btnCargarSede.disabled = true;
        return;
    }
    
    let options = '<option value="">-- Seleccione una sede --</option>';
    sedes.forEach(sede => {
        options += `<option value="${sede.IdSede}">
            ${sede.TipoSede} — ${sede.Ciudad}
        </option>`;
    });
    
    selectSede.innerHTML = options;
    selectSede.disabled = false;
    btnCargarSede.disabled = false;
}

selectInstitucion.addEventListener('change', function() {
    cargarSedesPorInstitucion();
    document.getElementById('contenidoParqueadero').style.display = 'none';
    document.getElementById('estadoInicial').style.display = 'block';
    document.getElementById('badgeSedeSel').style.display = 'none';
});

document.addEventListener('DOMContentLoaded', function() {
    selectInstitucion.value = '';
    selectSede.innerHTML = '<option value="">Primero seleccione una institución...</option>';
    selectSede.disabled = true;
    btnCargarSede.disabled = true;
});
</script>

<script src="/SEGTRACK/Public/js/javascript/js/ValidacionParqueadero.js"></script>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>