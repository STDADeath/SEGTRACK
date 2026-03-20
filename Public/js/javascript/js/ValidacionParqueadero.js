// ============================================================
// ValidacionParqueadero.js
// Lógica JS del módulo Parqueadero
// Admin (ParqueaderoAdmin.php) + Guardia (ParqueaderoGuardia.php)
// ============================================================


// ══════════════════════════════════════════════════════════════
// SECCIÓN 1 — ADMINISTRADOR (ParqueaderoAdmin.php)
// ══════════════════════════════════════════════════════════════

function esperarDependencias(cb) {
    if (typeof $ !== 'undefined' && typeof Swal !== 'undefined') cb();
    else setTimeout(function () { esperarDependencias(cb); }, 50);
}

esperarDependencias(function () {

    // ── Calcular total en tiempo real (CREAR) ─────────────────────────────────
    $(document).on('input', '.contador-tipo', function () {
        actualizarTotal('crearCarros', 'crearMotos', 'crearBicis', 'crearTotal');
    });

    // ── Calcular total en tiempo real (EDITAR) ────────────────────────────────
    $(document).on('input', '.contador-editar', function () {
        actualizarTotal('editCarros', 'editMotos', 'editBicis', 'editTotal');
    });

    function actualizarTotal(idC, idM, idB, idTotal) {
        var c = parseInt($('#' + idC).val()) || 0;
        var m = parseInt($('#' + idM).val()) || 0;
        var b = parseInt($('#' + idB).val()) || 0;
        $('#' + idTotal).text(c + m + b);
    }

    // ── Validar valores no negativos ──────────────────────────────────────────
    $(document).on('input', '.contador-tipo, .contador-editar', function () {
        var val = parseInt($(this).val());
        if (isNaN(val) || val < 0) {
            $(this).val(0);
            $(this).addClass('is-invalid').removeClass('is-valid');
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
        }
    });

    // ── Limpiar modal crear al abrirlo ────────────────────────────────────────
    $('#modalCrearParqueadero').on('show.bs.modal', function () {
        $('#crearIdSede').val('').removeClass('is-valid is-invalid');
        $('#crearCarros, #crearMotos, #crearBicis').val(0).removeClass('is-valid is-invalid');
        $('#crearTotal').text('0');
    });

    // ══ CREAR parqueadero ═════════════════════════════════════════════════════
    $('#btnCrearParqueadero').on('click', function () {
        var sede   = $('#crearIdSede').val();
        var carros = parseInt($('#crearCarros').val()) || 0;
        var motos  = parseInt($('#crearMotos').val())  || 0;
        var bicis  = parseInt($('#crearBicis').val())  || 0;
        var total  = carros + motos + bicis;

        if (!sede) {
            Swal.fire({ icon: 'warning', title: 'Sede requerida', text: 'Debe seleccionar una sede antes de continuar', confirmButtonColor: '#f6c23e' });
            $('#crearIdSede').addClass('is-invalid');
            return;
        }
        $('#crearIdSede').removeClass('is-invalid').addClass('is-valid');

        if (total <= 0) {
            Swal.fire({ icon: 'warning', title: 'Sin espacios definidos', html: 'Debe asignar al menos <strong>1 espacio</strong> en alguno de los tipos de vehículo.', confirmButtonColor: '#f6c23e' });
            return;
        }

        $('#modalCrearParqueadero').modal('hide');
        Swal.fire({
            title: 'Creando parqueadero...',
            html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br>Generando ' + total + ' espacios, por favor espere',
            allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false
        });

        $.ajax({
            url: '../../Controller/ControladorParqueadero.php',
            type: 'POST',
            data: { accion: 'crear', IdSede: sede, Carros: carros, Motos: motos, Bicis: bicis },
            dataType: 'json',
            timeout: 30000,
            success: function (r) {
                if (r.success) {
                    Swal.fire({ icon: 'success', title: '¡Parqueadero creado!', text: r.message, timer: 3000, timerProgressBar: true, confirmButtonText: 'Entendido', confirmButtonColor: '#1cc88a' })
                        .then(function () { location.reload(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'No se pudo crear', text: r.message, confirmButtonColor: '#e74a3b' });
                }
            },
            error: function (xhr, status) {
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: status === 'timeout' ? 'La solicitud tardó demasiado.' : 'No se pudo conectar con el servidor.', confirmButtonColor: '#e74a3b' });
            }
        });
    });

    // ══ Abrir modal EDITAR ════════════════════════════════════════════════════
    window.abrirModalEditar = function (p) {
        $('#editIdParqueadero').val(p.IdParqueadero);
        $('#editNombreSede').text((p.TipoSede || '') + ' — ' + (p.Ciudad || ''));
        $('#editCarros').val(parseInt(p.CantidadCarros)    || 0).removeClass('is-valid is-invalid');
        $('#editMotos').val(parseInt(p.CantidadMotos)      || 0).removeClass('is-valid is-invalid');
        $('#editBicis').val(parseInt(p.CantidadBicicletas) || 0).removeClass('is-valid is-invalid');
        $('#editTotal').text(
            (parseInt(p.CantidadCarros)     || 0) +
            (parseInt(p.CantidadMotos)      || 0) +
            (parseInt(p.CantidadBicicletas) || 0)
        );
        $('#editCarrosInfo').text('Actual: ' + (p.CantidadCarros     || 0) + ' espacios');
        $('#editMotosInfo').text('Actual: '  + (p.CantidadMotos      || 0) + ' espacios');
        $('#editBicisInfo').text('Actual: '  + (p.CantidadBicicletas || 0) + ' espacios');
        $('#modalEditarParqueadero').modal('show');
    };

    // ══ GUARDAR EDICIÓN ═══════════════════════════════════════════════════════
    $('#btnEditarParqueadero').on('click', function () {
        var id     = $('#editIdParqueadero').val();
        var carros = parseInt($('#editCarros').val()) || 0;
        var motos  = parseInt($('#editMotos').val())  || 0;
        var bicis  = parseInt($('#editBicis').val())  || 0;
        var total  = carros + motos + bicis;

        if (!id || parseInt(id) <= 0) {
            Swal.fire({ icon: 'error', title: 'Error interno', text: 'ID de parqueadero no válido', confirmButtonColor: '#e74a3b' });
            return;
        }
        if (total <= 0) {
            Swal.fire({ icon: 'warning', title: 'Sin espacios', text: 'El total de espacios no puede quedar en 0', confirmButtonColor: '#f6c23e' });
            return;
        }

        $('#modalEditarParqueadero').modal('hide');
        Swal.fire({
            title: 'Actualizando espacios...',
            html: '<i class="fas fa-spinner fa-spin fa-3x text-warning mb-3"></i><br>Ajustando espacios, por favor espere',
            allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false
        });

        $.ajax({
            url: '../../Controller/ControladorParqueadero.php',
            type: 'POST',
            data: { accion: 'actualizar', id: id, Carros: carros, Motos: motos, Bicis: bicis },
            dataType: 'json',
            timeout: 30000,
            success: function (r) {
                if (r.success) {
                    Swal.fire({ icon: 'success', title: '¡Actualizado!', text: r.message, timer: 3000, timerProgressBar: true, confirmButtonText: 'Entendido', confirmButtonColor: '#1cc88a' })
                        .then(function () { location.reload(); });
                } else {
                    Swal.fire({
                        icon: 'error', title: 'No se pudo actualizar',
                        html: (r.message || r.error || 'Error desconocido').replace(/\n/g, '<br>'),
                        confirmButtonColor: '#e74a3b',
                        footer: '<small class="text-muted">Solo puede reducir espacios <strong>libres</strong></small>'
                    });
                }
            },
            error: function (xhr, status) {
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: status === 'timeout' ? 'La solicitud tardó demasiado.' : 'No se pudo conectar con el servidor.', confirmButtonColor: '#e74a3b' });
            }
        });
    });

    // ══ VER espacios (Admin) ══════════════════════════════════════════════════
    window.verEspacios = function (idParqueadero, nombreSede) {
        $('#verEspaciosNombreSede').text(nombreSede);
        $('#verEspaciosContenido').html(
            '<div class="text-center py-4">' +
            '<i class="fas fa-spinner fa-spin fa-2x text-info"></i>' +
            '<p class="mt-2 text-muted">Cargando espacios...</p></div>'
        );
        $('#modalVerEspacios').modal('show');

        $.ajax({
            url: '../../Controller/ControladorParqueadero.php',
            type: 'POST',
            data: { accion: 'obtener_espacios', id: idParqueadero },
            dataType: 'json',
            timeout: 15000,
            success: function (r) {
                if (r.success && r.espacios) {
                    renderizarEspaciosAdmin(r.espacios);
                } else {
                    $('#verEspaciosContenido').html('<div class="alert alert-warning">No se pudieron cargar los espacios.</div>');
                }
            },
            error: function () {
                $('#verEspaciosContenido').html('<div class="alert alert-danger">Error de conexión al cargar los espacios.</div>');
            }
        });
    };

    function renderizarEspaciosAdmin(espacios) {
        var tipos   = ['Carro', 'Moto', 'Bicicleta'];
        var iconos  = { 'Carro': 'fa-car', 'Moto': 'fa-motorcycle', 'Bicicleta': 'fa-bicycle' };
        var colores = { 'Carro': 'text-primary', 'Moto': 'text-warning', 'Bicicleta': 'text-success' };
        var html    = '';

        tipos.forEach(function (tipo) {
            var delTipo  = espacios.filter(function (e) { return e.TipoVehiculo === tipo; });
            if (delTipo.length === 0) return;

            var libres   = delTipo.filter(function (e) { return e.Estado === 'Libre'; }).length;
            var ocupados = delTipo.filter(function (e) { return e.Estado === 'Ocupado'; }).length;

            html += '<div class="mb-4">' +
                '<h6 class="font-weight-bold ' + colores[tipo] + ' border-bottom pb-2 mb-3">' +
                '<i class="fas ' + iconos[tipo] + ' mr-2"></i>' + tipo + 's ' +
                '<span class="ml-2 badge badge-success">' + libres + ' libres</span> ' +
                '<span class="ml-1 badge badge-danger">' + ocupados + ' ocupados</span>' +
                '</h6><div class="row">';

            delTipo.forEach(function (e) {
                var libre   = e.Estado === 'Libre';
                var bgColor = libre ? '#e8f5e9' : '#ffebee';
                var border  = libre ? '#4caf50' : '#f44336';
                var icono   = libre ? 'fa-check-circle text-success' : 'fa-times-circle text-danger';
                var placa   = (!libre && e.PlacaVehiculo)
                    ? '<div><span class="badge badge-dark mt-1">' + e.PlacaVehiculo + '</span></div>' : '';

                html += '<div class="col-6 col-md-3 col-lg-2 mb-2">' +
                    '<div class="border rounded p-2 text-center h-100" ' +
                    'style="background:' + bgColor + ';border-color:' + border + '!important;">' +
                    '<div class="font-weight-bold" style="font-size:1.1rem;">#' + e.NumeroEspacio + '</div>' +
                    '<i class="fas ' + icono + ' mb-1"></i>' +
                    '<div><small class="text-muted">' + e.Estado + '</small></div>' +
                    placa +
                    '</div></div>';
            });

            html += '</div></div>';
        });

        $('#verEspaciosContenido').html(html || '<div class="alert alert-info">Sin espacios registrados.</div>');
    }

    // ══ CAMBIO DE ESTADO (Admin) ══════════════════════════════════════════════
    var parqueaderoACambiar = null;
    var estadoActual        = null;

    window.confirmarCambioEstado = function (id, estado) {
        parqueaderoACambiar = id;
        estadoActual        = estado;

        var nuevo  = estado === 'Activo' ? 'Inactivo' : 'Activo';
        var accion = nuevo  === 'Activo' ? 'activar'  : 'desactivar';
        var color  = nuevo  === 'Activo' ? 'bg-success' : 'bg-warning';
        var icono  = nuevo  === 'Activo' ? 'fa-lock-open' : 'fa-lock';

        $('#headerCambioEstado').removeClass('bg-success bg-warning').addClass(color + ' text-white');
        $('#tituloCambioEstado').html('<i class="fas ' + icono + ' mr-2"></i>' + accion.charAt(0).toUpperCase() + accion.slice(1) + ' Parqueadero');
        $('#mensajeCambioEstado').html('¿Está seguro que desea <strong>' + accion + '</strong> este parqueadero?');
        $('#modalCambiarEstado').modal('show');

        setTimeout(function () {
            var toggleLabel = document.getElementById('toggleEstadoVisualParqueadero');
            if (toggleLabel) {
                nuevo === 'Activo' ? toggleLabel.classList.add('activo') : toggleLabel.classList.remove('activo');
            }
        }, 100);
    };

    $('#btnConfirmarEstado').on('click', function () {
        if (!parqueaderoACambiar) return;

        var nuevo = estadoActual === 'Activo' ? 'Inactivo' : 'Activo';
        $('#modalCambiarEstado').modal('hide');

        Swal.fire({
            title: 'Procesando...',
            html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>',
            allowOutsideClick: false, showConfirmButton: false
        });

        $.ajax({
            url: '../../Controller/ControladorParqueadero.php',
            type: 'POST',
            data: { accion: 'cambiar_estado', id: parqueaderoACambiar, estado: nuevo },
            dataType: 'json',
            success: function (r) {
                if (r.success) {
                    Swal.fire({ icon: 'success', title: '¡Éxito!', text: r.message, timer: 2000, timerProgressBar: true, showConfirmButton: false })
                        .then(function () { location.reload(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: r.message, confirmButtonColor: '#e74a3b' });
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#e74a3b' });
            }
        });
    });

    $('#modalCambiarEstado').on('hidden.bs.modal', function () {
        parqueaderoACambiar = null;
        estadoActual        = null;
        $('#btnConfirmarEstado').prop('disabled', false).html('<i class="fas fa-check mr-1"></i>Confirmar');
    });

    // ── DataTable ─────────────────────────────────────────────────────────────
    if ($('#TablaParqueaderos').length) {
        $('#TablaParqueaderos').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json' },
            pageLength: 10,
            responsive: true
        });
    }

}); // fin esperarDependencias


// ══════════════════════════════════════════════════════════════
// SECCIÓN 2 — GUARDIA (ParqueaderoGuardia.php)
// ══════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', function () {

    // Solo ejecutar si estamos en la vista del guardia
    if (!document.getElementById('selectSede')) return;

    function esperarJQuery(cb) {
        if (typeof $ !== 'undefined') cb();
        else setTimeout(function () { esperarJQuery(cb); }, 50);
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
                    icon: 'warning', title: 'Seleccione una sede',
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
                            icon: 'warning', title: 'Sin parqueadero',
                            text: r.message || 'Esta sede no tiene parqueadero activo',
                            confirmButtonColor: '#f6c23e'
                        });
                        $('#estadoInicial').show();
                        $('#contenidoParqueadero').hide();
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error', title: 'Error de conexión',
                        text: 'No se pudo cargar la información del parqueadero',
                        confirmButtonColor: '#e74a3b'
                    });
                }
            });
        }

        // ── Renderizar tarjetas de resumen ────────────────────────────────────
        function renderizarResumen(resumen, parqueadero) {
            var total    = parseInt(parqueadero.CantidadParqueadero) || 0;
            var libres   = resumen.reduce(function (a, r) { return a + parseInt(r.Libres); }, 0);
            var ocupados = resumen.reduce(function (a, r) { return a + parseInt(r.Ocupados); }, 0);
            var pct      = total > 0 ? Math.round((ocupados / total) * 100) : 0;
            var colorBarra = pct >= 90 ? 'bg-danger' : (pct >= 60 ? 'bg-warning' : 'bg-success');

            var iconos  = { 'Carro': 'fa-car', 'Moto': 'fa-motorcycle', 'Bicicleta': 'fa-bicycle' };
            var colores = { 'Carro': 'text-primary', 'Moto': 'text-warning', 'Bicicleta': 'text-success' };
            var bgCards = { 'Carro': 'border-left-primary', 'Moto': 'border-left-warning', 'Bicicleta': 'border-left-success' };

            var html = '';

            // Tarjeta total general
            html += '<div class="col-xl-3 col-md-6 mb-4">' +
                '<div class="card shadow h-100 border-left-secondary"><div class="card-body">' +
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
                var icono  = iconos[r.TipoVehiculo]  || 'fa-car';
                var color  = colores[r.TipoVehiculo] || 'text-primary';
                var border = bgCards[r.TipoVehiculo] || 'border-left-primary';
                var pctT   = parseInt(r.Total) > 0 ? Math.round((parseInt(r.Ocupados) / parseInt(r.Total)) * 100) : 0;
                var colorT = pctT >= 90 ? 'bg-danger' : (pctT >= 60 ? 'bg-warning' : 'bg-success');

                html += '<div class="col-xl-3 col-md-4 mb-4">' +
                    '<div class="card shadow h-100 ' + border + '"><div class="card-body">' +
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
                    var libre       = e.Estado === 'Libre';
                    var bgColor     = libre ? '#e8f5e9' : '#ffebee';
                    var borderCol   = libre ? '#4caf50' : '#f44336';
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

            if (!html) html = '<div class="alert alert-info">No hay espacios registrados en este parqueadero.</div>';

            $('#gridEspacios').html(html);
        }

        // ── Abrir modal OCUPAR manualmente ────────────────────────────────────
        var iconosTipo = { 'Carro': 'fa-car', 'Moto': 'fa-motorcycle', 'Bicicleta': 'fa-bicycle' };

        window.abrirOcupar = function (idEspacio, numEspacio, tipoVehiculo) {
            $('#ocuparIdEspacio').val(idEspacio);
            $('#ocuparTipoVehiculo').val(tipoVehiculo);
            $('#ocuparNumEspacio').text(numEspacio);
            $('#ocuparTipoLabel').text(tipoVehiculo + 's');
            $('#ocuparIconoTipo').html('<i class="fas ' + (iconosTipo[tipoVehiculo] || 'fa-car') + '"></i>');
            $('#selectVehiculo').html('<option value="">-- Cargando... --</option>');
            $('#ocuparDetalleVehiculo').hide();
            $('#ocuparSelectInfo').text('');
            $('#btnConfirmarOcupar').prop('disabled', true);
            $('#modalOcuparEspacio').modal('show');

            $.ajax({
                url: '../../Controller/ControladorParqueadero.php',
                type: 'POST',
                data: { accion: 'obtener_vehiculos_tipo', tipo: tipoVehiculo, id_parqueadero: idParqueaderoActual || 0 },
                dataType: 'json',
                timeout: 10000,
                success: function (r) {
                    if (r.success && r.vehiculos && r.vehiculos.length > 0) {
                        var opts = '<option value="">-- Seleccione un vehículo --</option>';
                        r.vehiculos.forEach(function (v) {
                            var propietario   = v.NombreFuncionario || v.NombreVisitante || 'Sin propietario';
                            var identificador = v.PlacaVehiculo
                                ? v.PlacaVehiculo
                                : (v.DescripcionVehiculo ? v.DescripcionVehiculo : 'ID #' + v.IdVehiculo);
                            opts += '<option value="' + v.IdVehiculo + '" ' +
                                'data-propietario="' + propietario + '" ' +
                                'data-identificador="' + identificador + '">' +
                                identificador + ' — ' + propietario + '</option>';
                        });
                        $('#selectVehiculo').html(opts);
                        $('#ocuparSelectInfo').text(r.vehiculos.length + ' vehículo(s) disponible(s)');
                    } else {
                        $('#selectVehiculo').html('<option value="">Sin vehículos de este tipo registrados</option>');
                        $('#ocuparSelectInfo').html(
                            '<span class="text-danger"><i class="fas fa-exclamation-circle mr-1"></i>' +
                            'No hay ' + tipoVehiculo.toLowerCase() + 's activos registrados</span>'
                        );
                    }
                },
                error: function () {
                    $('#selectVehiculo').html('<option value="">Error al cargar vehículos</option>');
                }
            });
        };

        // Al cambiar el select — mostrar detalle del vehículo
        $(document).on('change', '#selectVehiculo', function () {
            var selected      = $(this).find('option:selected');
            var idVeh         = $(this).val();
            if (idVeh) {
                $('#detallePropietario').text(selected.data('propietario')   || '');
                $('#detalleIdentificador').text(selected.data('identificador') || '');
                $('#ocuparDetalleVehiculo').show();
                $('#btnConfirmarOcupar').prop('disabled', false);
            } else {
                $('#ocuparDetalleVehiculo').hide();
                $('#btnConfirmarOcupar').prop('disabled', true);
            }
        });

        // Confirmar OCUPAR
        $('#btnConfirmarOcupar').on('click', function () {
            var idEspacio  = $('#ocuparIdEspacio').val();
            var idVehiculo = $('#selectVehiculo').val();

            if (!idVehiculo) {
                Swal.fire({ icon: 'warning', title: 'Seleccione un vehículo', text: 'Debe seleccionar el vehículo a asignar', confirmButtonColor: '#f6c23e' });
                return;
            }

            $('#modalOcuparEspacio').modal('hide');
            Swal.fire({ title: 'Asignando espacio...', allowOutsideClick: false, showConfirmButton: false, didOpen: function () { Swal.showLoading(); } });

            $.ajax({
                url: '../../Controller/ControladorParqueadero.php',
                type: 'POST',
                data: { accion: 'ocupar_manual', id_espacio: idEspacio, id_vehiculo: idVehiculo },
                dataType: 'json',
                timeout: 15000,
                success: function (r) {
                    if (r.success) {
                        Swal.fire({ icon: 'success', title: '¡Espacio asignado!', text: r.message, timer: 2500, timerProgressBar: true, showConfirmButton: false })
                            .then(function () { cargarDatosSede(idSedeActual, true); });
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
            Swal.fire({ title: 'Registrando salida...', allowOutsideClick: false, showConfirmButton: false, didOpen: function () { Swal.showLoading(); } });

            $.ajax({
                url: '../../Controller/ControladorParqueadero.php',
                type: 'POST',
                data: { accion: 'liberar_manual', id_espacio: idEspacio },
                dataType: 'json',
                timeout: 15000,
                success: function (r) {
                    if (r.success) {
                        Swal.fire({ icon: 'success', title: '¡Salida registrada!', text: r.message, timer: 2500, timerProgressBar: true, showConfirmButton: false })
                            .then(function () { cargarDatosSede(idSedeActual, true); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'No se pudo liberar', text: r.message, confirmButtonColor: '#e74a3b' });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#e74a3b' });
                }
            });
        });

        // ── Limpiar al cerrar modales ─────────────────────────────────────────
        $('#modalOcuparEspacio').on('hidden.bs.modal', function () {
            $('#selectVehiculo').html('<option value="">-- Seleccione un vehículo --</option>');
            $('#ocuparDetalleVehiculo').hide();
            $('#ocuparSelectInfo').text('');
            $('#btnConfirmarOcupar').prop('disabled', true);
        });

        $('#modalLiberarEspacio').on('hidden.bs.modal', function () {
            $('#btnConfirmarLiberar').prop('disabled', false);
        });

    }); 
}); 