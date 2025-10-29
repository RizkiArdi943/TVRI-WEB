<?php
// Quick test untuk memverifikasi perbaikan duplikasi header
echo "<h1>Quick Header Duplication Test</h1>";

// Test header file
$headerContent = file_get_contents('views/layouts/header.php');

// Check for duplications
$htmlCount = substr_count($headerContent, '<!DOCTYPE html>');
$headCount = substr_count($headerContent, '<head>');
$bodyCount = substr_count($headerContent, '<body');
$styleCount = substr_count($headerContent, '<style>');

echo "<h2>Header File Analysis:</h2>";
echo "<ul>";
echo "<li>DOCTYPE HTML tags: $htmlCount " . ($htmlCount == 1 ? "✓" : "✗") . "</li>";
echo "<li>HEAD tags: $headCount " . ($headCount == 1 ? "✓" : "✗") . "</li>";
echo "<li>BODY tags: $bodyCount " . ($bodyCount == 1 ? "✓" : "✗") . "</li>";
echo "<li>STYLE blocks: $styleCount " . ($styleCount == 1 ? "✓" : "✗") . "</li>";
echo "</ul>";

// Test dashboard file
$dashboardContent = file_get_contents('views/dashboard/index.php');
$dashboardHtmlCount = substr_count($dashboardContent, '<!DOCTYPE html>');

echo "<h2>Dashboard File Analysis:</h2>";
echo "<ul>";
echo "<li>DOCTYPE HTML tags: $dashboardHtmlCount " . ($dashboardHtmlCount == 0 ? "✓" : "✗") . "</li>";
echo "</ul>";

if ($htmlCount == 1 && $headCount == 1 && $bodyCount == 1 && $styleCount == 1 && $dashboardHtmlCount == 0) {
    echo "<h2>✅ All Tests Passed!</h2>";
    echo "<p>Header duplication has been successfully fixed.</p>";
} else {
    echo "<h2>❌ Issues Found</h2>";
    echo "<p>There are still duplication issues that need to be addressed.</p>";
}

echo "<h2>Test Pages:</h2>";
echo '<a href="index.php?page=dashboard" target="_blank">Dashboard</a><br>';
echo '<a href="index.php?page=cases" target="_blank">Cases</a><br>';
echo '<a href="index.php?page=users" target="_blank">Users</a><br>';
echo '<a href="index.php?page=profile" target="_blank">Profile</a><br>';
?>
