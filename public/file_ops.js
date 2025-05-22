// public/file_ops.js
const API_BASE = '/subwf/api';
const CHUNK_SIZE = 9 * 1024 * 1024; // 9 MB

// State management
export let currentPath = '';
export const ROOT_PATH = '';


// Upload progress display
const uploadStatus = document.getElementById('uploadStatus') || document.createElement('div');

// Add this function
export function showUploadProgress(file, progress = 0) {
  const progressDiv = document.createElement('div');
  progressDiv.id = `progress-${file.name}`;
  progressDiv.className = 'mb-2 p-2 bg-gray-100 rounded';
  progressDiv.innerHTML = `
    <div class="truncate">${file.name}</div>
    <div class="w-full bg-gray-200 rounded-full h-2.5">
      <div class="bg-blue-600 h-2.5 rounded-full" style="width: ${progress}%"></div>
    </div>
  `;
  
  // Remove existing progress
  const existing = document.getElementById(`progress-${file.name}`);
  if (existing) existing.remove();
  
  uploadStatus.appendChild(progressDiv);
}


// =============================
// File Operations
// =============================

// Upload logic with chunking
export async function uploadFile(file) {
  showUploadProgress(file, 0);
  
  const sessionId = crypto.randomUUID();
  const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
  
  for (let i = 0; i < totalChunks; i++) {
    const start = i * CHUNK_SIZE;
    const blob = file.slice(start, start + CHUNK_SIZE);
    
    const form = new FormData();
    form.append('sessionId', sessionId);
    form.append('fileName', file.name);
    form.append('totalChunks', totalChunks);
    form.append('chunkIndex', i);
    form.append('chunkData', blob);
    
    await fetch(`${API_BASE}/upload_chunk.php`, { 
      method: 'POST', 
      body: form 
    })
    .then(r => r.json())
    .then(json => {
      const progress = Math.round(((i + 1) / totalChunks) * 100);
      showUploadProgress(file, progress);
      
      if (json.success && progress === 100) {
        // Show success briefly
        setTimeout(() => {
          showUploadProgress(file, 100);
          fetchFiles(); // Refresh after upload completes
        }, 500);
      }
      
      return json;
    })
    .catch(err => {
      console.error('Upload error:', err);
      showUploadProgress(file, 0);
      alert(`Error uploading ${file.name}`);
    });
  }

  const assembleForm = new FormData();
  assembleForm.append('sessionId', sessionId);
  assembleForm.append('fileName', file.name);
  assembleForm.append('totalChunks', totalChunks);
  
  await fetch(`${API_BASE}/assemble.php`, { 
    method: 'POST', 
    body: assembleForm 
  });
}


// Fetch files with path support
export async function fetchFiles(path = '') {
  try {
    const res = await fetch(`${API_BASE}/list_files.php${path ? `?path=${encodeURIComponent(path)}` : ''}`);
    const fileList = await res.json();
    window.files = fileList;
    window.currentPath = path;
    document.getElementById('currentPath').textContent = path || '/';
    renderFiles();
  } catch (err) {
    console.error('Error fetching files', err);
  }
}

// Navigate to a subfolder
export function navigateToFolder(folderName) {
  const newPath = currentPath ? `${currentPath}/${folderName}` : folderName;
  fetchFiles(newPath);
}

// Navigate to parent directory
export function navigateToParent() {
  if (currentPath) {
    const pathParts = currentPath.split('/').filter(Boolean);
    pathParts.pop(); // Remove last folder
    const newPath = pathParts.length > 0 ? pathParts.join('/') : '';
    fetchFiles(newPath);
  }
}

// Delete file
export async function deleteFile(name) {
  if (!confirm(`Delete ${name}?`)) return;
  const params = new URLSearchParams({ file: name });
  const res = await fetch(`${API_BASE}/delete_file.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: params
  });
  const json = await res.json();
  if (json.success) await fetchFiles();
}

// =============================
// Rename Operations
// =============================

// Show rename modal
export function showRename(name) {
  window.currentRename = name;
  document.getElementById('oldName').value = name;
  document.getElementById('newName').value = name;
  document.getElementById('renameModal').classList.remove('hidden');
}

// Handle rename form submission
export async function handleRenameSubmit(evt) {
  evt.preventDefault(); // ✅ Prevent default form submission

  const oldName = window.currentRename;
  const newName = document.getElementById('newName').value.trim();

  if (!newName) return;

  const form = new URLSearchParams({
    action: 'rename',
    old_name: oldName,
    new_name: newName
  });

  try {
    const res = await fetch(`${API_BASE}/rename_file.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: form
    });

    const json = await res.json();
    if (json.success) {
      closeRename();
      await fetchFiles(); // Refresh file list
    } else {
      alert(`Error: ${json.error}`);
    }
  } catch (err) {
    console.error('Rename error:', err);
    alert('An error occurred during rename.');
  }
}

export function closeRename() {
  document.getElementById('renameModal').classList.add('hidden');
  window.currentRename = null;
}

// =============================
// Move Operations
// =============================

// Show move modal
export function showMove(name) {
  window.currentMove = name;
  document.getElementById('moveTarget').value = '';
  document.getElementById('moveModal').classList.remove('hidden');
}

// Handle move form submission
export async function handleMoveSubmit(evt) {
  evt.preventDefault();

  const name = window.currentMove;
  const target = document.getElementById('moveTarget').value.trim() || '.';

  const form = new URLSearchParams({
    action: 'move',
    file: name,
    target_dir: target
  });

  try {
    const res = await fetch(`${API_BASE}/move_file.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: form
    });

    const json = await res.json();
    if (json.success) {
      closeMove();
      await fetchFiles();
    } else {
      alert(`Error: ${json.error}`);
    }
  } catch (err) {
    console.error('Move error:', err);
    alert('An error occurred while moving the file.');
  }
}
// Close move modal
export function closeMove() {
  const moveModal = document.getElementById('moveModal');
  if (moveModal) {
    moveModal.classList.add('hidden');
  }
  window.currentMove = null;
}

// =============================
// Folder Management
// =============================

// Populate folder select dropdown
export async function populateFolders() {
  try {
    const res = await fetch(`${API_BASE}/list_folders.php`);
    const data = await res.json();

    if (!data.success) {
      console.error('Failed to load folders:', data.error);
      return;
    }

    const folderSelect = document.getElementById('folderSelect');
    folderSelect.innerHTML = '<option value="">-- Create New Folder --</option>';

    const folders = Array.isArray(data.folders) ? data.folders : [];

    folders.forEach(folder => {
      const option = document.createElement('option');
      option.value = folder.path;
      option.textContent = folder.name;
      folderSelect.appendChild(option);
    });
  } catch (err) {
    console.error('Error loading folders:', err);
  }
}

// Update move target when folder is selected
export function updateMoveTarget() {
  const folderSelect = document.getElementById('folderSelect');
  const selected = folderSelect?.value;
  const moveTarget = document.getElementById('moveTarget');
  
  if (moveTarget && selected !== undefined) {
    moveTarget.value = selected;
  }
}

// =============================
// Rendering
// =============================

// Render file list
export function renderFiles() {

    const backBtn = document.getElementById('backBtn');
  if (backBtn) {
    backBtn.disabled = !window.currentPath;
  }
  
  const fileTableBody = document.getElementById('fileTableBody');
  if (!fileTableBody) return;
  
  fileTableBody.innerHTML = '';
  
  (window.files || []).forEach(item => {
    const tr = document.createElement('tr');
    tr.className = 'border-b';
    
    // Name cell
    const nameTd = document.createElement('td');
    nameTd.className = 'px-4 py-2 cursor-pointer';
    
    if (item.type === 'directory') {
      nameTd.classList.add('text-blue-500', 'hover:underline');
      nameTd.addEventListener('click', () => navigateToFolder(item.name));
    }
    
    nameTd.textContent = item.name;
    tr.appendChild(nameTd);
    
    // Size cell
    const sizeTd = document.createElement('td');
    sizeTd.className = 'px-4 py-2 text-right';
    sizeTd.textContent = item.type === 'file' ? 
      `${(item.size / 1024).toFixed(2)} KB` : '--';
    tr.appendChild(sizeTd);
    
    // Actions cell
    const actionsTd = document.createElement('td');
    actionsTd.className = 'px-4 py-2 space-x-2 text-center';
    
    if (item.type === 'file') {
      const dl = document.createElement('a');
      dl.href = `${API_BASE}/download.php?file=${encodeURIComponent(item.path || item.name)}`;
      dl.className = 'px-2 py-1 bg-blue-500 text-white rounded';
      dl.textContent = 'Download';
      actionsTd.appendChild(dl);
    }
    
    const del = document.createElement('button');
    del.className = 'px-2 py-1 bg-red-500 text-white rounded';
    del.textContent = 'Delete';
    del.onclick = () => deleteFile(item.name);
    actionsTd.appendChild(del);
    
    const ren = document.createElement('button');
    ren.className = 'px-2 py-1 bg-yellow-500 text-white rounded';
    ren.textContent = 'Rename';
    ren.onclick = () => showRename(item.name);
    actionsTd.appendChild(ren);
    
    const mv = document.createElement('button');
    mv.className = 'px-2 py-1 bg-green-500 text-white rounded';
    mv.textContent = 'Move';
    mv.onclick = () => showMove(item.name);
    actionsTd.appendChild(mv);
    
    tr.appendChild(actionsTd);
    fileTableBody.appendChild(tr);
  });
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', () => {
  const folderSelect = document.getElementById('folderSelect');
  if (folderSelect) {
    folderSelect.addEventListener('change', updateMoveTarget);
  }
});