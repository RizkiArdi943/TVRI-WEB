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
                <?php 
                // Use upload handler to get correct URL
                require_once __DIR__ . '/../../config/upload_simple.php';
                $uploadHandler = new SimpleVercelBlobUploadHandler();
                $imageUrl = $uploadHandler->getFileUrl($case['image_path']);
                ?>
                <?php if ($imageUrl): ?>
                    <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="Lampiran Laporan" style="max-width:100%; height:auto; border-radius:8px; display:block; margin:0 auto; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';" />
                <?php endif; ?>
                <div style="<?php echo $imageUrl ? 'display:none;' : 'display:block;'; ?> background:#f3f4f6; padding:40px; text-align:center; border-radius:8px; color:#6b7280;">
                    <i class="fas fa-image" style="font-size:48px; margin-bottom:16px;"></i><br>
                    <h4>Gambar tidak dapat dimuat</h4>
                    <p>File gambar mungkin telah dihapus atau tidak tersedia</p>
                </div>
            </div>
            <?php endif; ?>
            <div class="case-section">
                <h3>Deskripsi</h3>
                <p><?php echo nl2br(htmlspecialchars($case['description'])); ?></p>
            </div>

            <div class="case-section">
                <h3>Informasi Peralatan</h3>
                <div class="equipment-info">
                    <div class="info-row">
                        <strong>Nama Peralatan:</strong> <?php echo htmlspecialchars($case['equipment_name'] ?? 'Tidak ada data'); ?>
                    </div>
                    <div class="info-row">
                        <strong>Model:</strong> <?php echo htmlspecialchars($case['model'] ?? 'Tidak ada data'); ?>
                    </div>
                    <div class="info-row">
                        <strong>S/N:</strong> <?php echo htmlspecialchars($case['serial_number'] ?? 'Tidak ada data'); ?>
                    </div>
                    <div class="info-row">
                        <strong>Tanggal Kerusakan:</strong> <?php echo !empty($case['damage_date']) ? date('d/m/Y', strtotime($case['damage_date'])) : 'Tidak ada data'; ?>
                    </div>
                    <div class="info-row">
                        <strong>Kondisi Kerusakan:</strong> 
                        <?php 
                        $conditionLabels = [
                            'light' => 'Rusak Ringan',
                            'moderate' => 'Rusak Sedang', 
                            'severe' => 'Rusak Berat'
                        ];
                        echo $conditionLabels[$case['damage_condition']] ?? 'Tidak ada data';
                        ?>
                    </div>
                </div>
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