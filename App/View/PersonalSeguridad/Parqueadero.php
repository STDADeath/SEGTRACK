<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>
<?php require_once(__DIR__ . "/../../Core/conexion.php"); ?>

<?php
// Cargar sedes activas para el select
$conexionObj = new Conexion();
$connSede    = $conexionObj->getConexion();
$sqlSedes    = "SELECT IdSede, TipoSede, Ciudad FROM sede WHERE Estado = 'Activo' ORDER BY TipoSede ASC";
$stmtSedes   = $connSede->prepare($sqlSedes);
$stmtSedes->execute();
$sedes = $stmtSedes->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-car me-2"></i>Registrar Vehículo</h1>
                <a href="./Vehiculolista.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                    <i class="fas fa-list me-1"></i> Ver Vehículos
                </a>
            </div>

            <!-- Form Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">Información del Vehículo</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="../../Controller/ControladorParqueadero.php" class="needs-validation">
                        <div class="row">
                            <!-- Tipo de vehículo -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    Tipo de Vehículo <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-car-side"></i></span>
                                    <select class="form-select" name="TipoVehiculo" id="TipoVehiculo" required>
                                        <option value="">Seleccione tipo...</option>
                                        <option value="Bicicleta">Bicicleta</option>
                                        <option value="Moto">Moto</option>
                                        <option value="Carro">Carro</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Placa -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    Placa <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="text"
                                        class="form-control"
                                        name="PlacaVehiculo"
                                        id="PlacaVehiculo"
                                        maxlength="9"
                                        minlength="3"
                                        required
                                        placeholder="Ej: ABC123"
                                        pattern="[a-zA-Z0-9\s-]+"
                                        title="Solo letras, números, espacios y guiones">
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Mínimo 3 caracteres, máximo 9
                                </small>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Descripción <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                                <textarea class="form-control"
                                    name="DescripcionVehiculo"
                                    id="DescripcionVehiculo"
                                    rows="3"
                                    required
                                    minlength="5"
                                    placeholder="Ej: Chevrolet Spark rojo modelo 2020"></textarea>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Describa el color, modelo y características del vehículo
                            </small>
                        </div>

                        <div class="row">
                            <!-- Tarjeta de Propiedad -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    Tarjeta de Propiedad <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-file-alt"></i></span>
                                    <input type="text"
                                        class="form-control"
                                        name="TarjetaPropiedad"
                                        id="TarjetaPropiedad"
                                        maxlength="20"
                                        minlength="11"
                                        required
                                        placeholder="Número de tarjeta"
                                        pattern="[a-zA-Z0-9\s-]+"
                                        title="Solo letras, números, espacios y guiones">
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Mínimo 11 caracteres, máximo 20
                                </small>
                            </div>

                            <!-- Fecha automática -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    Fecha y Hora <span class="badge bg-info">Automática</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="datetime-local"
                                        class="form-control bg-light"
                                        name="FechaParqueadero"
                                        id="FechaParqueadero"
                                        readonly
                                        style="cursor: not-allowed;">
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-calendar-check"></i> La fecha y hora se registran automáticamente
                                </small>
                            </div>
                        </div>

                        <!-- Sede -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Sede <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-building"></i></span>
                                <select class="form-select" name="IdSede" id="IdSede" required>
                                    <option value="">Seleccione una sede...</option>
                                    <?php foreach ($sedes as $sede) : ?>
                                        <option value="<?= $sede['IdSede'] ?>">
                                            <?= htmlspecialchars($sede['TipoSede']) ?> — <?= htmlspecialchars($sede['Ciudad']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Solo se muestran sedes activas
                            </small>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='./Parqueadero.php'">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Vehículo
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">Información Adicional</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i> El código QR se generará automáticamente después de guardar los datos del vehículo.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../../../Public/js/javascript/js/ValidacionParqueadero.js"></script>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>