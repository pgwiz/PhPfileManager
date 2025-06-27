// public/file_ops.js

const API_BASE = '/subwf/api';
const CHUNK_SIZE = 9 * 1024 * 1024; // 9 MB

// State
let currentPath = '';
let files = [];
export let viewMode = localStorage.getItem('fileManagerView') || 'list';

// DOM Elements
const fileContainer = document.getElementById('fileContainer');
const currentPathSpan = document.getElementById('currentPath');
const backBtn = document.getElementById('backBtn');
const uploadStatus = document.getElementById('uploadStatus');
const contextMenu = document.getElementById('contextMenu');

// --- Helper Functions ---
function getFileIcon(type, name) {
    if (type === 'directory') return '📁';
    const ext = name.split('.').pop().toLowerCase();
    switch (ext) {
        case 'jpg': case 'jpeg': case 'png': case 'gif': return '🖼️';
        case 'pdf': return '📕';
        case 'doc': case 'docx': return '📝';
        case 'xls': case 'xlsx': return '📊';
        case 'zip': case 'rar': return '📦';
        case 'txt': case 'md': case 'html': case 'css': case 'js': case 'php': return '✏️';
        default: return '📄';
    }
}

function isEditable(name) {
    const editableExtensions = ['txt', 'md', 'html', 'css', 'js', 'php', 'json', 'xml', 'log'];
    const ext = name.split('.').pop().toLowerCase();
    return editableExtensions.includes(ext);
}

function formatSize(bytes) {
    if (bytes === null || bytes === 0) return '--';
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return parseFloat((bytes / Math.pow(1024, i)).toFixed(2)) + ' ' + ['B', 'KB', 'MB', 'GB'][i];
}

function createActions(item) {
    const container = document.createElement('div');
    container.className = 'actions-container';

    if (isEditable(item.name)) {
        const editBtn = document.createElement('button');
        editBtn.className = 'action-btn action-btn-edit';
        editBtn.textContent = 'Edit';
        editBtn.onclick = () => showEditor(item.path);
        container.appendChild(editBtn);
    }
    
    if (item.type === 'file') {
        const dl = document.createElement('a');
        dl.href = `${API_BASE}/phpfile_operations.php?action=download&file=${encodeURIComponent(item.path)}`;
        dl.className = 'action-btn action-btn-dl';
        dl.textContent = 'Download';
        container.appendChild(dl);
    }

    const ren = document.createElement('button');
    ren.className = 'action-btn action-btn-ren';
    ren.textContent = 'Rename';
    ren.onclick = (e) => { e.stopPropagation(); showRename(item.path, item.name); };
    container.appendChild(ren);
    
    const mov = document.createElement('button');
    mov.className = 'action-btn action-btn-mov';
    mov.textContent = 'Move';
    mov.onclick = (e) => { e.stopPropagation(); showMove(item.path); };
    container.appendChild(mov);

    const del = document.createElement('button');
    del.className = 'action-btn action-btn-del';
    del.textContent = 'Delete';
    del.onclick = (e) => { e.stopPropagation(); deleteItem(item.path); };
    container.appendChild(del);

    return container;
}


// --- View Renderers ---

function renderListView() {
    fileContainer.innerHTML = '';
    fileContainer.className = 'view-list';

    const table = document.createElement('table');
    table.innerHTML = `<thead><tr><th>Name</th><th>Size</th><th>Modified</th><th>Actions</th></tr></thead>`;
    const tbody = document.createElement('tbody');

    files.forEach(item => {
        const tr = document.createElement('tr');
        tr.dataset.path = item.path;
        tr.dataset.type = item.type;
        tr.dataset.name = item.name;

        const nameTd = document.createElement('td');
        nameTd.textContent = item.name;
        if (item.type === 'directory') {
            nameTd.className = 'item-name-dir';
            nameTd.onclick = () => navigateToFolder(item.path);
        }
        
        const sizeTd = document.createElement('td');
        sizeTd.textContent = formatSize(item.size);

        const modTd = document.createElement('td');
        modTd.textContent = new Date(item.modified).toLocaleString();
        
        const actTd = document.createElement('td');
        actTd.appendChild(createActions(item));

        tr.append(nameTd, sizeTd, modTd, actTd);
        tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    fileContainer.appendChild(table);
}

function renderGridView() {
    fileContainer.innerHTML = '';
    fileContainer.className = 'view-grid';

    files.forEach(item => {
        const card = document.createElement('div');
        card.className = 'file-card';
        card.dataset.path = item.path;
        card.dataset.type = item.type;
        card.dataset.name = item.name;

        if (item.type === 'directory') {
            card.onclick = () => navigateToFolder(item.path);
        }
        
        card.innerHTML = `
            <div class="file-icon">${getFileIcon(item.type, item.name)}</div>
            <div class="file-name">${item.name}</div>
            <div class="file-size">${formatSize(item.size)}</div>
        `;
        card.appendChild(createActions(item));
        fileContainer.appendChild(card);
    });
}

function renderCompactView() {
    fileContainer.innerHTML = '';
    fileContainer.className = 'view-compact';

    files.forEach(item => {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'compact-item';
        itemDiv.dataset.path = item.path;
        itemDiv.dataset.type = item.type;
        itemDiv.dataset.name = item.name;

        const icon = document.createElement('div');
        icon.className = 'compact-icon';
        icon.textContent = getFileIcon(item.type, item.name);

        const name = document.createElement('div');
        name.className = 'compact-name';
        name.textContent = item.name;
        if (item.type === 'directory') {
            name.classList.add('item-name-dir');
            name.onclick = () => navigateToFolder(item.path);
        }

        const size = document.createElement('div');
        size.className = 'compact-size';
        size.textContent = formatSize(item.size);

        itemDiv.append(icon, name, size, createActions(item));
        fileContainer.appendChild(itemDiv);
    });
}

function renderFiles() {
    backBtn.disabled = !currentPath;
    currentPathSpan.textContent = `/${currentPath}`;
    
    if (files.length === 0) {
        fileContainer.innerHTML = `<div style="text-align:center; padding: 40px; color: #6c757d;">This folder is empty.</div>`;
        return;
    }

    switch (viewMode) {
        case 'grid':
            renderGridView();
            break;
        case 'compact':
            renderCompactView();
            break;
        case 'list':
        default:
            renderListView();
            break;
    }
}


// --- API Functions ---
export async function fetchFiles(path = '') {
    currentPath = path;
    fileContainer.innerHTML = `<div style="text-align:center; padding: 40px; color: #6c757d;">Loading...</div>`;
    try {
        const res = await fetch(`${API_BASE}/phpfile_operations.php?action=list_files&path=${encodeURIComponent(path)}`);
        if (!res.ok) throw new Error(`Server error: ${res.statusText}`);
        files = await res.json();
        renderFiles();
    } catch (err) {
        console.error('Error fetching files:', err);
        fileContainer.innerHTML = `<div style="text-align:center; padding: 40px; color: #dc3545;">Error loading files.</div>`;
    }
}

export function navigateToFolder(path) {
    fetchFiles(path);
}

export function navigateToParent() {
    if (!currentPath) return;
    const pathParts = currentPath.split('/');
    pathParts.pop();
    fetchFiles(pathParts.join('/'));
}

export async function deleteItem(path) {
    if (!confirm(`Are you sure you want to delete '${path}'? This action cannot be undone.`)) return;
    const form = new URLSearchParams({ action: 'delete', path: path });
    try {
        const res = await fetch(`${API_BASE}/phpfile_operations.php`, { method: 'POST', body: form });
        const json = await res.json();
        if (json.success) {
            fetchFiles(currentPath);
        } else {
            alert(`Error: ${json.error}`);
        }
    } catch (err) {
        alert('An error occurred during deletion.');
    }
}

export async function handleCreateItem(type, name) {
    if (!name) return;

    const form = new URLSearchParams({
        action: 'create_item',
        current_path: currentPath,
        item_name: name,
        item_type: type
    });

    try {
        const res = await fetch(`${API_BASE}/phpfile_operations.php`, { method: 'POST', body: form });
        const json = await res.json();
        if (json.success) {
            closeCreateModal();
            fetchFiles(currentPath);
        } else {
            alert(`Error: ${json.error}`);
        }
    } catch (err) {
        alert('An error occurred while creating the item.');
    }
}

export async function showEditor(path) {
    const editorModal = document.getElementById('editorModal');
    const editorTextarea = document.getElementById('editor-textarea');
    const editorFilename = document.getElementById('editor-filename');
    
    editorFilename.textContent = `Editing: ${path.split('/').pop()}`;
    editorTextarea.value = 'Loading content...';
    editorModal.style.display = 'flex';

    try {
        const res = await fetch(`${API_BASE}/phpfile_operations.php?action=get_content&path=${encodeURIComponent(path)}`);
        const json = await res.json();
        if (json.success) {
            editorTextarea.value = json.content;
        } else {
            editorTextarea.value = `Error loading content: ${json.error}`;
        }
    } catch (err) {
        editorTextarea.value = 'Failed to fetch content.';
    }

    document.getElementById('editor-save').onclick = () => saveContent(path, editorTextarea.value);
}

export function closeEditor() {
    document.getElementById('editorModal').style.display = 'none';
}

async function saveContent(path, content) {
    const form = new URLSearchParams({
        action: 'save_content',
        path: path,
        content: content
    });
    try {
        const res = await fetch(`${API_BASE}/phpfile_operations.php`, { method: 'POST', body: form });
        const json = await res.json();
        if (json.success) {
            closeEditor();
        } else {
            alert(`Error saving file: ${json.error}`);
        }
    } catch(err) {
        alert('Failed to save the file.');
    }
}

export async function populateFolders() {
    const folderSelect = document.getElementById('folderSelect');
    try {
        const res = await fetch(`${API_BASE}/phpfile_operations.php?action=list_folders`);
        const data = await res.json();
        if (!data.success) return;

        folderSelect.innerHTML = '';
        if (Array.isArray(data.folders)) {
            data.folders.forEach(folder => {
                const option = document.createElement('option');
                option.value = folder.path;
                option.textContent = folder.name === '(Root Directory)' ? folder.name : `/${folder.path}`;
                folderSelect.appendChild(option);
            });
        }
    } catch (err) {
        console.error('Error loading folders:', err);
    }
}

export function showUploadProgress(file, progress = 0) {
    let progressDiv = document.getElementById(`progress-${file.name}`);
    if (!progressDiv) {
        progressDiv = document.createElement('div');
        progressDiv.id = `progress-${file.name}`;
        progressDiv.className = 'mb-2 p-2 bg-gray-100 rounded';
        uploadStatus.appendChild(progressDiv);
    }

    progressDiv.innerHTML = `
    <div class="truncate">${file.name}</div>
    <div class="w-full bg-gray-200 rounded-full h-2.5">
      <div class="bg-blue-600 h-2.5 rounded-full" style="width: ${progress}%"></div>
    </div>`;

    if (progress === 100) {
        setTimeout(() => progressDiv.remove(), 2000);
    }
}


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

        try {
            const res = await fetch(`${API_BASE}/upload_chunk.php`, { method: 'POST', body: form });
            const json = await res.json();
            const progress = Math.round(((i + 1) / totalChunks) * 100);
            showUploadProgress(file, progress);
        } catch (err) {
            console.error('Upload error:', err);
            showUploadProgress(file, 0);
            alert(`Error uploading ${file.name}`);
            return;
        }
    }

    const assembleForm = new FormData();
    assembleForm.append('sessionId', sessionId);
    assembleForm.append('fileName', file.name);
    assembleForm.append('totalChunks', totalChunks);
    await fetch(`${API_BASE}/assemble.php`, { method: 'POST', body: assembleForm });

    fetchFiles(currentPath);
}

// --- Modal Handling ---
const renameModal = document.getElementById('renameModal');
const moveModal = document.getElementById('moveModal');
const createModal = document.getElementById('createModal');

export function showRename(path, currentName) {
    document.getElementById('oldName').value = path;
    document.getElementById('newName').value = currentName;
    renameModal.style.display = 'flex';
    document.getElementById('newName').focus();
}

export function closeRename() {
    renameModal.style.display = 'none';
}

export function showMove(path) {
    document.getElementById('moveFile').value = path;
    document.getElementById('moveTarget').value = '';
    populateFolders();
    moveModal.style.display = 'flex';
}

export function closeMove() {
    moveModal.style.display = 'none';
}

export function showCreateModal(type) {
    document.getElementById('create-modal-header').textContent = `Create New ${type.charAt(0).toUpperCase() + type.slice(1)}`;
    document.getElementById('create-item-type').value = type;
    document.getElementById('create-item-name').value = '';
    createModal.style.display = 'flex';
    document.getElementById('create-item-name').focus();
}

export function closeCreateModal() {
    createModal.style.display = 'none';
}


// --- Form Submission Handlers ---
export async function handleRenameSubmit(e) {
    e.preventDefault();
    const oldPath = document.getElementById('oldName').value;
    const newName = document.getElementById('newName').value;
    if (!newName) return;
    const form = new URLSearchParams({ action: 'rename', old_path: oldPath, new_name: newName });
    try {
        const res = await fetch(`${API_BASE}/phpfile_operations.php`, { method: 'POST', body: form });
        const json = await res.json();
        if (json.success) {
            closeRename();
            fetchFiles(currentPath);
        } else { alert(`Error: ${json.error}`); }
    } catch (err) { alert('An error occurred during rename.'); }
}

export async function handleMoveSubmit(e) {
    e.preventDefault();
    const sourcePath = document.getElementById('moveFile').value;
    const targetDir = document.getElementById('moveTarget').value;
    const form = new URLSearchParams({ action: 'move', source_path: sourcePath, target_dir: targetDir });
    try {
        const res = await fetch(`${API_BASE}/phpfile_operations.php`, { method: 'POST', body: form });
        const json = await res.json();
        if (json.success) {
            closeMove();
            fetchFiles(currentPath);
        } else { alert(`Error: ${json.error}`); }
    } catch (err) { alert('An error occurred while moving.'); }
}

export function setViewMode(mode) {
    viewMode = mode;
    localStorage.setItem('fileManagerView', mode);
    renderFiles();
}

// --- Context Menu Logic ---
export function showContextMenu(e, item) {
    e.preventDefault();
    contextMenu.innerHTML = '';

    const createMenuItem = (text, icon, action) => {
        const menuItem = document.createElement('div');
        menuItem.className = 'context-menu-item';
        menuItem.innerHTML = `<span>${icon}</span><span>${text}</span>`;
        menuItem.onclick = () => {
            action();
            hideContextMenu();
        };
        contextMenu.appendChild(menuItem);
    };

    if (item.type === 'file') {
        if (isEditable(item.name)) {
            createMenuItem('Edit', '✏️', () => showEditor(item.path));
        }
        createMenuItem('Download', '⬇️', () => window.location.href = `${API_BASE}/phpfile_operations.php?action=download&file=${encodeURIComponent(item.path)}`);
    }

    createMenuItem('Rename', '🔄', () => showRename(item.path, item.name));
    createMenuItem('Move', '➡️', () => showMove(item.path));
    
    const separator = document.createElement('div');
    separator.className = 'context-menu-separator';
    contextMenu.appendChild(separator);

    createMenuItem('Delete', '🗑️', () => deleteItem(item.path));
    
    contextMenu.style.display = 'block';
    contextMenu.style.left = `${e.pageX}px`;
    contextMenu.style.top = `${e.pageY}px`;
}

export function hideContextMenu() {
    contextMenu.style.display = 'none';
}
