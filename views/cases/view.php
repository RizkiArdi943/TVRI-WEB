<?php
// Set timezone to WIB (Waktu Indonesia Barat)
date_default_timezone_set('Asia/Jakarta');

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
                        <span id="createdTime" data-timestamp="<?php echo $case['created_at']; ?>">
                            <?php echo date('d/m/Y H:i', strtotime($case['created_at'])); ?>
                        </span>
                        <small class="timezone-info">WIB</small>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-clock"></i>
                        Terakhir Diupdate
                    </div>
                    <div class="info-value">
                        <span id="updatedTime" data-timestamp="<?php echo $case['updated_at']; ?>">
                            <?php echo date('d/m/Y H:i', strtotime($case['updated_at'])); ?>
                        </span>
                        <small class="timezone-info">WIB</small>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-hashtag"></i>
                        ID Laporan
                    </div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($case['id_laporan'] ?? 'N/A'); ?>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">
                        <i class="fas fa-clock"></i>
                        Waktu Saat Ini
                    </div>
                    <div class="info-value">
                        <span id="currentTime">
                            <?php echo date('d/m/Y H:i:s'); ?>
                        </span>
                        <small class="timezone-info">WIB</small>
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

<style>
.timezone-info {
    color: #6b7280;
    font-size: 11px;
    margin-left: 5px;
    background: #f3f4f6;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 500;
}

#currentTime {
    font-weight: 600;
    color: #059669;
}

.info-value {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 5px;
}
</style>

<script>
// Real time clock function
function updateCurrentTime() {
    const now = new Date();
    const options = {
        timeZone: 'Asia/Jakarta',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    };
    
    const formatter = new Intl.DateTimeFormat('id-ID', options);
    const parts = formatter.formatToParts(now);
    
    const day = parts.find(part => part.type === 'day').value;
    const month = parts.find(part => part.type === 'month').value;
    const year = parts.find(part => part.type === 'year').value;
    const hour = parts.find(part => part.type === 'hour').value;
    const minute = parts.find(part => part.type === 'minute').value;
    const second = parts.find(part => part.type === 'second').value;
    
    const timeString = `${day}/${month}/${year} ${hour}:${minute}:${second}`;
    
    const currentTimeElement = document.getElementById('currentTime');
    if (currentTimeElement) {
        currentTimeElement.textContent = timeString;
    }
}

// Format timestamp to WIB
function formatTimestampToWIB(timestamp) {
    const date = new Date(timestamp);
    const options = {
        timeZone: 'Asia/Jakarta',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    };
    
    const formatter = new Intl.DateTimeFormat('id-ID', options);
    const parts = formatter.formatToParts(date);
    
    const day = parts.find(part => part.type === 'day').value;
    const month = parts.find(part => part.type === 'month').value;
    const year = parts.find(part => part.type === 'year').value;
    const hour = parts.find(part => part.type === 'hour').value;
    const minute = parts.find(part => part.type === 'minute').value;
    
    return `${day}/${month}/${year} ${hour}:${minute}`;
}

// Update timestamps on page load
function updateTimestamps() {
    const createdTimeElement = document.getElementById('createdTime');
    const updatedTimeElement = document.getElementById('updatedTime');
    
    if (createdTimeElement) {
        const timestamp = createdTimeElement.getAttribute('data-timestamp');
        if (timestamp) {
            createdTimeElement.textContent = formatTimestampToWIB(timestamp);
        }
    }
    
    if (updatedTimeElement) {
        const timestamp = updatedTimeElement.getAttribute('data-timestamp');
        if (timestamp) {
            updatedTimeElement.textContent = formatTimestampToWIB(timestamp);
        }
    }
}

// Initialize real time clock
document.addEventListener('DOMContentLoaded', function() {
    // Update current time immediately
    updateCurrentTime();
    
    // Update timestamps to WIB
    updateTimestamps();
    
    // Update current time every second
    setInterval(updateCurrentTime, 1000);
});

function deleteCase(id) {
    if (confirm('Apakah Anda yakin ingin menghapus laporan ini?')) {
        window.location.href = `index.php?page=cases/delete&id=${id}`;
    }
}
</script> 