<?php
// No server-side login processing - handled by JavaScript
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIPETRA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body.login-page {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom, #60EFFF, #0061FF);
            color: #2D2F31;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }
        
        /* Dayak Geometric Motifs Background */
        body.login-page::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(circle at 10% 10%, rgba(0, 123, 255, 0.05) 0%, transparent 30%),
                radial-gradient(circle at 90% 10%, rgba(0, 201, 167, 0.05) 0%, transparent 30%),
                radial-gradient(circle at 10% 90%, rgba(255, 107, 107, 0.05) 0%, transparent 30%),
                radial-gradient(circle at 90% 90%, rgba(0, 123, 255, 0.05) 0%, transparent 30%);
            z-index: -1;
        }
        
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            animation: fadeInUp 1s ease-out;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-large {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            padding: 5px;
        }
        
        .logo-large img {
            width: 140px;
            height: 140px;
            object-fit: contain;
            transition: all 0.3s ease;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
        }
        
        .logo-large:hover img {
            filter: drop-shadow(0 6px 12px rgba(0, 0, 0, 0.15));
        }
        
        .login-header h1 {
            color: #007BFF;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 28px;
        }
        
        .login-header p {
            color: #00C9A7;
            font-weight: 400;
            font-size: 16px;
        }
        
        .login-form {
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .input-group:hover {
            border-color: #007BFF;
            transform: translateY(-2px);
        }
        
        .input-group i {
            color: #007BFF;
            margin-right: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
            flex-shrink: 0;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .input-group input {
            border: none;
            background: transparent;
            outline: none;
            flex: 1;
            font-size: 16px;
            color: #2D2F31;
            position: relative;
            z-index: 1;
        }
        
        .input-group input:focus {
            outline: none;
        }
        
        .input-group:focus-within {
            border-color: #007BFF;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }
        
        .input-group:focus-within i {
            color: #00C9A7;
            transform: scale(1.1);
        }
        
        /* Ensure icon stays centered and doesn't move */
        .input-group:hover i,
        .input-group:focus-within i {
            position: relative;
            z-index: 1;
            flex-shrink: 0;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007BFF, #00C9A7);
            color: white;
            width: 100%;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0056b3, #009688);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #2D2F31;
            border: 1px solid #e9ecef;
            width: 100%;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
            color: #1f2937;
            transform: translateY(-2px);
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .login-divider {
            text-align: center;
            margin: 20px 0;
        }
        
        .register-section {
            text-align: center;
        }
        
        .register-text {
            color: #6c757d;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        /* Animations */
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .login-card {
                padding: 30px 20px;
                margin: 10px;
            }
            
            .logo-large img {
                width: 120px;
                height: 120px;
            }
            
            .login-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-large">
                    <img src="assets/images/sipetra-logo-login.png" alt="SIPETRA Logo">
                </div>
                <h1>SIPETRA</h1>
                <p>Sistem Pelaporan Transmisi</p>
            </div>
            
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
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Masuk
                </button>
            </form>
            
            <div class="login-divider">
                <br/>
            </div>
            
            <div class="register-section">
                <p class="register-text">Pegawai TVRI yang belum memiliki akun?</p>
                <a href="index.php?page=register" class="btn btn-secondary">
                    <i class="fas fa-user-plus"></i>
                    Daftar Sebagai Member
                </a>
            </div>
        </div>
    </div>
    
    <script src="assets/js/auth.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const inputs = document.querySelectorAll('input');
            
            // Auto-focus first input
            if (inputs.length > 0) {
                inputs[0].focus();
            }
            
            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get form data
                const formData = new FormData(form);
                const username = formData.get('username');
                const password = formData.get('password');
                
                // Simple validation
                if (!username || !password) {
                    alert('Username dan password harus diisi!');
                    return;
                }
                
                // Show loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                submitBtn.disabled = true;
                
                // Simulate login process
                setTimeout(() => {
                    // Reset button
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    // Show success message
                    alert('Login berhasil!');
                    
                    // Redirect to dashboard
                    window.location.href = 'index.php?page=dashboard';
                }, 2000);
            });
            
            // Input focus effects
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                });
            });
        });
    </script>
</body>
</html>