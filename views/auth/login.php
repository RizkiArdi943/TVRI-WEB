<?php
// No server-side login processing - handled by JavaScript
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TVRI Kalimantan Tengah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-large">
                    <i class="fas fa-tv"></i>
                </div>
                <h1>TVRI Kalimantan Tengah</h1>
                <p>Case Reporting System</p>
            </div>
            
            <!-- Error messages will be shown by JavaScript -->
            
            <form id="loginForm" class="login-form">
                <div class="form-group">
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" placeholder="Username" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i>
                    Masuk
                </button>
            </form>
            
            <div class="login-divider">
                <br/>
            </div>
            
            <div class="register-section">
                <p class="register-text">Pegawai TVRI yang belum memiliki akun?</p>
                <a href="index.php?page=register" class="btn btn-secondary btn-block">
                    <i class="fas fa-user-plus"></i>
                    Daftar Sebagai Member
                </a>
            </div>
            
            <!-- <div class="login-footer">
                <p>Default login: admin / admin123</p>
            </div> -->
        </div>
    </div>
    
    <!-- Include authentication script -->
    <script src="/assets/js/auth.js"></script>
</body>
</html> 