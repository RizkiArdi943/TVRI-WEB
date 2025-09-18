<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Initialize session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class MemberCasesController {
    private $db;

    public function __construct() {
        $this->db = new Database();
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
            $location = trim($_POST['location'] ?? '');
            $priority = $_POST['priority'] ?? 'medium';
            $category_id = (int)($_POST['category_id'] ?? 1);

            // Check session
            if (!isset($_SESSION['user_id'])) {
                $error = 'Sesi pengguna tidak valid. Silakan login ulang.';
                error_log('Session check failed: user_id not set');
            } elseif ($title === '' || $description === '' || $location === '') {
                $error = 'Semua field wajib diisi!';
                error_log('Validation failed: title=' . $title . ', description=' . $description . ', location=' . $location);
            } else {
                error_log('Validation passed, proceeding with save...');

                // Handle image upload (optional)
                $imagePath = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $uploadError = $_FILES['image']['error'];

                    if ($uploadError === UPLOAD_ERR_OK) {
                        $maxSize = 5 * 1024 * 1024; // 5MB
                        if (($_FILES['image']['size'] ?? 0) > $maxSize) {
                            $error = 'Ukuran gambar maksimal 5MB';
                        } else {
                            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                            $mimeType = null;

                            if (function_exists('getimagesize')) {
                                $imgInfo = @getimagesize($_FILES['image']['tmp_name']);
                                $mimeType = $imgInfo['mime'] ?? null;
                            }

                            if (!$mimeType || !isset($allowed[$mimeType])) {
                                $error = 'Format gambar tidak didukung. Gunakan JPG, PNG, atau WebP.';
                            } else {
                                // Create uploads directory if not exists
                                $uploadDir = __DIR__ . '/../uploads/';
                                if (!is_dir($uploadDir)) {
                                    mkdir($uploadDir, 0755, true);
                                }

                                // Generate unique filename
                                $extension = $allowed[$mimeType];
                                $filename = uniqid('case_', true) . '.' . $extension;
                                $targetPath = $uploadDir . $filename;

                                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                                    $imagePath = $filename;
                                    error_log('Image uploaded successfully: ' . $filename);
                                } else {
                                    $error = 'Gagal mengupload gambar.';
                                    error_log('Failed to move uploaded file to: ' . $targetPath);
                                }
                            }
                        }
                    } elseif ($uploadError !== UPLOAD_ERR_NO_FILE) {
                        $error = 'Terjadi kesalahan saat upload gambar: ' . $this->getUploadErrorMessage($uploadError);
                    }
                }

                if (empty($error)) {
                    // Prepare case data
                    $caseData = [
                        'title' => $title,
                        'description' => $description,
                        'location' => $location,
                        'category_id' => $category_id,
                        'priority' => $priority,
                        'status' => 'pending',
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
