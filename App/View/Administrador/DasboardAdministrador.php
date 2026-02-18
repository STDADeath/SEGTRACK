<?php require_once __DIR__ . '/../layouts/parte_superior_administrador.php'; ?>

<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard del Administrador</h1>
 
    </div>

    <!-- Tarjetas de resumen: Total Dotación y Total Bitácoras -->
    <div class="row mb-4">
        <!-- Total Dotación -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-dark shadow-sm h-100 py-2">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Total Dotación</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalDotacion">0</div>
                    </div>
                    <i class="fas fa-tshirt fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>

        <!-- Total Bitácoras -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow-sm h-100 py-2">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Bitácoras</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalBitacora">0</div>
                    </div>
                    <i class="fas fa-book fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos de Dotación -->
 <!-- Gráficos de Dotación -->
<div class="row">
    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header py-3 bg-dark text-white">
                <h6 class="m-0 font-weight-bold">Dotación por Tipo</h6>
            </div>
            <div class="card-body">
                <canvas id="graficoDotacionTipo" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header py-3 bg-secondary text-white">
                <h6 class="m-0 font-weight-bold">Dotación por Estado</h6>
            </div>
            <div class="card-body">
                <canvas id="graficoDotacionEstado" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>


    <div class="row">
        <div class="col-xl-6 col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Dotaciones Entregadas por Mes</h6>
                </div>
                <div class="card-body">
                    <canvas id="graficoDotacionesMes"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Dotaciones Devueltas por Mes</h6>
                </div>
                <div class="card-body">
                    <canvas id="graficoDotacionesDevolucion"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos de Bitácora -->
    <div class="row">
        <!-- Bitácora por Turno -->
        <div class="col-xl-6 col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Bitácora por Turno</h6>
                </div>
                <div class="card-body">
                    <canvas id="graficoBitacoraTurno"></canvas>
                </div>
            </div>
        </div>

        <!-- Bitácora por Mes -->
        <div class="col-xl-6 col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header py-3 bg-secondary text-white">
                    <h6 class="m-0 font-weight-bold">Bitácoras por Mes</h6>
                </div>
                <div class="card-body">
                    <canvas id="graficoBitacoraMes"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", async () => {
    const BASE_URL = "/SEGTRACK/segtrack/Controller/Graficas/ControladorDashboard.php";

    const crearGrafico = (id, tipo, labels, data, label, colores, opcionesExtra = {}) => {
        const ctx = document.getElementById(id);
        if (!ctx) return;
        new Chart(ctx, {
            type: tipo,
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: data,
                    backgroundColor: colores,
                    borderColor: 'rgba(0,0,0,0.1)',
                    borderWidth: 1
                }]
            },
            options: Object.assign({
                responsive: true,
                maintainAspectRatio: false,
                scales: tipo === 'bar' || tipo === 'line' ? { y: { beginAtZero: true } } : {},
                plugins: { legend: { display: true, position: 'bottom' } }
            }, opcionesExtra)
        });
    };

    try {
        // ================= Dotación =================
        const resTipo = await fetch(`${BASE_URL}?accion=dotacion_por_tipo`);
        const dataTipo = await resTipo.json();
        crearGrafico('graficoDotacionTipo', 'bar',
            dataTipo.map(d => d.tipo_dotaciones),
            dataTipo.map(d => d.cantidad_dotaciones),
            'Cantidad por Tipo',
            ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e']
        );

        const resEstado = await fetch(`${BASE_URL}?accion=dotacion_por_estado`);
        const dataEstado = await resEstado.json();
        crearGrafico('graficoDotacionEstado', 'doughnut',
            dataEstado.map(d => d.estado_dotaciones),
            dataEstado.map(d => d.cantidad_estado_dotaciones),
            'Dotaciones por Estado',
            ['#858796', '#36b9cc', '#f6c23e', '#e74a3b'],
            { cutout: '70%' }
        );

        const resMes = await fetch(`${BASE_URL}?accion=dotaciones_por_mes`);
        const dataMes = await resMes.json();
        crearGrafico('graficoDotacionesMes', 'line',
            dataMes.map(d => d.mes),
            dataMes.map(d => d.cantidad),
            'Entregas por Mes',
            ['rgba(78, 115, 223, 0.7)']
        );

        const resDev = await fetch(`${BASE_URL}?accion=dotaciones_por_devolucion`);
        const dataDev = await resDev.json();
        crearGrafico('graficoDotacionesDevolucion', 'line',
            dataDev.map(d => d.mes),
            dataDev.map(d => d.cantidad),
            'Devoluciones por Mes',
            ['rgba(28, 200, 138, 0.7)']
        );

        // ================= Totales Dotación =================
        const dot = await fetch(`${BASE_URL}?accion=total_dotacion`).then(r => r.json());
        document.getElementById("totalDotacion").textContent = dot.total_dotacion ?? 0;

        // ================= Bitácora =================
        const resBitTurno = await fetch(`${BASE_URL}?accion=bitacora_por_turno`);
        const dataBitTurno = await resBitTurno.json();
        crearGrafico('graficoBitacoraTurno', 'bar',
            dataBitTurno.map(d => d.turno_bitacoras),
            dataBitTurno.map(d => d.cantidad_bitacoras_turno),
            'Bitácora por Turno',
            ['#4e73df', '#1cc88a']
        );

        const resBitMes = await fetch(`${BASE_URL}?accion=bitacora_por_mes`);
        const dataBitMes = await resBitMes.json();
        crearGrafico('graficoBitacoraMes', 'line',
            dataBitMes.map(d => d.mes),
            dataBitMes.map(d => d.cantidad),
            'Bitácoras por Mes',
            ['rgba(78, 115, 223, 0.7)']
        );

        // ================= Total Bitácora =================
        const totalBit = await fetch(`${BASE_URL}?accion=total_bitacora`).then(r => r.json());
        document.getElementById("totalBitacora").textContent = totalBit.total_bitacora ?? 0;

    } catch (error) {
        console.error("❌ Error general del Dashboard:", error);
    }
});
</script>









<?php require_once __DIR__ . '/../layouts/parte_inferior_administrador.php'; ?>