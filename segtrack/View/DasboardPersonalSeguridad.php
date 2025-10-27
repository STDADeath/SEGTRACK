<?php require_once __DIR__ . '/../Plantilla/parte_superior.php'; ?>

<div class="container-fluid">
    <!-- Encabezado mejorado -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard de Seguridad</h1>
        <div class="d-none d-sm-inline-block">
            <span class="text-muted">Sistema SEGTRACK</span>
        </div>
    </div>

    <!-- Tarjetas de resumen mejoradas -->
    <div class="row mb-4">
        <!-- Dispositivos -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Dispositivos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalDispositivos">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tablet-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Funcionarios -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Funcionarios</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalFuncionarios">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visitantes -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Visitantes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalVisitantes">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico y estadísticas adicionales -->
    <div class="row">
        <!-- Gráfico principal -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Distribución de Dispositivos por Tipo</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 350px;">
                        <canvas id="graficoTipo"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel de estadísticas adicionales -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Estadísticas Rápidas</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-xs font-weight-bold text-uppercase text-primary mb-1">
                            Dispositivos Activos
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="dispositivosActivos">0</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-xs font-weight-bold text-uppercase text-success mb-1">
                            Funcionarios en Sitio
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="funcionariosEnSitio">0</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-xs font-weight-bold text-uppercase text-warning mb-1">
                            Visitantes Registrados Hoy
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="visitantesHoy">0</div>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-primary"></i> Dispositivos
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> Funcionarios
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-warning"></i> Visitantes
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Font Awesome para iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<script>
document.addEventListener("DOMContentLoaded", async () => {
    const BASE_URL = "/SEGTRACK/segtrack/Controller/Graficas/ControladorDashboard.php";

    // === 1️⃣ Gráfico de dispositivos por tipo ===
    try {
        const resTipo = await fetch(`${BASE_URL}?accion=tipos_dispositivos`);
        const datosTipo = await resTipo.json();

        const labels = datosTipo.map(d => d.tipo_dispositivos);
        const cantidades = datosTipo.map(d => d.cantidad_Dispositivos);

        // Colores más profesionales para el gráfico
        const colores = [
            'rgba(78, 115, 223, 0.8)',
            'rgba(28, 200, 138, 0.8)',
            'rgba(246, 194, 62, 0.8)',
            'rgba(231, 74, 59, 0.8)',
            'rgba(133, 135, 150, 0.8)'
        ];

        const bordes = [
            'rgba(78, 115, 223, 1)',
            'rgba(28, 200, 138, 1)',
            'rgba(246, 194, 62, 1)',
            'rgba(231, 74, 59, 1)',
            'rgba(133, 135, 150, 1)'
        ];

        new Chart(document.getElementById('graficoTipo'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Cantidad de Dispositivos',
                    data: cantidades,
                    backgroundColor: colores,
                    borderColor: bordes,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        titleFont: { size: 14 },
                        bodyFont: { size: 14 },
                        padding: 10
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    }
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

        // Para este ejemplo, usaré valores simulados para las estadísticas adicionales
        // En un caso real, estos vendrían de tu API
        document.getElementById("dispositivosActivos").textContent = Math.floor(totalDispositivos.total_dispositivos * 0.85) || 0;
        document.getElementById("funcionariosEnSitio").textContent = Math.floor(totalFuncionarios.total_funcionarios * 0.65) || 0;
        document.getElementById("visitantesHoy").textContent = Math.floor(totalVisitantes.total_visitantes * 0.1) || 0;
    } catch (error) {
        console.error("Error al cargar totales:", error);
    }
});
</script>

<?php require_once __DIR__ . '/../Plantilla/parte_inferior_supervisor.php'; ?>