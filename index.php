<?php
// Main entry point for TVRI Web Application
session_start();

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once 'config/database.php';
require_once 'config/browser_auth.php';

// Get page parameter - handle root path
$page = isset($_GET['page']) ? $_GET['page'] : 'landing';

// Handle direct access to root path
if ($_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '/index.php') {
    $page = 'landing';
}

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
            include 'views/layouts/header.php';
            include 'views/member/dashboard.php';
            include 'views/layouts/footer.php';
        }
        break;
        
    case 'cases':
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            include 'views/layouts/header.php';
            include 'views/cases/index.php';
            include 'views/layouts/footer.php';
        } else {
            header('Location: index.php?page=member/cases');
            exit();
        }
        break;
        
    case 'cases/create':
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            include 'views/layouts/header.php';
            include 'views/cases/create.php';
            include 'views/layouts/footer.php';
        } else {
            header('Location: index.php?page=member/cases/create');
            exit();
        }
        break;
        
    case 'cases/edit':
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            include 'views/layouts/header.php';
            include 'views/cases/edit.php';
            include 'views/layouts/footer.php';
        } else {
            header('Location: index.php?page=member/cases');
            exit();
        }
        break;
        
    case 'cases/view':
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            include 'views/layouts/header.php';
            include 'views/cases/view.php';
            include 'views/layouts/footer.php';
        } else {
            header('Location: index.php?page=member/cases');
            exit();
        }
        break;
        
    case 'cases/delete':
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            // Controller-only route (no layout)
            include 'controllers/cases/delete.php';
        } else {
            header('Location: index.php?page=member/cases');
            exit();
        }
        break;
        
    case 'users':
        // Check if user is logged in and is admin
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: index.php?page=login');
            exit;
        }
        include 'views/layouts/header.php';
        include 'views/users/index.php';
        include 'views/layouts/footer.php';
        break;
        
    case 'users/create':
        // Check if user is logged in and is admin
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: index.php?page=login');
            exit;
        }
        include 'views/layouts/header.php';
        include 'views/users/create.php';
        include 'views/layouts/footer.php';
        break;
        
    case 'profile':
        include 'views/layouts/header.php';
        include 'views/profile/index.php';
        include 'views/layouts/footer.php';
        break;
        
    case 'member/dashboard':
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user') {
            include 'views/layouts/header.php';
            include 'views/member/dashboard.php';
            include 'views/layouts/footer.php';
        } else {
            header('Location: index.php?page=login');
            exit();
        }
        break;
        
    case 'member/cases':
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user') {
            include 'views/layouts/header.php';
            include 'views/member/cases/index.php';
            include 'views/layouts/footer.php';
        } else {
            header('Location: index.php?page=login');
            exit();
        }
        break;
        
    case 'member/cases/create':
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user') {
            include 'views/layouts/header.php';
            include 'views/member/cases/create.php';
            include 'views/layouts/footer.php';
        } else {
            header('Location: index.php?page=login');
            exit();
        }
        break;
        
    case 'member/profile':
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user') {
            include 'views/layouts/header.php';
            include 'views/member/profile.php';
            include 'views/layouts/footer.php';
        } else {
            header('Location: index.php?page=login');
            exit();
        }
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
