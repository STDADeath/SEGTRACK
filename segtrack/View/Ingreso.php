<?php
require_once __DIR__ . '/../Plantilla/parte_superior.php';
?>


<div class="container mt-5">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
            <h4 class="text-center fw-bold text-primary mb-4">Control de Ingreso de Funcionarios</h4>

            <h5 class="mb-3 fw-semibold">Lista de Ingresos Recientes</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>Funcionario</th>
                            <th>Cargo</th>
                            <th>Tipo Movimiento</th>
                            <th>Fecha Ingreso</th>
                        </tr>
                    </thead>
                    <tbody id="tablaIngresos">
                        <tr>
                            <td colspan="4">Cargando...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="mensajeError" class="alert alert-danger text-center mt-3 d-none"></div>
            <div id="mensajeVacio" class="alert alert-warning text-center mt-3 d-none">No hay ingresos registrados todavía.</div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const tablaIngresos = document.getElementById("tablaIngresos");
    const mensajeError = document.getElementById("mensajeError");
    const mensajeVacio = document.getElementById("mensajeVacio");

    fetch("../controller/Ingreso_Visitante/ControladorIngreso.php")
        .then(res => res.json())
        .then(data => {
            tablaIngresos.innerHTML = "";
            mensajeError.classList.add("d-none");
            mensajeVacio.classList.add("d-none");

            if (!data.success) {
                mensajeError.textContent = "Error al cargar los datos.";
                mensajeError.classList.remove("d-none");
                return;
            }

            if (!data.data || data.data.length === 0) {
                mensajeVacio.classList.remove("d-none");
                return;
            }

            data.data.forEach(ingreso => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${ingreso.NombreFuncionario}</td>
                    <td>${ingreso.CargoFuncionario}</td>
                    <td>${ingreso.TipoMovimiento}</td>
                    <td>${new Date(ingreso.FechaIngreso).toLocaleString()}</td>
                `;
                tablaIngresos.appendChild(row);
            });
        })
        .catch(error => {
            console.error("Error:", error);
            tablaIngresos.innerHTML = "";
            mensajeError.textContent = "No se pudo conectar con el servidor.";
            mensajeError.classList.remove("d-none");
        });
});
</script>

<?php require_once __DIR__ . '/../Plantilla/parte_inferior.php'; ?>
<?php require_once __DIR__ . '/../Plantilla/parte_inferior.php'; ?>
