<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $employee_id = trim($_POST['employee_id'] ?? '');
    $department = trim($_POST['department'] ?? '');
    
    // Validasi input
    if (empty($username) || empty($password) || empty($full_name) || empty($email) || empty($employee_id)) {
        $error = 'Semua field wajib diisi!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        // Cek apakah username sudah ada
        $existing_user = $db->findAll('users', ['username' => $username]);
        if (!empty($existing_user)) {
            $error = 'Username sudah digunakan!';
        } else {
            // Cek apakah email sudah ada
            $existing_email = $db->findAll('users', ['email' => $email]);
            if (!empty($existing_email)) {
                $error = 'Email sudah terdaftar!';
            } else {
                // Cek apakah employee ID sudah ada
                $existing_employee = $db->findAll('users', ['employee_id' => $employee_id]);
                if (!empty($existing_employee)) {
                    $error = 'Nomor Pegawai sudah terdaftar!';
                } else {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert user baru
                    $user_data = [
                        'username' => $username,
                        'password' => $hashed_password,
                        'full_name' => $full_name,
                        'email' => $email,
                        'employee_id' => $employee_id,
                        'department' => $department,
                        'role' => 'user'
                    ];
                    
                    $user_id = $db->insert('users', $user_data);
                    
                    if ($user_id) {
                        $success = 'Registrasi berhasil! Silakan login dengan akun Anda.';
                        // Redirect ke login setelah 2 detik
                        header("refresh:2;url=index.php?page=login");
                    } else {
                        $error = 'Terjadi kesalahan saat registrasi. Silakan coba lagi.';
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Member - TVRI Kalimantan Tengah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #60a5fa 100%);
            padding: 20px;
        }
        
        .register-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 40px;
            width: 100%;
            max-width: 500px;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-large {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            margin: 0 auto 20px;
            box-shadow: 0 4px 15px rgba(30, 64, 175, 0.3);
        }
        
        .register-header h1 {
            color: #1e40af;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .register-header p {
            color: #6b7280;
            font-size: 16px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 500;
            font-size: 14px;
        }
        
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .input-group:focus-within {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .input-group i {
            padding: 12px 8px;
            color: #9ca3af;
            font-size: 16px;
            min-width: 16px;
            text-align: center;
        }
        
        .input-group input,
        .input-group select {
            flex: 1;
            padding: 12px 15px 12px 35px;
            border: none;
            outline: none;
            font-size: 14px;
            background: transparent;
        }
        
        .input-group input::placeholder {
            color: #9ca3af;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-2px);
        }
        
        .btn-block {
            width: 100%;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        
        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        
        .login-link a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .register-card {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <div class="logo-large">
                    <i class="fas fa-tv"></i>
                </div>
                <h1>Daftar Member</h1>
                <p>TVRI Kalimantan Tengah - Case Reporting System</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="register-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Nama Lengkap *</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" id="full_name" name="full_name" placeholder="Masukkan nama lengkap" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="employee_id">Nomor Pegawai *</label>
                        <div class="input-group">
                            <i class="fas fa-id-card"></i>
                            <input type="text" id="employee_id" name="employee_id" placeholder="Contoh: TVRI001" value="<?php echo htmlspecialchars($_POST['employee_id'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <div class="input-group">
                            <i class="fas fa-at"></i>
                            <input type="text" id="username" name="username" placeholder="Masukkan username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="email@tvri.id" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="department">Departemen</label>
                    <div class="input-group">
                        <i class="fas fa-building"></i>
                        <select id="department" name="department">
                            <option value="">Pilih Departemen</option>
                            <option value="Teknik Transmisi" <?php echo ($_POST['department'] ?? '') === 'Teknik Transmisi' ? 'selected' : ''; ?>>Teknik Transmisi</option>
                            <option value="Studio" <?php echo ($_POST['department'] ?? '') === 'Studio' ? 'selected' : ''; ?>>Studio</option>
                            <option value="IT & Sistem" <?php echo ($_POST['department'] ?? '') === 'IT & Sistem' ? 'selected' : ''; ?>>IT & Sistem</option>
                            <option value="Produksi" <?php echo ($_POST['department'] ?? '') === 'Produksi' ? 'selected' : ''; ?>>Produksi</option>
                            <option value="Keuangan" <?php echo ($_POST['department'] ?? '') === 'Keuangan' ? 'selected' : ''; ?>>Keuangan</option>
                            <option value="SDM" <?php echo ($_POST['department'] ?? '') === 'SDM' ? 'selected' : ''; ?>>SDM</option>
                            <option value="Umum" <?php echo ($_POST['department'] ?? '') === 'Umum' ? 'selected' : ''; ?>>Umum</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password *</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i>
                    Daftar Sebagai Member
                </button>
            </form>
            
            <div class="login-link">
                <p>Sudah memiliki akun? <a href="index.php?page=login">Login di sini</a></p>
            </div>
        </div>
    </div>
</body>
</html>
