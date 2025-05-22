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
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.x/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Advanced PHP File Manager</h1>

    <!-- Upload Form -->
    <div class="mb-6 flex items-center space-x-4">
      <input type="file" id="fileInput" class="border px-4 py-2 rounded w-full">
      <button id="uploadBtn" class="px-4 py-2 bg-blue-500 text-white rounded">Upload</button>
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
          <button type="button" onclick="closeRename()" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
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
        <input type="text" id="moveTarget" class="w-full border p-2 mb-4 rounded" placeholder="Target directory (e.g. subfolder)">
        <div class="flex justify-end space-x-2">
          <button type="button" onclick="closeMove()" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
          <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Move</button>
        </div>
      </form>
    </div>
  </div>

  <!-- App JS -->
  <script src="public/app.js"></script>
</body>
</html>