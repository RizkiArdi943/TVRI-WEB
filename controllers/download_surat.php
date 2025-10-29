
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
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

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
        
        // Log untuk debug
        error_log('Generating Excel for case ID: ' . ($case['id'] ?? 'unknown'));
        error_log('Case title: ' . ($case['title'] ?? 'unknown'));
        
        // Generate filename
        $filename = generateFilename($case, 'xlsx');
        error_log('Generated filename: ' . $filename);
        
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
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
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
        
        // Handle image insertion if available
        if (!empty($case['image_path'])) {
            $imageInserted = insertImageToExcel($worksheet, $case['image_path']);
            
            // If image insertion failed, add text fallback
            if (!$imageInserted) {
                $imageText = 'Gambar tersedia: ' . basename($case['image_path']) . ' (tidak dapat disisipkan)';
                $worksheet->setCellValue('B26', $imageText);
            } else {
                // Clear B26 cell since image is successfully inserted at B30
                $worksheet->setCellValue('B26', '');
            }
        } else {
            $worksheet->setCellValue('B26', 'Tidak ada gambar');
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
 * Insert image to Excel worksheet
 */
function insertImageToExcel($worksheet, $imagePath) {
    try {
        // Determine actual image path
        $actualImagePath = null;
        $tempFile = null;
        
        // Check if it's a local file first
        $localPath = __DIR__ . '/../uploads/' . basename($imagePath);
        if (file_exists($localPath)) {
            $actualImagePath = $localPath;
            error_log('Using local image: ' . $actualImagePath);
        } else {
            // Check if it's a Vercel Blob URL or any HTTP URL
            if (strpos($imagePath, 'http') === 0) {
                // Download image from URL to temporary file
                $tempFile = tempnam(sys_get_temp_dir(), 'excel_img_');
                $imageContent = file_get_contents($imagePath);
                if ($imageContent !== false) {
                    file_put_contents($tempFile, $imageContent);
                    $actualImagePath = $tempFile;
                    error_log('Downloaded image from URL: ' . $imagePath);
                } else {
                    error_log('Failed to download image from URL: ' . $imagePath);
                    return false;
                }
            } else {
                // Try direct path
                if (file_exists($imagePath)) {
                    $actualImagePath = $imagePath;
                } else {
                    error_log('Image not found: ' . $imagePath);
                    return false;
                }
            }
        }
        
        if (!$actualImagePath || !file_exists($actualImagePath)) {
            error_log('Image path is invalid or file does not exist: ' . $actualImagePath);
            return false;
        }
        
        // Create drawing object
        $drawing = new Drawing();
        $drawing->setName('Case Image');
        $drawing->setDescription('Gambar laporan kerusakan');
        $drawing->setPath($actualImagePath);
        
        // Set position (B30 area for image - below the text area)
        $drawing->setCoordinates('B30');
        $drawing->setOffsetX(0);
        $drawing->setOffsetY(0);
        
        // Set size (adjust as needed - smaller to fit better)
        $drawing->setWidth(250);
        $drawing->setHeight(150);
        
        // Add to worksheet using the correct method
        $worksheet->getDrawingCollection()->append($drawing);
        
        // Clean up temp file if created
        if ($tempFile && file_exists($tempFile)) {
            unlink($tempFile);
        }
        
        error_log('Image successfully inserted into Excel');
        return true;
        
    } catch (Exception $e) {
        error_log('Error inserting image: ' . $e->getMessage());
        return false;
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
 * B26 = Gambar (akan disisipkan sebagai gambar, bukan teks)
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
