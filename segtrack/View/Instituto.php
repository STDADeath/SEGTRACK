<?php
session_start();

require_once __DIR__ . '/../Plantilla/parte_superior_supervisor.php';
?>

<style>
    /* Estilo personalizado para el formulario de Institución */
    .page-header {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        padding: 2rem 0;
        margin-bottom: 2rem;
    }
    
    .form-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 2rem;
        margin: 0 1rem;
    }
    
    .form-card-header {
        color: #4e73df;
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e3e6f0;
    }
    
    .form-label {
        color: #858796;
        font-size: 0.95rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .form-control-custom {
        border: 2px solid #d1d3e2;
        border-radius: 6px;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        width: 100%;
    }
    
    .form-control-custom:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
        outline: none;
    }
    
    .form-control-custom::placeholder {
        color: #a0a0a0;
    }
    
    .btn-register {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        border-radius: 6px;
        padding: 0.75rem 2rem;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-register:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
    }
    
    .btn-register:active {
        transform: translateY(0);
    }
    
    .btn-view {
        background: #4e73df;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 0.6rem 1.5rem;
        font-size: 0.9rem;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }
    
    .btn-view:hover {
        background: #2e59d9;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(78, 115, 223, 0.3);
    }
    
    .page-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.75rem;
        color: #5a5c69;
        font-weight: 600;
        margin: 0;
    }
    
    .form-row-custom {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .form-group-custom {
        display: flex;
        flex-direction: column;
    }
</style>

<div class="page-header">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between">
            <h1 class="page-title">
                <i class="fas fa-university"></i>
                Registrar Institución
            </h1>
            <a href="InstitucionLista.php" class="btn-view">
                <i class="fas fa-list"></i> Ver Instituciones
            </a>
        </div>
    </div>
</div>

<div class="container-fluid px-4 pb-4">
    <div class="form-card">
        <div class="form-card-header">
            Recuerda estos campos son obligatorios con la regla de que verde es aprobado y rojo reprobado.
        </div>
        
        <form id="formInstituto" method="POST" action="../Controller/sede_institucion_funcionario_usuario/ControladorInstituto.php">
            <div class="form-row-custom">
                <div class="form-group-custom">
                    <label for="NombreInstitucion" class="form-label">Nombre de la Institución</label>
                    <input type="text" id="NombreInstitucion" name="NombreInstitucion" class="form-control-custom" placeholder="Ej: Universidad Nacional" required>
                </div>

                <div class="form-group-custom">
                    <label for="Nit_Codigo" class="form-label">NIT / Código</label>
                    <input type="text" id="Nit_Codigo" name="Nit_Codigo" class="form-control-custom" placeholder="Ej: 900123456-7" maxlength="20" required>
                </div>
            </div>

            <div class="form-row-custom">
                <div class="form-group-custom">
                    <label for="TipoInstitucion" class="form-label">Tipo de Institución</label>
                    <select id="TipoInstitucion" name="TipoInstitucion" class="form-control-custom" required>
                        <option value="">Seleccione tipo...</option>
                        <option value="Universidad">Universidad</option>
                        <option value="Colegio">Colegio</option>
                        <option value="Empresa">Empresa</option>
                        <option value="ONG">ONG</option>
                        <option value="Hospital">Hospital</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <div class="form-group-custom">
                    <label for="EstadoInstitucion" class="form-label">Estado</label>
                    <select id="EstadoInstitucion" name="EstadoInstitucion" class="form-control-custom" required>
                        <option value="Activo" selected>Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </div>
            </div>

            <div style="text-align: left; margin-top: 2rem;">
                <button type="submit" class="btn-register">
                    <i class="fas fa-save"></i> Registrar Institución
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Dependencias JS -->
<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../js/javascript/js/Instituto.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php require_once __DIR__ . '/../Plantilla/parte_inferior_supervisor.php'; ?>