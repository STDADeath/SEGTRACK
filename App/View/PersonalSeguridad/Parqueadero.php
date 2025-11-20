<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

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
                    <form method="POST" action="../../Controller/ControladorParqueadero.php" class="needs-validation" novalidate>
                        <div class="row">
                            <!-- Tipo de vehículo -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Tipo de Vehículo</label>
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
                                <label class="form-label fw-semibold">Placa</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="text" class="form-control" name="PlacaVehiculo" id="PlacaVehiculo" required>
                                </div>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Descripción</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                                <textarea class="form-control" name="DescripcionVehiculo" rows="3" placeholder="Color, modelo, características..." id="DescripcionVehiculo"></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Tarjeta de Propiedad -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Tarjeta de Propiedad</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-file-alt"></i></span>
                                    <input type="text" class="form-control" name="TarjetaPropiedad" placeholder="Número de tarjeta" id="TarjetaPropiedad">
                                </div>
                            </div>

                            <!-- Fecha -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Fecha</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="datetime-local" class="form-control" name="FechaParqueadero" id="FechaParqueadero">
                                </div>
                            </div>
                        </div>

                        <!-- ID de Sede -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre de Sede</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-building"></i></span>
                                <input type="text" class="form-control" name="IdSede" id="IdSede" required>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='../view/Vehiculolista.php'">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Vehículo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Librería SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Validación de formulario -->
<script src="../../../Public/js/javascript/js/ValidacionParqueadero.js"></script>
</body>
</html>

<!---fin del contenido principal--->
<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>