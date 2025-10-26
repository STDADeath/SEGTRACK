<?php require_once __DIR__ . '/../Plantilla/parte_superior.php'; ?>


<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Dashboard Supervisor - SEGTRACK</h1>

    <!-- Tarjetas de resumen -->
    <div class="row mb-4">
        <div class="col-lg-4 mb-3">
            <div class="card shadow text-center border-primary">
                <div class="card-body">
                    <h5 class="card-title text-primary">Total Dispositivos</h5>
                    <h3 id="totalDispositivos" class="fw-bold text-primary">0</h3>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card shadow text-center border-success">
                <div class="card-body">
                    <h5 class="card-title text-success">Total Funcionarios</h5>
                    <h3 id="totalFuncionarios" class="fw-bold text-success">0</h3>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card shadow text-center border-danger">
                <div class="card-body">
                    <h5 class="card-title text-danger">Total Visitantes</h5>
                    <h3 id="totalVisitantes" class="fw-bold text-danger">0</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico principal -->
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    Dispositivos por Tipo
                </div>
                <div class="card-body">
                    <canvas id="graficoTipo"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", async () => {

    const BASE_URL = "../../Controller/Graficas/ControladorDashboard.php";


    try {
        const resTipo = await fetch(`${BASE_URL}?accion=tipos_dispositivos`);
        const datosTipo = await resTipo.json();

        const labels = datosTipo.map(d => d.tipo_dispositivos);
        const cantidades = datosTipo.map(d => d.cantidad_Dispositivos);

        new Chart(document.getElementById('graficoTipo'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Cantidad de Dispositivos',
                    data: cantidades,
                    backgroundColor: [
                        '#007bff',
                        '#28a745',
                        '#ffc107',
                        '#dc3545',
                        '#17a2b8'
                    ],
                    borderColor: '#000',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: {
                        display: true,
                        text: 'Distribución por Tipo de Dispositivo'
                    }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    } catch (error) {
        console.error("Error al cargar gráfica:", error);
    }

    // === 2️⃣ Totales ===
    try {
        const totalDispositivos = await fetch(`${BASE_URL}?accion=total_dispositivos`).then(r => r.json());
        const totalFuncionarios = await fetch(`${BASE_URL}?accion=total_funcionarios`).then(r => r.json());
        const totalVisitantes = await fetch(`${BASE_URL}?accion=total_visitantes`).then(r => r.json());

        document.getElementById("totalDispositivos").textContent = totalDispositivos.total_dispositivos ?? 0;
        document.getElementById("totalFuncionarios").textContent = totalFuncionarios.total_funcionarios ?? 0;
        document.getElementById("totalVisitantes").textContent = totalVisitantes.total_visitantes ?? 0;
    } catch (error) {
        console.error("Error al cargar totales:", error);
    }
});
</script>

<?php require_once __DIR__ . '/../Plantilla/parte_inferior_supervisor.php'; ?>


