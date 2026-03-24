<?php require_once __DIR__ . '/../layouts/parte_superior_supervisor.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<div class="container-fluid">

    <!-- ══ ENCABEZADO ══════════════════════════════════════════════ -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard del Supervisor</h1>
        <button id="btnExportarPDF" class="btn btn-danger btn-sm shadow-sm">
            <i class="fas fa-file-pdf fa-sm mr-1"></i> Exportar PDF
        </button>
    </div>

    <div id="panelDashboard">

        <!-- ══ FILA 1 — 5 TARJETAS ════════════════════════════════
             Funcionarios | Visitantes | Vehículos | Dotación | Bitácoras
        ════════════════════════════════════════════════════════════ -->
        <div class="row mb-4">

            <!-- Funcionarios -->
            <div class="col-xl-2 col-md-4 mb-4">
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

            <!-- Visitantes -->
            <div class="col-xl-2 col-md-4 mb-4">
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

            <!-- Vehículos -->
            <div class="col-xl-2 col-md-4 mb-4">
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

            <!-- Dotación -->
            <div class="col-xl-2 col-md-4 mb-4">
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

            <!-- Bitácoras -->
            <div class="col-xl-2 col-md-4 mb-4">
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

            <!-- Sedes -->
            <div class="col-xl-2 col-md-4 mb-4">
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

        </div><!-- /fila tarjetas -->

        <!-- ══ FILA 2 — Vehículos por tipo | Vehículos por sede ════ -->
        <div class="row mb-4">

            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-car mr-1"></i> Vehículos por Tipo
                        </h6>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center" style="height:310px;">
                        <canvas id="graficoVehiculos" style="max-height:260px; max-width:260px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-danger">
                            <i class="fas fa-parking mr-1"></i> Vehículos por Sede
                        </h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative; height:280px;">
                            <canvas id="graficoVehiculosSede"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ══ FILA 3 — Funcionarios por cargo | Funcionarios por sede ═ -->
        <div class="row mb-4">

            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-user-tie mr-1"></i> Funcionarios por Cargo
                        </h6>
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
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-secondary">
                            <i class="fas fa-building mr-1"></i> Funcionarios por Sede
                        </h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative; height:280px;">
                            <canvas id="graficoFuncionariosSede"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ══ FILA 4 — Ingresos por mes | Entrada vs Salida ═══════ -->
        <div class="row mb-4">

            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-chart-line mr-1"></i> Ingresos por Mes
                        </h6>
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
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-exchange-alt mr-1"></i> Ingresos: Entrada vs Salida
                        </h6>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center" style="height:310px;">
                        <canvas id="graficoIngresosTipo" style="max-height:260px; max-width:260px;"></canvas>
                    </div>
                </div>
            </div>

        </div>

        <!-- ══ FILA 5 — Dotación por tipo | Dotación por estado ════ -->
        <div class="row mb-4">

            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-tshirt mr-1"></i> Dotación por Tipo
                        </h6>
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
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-clipboard-check mr-1"></i> Dotación por Estado
                        </h6>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center" style="height:310px;">
                        <canvas id="graficoDotacionEstado" style="max-height:260px; max-width:260px;"></canvas>
                    </div>
                </div>
            </div>

        </div>

        <!-- ══ FILA 6 — Dotación entregas por mes | Devoluciones por mes ═ -->
        <div class="row mb-4">

            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3 bg-primary text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-calendar-check mr-1"></i> Dotaciones Entregadas por Mes
                        </h6>
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
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-undo mr-1"></i> Dotaciones Devueltas por Mes
                        </h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative; height:280px;">
                            <canvas id="graficoDotacionesDevolucion"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ══ FILA 7 — Bitácora por turno | Bitácora por mes ══════ -->
        <div class="row mb-4">

            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-book mr-1"></i> Bitácora por Turno
                        </h6>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center" style="height:310px;">
                        <canvas id="graficoBitacoraTurno" style="max-height:260px; max-width:260px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-secondary">
                            <i class="fas fa-chart-bar mr-1"></i> Bitácoras por Mes
                        </h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative; height:280px;">
                            <canvas id="graficoBitacoraMes"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div><!-- /panelDashboard -->
</div>

<!-- ══ SCRIPTS ════════════════════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", async () => {

    // ── Ruta del controlador ──────────────────────────────────────
    // Vista en:       App/View/Supervisor/
    // Controlador en: App/Controller/
    // → mismo ControladorDashboard.php que usa Personal Seguridad
    const BASE = "../../Controller/ControladorDashboard.php";

    // ── Paleta de colores ─────────────────────────────────────────
    const C = [
        'rgba(78,115,223,0.85)',
        'rgba(28,200,138,0.85)',
        'rgba(246,194,62,0.85)',
        'rgba(231,74,59,0.85)',
        'rgba(54,162,235,0.85)',
        'rgba(153,102,255,0.85)',
        'rgba(255,159,64,0.85)',
        'rgba(52,58,64,0.85)'
    ];

    // ── Helper fetch seguro ───────────────────────────────────────
    async function get(accion) {
        const resp = await fetch(`${BASE}?accion=${accion}`);
        const text = await resp.text();
        try {
            return JSON.parse(text);
        } catch(e) {
            console.error(`❌ [${accion}] No es JSON:`, text.substring(0, 300));
            throw new Error('Respuesta no es JSON');
        }
    }

    function setCard(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = (val !== undefined && val !== null) ? val : '0';
    }

    function barras(id, labels, datos, titulo, horizontal = false) {
        const ctx = document.getElementById(id);
        if (!ctx) return;
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: titulo || 'Total',
                    data: datos,
                    backgroundColor: C,
                    borderRadius: 5,
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: horizontal ? 'y' : 'x',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    [horizontal ? 'x' : 'y']: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }

    function dona(id, labels, datos, tipo = 'doughnut') {
        const ctx = document.getElementById(id);
        if (!ctx) return;
        new Chart(ctx, {
            type: tipo,
            data: {
                labels,
                datasets: [{
                    data: datos,
                    backgroundColor: C,
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
            data: {
                labels,
                datasets: [{
                    label: 'Total',
                    data: datos,
                    borderColor: color,
                    backgroundColor: color.replace('1)', '0.15)'),
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: color,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    // ════════════════════════════════════════════════════════════
    //  TARJETAS — 6 totales en paralelo
    // ════════════════════════════════════════════════════════════
    try {
        const [func, vis, veh, dot, bit, sed] = await Promise.all([
            get('total_funcionarios'),
            get('total_visitantes'),
            get('total_vehiculos'),
            get('total_dotacion'),
            get('total_bitacora'),
            get('total_sedes')
        ]);
        setCard('totalFuncionarios', func.total_funcionarios);
        setCard('totalVisitantes',   vis.total_visitantes);
        setCard('totalVehiculos',    veh.total_vehiculos);
        setCard('totalDotacion',     dot.total_dotacion);
        setCard('totalBitacora',     bit.total_bitacora);
        setCard('totalSedes',        sed.total_sedes);
    } catch(e) {
        console.error('❌ Totales:', e.message);
        ['totalFuncionarios','totalVisitantes','totalVehiculos',
         'totalDotacion','totalBitacora','totalSedes']
            .forEach(id => setCard(id, 'Error'));
    }

    // ════════════════════════════════════════════════════════════
    //  VEHÍCULOS
    // ════════════════════════════════════════════════════════════
    try {
        const d = await get('vehiculos_por_tipo');
        dona('graficoVehiculos', d.map(r => r.tipo_vehiculos), d.map(r => r.cantidad_Vehiculos));
    } catch(e) { console.error('❌ Veh/tipo:', e.message); }

    try {
        const d = await get('vehiculos_por_sede');
        barras('graficoVehiculosSede', d.map(r => r.NombreSede), d.map(r => r.total), 'Vehículos');
    } catch(e) { console.error('❌ Veh/sede:', e.message); }

    // ════════════════════════════════════════════════════════════
    //  FUNCIONARIOS
    // ════════════════════════════════════════════════════════════
    try {
        const d = await get('funcionarios_por_cargo');
        barras('graficoFuncionariosCargo',
            d.map(r => r.CargoFuncionario), d.map(r => r.total), 'Funcionarios', true);
    } catch(e) { console.error('❌ Func/cargo:', e.message); }

    try {
        const d = await get('funcionarios_por_sede');
        barras('graficoFuncionariosSede', d.map(r => r.NombreSede), d.map(r => r.total), 'Funcionarios');
    } catch(e) { console.error('❌ Func/sede:', e.message); }

    // ════════════════════════════════════════════════════════════
    //  INGRESOS
    // ════════════════════════════════════════════════════════════
    try {
        const d = await get('visitantes_por_mes');
        linea('graficoIngresosMes', d.map(r => r.mes), d.map(r => r.total), 'rgba(246,194,62,1)');
    } catch(e) { console.error('❌ Ing/mes:', e.message); }

    try {
        const d = await get('ingresos_por_tipo');
        dona('graficoIngresosTipo', d.map(r => r.tipo), d.map(r => r.total));
    } catch(e) { console.error('❌ Ing/tipo:', e.message); }

    // ════════════════════════════════════════════════════════════
    //  DOTACIÓN
    // ════════════════════════════════════════════════════════════
    try {
        const d = await get('dotacion_por_tipo');
        // columnas: tipo_dotaciones, cantidad_dotaciones  (del ModeloDashboard)
        barras('graficoDotacionTipo',
            d.map(r => r.tipo_dotaciones), d.map(r => r.cantidad_dotaciones), 'Dotaciones');
    } catch(e) { console.error('❌ Dot/tipo:', e.message); }

    try {
        const d = await get('dotacion_por_estado');
        // columnas: estado_dotaciones, cantidad_estado_dotaciones
        dona('graficoDotacionEstado',
            d.map(r => r.estado_dotaciones), d.map(r => r.cantidad_estado_dotaciones));
    } catch(e) { console.error('❌ Dot/estado:', e.message); }

    try {
        const d = await get('dotaciones_por_mes');
        linea('graficoDotacionesMes', d.map(r => r.mes), d.map(r => r.cantidad), 'rgba(78,115,223,1)');
    } catch(e) { console.error('❌ Dot/mes:', e.message); }

    try {
        const d = await get('dotaciones_por_devolucion');
        linea('graficoDotacionesDevolucion', d.map(r => r.mes), d.map(r => r.cantidad), 'rgba(28,200,138,1)');
    } catch(e) { console.error('❌ Dot/dev:', e.message); }

    // ════════════════════════════════════════════════════════════
    //  BITÁCORA
    // ════════════════════════════════════════════════════════════
    try {
        const d = await get('bitacora_por_turno');
        // columnas: turno_bitacoras, cantidad_bitacoras_turno
        dona('graficoBitacoraTurno',
            d.map(r => r.turno_bitacoras), d.map(r => r.cantidad_bitacoras_turno), 'pie');
    } catch(e) { console.error('❌ Bit/turno:', e.message); }

    try {
        const d = await get('bitacora_por_mes');
        linea('graficoBitacoraMes', d.map(r => r.mes), d.map(r => r.cantidad), 'rgba(78,115,223,1)');
    } catch(e) { console.error('❌ Bit/mes:', e.message); }

    // ════════════════════════════════════════════════════════════
    //  EXPORTAR PDF
    // ════════════════════════════════════════════════════════════
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
        pdf.text('Dashboard Supervisor — SEGTRACK', 10, posY);
        posY += 6;
        pdf.setFontSize(9); pdf.setTextColor(120);
        pdf.text('Generado: ' + new Date().toLocaleString('es-CO'), 10, posY);
        posY += 6;

        let restante = imgH, srcY = 0;
        while (restante > 0) {
            const bloque = Math.min(pageH - posY - 10, restante);
            const srcH   = (bloque / imgH) * canvas.height;
            const tmp    = document.createElement('canvas');
            tmp.width    = canvas.width;
            tmp.height   = srcH;
            tmp.getContext('2d').drawImage(
                canvas, 0, srcY, canvas.width, srcH,
                0, 0, canvas.width, srcH
            );
            pdf.addImage(tmp.toDataURL('image/png'), 'PNG', 10, posY, imgW, bloque);
            restante -= bloque;
            srcY     += srcH;
            posY      = 10;
            if (restante > 0) pdf.addPage();
        }

        pdf.save('dashboard_supervisor.pdf');
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-file-pdf fa-sm mr-1"></i> Exportar PDF';
    });

});
</script>

<?php require_once __DIR__ . '/../layouts/parte_inferior_supervisor.php'; ?>