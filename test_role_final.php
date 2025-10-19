<?php
// Final test untuk role display
require_once 'config/database.php';

$db = new Database();
$users = $db->findAll('users');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Final Role Test</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
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
        .success-info {
            background: #e8f5e8;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #4caf50;
        }
    </style>
</head>
<body>
    <h1>🎯 Final Role Display Test</h1>
    
    <div class="debug-info">
        <strong>🔍 Debug Info:</strong><br>
        Total users: <?php echo count($users); ?><br>
        Test time: <?php echo date('Y-m-d H:i:s'); ?><br>
        Timestamp: <?php echo time(); ?>
    </div>
    
    <div class="success-info">
        <strong>✅ Expected Results:</strong><br>
        - <strong>admin</strong> → Yellow "ADMINISTRATOR" badge<br>
        - <strong>All other users</strong> → Blue "USER" badge<br>
        - <strong>Layout</strong> → All labels aligned to the right
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
            <div class="user-role role-<?php echo $user['role']; ?>" data-role="<?php echo $user['role']; ?>">
                <?php 
                // Debug: Show actual role value
                echo "<!-- DEBUG: role = '" . $user['role'] . "' (ID: " . $user['id'] . ") -->";
                
                // Force role display with explicit logic
                $roleDisplay = '';
                if ($user['role'] === 'admin') {
                    $roleDisplay = 'Administrator';
                } else {
                    $roleDisplay = 'User';
                }
                echo $roleDisplay;
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
    
    <div style="margin-top: 30px; padding: 20px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
        <h3>🚨 Jika Masih Melihat Semua "ADMINISTRATOR":</h3>
        <ol>
            <li><strong>Hard Refresh:</strong> Tekan <code>Ctrl + F5</code> (Windows) atau <code>Cmd + Shift + R</code> (Mac)</li>
            <li><strong>Clear Cache:</strong> Buka Developer Tools (F12) → Network tab → Check "Disable cache"</li>
            <li><strong>Private Mode:</strong> Buka halaman di Incognito/Private window</li>
            <li><strong>Check Console:</strong> Buka Developer Tools (F12) → Console untuk melihat debug info</li>
        </ol>
    </div>
    
    <script>
        console.log('🎯 Final Role Test loaded at:', new Date().toISOString());
        console.log('🎯 Timestamp:', <?php echo time(); ?>);
        
        // Check role elements
        const roleElements = document.querySelectorAll('.user-role');
        roleElements.forEach((el, index) => {
            const role = el.getAttribute('data-role');
            const text = el.textContent.trim();
            console.log(`User ${index + 1}: role="${role}", display="${text}"`);
        });
    </script>
</body>
</html>

