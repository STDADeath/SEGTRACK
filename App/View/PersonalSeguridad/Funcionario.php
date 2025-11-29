<?php
session_start();
require_once __DIR__ . '/../layouts/parte_superior.php';

// Cargar sedes para el select
require_once __DIR__ . "/../../Controller/ControladorSede.php";
$controladorSede = new ControladorSede();
$sedes = $controladorSede->obtenerSedes();
?>

<style>
    /* Oculta los iconos de validación de Bootstrap (check y X) */
    .form-control.is-valid,
    .form-control.is-invalid,
    .form-select.is-valid,
    .form-select.is-invalid {
        /* El !important es necesario para sobrescribir los estilos de Bootstrap */
        background-image: none !important;
        padding-right: 0.75rem !important;
        /* Restaura el padding original */
    }

    /* Opcional: Asegura que el foco no tenga un recuadro azul que cubra el borde */
    .form-control:focus,
    .form-select:focus {
        box-shadow: none;
    }
</style>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-tie me-2"></i>Registrar Funcionario</h1>
        <a href="./FuncionarioLista.php" class="btn btn-primary btn-sm">
            <i class="fas fa-list me-1"></i> Ver Funcionarios
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Formulario de registro</h6>
        </div>
        <div class="card-body">
            <form id="formRegistrarFuncionario">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="CargoFuncionario" class="form-label">Cargo *</label>
                        <select id="CargoFuncionario" name="CargoFuncionario"
                            class="form-control border-primary shadow-sm">
                            <option value="">Seleccione...</option>
                            <option value="Personal Seguridad">Personal Seguridad</option>
                            <option value="Visitante">Visitante</option>
                            <option value="Funcionario Institucion">Funcionario Institución</option>
                        </select>
                        <div class="invalid-feedback">Este campo es obligatorio.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="NombreFuncionario" class="form-label">Nombre Completo *</label>
                        <input type="text" id="NombreFuncionario" name="NombreFuncionario"
                            class="form-control border-primary shadow-sm" placeholder="Ej: Juan Pérez">
                        <div class="invalid-feedback">El nombre solo debe contener letras y espacios (Mínimo 3
                            caracteres).</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="IdSede" class="form-label">Sede *</label>
                        <select id="IdSede" name="IdSede" class="form-control border-primary shadow-sm">
                            <option value="">Seleccione...</option>
                            <?php if (!empty($sedes)): ?>
                                <?php foreach ($sedes as $sede): ?>
                                    <option value="<?= htmlspecialchars($sede['IdSede']) ?>">
                                        <?= htmlspecialchars($sede['NombreSede'] ?? $sede['TipoSede']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No hay sedes disponibles</option>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback">Este campo es obligatorio.</div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="TelefonoFuncionario" class="form-label">Teléfono *</label>
                        <input type="text" id="TelefonoFuncionario" name="TelefonoFuncionario" maxlength="10"
                            class="form-control border-primary shadow-sm" placeholder="Ej: 3001234567">
                        <div class="invalid-feedback">Debe contener exactamente 10 dígitos numéricos.</div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="DocumentoFuncionario" class="form-label">Documento *</label>
                        <input type="text" id="DocumentoFuncionario" name="DocumentoFuncionario" maxlength="11"
                            class="form-control border-primary shadow-sm" placeholder="Ej: 10024567891">
                        <div class="invalid-feedback">Debe contener exactamente 11 dígitos numéricos.</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="CorreoFuncionario" class="form-label">Correo Electrónico *</label>
                    <input type="email" id="CorreoFuncionario" name="CorreoFuncionario" maxlength="100"
                        class="form-control border-primary shadow-sm" placeholder="Ej: correo@dominio.com">
                    <div class="invalid-feedback">Ingrese un formato de correo válido (debe incluir @ y .).</div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success" id="btnRegistrar">
                        <i class="fas fa-save me-1"></i> Registrar Funcionario
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Información Adicional</h6>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i> El código QR se generará automáticamente después de guardar los
                datos del funcionario.
            </div>
        </div>
    </div>
</div>

    <?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>
    <script src="../../../Public/vendor/jquery/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../../Public/js/javascript/js/Funcionarios.js"></script> // <-- RUTA RELATIVA DIFERENTE