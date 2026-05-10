<?php
// src/Services/FileUploader.php

class FileUploader {
    private $allowedExtensions = ['pdf', 'doc', 'docx'];
    private $maxFileSize = 5242880; // 5MB in bytes

    /**
     * Handles the validation and movement of uploaded files.
     * * @param array $file The $_FILES['input_name'] array
     * @param string $uploadDir The physical path to save the file
     * @return string The new secured filename
     * @throws Exception If validation or upload fails
     */
    public function upload($file, $uploadDir) {
        // Check if file was actually uploaded without PHP errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Upload error code: " . $file['error']);
        }

        // 1. Validate File Extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            throw new Exception("Invalid file type. Only PDF, DOC, and DOCX are allowed.");
        }

        // 2. Validate File Size
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception("File is too large. Maximum size is 5MB.");
        }

        // 3. Ensure Directory Exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // 4. Generate Secure Unique Filename
        // Format: RESUME_65ef1234abcd.pdf
        $secureName = "RESUME_" . bin2hex(random_bytes(8)) . "." . $extension;
        $destination = rtrim($uploadDir, '/') . '/' . $secureName;

        // 5. Move the File
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return $secureName;
        } else {
            throw new Exception("Failed to save the file to the server.");
        }
    }
}