<?php
require_once __DIR__ . '/../Libs/fpdf186/fpdf.php';
require_once __DIR__ . '/../Model/ModeloIngresoDispositivo.php'; // ✅ Modelo correcto

class DispositivoIngresoPDF extends FPDF {
    
    // Encabezado del PDF
    function Header() {
        $this->SetFillColor(41, 128, 185);
        $this->Rect(0, 0, 210, 35, 'F');
        
        $this->SetFont('Arial', 'B', 24);
        $this->SetTextColor(255, 255, 255);
        $this->SetY(10);
        $this->Cell(0, 10, 'SEGTRACK', 0, 1, 'C');
        
        $this->SetFont('Arial', 'B', 18);
        $this->Cell(0, 8, utf8_decode('Reporte SEGTRACK DISPOSITIVOS'), 0, 1, 'C');
        
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, utf8_decode('Generado: ' . date('d/m/Y H:i:s')), 0, 1, 'C');
        
        $this->Ln(8);
    }
    
    // Pie de página
    function Footer() {
        $this->SetY(-20);
        
        $this->SetDrawColor(41, 128, 185);
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        
        $this->Ln(2);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, utf8_decode('Sistema de Gestión - Documento Confidencial'), 0, 0, 'L');
        $this->Cell(0, 5, utf8_decode('Página ') . $this->PageNo() . ' de {nb}', 0, 0, 'R');
    }
    
    // Tabla con colores alternados
    function TablaDispositivos($dispositivos) {
        $colorEncabezado = array(52, 73, 94);
        $colorFila1      = array(236, 240, 241);
        $colorFila2      = array(255, 255, 255);
        $colorTexto      = array(44, 62, 80);
        
        // Encabezados
        $this->SetFillColor($colorEncabezado[0], $colorEncabezado[1], $colorEncabezado[2]);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(41, 128, 185);
        $this->SetLineWidth(0.3);
        $this->SetFont('Arial', 'B', 10);
        
        $w       = array(30, 35, 35, 35, 30, 35);
        $headers = array('Tipo', 'Marca', 'Serial', 'Funcionario', 'Movimiento', 'Fecha');
        
        for ($i = 0; $i < count($headers); $i++) {
            $this->Cell($w[$i], 9, utf8_decode($headers[$i]), 1, 0, 'C', true);
        }
        $this->Ln();
        
        // Filas
        $this->SetFont('Arial', '', 9);
        $fill    = false;
        $contador = 0;
        
        foreach ($dispositivos as $row) {
            if ($fill) {
                $this->SetFillColor($colorFila1[0], $colorFila1[1], $colorFila1[2]);
            } else {
                $this->SetFillColor($colorFila2[0], $colorFila2[1], $colorFila2[2]);
            }
            $this->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
            
            $this->Cell($w[0], 8, utf8_decode(substr($row['TipoDispositivo'],  0, 14)), 'LR', 0, 'C', true);
            $this->Cell($w[1], 8, utf8_decode(substr($row['MarcaDispositivo'], 0, 16)), 'LR', 0, 'C', true);
            $this->Cell($w[2], 8, utf8_decode(substr($row['NumeroSerial'],     0, 16)), 'LR', 0, 'C', true);
            $this->Cell($w[3], 8, utf8_decode(substr($row['NombreFuncionario'],0, 16)), 'LR', 0, 'C', true);
            
            // Color según tipo de movimiento
            $tipoMov = $row['TipoMovimiento'];
            $x = $this->GetX();
            $y = $this->GetY();
            $this->Cell($w[4], 8, '', 'LR', 0, 'L', true);
            $this->SetXY($x, $y);
            
            if (stripos($tipoMov, 'entrada') !== false) {
                $this->SetTextColor(39, 174, 96);  // Verde
            } elseif (stripos($tipoMov, 'salida') !== false) {
                $this->SetTextColor(231, 76, 60);  // Rojo
            }
            $this->Cell($w[4], 8, utf8_decode($tipoMov), 0, 0, 'C');
            $this->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
            
            // Fecha formateada
            $fecha = date('d/m/Y H:i', strtotime($row['FechaIngreso']));
            $this->Cell($w[5], 8, $fecha, 'LR', 1, 'C', false);
            
            $fill = !$fill;
            $contador++;
        }
        
        // Línea de cierre
        $this->SetDrawColor(41, 128, 185);
        $this->Cell(array_sum($w), 0, '', 'T');
        $this->Ln(10);
        
        // Total
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(52, 73, 94);
        $this->Cell(0, 8, utf8_decode('Total de registros: ' . $contador), 0, 1, 'R');
    }
    
    public function generarPDF() {
        // ✅ Usar ModeloIngresoDispositivo (no ModeloDispositivo)
        $modelo      = new ModeloIngresoDispositivo();
        $dispositivos = $modelo->listarIngresos();
        
        $this->AliasNbPages();
        $this->AddPage();
        $this->SetAutoPageBreak(true, 25);
        
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->MultiCell(0, 5, utf8_decode('Este documento contiene el listado de los ingresos de dispositivos. La información es de carácter confidencial.'), 0, 'L');
        $this->Ln(5);
        
        $this->TablaDispositivos($dispositivos);
        
        $this->Output('I', 'ReporteDispositivos_' . date('Ymd_His') . '.pdf');
    }
}

// EJECUCIÓN DIRECTA
if (isset($_GET['accion']) && $_GET['accion'] === 'pdf') {
    $controller = new DispositivoIngresoPDF();
    $controller->generarPDF();
}
?>