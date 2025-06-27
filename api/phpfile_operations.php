<?php
/**
 * phpfile_operations.php
 *
 * Unified and secure endpoint for all file and folder operations.
 * This version includes a robust sanitization function that correctly handles
 * filenames with special characters, spaces, and non-breaking spaces while
 * preventing security vulnerabilities like directory traversal.
 *
 * The key improvement is a rewritten `sanitizePath` function that does not
 * rely on `realpath()` for user input, thus avoiding issues with special
 * characters and non-existent paths (required for rename/move operations).
 */

// Ensure the config file is included. It defines FINAL_DIR and jsonResponse().
require __DIR__ . '/config.php';

// The main router for all API actions. It reads the 'action' parameter
// from either a GET or POST request and calls the appropriate handler.
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'list_files':
        handleListFiles();
        break;
    case 'list_folders':
        handleListFolders();
        break;
    case 'download':
        handleDownload();
        break;
    case 'delete':
        handleDelete();
        break;
    case 'rename':
        handleRename();
        break;
    case 'move':
        handleMove();
        break;
    case 'create_item':
        handleCreateItem();
        break;
    case 'get_content':
        handleGetContent();
        break;
    case 'save_content':
        handleSaveContent();
        break;
    default:
        // If the action is unknown, return an error.
        jsonResponse(['success' => false, 'error' => 'Invalid action specified'], 400);
}

/**
 * Lists all files and directories within a specified path.
 * The path is relative to the FINAL_DIR.
 */
function handleListFiles() {
    $relativePath = isset($_GET['path']) ? $_GET['path'] : '';
    $safeRelativePath = sanitizePath($relativePath);

    // After sanitization, if the path is invalid (e.g., points outside FINAL_DIR), it will be null.
    if ($safeRelativePath === null && !empty($relativePath)) {
        jsonResponse(['success' => false, 'error' => 'Invalid or unsafe directory path'], 400);
        return;
    }

    $targetDir = FINAL_DIR . '/' . $safeRelativePath;

    if (!is_dir($targetDir)) {
        jsonResponse(['success' => false, 'error' => 'Directory not found'], 404);
        return;
    }

    $items = array_diff(scandir($targetDir), ['..', '.']);
    $result = [];

    foreach ($items as $item) {
        $pathToFile = $targetDir . '/' . $item;
        $result[] = [
            'name' => $item, // Raw name for display
            'type' => is_dir($pathToFile) ? 'directory' : 'file',
            'size' => is_file($pathToFile) ? filesize($pathToFile) : null,
            'modified' => date('c', filemtime($pathToFile)),
            // The 'path' sent to the frontend is the clean, relative path.
            'path' => !empty($safeRelativePath) ? $safeRelativePath . '/' . $item : $item
        ];
    }
    jsonResponse($result);
}

/**
 * Recursively lists all sub-directories for the "Move" modal.
 */
function handleListFolders() {
    // This recursive helper function does the work.
    function getFolders($dir, $basePath = '') {
        $folders = [];
        // Use try-catch for filesystem errors
        try {
            $items = array_diff(scandir($dir), ['.', '..']);
            foreach ($items as $item) {
                $path = $dir . '/' . $item;
                if (is_dir($path)) {
                    $relativePath = $basePath ? "$basePath/$item" : $item;
                    $folders[] = ['name' => $item, 'path' => $relativePath];
                    $folders = array_merge($folders, getFolders($path, $relativePath));
                }
            }
        } catch (Exception $e) {
            // Log error, but don't stop the whole process if a subdir is unreadable
            error_log("Could not read directory {$dir}: " . $e->getMessage());
        }
        return $folders;
    }

    try {
        $folders = getFolders(FINAL_DIR);
        // Add the root directory as the first option
        array_unshift($folders, ['name' => '(Root Directory)', 'path' => '']);
        jsonResponse(['success' => true, 'folders' => $folders]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to list folders'], 500);
    }
}

/**
 * Handles secure file downloads.
 */
function handleDownload() {
    $relativePath = $_GET['file'] ?? '';
    $safeRelativePath = sanitizePath($relativePath);

    if ($safeRelativePath === null) {
        http_response_code(400);
        echo 'Invalid or unsafe file path specified.';
        exit;
    }
    
    $filePath = FINAL_DIR . '/' . $safeRelativePath;

    if (!file_exists($filePath) || !is_readable($filePath) || is_dir($filePath)) {
        http_response_code(404);
        echo 'File not found or not readable.';
        exit;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filePath);
    finfo_close($finfo);

    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    flush();
    readfile($filePath);
    exit;
}

/**
 * Handles deletion of a file or an entire directory recursively.
 */
function handleDelete() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
    }
    $relativePath = $_POST['path'] ?? '';
    $safeRelativePath = sanitizePath($relativePath);

    if ($safeRelativePath === null || empty($safeRelativePath)) { // Do not allow deleting root
        jsonResponse(['success' => false, 'error' => 'Invalid or unsafe path specified'], 400);
    }
    
    $fullPath = FINAL_DIR . '/' . $safeRelativePath;

    if (!file_exists($fullPath)) {
        jsonResponse(['success' => false, 'error' => 'File or directory not found'], 404);
    }

    // Recursive delete helper function.
    function deleteRecursively($dir) {
        if (!is_readable($dir)) return false;
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            if (!deleteRecursively($dir . DIRECTORY_SEPARATOR . $item)) return false;
        }
        return rmdir($dir);
    }

    if (!deleteRecursively($fullPath)) {
        jsonResponse(['success' => false, 'error' => 'Delete failed. Check server file permissions.'], 500);
    }
    jsonResponse(['success' => true]);
}

/**
 * Handles renaming a file or folder.
 */
function handleRename() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
    }
    $oldRelativePath = $_POST['old_path'] ?? '';
    $newName = $_POST['new_name'] ?? '';

    // Sanitize the new name to remove slashes and other illegal characters.
    $safeNewName = basename(str_replace(['/', '\\'], '', $newName));

    if (empty($oldRelativePath) || empty($safeNewName) || $safeNewName === '.' || $safeNewName === '..') {
        jsonResponse(['success' => false, 'error' => 'Missing or invalid parameters'], 400);
    }

    $safeOldPath = sanitizePath($oldRelativePath);
    if ($safeOldPath === null) {
        jsonResponse(['success' => false, 'error' => 'Invalid source path'], 400);
    }

    $oldFullPath = FINAL_DIR . '/' . $safeOldPath;
    $newFullPath = dirname($oldFullPath) . '/' . $safeNewName;

    if (!file_exists($oldFullPath)) {
        jsonResponse(['success' => false, 'error' => 'Source file not found'], 404);
    }
    if (file_exists($newFullPath)) {
        jsonResponse(['success' => false, 'error' => 'Target name already exists'], 409);
    }
    if (!rename($oldFullPath, $newFullPath)) {
        jsonResponse(['success' => false, 'error' => 'Failed to rename. Check permissions.'], 500);
    }
    jsonResponse(['success' => true]);
}

/**
 * Handles moving a file or folder to a new directory.
 */
function handleMove() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
    }
    $sourceRelativePath = $_POST['source_path'] ?? '';
    $targetDirRelativePath = $_POST['target_dir'] ?? '';

    $safeSourcePath = sanitizePath($sourceRelativePath);
    $safeTargetDirPath = sanitizePath($targetDirRelativePath); // Target dir must also be sanitized.

    if ($safeSourcePath === null || $safeTargetDirPath === null) {
        jsonResponse(['success' => false, 'error' => 'Invalid source or target path'], 400);
    }

    $sourceFullPath = FINAL_DIR . '/' . $safeSourcePath;
    $targetDirFullPath = FINAL_DIR . '/' . $safeTargetDirPath;
    $finalTargetPath = $targetDirFullPath . '/' . basename($sourceFullPath);

    if (!file_exists($sourceFullPath)) {
        jsonResponse(['success' => false, 'error' => 'Source not found'], 404);
    }
    if (!is_dir($targetDirFullPath)) {
        jsonResponse(['success' => false, 'error' => 'Target directory does not exist'], 404);
    }
    if (strpos($finalTargetPath, $sourceFullPath) === 0) {
        jsonResponse(['success' => false, 'error' => 'Cannot move a directory into itself.'], 400);
    }
    if (file_exists($finalTargetPath)) {
        jsonResponse(['success' => false, 'error' => 'Target already exists'], 409);
    }
    if (!rename($sourceFullPath, $finalTargetPath)) {
        jsonResponse(['success' => false, 'error' => 'Move operation failed. Check permissions.'], 500);
    }
    jsonResponse(['success' => true]);
}

/**
 * Handles creating a new folder or an empty file.
 */
function handleCreateItem() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
    }
    $currentPath = $_POST['current_path'] ?? '';
    $itemName = $_POST['item_name'] ?? '';
    $itemType = $_POST['item_type'] ?? '';

    $safeNewItemName = basename(str_replace(['/', '\\'], '', $itemName));
    if (empty($safeNewItemName) || !in_array($itemType, ['file', 'folder'])) {
        jsonResponse(['success' => false, 'error' => 'Invalid name or type specified'], 400);
    }

    $safeCurrentPath = sanitizePath($currentPath);
    if ($safeCurrentPath === null) {
        jsonResponse(['success' => false, 'error' => 'Invalid base path'], 400);
    }

    $newItemPath = FINAL_DIR . '/' . $safeCurrentPath . '/' . $safeNewItemName;
    if (file_exists($newItemPath)) {
        jsonResponse(['success' => false, 'error' => 'An item with that name already exists'], 409);
    }

    $success = false;
    if ($itemType === 'folder') {
        $success = mkdir($newItemPath, 0755, true);
    } elseif ($itemType === 'file') {
        $success = touch($newItemPath);
    }

    if (!$success) {
        jsonResponse(['success' => false, 'error' => "Failed to create {$itemType}. Check permissions."], 500);
    }
    jsonResponse(['success' => true]);
}


/**
 * Retrieves the text content of a file for editing.
 */
function handleGetContent() {
    $relativePath = $_GET['path'] ?? '';
    $safeRelativePath = sanitizePath($relativePath);
    if ($safeRelativePath === null) {
        jsonResponse(['success' => false, 'error' => 'Invalid file path'], 400);
    }
    $fullPath = FINAL_DIR . '/' . $safeRelativePath;
    if (!file_exists($fullPath) || !is_file($fullPath)) {
        jsonResponse(['success' => false, 'error' => 'File not found'], 404);
    }
    $content = file_get_contents($fullPath);
    if ($content === false) {
        jsonResponse(['success' => false, 'error' => 'Could not read file content'], 500);
    }
    jsonResponse(['success' => true, 'content' => $content]);
}

/**
 * Saves updated text content to a file.
 */
function handleSaveContent() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
    }
    $relativePath = $_POST['path'] ?? '';
    $content = $_POST['content'] ?? '';

    $safeRelativePath = sanitizePath($relativePath);
    if ($safeRelativePath === null) {
        jsonResponse(['success' => false, 'error' => 'Invalid file path'], 400);
    }

    $fullPath = FINAL_DIR . '/' . $safeRelativePath;
    if (!file_exists($fullPath) || !is_file($fullPath)) {
        jsonResponse(['success' => false, 'error' => 'File not found'], 404);
    }

    if (file_put_contents($fullPath, $content) === false) {
        jsonResponse(['success' => false, 'error' => 'Failed to save file. Check permissions.'], 500);
    }
    jsonResponse(['success' => true]);
}


/**
 * =================================================================================
 * SECURITY HELPER FUNCTIONS
 * =================================================================================
 */

function get_absolute_path($path) {
    $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
    $absolutes = [];
    foreach ($parts as $part) {
        if ('.' == $part) continue;
        if ('..' == $part) {
            array_pop($absolutes);
        } else {
            $absolutes[] = $part;
        }
    }
    return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : DIRECTORY_SEPARATOR) . implode(DIRECTORY_SEPARATOR, $absolutes);
}

function sanitizePath($path) {
    $base = realpath(FINAL_DIR);
    if ($base === false) {
        error_log("Security Alert: The base directory FINAL_DIR does not exist.");
        return null;
    }
    $decodedPath = rawurldecode($path);
    $intendedFullPath = $base . DIRECTORY_SEPARATOR . $decodedPath;
    $resolvedFullPath = get_absolute_path($intendedFullPath);
    if (strpos($resolvedFullPath, $base) !== 0) {
        return null;
    }
    $relativePath = substr($resolvedFullPath, strlen($base));
    return trim($relativePath, DIRECTORY_SEPARATOR);
}

?>
