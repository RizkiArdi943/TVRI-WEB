<?php
/**
 * Simple upload handler menggunakan direct PUT ke Vercel Blob
 * Berdasarkan pendekatan yang lebih sederhana dan reliable
 */

class SimpleVercelBlobUploadHandler {
    private $isVercel;
    private $token;
    private $storeId;
    
    public function __construct() {
        $this->isVercel = getenv('VERCEL') === '1';
        $this->token = getenv('BLOB_READ_WRITE_TOKEN');
        $this->storeId = 'pxhnbjjgr6icu3vk'; // Store ID yang benar dari upload manual
    }
    
    /**
     * Upload file menggunakan pendekatan simple
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
        
        // Debug logging
        error_log('Upload attempt: ' . $file['tmp_name'] . ' -> ' . $targetPath);
        error_log('File exists: ' . (file_exists($file['tmp_name']) ? 'yes' : 'no'));
        error_log('Upload dir writable: ' . (is_writable($uploadDir) ? 'yes' : 'no'));
        
        // Use copy for existing files, move_uploaded_file for actual uploads
        if (is_uploaded_file($file['tmp_name'])) {
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                error_log('File uploaded successfully: ' . $filename);
                return ['success' => true, 'path' => $filename];
            } else {
                error_log('move_uploaded_file failed');
                return ['success' => false, 'error' => 'Gagal mengupload gambar.'];
            }
        } else {
            // For existing files (like in testing), use copy
            if (copy($file['tmp_name'], $targetPath)) {
                error_log('File copied successfully: ' . $filename);
                return ['success' => true, 'path' => $filename];
            } else {
                error_log('copy failed');
                return ['success' => false, 'error' => 'Gagal mengupload gambar.'];
            }
        }
    }
    
    /**
     * Upload to Vercel Blob menggunakan direct PUT
     */
    private function uploadToVercelBlob($file, $allowedTypes) {
        if (!$this->token) {
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
        
        // Baca isi file
        $fileContent = file_get_contents($file['tmp_name']);
        if ($fileContent === false) {
            return ['success' => false, 'error' => 'Gagal membaca file'];
        }
        
        // Tentukan content type
        if (!$mimeType) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if (!$mimeType) {
                $mimeType = 'application/octet-stream';
            }
        }
        
        // Nama unik supaya tidak tabrakan
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $extension = $allowed[$mimeType];
        $random = bin2hex(random_bytes(6));
        $filename = "case_{$random}.{$extension}";
        $pathInBlob = "uploads/{$filename}";
        
        // URL upload - format yang benar
        $url = "https://blob.vercel-storage.com/" . $pathInBlob;
        
        // CURL PUT
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->token,
            "Content-Type: " . $mimeType,
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        
        if ($err) {
            error_log('CURL Error: ' . $err);
            return ['success' => false, 'error' => 'Gagal mengupload gambar: ' . $err];
        }
        
        if ($httpcode >= 200 && $httpcode < 300) {
            // Parse response untuk mendapatkan URL yang benar
            $responseData = json_decode($response, true);
            if (isset($responseData['url'])) {
                $publicUrl = $responseData['url'];
            } else {
                $publicUrl = "https://" . $this->storeId . ".public.blob.vercel-storage.com/" . $pathInBlob;
            }
            error_log('Uploaded to Vercel Blob: ' . $publicUrl);
            return ['success' => true, 'path' => $publicUrl];
        } else {
            error_log('Vercel Blob upload failed. HTTP Code: ' . $httpcode . ', Response: ' . $response);
            return ['success' => false, 'error' => 'Gagal upload ke Vercel Blob (HTTP ' . $httpcode . '): ' . $response];
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
            // Check if file exists locally
            $localPath = __DIR__ . '/../uploads/' . $filename;
            if (file_exists($localPath)) {
                return '/uploads/' . $filename;
            } else {
                // File doesn't exist, return placeholder
                error_log('Image file not found: ' . $filename);
                return null;
            }
        } else {
            // For Vercel Blob, filename is already the full URL
            return $filename;
        }
    }
}
?>
