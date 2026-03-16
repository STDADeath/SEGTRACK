// ============================================================
// ValidacionGuardiaParqueadero.js
// Lógica JS de la vista del personal de seguridad (guardia)
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

    function esperarJQuery(cb) {
        if (typeof $ !== 'undefined') cb();
        else setTimeout(() => esperarJQuery(cb), 50);
    }

    esperarJQuery(function () {

        // Estado global de la sesión del guardia
        var idSedeActual       = null;
        var idParqueaderoActual = null;
        var intervaloRefresco  = null;

        // ── Cargar parqueadero al seleccionar sede ────────────────────────────
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

        // ── Función principal: cargar datos de la sede ────────────────────────
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

                        // Mostrar nombre sede en badge header
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
            var total   = parseInt(parqueadero.CantidadParqueadero) || 0;
            var libres  = resumen.reduce(function(a, r) { return a + parseInt(r.Libres); }, 0);
            var ocupados = resumen.reduce(function(a, r) { return a + parseInt(r.Ocupados); }, 0);
            var pct     = total > 0 ? Math.round((ocupados / total) * 100) : 0;
            var colorBarra = pct >= 90 ? 'bg-danger' : (pct >= 60 ? 'bg-warning' : 'bg-success');

            var iconos = { 'Carro': 'fa-car', 'Moto': 'fa-motorcycle', 'Bicicleta': 'fa-bicycle' };
            var colores = { 'Carro': 'text-primary', 'Moto': 'text-warning', 'Bicicleta': 'text-success' };
            var bgCards = { 'Carro': 'border-left-primary', 'Moto': 'border-left-warning', 'Bicicleta': 'border-left-success' };

            var html = '';

            // Tarjeta general
            html += `
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 border-left-secondary">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Total General</div>
                                <div class="h5 mb-2 font-weight-bold text-gray-800">${total} espacios</div>
                                <div class="progress mb-1" style="height:8px;">
                                    <div class="progress-bar ${colorBarra}" style="width:${pct}%"></div>
                                </div>
                                <small class="text-muted">${pct}% ocupado</small>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-parking fa-2x text-secondary"></i>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <small class="text-success fw-bold"><i class="fas fa-circle me-1" style="font-size:8px;"></i>${libres} libres</small>
                            <small class="text-danger fw-bold"><i class="fas fa-circle me-1" style="font-size:8px;"></i>${ocupados} ocupados</small>
                        </div>
                    </div>
                </div>
            </div>`;

            // Tarjeta por tipo
            resumen.forEach(function (r) {
                var icono  = iconos[r.TipoVehiculo]  || 'fa-car';
                var color  = colores[r.TipoVehiculo]  || 'text-primary';
                var border = bgCards[r.TipoVehiculo]  || 'border-left-primary';
                var pctTipo = parseInt(r.Total) > 0
                    ? Math.round((parseInt(r.Ocupados) / parseInt(r.Total)) * 100) : 0;
                var colorT = pctTipo >= 90 ? 'bg-danger' : (pctTipo >= 60 ? 'bg-warning' : 'bg-success');

                html += `
                <div class="col-xl-3 col-md-4 mb-4">
                    <div class="card shadow h-100 ${border}">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold ${color} text-uppercase mb-1">${r.TipoVehiculo}s</div>
                                    <div class="h5 mb-2 font-weight-bold text-gray-800">${r.Total} espacios</div>
                                    <div class="progress mb-1" style="height:8px;">
                                        <div class="progress-bar ${colorT}" style="width:${pctTipo}%"></div>
                                    </div>
                                    <small class="text-muted">${pctTipo}% ocupado</small>
                                </div>
                                <div class="col-auto">
                                    <i class="fas ${icono} fa-2x ${color}"></i>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <small class="text-success fw-bold">${r.Libres} libres</small>
                                <small class="text-danger fw-bold">${r.Ocupados} ocupados</small>
                            </div>
                        </div>
                    </div>
                </div>`;
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

                html += `
                <div class="mb-5">
                    <h6 class="fw-bold ${colores[tipo]} border-bottom pb-2 mb-3">
                        <i class="fas ${iconos[tipo]} me-2"></i>${tipo}s
                        <span class="ms-2 badge badge-success">${libres} libres</span>
                        <span class="ms-1 badge badge-danger">${ocupados} ocupados</span>
                    </h6>
                    <div class="row g-2">`;

                delTipo.forEach(function (e) {
                    var libre      = e.Estado === 'Libre';
                    var bgColor    = libre ? '#e8f5e9' : '#ffebee';
                    var border     = libre ? '#4caf50' : '#f44336';
                    var propietario = '';

                    if (!libre) {
                        if (e.NombreFuncionario) {
                            propietario = `<small class="text-muted d-block" style="font-size:10px;">
                                <i class="fas fa-user-tie me-1"></i>${e.NombreFuncionario}</small>`;
                        } else if (e.NombreVisitante) {
                            propietario = `<small class="text-muted d-block" style="font-size:10px;">
                                <i class="fas fa-user me-1"></i>${e.NombreVisitante}</small>`;
                        }
                    }

                    if (libre) {
                        // Espacio libre → clic para ocupar manualmente
                        html += `
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-2">
                            <div class="border rounded p-2 text-center h-100 espacio-card espacio-libre"
                                 style="background:${bgColor};border-color:${border}!important;cursor:pointer;"
                                 onclick="abrirOcupar(${e.IdEspacio}, ${e.NumeroEspacio}, '${e.TipoVehiculo}')"
                                 title="Clic para asignar un vehículo">
                                <div class="fw-bold fs-5">#${e.NumeroEspacio}</div>
                                <i class="fas fa-check-circle text-success mb-1"></i>
                                <div><small class="badge badge-success">Libre</small></div>
                                <small class="text-muted d-block" style="font-size:10px;">
                                    <i class="fas fa-mouse-pointer me-1"></i>Clic para asignar
                                </small>
                            </div>
                        </div>`;
                    } else {
                        // Espacio ocupado → clic para liberar
                        html += `
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-2">
                            <div class="border rounded p-2 text-center h-100 espacio-card espacio-ocupado"
                                 style="background:${bgColor};border-color:${border}!important;cursor:pointer;"
                                 onclick="abrirLiberar(${e.IdEspacio}, ${e.NumeroEspacio}, '${e.PlacaVehiculo || ''}')"
                                 title="Clic para registrar salida">
                                <div class="fw-bold fs-5">#${e.NumeroEspacio}</div>
                                <i class="fas fa-times-circle text-danger mb-1"></i>
                                <div><span class="badge badge-dark">${e.PlacaVehiculo || 'Sin placa'}</span></div>
                                ${propietario}
                                <small class="text-muted d-block mt-1" style="font-size:10px;">
                                    <i class="fas fa-sign-out-alt me-1"></i>Clic para salida
                                </small>
                            </div>
                        </div>`;
                    }
                });

                html += `</div></div>`;
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
            $('#ocuparPlaca').val('');
            $('#ocuparTipoEspacio').text(tipoVehiculo);
            $('#ocuparTipoInfo').show();
            $('#modalOcuparEspacio').modal('show');
            setTimeout(function () { $('#ocuparPlaca').focus(); }, 400);
        };

        // Forzar mayúsculas en placa
        $('#ocuparPlaca').on('input', function () {
            $(this).val($(this).val().toUpperCase().replace(/[^A-Z0-9]/g, ''));
        });

        // Confirmar ocupar
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
                        }).then(function () {
                            cargarDatosSede(idSedeActual, true);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'No se pudo asignar',
                            text: r.message,
                            confirmButtonColor: '#e74a3b'
                        });
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

        // Confirmar liberar
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
                        }).then(function () {
                            cargarDatosSede(idSedeActual, true);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'No se pudo liberar',
                            text: r.message,
                            confirmButtonColor: '#e74a3b'
                        });
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