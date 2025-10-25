<?php
// Set timezone to WIB (Waktu Indonesia Barat)
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../../controllers/cases.php';
require_once __DIR__ . '/../../config/database.php';

// Initialize controller
$casesController = new CasesController();
$db = new Database();

// Get case ID from URL
$id = $_GET['id'] ?? 0;

if (!$id) {
    header('Location: index.php?page=cases');
    exit();
}

// Get case details
$caseResult = $casesController->show($id);
$case = $caseResult[0] ?? null;

if (!$case) {
    header('Location: index.php?page=cases');
    exit();
}

// Handle form submission
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
    $damage_condition = trim($_POST['damage_condition'] ?? 'light');

    // Validasi field wajib
    if ($title === '' || $description === '' || $equipment_name === '' || $damage_date === '' || $location === '') {
        $error = 'Semua field wajib diisi!';
    } else {
        // Prepare update data
        $updateData = [
            'title' => $title,
            'description' => $description,
            'equipment_name' => $equipment_name,
            'model' => $model,
            'serial_number' => $serial_number,
            'damage_date' => $damage_date,
            'location' => $location,
            'damage_condition' => $damage_condition,
        ];

        // Handle image upload (optional untuk edit)
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            require_once __DIR__ . '/../../config/upload_simple.php';
            $uploadHandler = new SimpleVercelBlobUploadHandler();
            $uploadResult = $uploadHandler->uploadFile($_FILES['image']);
            
            if ($uploadResult['success']) {
                $updateData['image_path'] = $uploadResult['path'];
            } else {
                $error = $uploadResult['error'];
            }
        }

        // Update jika tidak ada error upload
        if (empty($error)) {
            $result = $casesController->update($id, $updateData);

            if ($result) {
                $success = 'Laporan berhasil diupdate!';
                // Reload data case yang sudah diupdate
                $caseResult = $casesController->show($id);
                $case = $caseResult[0] ?? null;
            } else {
                $error = 'Gagal mengupdate laporan. Silakan coba lagi.';
            }
        }
    }
}

// Debug: Log request method
error_log('=== EDIT FORM DEBUG ===');
error_log('Request Method: ' . $_SERVER['REQUEST_METHOD']);
error_log('Case ID: ' . $id);
?>

<div class="edit-case-page">
    <div class="page-header">
        <h1>Edit Laporan</h1>
        <a href="index.php?page=cases/view&id=<?php echo $case['id']; ?>" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i>
            Kembali
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <!-- ID Laporan Info -->
        <div class="id-laporan-info">
            <div class="id-label">
                <i class="fas fa-hashtag"></i>
                ID Laporan
            </div>
            <div class="id-value">
                <?php echo htmlspecialchars($case['id_laporan'] ?? 'N/A'); ?>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data" class="case-form" data-no-js-validation="true">
            <div class="form-group">
                <label for="title">Judul Laporan *</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($case['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Deskripsi *</label>
                <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($case['description']); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="equipment_name">Nama Peralatan *</label>
                    <input type="text" id="equipment_name" name="equipment_name" value="<?php echo htmlspecialchars($case['equipment_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="model">Model</label>
                    <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($case['model'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="serial_number">S/N</label>
                    <input type="text" id="serial_number" name="serial_number" value="<?php echo htmlspecialchars($case['serial_number'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="damage_date">Tanggal Kerusakan *</label>
                    <input type="date" id="damage_date" name="damage_date" value="<?php echo htmlspecialchars($case['damage_date']); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="location">Lokasi Transmisi Digital *</label>
                    <select id="location" name="location" required>
                        <option value="">Pilih lokasi transmisi</option>
                        <option value="Transmisi Palangkaraya" <?php echo $case['location'] === 'Transmisi Palangkaraya' ? 'selected' : ''; ?>>Transmisi Palangkaraya</option>
                        <option value="Transmisi Sampit" <?php echo $case['location'] === 'Transmisi Sampit' ? 'selected' : ''; ?>>Transmisi Sampit</option>
                        <option value="Transmisi Pangkalanbun" <?php echo $case['location'] === 'Transmisi Pangkalanbun' ? 'selected' : ''; ?>>Transmisi Pangkalanbun</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="damage_condition">Kondisi Kerusakan *</label>
                    <select id="damage_condition" name="damage_condition" required>
                        <option value="">Pilih kondisi kerusakan</option>
                        <option value="light" <?php echo ($case['damage_condition'] ?? '') === 'light' ? 'selected' : ''; ?>>Rusak Ringan</option>
                        <option value="moderate" <?php echo ($case['damage_condition'] ?? '') === 'moderate' ? 'selected' : ''; ?>>Rusak Sedang</option>
                        <option value="severe" <?php echo ($case['damage_condition'] ?? '') === 'severe' ? 'selected' : ''; ?>>Rusak Berat</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="image">Gambar (kosongkan jika tidak ingin mengubah)</label>
                <?php if (!empty($case['image_path'])): ?>
                    <div class="current-image" style="margin-bottom: 10px;">
                        <?php
                        require_once __DIR__ . '/../../config/upload_simple.php';
                        $uploadHandler = new SimpleVercelBlobUploadHandler();
                        $imageUrl = $uploadHandler->getFileUrl($case['image_path']);
                        ?>
                        <?php if ($imageUrl): ?>
                            <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="Gambar saat ini" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e5e7eb;">
                        <?php endif; ?>
                        <p style="font-size: 12px; color: #6b7280; margin-top: 5px;">Gambar saat ini</p>
                    </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" accept="image/*">
                <small class="form-hint">Format: JPG, PNG, WEBP. Maksimal 5MB.</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" data-no-loading="true">
                    <i class="fas fa-save"></i>
                    Update Laporan
                </button>
                <a href="index.php?page=cases/view&id=<?php echo $case['id']; ?>" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<style>
/* ID Laporan Info Styles */
.id-laporan-info {
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
    border: 2px solid #3b82f6;
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);
}

.id-label {
    font-size: 14px;
    font-weight: 600;
    color: #1e40af;
    display: flex;
    align-items: center;
    gap: 8px;
}

.id-label i {
    font-size: 16px;
}

.id-value {
    font-size: 18px;
    font-weight: 700;
    color: #1e40af;
    font-family: 'Courier New', monospace;
    background: white;
    padding: 6px 16px;
    border-radius: 6px;
    border: 1px solid #93c5fd;
}

/* Form styles */
.form-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #374151;
    font-size: 14px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-hint {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: #6b7280;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-outline {
    background: white;
    color: #6b7280;
    border: 2px solid #e5e7eb;
}

.btn-outline:hover {
    background: #f9fafb;
    border-color: #d1d5db;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.page-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .page-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
}
</style>

