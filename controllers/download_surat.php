<?php
/**
 * Controller untuk download surat laporan kerusakan
 * Menggunakan PhpSpreadsheet untuk kompatibilitas Vercel
 */

// Set encoding UTF-8 tanpa BOM
while (ob_get_level()) {
    ob_end_clean();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/browser_auth.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
    // Get case data from database with user information
    $db = new Database();
    
    // Query untuk mendapatkan case dengan informasi user
    $query = "SELECT c.*, u.full_name as reporter_name
              FROM cases c
              LEFT JOIN users u ON c.reported_by = u.id
              WHERE c.id = ?";
    
    $result = $db->query($query, [$case_id]);
    
    if (empty($result)) {
        sendErrorResponse('Laporan tidak ditemukan');
    }
    
    $case = $result[0]; // Ambil data pertama dari hasil query
    
    // Generate Excel file
    generateSuratExcel($case);
    
} catch (Exception $e) {
    error_log('Error generating surat: ' . $e->getMessage());
    sendErrorResponse('Terjadi kesalahan saat membuat surat: ' . $e->getMessage());
}

/**
 * Generate Excel surat from case data menggunakan template Excel
 * Mapping data sesuai dengan struktur template yang ada
 */
function generateSuratExcel($case) {
    try {
        // Clear any previous output
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Generate filename
        $filename = generateFilename($case, 'xlsx');
        
        // Path to template
        $templatePath = __DIR__ . '/../templates/Template Laporan Kerusakan Peralatan.xlsx';
        
        if (!file_exists($templatePath)) {
            sendErrorResponse('Template Excel tidak ditemukan. Hubungi admin untuk memperbaiki file template.');
        }
        
        // Load template and create new Excel with mapped data
        $spreadsheet = createExcelFromTemplate($templatePath, $case);
        
        // Create writer
        $writer = new Xlsx($spreadsheet);
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        header('Access-Control-Expose-Headers: Content-Disposition');
        
        // Output Excel file
        try {
            // Create temporary file first to ensure validity
            $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
            $writer->save($tempFile);
            
            // Verify file is valid
            if (!file_exists($tempFile) || filesize($tempFile) === 0) {
                throw new Exception('Generated Excel file is invalid or empty');
            }
            
            // Read and output file
            $fileContent = file_get_contents($tempFile);
            echo $fileContent;
            
            // Clean up
            unlink($tempFile);
            
        } catch (Exception $e) {
            error_log('Error saving Excel: ' . $e->getMessage());
            sendErrorResponse('Terjadi kesalahan saat menyimpan file Excel: ' . $e->getMessage());
        }
        exit();
        
    } catch (Exception $e) {
        error_log('Error generating Excel: ' . $e->getMessage());
        sendErrorResponse('Terjadi kesalahan saat membuat file Excel: ' . $e->getMessage());
    }
}

/**
 * Create Excel from template dengan mapping data
 */
function createExcelFromTemplate($templatePath, $case) {
    try {
        // Load template
        $spreadsheet = IOFactory::load($templatePath);
        $worksheet = $spreadsheet->getActiveSheet();
        
        // Mapping data sesuai dengan struktur template
        $mappedData = mapCaseDataToTemplate($case);
        
        // Apply mapping to specific cells
        foreach ($mappedData as $cellCoordinate => $value) {
            try {
                $worksheet->setCellValue($cellCoordinate, $value);
            } catch (Exception $e) {
                error_log("Error setting cell $cellCoordinate: " . $e->getMessage());
                // Continue with other cells
            }
        }
        
        // Validate spreadsheet
        if (!$spreadsheet) {
            throw new Exception('Failed to create spreadsheet');
        }
        
        return $spreadsheet;
        
    } catch (Exception $e) {
        error_log('Error processing template: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * Map case data to template structure
 * Mapping sesuai dengan permintaan user:
 * E10 = lokasi
 * E11 = Tanggal Kerusakan
 * E12 = Nama Peralatan
 * E13 = Model
 * E14 = S/N
 * B18 = Deskripsi
 * B26 = Gambar
 * B56 = Pelapor
 */
function mapCaseDataToTemplate($case) {
    return [
        'E10' => $case['location'] ?? 'N/A',                    // lokasi
        'E11' => formatDate($case['damage_date'] ?? 'N/A'),    // Tanggal Kerusakan
        'E12' => $case['equipment_name'] ?? 'N/A',              // Nama Peralatan
        'E13' => $case['model'] ?? '-',                         // Model
        'E14' => $case['serial_number'] ?? '-',                // S/N
        'B18' => $case['description'] ?? 'N/A',                // Deskripsi
        'B26' => 'Gambar tidak dapat dimuat',                 // Gambar (placeholder)
        'B56' => $case['reporter_name'] ?? 'Administrator'     // Pelapor (nama user yang benar)
    ];
}

/**
 * Format date untuk display
 */
function formatDate($date) {
    if ($date === 'N/A' || empty($date)) {
        return 'N/A';
    }
    
    try {
        $dateObj = new DateTime($date);
        return $dateObj->format('d/m/Y');
    } catch (Exception $e) {
        return $date; // Return original if parsing fails
    }
}

/**
 * Generate filename for download berdasarkan judul laporan
 */
function generateFilename($case, $format = 'xlsx') {
    // Gunakan judul laporan (title) sebagai nama file utama
    $title = $case['title'] ?? 'Laporan_Kerusakan';
    $equipment = $case['equipment_name'] ?? 'Peralatan';
    $location = $case['location'] ?? 'Lokasi';
    $date = $case['damage_date'] ?? date('Y-m-d');
    
    // Clean filename untuk kompatibilitas - hapus karakter khusus dan spasi
    $filenamePattern = '/[^a-zA-Z0-9_-]/';
    $title = preg_replace($filenamePattern, '_', $title);
    $equipment = preg_replace($filenamePattern, '_', $equipment);
    $location = preg_replace($filenamePattern, '_', $location);
    
    // Hapus underscore berulang
    $title = preg_replace('/_+/', '_', $title);
    $equipment = preg_replace('/_+/', '_', $equipment);
    $location = preg_replace('/_+/', '_', $location);
    
    // Trim underscore di awal dan akhir
    $title = trim($title, '_');
    $equipment = trim($equipment, '_');
    $location = trim($location, '_');
    
    // Buat filename yang lebih deskriptif dengan judul laporan sebagai prioritas
    $filename = "{$title}_{$equipment}_{$location}_{$date}.{$format}";
    
    // Pastikan filename tidak terlalu panjang
    if (strlen($filename) > 100) {
        $filename = "{$title}_{$date}.{$format}";
    }
    
    return $filename;
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