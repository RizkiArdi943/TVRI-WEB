<?php
require_once __DIR__ . '/../../controllers/users.php';

// Initialize controller
$usersController = new UsersController();

// Get users
$users = $usersController->index();
?>

<div class="users-page">
    <div class="page-header">
        <h1>Manajemen User</h1>
        <button onclick="showAddUserModal()" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Tambah User
        </button>
    </div>
    
    <!-- DEBUG: File updated at <?php echo date('Y-m-d H:i:s'); ?> -->
    <div style="background: #ffebee; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #f44336;">
        <strong>🚨 CRITICAL CACHE ISSUE DETECTED!</strong><br>
        Jika Anda masih melihat semua role sebagai "ADMINISTRATOR", ini adalah masalah cache browser.<br>
        <strong>SOLUSI:</strong>
        <ol>
            <li><strong>Hard Refresh:</strong> Tekan <code>Ctrl + F5</code> (Windows) atau <code>Cmd + Shift + R</code> (Mac)</li>
            <li><strong>Clear Cache:</strong> Buka Developer Tools (F12) → Network tab → Check "Disable cache"</li>
            <li><strong>Private Mode:</strong> Buka halaman di Incognito/Private window</li>
        </ol>
        <br><small>File terakhir diupdate: <?php echo date('Y-m-d H:i:s'); ?> | Timestamp: <?php echo time(); ?></small>
    </div>
    
    <script>
        // Force cache bust
        console.log('🔄 Cache bust applied at:', new Date().toISOString());
        console.log('🔄 Timestamp:', <?php echo time(); ?>);
    </script>

    <!-- Users List -->
    <div class="users-list">
        <?php if (empty($users)): ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>Tidak ada user ditemukan</h3>
                <p>Tambah user baru untuk memulai</p>
                <button onclick="showAddUserModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Tambah User
                </button>
            </div>
        <?php else: ?>
            <?php foreach ($users as $user): ?>
                <div class="user-card">
                    <div class="user-info">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="user-details">
                            <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                            <p class="user-username">@<?php echo htmlspecialchars($user['username']); ?></p>
                            <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>
                    
                    <div class="user-meta">
                        <div class="user-role role-<?php echo $user['role']; ?>" data-role="<?php echo $user['role']; ?>" data-timestamp="<?php echo time(); ?>">
                            <?php 
                            // Debug: Show actual role value
                            echo "<!-- DEBUG: role = '" . $user['role'] . "' (ID: " . $user['id'] . ") -->";
                            
                            // Force role display with explicit logic
                            $roleDisplay = '';
                            if ($user['role'] === 'admin') {
                                $roleDisplay = 'Administrator';
                            } else {
                                $roleDisplay = 'User';
                            }
                            echo $roleDisplay;
                            ?>
                        </div>
                        <div class="user-department">
                            <i class="fas fa-building"></i>
                            <?php echo htmlspecialchars($user['department'] ?? 'Umum'); ?>
                        </div>
                        <div class="user-date">
                            <i class="fas fa-calendar"></i>
                            <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                        </div>
                    </div>
                    
                    <div class="user-actions">
                        <button onclick="editUser(<?php echo $user['id']; ?>)" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
                            Edit
                        </button>
                        <?php if ($user['role'] !== 'admin' || count($users) > 1): ?>
                        <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                            Hapus
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Tambah User Baru</h2>
            <button onclick="closeUserModal()" class="btn-close">&times;</button>
        </div>
        
        <form id="userForm" onsubmit="submitUserForm(event)">
            <div class="form-group">
                <label for="username">Username *</label>
                <input type="text" id="username" name="username" required>
                <div class="error-message" id="username-error"></div>
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required>
                <div class="error-message" id="email-error"></div>
            </div>
            
            <div class="form-group">
                <label for="full_name">Nama Lengkap *</label>
                <input type="text" id="full_name" name="full_name" required>
                <div class="error-message" id="full_name-error"></div>
            </div>
            
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required>
                <div class="error-message" id="password-error"></div>
            </div>
            
            <div class="form-group">
                <label for="role">Role *</label>
                <select id="role" name="role" required onchange="console.log('Role changed to:', this.value)">
                    <option value="">Pilih Role</option>
                    <option value="admin">Administrator</option>
                    <option value="user">User</option>
                </select>
                <div class="error-message" id="role-error"></div>
                <small class="form-help">Pilih "User" untuk user biasa atau "Administrator" untuk admin</small>
            </div>
            
            
            <div class="form-actions">
                <button type="button" onclick="closeUserModal()" class="btn btn-outline">
                    Batal
                </button>
                <button type="button" id="submitBtn" class="btn btn-primary" onclick="manualFormSubmit()">
                    <i class="fas fa-save"></i>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.users-page {
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.page-header h1 {
    color: #1f2937;
    font-size: 28px;
    font-weight: 700;
}

.users-list {
    display: grid;
    gap: 20px;
}

.user-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.2s ease;
}

.user-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-avatar {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
}

.user-details h3 {
    margin: 0 0 5px 0;
    color: #1f2937;
    font-size: 18px;
    font-weight: 600;
}

.user-username {
    margin: 0 0 5px 0;
    color: #6b7280;
    font-size: 14px;
}

.user-email {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}

.user-meta {
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: flex-end;
    min-width: 200px;
}

.user-role {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    text-align: center;
    min-width: 100px;
    display: inline-block;
}

.role-admin {
    background: #fef3c7;
    color: #d97706;
    border: 1px solid #f59e0b;
}

.role-user {
    background: #dbeafe;
    color: #3b82f6;
    border: 1px solid #3b82f6;
}

.user-department {
    color: #6b7280;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 5px;
    justify-content: flex-end;
    min-width: 100px;
}

.user-date {
    color: #6b7280;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 5px;
    justify-content: flex-end;
    min-width: 100px;
}

.user-actions {
    display: flex;
    gap: 10px;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.modal-header h2 {
    margin: 0;
    color: #1f2937;
    font-size: 20px;
    font-weight: 600;
}

.btn-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #6b7280;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-close:hover {
    color: #374151;
}

#userForm {
    padding: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    color: #374151;
    font-weight: 500;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s ease;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #3b82f6;
}

.error-message {
    color: #dc2626;
    font-size: 12px;
    margin-top: 5px;
    display: none;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.empty-state i {
    font-size: 48px;
    color: #9ca3af;
    margin-bottom: 20px;
}

.empty-state h3 {
    color: #374151;
    margin-bottom: 10px;
}

.empty-state p {
    color: #6b7280;
    margin-bottom: 20px;
}

/* Toast Notifications */
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    padding: 20px;
    z-index: 10000;
    transform: translateX(100%);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    min-width: 350px;
    border: 1px solid #e5e7eb;
}

.toast.show {
    transform: translateX(0);
    animation: slideInRight 0.4s ease-out;
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

.toast-content {
    display: flex;
    align-items: center;
    gap: 12px;
}

.toast-content i {
    font-size: 18px;
}

.toast-close {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: #6b7280;
    margin-left: auto;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.toast-close:hover {
    color: #374151;
}

.toast-success {
    border-left: 4px solid #10b981;
}

.toast-success i {
    color: #10b981;
}

.toast-error {
    border-left: 4px solid #ef4444;
}

.toast-error i {
    color: #ef4444;
}

.toast-info {
    border-left: 4px solid #3b82f6;
}

.toast-info i {
    color: #3b82f6;
}

/* Ensure form elements are clickable */
.btn-primary {
    cursor: pointer !important;
    pointer-events: auto !important;
}

.btn-primary:disabled {
    cursor: not-allowed !important;
    opacity: 0.6;
}

/* Debug styles */
#submitBtn {
    position: relative;
    z-index: 1000;
}

.form-help {
    color: #6b7280;
    font-size: 12px;
    margin-top: 5px;
    display: block;
}
</style>

<script>
let currentUserId = null;

function showAddUserModal() {
    currentUserId = null;
    document.getElementById('modalTitle').textContent = 'Tambah User Baru';
    document.getElementById('userForm').reset();
    clearErrors();
    document.getElementById('userModal').style.display = 'block';
    
    // Show info toast
    showToast('📝 Form tambah user dibuka. Silakan isi data user baru dan pilih role.', 'info');
    
    // Debug: Log form elements
    console.log('🔍 Form elements check:');
    console.log('- Form:', document.getElementById('userForm'));
    console.log('- Submit button:', document.getElementById('submitBtn'));
    console.log('- Username field:', document.getElementById('username'));
    console.log('- Role field:', document.getElementById('role'));
}

function editUser(userId) {
    console.log('🔧 Edit user triggered for ID:', userId);
    
    // Show loading
    showToast('⏳ Mengambil data user...', 'info');
    
    // Get user data first
    fetch(`controllers/user_api.php?action=get&id=${userId}`)
    .then(response => {
        console.log('Edit user response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(result => {
        console.log('Edit user result:', result);
        if (result.success) {
            const user = result.user;
            
            // Fill form with user data
            document.getElementById('username').value = user.username;
            document.getElementById('email').value = user.email;
            document.getElementById('full_name').value = user.full_name;
            document.getElementById('password').value = ''; // Clear password
            document.getElementById('role').value = user.role;
            
            // Update modal title and form action
            document.getElementById('modalTitle').textContent = 'Edit User';
            currentUserId = userId;
            
            // Show modal
            document.getElementById('userModal').style.display = 'block';
            clearErrors();
            
            showToast('✅ Data user berhasil dimuat', 'success');
        } else {
            showToast('❌ Gagal mengambil data user: ' + result.message, 'error');
        }
    })
    .catch(error => {
        console.error('Edit user error:', error);
        showToast('❌ Terjadi kesalahan saat mengambil data user: ' + error.message, 'error');
    });
}

function deleteUser(userId) {
    if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
        // Show loading
        showToast('Menghapus user...', 'info');
        
        // Delete user
        fetch(`controllers/user_api.php?action=delete&id=${userId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showToast(result.message, 'success');
                location.reload();
            } else {
                showToast(result.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat menghapus user', 'error');
        });
    }
}

function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
    currentUserId = null;
}

function manualFormSubmit() {
    console.log('🔧 Manual form submit triggered');
    
    const form = document.getElementById('userForm');
    if (!form) {
        console.error('❌ Form not found!');
        showToast('❌ Form tidak ditemukan', 'error');
        return;
    }
    
    // Get form data manually
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    console.log('Form data:', data);
    
    // Validate required fields
    if (!data.username || !data.email || !data.full_name || !data.password || !data.role) {
        showToast('❌ Semua field harus diisi', 'error');
        return;
    }
    
    // Show loading
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    submitBtn.disabled = true;
    
    showToast('⏳ Sedang menyimpan user...', 'info');
    
    // Determine URL
    let url = 'controllers/user_api.php';
    if (currentUserId) {
        url += `?action=update&id=${currentUserId}`;
    } else {
        url += `?action=create`;
    }
    
    console.log('Submitting to:', url);
    
    // Submit form
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(result => {
        console.log('Response result:', result);
        if (result.success) {
            showToast('🎉 User berhasil ditambahkan! Halaman akan dimuat ulang...', 'success');
            setTimeout(() => {
                closeUserModal();
                location.reload();
            }, 2000);
        } else {
            if (result.errors) {
                displayErrors(result.errors);
            } else {
                showToast('❌ ' + result.message, 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('❌ Terjadi kesalahan: ' + error.message, 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function submitUserForm(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());
    
    // Debug logging
    console.log('Form submission started');
    console.log('Form data:', data);
    console.log('Current user ID:', currentUserId);
    
    // Log form data entries
    console.log('FormData entries:');
    for (let [key, value] of formData.entries()) {
        console.log(`  ${key}: ${value}`);
    }
    
    // Specifically check role value
    const roleValue = formData.get('role');
    console.log('Role value from form:', roleValue);
    console.log('Role value type:', typeof roleValue);
    
    // Validate role selection
    if (!roleValue || roleValue === '') {
        console.error('❌ Role not selected!');
        showToast('❌ Silakan pilih role user', 'error');
        return;
    }
    
    if (roleValue !== 'admin' && roleValue !== 'user') {
        console.error('❌ Invalid role value:', roleValue);
        showToast('❌ Role tidak valid', 'error');
        return;
    }
    
    console.log('✅ Role validation passed:', roleValue);
    
    // Clear previous errors
    clearErrors();
    
    // Show loading
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    submitBtn.disabled = true;
    
    // Show loading toast
    showToast('⏳ Sedang menyimpan user...', 'info');
    
    // Debug: Show form data in console
    console.log('=== FORM SUBMISSION DEBUG ===');
    console.log('URL:', url);
    console.log('Method: POST');
    console.log('FormData size:', formData.entries().length);
    
    // Determine URL based on whether we're editing or creating
    let url = 'index.php?page=users/create';
    if (currentUserId) {
        url += `&action=update&id=${currentUserId}`;
    }
    
    // Submit form
    console.log('Submitting to URL:', url);
    showToast('📤 Mengirim data user ke server...', 'info');
    
    // Debug: Show that we're about to submit
    console.log('🚀 About to submit form to:', url);
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        console.log('Response ok:', response.ok);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return response.text().then(text => {
            console.log('Raw response text:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text:', text);
                throw new Error('Invalid JSON response: ' + text);
            }
        });
    })
    .then(result => {
        console.log('Response result:', result);
        if (result.success) {
            showToast('🎉 User berhasil ditambahkan! Halaman akan dimuat ulang...', 'success');
            setTimeout(() => {
                closeUserModal();
                location.reload();
            }, 2000);
        } else {
            if (result.errors) {
                displayErrors(result.errors);
            } else {
                showToast('❌ ' + result.message, 'error');
            }
        }
    })
    .catch(error => {
        console.error('🚨 FETCH ERROR DETECTED:', error);
        console.error('Error details:', error.message);
        console.error('Error stack:', error.stack);
        console.error('Error name:', error.name);
        console.error('Error cause:', error.cause);
        
        // Show detailed error toast
        showToast('❌ Error: ' + error.message + ' (Check console for details)', 'error');
        
        // Also show alert for debugging
        alert('JavaScript Error: ' + error.message + '\n\nCheck browser console for full details.');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function clearErrors() {
    const errorElements = document.querySelectorAll('.error-message');
    errorElements.forEach(element => {
        element.style.display = 'none';
        element.textContent = '';
    });
}

function displayErrors(errors) {
    Object.keys(errors).forEach(field => {
        const errorElement = document.getElementById(field + '-error');
        if (errorElement) {
            errorElement.textContent = errors[field];
            errorElement.style.display = 'block';
        }
    });
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('userModal');
    if (event.target === modal) {
        closeUserModal();
    }
}

// Add form event listener
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM Content Loaded - Setting up form listeners');
    
    const userForm = document.getElementById('userForm');
    if (userForm) {
        console.log('✅ Form found, adding event listener');
        userForm.addEventListener('submit', function(event) {
            console.log('🎯 Form submit event triggered');
            console.log('Event target:', event.target);
            console.log('Event type:', event.type);
            submitUserForm(event);
        });
        
        // Also add click listener to submit button
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            console.log('✅ Submit button found, adding click listener');
            submitBtn.addEventListener('click', function(event) {
                console.log('🖱️ Submit button clicked');
                // Don't prevent default here, let form handle it
            });
        } else {
            console.log('❌ Submit button not found!');
        }
    } else {
        console.log('❌ Form not found!');
    }
});

// Toast notification function
function showToast(message, type = 'info') {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast');
    existingToasts.forEach(toast => toast.remove());
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="toast-close">&times;</button>
        </div>
    `;
    
    // Add to body
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => toast.classList.add('show'), 100);
    
    // Remove toast after 5 seconds (longer for success messages)
    const duration = type === 'success' ? 5000 : 4000;
    setTimeout(() => {
        if (toast.parentElement) {
            toast.classList.remove('show');
            setTimeout(() => {
                if (toast.parentElement) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }
    }, duration);
}
</script>
