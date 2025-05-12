<?php

class FileUploader {
    private $uploadDir;
    private $allowedTypes;
    private $maxFileSize;
    private $db;
    
    public function __construct($db, $uploadDir = 'uploads/', $maxFileSize = 5242880) { // 5MB default
        $this->db = $db;
        $this->uploadDir = $uploadDir;
        $this->maxFileSize = $maxFileSize;
        $this->allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        // Create upload directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
    }
    
    public function upload($file, $userId, $type = 'general') {
        try {
            // Validate file
            $this->validateFile($file);
            
            // Generate unique filename
            $filename = $this->generateUniqueFilename($file['name']);
            $filepath = $this->uploadDir . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('Failed to move uploaded file');
            }
            
            // Save file information to database
            $query = "INSERT INTO files (user_id, filename, original_name, file_type, file_size, upload_type, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, NOW())";
                     
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $userId,
                $filename,
                $file['name'],
                $file['type'],
                $file['size'],
                $type
            ]);
            
            return [
                'success' => true,
                'filename' => $filename,
                'original_name' => $file['name'],
                'file_type' => $file['type'],
                'file_size' => $file['size']
            ];
            
        } catch (Exception $e) {
            // Log error
            error_log("File upload error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function validateFile($file) {
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new Exception('No file was uploaded');
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('File size exceeds maximum limit');
        }
        
        // Check file type
        if (!in_array($file['type'], $this->allowedTypes)) {
            throw new Exception('File type not allowed');
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $this->getUploadErrorMessage($file['error']));
        }
    }
    
    private function generateUniqueFilename($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return uniqid() . '_' . time() . '.' . $extension;
    }
    
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload';
            default:
                return 'Unknown upload error';
        }
    }
    
    public function deleteFile($filename) {
        try {
            $filepath = $this->uploadDir . $filename;
            
            // Delete from database
            $query = "DELETE FROM files WHERE filename = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$filename]);
            
            // Delete physical file
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("File deletion error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getFileInfo($filename) {
        $query = "SELECT * FROM files WHERE filename = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$filename]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getUserFiles($userId) {
        $query = "SELECT * FROM files WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 