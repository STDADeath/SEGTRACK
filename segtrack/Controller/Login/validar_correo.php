<?php
session_start();

try {
    // Incluir el archivo de conexión
    require_once "../Conexion/conexion.php";
    
    // Instanciar la clase Conexion y obtener la conexión MySQLi
    $conexionObj = new Conexion();
    $mysqli = $conexionObj->getConexion();
    
    // Verificar conexión
    if ($conexion->connect_error) {
        die("Error de conexión: " . $mysqli->connect_error);
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $correo_ingresado = trim($_POST['correo']);
        $contrasena_ingresada = trim($_POST['contrasena']);
        
        if (empty($correo_ingresado) || empty($contrasena_ingresada)) {
            echo "<script>alert('Complete todos los campos'); window.history.back();</script>";
            exit();
        }
        
        // Consulta corregida con los nombres correctos de los campos
        $sql = "SELECT u.IdUsuario, u.Contrasena, u.TipoRol, f.NombreFuncionario, f.CorreoFuncionario, f.CargoFuncionario, f.IdSede 
                FROM usuario u 
                INNER JOIN funcionario f ON u.IdFuncionario = f.IdFuncionario 
                WHERE f.CorreoFuncionario = ?";
        
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            echo "<script>alert('Error en la consulta: " . $mysqli->error . "'); window.history.back();</script>";
            exit();
        }
        
        $stmt->bind_param("s", $correo_ingresado);
        $stmt->execute();
        $result = $stmt->get_result();
        $resultado = $result->fetch_assoc();
        
        if ($resultado) {
            // Verificar contraseña
            if ($contrasena_ingresada == $resultado['Contrasena']) {
                // Login exitoso - guardar en sesión con nombres corregidos
                $_SESSION['usuario_id'] = $resultado['IdUsuario'];
                $_SESSION['nombre'] = $resultado['NombreFuncionario'];
                $_SESSION['correo'] = $resultado['CorreoFuncionario'];
                $_SESSION['cargo'] = $resultado['CargoFuncionario'];
                $_SESSION['tipo_rol'] = $resultado['TipoRol'];
                $_SESSION['sede'] = $resultado['IdSede'];
                $_SESSION['logueado'] = true;
                
                // Redireccionar a dashboard
                header('Location: ../models/Funcionario.php');
                exit();
            } else {
                echo "<script>alert('Contraseña incorrecta'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('Usuario no encontrado con email: " . htmlspecialchars($correo_ingresado) . "'); window.history.back();</script>";
        }
        
        $stmt->close();
    }
    
} catch (Exception $e) {
    echo "<script>alert('Error: " . htmlspecialchars($e->getMessage()) . "'); window.history.back();</script>";
} finally {
    if (isset($mysqli)) {
        $mysqli->close();
    }
}
?>