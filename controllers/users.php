<?php
/**
 * Controller untuk manajemen user
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/browser_auth.php';

class UsersController {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Tampilkan daftar semua user
     */
    public function index() {
        $users = $this->db->findAll('users');
        
        // Tambahkan informasi tambahan untuk setiap user
        foreach ($users as $index => $user) {
            $users[$index]['role_label'] = $this->getRoleLabel($user['role']);
        }
        
        return $users;
    }
    
    /**
     * Tambah user baru
     */
    public function create($data) {
        // Debug: log data yang diterima
        error_log('User data received: ' . print_r($data, true));
        
        // Validasi input
        $errors = $this->validateUserData($data);
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }
        
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Remove fields that don't exist in database
        unset($data['status']);
        
        // Set default values
        $data['employee_id'] = $data['employee_id'] ?? 'EMP' . time();
        $data['department'] = $data['department'] ?? 'Umum';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // Insert ke database
        $result = $this->db->insert('users', $data);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'User berhasil ditambahkan',
                'user_id' => $result
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal menambahkan user'
            ];
        }
    }
    
    /**
     * Update user
     */
    public function update($id, $data) {
        // Validasi input
        $errors = $this->validateUserData($data, $id);
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }
        
        // Jika ada password baru, hash password
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            // Hapus password dari data jika kosong
            unset($data['password']);
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // Update database
        $result = $this->db->update('users', $data, $id);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'User berhasil diperbarui'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal memperbarui user'
            ];
        }
    }
    
    /**
     * Hapus user
     */
    public function delete($id) {
        // Cek apakah user adalah admin terakhir
        $adminCount = $this->db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
        $user = $this->db->find('users', $id);
        
        if ($user && $user['role'] === 'admin' && $adminCount[0]['count'] <= 1) {
            return [
                'success' => false,
                'message' => 'Tidak dapat menghapus admin terakhir'
            ];
        }
        
        $result = $this->db->delete('users', $id);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'User berhasil dihapus'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal menghapus user'
            ];
        }
    }
    
    /**
     * Validasi data user
     */
    private function validateUserData($data, $excludeId = null) {
        $errors = [];
        
        // Validasi username
        if (empty($data['username'])) {
            $errors['username'] = 'Username harus diisi';
        } elseif (strlen($data['username']) < 3) {
            $errors['username'] = 'Username minimal 3 karakter';
        } else {
            // Cek username unik
            $existingUser = $this->db->query("SELECT id FROM users WHERE username = ? AND id != ?", [$data['username'], $excludeId ?? 0]);
            if (!empty($existingUser)) {
                $errors['username'] = 'Username sudah digunakan';
            }
        }
        
        // Validasi email
        if (empty($data['email'])) {
            $errors['email'] = 'Email harus diisi';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid';
        } else {
            // Cek email unik
            $existingUser = $this->db->query("SELECT id FROM users WHERE email = ? AND id != ?", [$data['email'], $excludeId ?? 0]);
            if (!empty($existingUser)) {
                $errors['email'] = 'Email sudah digunakan';
            }
        }
        
        // Validasi full name
        if (empty($data['full_name'])) {
            $errors['full_name'] = 'Nama lengkap harus diisi';
        }
        
        // Validasi password (hanya untuk create atau jika password diisi)
        if (empty($excludeId) || !empty($data['password'])) {
            if (empty($data['password'])) {
                $errors['password'] = 'Password harus diisi';
            } elseif (strlen($data['password']) < 6) {
                $errors['password'] = 'Password minimal 6 karakter';
            }
        }
        
        // Validasi role
        if (empty($data['role'])) {
            $errors['role'] = 'Role harus dipilih';
        } elseif (!in_array($data['role'], ['admin', 'user'])) {
            $errors['role'] = 'Role tidak valid';
        }
        
        return $errors;
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($id) {
        $user = $this->db->find('users', $id);
        if ($user) {
            $user['role_label'] = $this->getRoleLabel($user['role']);
        }
        return $user;
    }
    
    /**
     * Get role label
     */
    private function getRoleLabel($role) {
        $labels = [
            'admin' => 'Administrator',
            'user' => 'User'
        ];
        return $labels[$role] ?? $role;
    }
    
}
?>
