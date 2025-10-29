<?php
// Set timezone to WIB (Waktu Indonesia Barat)
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../../controllers/cases.php';

// Initialize controller
$casesController = new CasesController();

// Handle form submission
$result = $casesController->create();
$success = $result['success'];
$error = $result['error'];

// Check for success message from URL parameter (after redirect)
if (isset($_GET['success']) && empty($success)) {
    $success = urldecode($_GET['success']);
}

// Debug: Log request method and session status
error_log('=== CREATE FORM DEBUG ===');
error_log('Request Method: ' . $_SERVER['REQUEST_METHOD']);
error_log('Session User ID: ' . ($_SESSION['user_id'] ?? 'Not set'));
error_log('Success: ' . $success);
error_log('Error: ' . $error);

// Form processing is now handled by CasesController
?>

<div class="create-case-page">
    <div class="page-header">
        <h1>Tambah Laporan Baru</h1>
        <a href="index.php?page=cases" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i>
            Kembali
        </a>
    </div>

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
                    <label for="equipment_name">Nama Peralatan *</label>
                    <input type="text" id="equipment_name" name="equipment_name" value="<?php echo htmlspecialchars($_POST['equipment_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="model">Model</label>
                    <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($_POST['model'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="serial_number">S/N</label>
                    <input type="text" id="serial_number" name="serial_number" value="<?php echo htmlspecialchars($_POST['serial_number'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="damage_date">Tanggal Kerusakan *</label>
                    <input type="date" id="damage_date" name="damage_date" value="<?php echo htmlspecialchars($_POST['damage_date'] ?? date('Y-m-d')); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="location">Lokasi Transmisi Digital *</label>
                    <select id="location" name="location" required>
                        <option value="">Pilih lokasi transmisi</option>
                        <option value="Transmisi Palangkaraya" <?php echo ($_POST['location'] ?? '') === 'Transmisi Palangkaraya' ? 'selected' : ''; ?>>Transmisi Palangkaraya</option>
                        <option value="Transmisi Sampit" <?php echo ($_POST['location'] ?? '') === 'Transmisi Sampit' ? 'selected' : ''; ?>>Transmisi Sampit</option>
                        <option value="Transmisi Pangkalanbun" <?php echo ($_POST['location'] ?? '') === 'Transmisi Pangkalanbun' ? 'selected' : ''; ?>>Transmisi Pangkalanbun</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="damage_condition">Kondisi Kerusakan *</label>
                    <select id="damage_condition" name="damage_condition" required>
                        <option value="">Pilih kondisi kerusakan</option>
                        <option value="light" <?php echo ($_POST['damage_condition'] ?? '') === 'light' ? 'selected' : ''; ?>>Rusak Ringan</option>
                        <option value="moderate" <?php echo ($_POST['damage_condition'] ?? '') === 'moderate' ? 'selected' : ''; ?>>Rusak Sedang</option>
                        <option value="severe" <?php echo ($_POST['damage_condition'] ?? '') === 'severe' ? 'selected' : ''; ?>>Rusak Berat</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="image">Gambar *</label>
                <input type="file" id="image" name="image" accept="image/*" required>
                <small class="form-hint">Format: JPG, PNG, WEBP. Maksimal 5MB.</small>
                
                <!-- Image Preview Container -->
                <div id="imagePreview" class="image-preview" style="display: none;">
                    <div class="preview-container">
                        <img id="previewImg" src="" alt="Preview" style="max-width: 100%; max-height: 200px; border-radius: 8px; margin-top: 10px;">
                        <div class="preview-info">
                            <span id="fileName"></span>
                            <span id="fileSize"></span>
                        </div>
                        <button type="button" id="removeImage" class="btn-remove-image">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i>
                    Simpan Laporan
                </button>
                <a href="index.php?page=cases" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<style>
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

/* Enhanced form styles */
.form-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    margin-bottom: 20px;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
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

/* Image Preview Styles */
.image-preview {
    margin-top: 10px;
    padding: 15px;
    background: #f8fafc;
    border: 2px dashed #e2e8f0;
    border-radius: 8px;
    position: relative;
}

.preview-container {
    position: relative;
    display: inline-block;
}

.preview-info {
    margin-top: 8px;
    font-size: 12px;
    color: #64748b;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.btn-remove-image {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    transition: all 0.2s ease;
}

.btn-remove-image:hover {
    background: #dc2626;
    transform: scale(1.1);
}

/* Form hint styling */
.form-hint {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: #6b7280;
}
</style>

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
        showToast(message, 'success', 3000);
        
        // Clear form completely on page load if success
        setTimeout(() => {
            document.getElementById('caseForm').reset();
            document.getElementById('imagePreview').style.display = 'none';
            document.querySelectorAll('input, textarea, select').forEach(field => {
                field.style.borderColor = '';
                field.style.boxShadow = '';
            });
        }, 100);
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
        const equipmentName = document.getElementById('equipment_name').value.trim();
        const damageDate = document.getElementById('damage_date').value.trim();
        const location = document.getElementById('location').value.trim();
        const damageCondition = document.getElementById('damage_condition').value.trim();
        const image = document.getElementById('image').files[0];

        if (!title || !description || !equipmentName || !damageDate || !location || !damageCondition || !image) {
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

        // AJAX call to CasesController
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
                
                // Hide image preview
                document.getElementById('imagePreview').style.display = 'none';
                
                // Reset form field styles
                document.querySelectorAll('input, textarea, select').forEach(field => {
                    field.style.borderColor = '';
                    field.style.boxShadow = '';
                });

                // Refresh page after 2 seconds to ensure clean state
                setTimeout(() => {
                    window.location.href = 'index.php?page=cases/create';
                }, 2000);
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

    // Image validation and preview
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        
        if (file) {
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                showToast('Ukuran gambar maksimal 5MB!', 'error');
                this.value = '';
                preview.style.display = 'none';
                return;
            }

            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                showToast('Format gambar harus JPG, PNG, atau WEBP!', 'error');
                this.value = '';
                preview.style.display = 'none';
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                fileName.textContent = file.name;
                fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);

            // Show success message for valid file
            showToast(`Gambar "${file.name}" siap diupload`, 'success', 2000);
        } else {
            preview.style.display = 'none';
        }
    });

    // Remove image functionality
    document.getElementById('removeImage').addEventListener('click', function() {
        document.getElementById('image').value = '';
        document.getElementById('imagePreview').style.display = 'none';
        showToast('Gambar dihapus', 'success', 2000);
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