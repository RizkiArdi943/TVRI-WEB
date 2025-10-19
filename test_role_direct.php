<?php
// Test role display langsung di browser
require_once 'config/database.php';

$db = new Database();
$users = $db->findAll('users');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Role Display</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .user-card { 
            border: 1px solid #ddd; 
            margin: 10px 0; 
            padding: 15px; 
            border-radius: 8px; 
            display: flex; 
            align-items: center; 
            gap: 15px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .user-role { 
            padding: 6px 12px; 
            border-radius: 20px; 
            font-size: 11px; 
            font-weight: 600; 
            text-transform: uppercase; 
            text-align: center; 
            min-width: 100px; 
            display: inline-block;
        }
        .role-admin { 
            background: #fef3c7; 
            color: #d97706; 
            border: 1px solid #f59e0b; 
        }
        .role-user { 
            background: #dbeafe; 
            color: #3b82f6; 
            border: 1px solid #3b82f6; 
        }
        .debug-info {
            background: #f0f8ff;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #2196f3;
        }
    </style>
</head>
<body>
    <h1>Test Role Display - Direct Browser Test</h1>
    
    <div class="debug-info">
        <strong>🔍 Debug Info:</strong><br>
        Total users: <?php echo count($users); ?><br>
        Test time: <?php echo date('Y-m-d H:i:s'); ?>
    </div>
    
    <?php foreach ($users as $user): ?>
    <div class="user-card">
        <div style="font-size: 24px;">👤</div>
        <div>
            <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
            <p>@<?php echo htmlspecialchars($user['username']); ?></p>
            <p><?php echo htmlspecialchars($user['email']); ?></p>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 8px; align-items: flex-end; min-width: 200px;">
            <div class="user-role role-<?php echo $user['role']; ?>">
                <?php 
                // Debug: Show actual role value
                echo "<!-- DEBUG: role = '" . $user['role'] . "' -->";
                
                if ($user['role'] === 'admin') {
                    echo 'Administrator';
                } else {
                    echo 'User';
                }
                ?>
            </div>
            <div style="color: #6b7280; font-size: 12px;">
                <i>🏢</i> <?php echo htmlspecialchars($user['department'] ?? 'Umum'); ?>
            </div>
            <div style="color: #6b7280; font-size: 12px;">
                <i>📅</i> <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <div style="margin-top: 30px; padding: 20px; background: #f0f8ff; border-radius: 8px;">
        <h3>✅ Expected Results:</h3>
        <ul>
            <li><strong>admin</strong> → Yellow "ADMINISTRATOR" badge</li>
            <li><strong>All other users</strong> → Blue "USER" badge</li>
        </ul>
        
        <h3>🔍 If you still see all "ADMINISTRATOR":</h3>
        <ol>
            <li>Check browser console for errors</li>
            <li>View page source to see HTML comments</li>
            <li>Clear browser cache completely</li>
        </ol>
    </div>
</body>
</html>

