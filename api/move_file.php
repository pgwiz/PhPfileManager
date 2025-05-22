<?php
// Moves a file/directory to a target location
require __DIR__ . '/config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
}

// Retrieve and sanitize input
$sourceRaw = $_POST['file'] ?? '';
$targetRaw = $_POST['target_dir'] ?? '';

if (empty($sourceRaw) || empty($targetRaw)) {
    jsonResponse(['success' => false, 'error' => 'Missing parameters'], 400);
}

$source = sanitizeFilename($sourceRaw);
$targetDir = trim($targetRaw, '/');

// Validate source path
$sourcePath = FINAL_DIR . '/' . $source;
if (!file_exists($sourcePath)) {
    jsonResponse(['success' => false, 'error' => 'Source file not found'], 404);
}

// Build target path
$targetPath = FINAL_DIR . ($targetDir ? '/' . $targetDir : '');
$finalTargetPath = $targetPath . '/' . basename($sourcePath);

// Create target directory if needed
if (!is_dir($targetPath)) {
    mkdir($targetPath, 0755, true);
}

// Check if target exists
if (file_exists($finalTargetPath)) {
    jsonResponse(['success' => false, 'error' => 'Target already exists'], 409);
}

// Move file/directory
if (!rename($sourcePath, $finalTargetPath)) {
    jsonResponse(['success' => false, 'error' => 'Move operation failed'], 500);
}

// Success response
jsonResponse([
    'success' => true, 
    'source' => $source,
    'target' => $targetDir
]);