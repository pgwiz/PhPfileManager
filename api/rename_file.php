<?php
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
}

$oldNameRaw = $_POST['old_name'] ?? '';
$newNameRaw = $_POST['new_name'] ?? '';

if (empty($oldNameRaw) || empty($newNameRaw)) {
    jsonResponse(['success' => false, 'error' => 'Missing parameters'], 400);
}

$oldName = sanitizeFilename($oldNameRaw);
$newName = sanitizeFilename($newNameRaw);

$oldPath = FINAL_DIR . '/' . $oldName;
$newPath = FINAL_DIR . '/' . $newName;

if (!file_exists($oldPath)) {
    jsonResponse(['success' => false, 'error' => 'File not found'], 404);
}

if (file_exists($newPath)) {
    jsonResponse(['success' => false, 'error' => 'Target name already exists'], 409);
}

if (!rename($oldPath, $newPath)) {
    jsonResponse(['success' => false, 'error' => 'Failed to rename file'], 500);
}

jsonResponse(['success' => true, 'old_name' => $oldName, 'new_name' => $newName]);