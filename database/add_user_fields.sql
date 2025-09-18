-- Add employee_id and department columns to users table
-- Run this migration to support member registration

USE tvri_ticketing;

-- Add employee_id column
ALTER TABLE users ADD COLUMN employee_id VARCHAR(20) NULL AFTER email;
ALTER TABLE users ADD UNIQUE INDEX idx_employee_id (employee_id);

-- Add department column
ALTER TABLE users ADD COLUMN department VARCHAR(100) NULL AFTER employee_id;

-- Update existing admin user with employee_id
UPDATE users SET employee_id = 'ADMIN001', department = 'Administrasi' WHERE username = 'admin';

-- Add sample employee users
INSERT INTO users (username, password, full_name, email, employee_id, department, role) VALUES
('operator1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Operator Studio', 'operator1@tvri.id', 'TVRI001', 'Studio', 'user'),
('technician1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Teknisi Transmisi', 'technician1@tvri.id', 'TVRI002', 'Teknik Transmisi', 'user'),
('it_staff1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff IT', 'it_staff1@tvri.id', 'TVRI003', 'IT & Sistem', 'user')
ON DUPLICATE KEY UPDATE username=username;
