<?php
// upload_chunk.php
// Handles 9MB-chunked uploads, storing each chunk in a temp directory per session

// Configuration: paths relative to this script
$uploadsDir = __DIR__ . '/../uploads';
$tempBase   = $uploadsDir . '/temp';

// Ensure base directories exist
if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
if (!is_dir($tempBase)) mkdir($tempBase, 0755, true);

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['chunkData'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$sessionId   = $_POST['sessionId']   ?? null;
$fileName    = basename($_POST['fileName'] ?? '');
$totalChunks = isset($_POST['totalChunks']) ? intval($_POST['totalChunks']) : null;
$chunkIndex  = isset($_POST['chunkIndex'])  ? intval($_POST['chunkIndex'])  : null;

if (!$sessionId || !$fileName || $totalChunks === null || $chunkIndex === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

// Create session temp directory
$sessionDir = "$tempBase/$sessionId";
if (!is_dir($sessionDir)) {
    mkdir($sessionDir, 0755, true);
}

// Move uploaded chunk to session folder
$chunkTmp  = $_FILES['chunkData']['tmp_name'];
$chunkPath = "$sessionDir/chunk_{$chunkIndex}";
if (!move_uploaded_file($chunkTmp, $chunkPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save chunk']);
    exit;
}

// Return success (frontend can trigger assemble.php when all chunks are uploaded)
header('Content-Type: application/json');
echo json_encode(['success' => true, 'chunkIndex' => $chunkIndex]);
