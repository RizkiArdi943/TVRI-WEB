<?php
require_once __DIR__ . '/../../config/database.php';

$success = '';
$error = '';

// Get current user
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Semua field password wajib diisi!';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Password baru dan konfirmasi password tidak cocok!';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Password baru minimal 6 karakter!';
    } elseif (!password_verify($currentPassword, $user['password'])) {
        $error = 'Password saat ini salah!';
    } else {
        try {
            $db->update('users', $user['id'], [
                'password' => password_hash($newPassword, PASSWORD_DEFAULT)
            ]);
            
            $success = 'Password berhasil diubah!';
            
            // Refresh user data
            $user = getCurrentUser();
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}
?>

<div class="profile-page">
    <div class="page-header">
        <h1>Profil Saya</h1>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="profile-grid">
        <!-- Profile Information -->
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="profile-info">
                    <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                    <p class="profile-role"><?php echo ucfirst($user['role']); ?></p>
                </div>
            </div>

            <div class="profile-details">
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-user"></i>
                        Username
                    </div>
                    <div class="detail-value">
                        <?php echo htmlspecialchars($user['username']); ?>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-envelope"></i>
                        Email
                    </div>
                    <div class="detail-value">
                        <?php echo htmlspecialchars($user['email']); ?>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-calendar"></i>
                        Bergabung Sejak
                    </div>
                    <div class="detail-value">
                        <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-shield-alt"></i>
                        Role
                    </div>
                    <div class="detail-value">
                        <?php echo ucfirst($user['role']); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="profile-card">
            <h3>Ubah Password</h3>
            <form method="POST" class="password-form">
                <div class="form-group">
                    <label for="current_password">Password Saat Ini *</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password">Password Baru *</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password Baru *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key"></i>
                        Ubah Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <div class="action-card">
            <div class="action-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="action-content">
                <h4>Laporan Saya</h4>
                <p>Lihat semua laporan yang Anda buat</p>
                <a href="index.php?page=cases" class="btn btn-primary">
                    <i class="fas fa-eye"></i>
                    Lihat Laporan
                </a>
            </div>
        </div>

        <div class="action-card">
            <div class="action-icon">
                <i class="fas fa-plus"></i>
            </div>
            <div class="action-content">
                <h4>Tambah Laporan</h4>
                <p>Buat laporan kasus baru</p>
                <a href="index.php?page=cases/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Tambah Laporan
                </a>
            </div>
        </div>

        <div class="action-card">
            <div class="action-icon">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <div class="action-content">
                <h4>Logout</h4>
                <p>Keluar dari sistem</p>
                <a href="#" class="btn btn-danger logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </div>
</div> 