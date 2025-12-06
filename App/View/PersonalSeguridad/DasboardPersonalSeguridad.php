<?php
session_start();

// ❌ Bloquear acceso si no hay sesión activa
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../View/login.html");
    exit;
}

// ❌ Evitar que el navegador almacene la página en caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>

<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard de Seguridad</h1>
    </div>

    <!-- Tarjetas de resumen -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow-sm h-100 py-2">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Dispositivos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalDispositivos">0</div>
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
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalFuncionarios">0</div>
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
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalVisitantes">0</div>
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
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalVehiculos">0</div>
                    </div>
                    <i class="fas fa-car fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <!-- Dispositivos -->
        <div class="col-xl-6 col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Distribución de Dispositivos por Tipo</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="graficoTipoDispositivos"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehículos -->
        <div class="col-xl-6 col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Distribución de Vehículos por Tipo</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container d-flex align-items-center justify-content-center" style="height: 300px;">
                        <canvas id="graficoTipoVehiculo" style="max-height: 250px; max-width: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", async () => {
    const BASE_URL = "../../../app/Controller/ControladorDashboard.php";

    // === Función para cargar gráficos y totales ===
    async function cargarDashboard() {
        try {
            const resDispositivos = await fetch(`${BASE_URL}?accion=tipos_dispositivos`);
            const datosDispositivos = await resDispositivos.json();

            const labelsD = datosDispositivos.map(d => d.tipo_dispositivos);
            const cantidadesD = datosDispositivos.map(d => d.cantidad_Dispositivos);

            new Chart(document.getElementById('graficoTipoDispositivos'), {
                type: 'bar',
                data: {
                    labels: labelsD,
                    datasets: [{
                        label: 'Cantidad de Dispositivos',
                        data: cantidadesD,
                        backgroundColor: [
                            'rgba(78, 115, 223, 0.8)',
                            'rgba(28, 200, 138, 0.8)',
                            'rgba(246, 194, 62, 0.8)',
                            'rgba(231, 74, 59, 0.8)'
                        ],
                        borderColor: 'rgba(0,0,0,0.1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } },
                    plugins: { legend: { display: false } }
                }
            });
        } catch (error) { console.error("Error gráfico dispositivos:", error); }

        try {
            const resVehiculos = await fetch(`${BASE_URL}?accion=vehiculos_por_tipo`);
            const datosVehiculos = await resVehiculos.json();

            const labelsV = datosVehiculos.map(v => v.tipo_vehiculos);
            const cantidadesV = datosVehiculos.map(v => v.cantidad_Vehiculos);

            new Chart(document.getElementById('graficoTipoVehiculo'), {
                type: 'doughnut',
                data: {
                    labels: labelsV,
                    datasets: [{
                        label: 'Vehículos',
                        data: cantidadesV,
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(255, 99, 132, 0.8)'
                        ],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    cutout: '70%'
                }
            });
        } catch (error) { console.error("Error gráfico vehículos:", error); }

        // Totales
        try {
            const [dis, func, vis, veh] = await Promise.all([
                fetch(`${BASE_URL}?accion=total_dispositivos`).then(r => r.json()),
                fetch(`${BASE_URL}?accion=total_funcionarios`).then(r => r.json()),
                fetch(`${BASE_URL}?accion=total_visitantes`).then(r => r.json()),
                fetch(`${BASE_URL}?accion=total_vehiculos`).then(r => r.json())
            ]);

            document.getElementById("totalDispositivos").textContent = dis.total_dispositivos ?? 0;
            document.getElementById("totalFuncionarios").textContent = func.total_funcionarios ?? 0;
            document.getElementById("totalVisitantes").textContent = vis.total_visitantes ?? 0;
            document.getElementById("totalVehiculos").textContent = veh.total_vehiculos ?? 0;
        } catch (error) { console.error("Error al cargar totales:", error); }
    }

    cargarDashboard();
});
</script>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>
