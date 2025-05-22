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
?><!DOCTYPE html><html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PHP File Manager</title>
  <!-- Updated Tailwind CSS -->
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <script src="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link href="public/tail.css" rel="stylesheet">
  
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Advanced PHP File Manager</h1>

    <!-- Upload Form -->
    <div id="dropZone" class="mb-6 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-blue-500 transition-colors">
      <input type="file" id="fileInput" class="hidden">
      <div class="text-gray-500">
        <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
        </svg>
        <p class="mt-2">Drag & drop files here, or <span class="text-blue-500">browse</span></p>
      </div>
    </div>
    <div id="uploadProgress" class="hidden mb-4">
      <div class="bg-gray-200 rounded-full h-2.5 w-full">
        <div id="progressBar" class="bg-blue-500 h-2.5 rounded-full" style="width: 0%;"></div>
      </div>
      <p id="progressText" class="text-sm text-gray-600 mt-1">0%</p>
    </div>

    <div id="uploadStatus" class="mb-6"></div>
    <!-- Navigation -->
   <!-- In index.php -->
<div class="flex justify-between mb-6">
  <div>
    <button id="backBtn" class="px-4 py-2 bg-gray-300 rounded disabled:opacity-50" disabled>Back</button>
    <button id="refreshBtn" class="px-4 py-2 bg-green-500 text-white rounded ml-2">Refresh</button>
  </div>
  <div class="text-sm text-gray-600">
    Current Path: <span id="currentPath">/</span>
  </div>
</div>
    <!-- File Table -->
    <table class="w-full table-auto text-sm">
      <thead>
        <tr class="bg-gray-200">
          <th class="text-left px-4 py-2">Name</th>
          <th class="text-right px-4 py-2">Size</th>
          <th class="text-center px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody id="fileTableBody">
        <!-- Populated by JS -->
      </tbody>
    </table>

  </div>

  <!-- Rename Modal -->
  <div id="renameModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center">
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
      <h2 class="text-xl mb-4">Rename File</h2>
      <form id="renameForm">
        <input type="hidden" id="oldName">
        <input type="text" id="newName" class="w-full border p-2 mb-4 rounded" placeholder="New name">
        <div class="flex justify-end space-x-2">
          <button type="button" id="cren" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
          <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded">Save</button>
        </div>
      </form>
    </div>
  </div>

 
    <!-- Move Modal -->
  
      <div id="moveModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center">
        <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
          <h2 class="text-xl mb-4">Move File</h2>
          <form id="moveForm">
            <input type="hidden" id="moveFile">
            
            <!-- Folder Selector -->
            <label class="block mb-2 text-sm font-medium">Select Folder</label>
            <select id="folderSelect" class="w-full border p-2 rounded mb-4" onchange="updateMoveTarget()">
              <option value="">-- Create New Folder --</option>
              <!-- Options populated by populateFolders() -->
            </select>

            <!-- Advanced Path Input -->
            <label class="block mb-2 text-sm font-medium">Or enter target path</label>
            <input type="text" id="moveTarget" class="w-full border p-2 rounded mb-4" placeholder="e.g., folder1/folder2">

            <div class="flex justify-end space-x-2">
            <button type="button" id="cmove" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>  
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Move</button>
            </div>
          </form>
        </div>
      </div>

  <!-- App JS -->
  <script type="module" src="public/navigation.js"></script>
  <script type="module" src="public/file_ops.js"></script>
  <script type="module" src="public/app.js"></script>
</body>
</html>
