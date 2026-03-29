<?php
require_once __DIR__ . '/../Libs/fpdf186/fpdf.php';
require_once __DIR__ . '/../Model/ModeloIngresoDispositivo.php';

class DispositivoIngresoPDF extends FPDF {

    // ── Paleta exacta del logo SEGTRACK ──────────────────────────────────────
    //    Naranja  #E87722  →  232, 119,  34
    //    Azul     #1A3A5C  →   26,  58,  92
    private $naranja    = [232, 119,  34];
    private $azul       = [ 26,  58,  92];
    private $grisOscuro = [ 44,  62,  80];
    private $grisMedio  = [120, 132, 143];
    private $grisClaro  = [245, 247, 249];
    private $blanco     = [255, 255, 255];
    private $amarillo   = [230, 126,  34];

    private $ml = 8;
    private $mr = 8;
    private $pw;

    // ────────────────────────────────────────────────────────────────────────
    // ENCABEZADO
    // ────────────────────────────────────────────────────────────────────────
    function Header() {

        // Franja naranja top
        $this->SetFillColor(...$this->naranja);
        $this->Rect(0, 0, 210, 3, 'F');

        // Fondo azul
        $this->SetFillColor(...$this->azul);
        $this->Rect(0, 3, 210, 40, 'F');

        // Logo
        $logo = $_SERVER['DOCUMENT_ROOT'] . '/SEGTRACK/Public/img/LOGO_SEGTRACk.jpg';
        if (file_exists($logo)) {
            $this->Image($logo, $this->ml, 6, 54, 0, 'JPG');
        } else {
            $this->SetFont('Arial', 'B', 16);
            $this->SetTextColor(...$this->naranja);
            $this->SetXY($this->ml, 16);
            $this->Cell(54, 10, 'SEGTRACK QR', 0, 0, 'C');
        }

        // Separador vertical naranja
        $this->SetDrawColor(...$this->naranja);
        $this->SetLineWidth(0.8);
        $this->Line($this->ml + 58, 8, $this->ml + 58, 40);

        $xText = $this->ml + 63;
        $wText = 210 - $xText - $this->mr;

        // Título
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(...$this->blanco);
        $this->SetXY($xText, 10);
        $this->Cell($wText, 9, 'REPORTE DE CONTROL DE DISPOSITIVOS', 0, 1, 'L');

        // Subtítulo
        $this->SetFont('Arial', '', 8.5);
        $this->SetTextColor(...$this->naranja);
        $this->SetX($xText);
        $this->Cell($wText, 6, 'Sistema de Gestion de Acceso y Seguridad - SEGTRACK QR', 0, 1, 'L');

        // Fecha + confidencial
        $this->SetFont('Arial', '', 7.5);
        $this->SetTextColor(185, 205, 225);
        $this->SetX($xText);
        $this->Cell($wText * 0.5, 5, 'Generado: ' . date('d/m/Y   H:i:s'), 0, 0, 'L');
        $this->Cell($wText * 0.5, 5, 'Documento Confidencial - Uso Interno', 0, 1, 'R');

        // Franja naranja bottom
        $this->SetFillColor(...$this->naranja);
        $this->Rect(0, 43, 210, 2.5, 'F');

        $this->Ln(8);
    }

    // ────────────────────────────────────────────────────────────────────────
    // PIE DE PÁGINA
    // ────────────────────────────────────────────────────────────────────────
    function Footer() {
        $pH = $this->GetPageHeight();

        $this->SetFillColor(...$this->naranja);
        $this->Rect(0, $pH - 15, 210, 2, 'F');

        $this->SetFillColor(...$this->azul);
        $this->Rect(0, $pH - 13, 210, 13, 'F');

        $this->SetY($pH - 10);
        $this->SetFont('Arial', '', 7);
        $this->SetTextColor(185, 205, 225);
        $this->Cell(80, 5, '(C) ' . date('Y') . ' SEGTRACK QR - Todos los derechos reservados', 0, 0, 'L');
        $this->Cell(0,  5, 'Sistema de Control de Acceso', 0, 0, 'C');
        $this->SetTextColor(...$this->naranja);
        $this->Cell(0,  5, 'Pagina ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
    }

    // ────────────────────────────────────────────────────────────────────────
    // TARJETAS RESUMEN
    // ────────────────────────────────────────────────────────────────────────
    function TarjetasResumen($dispositivos) {

        $total    = count($dispositivos);
        $entradas = 0; $salidas = 0; $hoyCount = 0;
        $hoy      = date('Y-m-d');

        foreach ($dispositivos as $r) {
            if (stripos($r['TipoMovimiento'] ?? '', 'entrada') !== false) $entradas++;
            if (stripos($r['TipoMovimiento'] ?? '', 'salida')  !== false) $salidas++;
            if (!empty($r['FechaIngreso']) && str_starts_with($r['FechaIngreso'], $hoy)) $hoyCount++;
        }

        // Título sección
        $this->SetFillColor(...$this->naranja);
        $this->Rect($this->ml, $this->GetY(), 3.5, 5.5, 'F');
        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor(...$this->grisOscuro);
        $this->SetX($this->ml + 5.5);
        $this->Cell(0, 5.5, 'RESUMEN EJECUTIVO', 0, 1, 'L');
        $this->Ln(3);

        $pw    = 210 - $this->ml - $this->mr;
        $gap   = 4;
        $cardW = ($pw - $gap * 3) / 4;
        $cardH = 30;
        $yBase = $this->GetY();

        $tarjetas = [
            ['TOTAL',       $total,    $this->azul,     [20, 45, 80],   'Dispositivos totales'],
            ['ENTRADAS',    $entradas, $this->azul,     [20, 45, 80],   'Ingresos registrados'],
            ['SALIDAS',     $salidas,  $this->naranja,  [190, 95,  20], 'Salidas registradas'],
            ['HOY',         $hoyCount, $this->amarillo, [190, 95,  20], 'Movimientos del dia'],
        ];

        foreach ($tarjetas as $i => [$label, $valor, $color, $colorDark, $sub]) {
            $cx = $this->ml + $i * ($cardW + $gap);

            // Sombra
            $this->SetFillColor(200, 210, 220);
            $this->RoundedRect($cx + 1.5, $yBase + 1.5, $cardW, $cardH, 3, 'F');

            // Fondo blanco
            $this->SetFillColor(...$this->blanco);
            $this->SetDrawColor(220, 226, 232);
            $this->SetLineWidth(0.25);
            $this->RoundedRect($cx, $yBase, $cardW, $cardH, 3, 'FD');

            // Barra superior de color
            $this->SetFillColor(...$color);
            $this->RoundedRect($cx, $yBase, $cardW, 7, 3, 'F');
            $this->Rect($cx, $yBase + 3.5, $cardW, 3.5, 'F');

            // Etiqueta
            $this->SetFont('Arial', 'B', 7);
            $this->SetTextColor(...$this->blanco);
            $this->SetXY($cx, $yBase + 1);
            $this->Cell($cardW, 5.5, $label, 0, 0, 'C');

            // Número grande
            $this->SetFont('Arial', 'B', 24);
            $this->SetTextColor(...$color);
            $this->SetXY($cx, $yBase + 7);
            $this->Cell($cardW, 15, (string)$valor, 0, 0, 'C');

            // Subtexto
            $this->SetFont('Arial', '', 6.5);
            $this->SetTextColor(...$this->grisMedio);
            $this->SetXY($cx, $yBase + 23);
            $this->Cell($cardW, 5, $sub, 0, 0, 'C');
        }

        $this->SetY($yBase + $cardH + 9);
    }

    // ────────────────────────────────────────────────────────────────────────
    // TABLA PRINCIPAL
    // ────────────────────────────────────────────────────────────────────────
    function TablaDispositivos($dispositivos) {

        $pw = 210 - $this->ml - $this->mr;

        // Título sección
        $this->SetFillColor(...$this->naranja);
        $this->Rect($this->ml, $this->GetY(), 3.5, 5.5, 'F');
        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor(...$this->grisOscuro);
        $this->SetX($this->ml + 5.5);
        $this->Cell(0, 5.5, 'DETALLE DE MOVIMIENTOS DE DISPOSITIVOS', 0, 1, 'L');
        $this->Ln(3);

        // Anchos: #(8) | Tipo(28) | Marca(28) | Serial(32) | Funcionario(40) | Movimiento(28) | Fecha(resto)
        $wNum  = 8;
        $wTipo = 28;
        $wMar  = 28;
        $wSer  = 32;
        $wFunc = 40;
        $wMov  = 28;
        $wFech = $pw - $wNum - $wTipo - $wMar - $wSer - $wFunc - $wMov;
        $w     = [$wNum, $wTipo, $wMar, $wSer, $wFunc, $wMov, $wFech];
        $heads = ['#', 'Tipo', 'Marca', 'Serial', 'Funcionario', 'Movimiento', 'Fecha y Hora'];

        // ── Cabecera ──────────────────────────────────────────────────────────
        $this->SetFillColor(...$this->azul);
        $this->SetTextColor(...$this->blanco);
        $this->SetFont('Arial', 'B', 7.5);
        $this->SetLineWidth(0);
        $this->SetX($this->ml);
        foreach ($heads as $i => $h) {
            $this->Cell($w[$i], 10, $h, 0, 0, 'C', true);
        }
        $this->Ln();

        // Línea naranja bajo cabecera
        $this->SetFillColor(...$this->naranja);
        $this->Rect($this->ml, $this->GetY(), $pw, 1.8, 'F');
        $this->Ln(1.8);

        // ── Filas ─────────────────────────────────────────────────────────────
        $this->SetFont('Arial', '', 7.5);
        $fill = false;

        foreach ($dispositivos as $idx => $row) {

            // Salto de página
            if ($this->GetY() > 250) {
                $this->AddPage();
                $this->SetFillColor(...$this->azul);
                $this->SetTextColor(...$this->blanco);
                $this->SetFont('Arial', 'B', 7.5);
                $this->SetX($this->ml);
                foreach ($heads as $i => $h) {
                    $this->Cell($w[$i], 10, $h, 0, 0, 'C', true);
                }
                $this->Ln();
                $this->SetFillColor(...$this->naranja);
                $this->Rect($this->ml, $this->GetY(), $pw, 1.8, 'F');
                $this->Ln(1.8);
                $this->SetFont('Arial', '', 7.5);
                $fill = false;
            }

            $bg = $fill ? $this->grisClaro : $this->blanco;
            $this->SetFillColor(...$bg);
            $this->SetTextColor(...$this->grisOscuro);
            $this->SetDrawColor(215, 222, 229);
            $this->SetLineWidth(0.1);
            $this->SetX($this->ml);

            // Nº
            $this->Cell($w[0], 9, (string)($idx + 1), 'B', 0, 'C', true);

            // Tipo dispositivo
            $this->Cell($w[1], 9, $this->limpiar(substr($row['TipoDispositivo']  ?? '-', 0, 14)), 'B', 0, 'C', true);

            // Marca
            $this->Cell($w[2], 9, $this->limpiar(substr($row['MarcaDispositivo'] ?? '-', 0, 14)), 'B', 0, 'C', true);

            // Serial
            $this->Cell($w[3], 9, $this->limpiar(substr($row['NumeroSerial']     ?? '-', 0, 16)), 'B', 0, 'C', true);

            // Funcionario
            $this->Cell($w[4], 9, $this->limpiar(substr($row['NombreFuncionario']?? '-', 0, 20)), 'B', 0, 'L', true);

            // Badge movimiento
            $tipo = $row['TipoMovimiento'] ?? '';
            $xB = $this->GetX(); $yB = $this->GetY();
            $this->Cell($w[5], 9, '', 'B', 0, 'C', true);

            $bW = $w[5] - 4; $bX = $xB + 2; $bY = $yB + 2;
            if (stripos($tipo, 'entrada') !== false) {
                $this->SetFillColor(...$this->azul);
                $txt = 'ENTRADA';
            } else {
                $this->SetFillColor(...$this->naranja);
                $txt = 'SALIDA';
            }
            $this->RoundedRect($bX, $bY, $bW, 5, 1.5, 'F');
            $this->SetFont('Arial', 'B', 6);
            $this->SetTextColor(...$this->blanco);
            $this->SetXY($bX, $bY);
            $this->Cell($bW, 5, $txt, 0, 0, 'C');

            // Restaurar
            $this->SetFillColor(...$bg);
            $this->SetTextColor(...$this->grisOscuro);
            $this->SetFont('Arial', '', 7.5);
            $this->SetXY($xB + $w[5], $yB);

            // Fecha
            $fecha = '-';
            if (!empty($row['FechaIngreso'])) {
                $ts = strtotime($row['FechaIngreso']);
                $fecha = $ts ? date('d/m/Y  H:i', $ts) : $row['FechaIngreso'];
            }
            $this->Cell($w[6], 9, $fecha, 'B', 1, 'C', true);

            $fill = !$fill;
        }

        // Línea de cierre naranja
        $this->SetFillColor(...$this->naranja);
        $this->Rect($this->ml, $this->GetY(), $pw, 1.8, 'F');
        $this->Ln(7);

        // Total
        $this->SetX($this->ml);
        $this->SetFont('Arial', 'B', 8.5);
        $this->SetTextColor(...$this->azul);
        $this->Cell($pw, 6, 'Total de registros: ' . count($dispositivos), 0, 1, 'R');
    }

    // ── Helper: UTF-8 → Latin-1 sin romper caracteres ────────────────────────
    private function limpiar($str) {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $str);
    }

    // ── Helper: rectángulo redondeado ─────────────────────────────────────────
    function RoundedRect($x, $y, $w, $h, $r, $style = '') {
        $op  = ($style === 'F') ? 'f' : (($style === 'FD' || $style === 'DF') ? 'B' : 'S');
        $arc = 4 / 3 * (sqrt(2) - 1);
        $k = $this->k; $hp = $this->h;

        $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));

        $xc = $x+$w-$r; $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k, ($hp-$y)*$k));
        $this->_Arc($xc+$r*$arc, $yc-$r, $xc+$r, $yc-$r*$arc, $xc+$r, $yc);

        $xc = $x+$w-$r; $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l', ($x+$w)*$k, ($hp-$yc)*$k));
        $this->_Arc($xc+$r, $yc+$r*$arc, $xc+$r*$arc, $yc+$r, $xc, $yc+$r);

        $xc = $x+$r; $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k, ($hp-($y+$h))*$k));
        $this->_Arc($xc-$r*$arc, $yc+$r, $xc-$r, $yc+$r*$arc, $xc-$r, $yc);

        $xc = $x+$r; $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $x*$k, ($hp-$yc)*$k));
        $this->_Arc($xc-$r, $yc-$r*$arc, $xc-$r*$arc, $yc-$r, $xc, $yc-$r);

        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3) {
        $hp = $this->h; $k = $this->k;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            $x1*$k, ($hp-$y1)*$k, $x2*$k, ($hp-$y2)*$k, $x3*$k, ($hp-$y3)*$k));
    }

    // ────────────────────────────────────────────────────────────────────────
    // GENERADOR PRINCIPAL
    // ────────────────────────────────────────────────────────────────────────
    public function generarPDF() {
        $modelo      = new ModeloIngresoDispositivo();
        $dispositivos = $modelo->listarIngresos();

        $this->pw = 210 - $this->ml - $this->mr;

        $this->AliasNbPages();
        $this->SetMargins($this->ml, 56, $this->mr);
        $this->AddPage('P', 'A4');
        $this->SetAutoPageBreak(true, 22);

        $this->TarjetasResumen($dispositivos);
        $this->TablaDispositivos($dispositivos);

        $this->Output('I', 'SEGTRACK_Dispositivos_' . date('Ymd_His') . '.pdf');
    }
}

if (isset($_GET['accion']) && $_GET['accion'] === 'pdf') {
    $controller = new DispositivoIngresoPDF();
    $controller->generarPDF();
}
?>