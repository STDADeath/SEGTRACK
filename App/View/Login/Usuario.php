<?php
// Incluye la parte superior de la plantilla y la conexión a la base de datos
require_once __DIR__ . '/../Plantilla/parte_superior_supervisor.php'; 
require_once __DIR__ . '/../Core/conexion.php'; 

$db = null;
$pdo = null;

// Inicialización de la conexión PDO
try {
    $db = new Conexion();
    $pdo = $db->getConexion(); 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("<div style='color: red; text-align: center; padding: 20px; border: 1px solid red;'>Error de Conexión a la Base de Datos: " . $e->getMessage() . "</div>");
}

// --- Lógica para obtener Funcionarios ---
$funcionarios = [];
$sql_funcionarios = "SELECT IdFuncionario, NombreFuncionario FROM Funcionario ORDER BY NombreFuncionario"; 
try {
    $stmt_func = $pdo->prepare($sql_funcionarios);
    $stmt_func->execute(); 
    $funcionarios = $stmt_func->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div style='color: red; text-align: center;'>Error al cargar los funcionarios: " . $e->getMessage() . "</div>";
}

// Roles permitidos (deben coincidir con el ENUM de la base de datos)
$roles_permitidos = ['Supervisor', 'Personal Seguridad', 'Administrador']; 
?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-circle me-2"></i>Registrar Usuario</h1>
        <a href="UsuarioLista.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-list me-1"></i> Ver Usuarios
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Formulario de Registro</h6>
        </div>
        <div class="card-body">
            <form id="formUsuario" action="../Controller/sede_institucion_funcionario_usuario/ControladorusuarioADM.php" method="POST">
                <div class="row">
                    <!-- Tipo de Rol -->
                    <div class="col-md-6 mb-3">
                        <label for="tipo_rol" class="form-label">Tipo de Rol</label>
                        <input 
                            type="text" 
                            id="tipo_rol" 
                            name="tipo_rol" 
                            class="form-control border-primary shadow-sm"
                            placeholder="Ej: Administrador, Supervisor" 
                            list="roles_list" 
                            required
                        >
                        <datalist id="roles_list">
                            <?php foreach ($roles_permitidos as $rol): ?>
                                <option value="<?php echo htmlspecialchars($rol); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <!-- Contraseña -->
                    <div class="col-md-6 mb-3">
                        <label for="contrasena" class="form-label">Contraseña</label>
                        <div style="position: relative;">
                            <input 
                                type="password" 
                                id="contrasena" 
                                name="contrasena" 
                                class="form-control border-primary shadow-sm"
                                placeholder="Ingresa una contraseña segura"
                                minlength="6"
                                required
                            >
                            <button type="button" onclick="togglePassword()" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #6c757d;">
                                <i class="fas fa-eye" id="eye-icon"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Funcionario Asignado -->
                    <div class="col-md-12 mb-3">
                        <label for="id_funcionario" class="form-label">Funcionario Asignado</label>
                        <select id="id_funcionario" name="id_funcionario" class="form-control border-primary shadow-sm" required>
                            <option value="">Selecciona un Funcionario</option>
                            <?php foreach ($funcionarios as $func): ?>
                                <option value="<?php echo $func['IdFuncionario']; ?>">
                                    <?php echo htmlspecialchars($func['NombreFuncionario']) . " (ID: " . $func['IdFuncionario'] . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Registrar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Dependencias JS -->
<script src="../vendor/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Toggle para mostrar/ocultar contraseña
function togglePassword() {
    const passwordInput = document.getElementById('contrasena');
    const eyeIcon = document.getElementById('eye-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}

// Manejo del formulario
document.getElementById("formUsuario").addEventListener("submit", function(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const datos = new FormData(form);

    // Deshabilitar botón mientras se procesa
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Procesando...';

    fetch(form.action, { 
        method: "POST", 
        body: datos 
    })
    .then(res => res.text())
    .then(data => {
        // Habilitar botón nuevamente
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save me-1"></i> Registrar Usuario';

        if (data.includes("✅") || data.includes("exitoso") || data.includes("correctamente")) {
            Swal.fire({
                icon: "success",
                title: "¡Registro exitoso!",
                text: "El usuario ha sido registrado correctamente",
                confirmButtonColor: "#10b981",
                confirmButtonText: "Aceptar"
            }).then(() => {
                form.reset();
            });
        } else {
            Swal.fire({
                icon: "error",
                title: "Error al registrar",
                html: data,
                confirmButtonColor: "#ef4444"
            });
        }
    })
    .catch(err => {
        // Habilitar botón en caso de error
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save me-1"></i> Registrar Usuario';
        
        Swal.fire({
            icon: "error",
            title: "Error de conexión",
            text: "No se pudo conectar con el servidor: " + err.message,
            confirmButtonColor: "#ef4444"
        });
    });
});

// Validación en tiempo real del rol
document.getElementById('tipo_rol').addEventListener('blur', function() {
    const roles = <?php echo json_encode($roles_permitidos); ?>;
    const valorIngresado = this.value.trim();
    
    if (valorIngresado && !roles.includes(valorIngresado)) {
        Swal.fire({
            icon: 'warning',
            title: 'Rol no reconocido',
            text: 'El rol ingresado no está en la lista de roles permitidos. ¿Deseas continuar de todas formas?',
            showCancelButton: true,
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'No, cambiar',
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6b7280'
        }).then((result) => {
            if (!result.isConfirmed) {
                this.value = '';
                this.focus();
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../Plantilla/parte_inferior_supervisor.php'; ?>