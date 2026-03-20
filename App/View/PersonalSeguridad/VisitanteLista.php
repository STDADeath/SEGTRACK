<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-friends me-2"></i> Lista de Visitantes
        </h1>
        <a href="./Visitante.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Visitante
        </a>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-1"></i> Filtrar Visitantes
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Identificación</label>
                    <input type="text" id="filtroIdentificacion" class="form-control" placeholder="Número de identificación">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" id="filtroNombre" class="form-control" placeholder="Nombre del visitante">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select id="filtroEstado" class="form-select">
                        <option value="">Todos</option>
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
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
            <h6 class="m-0 font-weight-bold text-primary">Lista de Visitantes Registrados</h6>
            <span class="badge bg-primary" id="contadorRegistros">0 registros</span>
        </div>
        <div class="card-body table-responsive">
            <table id="tablaVisitantesDT"
                   class="table table-bordered table-hover table-striped align-middle text-center"
                   width="100%" cellspacing="0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Identificación</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTabla"></tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../Public/js/javascript/js/validacionVisitante.js"></script>