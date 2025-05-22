<?php
// list_files.php
// Returns a JSON list of files (and directories) in the uploads directory

// Configuration: adjust path if needed
$uploadsDir = __DIR__ . '/../uploads';

// Get path parameter if exists
$path = isset($_GET['path']) ? trim($_GET['path'], '/') : '';

// Build target directory path
$targetDir = $uploadsDir . ($path ? '/' . $path : '');

// Ensure the directory exists
if (!is_dir($targetDir)) {
    http_response_code(404);
    echo json_encode(['error' => 'Directory not found']);
    exit;
}

// Scan directory (excluding . and ..)
$items = array_diff(scandir($targetDir), ['..', '.']);
$result = [];

foreach ($items as $item) {
    $pathToFile = $targetDir . '/' . $item;
    $result[] = [
        'name' => $item,
        'type' => is_dir($pathToFile) ? 'directory' : 'file',
        'size' => is_file($pathToFile) ? filesize($pathToFile) : null,
        'modified' => date('c', filemtime($pathToFile)),
        'path' => ($path ? "$path/" : "") . $item // Full path for navigation
    ];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);