<?php require_once __DIR__ . '/../Plantilla/parte_superior_supervisor.php'; ?>
<script>
document.addEventListener("DOMContentLoaded", async () => {
    try {
        const response = await fetch('../controller/graficas/');
        const data = await response.json();

        new Chart(document.getElementById("graficoBarras"), {
            type: 'bar',
            data: {
                labels: data.tipos,
                datasets: [{
                    label: 'Cantidad',
                    data: data.valores_tipos,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });

        // === Gráfico de pastel: estados de los dispositivos ===
        new Chart(document.getElementById("graficoPastel"), {
            type: 'doughnut',
            data: {
                labels: data.estados,
                datasets: [{
                    data: data.valores_estados,
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });

        // === Gráfico de línea: dispositivos por mes ===
        new Chart(document.getElementById("graficoLinea"), {
            type: 'line',
            data: {
                labels: data.meses,
                datasets: [{
                    label: 'Dispositivos registrados',
                    data: data.valores_meses,
                    borderColor: '#17a2b8',
                    tension: 0.3
                }]
            },
            options: { responsive: true }
        });
    } catch (error) {
        console.error("Error cargando datos del gráfico:", error);
    }
});
</script>




<?php require_once __DIR__ . '/../Plantilla/parte_inferior_supervisor.php'; ?>

