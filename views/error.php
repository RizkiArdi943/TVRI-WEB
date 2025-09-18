<?php
$error_code = $_GET['code'] ?? '404';
$error_messages = [
    '404' => [
        'title' => 'Halaman Tidak Ditemukan',
        'message' => 'Maaf, halaman yang Anda cari tidak ditemukan.',
        'icon' => 'fa-search'
    ],
    '403' => [
        'title' => 'Akses Ditolak',
        'message' => 'Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.',
        'icon' => 'fa-ban'
    ],
    '500' => [
        'title' => 'Kesalahan Server',
        'message' => 'Maaf, terjadi kesalahan pada server. Silakan coba lagi nanti.',
        'icon' => 'fa-exclamation-triangle'
    ]
];

$error = $error_messages[$error_code] ?? $error_messages['404'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?php echo $error_code; ?> - TVRI Kalimantan Tengah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="error-page">
    <div class="error-container">
        <div class="error-card">
            <div class="error-icon">
                <i class="fas <?php echo $error['icon']; ?>"></i>
            </div>
            <h1>Error <?php echo $error_code; ?></h1>
            <h2><?php echo $error['title']; ?></h2>
            <p><?php echo $error['message']; ?></p>
            
            <div class="error-actions">
                <a href="index.php?page=dashboard" class="btn btn-primary">
                    <i class="fas fa-home"></i>
                    Kembali ke Beranda
                </a>
                <a href="javascript:history.back()" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
            </div>
            
            <div class="error-help">
                <p>Jika masalah berlanjut, silakan hubungi administrator.</p>
                <p>Email: admin@tvri.id</p>
            </div>
        </div>
    </div>
</body>
</html>

<style>
.error-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
    padding: 20px;
}

.error-container {
    width: 100%;
    max-width: 500px;
}

.error-card {
    background: white;
    border-radius: 20px;
    padding: 40px 30px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.error-icon {
    font-size: 64px;
    color: #EF4444;
    margin-bottom: 24px;
}

.error-card h1 {
    font-size: 32px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 8px;
}

.error-card h2 {
    font-size: 20px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 16px;
}

.error-card p {
    color: #64748b;
    font-size: 16px;
    margin-bottom: 32px;
    line-height: 1.6;
}

.error-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin-bottom: 32px;
    flex-wrap: wrap;
}

.error-help {
    padding-top: 24px;
    border-top: 1px solid #e2e8f0;
    color: #64748b;
    font-size: 14px;
}

.error-help p {
    margin-bottom: 4px;
}

@media (max-width: 480px) {
    .error-card {
        padding: 30px 20px;
    }
    
    .error-icon {
        font-size: 48px;
    }
    
    .error-card h1 {
        font-size: 24px;
    }
    
    .error-card h2 {
        font-size: 18px;
    }
    
    .error-actions {
        flex-direction: column;
    }
}
</style> 