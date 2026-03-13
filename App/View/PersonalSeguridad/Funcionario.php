<?php
// ============================================================
// VISTA: Funcionario.php — Registrar Personal de Seguridad
//
// CAMBIOS RESPECTO A TU VERSIÓN ORIGINAL:
//   ✅ 1. Eliminado session_start() — ya lo hace parte_superior.php
//   ✅ 2. Eliminado if(!isset($_SESSION['usuario'])) — ídem
//   ✅ 3. Eliminados headers de cache — se ponen una sola vez
//   ✅ 4. Usa obtenerSedesActivas() para mostrar solo sedes Activas
//
// RESPONSABILIDAD DE ESTE ARCHIVO:
//   - Cargar sedes activas para el select
//   - Mostrar el formulario de registro
// ============================================================

require_once __DIR__ . '/../layouts/parte_superior.php';
require_once __DIR__ . "/../../Controller/ControladorSede.php";

$controladorSede = new ControladorSede();
$sedes           = $controladorSede->obtenerSedesActivas();
?>

<div class="container-fluid px-4 py-4">

    <div class="card shadow">

        <div class="card-header bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                Registrar Personal de Seguridad
            </h6>
        </div>

        <div class="card-body">

            <form id="formRegistrarFuncionario" enctype="multipart/form-data">

                <!-- ==========================================
                PANEL FOTO
                =========================================== -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-primary shadow">

                            <div class="card-header text-center">
                                <strong>Foto del Funcionario</strong>
                            </div>

                            <div class="card-body text-center">
                                <div class="row justify-content-center align-items-center">

                                    <!-- PREVIEW FOTO -->
                                    <div class="col-md-4 text-center">

                                        <div id="previewFotoContainer"
                                            style="
                                                width:200px; height:200px; margin:auto;
                                                border:3px dashed #4e73df; border-radius:10px;
                                                overflow:hidden; cursor:pointer;
                                                display:flex; align-items:center;
                                                justify-content:center; background:#f3f6ff;
                                            "
                                            onclick="document.getElementById('FotoFuncionario').click()">

                                            <img id="previewFoto"
                                                style="width:100%;height:100%;object-fit:cover;display:none;">

                                            <div id="previewPlaceholder">
                                                <i class="fas fa-user fa-3x text-secondary"></i>
                                                <div>Clic para foto</div>
                                            </div>

                                        </div>

                                        <input type="file"
                                            id="FotoFuncionario"
                                            name="FotoFuncionario"
                                            accept="image/*"
                                            class="d-none">

                                        <div class="mt-3">
                                            <button type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                onclick="document.getElementById('FotoFuncionario').click()">
                                                Subir foto
                                            </button>
                                        </div>

                                    </div>

                                    <!-- CAMARA -->
                                    <div class="col-md-4">

                                        <button type="button"
                                            class="btn btn-outline-success btn-sm mb-2"
                                            id="btnAbrirCamara">
                                            Usar cámara
                                        </button>

                                        <div id="areaCamera" class="d-none">
                                            <video id="videoCamera"
                                                autoplay
                                                playsinline
                                                style="width:100%;border-radius:10px;border:2px solid #28a745;">
                                            </video>
                                            <canvas id="canvasCaptura" style="display:none"></canvas>
                                            <div class="mt-2">
                                                <button type="button"
                                                    class="btn btn-success btn-sm"
                                                    id="btnCapturar">
                                                    Capturar
                                                </button>
                                                <button type="button"
                                                    class="btn btn-secondary btn-sm"
                                                    id="btnCerrarCamara">
                                                    Cancelar
                                                </button>
                                            </div>
                                        </div>

                                        <input type="hidden"
                                            id="FotoCapturaBase64"
                                            name="FotoCapturaBase64">

                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- ==========================================
                CAMPOS DEL FORMULARIO
                =========================================== -->
                <div class="row">
                    <div class="col-12">

                        <div class="row">

                            <!-- CARGO -->
                            <div class="col-md-4 mb-3">
                                <label for="CargoFuncionario" class="form-label">Cargo *</label>
                                <select id="CargoFuncionario" name="CargoFuncionario"
                                    class="form-control border-primary shadow-sm">
                                    <option value="">Seleccione...</option>
                                    <option value="Funcionario">Funcionario</option>
                                </select>
                                <div class="invalid-feedback">Este campo es obligatorio.</div>
                            </div>


                            <!-- NOMBRE -->
                            <div class="col-md-4 mb-3">
                                <label for="NombreFuncionario" class="form-label">Nombre Completo *</label>
                                <input type="text"
                                    id="NombreFuncionario"
                                    name="NombreFuncionario"
                                    class="form-control border-primary shadow-sm"
                                    placeholder="Ej: Juan Pérez">
                                <div class="invalid-feedback">Mínimo 3 letras, solo caracteres válidos.</div>
                            </div>

                            <option value="">Seleccione...</option>
                            <option value="Funcionario">Funcionario</option>
                          
                        </select>

                            <!-- SEDE — solo muestra sedes Activas -->
                            <div class="col-md-4 mb-3">
                                <label for="IdSede" class="form-label">Sede *</label>
                                <select id="IdSede" name="IdSede"
                                    class="form-control border-primary shadow-sm">

                                    <option value="">Seleccione...</option>

                                    <?php if (!empty($sedes)): ?>
                                        <?php foreach ($sedes as $sede): ?>
                                            <option value="<?= htmlspecialchars($sede['IdSede']) ?>">
                                                <?= htmlspecialchars($sede['NombreSede']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>No hay sedes activas disponibles</option>
                                    <?php endif; ?>

                                </select>
                                <div class="invalid-feedback">Este campo es obligatorio.</div>
                            </div>

                        </div>

                        <div class="row">

                            <!-- TELÉFONO -->
                            <div class="col-md-4 mb-3">
                                <label for="TelefonoFuncionario" class="form-label">Teléfono *</label>
                                <input type="text"
                                    id="TelefonoFuncionario"
                                    name="TelefonoFuncionario"
                                    maxlength="10"
                                    class="form-control border-primary shadow-sm"
                                    placeholder="Ej: 3001234567">
                                <div class="invalid-feedback">Debe tener exactamente 10 dígitos.</div>
                            </div>

                            <!-- DOCUMENTO -->
                            <div class="col-md-4 mb-3">
                                <label for="DocumentoFuncionario" class="form-label">Documento *</label>
                                <input type="text"
                                    id="DocumentoFuncionario"
                                    name="DocumentoFuncionario"
                                    maxlength="11"
                                    class="form-control border-primary shadow-sm"
                                    placeholder="Ej: 10024567891">
                                <div class="invalid-feedback">Debe tener entre 8 y 10 dígitos.</div>
                            </div>

                            <!-- CORREO -->
                            <div class="col-md-4 mb-3">
                                <label for="CorreoFuncionario" class="form-label">Correo *</label>
                                <input type="email"
                                    id="CorreoFuncionario"
                                    name="CorreoFuncionario"
                                    class="form-control border-primary shadow-sm"
                                    placeholder="correo@dominio.com">
                                <div class="invalid-feedback">Ingrese un correo válido.</div>
                            </div>

                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success" id="btnRegistrar">
                                <i class="fas fa-save"></i> Registrar Funcionario
                            </button>
                        </div>

                    </div>
                </div>

            </form>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>

<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../../Public/js/javascript/js/Funcionarios.js"></script>