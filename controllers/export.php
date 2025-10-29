<?php
// Start output buffering to prevent headers already sent errors
ob_start();

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if required files exist
if (!file_exists(__DIR__ . '/../config/database.php')) {
    http_response_code(500);
    die('Database configuration file not found');
}

if (!file_exists(__DIR__ . '/cases.php')) {
    http_response_code(500);
    die('Cases controller file not found');
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/cases.php';

$type = $_GET['type'] ?? 'csv';
// Orientation PDF: portrait | landscape (default: portrait)
$orientation = strtolower($_GET['orientation'] ?? 'portrait');
if (!in_array($orientation, ['portrait', 'landscape'], true)) {
    $orientation = 'portrait';
}
// Renderer preference for PDF export to control behavior (default: dompdf to avoid new tab)
$renderer = strtolower($_GET['renderer'] ?? 'dompdf');
// Inline mode: jika 1, kembalikan konten HTML (untuk di-embed iframe) agar tidak pindah halaman
$inline = isset($_GET['inline']) && $_GET['inline'] == '1';

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'created_at DESC';

try {
    $casesController = new CasesController();
} catch (Exception $e) {
    http_response_code(500);
    die('Failed to initialize CasesController: ' . $e->getMessage());
}

$filters = [];
if ($search) $filters['search'] = $search;
if ($category) $filters['category_id'] = $category;
if ($status) $filters['status'] = $status;

list($sortBy, $sortOrder) = explode(' ', $sort . ' DESC');
$filters['sort_by'] = $sortBy;
$filters['sort_order'] = strtoupper($sortOrder);

try {
    $cases = $casesController->index($filters);
} catch (Exception $e) {
    http_response_code(500);
    die('Failed to fetch cases: ' . $e->getMessage());
}

try {
    $categories = $db->findAll('categories');
    $users = $db->findAll('users');
} catch (Exception $e) {
    http_response_code(500);
    die('Failed to fetch categories/users: ' . $e->getMessage());
}

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

$dateNow = date('Y-m-d_H-i-s');

// Printable HTML for PDF via browser's Print to PDF
if ($type === 'pdf') {
    // Clear any previous output
    ob_clean();
    
    // Generate HTML content dengan layout yang lebih baik
    $html  = '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Export PDF - Laporan Kasus</title>';
    $html .= '<style>
        @page {
            size: A4 ' . $orientation . ';
            margin: 1.5cm;
        }
        body {
            font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
            color: #000;
            margin: 0;
            padding: 0;
            font-size: 9px;
            line-height: 1.2;
        }
        h1 {
            font-size: 16px;
            margin: 0 0 8px 0;
            font-weight: bold;
            color: #000;
        }
        .meta {
            font-size: 8px;
            color: #666;
            margin-bottom: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            table-layout: auto; /* biarkan browser/Dompdf hitung lebar */
        }
        th, td {
            border: 1px solid #000;
            padding: 3px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }
        thead th {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 8px;
        }
        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        /* lebar kolom via colgroup agar stabil di Dompdf */
        .col-id { width: 12%; }
        .col-title { width: 18%; }
        .col-desc { width: 22%; }
        .col-location { width: 12%; }
        .col-category { width: 8%; }
        .col-status { width: 6%; }
        .col-priority { width: 6%; }
        .col-reporter { width: 8%; }
        .col-created { width: 4%; }
        .col-updated { width: 4%; }
        
        /* Page break handling */
        .page-break {
            page-break-before: always;
        }
        tbody tr {
            page-break-inside: avoid;
        }
        thead {
            display: table-header-group;
        }
        tfoot {
            display: table-footer-group;
        }
    </style></head><body>';
    $html .= '<h1>Laporan Kasus - SIPETRA</h1>';
    $html .= '<div class="meta">Diunduh: '.date('d/m/Y H:i').' WIB</div>';
    // Gunakan colgroup agar lebar kolom dihormati Dompdf dan tidak memotong data
    $html .= '<table><colgroup>'
        .'<col class="col-id" />'
        .'<col class="col-title" />'
        .'<col class="col-desc" />'
        .'<col class="col-location" />'
        .'<col class="col-category" />'
        .'<col class="col-status" />'
        .'<col class="col-priority" />'
        .'<col class="col-reporter" />'
        .'<col class="col-created" />'
        .'<col class="col-updated" />'
        .'</colgroup><thead><tr>';
    $html .= '<th class="col-id">ID Laporan</th><th class="col-title">Judul</th><th class="col-desc">Deskripsi</th><th class="col-location">Lokasi</th><th class="col-category">Kategori</th><th class="col-status">Status</th><th class="col-priority">Prioritas</th><th class="col-reporter">Pelapor</th><th class="col-created">Dibuat</th><th class="col-updated">Update</th>';
    $html .= '</tr></thead><tbody>';
    foreach ($cases as $case) {
        $cols = [
            htmlspecialchars($case['id_laporan'] ?? $case['id'], ENT_QUOTES, 'UTF-8'),
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
        $colClasses = ['col-id', 'col-title', 'col-desc', 'col-location', 'col-category', 'col-status', 'col-priority', 'col-reporter', 'col-created', 'col-updated'];
        
        $html .= '<tr>';
        foreach ($cols as $index => $col) {
            $html .= '<td class="'.$colClasses[$index].'">'.$col.'</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table></body></html>';

    // Opsi PrintOptimized: gunakan jika renderer=print (default tidak aktif untuk mencegah tab baru)
    if ($renderer === 'print') {
        $printOptimizedPath = __DIR__ . '/../libs/print_optimized.php';
        if (file_exists($printOptimizedPath)) {
            try {
                require_once $printOptimizedPath;
                if (class_exists('PrintOptimized')) {
                    $printOptimized = new PrintOptimized('Laporan Kasus - SIPETRA');
                    $printOptimized->loadHtml($html);
                    $printOptimized->setPaper('A4', $orientation);
                    $printOptimized->render();
                    // Tampilkan inline; jika inline=1 maka hanya kirim HTML (untuk di-embed via fetch+iframe)
                    $content = $printOptimized->output();
                    // Sesuaikan orientasi @page
                    $content = str_replace('size: A4 portrait', 'size: A4 ' . $orientation, $content);
                    if ($inline) {
                        // Kembalikan HTML mentah untuk di-embed pada iframe oleh frontend
                        ob_end_clean();
                        header('Content-Type: text/plain; charset=utf-8');
                        echo $content;
                        exit();
                    } else {
                        // Mode lama: tampilkan halaman print langsung
                        $content = str_replace('</body>', '<script>window.onload=function(){setTimeout(function(){window.print();},50);};</script></body>', $content);
                        ob_end_clean();
                        header('Content-Type: text/html; charset=utf-8');
                        header('Content-Disposition: inline');
                        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                        header('Pragma: no-cache');
                        echo $content;
                        exit();
                    }
                } else {
                    error_log('PrintOptimized class not found');
                }
            } catch (Exception $e) {
                error_log('PrintOptimized error: ' . $e->getMessage());
                // Continue to fallback methods
            }
        } else {
            error_log('PrintOptimized file not found: ' . $printOptimizedPath);
        }
    }

    // Jika bukan mode print, lakukan rantai fallback yang SELALU menghasilkan PDF
    if ($renderer !== 'print') {
        // 1) Dompdf
        $autoloadPath = __DIR__ . '/../libs/dompdf_autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
            if (class_exists('Dompdf\\Dompdf')) {
                try {
                    $dompdf = new Dompdf\Dompdf();
                    $dompdf->loadHtml($html, 'UTF-8');
                    $dompdf->setPaper('A4', $orientation);
                    $dompdf->getOptions()->set([
                        'defaultFont' => 'DejaVu Sans',
                        'isRemoteEnabled' => false,
                        'isHtml5ParserEnabled' => true,
                        'isPhpEnabled' => false,
                        'isFontSubsettingEnabled' => true,
                        'defaultMediaType' => 'print',
                        'debugPng' => false
                    ]);
                    $dompdf->render();
                    $filename = 'laporan_kasus_' . $dateNow . '.pdf';
                    ob_end_clean();
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    echo $dompdf->output();
                    exit();
                } catch (Exception $e) {
                    error_log('Dompdf error: ' . $e->getMessage());
                }
            }
        }

        // 2) BasicPDF
        $basicPdfPath = __DIR__ . '/../libs/basic_pdf.php';
        if (file_exists($basicPdfPath)) {
            require_once $basicPdfPath;
            if (class_exists('BasicPDF')) {
                try {
                    $basicPdf = new BasicPDF('Laporan Kasus - SIPETRA');
                    $basicPdf->loadHtml($html);
                    $basicPdf->setPaper('A4', $orientation);
                    $basicPdf->render();
                    $filename = 'laporan_kasus_' . $dateNow . '.pdf';
                    ob_end_clean();
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    echo $basicPdf->output();
                    exit();
                } catch (Exception $e) {
                    error_log('BasicPDF error: ' . $e->getMessage());
                }
            }
        }

        // 3) ReliablePDF
        $reliablePdfPath = __DIR__ . '/../libs/reliable_pdf.php';
        if (file_exists($reliablePdfPath)) {
            require_once $reliablePdfPath;
            if (class_exists('ReliablePDF')) {
                try {
                    $reliablePdf = new ReliablePDF('Laporan Kasus - SIPETRA');
                    $reliablePdf->loadHtml($html);
                    $reliablePdf->setPaper('A4', $orientation);
                    $reliablePdf->render();
                    $filename = 'laporan_kasus_' . $dateNow . '.pdf';
                    ob_end_clean();
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    echo $reliablePdf->output();
                    exit();
                } catch (Exception $e) {
                    error_log('ReliablePDF error: ' . $e->getMessage());
                }
            }
        }

        // 4) TCPDFSimple
        $tcpdfSimplePath = __DIR__ . '/../libs/tcpdf_simple.php';
        if (file_exists($tcpdfSimplePath)) {
            require_once $tcpdfSimplePath;
            if (class_exists('TCPDFSimple')) {
                try {
                    $tcpdfSimple = new TCPDFSimple('Laporan Kasus - SIPETRA');
                    $tcpdfSimple->loadHtml($html);
                    $tcpdfSimple->setPaper('A4', $orientation);
                    $tcpdfSimple->render();
                    $filename = 'laporan_kasus_' . $dateNow . '.pdf';
                    ob_end_clean();
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    echo $tcpdfSimple->output();
                    exit();
                } catch (Exception $e) {
                    error_log('TCPDFSimple error: ' . $e->getMessage());
                }
            }
        }
    }

    // Fallback: Gunakan HtmlToPDF untuk HTML yang dioptimalkan untuk print
    $htmlToPdfPath = __DIR__ . '/../libs/html_to_pdf.php';
    if ($renderer === 'html' && file_exists($htmlToPdfPath)) {
        require_once $htmlToPdfPath;
        if (class_exists('HtmlToPDF')) {
            $htmlToPdf = new HtmlToPDF('Laporan Kasus - SIPETRA');
            $htmlToPdf->loadHtml($html);
            $htmlToPdf->setPaper('A4', $orientation);
            $htmlToPdf->render();
            
            // Paksa download sebagai attachment agar tidak membuka tab
            ob_end_clean();
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.html"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            echo $htmlToPdf->output();
            exit();
        }
    }

    // Fallback: Gunakan SimplePDF untuk HTML yang dioptimalkan untuk print
    $simplePdfPath = __DIR__ . '/../libs/simple_pdf.php';
    if ($renderer === 'simple' && file_exists($simplePdfPath)) {
        require_once $simplePdfPath;
        if (class_exists('SimplePDF')) {
            $simplePdf = new SimplePDF('Laporan Kasus - SIPETRA');
            $simplePdf->loadHtml($html);
            $simplePdf->setPaper('A4', $orientation);
            $simplePdf->render();
            
            // Paksa download sebagai attachment agar tidak membuka tab
            ob_end_clean();
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.html"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            echo $simplePdf->output();
            exit();
        }
    }

    // Final fallback: HTML biasa dengan error handling
    try {
        $filename = 'laporan_kasus_' . $dateNow . '.html';
        ob_end_clean();
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        echo $html;
        exit();
    } catch (Exception $e) {
        http_response_code(500);
        die('Failed to generate report: ' . $e->getMessage());
    }
}

if ($type === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.xls"');

	echo "<table border=\"1\">";
	echo "<thead><tr>";
	echo "<th>ID</th><th>Judul</th><th>Deskripsi</th><th>Lokasi</th><th>Kategori</th><th>Status</th><th>Prioritas</th><th>Pelapor</th><th>Tanggal Dibuat</th><th>Tanggal Update</th>";
	echo "</tr></thead><tbody>";

	foreach ($cases as $case) {
		$cols = [
			$case['id_laporan'] ?? $case['id'], // Use id_laporan if available, fallback to id
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
header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.csv"');

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
		$case['id_laporan'] ?? $case['id'], // Use id_laporan if available, fallback to id
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
header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.csv"');

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
		$case['id_laporan'] ?? $case['id'], // Use id_laporan if available, fallback to id
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
<?php
// Start output buffering to prevent headers already sent errors
ob_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/cases.php';

$type = $_GET['type'] ?? 'csv';
// Orientation PDF: portrait | landscape (default: portrait)
$orientation = strtolower($_GET['orientation'] ?? 'portrait');
if (!in_array($orientation, ['portrait', 'landscape'], true)) {
    $orientation = 'portrait';
}
// Renderer preference for PDF export to control behavior (default: dompdf to avoid new tab)
$renderer = strtolower($_GET['renderer'] ?? 'dompdf');
// Inline mode: jika 1, kembalikan konten HTML (untuk di-embed iframe) agar tidak pindah halaman
$inline = isset($_GET['inline']) && $_GET['inline'] == '1';

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

$dateNow = date('Y-m-d_H-i-s');

// Printable HTML for PDF via browser's Print to PDF
if ($type === 'pdf') {
    // Clear any previous output
    ob_clean();
    
    // Generate HTML content dengan layout yang lebih baik
    $html  = '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Export PDF - Laporan Kasus</title>';
    $html .= '<style>
        @page {
            size: A4 ' . $orientation . ';
            margin: 1.5cm;
        }
        body {
            font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
            color: #000;
            margin: 0;
            padding: 0;
            font-size: 9px;
            line-height: 1.2;
        }
        h1 {
            font-size: 16px;
            margin: 0 0 8px 0;
            font-weight: bold;
            color: #000;
        }
        .meta {
            font-size: 8px;
            color: #666;
            margin-bottom: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            table-layout: auto; /* biarkan browser/Dompdf hitung lebar */
        }
        th, td {
            border: 1px solid #000;
            padding: 3px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }
        thead th {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 8px;
        }
        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        /* lebar kolom via colgroup agar stabil di Dompdf */
        .col-id { width: 12%; }
        .col-title { width: 18%; }
        .col-desc { width: 22%; }
        .col-location { width: 12%; }
        .col-category { width: 8%; }
        .col-status { width: 6%; }
        .col-priority { width: 6%; }
        .col-reporter { width: 8%; }
        .col-created { width: 4%; }
        .col-updated { width: 4%; }
        
        /* Page break handling */
        .page-break {
            page-break-before: always;
        }
        tbody tr {
            page-break-inside: avoid;
        }
        thead {
            display: table-header-group;
        }
        tfoot {
            display: table-footer-group;
        }
    </style></head><body>';
    $html .= '<h1>Laporan Kasus - SIPETRA</h1>';
    $html .= '<div class="meta">Diunduh: '.date('d/m/Y H:i').' WIB</div>';
    // Gunakan colgroup agar lebar kolom dihormati Dompdf dan tidak memotong data
    $html .= '<table><colgroup>'
        .'<col class="col-id" />'
        .'<col class="col-title" />'
        .'<col class="col-desc" />'
        .'<col class="col-location" />'
        .'<col class="col-category" />'
        .'<col class="col-status" />'
        .'<col class="col-priority" />'
        .'<col class="col-reporter" />'
        .'<col class="col-created" />'
        .'<col class="col-updated" />'
        .'</colgroup><thead><tr>';
    $html .= '<th class="col-id">ID Laporan</th><th class="col-title">Judul</th><th class="col-desc">Deskripsi</th><th class="col-location">Lokasi</th><th class="col-category">Kategori</th><th class="col-status">Status</th><th class="col-priority">Prioritas</th><th class="col-reporter">Pelapor</th><th class="col-created">Dibuat</th><th class="col-updated">Update</th>';
    $html .= '</tr></thead><tbody>';
    foreach ($cases as $case) {
        $cols = [
            htmlspecialchars($case['id_laporan'] ?? $case['id'], ENT_QUOTES, 'UTF-8'),
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
        $colClasses = ['col-id', 'col-title', 'col-desc', 'col-location', 'col-category', 'col-status', 'col-priority', 'col-reporter', 'col-created', 'col-updated'];
        
        $html .= '<tr>';
        foreach ($cols as $index => $col) {
            $html .= '<td class="'.$colClasses[$index].'">'.$col.'</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table></body></html>';

    // Opsi PrintOptimized: gunakan jika renderer=print (default tidak aktif untuk mencegah tab baru)
    if ($renderer === 'print') {
        $printOptimizedPath = __DIR__ . '/../libs/print_optimized.php';
        if (file_exists($printOptimizedPath)) {
            require_once $printOptimizedPath;
            if (class_exists('PrintOptimized')) {
                try {
                    $printOptimized = new PrintOptimized('Laporan Kasus - SIPETRA');
                    $printOptimized->loadHtml($html);
                    $printOptimized->setPaper('A4', $orientation);
                    $printOptimized->render();
                    // Tampilkan inline; jika inline=1 maka hanya kirim HTML (untuk di-embed via fetch+iframe)
                    $content = $printOptimized->output();
                    // Sesuaikan orientasi @page
                    $content = str_replace('size: A4 portrait', 'size: A4 ' . $orientation, $content);
                    if ($inline) {
                        // Kembalikan HTML mentah untuk di-embed pada iframe oleh frontend
                        ob_end_clean();
                        header('Content-Type: text/plain; charset=utf-8');
                        echo $content;
                        exit();
                    } else {
                        // Mode lama: tampilkan halaman print langsung
                        $content = str_replace('</body>', '<script>window.onload=function(){setTimeout(function(){window.print();},50);};</script></body>', $content);
                        ob_end_clean();
                        header('Content-Type: text/html; charset=utf-8');
                        header('Content-Disposition: inline');
                        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                        header('Pragma: no-cache');
                        echo $content;
                        exit();
                    }
                } catch (Exception $e) {
                    error_log('PrintOptimized error: ' . $e->getMessage());
                }
            }
        }
    }

    // Jika bukan mode print, lakukan rantai fallback yang SELALU menghasilkan PDF
    if ($renderer !== 'print') {
        // 1) Dompdf
        $autoloadPath = __DIR__ . '/../libs/dompdf_autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
            if (class_exists('Dompdf\\Dompdf')) {
                try {
                    $dompdf = new Dompdf\Dompdf();
                    $dompdf->loadHtml($html, 'UTF-8');
                    $dompdf->setPaper('A4', $orientation);
                    $dompdf->getOptions()->set([
                        'defaultFont' => 'DejaVu Sans',
                        'isRemoteEnabled' => false,
                        'isHtml5ParserEnabled' => true,
                        'isPhpEnabled' => false,
                        'isFontSubsettingEnabled' => true,
                        'defaultMediaType' => 'print',
                        'debugPng' => false
                    ]);
                    $dompdf->render();
                    $filename = 'laporan_kasus_' . $dateNow . '.pdf';
                    ob_end_clean();
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    echo $dompdf->output();
                    exit();
                } catch (Exception $e) {
                    error_log('Dompdf error: ' . $e->getMessage());
                }
            }
        }

        // 2) BasicPDF
        $basicPdfPath = __DIR__ . '/../libs/basic_pdf.php';
        if (file_exists($basicPdfPath)) {
            require_once $basicPdfPath;
            if (class_exists('BasicPDF')) {
                try {
                    $basicPdf = new BasicPDF('Laporan Kasus - SIPETRA');
                    $basicPdf->loadHtml($html);
                    $basicPdf->setPaper('A4', $orientation);
                    $basicPdf->render();
                    $filename = 'laporan_kasus_' . $dateNow . '.pdf';
                    ob_end_clean();
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    echo $basicPdf->output();
                    exit();
                } catch (Exception $e) {
                    error_log('BasicPDF error: ' . $e->getMessage());
                }
            }
        }

        // 3) ReliablePDF
        $reliablePdfPath = __DIR__ . '/../libs/reliable_pdf.php';
        if (file_exists($reliablePdfPath)) {
            require_once $reliablePdfPath;
            if (class_exists('ReliablePDF')) {
                try {
                    $reliablePdf = new ReliablePDF('Laporan Kasus - SIPETRA');
                    $reliablePdf->loadHtml($html);
                    $reliablePdf->setPaper('A4', $orientation);
                    $reliablePdf->render();
                    $filename = 'laporan_kasus_' . $dateNow . '.pdf';
                    ob_end_clean();
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    echo $reliablePdf->output();
                    exit();
                } catch (Exception $e) {
                    error_log('ReliablePDF error: ' . $e->getMessage());
                }
            }
        }

        // 4) TCPDFSimple
        $tcpdfSimplePath = __DIR__ . '/../libs/tcpdf_simple.php';
        if (file_exists($tcpdfSimplePath)) {
            require_once $tcpdfSimplePath;
            if (class_exists('TCPDFSimple')) {
                try {
                    $tcpdfSimple = new TCPDFSimple('Laporan Kasus - SIPETRA');
                    $tcpdfSimple->loadHtml($html);
                    $tcpdfSimple->setPaper('A4', $orientation);
                    $tcpdfSimple->render();
                    $filename = 'laporan_kasus_' . $dateNow . '.pdf';
                    ob_end_clean();
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    echo $tcpdfSimple->output();
                    exit();
                } catch (Exception $e) {
                    error_log('TCPDFSimple error: ' . $e->getMessage());
                }
            }
        }
    }

    // Fallback: Gunakan HtmlToPDF untuk HTML yang dioptimalkan untuk print
    $htmlToPdfPath = __DIR__ . '/../libs/html_to_pdf.php';
    if ($renderer === 'html' && file_exists($htmlToPdfPath)) {
        require_once $htmlToPdfPath;
        if (class_exists('HtmlToPDF')) {
            $htmlToPdf = new HtmlToPDF('Laporan Kasus - SIPETRA');
            $htmlToPdf->loadHtml($html);
            $htmlToPdf->setPaper('A4', $orientation);
            $htmlToPdf->render();
            
            // Paksa download sebagai attachment agar tidak membuka tab
            ob_end_clean();
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.html"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            echo $htmlToPdf->output();
            exit();
        }
    }

    // Fallback: Gunakan SimplePDF untuk HTML yang dioptimalkan untuk print
    $simplePdfPath = __DIR__ . '/../libs/simple_pdf.php';
    if ($renderer === 'simple' && file_exists($simplePdfPath)) {
        require_once $simplePdfPath;
        if (class_exists('SimplePDF')) {
            $simplePdf = new SimplePDF('Laporan Kasus - SIPETRA');
            $simplePdf->loadHtml($html);
            $simplePdf->setPaper('A4', $orientation);
            $simplePdf->render();
            
            // Paksa download sebagai attachment agar tidak membuka tab
            ob_end_clean();
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.html"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            echo $simplePdf->output();
            exit();
        }
    }

    // Final fallback: HTML biasa
    $filename = 'laporan_kasus_' . $dateNow . '.html';
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    echo $html;
    exit();
}

if ($type === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.xls"');

	echo "<table border=\"1\">";
	echo "<thead><tr>";
	echo "<th>ID</th><th>Judul</th><th>Deskripsi</th><th>Lokasi</th><th>Kategori</th><th>Status</th><th>Prioritas</th><th>Pelapor</th><th>Tanggal Dibuat</th><th>Tanggal Update</th>";
	echo "</tr></thead><tbody>";

	foreach ($cases as $case) {
		$cols = [
			$case['id_laporan'] ?? $case['id'], // Use id_laporan if available, fallback to id
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
header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.csv"');

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
		$case['id_laporan'] ?? $case['id'], // Use id_laporan if available, fallback to id
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
<?php
// Start output buffering to prevent headers already sent errors
ob_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/cases.php';

$type = $_GET['type'] ?? 'csv';
// Orientation PDF: portrait | landscape (default: portrait)
$orientation = strtolower($_GET['orientation'] ?? 'portrait');
if (!in_array($orientation, ['portrait', 'landscape'], true)) {
    $orientation = 'portrait';
}
// Renderer preference for PDF export to control behavior (default: dompdf to avoid new tab)
$renderer = strtolower($_GET['renderer'] ?? 'dompdf');
// Inline mode: jika 1, kembalikan konten HTML (untuk di-embed iframe) agar tidak pindah halaman
$inline = isset($_GET['inline']) && $_GET['inline'] == '1';

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

$dateNow = date('Y-m-d_H-i-s');

// Printable HTML for PDF via browser's Print to PDF
if ($type === 'pdf') {
    // Clear any previous output
    ob_clean();
    
    // Generate HTML content dengan layout yang lebih baik
    $html  = '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Export PDF - Laporan Kasus</title>';
    $html .= '<style>
        @page {
            size: A4 ' . $orientation . ';
            margin: 1.5cm;
        }
        body {
            font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
            color: #000;
            margin: 0;
            padding: 0;
            font-size: 9px;
            line-height: 1.2;
        }
        h1 {
            font-size: 16px;
            margin: 0 0 8px 0;
            font-weight: bold;
            color: #000;
        }
        .meta {
            font-size: 8px;
            color: #666;
            margin-bottom: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            table-layout: auto; /* biarkan browser/Dompdf hitung lebar */
        }
        th, td {
            border: 1px solid #000;
            padding: 3px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }
        thead th {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 8px;
        }
        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        /* lebar kolom via colgroup agar stabil di Dompdf */
        .col-id { width: 12%; }
        .col-title { width: 18%; }
        .col-desc { width: 22%; }
        .col-location { width: 12%; }
        .col-category { width: 8%; }
        .col-status { width: 6%; }
        .col-priority { width: 6%; }
        .col-reporter { width: 8%; }
        .col-created { width: 4%; }
        .col-updated { width: 4%; }
        
        /* Page break handling */
        .page-break {
            page-break-before: always;
        }
        tbody tr {
            page-break-inside: avoid;
        }
        thead {
            display: table-header-group;
        }
        tfoot {
            display: table-footer-group;
        }
    </style></head><body>';
    $html .= '<h1>Laporan Kasus - SIPETRA</h1>';
    $html .= '<div class="meta">Diunduh: '.date('d/m/Y H:i').' WIB</div>';
    // Gunakan colgroup agar lebar kolom dihormati Dompdf dan tidak memotong data
    $html .= '<table><colgroup>'
        .'<col class="col-id" />'
        .'<col class="col-title" />'
        .'<col class="col-desc" />'
        .'<col class="col-location" />'
        .'<col class="col-category" />'
        .'<col class="col-status" />'
        .'<col class="col-priority" />'
        .'<col class="col-reporter" />'
        .'<col class="col-created" />'
        .'<col class="col-updated" />'
        .'</colgroup><thead><tr>';
    $html .= '<th class="col-id">ID Laporan</th><th class="col-title">Judul</th><th class="col-desc">Deskripsi</th><th class="col-location">Lokasi</th><th class="col-category">Kategori</th><th class="col-status">Status</th><th class="col-priority">Prioritas</th><th class="col-reporter">Pelapor</th><th class="col-created">Dibuat</th><th class="col-updated">Update</th>';
    $html .= '</tr></thead><tbody>';
    foreach ($cases as $case) {
        $cols = [
            htmlspecialchars($case['id_laporan'] ?? $case['id'], ENT_QUOTES, 'UTF-8'),
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
        $colClasses = ['col-id', 'col-title', 'col-desc', 'col-location', 'col-category', 'col-status', 'col-priority', 'col-reporter', 'col-created', 'col-updated'];
        
        $html .= '<tr>';
        foreach ($cols as $index => $col) {
            $html .= '<td class="'.$colClasses[$index].'">'.$col.'</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table></body></html>';

    // Opsi PrintOptimized: gunakan jika renderer=print (default tidak aktif untuk mencegah tab baru)
    if ($renderer === 'print') {
        $printOptimizedPath = __DIR__ . '/../libs/print_optimized.php';
        if (file_exists($printOptimizedPath)) {
            require_once $printOptimizedPath;
            if (class_exists('PrintOptimized')) {
                try {
                    $printOptimized = new PrintOptimized('Laporan Kasus - SIPETRA');
                    $printOptimized->loadHtml($html);
                    $printOptimized->setPaper('A4', $orientation);
                    $printOptimized->render();
                    // Tampilkan inline; jika inline=1 maka hanya kirim HTML (untuk di-embed via fetch+iframe)
                    $content = $printOptimized->output();
                    // Sesuaikan orientasi @page
                    $content = str_replace('size: A4 portrait', 'size: A4 ' . $orientation, $content);
                    if ($inline) {
                        // Kembalikan HTML mentah untuk di-embed pada iframe oleh frontend
                        ob_end_clean();
                        header('Content-Type: text/plain; charset=utf-8');
                        echo $content;
                        exit();
                    } else {
                        // Mode lama: tampilkan halaman print langsung
                        $content = str_replace('</body>', '<script>window.onload=function(){setTimeout(function(){window.print();},50);};</script></body>', $content);
                        ob_end_clean();
                        header('Content-Type: text/html; charset=utf-8');
                        header('Content-Disposition: inline');
                        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                        header('Pragma: no-cache');
                        echo $content;
                        exit();
                    }
                } catch (Exception $e) {
                    error_log('PrintOptimized error: ' . $e->getMessage());
                }
            }
        }
    }

    // Jika bukan mode print, lakukan rantai fallback yang SELALU menghasilkan PDF
    if ($renderer !== 'print') {
        // 1) Dompdf
        $autoloadPath = __DIR__ . '/../libs/dompdf_autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
            if (class_exists('Dompdf\\Dompdf')) {
                try {
                    $dompdf = new Dompdf\Dompdf();
                    $dompdf->loadHtml($html, 'UTF-8');
                    $dompdf->setPaper('A4', $orientation);
                    $dompdf->getOptions()->set([
                        'defaultFont' => 'DejaVu Sans',
                        'isRemoteEnabled' => false,
                        'isHtml5ParserEnabled' => true,
                        'isPhpEnabled' => false,
                        'isFontSubsettingEnabled' => true,
                        'defaultMediaType' => 'print',
                        'debugPng' => false
                    ]);
                    $dompdf->render();
                    $filename = 'laporan_kasus_' . $dateNow . '.pdf';
                    ob_end_clean();
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    echo $dompdf->output();
                    exit();
                } catch (Exception $e) {
                    error_log('Dompdf error: ' . $e->getMessage());
                }
            }
        }

        // 2) BasicPDF
        $basicPdfPath = __DIR__ . '/../libs/basic_pdf.php';
        if (file_exists($basicPdfPath)) {
            require_once $basicPdfPath;
            if (class_exists('BasicPDF')) {
                try {
                    $basicPdf = new BasicPDF('Laporan Kasus - SIPETRA');
                    $basicPdf->loadHtml($html);
                    $basicPdf->setPaper('A4', $orientation);
                    $basicPdf->render();
                    $filename = 'laporan_kasus_' . $dateNow . '.pdf';
                    ob_end_clean();
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    echo $basicPdf->output();
                    exit();
                } catch (Exception $e) {
                    error_log('BasicPDF error: ' . $e->getMessage());
                }
            }
        }

        // 3) ReliablePDF
        $reliablePdfPath = __DIR__ . '/../libs/reliable_pdf.php';
        if (file_exists($reliablePdfPath)) {
            require_once $reliablePdfPath;
            if (class_exists('ReliablePDF')) {
                try {
                    $reliablePdf = new ReliablePDF('Laporan Kasus - SIPETRA');
                    $reliablePdf->loadHtml($html);
                    $reliablePdf->setPaper('A4', $orientation);
                    $reliablePdf->render();
                    $filename = 'laporan_kasus_' . $dateNow . '.pdf';
                    ob_end_clean();
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    echo $reliablePdf->output();
                    exit();
                } catch (Exception $e) {
                    error_log('ReliablePDF error: ' . $e->getMessage());
                }
            }
        }

        // 4) TCPDFSimple
        $tcpdfSimplePath = __DIR__ . '/../libs/tcpdf_simple.php';
        if (file_exists($tcpdfSimplePath)) {
            require_once $tcpdfSimplePath;
            if (class_exists('TCPDFSimple')) {
                try {
                    $tcpdfSimple = new TCPDFSimple('Laporan Kasus - SIPETRA');
                    $tcpdfSimple->loadHtml($html);
                    $tcpdfSimple->setPaper('A4', $orientation);
                    $tcpdfSimple->render();
                    $filename = 'laporan_kasus_' . $dateNow . '.pdf';
                    ob_end_clean();
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    echo $tcpdfSimple->output();
                    exit();
                } catch (Exception $e) {
                    error_log('TCPDFSimple error: ' . $e->getMessage());
                }
            }
        }
    }

    // Fallback: Gunakan HtmlToPDF untuk HTML yang dioptimalkan untuk print
    $htmlToPdfPath = __DIR__ . '/../libs/html_to_pdf.php';
    if ($renderer === 'html' && file_exists($htmlToPdfPath)) {
        require_once $htmlToPdfPath;
        if (class_exists('HtmlToPDF')) {
            $htmlToPdf = new HtmlToPDF('Laporan Kasus - SIPETRA');
            $htmlToPdf->loadHtml($html);
            $htmlToPdf->setPaper('A4', $orientation);
            $htmlToPdf->render();
            
            // Paksa download sebagai attachment agar tidak membuka tab
            ob_end_clean();
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.html"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            echo $htmlToPdf->output();
            exit();
        }
    }

    // Fallback: Gunakan SimplePDF untuk HTML yang dioptimalkan untuk print
    $simplePdfPath = __DIR__ . '/../libs/simple_pdf.php';
    if ($renderer === 'simple' && file_exists($simplePdfPath)) {
        require_once $simplePdfPath;
        if (class_exists('SimplePDF')) {
            $simplePdf = new SimplePDF('Laporan Kasus - SIPETRA');
            $simplePdf->loadHtml($html);
            $simplePdf->setPaper('A4', $orientation);
            $simplePdf->render();
            
            // Paksa download sebagai attachment agar tidak membuka tab
            ob_end_clean();
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.html"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            echo $simplePdf->output();
            exit();
        }
    }

    // Final fallback: HTML biasa
    $filename = 'laporan_kasus_' . $dateNow . '.html';
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    echo $html;
    exit();
}

if ($type === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.xls"');

	echo "<table border=\"1\">";
	echo "<thead><tr>";
	echo "<th>ID</th><th>Judul</th><th>Deskripsi</th><th>Lokasi</th><th>Kategori</th><th>Status</th><th>Prioritas</th><th>Pelapor</th><th>Tanggal Dibuat</th><th>Tanggal Update</th>";
	echo "</tr></thead><tbody>";

	foreach ($cases as $case) {
		$cols = [
			$case['id_laporan'] ?? $case['id'], // Use id_laporan if available, fallback to id
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
header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.csv"');

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
		$case['id_laporan'] ?? $case['id'], // Use id_laporan if available, fallback to id
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
header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.csv"');

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
		$case['id_laporan'] ?? $case['id'], // Use id_laporan if available, fallback to id
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
header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.csv"');

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
		$case['id_laporan'] ?? $case['id'], // Use id_laporan if available, fallback to id
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
<?php
// Start output buffering to prevent headers already sent errors
ob_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/cases.php';

$type = $_GET['type'] ?? 'csv';
// Orientation PDF: portrait | landscape (default: portrait)
$orientation = strtolower($_GET['orientation'] ?? 'portrait');
if (!in_array($orientation, ['portrait', 'landscape'], true)) {
    $orientation = 'portrait';
}
// Renderer preference for PDF export to control behavior (default: dompdf to avoid new tab)
$renderer = strtolower($_GET['renderer'] ?? 'dompdf');
// Inline mode: jika 1, kembalikan konten HTML (untuk di-embed iframe) agar tidak pindah halaman
$inline = isset($_GET['inline']) && $_GET['inline'] == '1';

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

$dateNow = date('Y-m-d_H-i-s');

// Printable HTML for PDF via browser's Print to PDF
if ($type === 'pdf') {
    // Clear any previous output
    ob_clean();
    
    // Generate HTML content dengan layout yang lebih baik
    $html  = '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Export PDF - Laporan Kasus</title>';
    $html .= '<style>
        @page {
            size: A4 ' . $orientation . ';
            margin: 1.5cm;
        }
        body {
            font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
            color: #000;
            margin: 0;
            padding: 0;
            font-size: 9px;
            line-height: 1.2;
        }
        h1 {
            font-size: 16px;
            margin: 0 0 8px 0;
            font-weight: bold;
            color: #000;
        }
        .meta {
            font-size: 8px;
            color: #666;
            margin-bottom: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            table-layout: auto; /* biarkan browser/Dompdf hitung lebar */
        }
        th, td {
            border: 1px solid #000;
            padding: 3px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }
        thead th {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 8px;
        }
        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        /* lebar kolom via colgroup agar stabil di Dompdf */
        .col-id { width: 12%; }
        .col-title { width: 18%; }
        .col-desc { width: 22%; }
        .col-location { width: 12%; }
        .col-category { width: 8%; }
        .col-status { width: 6%; }
        .col-priority { width: 6%; }
        .col-reporter { width: 8%; }
        .col-created { width: 4%; }
        .col-updated { width: 4%; }
        
        /* Page break handling */
        .page-break {
            page-break-before: always;
        }
        tbody tr {
            page-break-inside: avoid;
        }
        thead {
            display: table-header-group;
        }
        tfoot {
            display: table-footer-group;
        }
    </style></head><body>';
    $html .= '<h1>Laporan Kasus - SIPETRA</h1>';
    $html .= '<div class="meta">Diunduh: '.date('d/m/Y H:i').' WIB</div>';
    // Gunakan colgroup agar lebar kolom dihormati Dompdf dan tidak memotong data
    $html .= '<table><colgroup>'
        .'<col class="col-id" />'
        .'<col class="col-title" />'
        .'<col class="col-desc" />'
        .'<col class="col-location" />'
        .'<col class="col-category" />'
        .'<col class="col-status" />'
        .'<col class="col-priority" />'
        .'<col class="col-reporter" />'
        .'<col class="col-created" />'
        .'<col class="col-updated" />'
        .'</colgroup><thead><tr>';
    $html .= '<th class="col-id">ID Laporan</th><th class="col-title">Judul</th><th class="col-desc">Deskripsi</th><th class="col-location">Lokasi</th><th class="col-category">Kategori</th><th class="col-status">Status</th><th class="col-priority">Prioritas</th><th class="col-reporter">Pelapor</th><th class="col-created">Dibuat</th><th class="col-updated">Update</th>';
    $html .= '</tr></thead><tbody>';
    foreach ($cases as $case) {
        $cols = [
            htmlspecialchars($case['id_laporan'] ?? $case['id'], ENT_QUOTES, 'UTF-8'),
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
        $colClasses = ['col-id', 'col-title', 'col-desc', 'col-location', 'col-category', 'col-status', 'col-priority', 'col-reporter', 'col-created', 'col-updated'];
        
        $html .= '<tr>';
        foreach ($cols as $index => $col) {
            $html .= '<td class="'.$colClasses[$index].'">'.$col.'</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table></body></html>';

    // Opsi PrintOptimized: gunakan jika renderer=print (default tidak aktif untuk mencegah tab baru)
    if ($renderer === 'print') {
        $printOptimizedPath = __DIR__ . '/../libs/print_optimized.php';
        if (file_exists($printOptimizedPath)) {
            require_once $printOptimizedPath;
            if (class_exists('PrintOptimized')) {
                try {
                    $printOptimized = new PrintOptimized('Laporan Kasus - SIPETRA');
                    $printOptimized->loadHtml($html);
                    $printOptimized->setPaper('A4', $orientation);
                    $printOptimized->render();
                    // Tampilkan inline; jika inline=1 maka hanya kirim HTML (untuk di-embed via fetch+iframe)
                    $content = $printOptimized->output();
                    // Sesuaikan orientasi @page
                    $content = str_replace('size: A4 portrait', 'size: A4 ' . $orientation, $content);
                    if ($inline) {
                        // Kembalikan HTML mentah untuk di-embed pada iframe oleh frontend
                        ob_end_clean();
                        header('Content-Type: text/plain; charset=utf-8');
                        echo $content;
                        exit();
                    } else {
                        // Mode lama: tampilkan halaman print langsung
                        $content = str_replace('</body>', '<script>window.onload=function(){setTimeout(function(){window.print();},50);};</script></body>', $content);
                        ob_end_clean();
                        header('Content-Type: text/html; charset=utf-8');
                        header('Content-Disposition: inline');
                        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                        header('Pragma: no-cache');
                        echo $content;
                        exit();
                    }
                } catch (Exception $e) {
                    error_log('PrintOptimized error: ' . $e->getMessage());
                }
            }
        }
    }

    // Jika bukan mode print, lakukan rantai fallback yang SELALU menghasilkan PDF
    if ($renderer !== 'print') {
        // 1) Dompdf
        $autoloadPath = __DIR__ . '/../libs/dompdf_autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
            if (class_exists('Dompdf\\Dompdf')) {
                try {
                    $dompdf = new Dompdf\Dompdf();
                    $dompdf->loadHtml($html, 'UTF-8');
                    $dompdf->setPaper('A4', $orientation);
                    $dompdf->getOptions()->set([
                        'defaultFont' => 'DejaVu Sans',
                        'isRemoteEnabled' => false,
                        'isHtml5ParserEnabled' => true,
                        'isPhpEnabled' => false,
                        'isFontSubsettingEnabled' => true,
                        'defaultMediaType' => 'print',
                        'debugPng' => false
                    ]);
                    $dompdf->render();
                    $filename = 'laporan_kasus_' . $dateNow . '.pdf';
                    ob_end_clean();
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    echo $dompdf->output();
                    exit();
                } catch (Exception $e) {
                    error_log('Dompdf error: ' . $e->getMessage());
                }
            }
        }

        // 2) BasicPDF
        $basicPdfPath = __DIR__ . '/../libs/basic_pdf.php';
        if (file_exists($basicPdfPath)) {
            require_once $basicPdfPath;
            if (class_exists('BasicPDF')) {
                try {
                    $basicPdf = new BasicPDF('Laporan Kasus - SIPETRA');
                    $basicPdf->loadHtml($html);
                    $basicPdf->setPaper('A4', $orientation);
                    $basicPdf->render();
                    $filename = 'laporan_kasus_' . $dateNow . '.pdf';
                    ob_end_clean();
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    echo $basicPdf->output();
                    exit();
                } catch (Exception $e) {
                    error_log('BasicPDF error: ' . $e->getMessage());
                }
            }
        }

        // 3) ReliablePDF
        $reliablePdfPath = __DIR__ . '/../libs/reliable_pdf.php';
        if (file_exists($reliablePdfPath)) {
            require_once $reliablePdfPath;
            if (class_exists('ReliablePDF')) {
                try {
                    $reliablePdf = new ReliablePDF('Laporan Kasus - SIPETRA');
                    $reliablePdf->loadHtml($html);
                    $reliablePdf->setPaper('A4', $orientation);
                    $reliablePdf->render();
                    $filename = 'laporan_kasus_' . $dateNow . '.pdf';
                    ob_end_clean();
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    echo $reliablePdf->output();
                    exit();
                } catch (Exception $e) {
                    error_log('ReliablePDF error: ' . $e->getMessage());
                }
            }
        }

        // 4) TCPDFSimple
        $tcpdfSimplePath = __DIR__ . '/../libs/tcpdf_simple.php';
        if (file_exists($tcpdfSimplePath)) {
            require_once $tcpdfSimplePath;
            if (class_exists('TCPDFSimple')) {
                try {
                    $tcpdfSimple = new TCPDFSimple('Laporan Kasus - SIPETRA');
                    $tcpdfSimple->loadHtml($html);
                    $tcpdfSimple->setPaper('A4', $orientation);
                    $tcpdfSimple->render();
                    $filename = 'laporan_kasus_' . $dateNow . '.pdf';
                    ob_end_clean();
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    echo $tcpdfSimple->output();
                    exit();
                } catch (Exception $e) {
                    error_log('TCPDFSimple error: ' . $e->getMessage());
                }
            }
        }
    }

    // Fallback: Gunakan HtmlToPDF untuk HTML yang dioptimalkan untuk print
    $htmlToPdfPath = __DIR__ . '/../libs/html_to_pdf.php';
    if ($renderer === 'html' && file_exists($htmlToPdfPath)) {
        require_once $htmlToPdfPath;
        if (class_exists('HtmlToPDF')) {
            $htmlToPdf = new HtmlToPDF('Laporan Kasus - SIPETRA');
            $htmlToPdf->loadHtml($html);
            $htmlToPdf->setPaper('A4', $orientation);
            $htmlToPdf->render();
            
            // Paksa download sebagai attachment agar tidak membuka tab
            ob_end_clean();
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.html"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            echo $htmlToPdf->output();
            exit();
        }
    }

    // Fallback: Gunakan SimplePDF untuk HTML yang dioptimalkan untuk print
    $simplePdfPath = __DIR__ . '/../libs/simple_pdf.php';
    if ($renderer === 'simple' && file_exists($simplePdfPath)) {
        require_once $simplePdfPath;
        if (class_exists('SimplePDF')) {
            $simplePdf = new SimplePDF('Laporan Kasus - SIPETRA');
            $simplePdf->loadHtml($html);
            $simplePdf->setPaper('A4', $orientation);
            $simplePdf->render();
            
            // Paksa download sebagai attachment agar tidak membuka tab
            ob_end_clean();
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.html"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            echo $simplePdf->output();
            exit();
        }
    }

    // Final fallback: HTML biasa
    $filename = 'laporan_kasus_' . $dateNow . '.html';
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    echo $html;
    exit();
}

if ($type === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.xls"');

	echo "<table border=\"1\">";
	echo "<thead><tr>";
	echo "<th>ID</th><th>Judul</th><th>Deskripsi</th><th>Lokasi</th><th>Kategori</th><th>Status</th><th>Prioritas</th><th>Pelapor</th><th>Tanggal Dibuat</th><th>Tanggal Update</th>";
	echo "</tr></thead><tbody>";

	foreach ($cases as $case) {
		$cols = [
			$case['id_laporan'] ?? $case['id'], // Use id_laporan if available, fallback to id
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
header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.csv"');

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
		$case['id_laporan'] ?? $case['id'], // Use id_laporan if available, fallback to id
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
<?php
// Start output buffering to prevent headers already sent errors
ob_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/cases.php';

$type = $_GET['type'] ?? 'csv';
// Orientation PDF: portrait | landscape (default: portrait)
$orientation = strtolower($_GET['orientation'] ?? 'portrait');
if (!in_array($orientation, ['portrait', 'landscape'], true)) {
    $orientation = 'portrait';
}
// Renderer preference for PDF export to control behavior (default: dompdf to avoid new tab)
$renderer = strtolower($_GET['renderer'] ?? 'dompdf');
// Inline mode: jika 1, kembalikan konten HTML (untuk di-embed iframe) agar tidak pindah halaman
$inline = isset($_GET['inline']) && $_GET['inline'] == '1';

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

$dateNow = date('Y-m-d_H-i-s');

// Printable HTML for PDF via browser's Print to PDF
if ($type === 'pdf') {
    // Clear any previous output
    ob_clean();
    
    // Generate HTML content dengan layout yang lebih baik
    $html  = '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Export PDF - Laporan Kasus</title>';
    $html .= '<style>
        @page {
            size: A4 ' . $orientation . ';
            margin: 1.5cm;
        }
        body {
            font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
            color: #000;
            margin: 0;
            padding: 0;
            font-size: 9px;
            line-height: 1.2;
        }
        h1 {
            font-size: 16px;
            margin: 0 0 8px 0;
            font-weight: bold;
            color: #000;
        }
        .meta {
            font-size: 8px;
            color: #666;
            margin-bottom: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            table-layout: auto; /* biarkan browser/Dompdf hitung lebar */
        }
        th, td {
            border: 1px solid #000;
            padding: 3px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }
        thead th {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 8px;
        }
        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        /* lebar kolom via colgroup agar stabil di Dompdf */
        .col-id { width: 12%; }
        .col-title { width: 18%; }
        .col-desc { width: 22%; }
        .col-location { width: 12%; }
        .col-category { width: 8%; }
        .col-status { width: 6%; }
        .col-priority { width: 6%; }
        .col-reporter { width: 8%; }
        .col-created { width: 4%; }
        .col-updated { width: 4%; }
        
        /* Page break handling */
        .page-break {
            page-break-before: always;
        }
        tbody tr {
            page-break-inside: avoid;
        }
        thead {
            display: table-header-group;
        }
        tfoot {
            display: table-footer-group;
        }
    </style></head><body>';
    $html .= '<h1>Laporan Kasus - SIPETRA</h1>';
    $html .= '<div class="meta">Diunduh: '.date('d/m/Y H:i').' WIB</div>';
    // Gunakan colgroup agar lebar kolom dihormati Dompdf dan tidak memotong data
    $html .= '<table><colgroup>'
        .'<col class="col-id" />'
        .'<col class="col-title" />'
        .'<col class="col-desc" />'
        .'<col class="col-location" />'
        .'<col class="col-category" />'
        .'<col class="col-status" />'
        .'<col class="col-priority" />'
        .'<col class="col-reporter" />'
        .'<col class="col-created" />'
        .'<col class="col-updated" />'
        .'</colgroup><thead><tr>';
    $html .= '<th class="col-id">ID Laporan</th><th class="col-title">Judul</th><th class="col-desc">Deskripsi</th><th class="col-location">Lokasi</th><th class="col-category">Kategori</th><th class="col-status">Status</th><th class="col-priority">Prioritas</th><th class="col-reporter">Pelapor</th><th class="col-created">Dibuat</th><th class="col-updated">Update</th>';
    $html .= '</tr></thead><tbody>';
    foreach ($cases as $case) {
        $cols = [
            htmlspecialchars($case['id_laporan'] ?? $case['id'], ENT_QUOTES, 'UTF-8'),
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
        $colClasses = ['col-id', 'col-title', 'col-desc', 'col-location', 'col-category', 'col-status', 'col-priority', 'col-reporter', 'col-created', 'col-updated'];
        
        $html .= '<tr>';
        foreach ($cols as $index => $col) {
            $html .= '<td class="'.$colClasses[$index].'">'.$col.'</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table></body></html>';

    // Opsi PrintOptimized: gunakan jika renderer=print (default tidak aktif untuk mencegah tab baru)
    if ($renderer === 'print') {
        $printOptimizedPath = __DIR__ . '/../libs/print_optimized.php';
        if (file_exists($printOptimizedPath)) {
            require_once $printOptimizedPath;
            if (class_exists('PrintOptimized')) {
                try {
                    $printOptimized = new PrintOptimized('Laporan Kasus - SIPETRA');
                    $printOptimized->loadHtml($html);
                    $printOptimized->setPaper('A4', $orientation);
                    $printOptimized->render();
                    // Tampilkan inline; jika inline=1 maka hanya kirim HTML (untuk di-embed via fetch+iframe)
                    $content = $printOptimized->output();
                    // Sesuaikan orientasi @page
                    $content = str_replace('size: A4 portrait', 'size: A4 ' . $orientation, $content);
                    if ($inline) {
                        // Kembalikan HTML mentah untuk di-embed pada iframe oleh frontend
                        ob_end_clean();
                        header('Content-Type: text/plain; charset=utf-8');
                        echo $content;
                        exit();
                    } else {
                        // Mode lama: tampilkan halaman print langsung
                        $content = str_replace('</body>', '<script>window.onload=function(){setTimeout(function(){window.print();},50);};</script></body>', $content);
                        ob_end_clean();
                        header('Content-Type: text/html; charset=utf-8');
                        header('Content-Disposition: inline');
                        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                        header('Pragma: no-cache');
                        echo $content;
                        exit();
                    }
                } catch (Exception $e) {
                    error_log('PrintOptimized error: ' . $e->getMessage());
                }
            }
        }
    }

    // Jika bukan mode print, lakukan rantai fallback yang SELALU menghasilkan PDF
    if ($renderer !== 'print') {
        // 1) Dompdf
        $autoloadPath = __DIR__ . '/../libs/dompdf_autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
            if (class_exists('Dompdf\\Dompdf')) {
                try {
                    $dompdf = new Dompdf\Dompdf();
                    $dompdf->loadHtml($html, 'UTF-8');
                    $dompdf->setPaper('A4', $orientation);
                    $dompdf->getOptions()->set([
                        'defaultFont' => 'DejaVu Sans',
                        'isRemoteEnabled' => false,
                        'isHtml5ParserEnabled' => true,
                        'isPhpEnabled' => false,
                        'isFontSubsettingEnabled' => true,
                        'defaultMediaType' => 'print',
                        'debugPng' => false
                    ]);
                    $dompdf->render();
                    $filename = 'laporan_kasus_' . $dateNow . '.pdf';
                    ob_end_clean();
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    echo $dompdf->output();
                    exit();
                } catch (Exception $e) {
                    error_log('Dompdf error: ' . $e->getMessage());
                }
            }
        }

        // 2) BasicPDF
        $basicPdfPath = __DIR__ . '/../libs/basic_pdf.php';
        if (file_exists($basicPdfPath)) {
            require_once $basicPdfPath;
            if (class_exists('BasicPDF')) {
                try {
                    $basicPdf = new BasicPDF('Laporan Kasus - SIPETRA');
                    $basicPdf->loadHtml($html);
                    $basicPdf->setPaper('A4', $orientation);
                    $basicPdf->render();
                    $filename = 'laporan_kasus_' . $dateNow . '.pdf';
                    ob_end_clean();
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    echo $basicPdf->output();
                    exit();
                } catch (Exception $e) {
                    error_log('BasicPDF error: ' . $e->getMessage());
                }
            }
        }

        // 3) ReliablePDF
        $reliablePdfPath = __DIR__ . '/../libs/reliable_pdf.php';
        if (file_exists($reliablePdfPath)) {
            require_once $reliablePdfPath;
            if (class_exists('ReliablePDF')) {
                try {
                    $reliablePdf = new ReliablePDF('Laporan Kasus - SIPETRA');
                    $reliablePdf->loadHtml($html);
                    $reliablePdf->setPaper('A4', $orientation);
                    $reliablePdf->render();
                    $filename = 'laporan_kasus_' . $dateNow . '.pdf';
                    ob_end_clean();
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    echo $reliablePdf->output();
                    exit();
                } catch (Exception $e) {
                    error_log('ReliablePDF error: ' . $e->getMessage());
                }
            }
        }

        // 4) TCPDFSimple
        $tcpdfSimplePath = __DIR__ . '/../libs/tcpdf_simple.php';
        if (file_exists($tcpdfSimplePath)) {
            require_once $tcpdfSimplePath;
            if (class_exists('TCPDFSimple')) {
                try {
                    $tcpdfSimple = new TCPDFSimple('Laporan Kasus - SIPETRA');
                    $tcpdfSimple->loadHtml($html);
                    $tcpdfSimple->setPaper('A4', $orientation);
                    $tcpdfSimple->render();
                    $filename = 'laporan_kasus_' . $dateNow . '.pdf';
                    ob_end_clean();
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    echo $tcpdfSimple->output();
                    exit();
                } catch (Exception $e) {
                    error_log('TCPDFSimple error: ' . $e->getMessage());
                }
            }
        }
    }

    // Fallback: Gunakan HtmlToPDF untuk HTML yang dioptimalkan untuk print
    $htmlToPdfPath = __DIR__ . '/../libs/html_to_pdf.php';
    if ($renderer === 'html' && file_exists($htmlToPdfPath)) {
        require_once $htmlToPdfPath;
        if (class_exists('HtmlToPDF')) {
            $htmlToPdf = new HtmlToPDF('Laporan Kasus - SIPETRA');
            $htmlToPdf->loadHtml($html);
            $htmlToPdf->setPaper('A4', $orientation);
            $htmlToPdf->render();
            
            // Paksa download sebagai attachment agar tidak membuka tab
            ob_end_clean();
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.html"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            echo $htmlToPdf->output();
            exit();
        }
    }

    // Fallback: Gunakan SimplePDF untuk HTML yang dioptimalkan untuk print
    $simplePdfPath = __DIR__ . '/../libs/simple_pdf.php';
    if ($renderer === 'simple' && file_exists($simplePdfPath)) {
        require_once $simplePdfPath;
        if (class_exists('SimplePDF')) {
            $simplePdf = new SimplePDF('Laporan Kasus - SIPETRA');
            $simplePdf->loadHtml($html);
            $simplePdf->setPaper('A4', $orientation);
            $simplePdf->render();
            
            // Paksa download sebagai attachment agar tidak membuka tab
            ob_end_clean();
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.html"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            echo $simplePdf->output();
            exit();
        }
    }

    // Final fallback: HTML biasa
    $filename = 'laporan_kasus_' . $dateNow . '.html';
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    echo $html;
    exit();
}

if ($type === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.xls"');

	echo "<table border=\"1\">";
	echo "<thead><tr>";
	echo "<th>ID</th><th>Judul</th><th>Deskripsi</th><th>Lokasi</th><th>Kategori</th><th>Status</th><th>Prioritas</th><th>Pelapor</th><th>Tanggal Dibuat</th><th>Tanggal Update</th>";
	echo "</tr></thead><tbody>";

	foreach ($cases as $case) {
		$cols = [
			$case['id_laporan'] ?? $case['id'], // Use id_laporan if available, fallback to id
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
header('Content-Disposition: attachment; filename="laporan_kasus_' . $dateNow . '.csv"');

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
		$case['id_laporan'] ?? $case['id'], // Use id_laporan if available, fallback to id
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