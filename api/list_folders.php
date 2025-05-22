<?php
// api/list_folders.php
require __DIR__ . '/config.php';

function getFolders($dir, $basePath = '') {
    $folders = [];
    $items = array_diff(scandir($dir), ['.', '..']);
    
    foreach ($items as $item) {
        $path = FINAL_DIR . '/' . $item;
        if (is_dir($path)) {
            $relativePath = $basePath ? "$basePath/$item" : $item;
            $folders[] = [
                'name' => $item,
                'path' => $relativePath
            ];
            $folders = array_merge($folders, getFolders($path, $relativePath));
        }
    }
    
    return $folders;
}

try {
    $folders = getFolders(FINAL_DIR);
    jsonResponse([
        'success' => true,
        'folders' => $folders
    ]);
} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'error' => 'Failed to list folders',
        'message' => $e->getMessage()
    ], 500);
}