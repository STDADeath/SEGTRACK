<?php
$archivo = __DIR__ . '/App/Model/ModeloParqueadero.php';
echo "<h2>Contenido de ModeloParqueadero.php:</h2>";
echo "<pre>";
echo htmlspecialchars(file_get_contents($archivo));
echo "</pre>";

echo "<hr>";
echo "<h2>Línea 2 específicamente:</h2>";
$lineas = file($archivo);
echo "<pre>";
echo "Línea 2: " . htmlspecialchars($lineas[1]);
echo "</pre>";
?>
```

Esto te mostrará **exactamente** lo que tiene el archivo en el servidor.

---

### **4. Si la línea 2 sigue mostrando `/../../`:**

Entonces el problema es que VS Code no está guardando correctamente. Haz esto:

1. **Cierra VS Code completamente**
2. Abre el archivo con **Notepad++** o **Bloc de notas**:
```
   C:\xampp\htdocs\SEGTRACK\App\Model\ModeloParqueadero.php