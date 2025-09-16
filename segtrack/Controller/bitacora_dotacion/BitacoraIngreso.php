<?php
// incluimos la conexion a la base de datos
require_once "../backed/conexion.php";
//
echo "<pre>";
print_r($_POST);
echo "</pre>";

//crear la conexion a la base de datos
$conn = (new Conexion())->getConexion();
//verificamos que la peticion sea por metodo POST si no es asi retornamos un error
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $IdFuncionario = isset($_POST["IdFuncionario"]) ? (int)$_POST["IdFuncionario"] : 0;

    //obtener los datos del formulario por el metodo POST de la bitacora
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $IdBitacora     = $_POST["IdBitacora"];
    $Turno          = $_POST["Turno"];
    $Novedades      = $_POST["Novedades"];
    $IdFuncionario   = $_POST["IdFuncionario"];
    $IdIngreso      = $_POST["IdIngreso"];
    $IdDispositivo   = $_POST["IdDispositivo"];
}
    // Validaciones básicas

    //este codigo verifica que el id de bitacora 
    $checkFun = $conn->prepare("SELECT COUNT(*) FROM bitacora WHERE IdBitacora = ?");
    //vincular el parametro
    $checkFun->bind_param("i", $IdBitacora);
    //ejecutar el parametro
    $checkFun->execute();
    //vincular el resultado
    $checkFun->bind_result($existeFun);
    //obtener el resultado
    $checkFun->fetch();
    //cerrar el parametro
    $checkFun->close();
    
    //verificamos si el id de la bitacora existe si no existe retornamos un error
    if ($existeFun == 0) {
        echo "<div style='color: red; font-weight: bold;'>❌ Error: La Bitacora con ID $IdBitacora no existe.</div>";
        $conn->close();
        exit;
    }

    // verficamos si el id del funcionario coicide con un funcionario existente
    // Preparamos la consulta
    $checkVis = $conn->prepare("SELECT COUNT(*) FROM bitacora WHERE IdFuncionario = ?");
    // Vincular el parámetro
    $checkVis->bind_param("i", $IdFuncionario);
    // Ejecutar la consulta
    $checkVis->execute();
    // Vincular el resultado
    $checkVis->bind_result($existeVis);
    // Obtener el resultado
    $checkVis->fetch();
    // Cerrar la consulta
    $checkVis->close();

//verificamos si el id del funcionario exite y coincide con un funcionario existente si no existe retornamos un error
    if ($existeVis == 0) {
        echo "<div style='color: red; font-weight: bold;'>❌ Error: El Funcionario con ID $IdFuncionario no existe.</div>";
        $conn->close();
        exit;
    }

// Insertar en la base de datos despues de que todas las validaciones sean correctas
    $sql = "INSERT INTO bitacora
        (IdBitacora, Turno, Novedades, IdFuncionario, IdIngreso, IdDispositivo)
        VALUES (?, ?, ?, ?, ?, ?)";

// Preparar la declaracion para evitar inyecciones sql
    $stmt = $conn->prepare($sql);
//verficar si la preparacion fue exitosa
    if ($stmt === false) {
        die("Error en prepare: " . $conn->error);
    }

// Vincular los parámetros con el tipo de dato del campo de la base de datos
    $stmt->bind_param(
            "isssssss", //tipo de dato i = entero, s = string, d = double, b = blob
            $IdBitacora,
            $Turno,
            $Novedades,
            $IdFuncionario,
            $IdIngreso,
            $IdDispositivo
             
    );
    
    // ejectutamos la consulta y verificamos si fue exitosa
    if ($stmt->execute()) {
        echo "<div style='color: green; font-weight: bold;'>Bitacora registrada correctamente</div>";
    } else {
        echo "<div style='color: red; font-weight: bold;'> Error al registrar Bitacora: " . $stmt->error . "</div>";
    }

    $stmt->close();
    $conn->close();
    // manejar errores de conexion y otros errores
} else {
    echo "Acceso no permitido.";
}
?>
