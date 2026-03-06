<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>
<?php require_once(__DIR__ . "/../../Core/conexion.php"); ?>
<?php require_once(__DIR__ . "/../../Model/ModeloParqueadero.php"); ?>

<?php
$conexionObj = new Conexion();
$conn        = $conexionObj->getConexion();
$modelo      = new ModeloParqueadero($conn);

// Sedes que tienen parqueadero activo configurado
$sedes = $modelo->obtenerSedesConParqueadero();
?>

<div class="container-fluid px-4 py-4">

    <!-- ── Header ──────────────────────────────────────────────────────────── -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-parking me-2"></i>Control de Parqueadero
        </h1>
        <span class="badge badge-primary" id="badgeSedeSel" style="font-size:0.9rem;display:none;">
            <i class="fas fa-map-marker-alt me-1"></i><span id="textSedeSel"></span>
        </span>
    </div>

    <!-- ── Selector de sede ─────────────────────────────────────────────────── -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-building me-2"></i>Seleccione su Sede
            </h6>
        </div>
        <div class="card-body">
            <?php if (count($sedes) === 0) : ?>
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No hay sedes con parqueadero activo configurado. Contacte al administrador.
                </div>
            <?php else : ?>
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Sede donde se encuentra</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                            <select class="form-select" id="selectSede">
                                <option value="">-- Seleccione una sede --</option>
                                <?php foreach ($sedes as $s) : ?>
                                    <option value="<?= $s['IdSede'] ?>">
                                        <?= htmlspecialchars($s['TipoSede']) ?> — <?= htmlspecialchars($s['Ciudad']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" id="btnCargarSede">
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

    <!-- ── Contenido del parqueadero (se carga dinámicamente) ──────────────── -->
    <div id="contenidoParqueadero" style="display:none;">

        <!-- Tarjetas resumen por tipo -->
        <div class="row mb-4" id="tarjetasResumen"></div>

        <!-- Grid de espacios -->
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

    <!-- ── Estado inicial (antes de seleccionar sede) ───────────────────────── -->
    <div id="estadoInicial" class="text-center py-5">
        <i class="fas fa-parking fa-4x text-muted mb-3"></i>
        <p class="text-muted fs-5">Seleccione una sede para ver los espacios disponibles</p>
    </div>

</div>

<!-- ══ MODAL OCUPAR ESPACIO MANUALMENTE ════════════════════════════════════ -->
<div class="modal fade" id="modalOcuparEspacio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-car me-2"></i>Asignar Vehículo — Espacio #<span id="ocuparNumEspacio"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="ocuparIdEspacio">
                <div class="alert alert-warning py-2">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Use esta opción cuando el escáner no esté disponible o no haya espacios automáticos.
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Placa del vehículo <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-car"></i></span>
                        <input type="text" class="form-control" id="ocuparPlaca"
                               placeholder="Ej: ABC123" maxlength="7"
                               style="text-transform:uppercase;">
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        El vehículo debe estar registrado y activo en el sistema
                    </small>
                </div>
                <div id="ocuparTipoInfo" class="alert alert-info py-2" style="display:none;">
                    <i class="fas fa-info-circle me-2"></i>
                    Este espacio es para: <strong id="ocuparTipoEspacio"></strong>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button class="btn btn-warning" id="btnConfirmarOcupar">
                    <i class="fas fa-check me-1"></i>Asignar Espacio
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL LIBERAR ESPACIO ══════════════════════════════════════════════════ -->
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
// ============================================================
// Lógica JS del Control de Parqueadero - Personal de Seguridad
// ============================================================
document.addEventListener('DOMContentLoaded', function () {

    function esperarJQuery(cb) {
        if (typeof $ !== 'undefined') cb();
        else setTimeout(function() { esperarJQuery(cb); }, 50);
    }

    esperarJQuery(function () {

        var idSedeActual        = null;
        var idParqueaderoActual = null;
        var intervaloRefresco   = null;

        // ── Cargar parqueadero al dar clic en Ver Parqueadero ─────────────────
        $('#btnCargarSede').on('click', function () {
            var idSede = $('#selectSede').val();
            if (!idSede) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Seleccione una sede',
                    text: 'Debe seleccionar la sede donde se encuentra',
                    confirmButtonColor: '#f6c23e'
                });
                return;
            }
            cargarDatosSede(parseInt(idSede));
        });

        // ── Botón refrescar ───────────────────────────────────────────────────
        $('#btnRefrescar').on('click', function () {
            if (idSedeActual) cargarDatosSede(idSedeActual, true);
        });

        // ── Cargar datos del parqueadero de la sede ───────────────────────────
        function cargarDatosSede(idSede, silencioso) {
            idSedeActual = idSede;

            if (!silencioso) {
                $('#estadoInicial').hide();
                $('#contenidoParqueadero').hide();
                $('#gridEspacios').html(
                    '<div class="text-center py-4">' +
                    '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i>' +
                    '<p class="mt-2 text-muted">Cargando espacios...</p></div>'
                );
            }

            $.ajax({
                url: '../../Controller/ControladorParqueadero.php',
                type: 'POST',
                data: { accion: 'obtener_sede', id_sede: idSede },
                dataType: 'json',
                timeout: 15000,
                success: function (r) {
                    if (r.success) {
                        idParqueaderoActual = r.parqueadero.IdParqueadero;

                        var nombreSede = r.parqueadero.TipoSede + ' — ' + r.parqueadero.Ciudad;
                        $('#textSedeSel').text(nombreSede);
                        $('#badgeSedeSel').show();
                        $('#btnRefrescar').show();

                        renderizarResumen(r.resumen, r.parqueadero);
                        renderizarGrid(r.espacios);

                        $('#estadoInicial').hide();
                        $('#contenidoParqueadero').show();

                        // Auto-refresco cada 30 segundos
                        if (intervaloRefresco) clearInterval(intervaloRefresco);
                        intervaloRefresco = setInterval(function () {
                            cargarDatosSede(idSedeActual, true);
                        }, 30000);

                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Sin parqueadero',
                            text: r.message || 'Esta sede no tiene parqueadero activo',
                            confirmButtonColor: '#f6c23e'
                        });
                        $('#estadoInicial').show();
                        $('#contenidoParqueadero').hide();
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo cargar la información del parqueadero',
                        confirmButtonColor: '#e74a3b'
                    });
                }
            });
        }

        // ── Renderizar tarjetas de resumen ────────────────────────────────────
        function renderizarResumen(resumen, parqueadero) {
            var total    = parseInt(parqueadero.CantidadParqueadero) || 0;
            var libres   = resumen.reduce(function(a, r) { return a + parseInt(r.Libres); }, 0);
            var ocupados = resumen.reduce(function(a, r) { return a + parseInt(r.Ocupados); }, 0);
            var pct      = total > 0 ? Math.round((ocupados / total) * 100) : 0;
            var colorBarra = pct >= 90 ? 'bg-danger' : (pct >= 60 ? 'bg-warning' : 'bg-success');

            var iconos  = { 'Carro': 'fa-car', 'Moto': 'fa-motorcycle', 'Bicicleta': 'fa-bicycle' };
            var colores = { 'Carro': 'text-primary', 'Moto': 'text-warning', 'Bicicleta': 'text-success' };
            var bgCards = { 'Carro': 'border-left-primary', 'Moto': 'border-left-warning', 'Bicicleta': 'border-left-success' };

            var html = '';

            // Tarjeta total general
            html += '<div class="col-xl-3 col-md-6 mb-4">' +
                '<div class="card shadow h-100 border-left-secondary">' +
                '<div class="card-body">' +
                '<div class="row no-gutters align-items-center">' +
                '<div class="col mr-2">' +
                '<div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Total General</div>' +
                '<div class="h5 mb-2 font-weight-bold text-gray-800">' + total + ' espacios</div>' +
                '<div class="progress mb-1" style="height:8px;"><div class="progress-bar ' + colorBarra + '" style="width:' + pct + '%"></div></div>' +
                '<small class="text-muted">' + pct + '% ocupado</small>' +
                '</div><div class="col-auto"><i class="fas fa-parking fa-2x text-secondary"></i></div>' +
                '</div>' +
                '<div class="d-flex justify-content-between mt-2">' +
                '<small class="text-success font-weight-bold">' + libres + ' libres</small>' +
                '<small class="text-danger font-weight-bold">' + ocupados + ' ocupados</small>' +
                '</div></div></div></div>';

            // Tarjetas por tipo
            resumen.forEach(function (r) {
                var icono   = iconos[r.TipoVehiculo]  || 'fa-car';
                var color   = colores[r.TipoVehiculo] || 'text-primary';
                var border  = bgCards[r.TipoVehiculo] || 'border-left-primary';
                var pctT    = parseInt(r.Total) > 0 ? Math.round((parseInt(r.Ocupados) / parseInt(r.Total)) * 100) : 0;
                var colorT  = pctT >= 90 ? 'bg-danger' : (pctT >= 60 ? 'bg-warning' : 'bg-success');

                html += '<div class="col-xl-3 col-md-4 mb-4">' +
                    '<div class="card shadow h-100 ' + border + '">' +
                    '<div class="card-body">' +
                    '<div class="row no-gutters align-items-center">' +
                    '<div class="col mr-2">' +
                    '<div class="text-xs font-weight-bold ' + color + ' text-uppercase mb-1">' + r.TipoVehiculo + 's</div>' +
                    '<div class="h5 mb-2 font-weight-bold text-gray-800">' + r.Total + ' espacios</div>' +
                    '<div class="progress mb-1" style="height:8px;"><div class="progress-bar ' + colorT + '" style="width:' + pctT + '%"></div></div>' +
                    '<small class="text-muted">' + pctT + '% ocupado</small>' +
                    '</div><div class="col-auto"><i class="fas ' + icono + ' fa-2x ' + color + '"></i></div>' +
                    '</div>' +
                    '<div class="d-flex justify-content-between mt-2">' +
                    '<small class="text-success font-weight-bold">' + r.Libres + ' libres</small>' +
                    '<small class="text-danger font-weight-bold">' + r.Ocupados + ' ocupados</small>' +
                    '</div></div></div></div>';
            });

            $('#tarjetasResumen').html(html);
        }

        // ── Renderizar grid de espacios ───────────────────────────────────────
        function renderizarGrid(espacios) {
            var tipos   = ['Carro', 'Moto', 'Bicicleta'];
            var iconos  = { 'Carro': 'fa-car', 'Moto': 'fa-motorcycle', 'Bicicleta': 'fa-bicycle' };
            var colores = { 'Carro': 'text-primary', 'Moto': 'text-warning', 'Bicicleta': 'text-success' };
            var html    = '';

            tipos.forEach(function (tipo) {
                var delTipo  = espacios.filter(function (e) { return e.TipoVehiculo === tipo; });
                if (delTipo.length === 0) return;

                var libres   = delTipo.filter(function (e) { return e.Estado === 'Libre'; }).length;
                var ocupados = delTipo.filter(function (e) { return e.Estado === 'Ocupado'; }).length;

                html += '<div class="mb-5">' +
                    '<h6 class="font-weight-bold ' + colores[tipo] + ' border-bottom pb-2 mb-3">' +
                    '<i class="fas ' + iconos[tipo] + ' mr-2"></i>' + tipo + 's ' +
                    '<span class="ml-2 badge badge-success">' + libres + ' libres</span> ' +
                    '<span class="ml-1 badge badge-danger">' + ocupados + ' ocupados</span>' +
                    '</h6><div class="row">';

                delTipo.forEach(function (e) {
                    var libre      = e.Estado === 'Libre';
                    var bgColor    = libre ? '#e8f5e9' : '#ffebee';
                    var borderCol  = libre ? '#4caf50' : '#f44336';
                    var propietario = '';

                    if (!libre) {
                        if (e.NombreFuncionario) {
                            propietario = '<small class="text-muted d-block" style="font-size:10px;">' +
                                '<i class="fas fa-user-tie mr-1"></i>' + e.NombreFuncionario + '</small>';
                        } else if (e.NombreVisitante) {
                            propietario = '<small class="text-muted d-block" style="font-size:10px;">' +
                                '<i class="fas fa-user mr-1"></i>' + e.NombreVisitante + '</small>';
                        }
                    }

                    if (libre) {
                        html += '<div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-2">' +
                            '<div class="border rounded p-2 text-center h-100" ' +
                            'style="background:' + bgColor + ';border-color:' + borderCol + '!important;cursor:pointer;" ' +
                            'onclick="abrirOcupar(' + e.IdEspacio + ', ' + e.NumeroEspacio + ', \'' + e.TipoVehiculo + '\')" ' +
                            'title="Clic para asignar un vehículo">' +
                            '<div class="font-weight-bold" style="font-size:1.2rem;">#' + e.NumeroEspacio + '</div>' +
                            '<i class="fas fa-check-circle text-success mb-1"></i>' +
                            '<div><small class="badge badge-success">Libre</small></div>' +
                            '<small class="text-muted d-block" style="font-size:10px;">' +
                            '<i class="fas fa-mouse-pointer mr-1"></i>Clic para asignar</small>' +
                            '</div></div>';
                    } else {
                        html += '<div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-2">' +
                            '<div class="border rounded p-2 text-center h-100" ' +
                            'style="background:' + bgColor + ';border-color:' + borderCol + '!important;cursor:pointer;" ' +
                            'onclick="abrirLiberar(' + e.IdEspacio + ', ' + e.NumeroEspacio + ', \'' + (e.PlacaVehiculo || '') + '\')" ' +
                            'title="Clic para registrar salida">' +
                            '<div class="font-weight-bold" style="font-size:1.2rem;">#' + e.NumeroEspacio + '</div>' +
                            '<i class="fas fa-times-circle text-danger mb-1"></i>' +
                            '<div><span class="badge badge-dark">' + (e.PlacaVehiculo || 'Sin placa') + '</span></div>' +
                            propietario +
                            '<small class="text-muted d-block mt-1" style="font-size:10px;">' +
                            '<i class="fas fa-sign-out-alt mr-1"></i>Clic para salida</small>' +
                            '</div></div>';
                    }
                });

                html += '</div></div>';
            });

            if (!html) {
                html = '<div class="alert alert-info">No hay espacios registrados en este parqueadero.</div>';
            }

            $('#gridEspacios').html(html);
        }

        // ── Abrir modal OCUPAR manualmente ────────────────────────────────────
        window.abrirOcupar = function (idEspacio, numEspacio, tipoVehiculo) {
            $('#ocuparIdEspacio').val(idEspacio);
            $('#ocuparNumEspacio').text(numEspacio);
            $('#ocuparPlaca').val('').removeClass('is-valid is-invalid');
            $('#ocuparTipoEspacio').text(tipoVehiculo);
            $('#ocuparTipoInfo').show();
            $('#modalOcuparEspacio').modal('show');
            setTimeout(function () { $('#ocuparPlaca').focus(); }, 400);
        };

        // Forzar mayúsculas en placa
        $('#ocuparPlaca').on('input', function () {
            $(this).val($(this).val().toUpperCase().replace(/[^A-Z0-9]/g, ''));
        });

        // Confirmar OCUPAR
        $('#btnConfirmarOcupar').on('click', function () {
            var idEspacio = $('#ocuparIdEspacio').val();
            var placa     = $('#ocuparPlaca').val().trim();

            if (!placa || placa.length < 3) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Placa requerida',
                    text: 'Ingrese una placa válida (mínimo 3 caracteres)',
                    confirmButtonColor: '#f6c23e'
                });
                return;
            }

            $('#modalOcuparEspacio').modal('hide');
            Swal.fire({
                title: 'Asignando espacio...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: function () { Swal.showLoading(); }
            });

            $.ajax({
                url: '../../Controller/ControladorParqueadero.php',
                type: 'POST',
                data: { accion: 'ocupar_manual', id_espacio: idEspacio, placa: placa },
                dataType: 'json',
                timeout: 15000,
                success: function (r) {
                    if (r.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Espacio asignado!',
                            text: r.message,
                            timer: 2500,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(function () { cargarDatosSede(idSedeActual, true); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'No se pudo asignar', text: r.message, confirmButtonColor: '#e74a3b' });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#e74a3b' });
                }
            });
        });

        // ── Abrir modal LIBERAR ───────────────────────────────────────────────
        window.abrirLiberar = function (idEspacio, numEspacio, placa) {
            $('#liberarIdEspacio').val(idEspacio);
            $('#liberarNumEspacio').text(numEspacio);
            $('#liberarPlaca').text(placa || 'Sin placa registrada');
            $('#modalLiberarEspacio').modal('show');
        };

        // Confirmar LIBERAR
        $('#btnConfirmarLiberar').on('click', function () {
            var idEspacio = $('#liberarIdEspacio').val();

            $('#modalLiberarEspacio').modal('hide');
            Swal.fire({
                title: 'Registrando salida...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: function () { Swal.showLoading(); }
            });

            $.ajax({
                url: '../../Controller/ControladorParqueadero.php',
                type: 'POST',
                data: { accion: 'liberar_manual', id_espacio: idEspacio },
                dataType: 'json',
                timeout: 15000,
                success: function (r) {
                    if (r.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Salida registrada!',
                            text: r.message,
                            timer: 2500,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(function () { cargarDatosSede(idSedeActual, true); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'No se pudo liberar', text: r.message, confirmButtonColor: '#e74a3b' });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#e74a3b' });
                }
            });
        });

        // Limpiar al cerrar modales
        $('#modalOcuparEspacio').on('hidden.bs.modal', function () {
            $('#ocuparPlaca').val('').removeClass('is-valid is-invalid');
            $('#btnConfirmarOcupar').prop('disabled', false);
        });
        $('#modalLiberarEspacio').on('hidden.bs.modal', function () {
            $('#btnConfirmarLiberar').prop('disabled', false);
        });

    }); // fin esperarJQuery
});
</script>


<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>