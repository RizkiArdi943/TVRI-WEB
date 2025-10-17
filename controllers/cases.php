<?php
// Set timezone to WIB (Waktu Indonesia Barat)
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/browser_auth.php';
require_once __DIR__ . '/../config/upload_simple.php';
require_once __DIR__ . '/../config/id_generator.php';

// Initialize session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class CasesController {
    private $db;
    private $uploadHandler;
    private $idGenerator;

    public function __construct() {
        $this->db = new Database();
        $this->uploadHandler = new SimpleVercelBlobUploadHandler();
        $this->idGenerator = new IDLaporanGenerator($this->db);
    }

    /**
     * Handle case creation
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

            // Check session
            if (!isset($_SESSION['user_id'])) {
                $error = 'Sesi pengguna tidak valid. Silakan login ulang.';
                error_log('Session check failed: user_id not set');
            } elseif ($title === '' || $description === '' || $equipment_name === '' || $damage_date === '' || $location === '') {
                $error = 'Semua field wajib diisi!';
                error_log('Validation failed: title=' . $title . ', description=' . $description . ', equipment_name=' . $equipment_name . ', model=' . $model . ', serial_number=' . $serial_number . ', damage_date=' . $damage_date . ', location=' . $location);
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
                    // Generate ID laporan
                    $idLaporan = $this->idGenerator->generateID($location);
                    
                    // Ensure ID is unique
                    $attempts = 0;
                    while (!$this->idGenerator->isUnique($idLaporan) && $attempts < 10) {
                        $attempts++;
                        // Add small delay and regenerate
                        usleep(1000); // 1ms delay
                        $idLaporan = $this->idGenerator->generateID($location);
                    }
                    
                    if ($attempts >= 10) {
                        $error = 'Gagal membuat ID laporan unik. Silakan coba lagi.';
                        error_log('Failed to generate unique ID after 10 attempts');
                    } else {
                        // Prepare case data
                        $caseData = [
                            'id_laporan' => $idLaporan,
                            'title' => $title,
                            'description' => $description,
                            'equipment_name' => $equipment_name,
                            'model' => $model,
                            'serial_number' => $serial_number,
                            'damage_date' => $damage_date,
                            'location' => $location,
                            'category_id' => 1, // Default category (Transmisi)
                            'status' => 'pending',
                            'priority' => 'medium', // Default priority
                            'reported_by' => (int)$_SESSION['user_id'],
                            'assigned_to' => null,
                            'image_path' => $imagePath
                        ];
                    }

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
                            $success = 'Laporan berhasil ditambahkan dengan ID: ' . $idLaporan;
                            error_log('SUCCESS variable set: ' . $success);
                            
                            // Redirect to clean form after successful submission
                            if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
                                // Only redirect for non-AJAX requests
                                header('Location: index.php?page=cases/create&success=' . urlencode($success));
                                exit();
                            }
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
     * Get all cases with filters
     */
    public function index($filters = []) {
        $query = "SELECT c.*, cat.name as category_name, cat.color as category_color,
                         u.full_name as reporter_name
                  FROM cases c
                  LEFT JOIN categories cat ON c.category_id = cat.id
                  LEFT JOIN users u ON c.reported_by = u.id
                  WHERE 1=1";

        $params = [];

        // Apply filters
        if (!empty($filters['status'])) {
            $query .= " AND c.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $query .= " AND c.priority = ?";
            $params[] = $filters['priority'];
        }

        if (!empty($filters['category_id'])) {
            $query .= " AND c.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (c.title LIKE ? OR c.description LIKE ? OR c.location LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'DESC';
        $allowedSortFields = ['created_at', 'title', 'status', 'priority', 'updated_at'];

        if (in_array($sortBy, $allowedSortFields)) {
            $query .= " ORDER BY c.{$sortBy} {$sortOrder}";
        } else {
            $query .= " ORDER BY c.created_at DESC";
        }

        return $this->db->query($query, $params);
    }

    /**
     * Get case by ID
     */
    public function show($id) {
        $query = "SELECT c.*, cat.name as category_name, cat.color as category_color,
                         u.full_name as reporter_name,
                         assignee.full_name as assignee_name
                  FROM cases c
                  LEFT JOIN categories cat ON c.category_id = cat.id
                  LEFT JOIN users u ON c.reported_by = u.id
                  LEFT JOIN users assignee ON c.assigned_to = assignee.id
                  WHERE c.id = ?";

        return $this->db->query($query, [$id]);
    }

    /**
     * Update case
     */
    public function update($id, $data) {
        return $this->db->update('cases', $data, $id);
    }

    /**
     * Delete case
     */
    public function delete($id) {
        return $this->db->delete('cases', $id);
    }

}
?>
