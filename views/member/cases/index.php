<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/browser_auth.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    header('Location: index.php?page=login');
    exit();
}

// Get user's cases
$user_id = $_SESSION['user_id'];
$cases = $db->findAll('cases', ['reported_by' => $user_id]);

// Get categories for filtering
$categories = $db->findAll('categories');

// Add category and status info to each case
foreach ($cases as $index => $case) {
    $category = $db->find('categories', $case['category_id']);
    $cases[$index]['category_name'] = $category['name'] ?? 'Unknown';
    $cases[$index]['category_color'] = $category['color'] ?? '#3B82F6';
}

// Helper function for time ago
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    $time = ($time < 1) ? 1 : $time;
    $tokens = array(
        31536000 => 'tahun',
        2592000 => 'bulan',
        604800 => 'minggu',
        86400 => 'hari',
        3600 => 'jam',
        60 => 'menit',
        1 => 'detik'
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits . ' ' . $text . ' yang lalu';
    }
}

// Helper function for status info
function getStatusInfo($status) {
    switch ($status) {
        case 'pending':
            return ['text' => 'Pending', 'color' => '#f59e0b', 'icon' => 'fas fa-clock'];
        case 'in_progress':
            return ['text' => 'Dalam Proses', 'color' => '#3b82f6', 'icon' => 'fas fa-tools'];
        case 'completed':
            return ['text' => 'Selesai', 'color' => '#10b981', 'icon' => 'fas fa-check-circle'];
        case 'cancelled':
            return ['text' => 'Dibatalkan', 'color' => '#ef4444', 'icon' => 'fas fa-times-circle'];
        default:
            return ['text' => 'Unknown', 'color' => '#6b7280', 'icon' => 'fas fa-question-circle'];
    }
}

// Helper function for priority info
function getPriorityInfo($priority) {
    switch ($priority) {
        case 'low':
            return ['text' => 'Rendah', 'color' => '#10b981'];
        case 'medium':
            return ['text' => 'Sedang', 'color' => '#f59e0b'];
        case 'high':
            return ['text' => 'Tinggi', 'color' => '#ef4444'];
        default:
            return ['text' => 'Sedang', 'color' => '#f59e0b'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Saya - TVRI Kalteng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .member-header {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .member-header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .member-header p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .back-btn {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            text-decoration: none;
            font-size: 18px;
            padding: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-50%) scale(1.1);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
        }
        
        .page-subtitle {
            color: #6b7280;
            font-size: 16px;
        }
        
        .create-btn {
            background: #3b82f6;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .create-btn:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #3b82f6;
            margin-bottom: 8px;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 14px;
        }
        
        .cases-grid {
            display: grid;
            gap: 20px;
        }
        
        .case-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .case-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .case-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            gap: 15px;
        }
        
        .case-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
        }
        
        .case-meta {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            color: #6b7280;
        }
        
        .case-description {
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .case-badges {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }
        
        .badge-category {
            background: #3b82f6;
        }
        
        .badge-priority {
            background: #f59e0b;
        }
        
        .badge-status {
            background: #10b981;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
            color: #d1d5db;
        }
        
        .empty-state h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #374151;
        }
        
        .empty-state p {
            margin-bottom: 30px;
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .case-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .case-meta {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="member-header">
        <a href="index.php?page=dashboard" class="back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1>Laporan Saya</h1>
        <p>Kelola semua laporan yang Anda buat</p>
    </div>
    
    <div class="container">
        <div class="page-header">
            <div>
                <h2 class="page-title">Daftar Laporan</h2>
                <p class="page-subtitle">Total <?php echo count($cases); ?> laporan</p>
            </div>
            <a href="index.php?page=member/cases/create" class="create-btn">
                <i class="fas fa-plus"></i>
                Buat Laporan Baru
            </a>
        </div>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($cases); ?></div>
                <div class="stat-label">Total Laporan</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($cases, function($c) { return $c['status'] === 'pending'; })); ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($cases, function($c) { return $c['status'] === 'in_progress'; })); ?></div>
                <div class="stat-label">Dalam Proses</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($cases, function($c) { return $c['status'] === 'completed'; })); ?></div>
                <div class="stat-label">Selesai</div>
            </div>
        </div>
        
        <!-- Cases List -->
        <div class="cases-grid">
            <?php if (!empty($cases)): ?>
                <?php foreach ($cases as $case): ?>
                    <div class="case-card">
                        <div class="case-header">
                            <div style="flex: 1;">
                                <div class="case-title"><?php echo htmlspecialchars($case['title']); ?></div>
                                <div class="case-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo timeAgo($case['created_at']); ?>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($case['location']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="case-badges">
                                <?php 
                                $status_info = getStatusInfo($case['status']);
                                $priority_info = getPriorityInfo($case['priority']);
                                ?>
                                <div class="badge badge-status" style="background-color: <?php echo $status_info['color']; ?>;">
                                    <i class="<?php echo $status_info['icon']; ?>"></i>
                                    <?php echo $status_info['text']; ?>
                                </div>
                                <div class="badge badge-priority" style="background-color: <?php echo $priority_info['color']; ?>;">
                                    <?php echo $priority_info['text']; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="case-description">
                            <?php echo nl2br(htmlspecialchars(substr($case['description'], 0, 200))); ?>
                            <?php if (strlen($case['description']) > 200): ?>
                                ...
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($case['image_path'])): ?>
                            <div class="case-image">
                                <img src="<?php echo htmlspecialchars($case['image_path']); ?>" 
                                     alt="Gambar laporan" 
                                     style="max-width: 200px; max-height: 150px; border-radius: 8px; margin-top: 10px;">
                            </div>
                        <?php endif; ?>
                        
                        <div class="case-badges">
                            <div class="badge badge-category">
                                <i class="fas fa-tag"></i>
                                <?php echo htmlspecialchars($case['category_name']); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>Belum ada laporan</h3>
                    <p>Anda belum membuat laporan apapun. Mulai dengan membuat laporan baru.</p>
                    <a href="index.php?page=member/cases/create" class="create-btn">
                        <i class="fas fa-plus"></i>
                        Buat Laporan Pertama
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
