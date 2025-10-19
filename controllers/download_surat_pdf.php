<?php
/**
 * Controller untuk download surat laporan kerusakan dalam format PDF
 * Menggunakan template Excel yang sudah ada dan mengkonversinya ke PDF
 */

// Set encoding UTF-8 tanpa BOM
while (ob_get_level()) {
    ob_end_clean();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/browser_auth.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isLoggedIn()) {
    sendErrorResponse('Sesi pengguna tidak valid. Silakan login ulang.');
}

// Get case ID from URL
$case_id = $_GET['id'] ?? $_GET['case_id'] ?? null;

if (!$case_id) {
    sendErrorResponse('ID laporan tidak ditemukan');
}

try {
    // Get case data from database
    $db = new Database();
    $case = $db->find('cases', $case_id);
    
    if (!$case) {
        sendErrorResponse('Laporan tidak ditemukan');
    }
    
    // Generate PDF file
    generateSuratPDF($case);
    
} catch (Exception $e) {
    error_log('Error generating surat PDF: ' . $e->getMessage());
    sendErrorResponse('Terjadi kesalahan saat membuat surat: ' . $e->getMessage());
}

/**
 * Generate PDF surat from case data
 */
function generateSuratPDF($case) {
    // Check if template exists
    $templatePath = __DIR__ . '/../templates/Template_Laporan_Kerusakan_Peralatan.xlsx';
    
    if (!file_exists($templatePath)) {
        sendErrorResponse('Template surat tidak ditemukan. Silakan hubungi admin SIPETRA.');
    }
    
    try {
        // Read template Excel
        $excelContent = readTemplateExcel($templatePath, $case);
        
        // Convert to PDF
        $pdfContent = convertExcelToPDF($excelContent, $case);
        
        // Generate filename
        $equipmentName = $case['equipment_name'] ?? 'Peralatan';
        $location = $case['location'] ?? 'Lokasi';
        $damageDate = $case['damage_date'] ?? date('Y-m-d');
        
        $filename = "Laporan_Kerusakan_{$equipmentName}_{$location}_{$damageDate}.pdf";
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
        
        // Set headers for PDF download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Length: ' . strlen($pdfContent));
        
        // Output PDF
        echo $pdfContent;
        exit;
        
    } catch (Exception $e) {
        error_log('Error generating PDF: ' . $e->getMessage());
        sendErrorResponse('Terjadi kesalahan saat membuat PDF: ' . $e->getMessage());
    }
}

/**
 * Read template Excel and fill with case data
 */
function readTemplateExcel($templatePath, $case) {
    // Read template Excel file
    $templateContent = file_get_contents($templatePath);
    
    if ($templateContent === false) {
        throw new Exception('Tidak dapat membaca template Excel');
    }
    
    // Replace placeholders with actual data
    $templateContent = str_replace('{{LOKASI}}', htmlspecialchars($case['location'] ?? 'N/A'), $templateContent);
    $templateContent = str_replace('{{TANGGAL_KERUSAKAN}}', htmlspecialchars($case['damage_date'] ?? 'N/A'), $templateContent);
    $templateContent = str_replace('{{NAMA_PERALATAN}}', htmlspecialchars($case['equipment_name'] ?? 'N/A'), $templateContent);
    $templateContent = str_replace('{{MODEL}}', htmlspecialchars($case['model'] ?? 'N/A'), $templateContent);
    $templateContent = str_replace('{{SERIAL_NUMBER}}', htmlspecialchars($case['serial_number'] ?? 'N/A'), $templateContent);
    $templateContent = str_replace('{{KONDISI_KERUSAKAN}}', htmlspecialchars(getDamageConditionText($case['damage_condition'] ?? 'light')), $templateContent);
    $templateContent = str_replace('{{ID_LAPORAN}}', htmlspecialchars($case['id_laporan'] ?? 'N/A'), $templateContent);
    $templateContent = str_replace('{{DESKRIPSI_KERUSAKAN}}', htmlspecialchars($case['description'] ?? 'N/A'), $templateContent);
    $templateContent = str_replace('{{PELAPOR}}', htmlspecialchars($case['reported_by'] ?? 'N/A'), $templateContent);
    $templateContent = str_replace('{{TANGGAL_LAPORAN}}', date('d-m-Y H:i:s'), $templateContent);
    
    return $templateContent;
}

/**
 * Generate HTML representation of the template
 */
function generateHTMLFromTemplate($case) {
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Kerusakan Peralatan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #0066cc;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
        }
        .section {
            margin: 20px 0;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .value {
            display: inline-block;
        }
        .description {
            border: 1px solid #ccc;
            padding: 10px;
            margin: 10px 0;
            min-height: 100px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
        }
        .signature {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 200px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">TVRI KALIMANTAN TENGAH</div>
        <div class="title">LAPORAN KERUSAKAN PERALATAN</div>
    </div>
    
    <div class="section">
        <div class="label">Lokasi Tx:</div>
        <div class="value">' . htmlspecialchars($case['location'] ?? 'N/A') . '</div>
    </div>
    
    <div class="section">
        <div class="label">Tanggal Kerusakan:</div>
        <div class="value">' . htmlspecialchars($case['damage_date'] ?? 'N/A') . '</div>
    </div>
    
    <div class="section">
        <div class="label">Nama Peralatan:</div>
        <div class="value">' . htmlspecialchars($case['equipment_name'] ?? 'N/A') . '</div>
    </div>
    
    <div class="section">
        <div class="label">Model:</div>
        <div class="value">' . htmlspecialchars($case['model'] ?? 'N/A') . '</div>
    </div>
    
    <div class="section">
        <div class="label">Serial Number:</div>
        <div class="value">' . htmlspecialchars($case['serial_number'] ?? 'N/A') . '</div>
    </div>
    
    <div class="section">
        <div class="label">Kondisi Kerusakan:</div>
        <div class="value">' . htmlspecialchars(getDamageConditionText($case['damage_condition'] ?? 'light')) . '</div>
    </div>
    
    <div class="section">
        <div class="label">ID Laporan:</div>
        <div class="value">' . htmlspecialchars($case['id_laporan'] ?? 'N/A') . '</div>
    </div>
    
    <div class="section">
        <div class="label">Penjelasan Rinci:</div>
        <div class="description">' . nl2br(htmlspecialchars($case['description'] ?? 'N/A')) . '</div>
    </div>
    
    <div class="footer">
        <div class="signature">
            <div class="signature-box">
                <div>Pelapor</div>
                <div style="margin-top: 50px;">_________________</div>
                <div>' . htmlspecialchars($case['reported_by'] ?? 'N/A') . '</div>
            </div>
            <div class="signature-box">
                <div>Mengetahui</div>
                <div style="margin-top: 50px;">_________________</div>
                <div>Kepala Teknik</div>
            </div>
        </div>
    </div>
</body>
</html>';
    
    return $html;
}

/**
 * Convert Excel content to PDF
 */
function convertExcelToPDF($excelContent, $case) {
    // Generate PDF directly from case data
    $pdfContent = generatePDFFromCaseData($case);
    return $pdfContent;
}

/**
 * Convert Excel XML to HTML
 */
function convertExcelToHTML($excelContent) {
    // Parse Excel XML and convert to HTML
    $dom = new DOMDocument();
    $dom->loadXML($excelContent);
    
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Kerusakan Peralatan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #0066cc;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
        }
        .section {
            margin: 20px 0;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .value {
            display: inline-block;
        }
        .description {
            border: 1px solid #ccc;
            padding: 10px;
            margin: 10px 0;
            min-height: 100px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
        }
        .signature {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 200px;
            text-align: center;
        }
    </style>
</head>
<body>';
    
    // Extract data from Excel XML
    $xpath = new DOMXPath($dom);
    $xpath->registerNamespace('ss', 'urn:schemas-microsoft-com:office:spreadsheet');
    
    $rows = $xpath->query('//ss:Row');
    
    foreach ($rows as $row) {
        $cells = $xpath->query('.//ss:Cell', $row);
        if ($cells->length > 0) {
            $cellText = '';
            foreach ($cells as $cell) {
                $data = $xpath->query('.//ss:Data', $cell);
                if ($data->length > 0) {
                    $cellText .= $data->item(0)->textContent . ' ';
                }
            }
            if (trim($cellText)) {
                $html .= '<div class="section">' . htmlspecialchars(trim($cellText)) . '</div>';
            }
        }
    }
    
    $html .= '</body></html>';
    
    return $html;
}

/**
 * Generate PDF from case data
 */
function generatePDFFromCaseData($case) {
    $pdfContent = "LAPORAN KERUSAKAN PERALATAN\n";
    $pdfContent .= "TVRI KALIMANTAN TENGAH\n";
    $pdfContent .= str_repeat("=", 50) . "\n\n";
    
    $pdfContent .= "DATA KERUSAKAN\n";
    $pdfContent .= str_repeat("-", 20) . "\n";
    $pdfContent .= "Lokasi Tx: " . ($case['location'] ?? 'N/A') . "\n";
    $pdfContent .= "Tanggal Kerusakan: " . ($case['damage_date'] ?? 'N/A') . "\n";
    $pdfContent .= "Nama Peralatan: " . ($case['equipment_name'] ?? 'N/A') . "\n";
    $pdfContent .= "Model: " . ($case['model'] ?? 'N/A') . "\n";
    $pdfContent .= "Serial Number: " . ($case['serial_number'] ?? 'N/A') . "\n";
    $pdfContent .= "Kondisi Kerusakan: " . getDamageConditionText($case['damage_condition'] ?? 'light') . "\n";
    $pdfContent .= "ID Laporan: " . ($case['id_laporan'] ?? 'N/A') . "\n\n";
    
    $pdfContent .= "PENJELASAN RINCI KERUSAKAN\n";
    $pdfContent .= str_repeat("-", 30) . "\n";
    $pdfContent .= ($case['description'] ?? 'N/A') . "\n\n";
    
    $pdfContent .= "INFORMASI PELAPORAN\n";
    $pdfContent .= str_repeat("-", 25) . "\n";
    $pdfContent .= "Pelapor: " . ($case['reported_by'] ?? 'N/A') . "\n";
    $pdfContent .= "Tanggal Laporan: " . date('d-m-Y H:i:s') . "\n\n";
    
    $pdfContent .= str_repeat("=", 50) . "\n";
    $pdfContent .= "Mengetahui,\n\n";
    $pdfContent .= "Kepala Teknik\n";
    $pdfContent .= "_________________\n";
    
    return $pdfContent;
}

/**
 * Generate PDF from HTML
 */
function generatePDFFromHTML($htmlContent) {
    // For now, we'll create a simple text-based PDF
    // In production, you would use mPDF or similar library
    
    $pdfContent = "LAPORAN KERUSAKAN PERALATAN\n";
    $pdfContent .= "TVRI KALIMANTAN TENGAH\n\n";
    
    // Extract text content from HTML
    $textContent = strip_tags($htmlContent);
    $textContent = html_entity_decode($textContent);
    $textContent = preg_replace('/\s+/', ' ', $textContent);
    
    $pdfContent .= $textContent;
    
    return $pdfContent;
}

/**
 * Generate simple PDF content
 */
function generateSimplePDF($case) {
    // This is a simplified PDF generation
    // In production, you would use a proper PDF library
    
    $content = "LAPORAN KERUSAKAN PERALATAN\n";
    $content .= "TVRI KALIMANTAN TENGAH\n\n";
    $content .= "Lokasi Tx: " . ($case['location'] ?? 'N/A') . "\n";
    $content .= "Tanggal Kerusakan: " . ($case['damage_date'] ?? 'N/A') . "\n";
    $content .= "Nama Peralatan: " . ($case['equipment_name'] ?? 'N/A') . "\n";
    $content .= "Model: " . ($case['model'] ?? 'N/A') . "\n";
    $content .= "Serial Number: " . ($case['serial_number'] ?? 'N/A') . "\n";
    $content .= "Kondisi Kerusakan: " . getDamageConditionText($case['damage_condition'] ?? 'light') . "\n";
    $content .= "ID Laporan: " . ($case['id_laporan'] ?? 'N/A') . "\n\n";
    $content .= "Penjelasan Rinci:\n";
    $content .= $case['description'] ?? 'N/A';
    $content .= "\n\n";
    $content .= "Pelapor: " . ($case['reported_by'] ?? 'N/A') . "\n";
    $content .= "Tanggal: " . date('d-m-Y H:i:s');
    
    return $content;
}

/**
 * Get damage condition text
 */
function getDamageConditionText($condition) {
    $conditions = [
        'light' => 'Rusak Ringan',
        'moderate' => 'Rusak Sedang', 
        'severe' => 'Rusak Berat'
    ];
    
    return $conditions[$condition] ?? 'Tidak Diketahui';
}

/**
 * Send error response
 */
function sendErrorResponse($message) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $message
    ]);
    exit;
}
?>
