<?php
require_once __DIR__ . '/../../controllers/member_cases.php';
require_once __DIR__ . '/../../config/database.php';

// Initialize member cases controller
$memberCasesController = new MemberCasesController();
$db = new Database();

$user_id = $_SESSION['user_id'];
$user_profile = $db->find('users', $user_id);

// Get user's cases for stats
$user_cases = $memberCasesController->getMemberCases($user_id);
$recent_cases = array_slice($user_cases, 0, 5);

// Calculate stats
$my_stats = [
    'total_cases' => count($user_cases),
    'today_cases' => count(array_filter($user_cases, function($c) { 
        return date('Y-m-d', strtotime($c['created_at'])) === date('Y-m-d'); 
    })),
    'month_cases' => count(array_filter($user_cases, function($c) { 
        return date('Y-m', strtotime($c['created_at'])) === date('Y-m'); 
    })),
    'pending_cases' => count(array_filter($user_cases, function($c) { 
        return $c['status'] === 'pending'; 
    })),
    'completed_cases' => count(array_filter($user_cases, function($c) { 
        return $c['status'] === 'completed'; 
    }))
];

// Helper function to format time ago
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

// Helper function to get status icon and color
function getStatusInfo($status) {
    switch ($status) {
        case 'pending':
            return ['icon' => 'fas fa-clock', 'color' => '#F59E0B', 'text' => 'Pending'];
        case 'in_progress':
            return ['icon' => 'fas fa-tools', 'color' => '#3B82F6', 'text' => 'Dalam Proses'];
        case 'completed':
            return ['icon' => 'fas fa-check-circle', 'color' => '#10B981', 'text' => 'Selesai'];
        case 'cancelled':
            return ['icon' => 'fas fa-times-circle', 'color' => '#EF4444', 'text' => 'Dibatalkan'];
        default:
            return ['icon' => 'fas fa-question-circle', 'color' => '#6B7280', 'text' => 'Tidak Diketahui'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Member - TVRI Kalimantan Tengah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .member-dashboard {
            background: #f8fafc;
            min-height: 100vh;
        }
        
        .member-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #60a5fa 100%);
            color: white;
            padding: 30px 0;
        }
        
        .member-header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .welcome-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .welcome-text h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .welcome-text p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .user-info-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            min-width: 200px;
        }
        
        .user-avatar {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
        }
        
        .user-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .user-dept {
            font-size: 14px;
            opacity: 0.8;
        }
        
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
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
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }
        
        .stat-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 14px;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .recent-cases {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #1e40af;
        }
        
        .view-all-btn {
            color: #3b82f6;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .view-all-btn:hover {
            color: #1e3a8a;
        }
        
        .case-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .case-item:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
        }
        
        .case-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }
        
        .case-content {
            flex: 1;
        }
        
        .case-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
        }
        
        .case-meta {
            font-size: 12px;
            color: #6b7280;
        }
        
        .case-status {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 4px;
            color: white;
            font-weight: 500;
        }
        
        .quick-actions {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            text-decoration: none;
            color: #374151;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            border-color: #3b82f6;
            background: #f8fafc;
            transform: translateX(5px);
        }
        
        .action-icon {
            width: 40px;
            height: 40px;
            background: #3b82f6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }
        
        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .welcome-section {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="member-dashboard">
        <!-- Header -->
        <div class="member-header">
            <div class="member-header-content">
                <div class="welcome-section">
                    <div class="welcome-text">
                        <h1>Selamat Datang, <?php echo htmlspecialchars($user_profile['full_name']); ?>!</h1>
                        <p>Dashboard Member - TVRI Kalimantan Tengah</p>
                    </div>
                    
                    <div class="user-info-card">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="user-name"><?php echo htmlspecialchars($user_profile['full_name']); ?></div>
                        <div class="user-dept"><?php echo htmlspecialchars($user_profile['department'] ?? 'Tidak ada departemen'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: #3b82f6;">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div>
                            <div class="stat-number"><?php echo $my_stats['total_cases']; ?></div>
                            <div class="stat-label">Total Laporan</div>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: #10b981;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <div class="stat-number"><?php echo $my_stats['completed_cases']; ?></div>
                            <div class="stat-label">Laporan Selesai</div>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: #f59e0b;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <div class="stat-number"><?php echo $my_stats['pending_cases']; ?></div>
                            <div class="stat-label">Laporan Pending</div>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: #8b5cf6;">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div>
                            <div class="stat-number"><?php echo $my_stats['today_cases']; ?></div>
                            <div class="stat-label">Laporan Hari Ini</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Recent Cases -->
                <div class="recent-cases">
                    <div class="section-header">
                        <h3 class="section-title">Laporan Terbaru</h3>
                        <a href="index.php?page=member/cases" class="view-all-btn">Lihat Semua</a>
                    </div>
                    
                    <?php if (!empty($recent_cases)): ?>
                        <?php foreach ($recent_cases as $case): ?>
                            <div class="case-item">
                                <div class="case-icon" style="background-color: <?php echo $case['category_color']; ?>;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="case-content">
                                    <div class="case-title"><?php echo htmlspecialchars($case['title']); ?></div>
                                    <div class="case-meta">
                                        <?php echo htmlspecialchars($case['category_name']); ?> • 
                                        <?php echo htmlspecialchars($case['location']); ?> • 
                                        <?php echo timeAgo($case['created_at']); ?>
                                    </div>
                                </div>
                                <?php 
                                $status_info = getStatusInfo($case['status']);
                                ?>
                                <div class="case-status" style="background-color: <?php echo $status_info['color']; ?>;">
                                    <?php echo $status_info['text']; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="case-item">
                            <div class="case-icon" style="background-color: #6b7280;">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="case-content">
                                <div class="case-title">Belum ada laporan</div>
                                <div class="case-meta">Mulai dengan membuat laporan baru</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Quick Actions -->
                <div class="quick-actions">
                    <div class="section-header">
                        <h3 class="section-title">Aksi Cepat</h3>
                    </div>
                    
                    <a href="index.php?page=member/cases/create" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div>
                            <div style="font-weight: 600;">Buat Laporan Baru</div>
                            <div style="font-size: 12px; color: #6b7280;">Laporkan masalah baru</div>
                        </div>
                    </a>
                    
                    <a href="index.php?page=member/cases" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <div>
                            <div style="font-weight: 600;">Lihat Laporan Saya</div>
                            <div style="font-size: 12px; color: #6b7280;">Kelola semua laporan</div>
                        </div>
                    </a>
                    
                    <a href="index.php?page=member/profile" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <div>
                            <div style="font-weight: 600;">Edit Profil</div>
                            <div style="font-size: 12px; color: #6b7280;">Update informasi pribadi</div>
                        </div>
                    </a>
                    
                    <a href="index.php?page=logout" class="action-btn">
                        <div class="action-icon" style="background: #ef4444;">
                            <i class="fas fa-sign-out-alt"></i>
                        </div>
                        <div>
                            <div style="font-weight: 600;">Keluar</div>
                            <div style="font-size: 12px; color: #6b7280;">Logout dari sistem</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
