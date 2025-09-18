<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/cases.php';

// Get filter parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'created_at DESC';

// Initialize controller
$casesController = new CasesController();

// Prepare filters for controller
$filters = [];
if ($search) $filters['search'] = $search;
if ($category) $filters['category_id'] = $category;
if ($status) $filters['status'] = $status;

// Apply sorting
list($sortBy, $sortOrder) = explode(' ', $sort . ' DESC');
$filters['sort_by'] = $sortBy;
$filters['sort_order'] = strtoupper($sortOrder);

// Get filtered cases from controller
$cases = $casesController->index($filters);

// Get categories and users for reference
$categories = $db->findAll('categories');
$users = $db->findAll('users');

// Create category and user lookup arrays
$categoryLookup = [];
foreach ($categories as $cat) {
    $categoryLookup[$cat['id']] = $cat['name'];
}

$userLookup = [];
foreach ($users as $user) {
    $userLookup[$user['id']] = $user['full_name'];
}

// Add category and user info to cases (without reference to avoid duplicates)
foreach ($cases as $index => $case) {
    $cases[$index]['category_name'] = $categoryLookup[$case['category_id']] ?? 'Unknown';
    $cases[$index]['reporter_name'] = $userLookup[$case['reported_by']] ?? 'Unknown';
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="laporan_kasus_' . date('Y-m-d_H-i-s') . '.csv"');

// Create file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// CSV headers
$headers = [
    'ID',
    'Judul',
    'Deskripsi',
    'Lokasi',
    'Kategori',
    'Status',
    'Prioritas',
    'Pelapor',
    'Tanggal Dibuat',
    'Tanggal Update'
];

fputcsv($output, $headers);

// Status and priority labels
$statusLabels = [
    'pending' => 'Menunggu',
    'in_progress' => 'Sedang Dikerjakan',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];

$priorityLabels = [
    'low' => 'Rendah',
    'medium' => 'Sedang',
    'high' => 'Tinggi'
];

// Add data rows
foreach ($cases as $case) {
    $row = [
        $case['id'],
        $case['title'],
        $case['description'],
        $case['location'],
        $case['category_name'],
        $statusLabels[$case['status']] ?? $case['status'],
        $priorityLabels[$case['priority']] ?? $case['priority'],
        $case['reporter_name'],
        $case['created_at'],
        $case['updated_at']
    ];
    
    fputcsv($output, $row);
}

fclose($output);
exit();
?> 