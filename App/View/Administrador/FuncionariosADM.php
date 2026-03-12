<?php

// Bloquear cache para que no puedan volver con flecha
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . '/../layouts/parte_superior_administrador.php';

require_once __DIR__ . "/../../Controller/ControladorSede.php";
$controladorSede = new ControladorSede();
$sedes = $controladorSede->obtenerSedes();
?>

<!-- ================================================================
     CONTENEDOR PRINCIPAL
================================================================= -->
<div class="container-fluid px-4 py-4">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-tie me-2"></i>Registrar Funcionario
        </h1>
        <a href="./FuncionarioListaADM.php" class="btn btn-primary btn-sm">
            <i class="fas fa-list me-1"></i> Ver Funcionarios
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Formulario de registro</h6>
        </div>
        <div class="card-body">

            <form id="formRegistrarFuncionario" enctype="multipart/form-data">

                <!-- ============================================================
                     SECCIÓN FOTO — cámara o subir archivo
                ============================================================ -->
                <div class="row mb-4">
                    <div class="col-12">
                        <label class="form-label fw-bold">
                            <i class="fas fa-camera me-1 text-primary"></i>Foto del Funcionario
                        </label>

                        <div class="d-flex flex-wrap gap-3 align-items-start">

                            <!-- Preview circular -->
                            <div style="flex-shrink:0;">
                                <div id="previewFotoContainer"
                                     style="width:130px; height:130px; border-radius:50%;
                                            border:3px dashed #4e73df; overflow:hidden;
                                            background:#f0f4ff; display:flex;
                                            align-items:center; justify-content:center;
                                            cursor:pointer;" title="Clic para subir foto"
                                     onclick="document.getElementById('FotoFuncionario').click()">
                                    <img id="previewFoto" src="" alt=""
                                         style="width:100%; height:100%; object-fit:cover; display:none;">
                                    <div id="previewPlaceholder" class="text-center text-muted px-2">
                                        <i class="fas fa-user-circle fa-3x text-secondary mb-1"></i>
                                        <div style="font-size:11px;">Clic para foto</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="d-flex flex-column gap-2 justify-content-center" style="padding-top:10px;">

                                <!-- Subir archivo -->
                                <div>
                                    <input type="file" id="FotoFuncionario" name="FotoFuncionario"
                                           accept="image/*" class="d-none">
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                            onclick="document.getElementById('FotoFuncionario').click()">
                                        <i class="fas fa-upload me-1"></i> Subir foto
                                    </button>
                                </div>

                                <!-- Tomar foto con cámara -->
                                <button type="button" class="btn btn-outline-success btn-sm"
                                        id="btnAbrirCamara">
                                    <i class="fas fa-camera me-1"></i> Usar cámara
                                </button>

                                <!-- Quitar foto -->
                                <button type="button" class="btn btn-outline-danger btn-sm d-none"
                                        id="btnQuitarFoto" onclick="quitarFoto()">
                                    <i class="fas fa-times me-1"></i> Quitar foto
                                </button>

                            </div>
                        </div>

                        <!-- ---- ÁREA DE CÁMARA (oculta por defecto) ---- -->
                        <div id="areaCamera" class="mt-3 d-none">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white py-2">
                                    <small><i class="fas fa-video me-1"></i>Vista previa de la cámara</small>
                                </div>
                                <div class="card-body text-center p-2">
                                    <video id="videoCamera" autoplay playsinline muted
                                           style="width:100%; max-width:350px; border-radius:8px;
                                                  border:2px solid #28a745;"></video>
                                    <canvas id="canvasCaptura" style="display:none;"></canvas>
                                    <div class="mt-2 d-flex justify-content-center gap-2">
                                        <button type="button" class="btn btn-success btn-sm"
                                                id="btnCapturar">
                                            <i class="fas fa-camera me-1"></i> Capturar
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm"
                                                id="btnCerrarCamara">
                                            <i class="fas fa-times me-1"></i> Cancelar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Campo oculto para foto capturada con cámara (base64) -->
                        <input type="hidden" id="FotoCapturaBase64" name="FotoCapturaBase64">

                    </div>
                </div>

                <!-- FILA 1 (Cargo - Nombre) -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="CargoFuncionario" class="form-label">Cargo *</label>
                        <select id="CargoFuncionario" name="CargoFuncionario"
                            class="form-control border-primary shadow-sm">
                            <option value="">Seleccione...</option>
                            <option value="Personal Seguridad">Personal Seguridad</option>
                            <option value="Funcionario">Funcionario</option>
                            <option value="Visitante">Visitante</option>
                            <option value="RR.HH">RR.HH</option>
                            <option value="Contador">Contador</option>
                            <option value="Financiero">Financiero</option>
                        </select>
                        <div class="invalid-feedback">Este campo es obligatorio.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="NombreFuncionario" class="form-label">Nombre Completo *</label>
                        <input type="text" id="NombreFuncionario" name="NombreFuncionario"
                            class="form-control border-primary shadow-sm"
                            placeholder="Ej: Juan Pérez">
                        <div class="invalid-feedback">
                            El nombre solo debe contener letras y espacios (Mínimo 3 caracteres).
                        </div>
                    </div>
                </div>

                <!-- FILA 2 (Sede - Teléfono - Documento) -->
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
                        <input type="text" id="TelefonoFuncionario" name="TelefonoFuncionario"
                            maxlength="10"
                            class="form-control border-primary shadow-sm"
                            placeholder="Ej: 3001234567">
                        <div class="invalid-feedback">Debe contener exactamente 10 dígitos numéricos.</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="DocumentoFuncionario" class="form-label">Documento *</label>
                        <input type="text" id="DocumentoFuncionario" name="DocumentoFuncionario"
                            maxlength="11"
                            class="form-control border-primary shadow-sm"
                            placeholder="Ej: 10024567891">
                        <div class="invalid-feedback">Debe contener exactamente 11 dígitos numéricos.</div>
                    </div>
                </div>

                <!-- CORREO -->
                <div class="mb-3">
                    <label for="CorreoFuncionario" class="form-label">Correo Electrónico *</label>
                    <input type="email" id="CorreoFuncionario" name="CorreoFuncionario"
                        maxlength="100"
                        class="form-control border-primary shadow-sm"
                        placeholder="Ej: correo@dominio.com">
                    <div class="invalid-feedback">Ingrese un formato de correo válido (debe incluir @ y .).</div>
                </div>

                <!-- BOTÓN REGISTRAR -->
                <div class="text-end">
                    <button type="submit" class="btn btn-success" id="btnRegistrar">
                        <i class="fas fa-save me-1"></i> Registrar Funcionario
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- TARJETA INFO QR -->
    <div class="card shadow mb-4">
        <div class="card-header bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Información Adicional</h6>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i>
                El código QR se generará automáticamente después de guardar los datos del funcionario.
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior_administrador.php'; ?>
<link rel="stylesheet" href="../../../Public/css/Tablas.css">
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../../Public/js/javascript/js/Funcionarios.js"></script>