<?php
// delete_file.php
// Deletes a specified file from the uploads/files directory

require __DIR__ . '/config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
}

// Retrieve and sanitize input
$rawName = $_POST['file'] ?? '';
$fileName = sanitizeFilename($rawName);

if (empty($fileName)) {
    jsonResponse(['success' => false, 'error' => 'No file specified'], 400);
}

$filePath = FINAL_DIR . '/' . $fileName;

// Verify file exists and is within allowed directory
if (!file_exists($filePath) || !is_file($filePath)) {
    jsonResponse(['success' => false, 'error' => 'File not found'], 404);
}

// Attempt deletion
if (!unlink($filePath)) {
    jsonResponse(['success' => false, 'error' => 'Failed to delete file'], 500);
}

// Success response
jsonResponse(['success' => true, 'file' => $fileName]);