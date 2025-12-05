<?php
require_once __DIR__ . '/../Libs/fpdf186/fpdf.php';
require_once __DIR__ . '/../Model/ModeloIngreso.php';

class IngresoPDFController {

    public function generarPDF() {
        // Modelo
        $modelo = new ModeloIngreso();
        $ingresos = $modelo->listarIngresos();

        // Crear PDF (sin argumentos nombrados para compatibilidad)
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        

        // Título
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, utf8_decode('REPORTE DE INGRESOS'), 0, 1, 'C');
        $pdf->Ln(5);

        // Encabezados
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(20, 10, 'ID', 1, 0, 'C');
        $pdf->Cell(35, 10, 'Movimiento', 1, 0, 'C');
        $pdf->Cell(50, 10, 'Funcionario', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Cargo', 1, 0, 'C');
        $pdf->Cell(45, 10, 'Fecha', 1, 1, 'C');

        // Contenido
        $pdf->SetFont('Arial', '', 11);

        foreach ($ingresos as $row) {
            $pdf->Cell(20, 10, $row['IdIngreso'], 1);
            $pdf->Cell(35, 10, utf8_decode($row['TipoMovimiento']), 1);
            $pdf->Cell(50, 10, utf8_decode($row['NombreFuncionario']), 1);
            $pdf->Cell(40, 10, utf8_decode($row['CargoFuncionario']), 1);
            $pdf->Cell(45, 10, $row['FechaIngreso'], 1, 1);
        }

        // Mostrar PDF
        $pdf->Output('I', 'ReporteIngresos.pdf');
    }
}

// EJECUCIÓN DIRECTA
if (isset($_GET['accion']) && $_GET['accion'] === 'pdf') {
    $controller = new IngresoPDFController();
    $controller->generarPDF();
}