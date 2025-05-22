<?php
// assemble.php
// Endpoint to reassemble uploaded chunks into the final file

// Configuration
$uploadsDir = __DIR__ . '/../uploads';
$tempBase = $uploadsDir . '/temp';
$finalBase = $uploadsDir . '/files';

// Ensure directories exist
if (!is_dir($tempBase) || !is_dir($finalBase)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Upload directories missing']);
    exit;
}

// Get parameters (POST)
$sessionId   = $_POST['sessionId']   ?? null;
$fileName    = basename($_POST['fileName'] ?? '');
$totalChunks = isset($_POST['totalChunks']) ? intval($_POST['totalChunks']) : null;

if (!$sessionId || !$fileName || !$totalChunks) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

$tempDir = "$tempBase/$sessionId";
if (!is_dir($tempDir)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Session not found']);
    exit;
}

$finalPath = "$finalBase/$fileName";

// Open output file
$out = fopen($finalPath, 'wb');
if (!$out) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Cannot open final file for writing']);
    exit;
}

// Append each chunk in order
for ($i = 0; $i < $totalChunks; $i++) {
    $chunkFile = "$tempDir/chunk_$i";
    if (!file_exists($chunkFile)) {
        fclose($out);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => "Missing chunk $i"]); 
        exit;
    }
    $in = fopen($chunkFile, 'rb');
    stream_copy_to_stream($in, $out);
    fclose($in);
}
fclose($out);

// Cleanup temp chunks and directory
$files = glob("$tempDir/*");
foreach ($files as $f) {
    @unlink($f);
}
@rmdir($tempDir);

// Success
echo json_encode(['success' => true, 'file' => $fileName]);
