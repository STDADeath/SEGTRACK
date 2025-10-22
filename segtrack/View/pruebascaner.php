<?php require_once __DIR__ . '/../Plantilla/parte_superior.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-dark"><i class="fas fa-id-card me-2"></i>Lector de Documentos de Identidad</h1>
    </div>

    <div class="row g-4">
        <!-- Cámara -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="mb-3 fw-bold text-secondary">Escanear Documento</h5>
                    
                    <div id="camera-container" class="d-flex align-items-center justify-content-center mb-3" style="height: 300px; background: #e9ecef; border-radius: 12px;">
                        <p class="text-muted">[Vista de cámara]</p>
                    </div>
                    
                    <img src="https://via.placeholder.com/300x200?text=Preview" class="w-100 mb-3 rounded shadow-sm" style="height:200px; object-fit:cover;" alt="Vista previa">

                    <div class="d-flex gap-2 mb-3">
                        <select class="form-select flex-grow-1">
                            <option>Cámara 1</option>
                            <option>Cámara 2</option>
                        </select>
                        <button class="btn btn-outline-primary flex-shrink-0">Iniciar</button>
                    </div>

                    <div class="d-flex gap-2 mb-2">
                        <button class="btn btn-success flex-grow-1">Capturar</button>
                        <button class="btn btn-danger flex-grow-1">Detener</button>
                    </div>

                    <div class="text-center mt-3 text-muted small">
                        Presiona "Iniciar" para comenzar
                    </div>
                </div>
            </div>
        </div>

        <!-- Resultados -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="mb-3 fw-bold text-secondary">Datos Extraídos</h5>
                    
                    <div class="mb-2 d-flex justify-content-between">
                        <span class="fw-semibold text-dark">Documento:</span>
                        <span class="text-muted">123456789</span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span class="fw-semibold text-dark">Nombre:</span>
                        <span class="text-muted">Juan Pérez</span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span class="fw-semibold text-dark">Apellido:</span>
                        <span class="text-muted">Gómez</span>
                    </div>

                    <div class="mt-4 text-center">
                        <span class="badge bg-success fs-6 px-3 py-2 rounded-pill">✓ VERIFICADO</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #camera-container p {
        font-size: 1rem;
    }

    .card-body h5 {
        font-weight: 600;
    }

    .badge {
        font-weight: 600;
        letter-spacing: 0.5px;
    }
</style>

<?php require_once __DIR__ . '/../Plantilla/parte_inferior.php'; ?>
