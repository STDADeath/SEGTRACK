<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-book me-2"></i> Lista de Bitácoras
        </h1>
        <a href="./Bitacora.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nueva Bitácora
        </a>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-1"></i> Filtrar Bitácoras
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-3">
                    <label for="filtroTurno" class="form-label">Turno</label>
                    <select id="filtroTurno" class="form-select">
                        <option value="">Todos</option>
                        <option value="Jornada mañana">Jornada mañana</option>
                        <option value="Jornada tarde">Jornada tarde</option>
                        <option value="Jornada noche">Jornada noche</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="filtroFecha" class="form-label">Fecha</label>
                    <input type="date" id="filtroFecha" class="form-control">
                </div>

                <div class="col-md-3">
                    <label for="filtroFuncionario" class="form-label">Personal de Seguridad</label>
                    <input type="text" id="filtroFuncionario" class="form-control" placeholder="Buscar por nombre...">
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="button" id="btnFiltrar" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                    <button type="button" id="btnLimpiar" class="btn btn-secondary">
                        <i class="fas fa-broom me-1"></i> Limpiar
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Bitácoras Registradas</h6>
            <span class="badge bg-primary" id="contadorRegistros">0 registros</span>
        </div>
        <div class="card-body table-responsive">
            <table id="tablaBitacorasDT"
                   class="table table-bordered table-hover table-striped align-middle text-center"
                   width="100%" cellspacing="0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Turno</th>
                        <th>Novedades</th>
                        <th>Fecha y Hora</th>
                        <th>Personal Seguridad</th>
                        <th>Visitante</th>
                        <th>Dispositivo</th>
                        <th>PDF</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTablaBitacoras">
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-2x text-muted mb-2 d-block"></i>
                            <span class="text-muted">Cargando...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../../Public/js/javascript/js/ValidacionBitacora.js"></script>