<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/dashboard_stats.php';

// Initialize dashboard stats
$dashboardStats = new DashboardStats();
$stats = $dashboardStats->getAllStats();

// Extract data
$totalCases = $stats['total_cases'];
$todayCases = $stats['today_cases'];
$monthCases = $stats['month_cases'];
$statusData = $stats['cases_by_status'];
$categoryData = $stats['cases_by_category'];
$locationData = $stats['cases_by_location'];
$locationTrendData = $stats['cases_by_location_trend'];
$timePeriodData = $stats['cases_by_time_period'];
$quarterData = $stats['cases_by_quarter'];
$equipmentTypeData = $stats['cases_by_equipment_type'];
$equipmentCriticalityData = $stats['cases_by_equipment_criticality'];
$recentCases = $stats['recent_activities'];

// Prepare data for charts
$statusLabels = [];
$statusCounts = [];
$statusColors = [
    'Pending' => '#FF6B6B',
    'In Progress' => '#4ECDC4', 
    'Completed' => '#45B7D1',
    'Cancelled' => '#96CEB4'
];

foreach ($statusData as $status) {
    $statusLabels[] = $status['status'];
    $statusCounts[] = $status['count'];
}

$categoryLabels = [];
$categoryCounts = [];
foreach ($categoryData as $category) {
    $categoryLabels[] = $category['category_name'];
    $categoryCounts[] = $category['count'];
}

// Prepare location data
$locationLabels = [];
$locationCounts = [];
foreach ($locationData as $location) {
    $locationLabels[] = $location['location'];
    $locationCounts[] = $location['count'];
}

// Prepare location trend data (monthly data for line chart)
$locationTrendLabels = [];
$locationTrendData = [];
$trendDataByLocation = [];

// Group trend data by location
foreach ($locationTrendData as $trend) {
    if (!isset($trendDataByLocation[$trend['location']])) {
        $trendDataByLocation[$trend['location']] = [];
    }
    $trendDataByLocation[$trend['location']][$trend['month']] = $trend['count'];
}

// Get unique months for labels
$allMonths = [];
foreach ($locationTrendData as $trend) {
    if (!in_array($trend['month'], $allMonths)) {
        $allMonths[] = $trend['month'];
    }
}
sort($allMonths);
$locationTrendLabels = $allMonths;

// Prepare time period data
$timeLabels = [];
$timeCounts = [];
foreach ($timePeriodData as $time) {
    $timeLabels[] = $time['month'];
    $timeCounts[] = $time['count'];
}

// Prepare quarter data
$quarterLabels = [];
$quarterCounts = [];
foreach ($quarterData as $quarter) {
    $quarterLabels[] = $quarter['quarter'];
    $quarterCounts[] = $quarter['count'];
}

// Prepare equipment type data
$equipmentLabels = [];
$equipmentCounts = [];
foreach ($equipmentTypeData as $equipment) {
    $equipmentLabels[] = $equipment['equipment_type'];
    $equipmentCounts[] = $equipment['count'];
}

// Prepare equipment criticality data
$criticalityLabels = [];
$criticalityCounts = [];
foreach ($equipmentCriticalityData as $criticality) {
    $criticalityLabels[] = $criticality['criticality'];
    $criticalityCounts[] = $criticality['count'];
}
?>

<div class="dashboard">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="index.php?page=dashboard">Beranda</a> / Dashboard
    </div>
    
    <div class="page-header">
        <div>
        <h1>Dashboard</h1>
            <p>Ringkasan aktivitas pelaporan internal</p>
        </div>
    </div>
    
    <!-- Quick Insight -->
    <div class="quick-insight">
        <h4>⚡ Quick Insight</h4>
        <p id="quickInsightText">Lokasi paling sering melapor: Palangka Raya</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                📄
            </div>
            <div class="stat-content">
                <h3 class="count-up" data-target="<?php echo $totalCases; ?>">0</h3>
                <p>Total Kasus</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                📅
            </div>
            <div class="stat-content">
                <h3 class="count-up" data-target="<?php echo $todayCases; ?>">0</h3>
                <p>Hari Ini</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                📊
            </div>
            <div class="stat-content">
                <h3 class="count-up" data-target="<?php echo $monthCases; ?>">0</h3>
                <p>Bulan Ini</p>
            </div>
        </div>
    </div>

    <!-- Interactive Statistics Section -->
    <div class="interactive-stats-section">
        <div class="stats-controls">
            <div class="stats-dropdown">
                <label for="statsCategory">Pilih Kategori Statistik:</label>
                <select id="statsCategory" class="stats-select">
                    <option value="location">1️⃣ Lokasi Transmisi</option>
                    <option value="time">2️⃣ Periode Waktu</option>
                    <option value="equipment">3️⃣ Jenis Peralatan</option>
                </select>
            </div>
        </div>
        
        <div class="charts-container">
            <div class="chart-container">
                <button class="chart-action-btn" onclick="window.location.href='index.php?page=cases'">Lihat Detail</button>
                <h3 id="chart1Title">Jumlah Laporan per Lokasi Transmisi</h3>
                <canvas id="chart1"></canvas>
            </div>
            
            <div class="chart-container">
                <button class="chart-action-btn" onclick="window.location.href='index.php?page=cases'">Lihat Detail</button>
                <h3 id="chart2Title">Tren Laporan per Lokasi</h3>
                <canvas id="chart2"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Cases -->
    <div class="recent-cases">
        <div class="section-header">
            <h3>Kasus Terbaru</h3>
        </div>
        
        <div class="cases-list">
            <?php if (empty($recentCases)): ?>
                <div class="no-cases">
                    <i class="fas fa-inbox"></i>
                    <p>Belum ada kasus yang dilaporkan</p>
                </div>
            <?php else: ?>
            <?php foreach ($recentCases as $case): ?>
                <div class="case-card">
                    <div class="case-header">
                            <div class="case-category" style="background-color: <?php echo $case['category_color'] ?? '#3B82F6'; ?>">
                                <?php echo htmlspecialchars($case['category_name'] ?? 'Unknown'); ?>
                            </div>
                            <div class="case-status status-<?php echo strtolower(str_replace(' ', '_', $case['status'])); ?>">
                                <?php echo htmlspecialchars($case['status']); ?>
                        </div>
                        </div>
                        
                    <h4><?php echo htmlspecialchars($case['title']); ?></h4>
                        <p><?php echo htmlspecialchars(substr($case['description'], 0, 100)) . '...'; ?></p>
                        
                    <div class="case-footer">
                            <div class="case-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($case['location']); ?>
                            </div>
                            <div class="case-date">
                                <i class="fas fa-clock"></i>
                                <?php echo date('d M Y', strtotime($case['created_at'])); ?>
                            </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// SIPETRA Color Theme
const SIPETRA_COLORS = {
    primary: '#4A90E2',
    green: '#7ED321',
    coral: '#FF6B6B',
    lightBlue: '#87CEEB',
    darkBlue: '#1E40AF'
};

// Chart data
const chartData = {
    location: {
        chart1: {
            title: 'Jumlah Laporan per Lokasi Transmisi',
            type: 'bar',
            labels: <?php echo json_encode($locationLabels); ?>,
            data: <?php echo json_encode($locationCounts); ?>
        },
        chart2: {
            title: 'Tren Laporan per Lokasi',
            type: 'line',
            labels: <?php echo json_encode($locationTrendLabels); ?>,
            data: <?php echo json_encode($trendDataByLocation); ?>
        }
    },
    time: {
        chart1: {
            title: 'Jumlah Laporan per Bulan',
            type: 'line',
            labels: <?php echo json_encode($timeLabels); ?>,
            data: <?php echo json_encode($timeCounts); ?>
        },
        chart2: {
            title: 'Proporsi Laporan per Kuartal',
            type: 'pie',
            labels: <?php echo json_encode($quarterLabels); ?>,
            data: <?php echo json_encode($quarterCounts); ?>
        }
    },
    equipment: {
        chart1: {
            title: 'Jumlah Laporan per Jenis Peralatan',
            type: 'bar',
            labels: <?php echo json_encode($equipmentLabels); ?>,
            data: <?php echo json_encode($equipmentCounts); ?>
        },
        chart2: {
            title: 'Persentase Alat Kritis vs Non-Kritis',
    type: 'doughnut',
            labels: <?php echo json_encode($criticalityLabels); ?>,
            data: <?php echo json_encode($criticalityCounts); ?>
        }
    }
};

let chart1, chart2;

// Initialize charts
function initializeCharts() {
    const category = document.getElementById('statsCategory').value;
    const data = chartData[category];
    
    // Update titles
    document.getElementById('chart1Title').textContent = data.chart1.title;
    document.getElementById('chart2Title').textContent = data.chart2.title;
    
    // Destroy existing charts
    if (chart1) chart1.destroy();
    if (chart2) chart2.destroy();
    
    // Create Chart 1
    const ctx1 = document.getElementById('chart1').getContext('2d');
    
    // Special handling for location area chart
    if (category === 'location' && data.chart1.type === 'bar') {
        chart1 = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: data.chart1.labels,
                datasets: [{
                    label: data.chart1.title,
                    data: data.chart1.data,
                    backgroundColor: 'rgba(74, 144, 226, 0.2)',
                    borderColor: SIPETRA_COLORS.primary,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#FFFFFF',
                    pointBorderColor: SIPETRA_COLORS.primary,
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
    options: {
        responsive: true,
                maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: '#333333',
                            font: {
                                family: 'Poppins',
                                size: 12
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#333333',
                            font: {
                                family: 'Poppins',
                                size: 12
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    } else {
        // Default chart creation for other types
        chart1 = new Chart(ctx1, {
            type: data.chart1.type,
            data: {
                labels: data.chart1.labels,
                datasets: [{
                    label: data.chart1.title,
                    data: data.chart1.data,
                    backgroundColor: data.chart1.type === 'bar' ? 
                        [SIPETRA_COLORS.primary, SIPETRA_COLORS.green, SIPETRA_COLORS.coral, SIPETRA_COLORS.lightBlue] :
                        SIPETRA_COLORS.primary,
                    borderColor: SIPETRA_COLORS.darkBlue,
                    borderWidth: 2,
                    fill: data.chart1.type === 'line' ? true : false,
                    tension: data.chart1.type === 'line' ? 0.4 : 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: data.chart1.type === 'pie' || data.chart1.type === 'doughnut',
                        position: 'bottom'
                    }
                },
                scales: data.chart1.type === 'line' || data.chart1.type === 'bar' ? {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                } : {},
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    // Create Chart 2
    const ctx2 = document.getElementById('chart2').getContext('2d');
    
    // Special handling for location trend line chart
    if (category === 'location' && data.chart2.type === 'line') {
        // Prepare datasets for multiple locations
        const datasets = [];
        const colors = [SIPETRA_COLORS.coral, SIPETRA_COLORS.primary, SIPETRA_COLORS.green, SIPETRA_COLORS.lightBlue];
        let colorIndex = 0;
        
        for (const [location, monthData] of Object.entries(data.chart2.data)) {
            const locationData = data.chart2.labels.map(month => monthData[month] || 0);
            
            datasets.push({
                label: location,
                data: locationData,
                borderColor: colors[colorIndex % colors.length],
                backgroundColor: colors[colorIndex % colors.length] + '20',
                borderWidth: 3,
                fill: false,
                tension: 0.4,
                pointBackgroundColor: '#FFFFFF',
                pointBorderColor: colors[colorIndex % colors.length],
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            });
            colorIndex++;
        }
        
        chart2 = new Chart(ctx2, {
            type: 'line',
            data: {
                labels: data.chart2.labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            color: '#333333',
                            font: {
                                family: 'Poppins',
                                size: 12
                            },
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: '#333333',
                            font: {
                                family: 'Poppins',
                                size: 12
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#333333',
                            font: {
                                family: 'Poppins',
                                size: 12
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    } else {
        // Default chart creation for other types
        chart2 = new Chart(ctx2, {
            type: data.chart2.type,
            data: {
                labels: data.chart2.labels,
                datasets: [{
                    label: data.chart2.title,
                    data: data.chart2.data,
                    backgroundColor: data.chart2.type === 'pie' || data.chart2.type === 'doughnut' ?
                        [SIPETRA_COLORS.primary, SIPETRA_COLORS.green, SIPETRA_COLORS.coral, SIPETRA_COLORS.lightBlue] :
                        SIPETRA_COLORS.green,
                    borderColor: SIPETRA_COLORS.darkBlue,
                    borderWidth: 2,
                    fill: data.chart2.type === 'line' ? true : false,
                    tension: data.chart2.type === 'line' ? 0.4 : 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: data.chart2.type === 'pie' || data.chart2.type === 'doughnut',
                        position: 'bottom'
                    }
                },
                scales: data.chart2.type === 'line' || data.chart2.type === 'bar' ? {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                } : {},
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }
}

// Count-up animation
function animateCountUp() {
    const counters = document.querySelectorAll('.count-up');
    
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const duration = 2000; // 2 seconds
        const increment = target / (duration / 16); // 60fps
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            counter.textContent = Math.floor(current);
        }, 16);
    });
}

// Icon animation
function animateIcons() {
    const icons = document.querySelectorAll('.stat-icon');
    
    icons.forEach((icon, index) => {
        setTimeout(() => {
            icon.style.animation = 'bounceIn 0.6s ease-out';
        }, index * 200);
    });
}

// Event listener for dropdown change
document.addEventListener('DOMContentLoaded', function() {
    // Initialize animations
    setTimeout(() => {
        animateCountUp();
        animateIcons();
    }, 500);
    
    initializeCharts();
    
    document.getElementById('statsCategory').addEventListener('change', function() {
        initializeCharts();
    });
});
</script> 