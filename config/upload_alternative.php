<?php
/**
 * Alternative upload handler for Vercel Blob
 * Using base64 encoding approach
 */

class AlternativeUploadHandler {
    private $isVercel;
    
    public function __construct() {
        $this->isVercel = getenv('VERCEL') === '1';
    }
    
    /**
     * Upload file using alternative method
     */
    public function uploadFile($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/webp']) {
        if (!$this->isVercel) {
            return $this->uploadToLocal($file, $allowedTypes);
        } else {
            return $this->uploadToVercelBlobAlternative($file, $allowedTypes);
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
        
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'Ukuran gambar maksimal 5MB'];
        }
        
        $mimeType = null;
        if (function_exists('getimagesize')) {
            $imgInfo = @getimagesize($file['tmp_name']);
            $mimeType = $imgInfo['mime'] ?? null;
        }
        
        if (!$mimeType || !in_array($mimeType, $allowedTypes)) {
            return ['success' => false, 'error' => 'Format gambar tidak didukung'];
        }
        
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
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
     * Alternative upload to Vercel Blob using base64
     */
    private function uploadToVercelBlobAlternative($file, $allowedTypes) {
        $token = getenv('BLOB_READ_WRITE_TOKEN');
        if (!$token) {
            return ['success' => false, 'error' => 'Blob token tidak dikonfigurasi'];
        }
        
        $uploadError = $file['error'];
        if ($uploadError !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => $this->getUploadErrorMessage($uploadError)];
        }
        
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'Ukuran gambar maksimal 5MB'];
        }
        
        $mimeType = null;
        if (function_exists('getimagesize')) {
            $imgInfo = @getimagesize($file['tmp_name']);
            $mimeType = $imgInfo['mime'] ?? null;
        }
        
        if (!$mimeType || !in_array($mimeType, $allowedTypes)) {
            return ['success' => false, 'error' => 'Format gambar tidak didukung'];
        }
        
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $extension = $allowed[$mimeType];
        $filename = uniqid('case_', true) . '.' . $extension;
        
        // Convert file to base64
        $fileContent = file_get_contents($file['tmp_name']);
        $base64Content = base64_encode($fileContent);
        
        // Upload using JSON payload
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.vercel.com/v1/blob');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        
        $payload = json_encode([
            'data' => $base64Content,
            'contentType' => $mimeType,
            'filename' => $filename
        ]);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
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
                error_log('Uploaded to Vercel Blob (Alternative): ' . $data['url']);
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
            return $filename;
        }
    }
}
?>
