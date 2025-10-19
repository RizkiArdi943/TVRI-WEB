<?php
/**
 * Controller untuk download surat laporan kerusakan
 * Mendukung template .xls dan .xlsx dengan konversi otomatis
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
    
    // Generate Excel file
    generateSuratExcel($case);
    
} catch (Exception $e) {
    error_log('Error generating surat: ' . $e->getMessage());
    sendErrorResponse('Terjadi kesalahan saat membuat surat: ' . $e->getMessage());
}

/**
 * Generate Excel surat from case data
 */
function generateSuratExcel($case) {
    try {
        // Template paths
        $templateXls = __DIR__ . '/../templates/Template_Laporan_Kerusakan_Peralatan.xls';
        $templateXlsx = __DIR__ . '/../templates/Template_Laporan_Kerusakan_Peralatan.xlsx';
        
        // Check which template exists
        $templatePath = null;
        $isXls = false;
        
        if (file_exists($templateXlsx)) {
            $templatePath = $templateXlsx;
        } elseif (file_exists($templateXls)) {
            $templatePath = $templateXls;
            $isXls = true;
        } else {
            sendErrorResponse('Template surat tidak ditemukan. Hubungi admin untuk memperbaiki file template.');
        }
        
        // Read template content
        $templateContent = file_get_contents($templatePath);
        
        if (!$templateContent) {
            sendErrorResponse('Template surat rusak atau tidak dapat dibaca. Hubungi admin untuk memperbaiki file template.');
        }
        
        // Process template based on format
        if ($isXls) {
            $excelContent = processXlsTemplate($templateContent, $case);
        } else {
            $excelContent = processXlsxTemplate($templateContent, $case);
        }
        
        // Generate filename
        $filename = generateFilename($case);
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        header('Content-Length: ' . strlen($excelContent));
        
        // Output file
        echo $excelContent;
        exit();
        
    } catch (Exception $e) {
        error_log('Error generating Excel: ' . $e->getMessage());
        sendErrorResponse('Terjadi kesalahan saat membuat file Excel: ' . $e->getMessage());
    }
}

/**
 * Process XLS template (legacy format)
 */
function processXlsTemplate($templateContent, $case) {
    // For XLS files, we'll create a new XLSX format
    return createXlsxFromData($case);
}

/**
 * Process XLSX template (modern format)
 */
function processXlsxTemplate($templateContent, $case) {
    // Replace placeholders in XLSX template
    $content = str_replace('{{ID_LAPORAN}}', htmlspecialchars($case['id_laporan'] ?? 'N/A'), $templateContent);
    $content = str_replace('{{LOKASI_TX}}', htmlspecialchars($case['location'] ?? 'N/A'), $content);
    $content = str_replace('{{TGL_KERUSAKAN}}', htmlspecialchars($case['damage_date'] ?? 'N/A'), $content);
    $content = str_replace('{{PERALATAN}}', htmlspecialchars($case['equipment_name'] ?? 'N/A'), $content);
    $content = str_replace('{{MODEL}}', htmlspecialchars($case['model'] ?? 'N/A'), $content);
    $content = str_replace('{{SERIAL_NUMBER}}', htmlspecialchars($case['serial_number'] ?? 'N/A'), $content);
    $content = str_replace('{{DESKRIPSI}}', htmlspecialchars($case['description'] ?? 'N/A'), $content);
    $content = str_replace('{{KONDISI_KERUSAKAN}}', htmlspecialchars(getDamageConditionText($case['damage_condition'] ?? 'light')), $content);
    
    return $content;
}

/**
 * Create XLSX from data (fallback method)
 */
function createXlsxFromData($case) {
    // Create a simple but valid Excel XML
    $xml = '<?xml version="1.0" encoding="UTF-8"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
<Title>Laporan Kerusakan Peralatan</Title>
<Author>TVRI Kalimantan Tengah</Author>
<Created>' . date('Y-m-d\TH:i:s\Z') . '</Created>
</DocumentProperties>
<Styles>
<Style ss:ID="Default" ss:Name="Normal">
<Alignment ss:Vertical="Bottom"/>
<Borders/>
<Font ss:FontName="Calibri" ss:Size="11" ss:Color="#000000"/>
<Interior/>
<NumberFormat/>
<Protection/>
</Style>
<Style ss:ID="Header">
<Font ss:FontName="Calibri" ss:Bold="1" ss:Size="16" ss:Color="#000000"/>
<Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
</Style>
<Style ss:ID="SubHeader">
<Font ss:FontName="Calibri" ss:Bold="1" ss:Size="14" ss:Color="#000000"/>
<Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
</Style>
<Style ss:ID="Section">
<Font ss:FontName="Calibri" ss:Bold="1" ss:Size="12" ss:Color="#000000"/>
</Style>
<Style ss:ID="Label">
<Font ss:FontName="Calibri" ss:Bold="1" ss:Size="11" ss:Color="#000000"/>
</Style>
<Style ss:ID="Data">
<Font ss:FontName="Calibri" ss:Size="11" ss:Color="#000000"/>
</Style>
</Styles>
<Worksheet ss:Name="Laporan Kerusakan">
<Table>
<Row>
<Cell ss:StyleID="Header"><Data ss:Type="String">LAPORAN KERUSAKAN PERALATAN</Data></Cell>
</Row>
<Row>
<Cell ss:StyleID="SubHeader"><Data ss:Type="String">TVRI KALIMANTAN TENGAH</Data></Cell>
</Row>
<Row></Row>
<Row>
<Cell ss:StyleID="Section"><Data ss:Type="String">LOKASI KERUSAKAN</Data></Cell>
</Row>
<Row>
<Cell ss:StyleID="Label"><Data ss:Type="String">Lokasi Tx:</Data></Cell>
<Cell ss:StyleID="Data"><Data ss:Type="String">' . htmlspecialchars($case['location'] ?? 'N/A') . '</Data></Cell>
</Row>
<Row>
<Cell ss:StyleID="Label"><Data ss:Type="String">Tgl. Kerusakan:</Data></Cell>
<Cell ss:StyleID="Data"><Data ss:Type="String">' . htmlspecialchars($case['damage_date'] ?? 'N/A') . '</Data></Cell>
</Row>
<Row>
<Cell ss:StyleID="Label"><Data ss:Type="String">Peralatan:</Data></Cell>
<Cell ss:StyleID="Data"><Data ss:Type="String">' . htmlspecialchars($case['equipment_name'] ?? 'N/A') . '</Data></Cell>
</Row>
<Row>
<Cell ss:StyleID="Label"><Data ss:Type="String">Model:</Data></Cell>
<Cell ss:StyleID="Data"><Data ss:Type="String">' . htmlspecialchars($case['model'] ?? 'N/A') . '</Data></Cell>
</Row>
<Row>
<Cell ss:StyleID="Label"><Data ss:Type="String">S/N:</Data></Cell>
<Cell ss:StyleID="Data"><Data ss:Type="String">' . htmlspecialchars($case['serial_number'] ?? 'N/A') . '</Data></Cell>
</Row>
<Row></Row>
<Row>
<Cell ss:StyleID="Label"><Data ss:Type="String">Penjelasan rinci mengenai gangguan:</Data></Cell>
</Row>
<Row>
<Cell ss:StyleID="Data"><Data ss:Type="String">' . htmlspecialchars($case['description'] ?? 'N/A') . '</Data></Cell>
</Row>
<Row></Row>
<Row>
<Cell ss:StyleID="Label"><Data ss:Type="String">Kondisi Kerusakan:</Data></Cell>
<Cell ss:StyleID="Data"><Data ss:Type="String">' . htmlspecialchars(getDamageConditionText($case['damage_condition'] ?? 'light')) . '</Data></Cell>
</Row>
<Row></Row>
<Row>
<Cell ss:StyleID="Label"><Data ss:Type="String">ID Laporan:</Data></Cell>
<Cell ss:StyleID="Data"><Data ss:Type="String">' . htmlspecialchars($case['id_laporan'] ?? 'N/A') . '</Data></Cell>
</Row>
</Table>
</Worksheet>
</Workbook>';
    
    return $xml;
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
 * Generate filename for download
 */
function generateFilename($case) {
    $equipment = str_replace(' ', '_', $case['equipment_name'] ?? 'Peralatan');
    $location = str_replace(' ', '_', $case['location'] ?? 'Lokasi');
    $date = $case['damage_date'] ?? date('Y-m-d');
    
    return "Laporan_Kerusakan_{$equipment}_{$location}_{$date}.xlsx";
}

/**
 * Send error response
 */
function sendErrorResponse($message) {
    // Clear any previous output
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Set JSON response for AJAX
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $message
    ]);
    exit();
}
?>
