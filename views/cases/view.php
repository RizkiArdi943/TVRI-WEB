<?php
require_once __DIR__ . '/../../config/database.php';

$id = $_GET['id'] ?? 0;

if (!$id) {
    header('Location: index.php?page=cases');
    exit();
}

// Get case details
$case = $db->find('cases', $id);

if (!$case) {
    header('Location: index.php?page=cases');
    exit();
}

// Get related data
$category = $db->find('categories', $case['category_id']);
$reporter = $db->find('users', $case['reported_by']);
$assigned = $case['assigned_to'] ? $db->find('users', $case['assigned_to']) : null;

$statusLabels = [
    'pending' => 'Menunggu',
    'in_progress' => 'Sedang Dikerjakan',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];

$priorityLabels = [
    'low' => 'Rendah',
    'medium' => 'Sedang',
    'high' => 'Tinggi'
];
?>

<div class="case-detail-page">
    <div class="page-header">
        <h1>Detail Laporan</h1>
        <div class="header-actions">
            <a href="index.php?page=cases/edit&id=<?php echo $case['id']; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i>
                Edit
            </a>
            <a href="index.php?page=cases" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>

    <div class="case-detail-card">
        <div class="case-header">
            <div class="case-title">
                <h2><?php echo htmlspecialchars($case['title']); ?></h2>
                <div class="case-badges">
                    <span class="case-category" style="background-color: <?php echo $category['color'] ?? '#3B82F6'; ?>">
                        <?php echo htmlspecialchars($category['name'] ?? 'Unknown'); ?>
                    </span>
                    <span class="case-status status-<?php echo $case['status']; ?>">
                        <?php echo $statusLabels[$case['status']] ?? $case['status']; ?>
                    </span>
                    <span class="case-priority priority-<?php echo $case['priority']; ?>">
                        <?php echo $priorityLabels[$case['priority']] ?? $case['priority']; ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="case-content">
            <?php if (!empty($case['image_path'])): ?>
            <div class="case-image" style="margin-bottom:16px;">
                <img src="<?php echo htmlspecialchars($case['image_path']); ?>" alt="Lampiran Laporan" style="max-width:100%; border-radius:8px;" />
            </div>
            <?php endif; ?>
            <div class="case-section">
                <h3>Deskripsi</h3>
                <p><?php echo nl2br(htmlspecialchars($case['description'])); ?></p>
            </div>

            <div class="case-info-grid">
                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-map-marker-alt"></i>
                        Lokasi
                    </div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($case['location']); ?>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-user"></i>
                        Pelapor
                    </div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($reporter['full_name'] ?? 'Unknown'); ?>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-user-tie"></i>
                        Ditugaskan Kepada
                    </div>
                    <div class="info-value">
                        <?php echo $assigned ? htmlspecialchars($assigned['full_name']) : '-'; ?>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-calendar"></i>
                        Tanggal Dibuat
                    </div>
                    <div class="info-value">
                        <?php echo date('d/m/Y H:i', strtotime($case['created_at'])); ?>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-clock"></i>
                        Terakhir Diupdate
                    </div>
                    <div class="info-value">
                        <?php echo date('d/m/Y H:i', strtotime($case['updated_at'])); ?>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-hashtag"></i>
                        ID Laporan
                    </div>
                    <div class="info-value">
                        #<?php echo str_pad($case['id'], 4, '0', STR_PAD_LEFT); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="case-actions">
            <a href="index.php?page=cases/edit&id=<?php echo $case['id']; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i>
                Edit Laporan
            </a>
            <button onclick="deleteCase(<?php echo $case['id']; ?>)" class="btn btn-danger">
                <i class="fas fa-trash"></i>
                Hapus Laporan
            </button>
        </div>
    </div>
</div>

<script>
function deleteCase(id) {
    if (confirm('Apakah Anda yakin ingin menghapus laporan ini?')) {
        window.location.href = `index.php?page=cases/delete&id=${id}`;
    }
}
</script> 