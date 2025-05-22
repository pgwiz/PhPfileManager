<?php
// download.php
// Streams a specified file from the uploads/files directory to the client

// Configuration: adjust path as needed
$finalBase = __DIR__ . '/../uploads/files';

// Verify 'file' parameter
if (!isset($_GET['file'])) {
    http_response_code(400);
    echo 'No file specified.';
    exit;
}

$rawName = $_GET['file'];
$fileName = basename($rawName);
$filePath = $finalBase . '/' . $fileName;

// Check existence and readability
if (!file_exists($filePath) || !is_readable($filePath)) {
    http_response_code(404);
    echo 'File not found.';
    exit;
}

// Determine MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $filePath);
finfo_close($finfo);

// Set headers for download
header('Content-Description: File Transfer');
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

// Clear output buffer
flush();

// Stream the file
readfile($filePath);
exit;
