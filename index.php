<?php
// Enable output buffering so nested pages can safely call header() redirects
ob_start();

// Database connection
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/browser_auth.php';

// Start session for compatibility
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
        include __DIR__ . '/views/landing-simple.php';
        break;
    case 'login':
        include __DIR__ . '/views/auth/login.php';
        break;
    case 'register':
        include __DIR__ . '/views/auth/register.php';
        break;
    case 'dashboard':
        // Check user role and redirect accordingly
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            // Include header and footer for authenticated pages
            include __DIR__ . '/views/layouts/header.php';
            include __DIR__ . '/views/dashboard/index.php';
            include __DIR__ . '/views/layouts/footer.php';
        } else {
            // Member dashboard
            include __DIR__ . '/views/member/dashboard.php';
        }
        break;
    case 'member/cases/create':
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user') {
            include __DIR__ . '/views/member/cases/create.php';
        } else {
            header('Location: index.php?page=login');
            exit();
        }
        break;
    case 'member/cases':
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user') {
            include __DIR__ . '/views/member/cases/index.php';
        } else {
            header('Location: index.php?page=login');
            exit();
        }
        break;
    case 'member/profile':
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user') {
            include __DIR__ . '/views/member/profile.php';
        } else {
            header('Location: index.php?page=login');
            exit();
        }
        break;
    case 'cases':
        include __DIR__ . '/views/layouts/header.php';
        include __DIR__ . '/views/cases/index.php';
        include __DIR__ . '/views/layouts/footer.php';
        break;
    case 'cases/create':
        include __DIR__ . '/views/layouts/header.php';
        include __DIR__ . '/views/cases/create.php';
        include __DIR__ . '/views/layouts/footer.php';
        break;
    case 'cases/edit':
        include __DIR__ . '/views/layouts/header.php';
        include __DIR__ . '/views/cases/edit.php';
        include __DIR__ . '/views/layouts/footer.php';
        break;
    case 'cases/view':
        include __DIR__ . '/views/layouts/header.php';
        include __DIR__ . '/views/cases/view.php';
        include __DIR__ . '/views/layouts/footer.php';
        break;
    case 'cases/delete':
        // Controller-only route (no layout)
        include __DIR__ . '/controllers/cases/delete.php';
        break;
    case 'profile':
        include __DIR__ . '/views/layouts/header.php';
        include __DIR__ . '/views/profile/index.php';
        include __DIR__ . '/views/layouts/footer.php';
        break;
    case 'users':
        // Check if user is admin
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            include __DIR__ . '/views/layouts/header.php';
            include __DIR__ . '/views/users/index.php';
            include __DIR__ . '/views/layouts/footer.php';
        } else {
            header('Location: index.php?page=login');
            exit();
        }
        break;
    case 'users/create':
        // Controller-only route (no layout)
        include __DIR__ . '/views/users/create_clean.php';
        break;
    case 'users/get':
        // Controller-only route (no layout)
        include __DIR__ . '/views/users/get_clean.php';
        break;
    case 'export':
        // Controller-only route (no layout)
        include __DIR__ . '/controllers/export.php';
        break;
    case 'error':
        include __DIR__ . '/views/error.php';
        break;
    case 'logout':
        include __DIR__ . '/controllers/logout.php';
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

<script src='https://cdn.jotfor.ms/agent/embedjs/01995dd1132d76b89c409a5f5c4e50b3aeea/embed.js'></script>
        break;

    case 'member/profile':

        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user') {

            include __DIR__ . '/views/member/profile.php';

        } else {

            header('Location: index.php?page=login');

            exit();

        }

        break;

    case 'cases':

        include __DIR__ . '/views/layouts/header.php';

        include __DIR__ . '/views/cases/index.php';

        include __DIR__ . '/views/layouts/footer.php';

        break;

    case 'cases/create':

        include __DIR__ . '/views/layouts/header.php';

        include __DIR__ . '/views/cases/create.php';

        include __DIR__ . '/views/layouts/footer.php';

        break;

    case 'cases/edit':

        include __DIR__ . '/views/layouts/header.php';

        include __DIR__ . '/views/cases/edit.php';

        include __DIR__ . '/views/layouts/footer.php';

        break;

    case 'cases/view':

        include __DIR__ . '/views/layouts/header.php';

        include __DIR__ . '/views/cases/view.php';

        include __DIR__ . '/views/layouts/footer.php';

        break;

    case 'cases/delete':

        // Controller-only route (no layout)

        include __DIR__ . '/controllers/cases/delete.php';

        break;

    case 'profile':

        include __DIR__ . '/views/layouts/header.php';

        include __DIR__ . '/views/profile/index.php';

        include __DIR__ . '/views/layouts/footer.php';

        break;

    case 'export':

        // Controller-only route (no layout)

        include __DIR__ . '/controllers/export.php';

        break;

    case 'error':

        include __DIR__ . '/views/error.php';

        break;

    case 'logout':

        include __DIR__ . '/controllers/logout.php';

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



<script src='https://cdn.jotfor.ms/agent/embedjs/01995dd1132d76b89c409a5f5c4e50b3aeea/embed.js'></script>
