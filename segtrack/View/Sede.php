<?php
session_start();

// ✅ Verificar sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../View/login.html");
    exit;
}

require_once __DIR__ . '/../Plantilla/parte_superior.php';
require_once __DIR__ . '/../Model/sede_institucion_funcionario_usuario/modelosede.php';
require_once __DIR__ . '/../Model/sede_institucion_funcionario_usuario/modelofuncionario.php';
require_once __DIR__ . '/../Model/sede_institucion_funcionario_usuario/modeloinstituto.php'; // 👈 Nuevo modelo para traer instituciones
?>

<div class="container-fluid px-4 py-4">
    <h2>Registrar Sede y Usuario</h2>

    <!-- ================================
         FORMULARIO DE SEDE
    ================================== -->
    <div class="card mb-4 shadow"> 
        <div class="card-header bg-primary text-white">Agregar Sede</div>
        <div class="card-body">
            <form method="POST" action="../Controller/sede_institucion_funcionario_usuario/Controladorsede.php" class="needs-validation" novalidate>
                <!-- Tipo de sede -->
                <div class="mb-3">
                    <label class="form-label">Tipo de Sede *</label>
                    <input type="text" class="form-control" name="TipoSede" placeholder="Escriba o seleccione Sede" list="sedeList" required>
                    <datalist id="sedeList">
                        <option value="Sede Chapinero Segtrack">
                        <option value="Sede Bosa Segtrack">
                        <option value="Sede Soacha Segtrack">
                        <option value="Sede Toberín Segtrack">
                    </datalist>
                </div>

                <!-- Ciudad -->
                <div class="mb-3">
                    <label class="form-label">Ciudad *</label>
                    <select class="form-select" name="Ciudad" required>
                        <option value="">Seleccione ciudad...</option>
                        <option value="Bogotá">Bogotá</option>
                        <option value="Medellín">Medellín</option>
                        <option value="Cali">Cali</option>
                        <option value="Barranquilla">Barranquilla</option>
                        <option value="Otra">Otra</option>
                    </select>
                </div>

                <!-- Institución -->
                <div class="mb-3">
                    <label class="form-label">Institución *</label>
                    <select class="form-select" name="IdInstitucion" required>
                        <option value="">Seleccione institución...</option>
                        <?php
                        $institucionModel = new ModeloInstitucion();
                        $instituciones = $institucionModel->listar();

                        foreach ($instituciones as $i) {
                            echo "<option value='{$i['IdInstitucion']}'>{$i['NombreInstitucion']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Guardar Sede</button>
            </form>
        </div>
    </div>

    <!-- ================================
         FORMULARIO DE USUARIO
    ================================== -->
    <div class="card mb-4 shadow">
        <div class="card-header bg-success text-white">Agregar Usuario</div>
        <div class="card-body">
            <form method="POST" action="../Controller/usuario/ControladorUsuario.php" class="needs-validation" novalidate>
                <!-- Funcionario -->
                <div class="mb-3">
                    <label class="form-label">Funcionario *</label>
                    <select class="form-select" name="IdFuncionario" required>
                        <option value="">Seleccione Funcionario...</option>
                        <?php
                        $funcionarioModel = new ModeloFuncionario();
                        $funcionarios = $funcionarioModel->listarTodos();
                        foreach ($funcionarios as $f) {
                            echo "<option value='{$f['IdFuncionario']}'>{$f['NombreFuncionario']} ({$f['DocumentoFuncionario']})</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Rol -->
                <div class="mb-3">
                    <label class="form-label">Tipo de Rol *</label>
                    <select class="form-select" name="TipoRol" required>
                        <option value="">Seleccione rol...</option>
                        <option value="Supervisor">Supervisor</option>
                        <option value="Personal Seguridad">Personal Seguridad</option>
                        <option value="Administrador">Administrador</option>
                    </select>
                </div>

                <!-- Contraseña -->
                <div class="mb-3">
                    <label class="form-label">Contraseña *</label>
                    <input type="password" class="form-control" name="Contrasena" placeholder="Ingrese contraseña" required>
                </div>

                <button type="submit" class="btn btn-success">Guardar Usuario</button>
            </form>
        </div>
    </div>
</div>


