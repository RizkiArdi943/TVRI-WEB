# Panduan Setup Vercel Blob untuk Upload Gambar

## Masalah Upload di Vercel

Vercel menggunakan filesystem read-only, sehingga tidak bisa menulis file ke folder `uploads`. Untuk mengatasi ini, kita perlu menggunakan layanan penyimpanan eksternal.

## Solusi: Vercel Blob

### 1. Install Vercel Blob

```bash
npm install @vercel/blob
```

### 2. Setup Environment Variables

Di Vercel Dashboard, tambahkan environment variable:
```
BLOB_READ_WRITE_TOKEN=your_blob_token_here
```

### 3. Update Upload Handler

Ganti method `uploadToVercelBlob` di `config/upload.php`:

```php
private function uploadToVercelBlob($file, $allowedTypes) {
    $token = getenv('BLOB_READ_WRITE_TOKEN');
    if (!$token) {
        return ['success' => false, 'error' => 'Blob token tidak dikonfigurasi'];
    }
    
    // Validate file
    $uploadError = $file['error'];
    if ($uploadError !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => $this->getUploadErrorMessage($uploadError)];
    }
    
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'Ukuran gambar maksimal 5MB'];
    }
    
    // Validate file type
    $mimeType = null;
    if (function_exists('getimagesize')) {
        $imgInfo = @getimagesize($file['tmp_name']);
        $mimeType = $imgInfo['mime'] ?? null;
    }
    
    if (!$mimeType || !in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'error' => 'Format gambar tidak didukung'];
    }
    
    // Generate unique filename
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $extension = $allowed[$mimeType];
    $filename = uniqid('case_', true) . '.' . $extension;
    
    // Upload to Vercel Blob
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.vercel.com/v1/blob');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/octet-stream'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($file['tmp_name']));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        return ['success' => true, 'path' => $data['url']];
    } else {
        return ['success' => false, 'error' => 'Gagal upload ke Vercel Blob'];
    }
}
```

### 4. Update getFileUrl Method

```php
public function getFileUrl($filename) {
    if (!$this->isVercel) {
        return '/uploads/' . $filename;
    } else {
        // For Vercel Blob, filename is already the full URL
        return $filename;
    }
}
```

## Alternatif: Cloudinary

Jika Vercel Blob tidak cocok, Anda bisa menggunakan Cloudinary:

### 1. Daftar di Cloudinary
- Kunjungi [cloudinary.com](https://cloudinary.com)
- Buat akun gratis
- Dapatkan Cloud Name, API Key, dan API Secret

### 2. Environment Variables
```
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
```

### 3. Install Cloudinary PHP SDK
```bash
composer require cloudinary/cloudinary_php
```

## Status Saat Ini

Saat ini aplikasi sudah dikonfigurasi untuk:
- ✅ Development: Upload ke folder `uploads/` lokal
- ❌ Production: Menampilkan pesan error (perlu konfigurasi Vercel Blob)

Untuk sementara, upload gambar akan gagal di production sampai Vercel Blob dikonfigurasi.
