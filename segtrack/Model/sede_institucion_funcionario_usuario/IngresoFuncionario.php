<?php
require_once "conexion.php";

// Crear conexión
$conn = (new Conexion())->getConexion();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recibir datos del formulario (coincidiendo con los nombres reales de la tabla)
    $Nombre    = trim($_POST["NombreFuncionario"]);
    $Documento = trim($_POST["DocumentoFuncionario"]);
    $Telefono  = trim($_POST["TelefonoFuncionario"]);
    $Correo    = trim($_POST["CorreoFuncionario"]);
    $Cargo     = trim($_POST["CargoFuncionario"]);
    $IdSede    = trim($_POST["IdSede"]);

    // Generar código QR único
    $QrCodigo = "QR-FUNC-" . strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));

    // Validaciones básicas (ejemplo, puedes ampliar)
    if (empty($Nombre) || empty($Documento) || empty($Telefono) || empty($Correo) || empty($Cargo) || empty($IdSede)) {
        echo "<div style='color:red;text-align:center;'>❌ Complete todos los campos obligatorios.</div>";
        exit;
    }

    // Verificar duplicados
    $checkDocumento = $conn->prepare("SELECT COUNT(*) FROM funcionario WHERE DocumentoFuncionario = ?");
    $checkDocumento->bind_param("s", $Documento);
    $checkDocumento->execute();
    $checkDocumento->bind_result($docExiste);
    $checkDocumento->fetch();
    $checkDocumento->close();

    if ($docExiste > 0) {
        echo "<div style='color:red;text-align:center;'>❌ Ya existe un funcionario con el documento $Documento.</div>";
        exit;
    }

    $checkCorreo = $conn->prepare("SELECT COUNT(*) FROM funcionario WHERE CorreoFuncionario = ?");
    $checkCorreo->bind_param("s", $Correo);
    $checkCorreo->execute();
    $checkCorreo->bind_result($correoExiste);
    $checkCorreo->fetch();
    $checkCorreo->close();

    if ($correoExiste > 0) {
        echo "<div style='color:red;text-align:center;'>❌ Ya existe un funcionario con el correo $Correo.</div>";
        exit;
    }

    // Validar sede
    $checkSede = $conn->prepare("SELECT COUNT(*) FROM sede WHERE IdSede = ?");
    $checkSede->bind_param("i", $IdSede);
    $checkSede->execute();
    $checkSede->bind_result($sedeExiste);
    $checkSede->fetch();
    $checkSede->close();

    if ($sedeExiste == 0) {
        echo "<div style='color:red;text-align:center;'>❌ La sede seleccionada no existe.</div>";
        exit;
    }

    // Insertar funcionario
    $sql = "INSERT INTO funcionario 
            (CargoFuncionario, QrCodigoFuncionario, NombreFuncionario, IdSede, TelefonoFuncionario, DocumentoFuncionario, CorreoFuncionario) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssisis", 
        $Cargo, 
        $QrCodigo, 
        $Nombre, 
        $IdSede, 
        $Telefono, 
        $Documento, 
        $Correo
    );

    if ($stmt->execute()) {
        echo "<div style='color:green;font-weight:bold;text-align:center;margin:20px;'>✅ Funcionario registrado correctamente.</div>";

        echo "<div style='text-align:center;background:#f8f9fa;padding:20px;border-radius:10px;margin:20px;border:1px solid #dee2e6;'>";
        echo "<h3 style='color:#28a745;'>📋 Datos Registrados</h3>";
        echo "<p><strong>ID:</strong> " . $conn->insert_id . "</p>";
        echo "<p><strong>Nombre:</strong> " . htmlspecialchars($Nombre) . "</p>";
        echo "<p><strong>Documento:</strong> " . htmlspecialchars($Documento) . "</p>";
        echo "<p><strong>Teléfono:</strong> " . htmlspecialchars($Telefono) . "</p>";
        echo "<p><strong>Correo:</strong> " . htmlspecialchars($Correo) . "</p>";
        echo "<p><strong>Cargo:</strong> " . htmlspecialchars($Cargo) . "</p>";
        echo "<p><strong>Sede ID:</strong> " . htmlspecialchars($IdSede) . "</p>";
        echo "<p><strong>Código QR:</strong> " . htmlspecialchars($QrCodigo) . "</p>";
        echo "</div>";
    } else {
        echo "<div style='color:red;text-align:center;'>❌ Error al registrar funcionario: " . htmlspecialchars($stmt->error) . "</div>";
    }

    $stmt->close();
    $conn->close();

} else {
    echo "<div style='color:red;text-align:center;'>❌ Acceso no permitido. Use el formulario para registrar.</div>";
}
?>
