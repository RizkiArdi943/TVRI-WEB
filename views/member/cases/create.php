<?php
require_once __DIR__ . '/../../../controllers/member_cases.php';

// Initialize controller
$memberCasesController = new MemberCasesController();

// Handle form submission
$result = $memberCasesController->create();
$success = $result['success'];
$error = $result['error'];

// Debug: Log request method and session status
error_log('=== MEMBER CREATE FORM DEBUG ===');
error_log('Request Method: ' . $_SERVER['REQUEST_METHOD']);
error_log('Session User ID: ' . ($_SESSION['user_id'] ?? 'Not set'));
error_log('Success: ' . $success);
error_log('Error: ' . $error);

// Get categories for dropdown
$categories = $memberCasesController->getCategories();

// Form processing is now handled by MemberCasesController
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Laporan Baru - TVRI Kalteng</title>
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .priority-options {
            display: flex;
            gap: 10px;
        }
        
        .priority-option {
            flex: 1;
            padding: 10px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        
        .priority-option:hover {
            border-color: #3b82f6;
        }
        
        .priority-option input[type="radio"] {
            display: none;
        }
        
        .priority-option input[type="radio"]:checked + label {
            color: #3b82f6;
            font-weight: 600;
        }
        
        .priority-option.priority-low {
            border-color: #10b981;
        }
        
        .priority-option.priority-low:hover,
        .priority-option.priority-low input[type="radio"]:checked + label {
            color: #10b981;
        }
        
        .priority-option.priority-medium {
            border-color: #f59e0b;
        }
        
        .priority-option.priority-medium:hover,
        .priority-option.priority-medium input[type="radio"]:checked + label {
            color: #f59e0b;
        }
        
        .priority-option.priority-high {
            border-color: #ef4444;
        }
        
        .priority-option.priority-high:hover,
        .priority-option.priority-high input[type="radio"]:checked + label {
            color: #ef4444;
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
            transition: all 0.2s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .btn-outline {
            background: white;
            color: #6b7280;
            border: 2px solid #e5e7eb;
        }
        
        .btn-outline:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }
        
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        .toast {
            min-width: 300px;
            padding: 16px 20px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            font-weight: 500;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: slideInRight 0.3s ease-out;
        }
        
        .toast.success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border-left: 4px solid #047857;
        }
        
        .toast.error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border-left: 4px solid #b91c1c;
        }
        
        .toast-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .toast-close {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            padding: 0;
            opacity: 0.8;
            transition: opacity 0.2s;
        }
        
        .toast-close:hover {
            opacity: 1;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .toast.hide {
            animation: slideOutRight 0.3s ease-in forwards;
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
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
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
        <h1>Buat Laporan Baru</h1>
        <p>Laporkan masalah atau keluhan Anda</p>
    </div>
    
    <div class="form-container">
        <!-- Toast Notification Container -->
        <div id="toast-container" class="toast-container"></div>

        <!-- Alert Messages (fallback) -->
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

        <div class="form-card">
            <div class="form-title">
                <i class="fas fa-plus-circle"></i>
                Form Laporan Baru
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="case-form" id="caseForm">
                <div class="form-group">
                    <label for="title">Judul Laporan *</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi *</label>
                    <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="location">Lokasi *</label>
                        <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Kategori</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        <?php echo ($_POST['category_id'] ?? '1') == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="priority">Prioritas</label>
                        <select id="priority" name="priority">
                            <option value="low" <?php echo ($_POST['priority'] ?? 'medium') === 'low' ? 'selected' : ''; ?>>Rendah</option>
                            <option value="medium" <?php echo ($_POST['priority'] ?? 'medium') === 'medium' ? 'selected' : ''; ?>>Sedang</option>
                            <option value="high" <?php echo ($_POST['priority'] ?? 'medium') === 'high' ? 'selected' : ''; ?>>Tinggi</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="image">Gambar (opsional)</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small class="form-hint">Format: JPG, PNG, WEBP. Maksimal 5MB.</small>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i>
                        Simpan Laporan
                    </button>
                    <a href="index.php?page=dashboard" class="btn btn-outline">
                        <i class="fas fa-times"></i>
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

<script>
// Toast notification system
function showToast(message, type = 'success', duration = 5000) {
    const container = document.getElementById('toast-container');

    // Remove existing toasts
    const existingToasts = container.querySelectorAll('.toast');
    existingToasts.forEach(toast => toast.remove());

    // Create new toast
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;

    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas ${icon}"></i>
            <span>${message}</span>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
    `;

    container.appendChild(toast);

    // Auto remove after duration
    setTimeout(() => {
        if (toast.parentElement) {
            toast.classList.add('hide');
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }
    }, duration);
}

// Check for success/error messages on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check for success message
    const successAlert = document.querySelector('.alert-success');
    if (successAlert) {
        const message = successAlert.textContent.trim();
        showToast(message, 'success');
    }

    // Check for error message
    const errorAlert = document.querySelector('.alert-error');
    if (errorAlert) {
        const message = errorAlert.textContent.trim();
        showToast(message, 'error');
    }
});

// Ensure DOM is ready before adding event listeners
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    console.log('JavaScript loaded successfully');
    console.log('Form element:', document.getElementById('caseForm'));

    // Test if form exists
    const form = document.getElementById('caseForm');
    if (!form) {
        console.error('Form with ID "caseForm" not found!');
        alert('ERROR: Form not found!');
        return;
    }

    // Button click handler for AJAX submission
    document.getElementById('submitBtn').addEventListener('click', function(e) {
        console.log('Submit button clicked!');

        // Validate form fields
        const title = document.getElementById('title').value.trim();
        const description = document.getElementById('description').value.trim();
        const location = document.getElementById('location').value.trim();

        if (!title || !description || !location) {
            showToast('Semua field wajib diisi!', 'error');
            return false;
        }

        // Show loading state
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        submitBtn.disabled = true;
        submitBtn.classList.add('loading');

        // Prepare form data
        const formData = new FormData(document.getElementById('caseForm'));

        // AJAX call to MemberCasesController
        fetch(window.location.href, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(data => {
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            submitBtn.classList.remove('loading');

            // Parse response to find success/error messages
            const parser = new DOMParser();
            const doc = parser.parseFromString(data, 'text/html');

            const successAlert = doc.querySelector('.alert-success');
            const errorAlert = doc.querySelector('.alert-error');

            if (successAlert) {
                const message = successAlert.textContent.trim();
                showToast(message, 'success');

                // Clear form on success
                document.getElementById('caseForm').reset();

                // Optional: redirect after success
                // setTimeout(() => window.location.href = 'index.php?page=member/cases', 2000);
            } else if (errorAlert) {
                const message = errorAlert.textContent.trim();
                showToast(message, 'error');
            } else {
                showToast('Terjadi kesalahan sistem', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan jaringan', 'error');

            // Reset button on error
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            submitBtn.classList.remove('loading');
        });
    });

    // Auto-resize textarea
    document.getElementById('description').addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });

    // Image validation
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                showToast('Ukuran gambar maksimal 5MB!', 'error');
                this.value = '';
                return;
            }

            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                showToast('Format gambar harus JPG, PNG, atau WEBP!', 'error');
                this.value = '';
                return;
            }

            // Show success message for valid file
            showToast(`Gambar "${file.name}" siap diupload`, 'success', 2000);
        }
    });

    // Form field validation on blur
    document.querySelectorAll('input[required], textarea[required]').forEach(field => {
        field.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                this.style.borderColor = '#ef4444';
                this.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
            } else {
                this.style.borderColor = '#10b981';
                this.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.1)';
            }
        });

        field.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                this.style.borderColor = '#10b981';
                this.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.1)';
            }
        });
    });

    console.log('All event listeners registered successfully');
}); // Close DOMContentLoaded
</script>
</body>
</html>
<body>
    <!-- Header -->
    <div class="member-header">
        <a href="index.php?page=dashboard" class="back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1>Buat Laporan Baru</h1>
        <p>Laporkan masalah atau keluhan Anda</p>
    </div>
    
    <div class="form-container">
        <!-- Toast Notification Container -->
        <div id="toast-container" class="toast-container"></div>

        <!-- Alert Messages (fallback) -->
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


