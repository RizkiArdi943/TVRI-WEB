-- TVRI Ticketing System Database Migration
-- Database: tvri_ticketing
-- Created: <?php echo date('Y-m-d H:i:s'); ?>

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS tvri_ticketing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tvri_ticketing;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    color VARCHAR(7) NOT NULL DEFAULT '#3B82F6',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name)
);

-- Cases table
CREATE TABLE IF NOT EXISTS cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    equipment_name VARCHAR(255) NOT NULL,
    model VARCHAR(255) NULL,
    serial_number VARCHAR(255) NULL,
    damage_date DATE NOT NULL,
    location VARCHAR(100) NOT NULL,
    damage_condition ENUM('light', 'moderate', 'severe') DEFAULT 'light',
    category_id INT NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    reported_by INT NOT NULL,
    assigned_to INT NULL,
    image_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_category (category_id),
    INDEX idx_reported_by (reported_by),
    INDEX idx_created_at (created_at)
);

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
);

-- Insert default admin user
-- Password: admin123 (hashed with bcrypt)
INSERT INTO users (username, password, full_name, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@tvri.id', 'admin')
ON DUPLICATE KEY UPDATE username=username;

-- Insert default categories
INSERT INTO categories (name, color) VALUES
('Transmisi', '#3B82F6'),
('Studio', '#10B981'),
('Perangkat', '#F59E0B'),
('Jaringan', '#EF4444'),
('Lainnya', '#8B5CF6')
ON DUPLICATE KEY UPDATE name=name;

-- Insert sample cases
INSERT INTO cases (title, description, equipment_name, model, serial_number, damage_date, location, damage_condition, category_id, status, priority, reported_by) VALUES
('Gangguan Sinyal Transmisi', 'Sinyal transmisi terputus-putus di area Palangkaraya. Kualitas gambar tidak stabil dan sering terjadi gangguan.', 'Transmitter', 'TX-2000', 'SN001', '2024-01-15', 'Transmisi Palangkaraya', 'severe', 1, 'pending', 'high', 1),
('Maintenance Studio A', 'Pembersihan dan maintenance rutin studio A. Perlu pengecekan peralatan audio dan video.', 'Audio Mixer', 'MX-500', 'SN002', '2024-01-16', 'Transmisi Sampit', 'light', 2, 'in_progress', 'medium', 1),
('Update Software Encoder', 'Update software encoder untuk meningkatkan kualitas siaran. Perlu restart sistem setelah update.', 'Encoder', 'ENC-100', 'SN003', '2024-01-17', 'Transmisi Pangkalanbun', 'moderate', 3, 'completed', 'low', 1),
('Gangguan Jaringan Internet', 'Koneksi internet tidak stabil di area kantor. Perlu pengecekan router dan switch.', 'Router', 'RT-3000', 'SN004', '2024-01-18', 'Transmisi Palangkaraya', 'severe', 4, 'pending', 'high', 1),
('Perbaikan Antena Transmisi', 'Antena transmisi di menara utama mengalami kerusakan. Perlu penggantian komponen.', 'Antena', 'ANT-500', 'SN005', '2024-01-19', 'Transmisi Sampit', 'severe', 1, 'in_progress', 'high', 1),
('Pembersihan Kamera Studio', 'Pembersihan lensa dan body kamera studio B. Perlu pengecekan autofocus.', 'Kamera', 'CAM-200', 'SN006', '2024-01-20', 'Transmisi Pangkalanbun', 'light', 2, 'completed', 'low', 1),
('Upgrade RAM Server', 'Upgrade RAM server untuk meningkatkan performa sistem. Perlu shutdown server sementara.', 'Server', 'SRV-1000', 'SN007', '2024-01-21', 'Transmisi Palangkaraya', 'moderate', 3, 'pending', 'medium', 1),
('Perbaikan AC Control Room', 'AC di control room tidak dingin. Perlu pengecekan freon dan filter.', 'AC Unit', 'AC-5000', 'SN008', '2024-01-22', 'Transmisi Sampit', 'moderate', 5, 'in_progress', 'medium', 1)
ON DUPLICATE KEY UPDATE title=title;

-- Insert system settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('system_name', 'TVRI Kalimantan Tengah', 'Nama sistem'),
('system_version', '1.0.0', 'Versi aplikasi'),
('max_file_size', '5242880', 'Maksimal ukuran file upload (5MB)'),
('allowed_file_types', 'jpg,jpeg,png,pdf,doc,docx', 'Tipe file yang diizinkan')
ON DUPLICATE KEY UPDATE setting_key=setting_key;

-- Insert additional sample users (optional)
INSERT INTO users (username, password, full_name, email, role) VALUES
('operator1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Operator Studio', 'operator1@tvri.id', 'user'),
('technician1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Teknisi Transmisi', 'technician1@tvri.id', 'user')
ON DUPLICATE KEY UPDATE username=username;

-- Insert additional sample cases
INSERT INTO cases (title, description, equipment_name, model, serial_number, damage_date, location, damage_condition, category_id, status, priority, reported_by) VALUES
('Perbaikan Monitor Studio', 'Monitor studio C mengalami dead pixel. Perlu penggantian panel LCD.', 'Monitor', 'MON-24', 'SN009', '2024-01-23', 'Transmisi Pangkalanbun', 'moderate', 2, 'pending', 'medium', 2),
('Gangguan Audio Mixer', 'Audio mixer di studio A mengalami noise. Perlu pengecekan kabel dan connector.', 'Audio Mixer', 'MX-300', 'SN010', '2024-01-24', 'Transmisi Palangkaraya', 'severe', 2, 'in_progress', 'high', 2),
('Update Firmware Transmitter', 'Update firmware transmitter untuk memperbaiki stabilitas sinyal. Perlu maintenance window.', 'Transmitter', 'TX-1500', 'SN011', '2024-01-25', 'Transmisi Sampit', 'moderate', 1, 'pending', 'high', 3),
('Pembersihan Filter AC', 'Filter AC di server room kotor. Perlu pembersihan rutin untuk mencegah overheating.', 'AC Unit', 'AC-3000', 'SN012', '2024-01-26', 'Transmisi Pangkalanbun', 'light', 5, 'completed', 'low', 3),
('Perbaikan UPS', 'UPS di control room tidak berfungsi dengan baik. Perlu pengecekan battery dan inverter.', 'UPS', 'UPS-2000', 'SN013', '2024-01-27', 'Transmisi Palangkaraya', 'severe', 3, 'in_progress', 'high', 3)
ON DUPLICATE KEY UPDATE title=title;

-- End of initialization script
-- Database is now ready for use 