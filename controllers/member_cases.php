<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/browser_auth.php';
require_once __DIR__ . '/../config/upload_simple.php';

// Initialize session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class MemberCasesController {
    private $db;
    private $uploadHandler;

    public function __construct() {
        $this->db = new Database();
        $this->uploadHandler = new SimpleVercelBlobUploadHandler();
    }

    /**
     * Handle case creation for members
     */
    public function create() {
        $success = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $equipment_name = trim($_POST['equipment_name'] ?? '');
            $model = trim($_POST['model'] ?? '');
            $serial_number = trim($_POST['serial_number'] ?? '');
            $damage_date = trim($_POST['damage_date'] ?? '');
            $location = trim($_POST['location'] ?? '');
            $damage_condition = $_POST['damage_condition'] ?? 'light';
            // Samakan default dengan admin
            $priority = 'medium';
            $category_id = 1;

            // Check session
            if (!isset($_SESSION['user_id'])) {
                $error = 'Sesi pengguna tidak valid. Silakan login ulang.';
                error_log('Session check failed: user_id not set');
            } elseif ($title === '' || $description === '' || $equipment_name === '' || $damage_date === '' || $location === '') {
                $error = 'Semua field wajib diisi!';
                error_log('Validation failed: title=' . $title . ', description=' . $description . ', equipment_name=' . $equipment_name . ', damage_date=' . $damage_date . ', location=' . $location);
            } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
                $error = 'Gambar wajib diupload!';
                error_log('Validation failed: No image uploaded');
            } else {
                error_log('Validation passed, proceeding with save...');

                // Handle image upload (required)
                $imagePath = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $uploadResult = $this->uploadHandler->uploadFile($_FILES['image']);
                    
                    if ($uploadResult['success']) {
                        $imagePath = $uploadResult['path'];
                        error_log('Image uploaded successfully: ' . $imagePath);
                    } else {
                        $error = $uploadResult['error'];
                        error_log('Upload failed: ' . $error);
                    }
                } else {
                    $error = 'Gambar wajib diupload!';
                    error_log('No image file provided');
                }

                if (empty($error)) {
                    // Prepare case data
                    $caseData = [
                        'title' => $title,
                        'description' => $description,
                        'equipment_name' => $equipment_name,
                        'model' => $model,
                        'serial_number' => $serial_number,
                        'damage_date' => $damage_date,
                        'location' => $location,
                        'damage_condition' => $damage_condition,
                        'category_id' => $category_id,
                        'status' => 'pending',
                        'priority' => $priority,
                        'reported_by' => (int)$_SESSION['user_id'],
                        'assigned_to' => null,
                        'image_path' => $imagePath,
                        'created_at' => date('Y-m-d H:i:s')
                    ];

                    error_log('Attempting to insert case: ' . json_encode($caseData));

                    try {
                        // Test database connection
                        $testConnection = $this->db->query('SELECT 1');
                        if (!$testConnection) {
                            throw new Exception('Database connection failed');
                        }

                        $result = $this->db->insert('cases', $caseData);

                        if ($result) {
                            error_log('Case inserted successfully with ID: ' . $result);
                            $_POST = []; // Clear form data
                            $success = 'Laporan berhasil ditambahkan dengan ID: ' . $result;
                            error_log('SUCCESS variable set: ' . $success);
                        } else {
                            $error = 'Gagal menyimpan laporan ke database. Silakan coba lagi.';
                            error_log('Database insert returned false. Check database configuration.');
                            error_log('Database insert result: ' . var_export($result, true));
                        }
                    } catch (Exception $e) {
                        $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
                        error_log('Exception during case insertion: ' . $e->getMessage());
                        error_log('Stack trace: ' . $e->getTraceAsString());
                    }
                }
            }
        }

        return ['success' => $success, 'error' => $error];
    }

    /**
     * Get member's cases
     */
    public function getMemberCases($user_id) {
        $query = "SELECT c.*, cat.name as category_name, cat.color as category_color
                  FROM cases c
                  LEFT JOIN categories cat ON c.category_id = cat.id
                  WHERE c.reported_by = ?
                  ORDER BY c.created_at DESC";

        return $this->db->query($query, [$user_id]);
    }

    /**
     * Get categories for dropdown
     */
    public function getCategories() {
        return $this->db->findAll('categories');
    }

    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($error) {
        switch ($error) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File terlalu besar (melebihi upload_max_filesize)';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File terlalu besar (melebihi MAX_FILE_SIZE)';
            case UPLOAD_ERR_PARTIAL:
                return 'File hanya terupload sebagian';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Tidak ada folder temporary';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Gagal menulis file ke disk';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload dihentikan oleh extension';
            default:
                return 'Error tidak diketahui';
        }
    }
}
?>
