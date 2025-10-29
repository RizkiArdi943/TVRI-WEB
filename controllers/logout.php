<?php
require_once __DIR__ . '/../config/browser_auth.php';

logout();

// Redirect to landing page after logout
header('Location: index.php?page=landing');
exit();
?> 