<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<div class="container-fluid">

    <!-- ══ ENCABEZADO ══════════════════════════════════════════════ -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard de Seguridad</h1>
        <button id="btnExportarPDF" class="btn btn-danger btn-sm shadow-sm">
            <i class="fas fa-file-pdf fa-sm mr-1"></i> Exportar PDF
        </button>
    </div>

    <div id="panelDashboard">

        <!-- ══ FILA 1 — 4 TARJETAS ════════════════════════════════ -->
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

        </div><!-- /fila tarjetas -->

        <!-- ══ FILA 2 — Dispositivos por tipo | Vehículos por tipo ═ -->
        <div class="row mb-4">

            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-tablet-alt mr-1"></i> Dispositivos por Tipo
                        </h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative; height:280px;">
                            <canvas id="graficoDispositivos"></canvas>
                        </div>
                    </div>
                </div>
            </div>

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

        </div>

        <!-- ══ FILA 3 — Funcionarios por cargo | Ingresos por mes ══ -->
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

        </div>

        <!-- ══ FILA 4 — Funcionarios por sede | Vehículos por sede ═ -->
        <div class="row mb-4">

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

        <!-- ══ FILA 5 — Entrada vs Salida | Bitácora por turno ═════ -->
        <div class="row mb-4">

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

        </div>

        <!-- ══ FILA 6 — Dotación por tipo | Dotación por estado ════ -->
        <div class="row mb-4">

            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">
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

    </div><!-- /panelDashboard -->
</div>

<!-- ══ SCRIPTS ════════════════════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", async () => {

    // ── Ruta del controlador desde la vista ──────────────────────
    const BASE = "../../Controller/ControladorDashboard.php";

    // ── Paleta de colores ────────────────────────────────────────
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

    // ── Helper fetch con manejo de errores mejorado ────────────────
    async function get(accion) {
        try {
            const resp = await fetch(`${BASE}?accion=${accion}`);
            if (!resp.ok) {
                throw new Error(`HTTP ${resp.status}: ${resp.statusText}`);
            }
            const text = await resp.text();
            
            // Verificar si la respuesta está vacía
            if (!text || text.trim() === '') {
                console.warn(`⚠️ [${accion}] Respuesta vacía`);
                return [];
            }
            
            try {
                const data = JSON.parse(text);
                // Verificar que data sea un array (para la mayoría de gráficos)
                if (Array.isArray(data)) {
                    return data;
                } else if (data && typeof data === 'object') {
                    // Si es un objeto, devolverlo tal cual
                    return data;
                } else {
                    console.warn(`⚠️ [${accion}] Datos no válidos:`, data);
                    return [];
                }
            } catch(e) {
                console.error(`❌ [${accion}] Error parseando JSON:`, text.substring(0, 200));
                return [];
            }
        } catch(e) {
            console.error(`❌ [${accion}] Error en fetch:`, e.message);
            return [];
        }
    }

    // ── Helper tarjeta ───────────────────────────────────────────
    function setCard(id, val) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = (val !== undefined && val !== null && val !== 0) ? val : '0';
        }
    }

    // ── Helper barras con validación de datos ─────────────────────
    function barras(id, labels, datos, titulo, horizontal = false) {
        const ctx = document.getElementById(id);
        if (!ctx) {
            console.warn(`⚠️ Canvas ${id} no encontrado`);
            return;
        }
        
        // Validar que haya datos
        if (!labels || !datos || labels.length === 0 || datos.length === 0) {
            console.warn(`⚠️ Sin datos para ${id}`);
            // Mostrar mensaje en el canvas
            const parent = ctx.parentElement;
            if (parent && !parent.querySelector('.no-data-message')) {
                const msg = document.createElement('div');
                msg.className = 'no-data-message text-center text-muted';
                msg.style.padding = '40px 0';
                msg.innerHTML = '<i class="fas fa-chart-bar fa-2x mb-2"></i><br>No hay datos disponibles';
                parent.style.position = 'relative';
                parent.appendChild(msg);
            }
            return;
        }
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: titulo || 'Total',
                    data: datos,
                    backgroundColor: C.slice(0, labels.length),
                    borderRadius: 5,
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: horizontal ? 'y' : 'x',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.raw} registros` } }
                },
                scales: {
                    [horizontal ? 'x' : 'y']: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }

    // ── Helper dona / torta con validación ────────────────────────
    function dona(id, labels, datos, tipo = 'doughnut') {
        const ctx = document.getElementById(id);
        if (!ctx) {
            console.warn(`⚠️ Canvas ${id} no encontrado`);
            return;
        }
        
        // Validar que haya datos
        if (!labels || !datos || labels.length === 0 || datos.length === 0) {
            console.warn(`⚠️ Sin datos para ${id}`);
            const parent = ctx.parentElement;
            if (parent && !parent.querySelector('.no-data-message')) {
                const msg = document.createElement('div');
                msg.className = 'no-data-message text-center text-muted';
                msg.style.padding = '40px 0';
                msg.innerHTML = '<i class="fas fa-chart-pie fa-2x mb-2"></i><br>No hay datos disponibles';
                parent.style.position = 'relative';
                parent.appendChild(msg);
            }
            return;
        }
        
        new Chart(ctx, {
            type: tipo,
            data: {
                labels,
                datasets: [{
                    data: datos,
                    backgroundColor: C.slice(0, labels.length),
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.raw} registros` } }
                },
                cutout: tipo === 'doughnut' ? '60%' : 0
            }
        });
    }

    // ── Helper línea con validación ───────────────────────────────
    function linea(id, labels, datos, color) {
        const ctx = document.getElementById(id);
        if (!ctx) {
            console.warn(`⚠️ Canvas ${id} no encontrado`);
            return;
        }
        
        if (!labels || !datos || labels.length === 0 || datos.length === 0) {
            console.warn(`⚠️ Sin datos para ${id}`);
            return;
        }
        
        color = color || 'rgba(246,194,62,1)';
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
                plugins: { 
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.raw} registros` } }
                },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    // ════════════════════════════════════════════════════════════
    //  TARJETAS — 4 totales en paralelo
    // ════════════════════════════════════════════════════════════
    try {
        const [func, vis, veh, dis] = await Promise.all([
            get('total_funcionarios'),
            get('total_visitantes'),
            get('total_vehiculos'),
            get('total_dispositivos')
        ]);
        setCard('totalFuncionarios', func.total_funcionarios);
        setCard('totalVisitantes',   vis.total_visitantes);
        setCard('totalVehiculos',    veh.total_vehiculos);
        setCard('totalDispositivos', dis.total_dispositivos);
    } catch(e) {
        console.error('❌ Totales:', e.message);
        ['totalFuncionarios','totalVisitantes','totalVehiculos','totalDispositivos']
            .forEach(id => {
                const el = document.getElementById(id);
                if (el) el.textContent = 'Error';
            });
    }

    // ════════════════════════════════════════════════════════════
    //  GRÁFICA 1 — Dispositivos por tipo (barras)
    // ════════════════════════════════════════════════════════════
    const dispositivosData = await get('tipos_dispositivos');
    if (dispositivosData && dispositivosData.length > 0) {
        barras('graficoDispositivos',
            dispositivosData.map(r => r.tipo_dispositivos || r.Tipo || 'Sin tipo'),
            dispositivosData.map(r => parseInt(r.cantidad_Dispositivos) || 0),
            'Dispositivos'
        );
    } else {
        console.warn('⚠️ No hay datos de dispositivos');
    }

    // ════════════════════════════════════════════════════════════
    //  GRÁFICA 2 — Vehículos por tipo (dona)
    // ════════════════════════════════════════════════════════════
    const vehiculosTipoData = await get('vehiculos_por_tipo');
    if (vehiculosTipoData && vehiculosTipoData.length > 0) {
        dona('graficoVehiculos',
            vehiculosTipoData.map(r => r.tipo_vehiculos || r.Tipo || 'Sin tipo'),
            vehiculosTipoData.map(r => parseInt(r.cantidad_Vehiculos) || 0)
        );
    } else {
        console.warn('⚠️ No hay datos de vehículos por tipo');
    }

    // ════════════════════════════════════════════════════════════
    //  GRÁFICA 3 — Funcionarios por cargo (barras horizontales)
    // ════════════════════════════════════════════════════════════
    const funcionariosCargoData = await get('funcionarios_por_cargo');
    if (funcionariosCargoData && funcionariosCargoData.length > 0) {
        barras('graficoFuncionariosCargo',
            funcionariosCargoData.map(r => r.CargoFuncionario || r.cargo || 'Sin cargo'),
            funcionariosCargoData.map(r => parseInt(r.total) || 0),
            'Funcionarios',
            true
        );
    } else {
        console.warn('⚠️ No hay datos de funcionarios por cargo');
    }

    // ════════════════════════════════════════════════════════════
    //  GRÁFICA 4 — Ingresos por mes (línea)
    // ════════════════════════════════════════════════════════════
    const ingresosMesData = await get('visitantes_por_mes');
    if (ingresosMesData && ingresosMesData.length > 0) {
        linea('graficoIngresosMes',
            ingresosMesData.map(r => r.mes || r.Mes || 'Sin mes'),
            ingresosMesData.map(r => parseInt(r.total) || 0),
            'rgba(246,194,62,1)'
        );
    } else {
        console.warn('⚠️ No hay datos de ingresos por mes');
    }

    // ════════════════════════════════════════════════════════════
    //  GRÁFICA 5 — Funcionarios por sede (barras)
    // ════════════════════════════════════════════════════════════
    const funcionariosSedeData = await get('funcionarios_por_sede');
    if (funcionariosSedeData && funcionariosSedeData.length > 0) {
        barras('graficoFuncionariosSede',
            funcionariosSedeData.map(r => r.NombreSede || r.sede || 'Sin sede'),
            funcionariosSedeData.map(r => parseInt(r.total) || 0),
            'Funcionarios'
        );
    } else {
        console.warn('⚠️ No hay datos de funcionarios por sede');
    }

    // ════════════════════════════════════════════════════════════
    //  GRÁFICA 6 — Vehículos por sede (barras)
    // ════════════════════════════════════════════════════════════
    const vehiculosSedeData = await get('vehiculos_por_sede');
    if (vehiculosSedeData && vehiculosSedeData.length > 0) {
        barras('graficoVehiculosSede',
            vehiculosSedeData.map(r => r.NombreSede || r.sede || 'Sin sede'),
            vehiculosSedeData.map(r => parseInt(r.total) || 0),
            'Vehículos'
        );
    } else {
        console.warn('⚠️ No hay datos de vehículos por sede');
    }

    // ════════════════════════════════════════════════════════════
    //  GRÁFICA 7 — Entrada vs Salida (dona)
    // ════════════════════════════════════════════════════════════
    const ingresosTipoData = await get('ingresos_por_tipo');
    if (ingresosTipoData && ingresosTipoData.length > 0) {
        dona('graficoIngresosTipo',
            ingresosTipoData.map(r => r.tipo || r.Tipo || 'Sin tipo'),
            ingresosTipoData.map(r => parseInt(r.total) || 0)
        );
    } else {
        console.warn('⚠️ No hay datos de ingresos por tipo');
    }

    // ════════════════════════════════════════════════════════════
    //  GRÁFICA 8 — Bitácora por turno (torta)
    // ════════════════════════════════════════════════════════════
    const bitacoraTurnoData = await get('bitacora_por_turno');
    if (bitacoraTurnoData && bitacoraTurnoData.length > 0) {
        dona('graficoBitacoraTurno',
            bitacoraTurnoData.map(r => r.TurnoBitacora || r.turno || 'Sin turno'),
            bitacoraTurnoData.map(r => parseInt(r.total) || 0),
            'pie'
        );
    } else {
        console.warn('⚠️ No hay datos de bitácora por turno');
    }

    // ════════════════════════════════════════════════════════════
    //  GRÁFICA 9 — Dotación por tipo (barras)
    // ════════════════════════════════════════════════════════════
    const dotacionTipoData = await get('dotacion_por_tipo');
    console.log('📊 Dotación por tipo - Datos recibidos:', dotacionTipoData);
    
    if (dotacionTipoData && dotacionTipoData.length > 0) {
        barras('graficoDotacionTipo',
            dotacionTipoData.map(r => r.TipoDotacion || r.tipo || r.Tipo || 'Sin tipo'),
            dotacionTipoData.map(r => parseInt(r.total) || parseInt(r.cantidad) || 0),
            'Dotaciones'
        );
    } else {
        console.error('❌ No hay datos de dotación por tipo - Verificar consulta SQL');
        // Mostrar mensaje de error en el canvas
        const canvas = document.getElementById('graficoDotacionTipo');
        if (canvas && canvas.parentElement) {
            const msg = document.createElement('div');
            msg.className = 'alert alert-warning text-center';
            msg.style.margin = '20px';
            msg.innerHTML = '<i class="fas fa-exclamation-triangle"></i> No se pudieron cargar los datos de dotación por tipo';
            canvas.parentElement.appendChild(msg);
        }
    }

    // ════════════════════════════════════════════════════════════
    //  GRÁFICA 10 — Dotación por estado (dona)
    // ════════════════════════════════════════════════════════════
    const dotacionEstadoData = await get('dotacion_por_estado');
    console.log('📊 Dotación por estado - Datos recibidos:', dotacionEstadoData);
    
    if (dotacionEstadoData && dotacionEstadoData.length > 0) {
        dona('graficoDotacionEstado',
            dotacionEstadoData.map(r => r.EstadoDotacion || r.estado || r.Estado || 'Sin estado'),
            dotacionEstadoData.map(r => parseInt(r.total) || parseInt(r.cantidad) || 0)
        );
    } else {
        console.error('❌ No hay datos de dotación por estado - Verificar consulta SQL');
        const canvas = document.getElementById('graficoDotacionEstado');
        if (canvas && canvas.parentElement) {
            const msg = document.createElement('div');
            msg.className = 'alert alert-warning text-center';
            msg.style.margin = '20px';
            msg.innerHTML = '<i class="fas fa-exclamation-triangle"></i> No se pudieron cargar los datos de dotación por estado';
            canvas.parentElement.appendChild(msg);
        }
    }

    // ════════════════════════════════════════════════════════════
    //  EXPORTAR PDF
    // ════════════════════════════════════════════════════════════
    const btnExportar = document.getElementById('btnExportarPDF');
    if (btnExportar) {
        btnExportar.addEventListener('click', async () => {
            const { jsPDF } = window.jspdf;
            const panel = document.getElementById('panelDashboard');
            
            if (!panel) {
                console.error('Panel no encontrado');
                return;
            }
            
            btnExportar.disabled  = true;
            btnExportar.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Generando...';
            
            try {
                const canvas = await html2canvas(panel, { 
                    scale: 1.5, 
                    useCORS: true,
                    logging: false
                });
                const pdf    = new jsPDF('p', 'mm', 'a4');
                const pageW  = pdf.internal.pageSize.getWidth();
                const pageH  = pdf.internal.pageSize.getHeight();
                const imgW   = pageW - 20;
                const imgH   = imgW / (canvas.width / canvas.height);
                
                let posY = 10;
                pdf.setFontSize(14); 
                pdf.setTextColor(40);
                pdf.text('Dashboard de Seguridad — SEGTRACK', 10, posY);
                posY += 6;
                pdf.setFontSize(9); 
                pdf.setTextColor(120);
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
                
                pdf.save('dashboard_seguridad.pdf');
            } catch(error) {
                console.error('Error generando PDF:', error);
                alert('Error al generar el PDF: ' + error.message);
            } finally {
                btnExportar.disabled  = false;
                btnExportar.innerHTML = '<i class="fas fa-file-pdf fa-sm mr-1"></i> Exportar PDF';
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>