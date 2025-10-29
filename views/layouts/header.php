<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPETRA - Sistem Pelaporan Transmisi</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }
        
        .header {
            background: linear-gradient(135deg, #4A90E2 0%, #60EFFF 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 1000;
            padding: 15px 40px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo img {
            height: 70px;
            width: auto;
            object-fit: contain;
        }
        
        .tvri-logo {
            height: 70px;
            width: auto;
            max-width: 200px;
            object-fit: contain;
            margin-right: 15px;
        }
        
        .sipetra-logo {
            height: 70px;
            width: auto;
            max-width: 200px;
            object-fit: contain;
        }
        
        .logo-text {
            color: #FFFFFF;
            font-size: 24px;
            font-weight: 700;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #FFFFFF;
        }
        
        .user-name {
            font-weight: 500;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .main-content {
            margin-top: 100px;
            min-height: calc(100vh - 100px);
            padding-bottom: 80px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 12px 20px;
            }
            
            .logo img {
                height: 60px;
            }
            
            .tvri-logo {
                height: 60px;
                max-width: 150px;
                margin-right: 10px;
            }
            
            .sipetra-logo {
                height: 60px;
                max-width: 150px;
            }
            
            .main-content {
                margin-top: 90px;
            }
            
            .container {
                padding: 25px 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo-section">
                <div class="logo">
                    <img src="assets/images/tvri-logo.png" alt="TVRI Logo" class="tvri-logo">
                    <img src="assets/images/sipetra-logo.png" alt="SIPETRA Logo" class="sipetra-logo">
                </div>
            </div>
            <div class="user-info">
                <span class="user-name"><?php echo $_SESSION['username'] ?? 'User'; ?></span>
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">