<?php
// index.php - Advanced File Manager

// Configuration
$baseDir = __DIR__ . '/uploads/files';
if (!is_dir($baseDir)) mkdir($baseDir, 0755, true);

// Helper to list files
function listFiles($dir) {
  $items = array_diff(scandir($dir), ['.', '..']);
  $results = [];
  foreach ($items as $item) {
    $path = $dir . '/' . $item;
    $results[] = [
      'name' => $item,
      'type' => is_dir($path) ? 'dir' : 'file',
      'size' => is_file($path) ? filesize($path) : 0,
    ];
  }
  return $results;
}

$files = listFiles($baseDir);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP File Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="public/theme.css" rel="stylesheet">
    <link href="public/responsive.css" rel="stylesheet">
</head>
<body class="theme-modern">
    <div class="container">
        <div class="header">
            <h1>📁 Advanced PHP File Manager</h1>
            <p>Upload, manage, and download your files securely</p>
        </div>

        <div class="main-content">
            <!-- Drop Zone -->
            <div id="dropZone" class="mb-6 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-blue-500 transition-colors">
              <input type="file" id="fileInput" class="hidden" multiple>
              <div class="text-gray-500">
                <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                <p class="mt-2">Drag & drop files here, or <span class="text-blue-500">browse</span></p>
              </div>
            </div>
            <div id="uploadStatus" class="mb-6"></div>

            <!-- Controls Toolbar -->
            <div class="controls-toolbar">
                <div class="navigation-controls">
                    <button id="backBtn" class="control-btn" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        <span>Back</span>
                    </button>
                    <button id="refreshBtn" class="control-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                        <span>Refresh</span>
                    </button>
                    <button id="createFolderBtn" class="control-btn control-btn-special">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg>
                        <span>Create Folder</span>
                    </button>
                    <button id="createFileBtn" class="control-btn control-btn-special-alt">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
                        <span>Create File</span>
                    </button>
                </div>
                <div class="path-display">
                    Current Path: <span id="currentPath">/</span>
                </div>
                <div class="view-controls">
                    <button id="view-list" class="view-btn" title="List View">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                    </button>
                    <button id="view-grid" class="view-btn" title="Grid View">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    </button>
                    <button id="view-compact" class="view-btn" title="Compact View">
                         <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                </div>
            </div>

            <!-- File Container -->
            <div id="fileContainer" class="mt-6">
                <!-- Files will be rendered here by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div id="renameModal" class="modal-backdrop">
        <div class="modal-content">
            <h2 class="modal-header">Rename Item</h2>
            <form id="renameForm">
                <input type="hidden" id="oldName">
                <input type="text" id="newName" class="modal-input" placeholder="Enter new name">
                <div class="modal-actions">
                    <button type="button" id="cren" class="modal-btn-cancel">Cancel</button>
                    <button type="submit" class="modal-btn-confirm">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div id="moveModal" class="modal-backdrop">
        <div class="modal-content">
            <h2 class="modal-header">Move Item</h2>
            <form id="moveForm">
                <input type="hidden" id="moveFile">
                <label class="modal-label">Select destination folder:</label>
                <select id="folderSelect" class="modal-input"></select>
                <label class="modal-label">Or enter target path:</label>
                <input type="text" id="moveTarget" class="modal-input" placeholder="e.g., folder1/subfolder">
                <div class="modal-actions">
                    <button type="button" id="cmove" class="modal-btn-cancel">Cancel</button>
                    <button type="submit" class="modal-btn-confirm">Move</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editorModal" class="modal-backdrop">
        <div class="modal-content modal-editor">
            <h2 id="editor-filename" class="modal-header">Edit File</h2>
            <textarea id="editor-textarea"></textarea>
            <div class="modal-actions">
                <button type="button" id="editor-cancel" class="modal-btn-cancel">Cancel</button>
                <button type="button" id="editor-save" class="modal-btn-confirm">Save Changes</button>
            </div>
        </div>
    </div>

    <div id="createModal" class="modal-backdrop">
        <div class="modal-content">
            <h2 id="create-modal-header" class="modal-header">Create New</h2>
            <form id="createForm">
                <input type="hidden" id="create-item-type">
                <input type="text" id="create-item-name" class="modal-input" placeholder="Enter name">
                <div class="modal-actions">
                    <button type="button" id="create-cancel" class="modal-btn-cancel">Cancel</button>
                    <button type="submit" class="modal-btn-confirm">Create</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Custom Context Menu -->
    <div id="contextMenu" class="context-menu"></div>

    <script type="module" src="public/file_ops.js"></script>
    <script type="module" src="public/app.js"></script>
</body>
</html>
