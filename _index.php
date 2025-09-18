<?php
session_start();
// Enable output buffering so nested pages can safely call header() redirects
ob_start();

// Database connection
require_once 'config/database.php';
require_once 'config/auth.php';

// Simple routing
$page = $_GET['page'] ?? 'landing';

// Check if user is logged in
if (!isLoggedIn() && $page !== 'login' && $page !== 'landing' && $page !== 'register') {
    header('Location: index.php?page=login');
    exit();
}

// Route to appropriate page
switch ($page) {
    case 'landing':
        include 'views/landing-simple.php';
        break;
    case 'login':
        include 'views/auth/login.php';
        break;
    case 'register':
        include 'views/auth/register.php';
        break;
    case 'dashboard':
        // Check user role and redirect accordingly
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            // Include header and footer for authenticated pages
            include 'views/layouts/header.php';
            include 'views/dashboard/index.php';
            include 'views/layouts/footer.php';
        } else {
            // Member dashboard
            include 'views/member/dashboard.php';
        }
        break;
    case 'member/cases/create':
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user') {
            include 'views/member/cases/create.php';
        } else {
            header('Location: index.php?page=login');
            exit();
        }
        break;
    case 'member/cases':
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user') {
            include 'views/member/cases/index.php';
        } else {
            header('Location: index.php?page=login');
            exit();
        }
        break;
    case 'member/profile':
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user') {
            include 'views/member/profile.php';
        } else {
            header('Location: index.php?page=login');
            exit();
        }
        break;
    case 'cases':
        include 'views/layouts/header.php';
        include 'views/cases/index.php';
        include 'views/layouts/footer.php';
        break;
    case 'cases/create':
        include 'views/layouts/header.php';
        include 'views/cases/create.php';
        include 'views/layouts/footer.php';
        break;
    case 'cases/edit':
        include 'views/layouts/header.php';
        include 'views/cases/edit.php';
        include 'views/layouts/footer.php';
        break;
    case 'cases/view':
        include 'views/layouts/header.php';
        include 'views/cases/view.php';
        include 'views/layouts/footer.php';
        break;
    case 'cases/delete':
        // Controller-only route (no layout)
        include 'controllers/cases/delete.php';
        break;
    case 'profile':
        include 'views/layouts/header.php';
        include 'views/profile/index.php';
        include 'views/layouts/footer.php';
        break;
    case 'export':
        // Controller-only route (no layout)
        include 'controllers/export.php';
        break;
    case 'error':
        include 'views/error.php';
        break;
    case 'logout':
        include 'controllers/logout.php';
        break;
    default:
        header('Location: index.php?page=landing');
        exit();
}
?> 
<?php
// Flush output buffer
ob_end_flush();
?>