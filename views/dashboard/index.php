<?php
require_once __DIR__ . '/../../config/database.php';

// Get statistics
$cases = $db->findAll('cases');
$totalCases = count($cases);

$todayCases = 0;
$monthCases = 0;
$today = date('Y-m-d');
$month = date('Y-m');

foreach ($cases as $case) {
    $caseDate = date('Y-m-d', strtotime($case['created_at']));
    $caseMonth = date('Y-m', strtotime($case['created_at']));
    
    if ($caseDate === $today) {
        $todayCases++;
    }
    
    if ($caseMonth === $month) {
        $monthCases++;
    }
}

// Get cases by status
$statusData = [];
$statusCounts = [];
foreach ($cases as $case) {
    $status = $case['status'];
    if (!isset($statusCounts[$status])) {
        $statusCounts[$status] = 0;
    }
    $statusCounts[$status]++;
}

foreach ($statusCounts as $status => $count) {
    $statusData[] = ['status' => $status, 'count' => $count];
}

// Get cases by category
$categories = $db->findAll('categories');
$categoryData = [];

foreach ($categories as $category) {
    $categoryCases = array_filter($cases, function($case) use ($category) {
        return $case['category_id'] == $category['id'];
    });
    
    $categoryData[] = [
        'name' => $category['name'],
        'count' => count($categoryCases)
    ];
}

// Get recent cases
$recentCases = array_slice($cases, 0, 5);
foreach ($recentCases as $index => $case) {
    $category = $db->find('categories', $case['category_id']);
    $recentCases[$index]['category_name'] = $category['name'] ?? 'Unknown';
    $recentCases[$index]['category_color'] = $category['color'] ?? '#3B82F6';
}
?>

<div class="dashboard">
    <div class="page-header">
        <h1>Dashboard</h1>
        <p>Ringkasan laporan kasus TVRI Kalimantan Tengah</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $totalCases; ?></h3>
                <p>Total Kasus</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $todayCases; ?></h3>
                <p>Hari Ini</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $monthCases; ?></h3>
                <p>Bulan Ini</p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <div class="chart-container">
            <h3>Kasus berdasarkan Status</h3>
            <canvas id="statusChart"></canvas>
        </div>
        
        <div class="chart-container">
            <h3>Kasus berdasarkan Kategori</h3>
            <canvas id="categoryChart"></canvas>
        </div>
    </div>

    <!-- Recent Cases -->
    <div class="recent-cases">
        <div class="section-header">
            <h3>Kasus Terbaru</h3>
            <a href="index.php?page=cases" class="btn btn-outline">Lihat Semua</a>
        </div>
        
        <div class="cases-list">
            <?php foreach ($recentCases as $case): ?>
                <div class="case-card">
                    <div class="case-header">
                        <div class="case-category" style="background-color: <?php echo $case['category_color']; ?>">
                            <?php echo htmlspecialchars($case['category_name']); ?>
                        </div>
                        <div class="case-status status-<?php echo $case['status']; ?>">
                            <?php 
                            $statusLabels = [
                                'pending' => 'Menunggu',
                                'in_progress' => 'Sedang Dikerjakan',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan'
                            ];
                            echo $statusLabels[$case['status']] ?? $case['status'];
                            ?>
                        </div>
                    </div>
                    <h4><?php echo htmlspecialchars($case['title']); ?></h4>
                    <p><?php echo htmlspecialchars($case['description']); ?></p>
                    <div class="case-footer">
                        <span class="case-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($case['location']); ?>
                        </span>
                        <span class="case-date">
                            <i class="fas fa-calendar"></i>
                            <?php echo date('d/m/Y', strtotime($case['created_at'])); ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($statusData, 'status')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($statusData, 'count')); ?>,
            backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Category Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
const categoryChart = new Chart(categoryCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($categoryData, 'name')); ?>,
        datasets: [{
            label: 'Jumlah Kasus',
            data: <?php echo json_encode(array_column($categoryData, 'count')); ?>,
            backgroundColor: '#3B82F6'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script> 