<?php
// Check database data for dashboard
require_once 'config/database.php';

$db = new Database();

echo "<h1>Database Data Check</h1>";

// Check cases
$cases = $db->findAll('cases');
echo "<h2>Cases Data:</h2>";
echo "<p>Total Cases: " . count($cases) . "</p>";

if (count($cases) > 0) {
    echo "<h3>Sample Case:</h3>";
    echo "<pre>" . print_r($cases[0], true) . "</pre>";
    
    // Check status distribution
    $statusCounts = [];
    foreach ($cases as $case) {
        $status = $case['status'];
        if (!isset($statusCounts[$status])) {
            $statusCounts[$status] = 0;
        }
        $statusCounts[$status]++;
    }
    
    echo "<h3>Status Distribution:</h3>";
    echo "<ul>";
    foreach ($statusCounts as $status => $count) {
        echo "<li>$status: $count</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No cases found in database</p>";
}

// Check categories
$categories = $db->findAll('categories');
echo "<h2>Categories Data:</h2>";
echo "<p>Total Categories: " . count($categories) . "</p>";

if (count($categories) > 0) {
    echo "<h3>Sample Category:</h3>";
    echo "<pre>" . print_r($categories[0], true) . "</pre>";
    
    // Check category distribution
    $categoryCounts = [];
    foreach ($cases as $case) {
        $categoryId = $case['category_id'];
        $category = $db->find('categories', $categoryId);
        $categoryName = $category['name'] ?? 'Unknown';
        if (!isset($categoryCounts[$categoryName])) {
            $categoryCounts[$categoryName] = 0;
        }
        $categoryCounts[$categoryName]++;
    }
    
    echo "<h3>Category Distribution:</h3>";
    echo "<ul>";
    foreach ($categoryCounts as $category => $count) {
        echo "<li>$category: $count</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No categories found in database</p>";
}

// Test DashboardStats controller
echo "<h2>DashboardStats Controller Test:</h2>";
require_once 'controllers/dashboard_stats.php';

$dashboardStats = new DashboardStats();
$stats = $dashboardStats->getAllStats();

echo "<h3>All Stats:</h3>";
echo "<pre>" . print_r($stats, true) . "</pre>";

echo "<h3>Status Data for Chart:</h3>";
echo "<pre>" . print_r($stats['cases_by_status'], true) . "</pre>";

echo "<h3>Category Data for Chart:</h3>";
echo "<pre>" . print_r($stats['cases_by_category'], true) . "</pre>";
?>
