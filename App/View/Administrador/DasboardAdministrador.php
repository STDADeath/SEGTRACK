<?php require_once __DIR__ . '/../layouts/parte_superior_administrador.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard del Administrador</h1>
        <button id="btnExportarPDF" class="btn btn-danger btn-sm shadow-sm">
            <i class="fas fa-file-pdf fa-sm mr-1"></i> Exportar PDF
        </button>
    </div>

    <div id="panelDashboard">

        <!-- ══ TARJETAS — 8 totales ═══════════════════════════════ -->
        <div class="row mb-4">

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow-sm h-100 py-2">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Funcionarios</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" id="totalFuncionarios">
                                <span class="spinner-border spinner-border-sm text-success"></span>
                            </div>
                        </div>
                        <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow-sm h-100 py-2">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Visitantes</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" id="totalVisitantes">
                                <span class="spinner-border spinner-border-sm text-warning"></span>
                            </div>
                        </div>
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow-sm h-100 py-2">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Vehículos</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" id="totalVehiculos">
                                <span class="spinner-border spinner-border-sm text-info"></span>
                            </div>
                        </div>
                        <i class="fas fa-car fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow-sm h-100 py-2">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Dispositivos</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" id="totalDispositivos">
                                <span class="spinner-border spinner-border-sm text-primary"></span>
                            </div>
                        </div>
                        <i class="fas fa-tablet-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-dark shadow-sm h-100 py-2">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Dotación</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" id="totalDotacion">
                                <span class="spinner-border spinner-border-sm text-dark"></span>
                            </div>
                        </div>
                        <i class="fas fa-tshirt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-secondary shadow-sm h-100 py-2">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Bitácoras</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" id="totalBitacora">
                                <span class="spinner-border spinner-border-sm text-secondary"></span>
                            </div>
                        </div>
                        <i class="fas fa-book fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow-sm h-100 py-2">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Sedes</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" id="totalSedes">
                                <span class="spinner-border spinner-border-sm text-danger"></span>
                            </div>
                        </div>
                        <i class="fas fa-building fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow-sm h-100 py-2" style="border-left: 4px solid #6f42c1;">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color:#6f42c1;">Instituciones</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" id="totalInstitutos">
                                <span class="spinner-border spinner-border-sm" style="color:#6f42c1;"></span>
                            </div>
                        </div>
                        <i class="fas fa-university fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>

        </div>

        <!-- ══ SECCIÓN: FUNCIONARIOS ══════════════════════════════ -->
        <div class="row mb-2"><div class="col-12">
            <h5 class="font-weight-bold text-success border-bottom pb-2">
                <i class="fas fa-user-tie mr-2"></i> Funcionarios
            </h5>
        </div></div>
        <div class="row mb-4">
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3 bg-success text-white">
                        <h6 class="m-0 font-weight-bold">Por Cargo</h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative; height:280px;">
                            <canvas id="graficoFuncionariosCargo"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3 bg-success text-white">
                        <h6 class="m-0 font-weight-bold">Por Sede</h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative; height:280px;">
                            <canvas id="graficoFuncionariosSede"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ SECCIÓN: VISITANTES ════════════════════════════════ -->
        <div class="row mb-2"><div class="col-12">
            <h5 class="font-weight-bold text-warning border-bottom pb-2">
                <i class="fas fa-users mr-2"></i> Visitantes e Ingresos
            </h5>
        </div></div>
        <div class="row mb-4">
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3 bg-warning text-white">
                        <h6 class="m-0 font-weight-bold">Ingresos por Mes (últimos 12 meses)</h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative; height:280px;">
                            <canvas id="graficoIngresosMes"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3 bg-warning text-white">
                        <h6 class="m-0 font-weight-bold">Entrada vs Salida</h6>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center" style="height:310px;">
                        <canvas id="graficoIngresosTipo" style="max-height:260px; max-width:260px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ SECCIÓN: VEHÍCULOS ══════════════════════════════════ -->
        <div class="row mb-2"><div class="col-12">
            <h5 class="font-weight-bold text-info border-bottom pb-2">
                <i class="fas fa-car mr-2"></i> Vehículos
            </h5>
        </div></div>
        <div class="row mb-4">
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3 bg-info text-white">
                        <h6 class="m-0 font-weight-bold">Por Tipo</h6>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center" style="height:310px;">
                        <canvas id="graficoVehiculos" style="max-height:260px; max-width:260px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3 bg-info text-white">
                        <h6 class="m-0 font-weight-bold">Por Sede</h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative; height:280px;">
                            <canvas id="graficoVehiculosSede"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ SECCIÓN: DISPOSITIVOS ═══════════════════════════════ -->
        <div class="row mb-2"><div class="col-12">
            <h5 class="font-weight-bold text-primary border-bottom pb-2">
                <i class="fas fa-tablet-alt mr-2"></i> Dispositivos
            </h5>
        </div></div>
        <div class="row mb-4">
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3 bg-primary text-white">
                        <h6 class="m-0 font-weight-bold">Por Tipo</h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative; height:280px;">
                            <canvas id="graficoDispositivos"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ SECCIÓN: DOTACIÓN ═══════════════════════════════════ -->
        <div class="row mb-2"><div class="col-12">
            <h5 class="font-weight-bold text-dark border-bottom pb-2">
                <i class="fas fa-tshirt mr-2"></i> Dotación
            </h5>
        </div></div>
        <div class="row mb-4">
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3 bg-dark text-white">
                        <h6 class="m-0 font-weight-bold">Por Tipo</h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative; height:280px;">
                            <canvas id="graficoDotacionTipo"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3 bg-secondary text-white">
                        <h6 class="m-0 font-weight-bold">Por Estado</h6>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center" style="height:310px;">
                        <canvas id="graficoDotacionEstado" style="max-height:260px; max-width:260px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3 bg-primary text-white">
                        <h6 class="m-0 font-weight-bold">Entregadas por Mes</h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative; height:280px;">
                            <canvas id="graficoDotacionesMes"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3 bg-info text-white">
                        <h6 class="m-0 font-weight-bold">Devueltas por Mes</h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative; height:280px;">
                            <canvas id="graficoDotacionesDevolucion"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ SECCIÓN: BITÁCORA ═══════════════════════════════════ -->
        <div class="row mb-2"><div class="col-12">
            <h5 class="font-weight-bold text-secondary border-bottom pb-2">
                <i class="fas fa-book mr-2"></i> Bitácora
            </h5>
        </div></div>
        <div class="row mb-4">
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3 bg-primary text-white">
                        <h6 class="m-0 font-weight-bold">Por Turno</h6>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center" style="height:310px;">
                        <canvas id="graficoBitacoraTurno" style="max-height:260px; max-width:260px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3 bg-secondary text-white">
                        <h6 class="m-0 font-weight-bold">Por Mes</h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative; height:280px;">
                            <canvas id="graficoBitacoraMes"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ SECCIÓN: SEDES E INSTITUCIONES ═════════════════════ -->
        <div class="row mb-2"><div class="col-12">
            <h5 class="font-weight-bold text-danger border-bottom pb-2">
                <i class="fas fa-building mr-2"></i> Sedes e Instituciones
            </h5>
        </div></div>
        <div class="row mb-4">
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3 bg-danger text-white">
                        <h6 class="m-0 font-weight-bold">Sedes por Ciudad</h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative; height:280px;">
                            <canvas id="graficoSedesCiudad"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3 text-white" style="background-color:#6f42c1;">
                        <h6 class="m-0 font-weight-bold">Sedes por Institución</h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative; height:280px;">
                            <canvas id="graficoSedesInstitucion"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3 text-white" style="background-color:#6f42c1;">
                        <h6 class="m-0 font-weight-bold">Instituciones por Tipo</h6>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center" style="height:310px;">
                        <canvas id="graficoInstitucionesTipo" style="max-height:260px; max-width:260px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3 bg-danger text-white">
                        <h6 class="m-0 font-weight-bold">Funcionarios por Sede (todas)</h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative; height:280px;">
                            <canvas id="graficoTodasSedes"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /panelDashboard -->
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", async () => {

    // ── Ruta del controlador ──────────────────────────────────────
    // Vista en:       App/View/Administrador/
    // Controlador en: App/Controller/
    const BASE = "../../Controller/ControladorDashboard.php";

    const C = [
        'rgba(78,115,223,0.85)',  'rgba(28,200,138,0.85)',
        'rgba(246,194,62,0.85)', 'rgba(231,74,59,0.85)',
        'rgba(54,162,235,0.85)', 'rgba(153,102,255,0.85)',
        'rgba(255,159,64,0.85)', 'rgba(52,58,64,0.85)',
        'rgba(32,201,151,0.85)', 'rgba(255,99,132,0.85)',
        'rgba(255,205,86,0.85)', 'rgba(75,192,192,0.85)'
    ];

    async function get(accion) {
        const r = await fetch(`${BASE}?accion=${accion}`);
        const t = await r.text();
        try { return JSON.parse(t); }
        catch(e) { console.error(`❌ [${accion}]:`, t.substring(0,300)); throw e; }
    }

    function setCard(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val ?? '0';
    }

    function barras(id, labels, datos, titulo, horizontal = false) {
        const ctx = document.getElementById(id);
        if (!ctx) return;
        new Chart(ctx, {
            type: 'bar',
            data: { labels, datasets: [{
                label: titulo || 'Total', data: datos,
                backgroundColor: C, borderRadius: 5, borderWidth: 1
            }]},
            options: {
                indexAxis: horizontal ? 'y' : 'x',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { [horizontal ? 'x' : 'y']: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    function dona(id, labels, datos, tipo = 'doughnut') {
        const ctx = document.getElementById(id);
        if (!ctx) return;
        new Chart(ctx, {
            type: tipo,
            data: { labels, datasets: [{
                data: datos, backgroundColor: C,
                borderColor: '#fff', borderWidth: 2
            }]},
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                cutout: tipo === 'doughnut' ? '60%' : 0
            }
        });
    }

    function linea(id, labels, datos, color) {
        const ctx = document.getElementById(id);
        if (!ctx) return;
        color = color || 'rgba(78,115,223,1)';
        new Chart(ctx, {
            type: 'line',
            data: { labels, datasets: [{
                label: 'Total', data: datos,
                borderColor: color,
                backgroundColor: color.replace('1)', '0.15)'),
                borderWidth: 2, tension: 0.4, fill: true,
                pointBackgroundColor: color, pointRadius: 4
            }]},
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    // ── TARJETAS ─────────────────────────────────────────────────
    try {
        const [func, vis, veh, dis, dot, bit, sed, ins] = await Promise.all([
            get('total_funcionarios'), get('total_visitantes'),
            get('total_vehiculos'),    get('total_dispositivos'),
            get('total_dotacion'),     get('total_bitacora'),
            get('total_sedes'),        get('total_institutos')
        ]);
        setCard('totalFuncionarios', func.total_funcionarios);
        setCard('totalVisitantes',   vis.total_visitantes);
        setCard('totalVehiculos',    veh.total_vehiculos);
        setCard('totalDispositivos', dis.total_dispositivos);
        setCard('totalDotacion',     dot.total_dotacion);
        setCard('totalBitacora',     bit.total_bitacora);
        setCard('totalSedes',        sed.total_sedes);
        setCard('totalInstitutos',   ins.total_institutos);
    } catch(e) { console.error('❌ Totales:', e.message); }

    // ── FUNCIONARIOS ─────────────────────────────────────────────
    try {
        const d = await get('funcionarios_por_cargo');
        barras('graficoFuncionariosCargo', d.map(r=>r.CargoFuncionario), d.map(r=>r.total), 'Funcionarios', true);
    } catch(e) { console.error('❌ Func/cargo:', e.message); }

    try {
        const d = await get('funcionarios_por_sede');
        barras('graficoFuncionariosSede', d.map(r=>r.NombreSede), d.map(r=>r.total), 'Funcionarios');
    } catch(e) { console.error('❌ Func/sede:', e.message); }

    // ── VISITANTES / INGRESOS ────────────────────────────────────
    try {
        const d = await get('visitantes_por_mes');
        linea('graficoIngresosMes', d.map(r=>r.mes), d.map(r=>r.total), 'rgba(246,194,62,1)');
    } catch(e) { console.error('❌ Ing/mes:', e.message); }

    try {
        const d = await get('ingresos_por_tipo');
        dona('graficoIngresosTipo', d.map(r=>r.tipo), d.map(r=>r.total));
    } catch(e) { console.error('❌ Ing/tipo:', e.message); }

    // ── VEHÍCULOS ─────────────────────────────────────────────────
    try {
        const d = await get('vehiculos_por_tipo');
        dona('graficoVehiculos', d.map(r=>r.tipo_vehiculos), d.map(r=>r.cantidad_Vehiculos));
    } catch(e) { console.error('❌ Veh/tipo:', e.message); }

    try {
        const d = await get('vehiculos_por_sede');
        barras('graficoVehiculosSede', d.map(r=>r.NombreSede), d.map(r=>r.total), 'Vehículos');
    } catch(e) { console.error('❌ Veh/sede:', e.message); }

    // ── DISPOSITIVOS ─────────────────────────────────────────────
    try {
        const d = await get('tipos_dispositivos');
        barras('graficoDispositivos', d.map(r=>r.tipo_dispositivos), d.map(r=>r.cantidad_Dispositivos), 'Dispositivos');
    } catch(e) { console.error('❌ Disp/tipo:', e.message); }

    // ── DOTACIÓN ─────────────────────────────────────────────────
    try {
        const d = await get('dotacion_por_tipo');
        barras('graficoDotacionTipo', d.map(r=>r.tipo_dotaciones), d.map(r=>r.cantidad_dotaciones), 'Dotaciones');
    } catch(e) { console.error('❌ Dot/tipo:', e.message); }

    try {
        const d = await get('dotacion_por_estado');
        dona('graficoDotacionEstado', d.map(r=>r.estado_dotaciones), d.map(r=>r.cantidad_estado_dotaciones));
    } catch(e) { console.error('❌ Dot/estado:', e.message); }

    try {
        const d = await get('dotaciones_por_mes');
        linea('graficoDotacionesMes', d.map(r=>r.mes), d.map(r=>r.cantidad), 'rgba(78,115,223,1)');
    } catch(e) { console.error('❌ Dot/mes:', e.message); }

    try {
        const d = await get('dotaciones_por_devolucion');
        linea('graficoDotacionesDevolucion', d.map(r=>r.mes), d.map(r=>r.cantidad), 'rgba(28,200,138,1)');
    } catch(e) { console.error('❌ Dot/dev:', e.message); }

    // ── BITÁCORA ─────────────────────────────────────────────────
    try {
        const d = await get('bitacora_por_turno');
        dona('graficoBitacoraTurno', d.map(r=>r.turno_bitacoras), d.map(r=>r.cantidad_bitacoras_turno), 'pie');
    } catch(e) { console.error('❌ Bit/turno:', e.message); }

    try {
        const d = await get('bitacora_por_mes');
        linea('graficoBitacoraMes', d.map(r=>r.mes), d.map(r=>r.cantidad), 'rgba(78,115,223,1)');
    } catch(e) { console.error('❌ Bit/mes:', e.message); }

    // ── SEDES E INSTITUCIONES ────────────────────────────────────
    try {
        const d = await get('sedes_por_ciudad');
        barras('graficoSedesCiudad', d.map(r=>r.ciudad), d.map(r=>r.total), 'Sedes');
    } catch(e) { console.error('❌ Sedes/ciudad:', e.message); }

    try {
        const d = await get('sedes_por_institucion');
        barras('graficoSedesInstitucion', d.map(r=>r.institucion), d.map(r=>r.total_sedes), 'Sedes');
    } catch(e) { console.error('❌ Sedes/inst:', e.message); }

    try {
        const d = await get('instituciones_por_tipo');
        dona('graficoInstitucionesTipo', d.map(r=>r.tipo_institucion), d.map(r=>r.total));
    } catch(e) { console.error('❌ Inst/tipo:', e.message); }

    try {
        // Muestra las 12 sedes individuales con funcionarios asignados
        const d = await get('todas_las_sedes');
        barras('graficoTodasSedes', d.map(r=>r.NombreSede), d.map(r=>r.total_funcionarios), 'Funcionarios');
    } catch(e) { console.error('❌ Todas sedes:', e.message); }

    // ── EXPORTAR PDF ─────────────────────────────────────────────
    document.getElementById('btnExportarPDF').addEventListener('click', async () => {
        const { jsPDF } = window.jspdf;
        const panel = document.getElementById('panelDashboard');
        const btn   = document.getElementById('btnExportarPDF');
        btn.disabled  = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Generando...';

        const canvas = await html2canvas(panel, { scale: 1.5, useCORS: true });
        const pdf    = new jsPDF('p', 'mm', 'a4');
        const pageW  = pdf.internal.pageSize.getWidth();
        const pageH  = pdf.internal.pageSize.getHeight();
        const imgW   = pageW - 20;
        const imgH   = imgW / (canvas.width / canvas.height);

        let posY = 10;
        pdf.setFontSize(14); pdf.setTextColor(40);
        pdf.text('Dashboard Administrador — SEGTRACK', 10, posY); posY += 6;
        pdf.setFontSize(9);  pdf.setTextColor(120);
        pdf.text('Generado: ' + new Date().toLocaleString('es-CO'), 10, posY); posY += 6;

        let restante = imgH, srcY = 0;
        while (restante > 0) {
            const bloque = Math.min(pageH - posY - 10, restante);
            const srcH   = (bloque / imgH) * canvas.height;
            const tmp    = document.createElement('canvas');
            tmp.width = canvas.width; tmp.height = srcH;
            tmp.getContext('2d').drawImage(canvas, 0, srcY, canvas.width, srcH, 0, 0, canvas.width, srcH);
            pdf.addImage(tmp.toDataURL('image/png'), 'PNG', 10, posY, imgW, bloque);
            restante -= bloque; srcY += srcH; posY = 10;
            if (restante > 0) pdf.addPage();
        }
        pdf.save('dashboard_administrador.pdf');
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-file-pdf fa-sm mr-1"></i> Exportar PDF';
    });

});
</script>

<?php require_once __DIR__ . '/../layouts/parte_inferior_administrador.php'; ?>