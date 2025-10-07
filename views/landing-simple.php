<?php
require_once __DIR__ . '/../controllers/dashboard_stats.php';

// Initialize dashboard stats
$stats = new DashboardStats();
$dashboardData = $stats->getAllStats();

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

// Helper function to get status icon
function getStatusIcon($status) {
    switch ($status) {
        case 'pending':
            return 'fas fa-clock';
        case 'in_progress':
            return 'fas fa-tools';
        case 'completed':
            return 'fas fa-check-circle';
        case 'cancelled':
            return 'fas fa-times-circle';
        default:
            return 'fas fa-question-circle';
    }
}

// Helper function to get status color
function getStatusColor($status) {
    switch ($status) {
        case 'pending':
            return '#F59E0B';
        case 'in_progress':
            return '#3B82F6';
        case 'completed':
            return '#10B981';
        case 'cancelled':
            return '#EF4444';
        default:
            return '#6B7280';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TVRI Kalimantan Tengah - Reporting Internal</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #60a5fa 100%);
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding: 20px 0;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .logo-tv {
            font-size: 32px;
            font-weight: 700;
            color: #1e40af;
        }
        
        .logo-circle {
            width: 60px;
            height: 60px;
            background: #1e40af;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.4);
            border: 3px solid #dbeafe;
        }
        
        .logo-text {
            font-size: 18px;
            font-weight: 600;
            color: white;
        }
        
        .login-btn {
            background: #1e40af;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
        }
        
        .login-btn:hover {
            background: #1e3a8a;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(30, 64, 175, 0.4);
        }
        
        /* Main Title */
        .main-title {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .title-line-1 {
            font-size: 28px;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
        }
        
        .title-line-2 {
            font-size: 24px;
            font-weight: 600;
            color: #dbeafe;
        }
        
        /* Search Section */
        .search-section {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .search-container {
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }
        
        .search-input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #3b82f6;
            border-radius: 8px;
            font-size: 16px;
            background: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.2);
        }
        
        .search-input:focus {
            outline: none;
            border-color: #1e40af;
            box-shadow: 0 6px 20px rgba(30, 64, 175, 0.3);
        }
        
        .search-input::placeholder {
            color: #9ca3af;
        }
        
        /* Content Area */
        .content-area {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .content-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .content-title {
            font-size: 22px;
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 10px;
        }
        
        .content-subtitle {
            color: #6b7280;
            font-size: 16px;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border: 1px solid #3b82f6;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.2);
            border-color: #1e40af;
            background: linear-gradient(135deg, #bfdbfe 0%, #93c5fd 100%);
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #374151;
            font-weight: 500;
        }
        
        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .action-card {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border: 1px solid #3b82f6;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
        }
        
        .action-card:hover {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(30, 64, 175, 0.3);
        }
        
        .action-card:hover .action-icon {
            background: white;
            color: #1e40af;
        }
        
        .action-icon {
            width: 50px;
            height: 50px;
            background: #1e40af;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            margin: 0 auto 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
        }
        
        .action-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .action-description {
            font-size: 14px;
            opacity: 0.8;
        }
        
        /* Recent Activity */
        .recent-activity {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border: 1px solid #3b82f6;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
        }
        
        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .activity-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e40af;
        }
        
        .view-all-btn {
            color: #1e40af;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .view-all-btn:hover {
            color: #1e3a8a;
        }
        
        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 1px solid #3b82f6;
            transition: all 0.3s ease;
        }
        
        .activity-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            background: #1e40af;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
            box-shadow: 0 2px 8px rgba(30, 64, 175, 0.3);
        }
        
        .activity-content h4 {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
        }
        
        .activity-content p {
            font-size: 12px;
            color: #6b7280;
        }
        
        .activity-time {
            margin-left: auto;
            font-size: 12px;
            color: #9ca3af;
        }
        
        @media (max-width: 768px) {
            .title-line-1 {
                font-size: 22px;
            }
            
            .title-line-2 {
                font-size: 20px;
            }
            
            .search-input {
                padding: 14px 16px;
                font-size: 14px;
            }
            
            .content-area {
                padding: 25px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                <div class="logo-container">
                    <div class="logo-tv">TV</div>
                    <div class="logo-circle">RI</div>
                </div>
                <div class="logo-text">TVRI KALTENG</div>
            </div>
            
            <a href="index.php?page=login" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </a>
        </div>
        
        <!-- Main Title -->
        <div class="main-title">
            <h1 class="title-line-1">REPORTING INTERNAL TRANSMISI TELEVISI</h1>
            <h2 class="title-line-2">REPUBLIK INDONESIA STASIUN KALIMANTAN TENGAH</h2>
        </div>
        
        <!-- Search Section -->
        <div class="search-section">
            <div class="search-container">
                <input type="text" class="search-input" placeholder="apa yang anda cari" id="searchInput">
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
            <div class="content-header">
                <h3 class="content-title">Dashboard Overview</h3>
                <p class="content-subtitle">Ringkasan data dan statistik sistem reporting internal</p>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $dashboardData['total_cases']; ?></div>
                    <div class="stat-label">Total Laporan</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?php echo $dashboardData['today_cases']; ?></div>
                    <div class="stat-label">Laporan Hari Ini</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?php echo $dashboardData['month_cases']; ?></div>
                    <div class="stat-label">Laporan Bulan Ini</div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <div class="action-card" onclick="window.location.href='index.php?page=cases/create'">
                    <div class="action-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="action-title">Buat Laporan Baru</div>
                    <div class="action-description">Laporkan kasus atau masalah baru</div>
                </div>
                
                <div class="action-card" onclick="window.location.href='index.php?page=cases'">
                    <div class="action-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="action-title">Lihat Semua Laporan</div>
                    <div class="action-description">Kelola dan pantau semua laporan</div>
                </div>
                
                <div class="action-card" onclick="window.location.href='index.php?page=dashboard'">
                    <div class="action-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="action-title">Analisis Data</div>
                    <div class="action-description">Lihat grafik dan statistik detail</div>
                </div>
                
                <div class="action-card" onclick="window.location.href='index.php?page=export'">
                    <div class="action-icon">
                        <i class="fas fa-download"></i>
                    </div>
                    <div class="action-title">Export Data</div>
                    <div class="action-description">Download laporan dalam format Excel</div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="recent-activity">
                <div class="activity-header">
                    <h4 class="activity-title">Aktivitas Terbaru</h4>
                    <a href="index.php?page=cases" class="view-all-btn">Lihat Semua</a>
                </div>
                
                <div class="activity-list">
                    <?php if (!empty($dashboardData['recent_activities'])): ?>
                        <?php foreach ($dashboardData['recent_activities'] as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon" style="background-color: <?php echo getStatusColor($activity['status']); ?>;">
                                    <i class="<?php echo getStatusIcon($activity['status']); ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <h4><?php echo htmlspecialchars($activity['title']); ?></h4>
                                    <p><?php echo htmlspecialchars($activity['category_name']); ?> - <?php echo htmlspecialchars($activity['location']); ?></p>
                                </div>
                                <div class="activity-time"><?php echo timeAgo($activity['created_at']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="activity-content">
                                <h4>Tidak ada aktivitas terbaru</h4>
                                <p>Belum ada laporan yang dibuat</p>
                            </div>
                            <div class="activity-time">-</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const searchTerm = this.value.trim();
                if (searchTerm) {
                    // Redirect to dashboard with search parameter
                    window.location.href = `index.php?page=dashboard&search=${encodeURIComponent(searchTerm)}`;
                }
            }
        });
        
        // Add hover effects to stat cards
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-4px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
        
        // Add click effects to action cards
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 150);
            });
        });
    </script>
</body>
</html>
