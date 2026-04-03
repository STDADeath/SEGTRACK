<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>
<?php require_once(__DIR__ . "/../../Core/conexion.php"); ?>

<?php
$conexionObj = new Conexion();
$conn        = $conexionObj->getConexion();

// Sedes activas
$sqlSedes  = "SELECT IdSede, TipoSede, Ciudad FROM sede WHERE Estado = 'Activo' ORDER BY TipoSede ASC";
$stmtSedes = $conn->prepare($sqlSedes);
$stmtSedes->execute();
$sedes = $stmtSedes->fetchAll(PDO::FETCH_ASSOC);
// Ya NO se precargan funcionarios ni visitantes — se cargan dinámicamente por sede
?>

<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <!-- Header -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-car me-2"></i>Registrar Vehículo</h1>
                <a href="./VehiculoLista.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                    <i class="fas fa-list me-1"></i> Ver Vehículos
                </a>
            </div>

            <!-- Form Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">Información del Vehículo</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="../../Controller/ControladorVehiculo.php" class="needs-validation">

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
                                    <input type="text" class="form-control"
                                        name="PlacaVehiculo" id="PlacaVehiculo"
                                        maxlength="6" minlength="3" required
                                        placeholder="Ej: ABC123"
                                        pattern="[a-zA-Z0-9 -]+"
                                        title="Solo letras, números, espacios y guiones">
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Mínimo 3 caracteres, máximo 6
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
                                    name="DescripcionVehiculo" id="DescripcionVehiculo"
                                    rows="3" required minlength="5"
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
                                    <input type="text" class="form-control"
                                        name="TarjetaPropiedad" id="TarjetaPropiedad"
                                        maxlength="20" minlength="10" required
                                        placeholder="Número de tarjeta"
                                        pattern="[a-zA-Z0-9 -]+"
                                        title="Solo letras, números, espacios y guiones">
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Mínimo 10 caracteres, máximo 20
                                </small>
                            </div>

                            <!-- Fecha automática -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    Fecha y Hora <span class="badge bg-info">Automática</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="datetime-local" class="form-control bg-light"
                                        name="FechaDeVehiculo" id="FechaDeVehiculo"
                                        readonly style="cursor:not-allowed;">
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
                                <i class="fas fa-info-circle"></i> Al seleccionar la sede se cargarán sus funcionarios y visitantes
                            </small>
                        </div>

                        <!-- Tipo de Propietario -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Tipo de Propietario <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                <select class="form-select" name="TipoPersona" id="TipoPersona" required disabled>
                                    <option value="">Primero seleccione una sede...</option>
                                    <option value="Funcionario">Funcionario</option>
                                    <option value="Visitante">Visitante</option>
                                </select>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Indique si el vehículo pertenece a un funcionario o visitante
                            </small>
                        </div>

                        <!-- Select Funcionario -->
                        <div class="mb-3 d-none" id="divFuncionario">
                            <label class="form-label fw-semibold">
                                Funcionario <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                                <select class="form-select" name="IdFuncionario" id="IdFuncionario">
                                    <option value="">Seleccione un funcionario...</option>
                                </select>
                            </div>
                        </div>

                        <!-- Select Visitante -->
                        <div class="mb-3 d-none" id="divVisitante">
                            <label class="form-label fw-semibold">
                                Visitante <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <select class="form-select" name="IdVisitante" id="IdVisitante">
                                    <option value="">Seleccione un visitante...</option>
                                </select>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='./Vehiculo.php'">
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
                        <i class="fas fa-info-circle me-2"></i>
                        El código QR se generará automáticamente después de guardar los datos del vehículo.
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
// ── Filtrar funcionarios/visitantes al cambiar sede ──────────────────────────
document.getElementById('IdSede').addEventListener('change', function () {
    const idSede     = this.value;
    const selTipo    = document.getElementById('TipoPersona');
    const selF       = document.getElementById('IdFuncionario');
    const selV       = document.getElementById('IdVisitante');
    const divF       = document.getElementById('divFuncionario');
    const divV       = document.getElementById('divVisitante');

    // Resetear todo al cambiar de sede
    selTipo.value    = '';
    selTipo.disabled = true;
    selF.innerHTML   = '<option value="">Seleccione un funcionario...</option>';
    selV.innerHTML   = '<option value="">Seleccione un visitante...</option>';
    divF.classList.add('d-none');
    divV.classList.add('d-none');
    selF.required    = false;
    selV.required    = false;

    if (!idSede) {
        selTipo.innerHTML = '<option value="">Primero seleccione una sede...</option>'
                          + '<option value="Funcionario">Funcionario</option>'
                          + '<option value="Visitante">Visitante</option>';
        return;
    }

    // Mostrar carga
    selTipo.innerHTML = '<option value="">Cargando personas...</option>';

    const formData = new FormData();
    formData.append('accion',  'obtener_personas_por_sede');
    formData.append('id_sede', idSede);

    fetch('../../Controller/ControladorVehiculo.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            // Restaurar opciones del select tipo
            selTipo.innerHTML = '<option value="">Seleccione tipo de propietario...</option>'
                              + '<option value="Funcionario">Funcionario</option>'
                              + '<option value="Visitante">Visitante</option>';
            selTipo.disabled = false;

            if (!data.success) {
                Swal.fire({ icon: 'warning', title: 'Aviso', text: data.message, confirmButtonColor: '#f6c23e' });
                return;
            }

            // Poblar funcionarios
            if (data.funcionarios.length === 0) {
                selF.innerHTML = '<option value="" disabled>Sin funcionarios en esta sede</option>';
            } else {
                selF.innerHTML = '<option value="">Seleccione un funcionario...</option>';
                data.funcionarios.forEach(f => {
                    selF.innerHTML += `<option value="${f.IdFuncionario}">${f.NombreFuncionario}</option>`;
                });
            }

            // Poblar visitantes
            if (data.visitantes.length === 0) {
                selV.innerHTML = '<option value="" disabled>Sin visitantes en esta sede</option>';
            } else {
                selV.innerHTML = '<option value="">Seleccione un visitante...</option>';
                data.visitantes.forEach(v => {
                    selV.innerHTML += `<option value="${v.IdVisitante}">${v.NombreVisitante}</option>`;
                });
            }
        })
        .catch(() => {
            selTipo.innerHTML = '<option value="">Seleccione tipo de propietario...</option>'
                              + '<option value="Funcionario">Funcionario</option>'
                              + '<option value="Visitante">Visitante</option>';
            selTipo.disabled = false;
            Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudieron cargar las personas de la sede', confirmButtonColor: '#e74a3b' });
        });
});

// ── Mostrar/ocultar select según tipo de propietario ─────────────────────────
document.getElementById('TipoPersona').addEventListener('change', function () {
    const divF = document.getElementById('divFuncionario');
    const divV = document.getElementById('divVisitante');
    const selF = document.getElementById('IdFuncionario');
    const selV = document.getElementById('IdVisitante');

    if (this.value === 'Funcionario') {
        divF.classList.remove('d-none');
        divV.classList.add('d-none');
        selF.required = true;
        selV.required = false;
        selV.value    = '';
    } else if (this.value === 'Visitante') {
        divV.classList.remove('d-none');
        divF.classList.add('d-none');
        selV.required = true;
        selF.required = false;
        selF.value    = '';
    } else {
        divF.classList.add('d-none');
        divV.classList.add('d-none');
        selF.required = false;
        selV.required = false;
    }
});
</script>

<script src="../../../Public/js/javascript/js/ValidacionVehiculo.js"></script>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>