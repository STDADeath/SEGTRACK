<?php
require_once __DIR__ . '/../Libs/fpdf186/fpdf.php';
require_once __DIR__ . '/../Model/ModeloIngreso.php';

class IngresoPDFController extends FPDF {
    
    // Encabezado del PDF
   function Header() {
    // Fondo del encabezado
    $this->SetFillColor(41, 128, 185); // Azul profesional
    $this->Rect(0, 0, 210, 35, 'F');

    // Título principal (sin emoji)
    $this->SetFont('Arial', 'B', 18);
    $this->SetTextColor(255, 255, 255);
    $this->SetY(10);
    $this->Cell(0, 10, utf8_decode('Reporte SEGTRACK INGRESOS'), 0, 1, 'C');

    // Fecha
    $this->SetFont('Arial', '', 10);
    $this->Cell(0, 5, utf8_decode('Generado: ' . date('d/m/Y H:i:s')), 0, 1, 'C');

    $this->Ln(8);
}

    
    // Pie de página
    function Footer() {
        $this->SetY(-20);
        
        // Línea decorativa
        $this->SetDrawColor(41, 128, 185);
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        
        // Información del pie
        $this->Ln(2);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, utf8_decode('Sistema de Gestión - Documento Confidencial'), 0, 0, 'L');
        $this->Cell(0, 5, utf8_decode('Página ') . $this->PageNo() . ' de {nb}', 0, 0, 'R');
    }
    
    // Tabla mejorada con colores alternados
    function TablaIngresos($ingresos) {
        // Colores
        $colorEncabezado = array(52, 73, 94); // Gris oscuro
        $colorFila1 = array(236, 240, 241); // Gris claro
        $colorFila2 = array(255, 255, 255); // Blanco
        $colorTexto = array(44, 62, 80); // Gris oscuro para texto
        
        // Encabezados con estilo
        $this->SetFillColor($colorEncabezado[0], $colorEncabezado[1], $colorEncabezado[2]);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(41, 128, 185);
        $this->SetLineWidth(0.3);
        $this->SetFont('Arial', 'B', 11);
        
        // Anchos de columna optimizados
        $w = array(15, 35, 55, 40, 45);
        $headers = array('ID', 'Movimiento', 'Funcionario', 'Cargo', 'Fecha');
        
        for($i = 0; $i < count($headers); $i++) {
            $this->Cell($w[$i], 9, utf8_decode($headers[$i]), 1, 0, 'C', true);
        }
        $this->Ln();
        
        // Datos con filas alternadas
        $this->SetFont('Arial', '', 10);
        $fill = false;
        $contador = 0;
        
        foreach($ingresos as $row) {
            // Alternar colores
            if($fill) {
                $this->SetFillColor($colorFila1[0], $colorFila1[1], $colorFila1[2]);
            } else {
                $this->SetFillColor($colorFila2[0], $colorFila2[1], $colorFila2[2]);
            }
            $this->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
            
            // Badge de color según tipo de movimiento
            $tipoMov = $row['TipoMovimiento'];
            $this->Cell($w[0], 8, $row['IdIngreso'], 'LR', 0, 'C', true);
            
            // Celda de movimiento con color especial
            $x = $this->GetX();
            $y = $this->GetY();
            $this->Cell($w[1], 8, '', 'LR', 0, 'L', true);
            $this->SetXY($x, $y);
            
            // Color según tipo
            if(stripos($tipoMov, 'entrada') !== false) {
                $this->SetTextColor(39, 174, 96); // Verde
            } elseif(stripos($tipoMov, 'salida') !== false) {
                $this->SetTextColor(231, 76, 60); // Rojo
            } else {
                $this->SetTextColor(52, 152, 219); // Azul
            }
            
            $this->Cell($w[1], 8, utf8_decode(substr($tipoMov, 0, 15)), 0, 0, 'C');
            $this->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
            
            // Resto de celdas
            $this->Cell($w[2], 8, utf8_decode(substr($row['NombreFuncionario'], 0, 25)), 'LR', 0, 'L', false);
            $this->Cell($w[3], 8, utf8_decode(substr($row['CargoFuncionario'], 0, 18)), 'LR', 0, 'L', false);
            $this->Cell($w[4], 8, $row['FechaIngreso'], 'LR', 1, 'C', false);
            
            $fill = !$fill;
            $contador++;
        }
        
        // Línea de cierre de la tabla
        $this->SetDrawColor(41, 128, 185);
        $this->Cell(array_sum($w), 0, '', 'T');
        $this->Ln(10);
        
        // Resumen
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(52, 73, 94);
        $this->Cell(0, 8, utf8_decode('Total de registros: ' . $contador), 0, 1, 'R');
    }
    
    public function generarPDF() {
        // Obtener datos
        $modelo = new ModeloIngreso();
        $ingresos = $modelo->listarIngresos();
        
        // Configurar PDF
        $this->AliasNbPages();
        $this->AddPage();
        $this->SetAutoPageBreak(true, 25);
        
        // Información adicional (opcional)
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->MultiCell(0, 5, utf8_decode(''), 0, 'L');
        $this->Ln(5);
        
        // Generar tabla
        $this->TablaIngresos($ingresos);
        
        // Salida
        $this->Output('I', 'ReporteIngresos_' . date('Ymd_His') . '.pdf');
    }
}

// EJECUCIÓN DIRECTA
if (isset($_GET['accion']) && $_GET['accion'] === 'pdf') {
    $controller = new IngresoPDFController();
    $controller->generarPDF();
}
?>