<?php
// Exportar reportes en CSV
require_once __DIR__ . '/../../config/database.php';

// report: monthly | trimestral | anual
$report = $_GET['report'] ?? 'monthly';
$format = $_GET['format'] ?? 'csv';

// Validar
$allowed = ['monthly' => 30, 'trimestral' => 90, 'anual' => 365];
if (!isset($allowed[$report])) {
    http_response_code(400);
    echo "Reporte no válido";
    exit;
}

$days = $allowed[$report];

try {
    $conn = getConnection();

    // Fecha desde
    $desde = (new DateTime())->modify("-{$days} days")->format('Y-m-d');

    // Consultar solicitudes en rango
    $sql = "SELECT s.id, u.nombre AS empleado, s.tipo, s.fecha_inicio, s.fecha_fin, s.dias, s.estado, s.prioridad, a.nombre AS aprobador, s.motivo
            FROM solicitudes s
            LEFT JOIN usuarios u ON s.usuario_id = u.id
            LEFT JOIN usuarios a ON s.aprobador_id = a.id
            WHERE s.fecha_inicio >= ?
            ORDER BY s.fecha_inicio DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $desde);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($format === 'pdf') {
        // Generate PDF using bundled minimal PDF helper
        require_once __DIR__ . '/../../lib/fpdf.php';

        $filename = "reporte_{$report}_" . date('Ymd') . ".pdf";

        $pdf = new FPDF();
        $pdf->SetTitle("Reporte {$report}");
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 10);

        // Header line
        $header = ['ID','Empleado','Tipo','Fecha inicio','Fecha fin','Días','Estado','Prioridad','Aprobador','Motivo'];
        $pdf->Cell(0, 6, implode(' | ', $header));
        $pdf->Ln(8);

        while ($row = $res->fetch_assoc()) {
            $line = [
                $row['id'],
                $row['empleado'],
                $row['tipo'],
                $row['fecha_inicio'],
                $row['fecha_fin'],
                $row['dias'],
                $row['estado'],
                $row['prioridad'],
                $row['aprobador'] ?? '',
                $row['motivo'] ?? ''
            ];
            // Limit length to avoid overly long lines
            $pdf->Cell(0, 6, mb_substr(implode(' | ', $line), 0, 100));
            $pdf->Ln(6);
        }

        // Output PDF (inline will prompt download due to headers)
        $pdf->Output('I', $filename);

    } else {
        // Default CSV
        // Cabeceras para forzar descarga CSV
        $filename = "reporte_{$report}_" . date('Ymd') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // BOM para Excel en Windows
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Encabezado
        fputcsv($output, ['ID','Empleado','Tipo','Fecha inicio','Fecha fin','Días','Estado','Prioridad','Aprobador','Motivo']);

        while ($row = $res->fetch_assoc()) {
            fputcsv($output, [
                $row['id'],
                $row['empleado'],
                $row['tipo'],
                $row['fecha_inicio'],
                $row['fecha_fin'],
                $row['dias'],
                $row['estado'],
                $row['prioridad'],
                $row['aprobador'] ?? '',
                $row['motivo'] ?? ''
            ]);
        }

        fclose($output);
    }
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo "Error generando reporte: " . $e->getMessage();
}

?>
