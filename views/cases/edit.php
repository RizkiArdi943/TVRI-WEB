<?php
require_once __DIR__ . '/../../config/database.php';

$id = $_GET['id'] ?? 0;
$success = '';
$error = '';

if (!$id) {
    header('Location: index.php?page=cases');
    exit();
}

// Get categories
$categories = $db->findAll('categories');

// Get case data
$case = $db->find('cases', $id);

if (!$case) {
    header('Location: index.php?page=cases');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $location = $_POST['location'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $priority = $_POST['priority'] ?? 'medium';
    $status = $_POST['status'] ?? 'pending';
    $imagePath = $case['image_path'] ?? null;
    
    if (empty($title) || empty($description) || empty($location) || empty($category_id)) {
        $error = 'Semua field wajib diisi!';
    } else {
        // Handle image upload (optional)
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
                    if (!$mimeType && class_exists('finfo')) {
                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                        $mimeType = $finfo->file($_FILES['image']['tmp_name']);
                    }
                    if (!$mimeType || !isset($allowed[$mimeType])) {
                        $error = 'Format gambar harus JPG/PNG/WEBP';
                    } else {
                        if (!is_dir('uploads')) {
                            mkdir('uploads', 0755, true);
                        }
                        $ext = $allowed[$mimeType];
                        $safeName = bin2hex(random_bytes(8)) . '.' . $ext;
                        $targetPath = 'uploads/' . $safeName;
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                            $imagePath = $targetPath;
                        } else {
                            $error = 'Gagal mengunggah gambar';
                        }
                    }
                }
            } elseif ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE) {
                $error = 'Ukuran gambar melebihi batas server';
            } else {
                $error = 'Kesalahan unggah file (kode: ' . $uploadError . ')';
            }
        }

        try {
            $updateData = [
                'title' => $title,
                'description' => $description,
                'location' => $location,
                'category_id' => (int)$category_id,
                'priority' => $priority,
                'status' => $status,
                'image_path' => $imagePath
            ];
            
            $db->update('cases', $id, $updateData);
            
            $success = 'Laporan berhasil diupdate!';
            
            // Refresh case data
            $case = $db->find('cases', $id);
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}
?>

<div class="edit-case-page">
    <div class="page-header">
        <h1>Edit Laporan</h1>
        <div class="header-actions">
            <a href="index.php?page=cases/view&id=<?php echo $case['id']; ?>" class="btn btn-outline">
                <i class="fas fa-eye"></i>
                Detail
            </a>
            <a href="index.php?page=cases" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>

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
        <form method="POST" enctype="multipart/form-data" class="case-form">
            <div class="form-group">
                <label for="title">Judul Laporan *</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($case['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Deskripsi *</label>
                <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($case['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="image">Gambar (opsional)</label>
                <?php if (!empty($case['image_path'])): ?>
                    <div class="image-preview">
                        <img src="<?php echo htmlspecialchars($case['image_path']); ?>" alt="Lampiran" style="max-width: 200px; display:block; margin-bottom:8px;" />
                    </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" accept="image/*">
                <small class="form-hint">Format: JPG, PNG, WEBP. Maksimal 5MB.</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="location">Lokasi *</label>
                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($case['location']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="category_id">Kategori *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo $case['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="priority">Prioritas</label>
                    <select id="priority" name="priority">
                        <option value="low" <?php echo $case['priority'] === 'low' ? 'selected' : ''; ?>>Rendah</option>
                        <option value="medium" <?php echo $case['priority'] === 'medium' ? 'selected' : ''; ?>>Sedang</option>
                        <option value="high" <?php echo $case['priority'] === 'high' ? 'selected' : ''; ?>>Tinggi</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="pending" <?php echo $case['status'] === 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                        <option value="in_progress" <?php echo $case['status'] === 'in_progress' ? 'selected' : ''; ?>>Sedang Dikerjakan</option>
                        <option value="completed" <?php echo $case['status'] === 'completed' ? 'selected' : ''; ?>>Selesai</option>
                        <option value="cancelled" <?php echo $case['status'] === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
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