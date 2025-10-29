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
            return '#FFA45B'; // Orange untuk status pending
        case 'in_progress':
            return '#009FE3'; // Biru TVRI untuk in progress
        case 'completed':
            return '#A9E4A9'; // Hijau lembut untuk selesai
        case 'cancelled':
            return '#EF4444'; // Merah untuk dibatalkan
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
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>TVRI Kalimantan Tengah - Reporting Internal</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css?v=<?php echo time(); ?>">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom, #60EFFF, #0061FF);
            color: #2D2F31;
            line-height: 1.6;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            padding-top: 130px;
        }
        
        /* Dayak Geometric Motifs Background */
        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                /* Top left corner motif */
                radial-gradient(circle at 10% 10%, rgba(0, 123, 255, 0.05) 0%, transparent 30%),
                /* Top right corner motif */
                radial-gradient(circle at 90% 10%, rgba(0, 201, 167, 0.05) 0%, transparent 30%),
                /* Bottom left corner motif */
                radial-gradient(circle at 10% 90%, rgba(255, 107, 107, 0.05) 0%, transparent 30%),
                /* Bottom right corner motif */
                radial-gradient(circle at 90% 90%, rgba(0, 123, 255, 0.05) 0%, transparent 30%);
            z-index: -1;
        }
        
        @keyframes backgroundShift {
            0%, 100% { transform: translateX(0) translateY(0); }
            25% { transform: translateX(-20px) translateY(-10px); }
            50% { transform: translateX(20px) translateY(10px); }
            75% { transform: translateX(-10px) translateY(20px); }
        }
        
        /* Dayak Motif Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 20%, rgba(32, 178, 170, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(126, 211, 33, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(74, 144, 226, 0.08) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
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
            padding: 15px 40px;
            background: linear-gradient(to right, #60EFFF 0%, #0061FF 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 1000;
            animation: slideDown 1s ease-out;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-left: 40px;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 8px;
            animation: fadeInLeft 1s ease-out 0.2s both;
        }
        
        .logo-image {
            height: 100px;
            width: auto;
            max-width: 350px;
            object-fit: contain;
        }
        
        .tvri-logo {
            height: 100px;
            width: auto;
            max-width: 350px;
            object-fit: contain;
            animation: fadeInRight 1s ease-out 0.2s both;
        }
        
        /* Fallback jika logo tidak ditemukan */
        .logo-container:not(:has(.logo-image)) {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .logo-container:not(:has(.logo-image))::before {
            content: 'SIPETRA';
            font-size: 32px;
            font-weight: 600;
            color: #FFFFFF;
            font-family: 'Poppins', sans-serif;
        }
        
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo-tv {
            font-size: 32px;
            font-weight: 600;
            color: #FFFFFF;
            font-family: 'Poppins', sans-serif;
        }
        
        .logo-circle {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #FFFFFF, #F4F6F8);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #003B73;
            font-size: 20px;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
            border: 2px solid #00C9A7;
        }
        
        .logo-text {
            font-size: 18px;
            font-weight: 400;
            color: #FFFFFF;
            font-family: 'Poppins', sans-serif;
        }
        
        .logo-branch {
            color: #CDEFFF;
            font-size: 14px;
            margin-left: 8px;
            font-weight: 400;
        }
        
        .logo-subtitle {
            color: #CDEFFF;
            font-size: 12px;
            margin-top: 2px;
            font-weight: 300;
        }
        
        .login-btn {
            background: #FF6B6B;
            color: #FFFFFF;
            padding: 12px 24px;
            border-radius: 16px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
            font-family: 'Poppins', sans-serif;
            animation: fadeInRight 1s ease-out 0.2s both;
        }
        
        .login-btn:hover {
            transform: translateY(-2px) scale(1.03);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
            background: #E55A5A;
        }
        
        /* Main Title */
        .main-title {
            text-align: center;
            margin-top: 40px;
            margin-bottom: 40px;
            animation: fadeInUp 1s ease-out 0.5s both;
            position: relative;
        }
        
        .main-title::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
            z-index: -1;
            animation: pulse 3s ease-in-out infinite;
        }
        
        .main-title::after {
            content: '📡';
            position: absolute;
            top: -20px;
            right: 20%;
            font-size: 24px;
            opacity: 0.3;
            animation: float 4s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .title-line-1 {
            font-size: 40px;
            font-weight: 700;
            color: #0B2C8E;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            animation: slideInDown 1s ease-out 0.7s both;
        }
        
        .title-line-2 {
            font-size: 24px;
            font-weight: 600;
            color: #0B2C8E;
            margin-bottom: 20px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
            animation: slideInDown 1s ease-out 0.9s both;
        }
        
        .title-line-3 {
            font-size: 18px;
            font-weight: 500;
            color: #333333;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
            animation: slideInDown 1s ease-out 1.1s both;
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Search Section */
        .search-section {
            text-align: center;
            margin-top: 30px;
            margin-bottom: 50px;
            animation: fadeInUp 1s ease-out 0.8s both;
        }
        
        .search-container {
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }
        
        .search-input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #007BFF;
            border-radius: 16px;
            font-size: 16px;
            background: #FFFFFF;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.1);
            font-family: 'Poppins', sans-serif;
            position: relative;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #00C9A7;
            box-shadow: 0 6px 20px rgba(0, 201, 167, 0.3);
            transform: scale(1.02);
        }
        
        .search-input::placeholder {
            color: #9ca3af;
            font-family: 'Poppins', sans-serif;
        }
        
        /* Content Area */
        .content-area {
            background: #FFFFFF;
            border-radius: 20px;
            padding: 40px;
            margin-top: 20px;
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.1);
            border: 1px solid rgba(0, 123, 255, 0.1);
            animation: fadeInUp 1s ease-out 1.1s both;
            transition: all 0.3s ease;
        }
        
        .content-area:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0, 123, 255, 0.15);
        }
        
        .content-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .content-title {
            font-size: 22px;
            font-weight: 600;
            color: #007BFF;
            margin-bottom: 10px;
        }
        
        .content-subtitle {
            color: #2D2F31;
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
            background: #FFFFFF;
            border: 1px solid #007BFF;
            padding: 20px;
            border-radius: 20px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.1);
            animation: fadeInUp 1s ease-out calc(1.4s + var(--delay, 0s)) both;
        }
        
        .stat-card:nth-child(1) { --delay: 0s; }
        .stat-card:nth-child(2) { --delay: 0.1s; }
        .stat-card:nth-child(3) { --delay: 0.2s; }
        
        .stat-card:hover {
            transform: translateY(-4px) scale(1.03);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.2);
            border-color: #00C9A7;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #007BFF;
            margin-bottom: 8px;
            animation: countUp 2s ease-out calc(1.6s + var(--delay, 0s)) both;
        }
        
        @keyframes countUp {
            from {
                opacity: 0;
                transform: scale(0.5);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .stat-label {
            font-size: 14px;
            color: #2D2F31;
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
            background: #FFFFFF;
            border: 1px solid #007BFF;
            padding: 20px;
            border-radius: 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.1);
            animation: fadeInUp 1s ease-out calc(1.7s + var(--delay, 0s)) both;
        }
        
        .action-card:nth-child(1) { --delay: 0s; }
        .action-card:nth-child(2) { --delay: 0.1s; }
        .action-card:nth-child(3) { --delay: 0.2s; }
        .action-card:nth-child(4) { --delay: 0.3s; }
        
        .action-card:hover {
            background: linear-gradient(135deg, #007BFF 0%, #00C9A7 100%);
            color: white;
            transform: translateY(-4px) scale(1.03);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.3);
        }
        
        .action-card:hover .action-icon {
            background: #d9f0ff !important;
            color: #007BFF !important;
        }
        
        /* Ensure Font Awesome icons are visible */
        .fas, .far, .fab {
            display: inline-block !important;
            font-style: normal !important;
            font-variant: normal !important;
            text-rendering: auto !important;
            -webkit-font-smoothing: antialiased !important;
        }
        
        /* Force icon visibility */
        .stat-number i,
        .action-icon i {
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        .action-icon {
            width: 50px;
            height: 50px;
            background: #007BFF;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            margin: 0 auto 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
            animation: bounceIn 1s ease-out calc(1.8s + var(--delay, 0s)) both;
        }
        
        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.3);
            }
            50% {
                opacity: 1;
                transform: scale(1.05);
            }
            70% {
                transform: scale(0.9);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
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
            background: #FFFFFF;
            border: 1px solid #007BFF;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.1);
            animation: fadeInUp 1s ease-out 2s both;
            transition: all 0.3s ease;
        }
        
        .recent-activity:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.2);
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
            color: #007BFF;
        }
        
        .view-all-btn {
            color: #007BFF;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .view-all-btn:hover {
            color: #00C9A7;
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
            background: #FFFFFF;
            border-radius: 20px;
            border: 1px solid #007BFF;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.1);
            animation: fadeInUp 1s ease-out calc(2.2s + var(--delay, 0s)) both;
        }
        
        .activity-item:nth-child(1) { --delay: 0s; }
        .activity-item:nth-child(2) { --delay: 0.1s; }
        .activity-item:nth-child(3) { --delay: 0.2s; }
        
        .activity-item:hover {
            transform: translateX(5px) scale(1.02);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.2);
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            background: #007BFF;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }
        
        /* Loading Animation */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #4A90E2;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .activity-wave-icon {
            color: #00C9A7;
            font-size: 14px;
            margin-right: 8px;
        }
        
        .activity-content h4 {
            font-size: 14px;
            font-weight: 600;
            color: #2D2F31;
            margin-bottom: 4px;
        }
        
        .activity-content p {
            font-size: 12px;
            color: #2D2F31;
        }
        
        .activity-time {
            margin-left: auto;
            font-size: 12px;
            color: #9ca3af;
        }
        
        @media (max-width: 768px) {
            body {
                padding-top: 110px;
            }
            
            .header {
                padding: 12px 20px;
            }
            
            .logo {
                margin-left: 20px;
            }
            
            .logo-image {
                height: 70px;
                max-width: 250px;
            }
            
            .tvri-logo {
                height: 70px;
                max-width: 250px;
            }
            
            .logo-text {
                font-size: 14px;
            }
            
            .logo-subtitle {
                font-size: 10px;
            }
            
            .login-btn {
                padding: 10px 16px;
                font-size: 12px;
            }
            
            .main-title {
                margin-top: 20px;
                margin-bottom: 30px;
            }
            
            .search-section {
                margin-top: 20px;
                margin-bottom: 30px;
            }
            
            .content-area {
                margin-top: 15px;
                padding: 25px;
            }
            
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
                <img src="assets/images/tvri-logo.png" alt="TVRI Logo" class="tvri-logo">
                <div class="logo-container">
                    <img src="assets/images/sipetra-logo.png" alt="SIPETRA Logo" class="logo-image">
                </div>
            </div>
            
            <a href="index.php?page=login" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </a>
        </div>
        
        <!-- Main Title -->
        <div class="main-title">
            <h1 class="title-line-1">Selamat Datang di SIPETRA</h1>
            <h2 class="title-line-2">(Sistem Pelaporan Transmisi)</h2>
            <p class="title-line-3">Digitalisasi Pelaporan Internal untuk Meningkatkan Efisiensi, Akurasi, dan Transparansi Operasional TVRI Kalimantan Tengah.</p>
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
                <div class="stat-card" style="border-color: #4A90E2; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                    <div class="stat-number" style="color: #4A90E2;">
                        <svg width="24" height="24" viewBox="0 0 48 48" fill="none" style="margin-right: 8px;">
                            <!-- Perisai Talawang - Desain Tribal Kalteng -->
                            <path d="M24 2L8 8V20C8 29.33 15.33 38.67 24 46C32.67 38.67 40 29.33 40 20V8L24 2Z" fill="#4A90E2"/>
                            <!-- Motif Tribal di bagian atas - Coral -->
                            <path d="M24 6C20 8 18 12 18 16C18 20 20 22 24 22C28 22 30 20 30 16C30 12 28 8 24 6Z" fill="#FF6B6B"/>
                            <!-- Motif spiral di tengah - Teal -->
                            <path d="M24 12C22 14 20 16 20 18C20 20 22 22 24 22C26 22 28 20 28 18C28 16 26 14 24 12Z" fill="#20B2AA"/>
                            <!-- Detail tribal di bawah - White -->
                            <path d="M24 24C22 26 20 28 20 30C20 32 22 34 24 34C26 34 28 32 28 30C28 28 26 26 24 24Z" fill="#FFFFFF"/>
                            <!-- Garis-garis tribal putih -->
                            <path d="M16 8L20 12L24 8L28 12L32 8" stroke="#FFFFFF" stroke-width="1.5" fill="none"/>
                            <path d="M16 16L20 20L24 16L28 20L32 16" stroke="#FFFFFF" stroke-width="1.5" fill="none"/>
                            <!-- Motif spiral di mata -->
                            <circle cx="20" cy="18" r="2" fill="#FFFFFF"/>
                            <circle cx="28" cy="18" r="2" fill="#FFFFFF"/>
                            <!-- Garis tribal di bawah -->
                            <path d="M18 30L24 26L30 30" stroke="#FFFFFF" stroke-width="2" fill="none"/>
                        </svg>
                        <?php echo $dashboardData['total_cases']; ?>
                    </div>
                    <div class="stat-label">Total Laporan</div>
                </div>
                
                <div class="stat-card" style="border-color: #FF6B6B; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                    <div class="stat-number" style="color: #FF6B6B;">
                        <i class="fas fa-calendar-day" style="font-size: 24px; margin-right: 8px;"></i>
                        <?php echo $dashboardData['today_cases']; ?>
                    </div>
                    <div class="stat-label">Laporan Hari Ini</div>
                </div>
                
                <div class="stat-card" style="border-color: #20B2AA; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                    <div class="stat-number" style="color: #20B2AA;">
                        <i class="fas fa-calendar-alt" style="font-size: 24px; margin-right: 8px;"></i>
                        <?php echo $dashboardData['month_cases']; ?>
                    </div>
                    <div class="stat-label">Laporan Bulan Ini</div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <div class="action-card" onclick="window.location.href='index.php?page=cases/create'" style="border-color: #FF6B6B;">
                    <div class="action-icon" style="background: #FF6B6B;">
                        <i class="fas fa-plus-circle" style="color: white; font-size: 20px;"></i>
                    </div>
                    <div class="action-title">Buat Laporan Baru</div>
                    <div class="action-description">Laporkan kasus atau masalah baru</div>
                </div>
                
                <div class="action-card" onclick="window.location.href='index.php?page=cases'" style="border-color: #7ED321;">
                    <div class="action-icon" style="background: #7ED321;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="color: white;">
                            <!-- Folder dengan motif Dayak -->
                            <path d="M3 6H21L19 4H5L3 6Z" fill="white"/>
                            <path d="M3 6V18C3 19.1 3.9 20 5 20H19C20.1 20 21 19.1 21 18V8H3V6Z" fill="white"/>
                            <!-- Motif tribal di dalam folder -->
                            <path d="M7 12H17M7 14H15M7 16H13" stroke="#7ED321" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="action-title">Lihat Semua Laporan</div>
                    <div class="action-description">Kelola dan pantau semua laporan</div>
                </div>
                
                <div class="action-card" onclick="window.location.href='index.php?page=dashboard'" style="border-color: #4A90E2;">
                    <div class="action-icon" style="background: #4A90E2;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="color: white;">
                            <!-- Grafik dengan motif Dayak -->
                            <path d="M3 17L9 11L13 15L21 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="3" cy="17" r="2" fill="white"/>
                            <circle cx="9" cy="11" r="2" fill="white"/>
                            <circle cx="13" cy="15" r="2" fill="white"/>
                            <circle cx="21" cy="7" r="2" fill="white"/>
                            <!-- Motif tribal di garis -->
                            <path d="M6 14L8 12M15 10L17 8" stroke="white" stroke-width="1" stroke-linecap="round" opacity="0.7"/>
                        </svg>
                    </div>
                    <div class="action-title">Analisis Data</div>
                    <div class="action-description">Lihat grafik dan statistik detail</div>
                </div>
                
                <div class="action-card" onclick="checkLoginAndRedirect('index.php?page=cases')" style="border-color: #7ED321;">
                    <div class="action-icon" style="background: #7ED321;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="color: white;">
                            <!-- Dokumen dengan motif Dayak -->
                            <path d="M14 2H6C4.9 2 4 2.9 4 4V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V8L14 2Z" fill="white"/>
                            <path d="M14 2V8H20" stroke="#7ED321" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <!-- Motif tribal di dokumen -->
                            <path d="M8 12H16M8 14H14M8 16H12" stroke="#7ED321" stroke-width="1.5" stroke-linecap="round"/>
                            <!-- Panah download -->
                            <path d="M12 18L12 12M9 15L12 18L15 15" stroke="#7ED321" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
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
                                <i class="fas fa-water activity-wave-icon"></i>
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
        // Enhanced interactive text effects
        document.addEventListener('DOMContentLoaded', function() {
            // Add interactive effects to title lines
            const titleLines = document.querySelectorAll('.title-line-1, .title-line-2, .title-line-3');
            
            titleLines.forEach((line, index) => {
                // Add hover effect
                line.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.05)';
                    this.style.transition = 'all 0.3s ease';
                    this.style.textShadow = '0 4px 8px rgba(0,0,0,0.2)';
                });
                
                line.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                    this.style.textShadow = '0 2px 4px rgba(0,0,0,0.1)';
                });
                
                // Add click effect
                line.addEventListener('click', function() {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1.05)';
                        setTimeout(() => {
                            this.style.transform = 'scale(1)';
                        }, 150);
                    }, 150);
                });
                
                // Add typing effect for title-line-1
                if (line.classList.contains('title-line-1')) {
                    const originalText = line.textContent;
                    line.textContent = '';
                    let i = 0;
                    
                    function typeWriter() {
                        if (i < originalText.length) {
                            line.textContent += originalText.charAt(i);
                            i++;
                            setTimeout(typeWriter, 100);
                        }
                    }
                    
                    setTimeout(typeWriter, 1000);
                }
            });
            
            // Add floating particles around main title
            const mainTitle = document.querySelector('.main-title');
            if (mainTitle) {
                for (let i = 0; i < 5; i++) {
                    const particle = document.createElement('div');
                    particle.style.position = 'absolute';
                    particle.style.width = '4px';
                    particle.style.height = '4px';
                    particle.style.background = 'rgba(11, 44, 142, 0.3)';
                    particle.style.borderRadius = '50%';
                    particle.style.left = Math.random() * 100 + '%';
                    particle.style.top = Math.random() * 100 + '%';
                    particle.style.animation = `float ${3 + Math.random() * 2}s ease-in-out infinite`;
                    particle.style.animationDelay = Math.random() * 2 + 's';
                    mainTitle.appendChild(particle);
                }
            }
            
            // Add ripple effect to main title
            mainTitle.addEventListener('click', function(e) {
                const ripple = document.createElement('div');
                ripple.style.position = 'absolute';
                ripple.style.borderRadius = '50%';
                ripple.style.background = 'rgba(11, 44, 142, 0.2)';
                ripple.style.transform = 'scale(0)';
                ripple.style.animation = 'ripple 0.6s linear';
                ripple.style.left = (e.clientX - this.offsetLeft) + 'px';
                ripple.style.top = (e.clientY - this.offsetTop) + 'px';
                ripple.style.width = ripple.style.height = '20px';
                
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });
        
        // Function to check login status and redirect
        function checkLoginAndRedirect(url) {
            // Check if user is logged in by checking for session or localStorage
            const isLoggedIn = checkLoginStatus();
            
            if (isLoggedIn) {
                // User is logged in, redirect to the target page
                window.location.href = url;
            } else {
                // User is not logged in, redirect to login page
                window.location.href = 'index.php?page=login';
            }
        }
        
        // Function to check login status
        function checkLoginStatus() {
            // Check localStorage for login status
            const loginStatus = localStorage.getItem('user_logged_in');
            const userSession = localStorage.getItem('user_session');
            
            // Check if login status exists and is true
            if (loginStatus === 'true' && userSession) {
                return true;
            }
            
            // Check if there's a session cookie (alternative method)
            const cookies = document.cookie.split(';');
            for (let cookie of cookies) {
                const [name, value] = cookie.trim().split('=');
                if (name === 'user_logged_in' && value === 'true') {
                    return true;
                }
            }
            
            return false;
        }
        
        // Search functionality with enhanced UX
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const searchTerm = this.value.trim();
                if (searchTerm) {
                    // Add loading state
                    this.style.opacity = '0.6';
                    this.style.pointerEvents = 'none';
                    
                    // Redirect to dashboard with search parameter
                    setTimeout(() => {
                    window.location.href = `index.php?page=dashboard&search=${encodeURIComponent(searchTerm)}`;
                    }, 300);
                }
            }
        });
        
        // Enhanced hover effects for stat cards
        document.querySelectorAll('.stat-card').forEach((card, index) => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
                this.style.boxShadow = '0 12px 25px rgba(0, 123, 255, 0.3)';
                
                // Animate the number
                const number = this.querySelector('.stat-number');
                if (number) {
                    number.style.transform = 'scale(1.1)';
                    number.style.color = '#00C9A7';
                }
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
                this.style.boxShadow = '0 6px 20px rgba(0, 123, 255, 0.1)';
                
                // Reset the number
                const number = this.querySelector('.stat-number');
                if (number) {
                    number.style.transform = 'scale(1)';
                    number.style.color = '#007BFF';
                }
            });
        });
        
        // Enhanced click effects for action cards
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Add ripple effect
                const ripple = document.createElement('div');
                ripple.style.position = 'absolute';
                ripple.style.borderRadius = '50%';
                ripple.style.background = 'rgba(0, 123, 255, 0.3)';
                ripple.style.transform = 'scale(0)';
                ripple.style.animation = 'ripple 0.6s linear';
                ripple.style.left = (e.clientX - this.offsetLeft) + 'px';
                ripple.style.top = (e.clientY - this.offsetTop) + 'px';
                ripple.style.width = ripple.style.height = '20px';
                
                this.appendChild(ripple);
                
                // Remove ripple after animation
                setTimeout(() => {
                    ripple.remove();
                }, 600);
                
                // Navigate after animation
                setTimeout(() => {
                    const href = this.getAttribute('onclick');
                    if (href) {
                        eval(href);
                    }
                }, 300);
            });
        });
        
        // Add typing effect to search placeholder
        const searchInput = document.getElementById('searchInput');
        const placeholders = [
            'Cari laporan kerusakan...',
            'Cari berdasarkan lokasi...',
            'Cari berdasarkan peralatan...',
            'Cari berdasarkan status...'
        ];
        
        let currentIndex = 0;
        let currentText = '';
        let isDeleting = false;
        
        function typeEffect() {
            const current = placeholders[currentIndex];
            
            if (isDeleting) {
                currentText = current.substring(0, currentText.length - 1);
            } else {
                currentText = current.substring(0, currentText.length + 1);
            }
            
            searchInput.placeholder = currentText;
            
            let typeSpeed = isDeleting ? 50 : 100;
            
            if (!isDeleting && currentText === current) {
                typeSpeed = 2000;
                isDeleting = true;
            } else if (isDeleting && currentText === '') {
                isDeleting = false;
                currentIndex = (currentIndex + 1) % placeholders.length;
                typeSpeed = 500;
            }
            
            setTimeout(typeEffect, typeSpeed);
        }
        
        // Start typing effect when page loads
        window.addEventListener('load', () => {
            setTimeout(typeEffect, 2000);
        });
        
        // Add scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        // Observe all animated elements
        document.querySelectorAll('.stat-card, .action-card, .activity-item').forEach(el => {
            observer.observe(el);
        });
        
        // Add CSS for ripple effect
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>