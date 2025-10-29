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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        
        .landing-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .logo-text {
            font-size: 18px;
            font-weight: 600;
            color: white;
        }
        
        .login-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .login-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
        }
        
        .main-title {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .title-line-1 {
            font-size: 28px;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .title-line-2 {
            font-size: 24px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
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
            padding: 18px 25px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            transform: translateY(-2px);
        }
        
        .search-input::placeholder {
            color: #999;
        }
        
        .search-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-size: 18px;
        }
        
        .content-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }
        
        .content-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .content-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .content-subtitle {
            color: #666;
            font-size: 16px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .feature-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
        
        .feature-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            margin-bottom: 20px;
        }
        
        .feature-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .feature-description {
            color: #666;
            line-height: 1.6;
        }
        
        @media (max-width: 768px) {
            .title-line-1 {
                font-size: 22px;
            }
            
            .title-line-2 {
                font-size: 20px;
            }
            
            .search-input {
                padding: 15px 20px;
                font-size: 14px;
            }
            
            .content-section {
                padding: 25px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <!-- Header Section -->
        <div class="header-section">
            <div class="logo-section">
                <div class="logo">
                    <i class="fas fa-tv"></i>
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
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>
        
        <!-- Content Section -->
        <div class="content-section">
            <div class="content-header">
                <h3 class="content-title">Dashboard Overview</h3>
                <p class="content-subtitle">Ringkasan data dan statistik sistem reporting internal</p>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">156</div>
                    <div class="stat-label">Total Laporan</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number">23</div>
                    <div class="stat-label">Laporan Hari Ini</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number">89</div>
                    <div class="stat-label">Laporan Bulan Ini</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number">12</div>
                    <div class="stat-label">Laporan Pending</div>
                </div>
            </div>
            
            <!-- Features Grid -->
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h4 class="feature-title">Pelaporan Kasus</h4>
                    <p class="feature-description">
                        Sistem pelaporan kasus internal yang terintegrasi untuk memantau dan mengelola berbagai masalah teknis dan operasional.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4 class="feature-title">Analisis Data</h4>
                    <p class="feature-description">
                        Dashboard analitik yang menyediakan insight mendalam tentang tren dan pola dalam pelaporan kasus.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4 class="feature-title">Manajemen Tim</h4>
                    <p class="feature-description">
                        Sistem manajemen tim yang memungkinkan koordinasi dan kolaborasi antar departemen dalam penanganan kasus.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h4 class="feature-title">Notifikasi Real-time</h4>
                    <p class="feature-description">
                        Sistem notifikasi yang memberikan update real-time tentang status dan perkembangan kasus.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-export"></i>
                    </div>
                    <h4 class="feature-title">Export & Reporting</h4>
                    <p class="feature-description">
                        Fitur export data dan pembuatan laporan yang dapat disesuaikan untuk kebutuhan analisis dan audit.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4 class="feature-title">Keamanan Data</h4>
                    <p class="feature-description">
                        Sistem keamanan tingkat tinggi yang melindungi data sensitif dan memastikan privasi informasi internal.
                    </p>
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
        
        // Add some interactive effects
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
        
        // Animate stats on page load
        window.addEventListener('load', function() {
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach((stat, index) => {
                setTimeout(() => {
                    stat.style.opacity = '0';
                    stat.style.transform = 'translateY(20px)';
                    stat.style.transition = 'all 0.8s ease';
                    
                    setTimeout(() => {
                        stat.style.opacity = '1';
                        stat.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 200);
            });
        });
    </script>
</body>
</html>
