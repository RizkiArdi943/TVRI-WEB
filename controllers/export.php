<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/cases.php';

$type = $_GET['type'] ?? 'csv';

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'created_at DESC';

$casesController = new CasesController();

$filters = [];
if ($search) $filters['search'] = $search;
if ($category) $filters['category_id'] = $category;
if ($status) $filters['status'] = $status;

list($sortBy, $sortOrder) = explode(' ', $sort . ' DESC');
$filters['sort_by'] = $sortBy;
$filters['sort_order'] = strtoupper($sortOrder);

$cases = $casesController->index($filters);

$categories = $db->findAll('categories');
$users = $db->findAll('users');

$categoryLookup = [];
foreach ($categories as $cat) {
	$categoryLookup[$cat['id']] = $cat['name'];
}

$userLookup = [];
foreach ($users as $user) {
	$userLookup[$user['id']] = $user['full_name'];
}

foreach ($cases as $index => $case) {
	$cases[$index]['category_name'] = $categoryLookup[$case['category_id']] ?? 'Unknown';
	$cases[$index]['reporter_name'] = $userLookup[$case['reported_by']] ?? 'Unknown';
}

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

if ($type === 'excel') {
	header('Content-Type: application/vnd.ms-excel; charset=utf-8');
	header('Content-Disposition: attachment; filename="laporan_kasus_' . date('Y-m-d_H-i-s') . '.xls"');

	echo "<table border=\"1\">";
	echo "<thead><tr>";
	echo "<th>ID</th><th>Judul</th><th>Deskripsi</th><th>Lokasi</th><th>Kategori</th><th>Status</th><th>Prioritas</th><th>Pelapor</th><th>Tanggal Dibuat</th><th>Tanggal Update</th>";
	echo "</tr></thead><tbody>";

	foreach ($cases as $case) {
		$cols = [
			$case['id'],
			htmlspecialchars($case['title'], ENT_QUOTES, 'UTF-8'),
			htmlspecialchars($case['description'], ENT_QUOTES, 'UTF-8'),
			htmlspecialchars($case['location'], ENT_QUOTES, 'UTF-8'),
			htmlspecialchars($case['category_name'], ENT_QUOTES, 'UTF-8'),
			htmlspecialchars($statusLabels[$case['status']] ?? $case['status'], ENT_QUOTES, 'UTF-8'),
			htmlspecialchars($priorityLabels[$case['priority']] ?? $case['priority'], ENT_QUOTES, 'UTF-8'),
			htmlspecialchars($case['reporter_name'], ENT_QUOTES, 'UTF-8'),
			htmlspecialchars($case['created_at'], ENT_QUOTES, 'UTF-8'),
			htmlspecialchars($case['updated_at'], ENT_QUOTES, 'UTF-8')
		];

		echo '<tr>';
		foreach ($cols as $col) {
			echo '<td>' . $col . '</td>';
		}
		echo '</tr>';
	}

	echo "</tbody></table>";
	exit();
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="laporan_kasus_' . date('Y-m-d_H-i-s') . '.csv"');

$output = fopen('php://output', 'w');

fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

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