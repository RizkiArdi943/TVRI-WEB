<?php
/**
 * Controller untuk generate surat laporan kerusakan
 * Format: Excel dengan template yang sudah ada
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/browser_auth.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isLoggedIn()) {
    header('Location: index.php?page=login');
    exit();
}

// Get case ID from URL
$case_id = $_GET['id'] ?? null;

if (!$case_id) {
    die('ID laporan tidak ditemukan');
}

try {
    // Get case data from database
    $db = new Database();
    $case = $db->find('cases', $case_id);
    
    if (!$case) {
        die('Laporan tidak ditemukan');
    }
    
    // Generate Excel file
    generateLetterExcel($case);
    
} catch (Exception $e) {
    error_log('Error generating letter: ' . $e->getMessage());
    die('Terjadi kesalahan saat membuat surat');
}

/**
 * Generate Excel letter from case data using proper Excel format
 */
function generateLetterExcel($case) {
    try {
        // Generate filename
        $filename = generateFilename($case);
        
        // Clear any previous output
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        
        // Create proper Excel XML structure
        $excelContent = createExcelContent($case);
        
        // Output file
        echo $excelContent;
        exit();
        
    } catch (Exception $e) {
        error_log('Error generating Excel: ' . $e->getMessage());
        die('Terjadi kesalahan saat membuat file Excel');
    }
}

/**
 * Create proper Excel XML content
 */
function createExcelContent($case) {
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
<Style ss:ID="Header">
<Font ss:Bold="1" ss:Size="16"/>
<Alignment ss:Horizontal="Center"/>
</Style>
<Style ss:ID="SubHeader">
<Font ss:Bold="1" ss:Size="14"/>
<Alignment ss:Horizontal="Center"/>
</Style>
<Style ss:ID="Section">
<Font ss:Bold="1" ss:Size="12"/>
</Style>
<Style ss:ID="Label">
<Font ss:Bold="1"/>
</Style>
<Style ss:ID="Data">
<Font ss:Size="11"/>
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
?>
