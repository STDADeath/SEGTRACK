<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard de Seguridad</h1>
        <button id="btnExportarPDF" class="btn btn-danger btn-sm shadow-sm">
            <i class="fas fa-file-pdf fa-sm mr-1"></i> Exportar PDF
        </button>
    </div>

    <div id="panelDashboard">

        <!-- TARJETAS -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow-sm h-100 py-2">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Dispositivos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalDispositivos">—</div>
                        </div>
                        <i class="fas fa-tablet-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow-sm h-100 py-2">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Funcionarios</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalFuncionarios">—</div>
                        </div>
                        <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow-sm h-100 py-2">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Visitantes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalVisitantes">—</div>
                        </div>
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow-sm h-100 py-2">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Vehículos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalVehiculos">—</div>
                        </div>
                        <i class="fas fa-car fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRÁFICAS FILA 1 -->
        <div class="row mb-4">
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Dispositivos por Tipo</h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative;height:280px;">
                            <canvas id="graficoDispositivos"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">Vehículos por Tipo</h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative;height:280px;
                                    display:flex;align-items:center;justify-content:center;">
                            <canvas id="graficoVehiculos"
                                style="max-height:260px;max-width:260px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRÁFICAS FILA 2 -->
        <div class="row mb-4">
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">Funcionarios por Cargo</h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative;height:280px;">
                            <canvas id="graficoFuncionarios"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">Ingresos por Mes</h6>
                    </div>
                    <div class="card-body">
                        <div style="position:relative;height:280px;">
                            <canvas id="graficoIngresos"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", async () => {

    const BASE_URL = "../../Controller/ControladorDashboard.php";

    const colores = [
        'rgba(78,115,223,0.85)',
        'rgba(28,200,138,0.85)',
        'rgba(246,194,62,0.85)',
        'rgba(231,74,59,0.85)',
        'rgba(54,162,235,0.85)',
        'rgba(153,102,255,0.85)'
    ];

    // ── TOTALES ──────────────────────────────────────
    try {
        const [dis, func, vis, veh] = await Promise.all([
            fetch(`${BASE_URL}?accion=total_dispositivos`).then(r => r.json()),
            fetch(`${BASE_URL}?accion=total_funcionarios`).then(r => r.json()),
            fetch(`${BASE_URL}?accion=total_visitantes`).then(r => r.json()),
            fetch(`${BASE_URL}?accion=total_vehiculos`).then(r => r.json())
        ]);
        document.getElementById('totalDispositivos').textContent = dis.total_dispositivos  ?? 0;
        document.getElementById('totalFuncionarios').textContent = func.total_funcionarios ?? 0;
        document.getElementById('totalVisitantes').textContent   = vis.total_visitantes    ?? 0;
        document.getElementById('totalVehiculos').textContent    = veh.total_vehiculos     ?? 0;
    } catch(e) { console.error('Totales:', e); }

    // ── DISPOSITIVOS POR TIPO — barras ───────────────
    try {
        const data = await fetch(`${BASE_URL}?accion=tipos_dispositivos`).then(r => r.json());
        if (!Array.isArray(data)) throw new Error(JSON.stringify(data));
        new Chart(document.getElementById('graficoDispositivos'), {
            type: 'bar',
            data: {
                labels: data.map(d => d.tipo_dispositivos),
                datasets: [{
                    label: 'Cantidad',
                    data: data.map(d => d.cantidad_Dispositivos),
                    backgroundColor: colores,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    } catch(e) { console.error('Dispositivos:', e); }

    // ── VEHÍCULOS POR TIPO — torta ───────────────────
    try {
        const data = await fetch(`${BASE_URL}?accion=vehiculos_por_tipo`).then(r => r.json());
        if (!Array.isArray(data)) throw new Error(JSON.stringify(data));
        new Chart(document.getElementById('graficoVehiculos'), {
            type: 'doughnut',
            data: {
                labels: data.map(v => v.tipo_vehiculos),
                datasets: [{
                    data: data.map(v => v.cantidad_Vehiculos),
                    backgroundColor: colores,
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                cutout: '65%'
            }
        });
    } catch(e) { console.error('Vehículos:', e); }

    // ── FUNCIONARIOS POR CARGO — barras horizontales ─
    try {
        const data = await fetch(`${BASE_URL}?accion=funcionarios_por_cargo`).then(r => r.json());
        if (!Array.isArray(data)) throw new Error(JSON.stringify(data));
        new Chart(document.getElementById('graficoFuncionarios'), {
            type: 'bar',
            data: {
                labels: data.map(f => f.CargoFuncionario),
                datasets: [{
                    label: 'Funcionarios',
                    data: data.map(f => f.total),
                    backgroundColor: colores,
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    } catch(e) { console.error('Funcionarios:', e); }

    // ── INGRESOS POR MES — línea ─────────────────────
    // (visitante no tiene fecha, usamos tabla ingreso)
    try {
        const data = await fetch(`${BASE_URL}?accion=visitantes_por_mes`).then(r => r.json());
        if (!Array.isArray(data)) throw new Error(JSON.stringify(data));
        new Chart(document.getElementById('graficoIngresos'), {
            type: 'line',
            data: {
                labels: data.map(v => v.mes),
                datasets: [{
                    label: 'Ingresos',
                    data: data.map(v => v.total),
                    borderColor: 'rgba(246,194,62,1)',
                    backgroundColor: 'rgba(246,194,62,0.15)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'rgba(246,194,62,1)'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    } catch(e) { console.error('Ingresos:', e); }

    // ── EXPORTAR PDF ─────────────────────────────────
    document.getElementById('btnExportarPDF').addEventListener('click', async () => {
        const { jsPDF } = window.jspdf;
        const panel   = document.getElementById('panelDashboard');
        const btn     = document.getElementById('btnExportarPDF');

        btn.disabled  = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Generando...';

        const canvas = await html2canvas(panel, { scale: 1.5, useCORS: true });
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

        let alturaRestante = imgH;
        let srcY = 0;
        while (alturaRestante > 0) {
            const alturaBloque = Math.min(pageH - posY - 10, alturaRestante);
            const srcH         = (alturaBloque / imgH) * canvas.height;
            const tmp          = document.createElement('canvas');
            tmp.width          = canvas.width;
            tmp.height         = srcH;
            tmp.getContext('2d').drawImage(
                canvas, 0, srcY, canvas.width, srcH,
                0, 0, canvas.width, srcH
            );
            pdf.addImage(tmp.toDataURL('image/png'), 'PNG', 10, posY, imgW, alturaBloque);
            alturaRestante -= alturaBloque;
            srcY           += srcH;
            posY            = 10;
            if (alturaRestante > 0) pdf.addPage();
        }

        pdf.save('dashboard_seguridad.pdf');
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-file-pdf fa-sm mr-1"></i> Exportar PDF';
    });

});
</script>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>