<?php
// Incluye la parte superior de la plantilla y la conexión a la base de datos.
require_once __DIR__ . '/../Plantilla/parte_superior.php'; 
require_once __DIR__ .'/../Core/conexion.php'; 

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

// --- Lógica para obtener Instituciones ---
$instituciones = [];
$sql_instituciones = "SELECT IdInstitucion, NombreInstitucion, EstadoInstitucion FROM Institucion"; 
try {
    $stmt_inst = $pdo->prepare($sql_instituciones);
    $stmt_inst->execute(); 
    $instituciones = $stmt_inst->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div style='color: red; text-align: center;'>Error al cargar las instituciones: " . $e->getMessage() . "</div>";
}

// --- Lógica para obtener Funcionarios ---
$funcionarios = [];
$sql_funcionarios = "SELECT IdFuncionario, NombreFuncionario FROM Funcionario"; 
try {
    $stmt_func = $pdo->prepare($sql_funcionarios);
    $stmt_func->execute(); 
    $funcionarios = $stmt_func->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div style='color: red; text-align: center;'>Error al cargar los funcionarios: " . $e->getMessage() . "</div>";
}

// Roles permitidos
$roles_permitidos = ['Administrador', 'Supervisor', 'Personal Seguridad']; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Sede y Usuario</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; }
        .container { display: flex; justify-content: center; gap: 40px; padding: 40px 20px; max-width: 1200px; margin: 0 auto; }
        .form-section { background-color: #ffffff; padding: 30px; border-radius: 12px; width: 48%; min-width: 350px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); transition: transform 0.3s ease; box-sizing: border-box; }
        .form-section:hover { transform: translateY(-5px); }
        h2 { text-align: center; color: #333; margin-bottom: 25px; border-bottom: 2px solid #007bff; padding-bottom: 10px; font-weight: 600; }
        label { display: block; margin-top: 15px; color: #555; font-weight: 500; margin-bottom: 5px; }
        input[type="text"], input[type="password"], select { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; transition: border-color 0.3s; font-size: 16px; }
        input[type="text"]:focus, input[type="password"]:focus, select:focus { border-color: #007bff; outline: none; box-shadow: 0 0 5px rgba(0, 123, 255, 0.2); }
        input[type="submit"] { width: 100%; background-color: #28a745; color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; margin-top: 30px; font-size: 18px; font-weight: 600; transition: background-color 0.3s ease, transform 0.2s ease; }
        input[type="submit"]:hover { background-color: #218838; transform: translateY(-2px); }
        @media (max-width: 800px) { .container { flex-direction: column; align-items: center; } .form-section { width: 90%; margin-bottom: 20px; } }
    </style>
</head>
<body>

<div class="container">

    <!-- 🏥 Formulario de Registro de Sede -->
    <div class="form-section">
        <h2>Registro de Nueva Sede</h2>
        <form action="../Controller/sede_institucion_funcionario_usuario/Controladorsede.php" method="POST">
            <label for="tipo_sede">Tipo de Sede:</label>
            <input type="text" id="tipo_sede" name="tipo_sede" placeholder="Ej: Sede Central, Sucursal Norte" required>

            <label for="ciudad_sede">Ciudad:</label>
            <input type="text" id="ciudad_sede" name="ciudad_sede" placeholder="Ej: Bogotá, Cali, Medellín" required>
            
            <label for="id_institucion">Institución (IdInstitucion):</label>
            <select id="id_institucion" name="id_institucion" required>
                <option value="">Selecciona una Institución</option>
                <?php foreach ($instituciones as $inst): ?>
                    <option value="<?php echo $inst['IdInstitucion']; ?>">
                        <?php echo "ID " . $inst['IdInstitucion'] . " - " . $inst['NombreInstitucion'] . " (" . $inst['EstadoInstitucion'] . ")"; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="estado_sede">Estado de la Sede:</label>
            <select id="estado_sede" name="estado_sede" required>
                <option value="Activa">Activa</option>
                <option value="Inactiva">Inactiva</option>
            </select>

            <input type="submit" value="Registrar Sede">
        </form>
    </div>

    <!-- 👤 Formulario de Registro de Usuario -->
    <div class="form-section">
        <h2>Registro de Nuevo Usuario</h2>
        <form id="formUsuario" action="../Controller/sede_institucion_funcionario_usuario/ControladorusuarioADM.php" method="POST">
            <label for="tipo_rol">Tipo de Rol:</label>
            <input type="text" id="tipo_rol" name="tipo_rol" placeholder="Ej: Administrador, Supervisor" list="roles_list" required>
            <datalist id="roles_list">
                <?php foreach ($roles_permitidos as $rol): ?>
                    <option value="<?php echo htmlspecialchars($rol); ?>">
                <?php endforeach; ?>
            </datalist>

            <label for="contrasena">Contraseña:</label>
            <input type="password" id="contrasena" name="contrasena" required>

            <label for="id_funcionario">Funcionario Asignado (IdFuncionario):</label>
            <select id="id_funcionario" name="id_funcionario" required>
                <option value="">Selecciona un Funcionario</option>
                <?php foreach ($funcionarios as $func): ?>
                    <option value="<?php echo $func['IdFuncionario']; ?>">
                        <?php echo $func['NombreFuncionario'] . " (ID: " . $func['IdFuncionario'] . ")"; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="submit" value="Registrar Usuario">
        </form>
    </div>
</div>

<script>
document.getElementById("formUsuario").addEventListener("submit", function(e){
    e.preventDefault();
    const form = e.target;
    const datos = new FormData(form);

    fetch(form.action, { method: "POST", body: datos })
    .then(res => res.text())
    .then(data => {
        if (data.includes("✅")) {
            Swal.fire({
                icon: "success",
                title: "Registro exitoso",
                text: data,
                confirmButtonColor: "#28a745"
            }).then(() => location.reload());
        } else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: data,
                confirmButtonColor: "#dc3545"
            });
        }
    })
    .catch(err => Swal.fire("Error", err.message, "error"));
});
</script>

</body>
</html>

<?php require_once __DIR__ . '/../Plantilla/parte_inferior.php'; ?>
