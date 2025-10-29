<?php
/**
 * Upload handler for Vercel deployment
 * Supports both local development and Vercel Blob for production
 */

class UploadHandler {
    private $isVercel;
    
    public function __construct() {
        $this->isVercel = getenv('VERCEL') === '1';
    }
    
    /**
     * Upload file to appropriate storage
     */
    public function uploadFile($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/webp']) {
        if (!$this->isVercel) {
            return $this->uploadToLocal($file, $allowedTypes);
        } else {
            return $this->uploadToVercelBlob($file, $allowedTypes);
        }
    }
    
    /**
     * Upload to local filesystem (development)
     */
    private function uploadToLocal($file, $allowedTypes) {
        $uploadError = $file['error'];
        
        if ($uploadError !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => $this->getUploadErrorMessage($uploadError)];
        }
        
        // Validate file size (5MB max)
        $maxSize = 5 * 1024 * 1024;
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
            return ['success' => false, 'error' => 'Format gambar tidak didukung. Gunakan JPG, PNG, atau WebP.'];
        }
        
        // Create uploads directory if not exists
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $extension = $allowed[$mimeType];
        $filename = uniqid('case_', true) . '.' . $extension;
        $targetPath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => true, 'path' => $filename];
        } else {
            return ['success' => false, 'error' => 'Gagal mengupload gambar.'];
        }
    }
    
    /**
     * Upload to Vercel Blob (production)
     */
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
            return ['success' => false, 'error' => 'Format gambar tidak didukung. Gunakan JPG, PNG, atau WebP.'];
        }
        
        // Generate unique filename
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $extension = $allowed[$mimeType];
        $filename = uniqid('case_', true) . '.' . $extension;
        
        // Upload to Vercel Blob using multipart form data
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.vercel.com/v1/blob');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);
        
        // Create multipart form data
        $postData = [
            'file' => new CURLFile($file['tmp_name'], $mimeType, $filename)
        ];
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            error_log('CURL Error: ' . $curlError);
            return ['success' => false, 'error' => 'Gagal mengupload gambar: ' . $curlError];
        }
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if ($data && isset($data['url'])) {
                error_log('Uploaded to Vercel Blob: ' . $data['url']);
                return ['success' => true, 'path' => $data['url']];
            } else {
                error_log('Invalid response from Vercel Blob: ' . $response);
                return ['success' => false, 'error' => 'Response tidak valid dari Vercel Blob'];
            }
        } else {
            error_log('Vercel Blob upload failed. HTTP Code: ' . $httpCode . ', Response: ' . $response);
            return ['success' => false, 'error' => 'Gagal upload ke Vercel Blob (HTTP ' . $httpCode . ')'];
        }
    }
    
    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'Ukuran file melebihi batas upload server';
            case UPLOAD_ERR_FORM_SIZE:
                return 'Ukuran file melebihi batas form';
            case UPLOAD_ERR_PARTIAL:
                return 'File hanya terupload sebagian';
            case UPLOAD_ERR_NO_FILE:
                return 'Tidak ada file yang diupload';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Folder temporary tidak ditemukan';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Gagal menulis file ke disk';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload diblokir oleh ekstensi PHP';
            default:
                return 'Terjadi kesalahan tidak dikenal';
        }
    }
    
    /**
     * Get file URL for display
     */
    public function getFileUrl($filename) {
        if (!$this->isVercel) {
            return '/uploads/' . $filename;
        } else {
            // For Vercel Blob, filename is already the full URL
            return $filename;
        }
    }
}
?>
