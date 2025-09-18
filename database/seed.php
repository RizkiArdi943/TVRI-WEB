<?php
require_once '../config/database.php';

class DatabaseSeeder {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function run() {
        echo "Starting database seeding...\n";

        try {
            $this->seedUsers();
            $this->seedCategories();
            $this->seedCases();
            $this->seedSettings();

            echo "Database seeding completed successfully!\n";
        } catch (Exception $e) {
            echo "Error during seeding: " . $e->getMessage() . "\n";
        }
    }

    private function seedUsers() {
        echo "Seeding users...\n";

        $users = [
            [
                'username' => 'admin',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'full_name' => 'Administrator',
                'email' => 'admin@tvri.id',
                'role' => 'admin'
            ],
            [
                'username' => 'operator1',
                'password' => password_hash('operator123', PASSWORD_DEFAULT),
                'full_name' => 'Operator Studio',
                'email' => 'operator1@tvri.id',
                'role' => 'user'
            ],
            [
                'username' => 'technician1',
                'password' => password_hash('tech123', PASSWORD_DEFAULT),
                'full_name' => 'Teknisi Transmisi',
                'email' => 'technician1@tvri.id',
                'role' => 'user'
            ]
        ];

        foreach ($users as $user) {
            $this->db->insert('users', $user);
        }
    }

    private function seedCategories() {
        echo "Seeding categories...\n";

        $categories = [
            ['name' => 'Transmisi', 'color' => '#3B82F6'],
            ['name' => 'Studio', 'color' => '#10B981'],
            ['name' => 'Perangkat', 'color' => '#F59E0B'],
            ['name' => 'Jaringan', 'color' => '#EF4444'],
            ['name' => 'Lainnya', 'color' => '#8B5CF6']
        ];

        foreach ($categories as $category) {
            $this->db->insert('categories', $category);
        }
    }

    private function seedCases() {
        echo "Seeding cases...\n";

        $cases = [
            [
                'title' => 'Gangguan Sinyal Transmisi',
                'description' => 'Sinyal transmisi terputus-putus di area Palangkaraya. Kualitas gambar tidak stabil dan sering terjadi gangguan.',
                'location' => 'Palangkaraya',
                'category_id' => 1,
                'status' => 'pending',
                'priority' => 'high',
                'reported_by' => 1,
                'assigned_to' => null
            ],
            [
                'title' => 'Maintenance Studio A',
                'description' => 'Pembersihan dan maintenance rutin studio A. Perlu pengecekan peralatan audio dan video.',
                'location' => 'Studio A',
                'category_id' => 2,
                'status' => 'in_progress',
                'priority' => 'medium',
                'reported_by' => 1,
                'assigned_to' => null
            ],
            [
                'title' => 'Update Software Encoder',
                'description' => 'Update software encoder untuk meningkatkan kualitas siaran. Perlu restart sistem setelah update.',
                'location' => 'Control Room',
                'category_id' => 3,
                'status' => 'completed',
                'priority' => 'low',
                'reported_by' => 1,
                'assigned_to' => null
            ],
            [
                'title' => 'Gangguan Jaringan Internet',
                'description' => 'Koneksi internet tidak stabil di area kantor. Perlu pengecekan router dan switch.',
                'location' => 'Kantor TVRI',
                'category_id' => 4,
                'status' => 'pending',
                'priority' => 'high',
                'reported_by' => 1,
                'assigned_to' => null
            ],
            [
                'title' => 'Perbaikan Antena Transmisi',
                'description' => 'Antena transmisi di menara utama mengalami kerusakan. Perlu penggantian komponen.',
                'location' => 'Menara Transmisi',
                'category_id' => 1,
                'status' => 'in_progress',
                'priority' => 'high',
                'reported_by' => 1,
                'assigned_to' => null
            ],
            [
                'title' => 'Pembersihan Kamera Studio',
                'description' => 'Pembersihan lensa dan body kamera studio B. Perlu pengecekan autofocus.',
                'location' => 'Studio B',
                'category_id' => 2,
                'status' => 'completed',
                'priority' => 'low',
                'reported_by' => 2,
                'assigned_to' => null
            ],
            [
                'title' => 'Upgrade RAM Server',
                'description' => 'Upgrade RAM server untuk meningkatkan performa sistem. Perlu shutdown server sementara.',
                'location' => 'Server Room',
                'category_id' => 3,
                'status' => 'pending',
                'priority' => 'medium',
                'reported_by' => 1,
                'assigned_to' => null
            ],
            [
                'title' => 'Perbaikan AC Control Room',
                'description' => 'AC di control room tidak dingin. Perlu pengecekan freon dan filter.',
                'location' => 'Control Room',
                'category_id' => 5,
                'status' => 'in_progress',
                'priority' => 'medium',
                'reported_by' => 1,
                'assigned_to' => null
            ]
        ];

        foreach ($cases as $case) {
            $this->db->insert('cases', $case);
        }
    }

    private function seedSettings() {
        echo "Seeding settings...\n";

        $settings = [
            ['setting_key' => 'system_name', 'setting_value' => 'TVRI Kalimantan Tengah', 'description' => 'Nama sistem'],
            ['setting_key' => 'system_version', 'setting_value' => '1.0.0', 'description' => 'Versi aplikasi'],
            ['setting_key' => 'max_file_size', 'setting_value' => '5242880', 'description' => 'Maksimal ukuran file upload (5MB)'],
            ['setting_key' => 'allowed_file_types', 'setting_value' => 'jpg,jpeg,png,pdf,doc,docx', 'description' => 'Tipe file yang diizinkan']
        ];

        foreach ($settings as $setting) {
            $this->db->insert('settings', $setting);
        }
    }
}

// Run the seeder
if ($argc > 1 && $argv[1] === '--run') {
    $seeder = new DatabaseSeeder();
    $seeder->run();
} else {
    echo "Usage: php seed.php --run\n";
    echo "This will populate the database with initial data.\n";
}
?>
