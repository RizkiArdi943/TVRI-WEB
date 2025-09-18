<?php
class DatabaseMigrator {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host=localhost;charset=utf8mb4",
                'root',
                'password',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function run() {
        echo "Starting database migration...\n";

        try {
            $this->createDatabase();
            $this->createTables();
            $this->insertData();

            echo "Database migration completed successfully!\n";
        } catch (Exception $e) {
            echo "Error during migration: " . $e->getMessage() . "\n";
        }
    }

    private function createDatabase() {
        echo "Creating database...\n";

        $sql = "CREATE DATABASE IF NOT EXISTS tvri_ticketing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $this->pdo->exec($sql);

        // Switch to the new database
        $this->pdo->exec("USE tvri_ticketing");
    }

    private function createTables() {
        echo "Creating tables...\n";

        $tables = [
            "CREATE TABLE IF NOT EXISTS users (
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
            )",

            "CREATE TABLE IF NOT EXISTS categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL,
                color VARCHAR(7) NOT NULL DEFAULT '#3B82F6',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_name (name)
            )",

            "CREATE TABLE IF NOT EXISTS cases (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                location VARCHAR(100) NOT NULL,
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
            )",

            "CREATE TABLE IF NOT EXISTS settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(50) NOT NULL UNIQUE,
                setting_value TEXT NOT NULL,
                description VARCHAR(255) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_setting_key (setting_key)
            )"
        ];

        foreach ($tables as $table) {
            $this->pdo->exec($table);
        }
    }

    private function insertData() {
        echo "Inserting initial data...\n";

        // Insert default admin user
        $stmt = $this->pdo->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE username=username");
        $stmt->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'Administrator', 'admin@tvri.id', 'admin']);

        // Insert categories
        $categories = [
            ['Transmisi', '#3B82F6'],
            ['Studio', '#10B981'],
            ['Perangkat', '#F59E0B'],
            ['Jaringan', '#EF4444'],
            ['Lainnya', '#8B5CF6']
        ];

        $stmt = $this->pdo->prepare("INSERT INTO categories (name, color) VALUES (?, ?) ON DUPLICATE KEY UPDATE name=name");
        foreach ($categories as $category) {
            $stmt->execute($category);
        }

        // Insert settings
        $settings = [
            ['system_name', 'TVRI Kalimantan Tengah', 'Nama sistem'],
            ['system_version', '1.0.0', 'Versi aplikasi'],
            ['max_file_size', '5242880', 'Maksimal ukuran file upload (5MB)'],
            ['allowed_file_types', 'jpg,jpeg,png,pdf,doc,docx', 'Tipe file yang diizinkan']
        ];

        $stmt = $this->pdo->prepare("INSERT INTO settings (setting_key, setting_value, description) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_key=setting_key");
        foreach ($settings as $setting) {
            $stmt->execute($setting);
        }

        // Insert sample cases
        $cases = [
            ['Gangguan Sinyal Transmisi', 'Sinyal transmisi terputus-putus di area Palangkaraya. Kualitas gambar tidak stabil dan sering terjadi gangguan.', 'Palangkaraya', 1, 'pending', 'high', 1],
            ['Maintenance Studio A', 'Pembersihan dan maintenance rutin studio A. Perlu pengecekan peralatan audio dan video.', 'Studio A', 2, 'in_progress', 'medium', 1],
            ['Update Software Encoder', 'Update software encoder untuk meningkatkan kualitas siaran. Perlu restart sistem setelah update.', 'Control Room', 3, 'completed', 'low', 1],
            ['Gangguan Jaringan Internet', 'Koneksi internet tidak stabil di area kantor. Perlu pengecekan router dan switch.', 'Kantor TVRI', 4, 'pending', 'high', 1],
            ['Perbaikan Antena Transmisi', 'Antena transmisi di menara utama mengalami kerusakan. Perlu penggantian komponen.', 'Menara Transmisi', 1, 'in_progress', 'high', 1],
            ['Pembersihan Kamera Studio', 'Pembersihan lensa dan body kamera studio B. Perlu pengecekan autofocus.', 'Studio B', 2, 'completed', 'low', 1],
            ['Upgrade RAM Server', 'Upgrade RAM server untuk meningkatkan performa sistem. Perlu shutdown server sementara.', 'Server Room', 3, 'pending', 'medium', 1],
            ['Perbaikan AC Control Room', 'AC di control room tidak dingin. Perlu pengecekan freon dan filter.', 'Control Room', 5, 'in_progress', 'medium', 1]
        ];

        $stmt = $this->pdo->prepare("INSERT INTO cases (title, description, location, category_id, status, priority, reported_by) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title=title");
        foreach ($cases as $case) {
            $stmt->execute($case);
        }
    }
}

// Run the migration
if ($argc > 1 && $argv[1] === '--run') {
    $migrator = new DatabaseMigrator();
    $migrator->run();
} else {
    echo "Usage: php migrate.php --run\n";
    echo "This will create the database and tables for the TVRI Ticketing system.\n";
}
?>
