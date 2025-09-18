<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    header('Location: index.php?page=login');
    exit();
}

$error = '';
$success = '';

// Get current user data
$user_id = $_SESSION['user_id'];
$user = $db->find('users', $user_id);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $employee_id = trim($_POST['employee_id'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($full_name)) {
        $error = 'Nama lengkap harus diisi';
    } elseif (empty($email)) {
        $error = 'Email harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid';
    } elseif (empty($employee_id)) {
        $error = 'Nomor pegawai harus diisi';
    } elseif (empty($department)) {
        $error = 'Departemen harus diisi';
    } else {
        try {
            // Check if email is already taken by another user
            $existing_email = $db->findAll('users', ['email' => $email]);
            if (!empty($existing_email) && $existing_email[0]['id'] != $user_id) {
                $error = 'Email sudah digunakan oleh user lain';
            } else {
                // Check if employee_id is already taken by another user
                $existing_employee = $db->findAll('users', ['employee_id' => $employee_id]);
                if (!empty($existing_employee) && $existing_employee[0]['id'] != $user_id) {
                    $error = 'Nomor pegawai sudah digunakan oleh user lain';
                } else {
                    // Update user data
                    $updateData = [
                        'full_name' => $full_name,
                        'email' => $email,
                        'employee_id' => $employee_id,
                        'department' => $department
                    ];
                    
                    // If password is being changed
                    if (!empty($current_password)) {
                        if (!password_verify($current_password, $user['password'])) {
                            $error = 'Password saat ini salah';
                        } elseif (empty($new_password)) {
                            $error = 'Password baru harus diisi';
                        } elseif (strlen($new_password) < 6) {
                            $error = 'Password baru minimal 6 karakter';
                        } elseif ($new_password !== $confirm_password) {
                            $error = 'Konfirmasi password tidak cocok';
                        } else {
                            $updateData['password'] = password_hash($new_password, PASSWORD_DEFAULT);
                        }
                    }
                    
                    if (empty($error)) {
                        $result = $db->update('users', $user_id, $updateData);
                        
                        if ($result) {
                            $success = 'Profil berhasil diperbarui!';
                            // Update session data
                            $_SESSION['full_name'] = $full_name;
                            // Refresh user data
                            $user = $db->find('users', $user_id);
                        } else {
                            $error = 'Gagal memperbarui profil. Silakan coba lagi.';
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - TVRI Kalteng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
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
        
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .form-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }
        
        .form-title {
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .password-section {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .password-section h3 {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        .user-info {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .user-info h3 {
            font-size: 16px;
            font-weight: 600;
            color: #0369a1;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .info-label {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
        }
        
        .info-value {
            font-size: 14px;
            color: #1e293b;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
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
        <h1>Edit Profil</h1>
        <p>Update informasi pribadi Anda</p>
    </div>
    
    <div class="form-container">
        <div class="form-card">
            <div class="form-title">
                <i class="fas fa-user-edit"></i>
                Informasi Profil
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
            
            <!-- Current User Info -->
            <div class="user-info">
                <h3>
                    <i class="fas fa-info-circle"></i>
                    Informasi Saat Ini
                </h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Username</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Role</div>
                        <div class="info-value"><?php echo ucfirst($user['role']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Bergabung Sejak</div>
                        <div class="info-value"><?php echo date('d M Y', strtotime($user['created_at'])); ?></div>
                    </div>
                </div>
            </div>
            
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Nama Lengkap *</label>
                        <input type="text" id="full_name" name="full_name" 
                               placeholder="Masukkan nama lengkap" 
                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? $user['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" 
                               placeholder="email@tvri.id" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? $user['email']); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="employee_id">Nomor Pegawai *</label>
                        <input type="text" id="employee_id" name="employee_id" 
                               placeholder="Contoh: TVRI001" 
                               value="<?php echo htmlspecialchars($_POST['employee_id'] ?? $user['employee_id']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="department">Departemen *</label>
                        <select id="department" name="department" required>
                            <option value="">Pilih Departemen</option>
                            <option value="Teknik Transmisi" <?php echo ($_POST['department'] ?? $user['department']) == 'Teknik Transmisi' ? 'selected' : ''; ?>>Teknik Transmisi</option>
                            <option value="Studio" <?php echo ($_POST['department'] ?? $user['department']) == 'Studio' ? 'selected' : ''; ?>>Studio</option>
                            <option value="IT & Sistem" <?php echo ($_POST['department'] ?? $user['department']) == 'IT & Sistem' ? 'selected' : ''; ?>>IT & Sistem</option>
                            <option value="Produksi" <?php echo ($_POST['department'] ?? $user['department']) == 'Produksi' ? 'selected' : ''; ?>>Produksi</option>
                            <option value="Keuangan" <?php echo ($_POST['department'] ?? $user['department']) == 'Keuangan' ? 'selected' : ''; ?>>Keuangan</option>
                            <option value="SDM" <?php echo ($_POST['department'] ?? $user['department']) == 'SDM' ? 'selected' : ''; ?>>SDM</option>
                            <option value="Umum" <?php echo ($_POST['department'] ?? $user['department']) == 'Umum' ? 'selected' : ''; ?>>Umum</option>
                        </select>
                    </div>
                </div>
                
                <!-- Password Change Section -->
                <div class="password-section">
                    <h3>
                        <i class="fas fa-lock"></i>
                        Ubah Password (Opsional)
                    </h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="current_password">Password Saat Ini</label>
                            <input type="password" id="current_password" name="current_password" 
                                   placeholder="Masukkan password saat ini">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password">Password Baru</label>
                            <input type="password" id="new_password" name="new_password" 
                                   placeholder="Minimal 6 karakter">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Konfirmasi Password Baru</label>
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   placeholder="Ulangi password baru">
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Simpan Perubahan
                    </button>
                    <a href="index.php?page=dashboard" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
