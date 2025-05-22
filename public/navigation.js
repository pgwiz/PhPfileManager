// navigation.js
// Handles fetching and rendering files, and showing modals

import { deleteFile, showRename, showMove, handleRenameSubmit, handleMoveSubmit} from './file_ops.js';

const API_BASE = '/subwf/api';
let files = [];

export function setFileTableBodyRef(ref) {
  fileTableBody = ref;
}

let fileTableBody;

export async function fetchFiles() {
  try {
    const res = await fetch(`${API_BASE}/list_files.php`);
    files = await res.json();
    renderFiles();
  } catch (err) {
    console.error('Error fetching files', err);
  }
}


function renderFiles() {
    const fileTableBody = document.getElementById('fileTableBody');
    fileTableBody.innerHTML = '';
    
    (window.files || []).forEach(item => {
        const tr = document.createElement('tr');
        tr.className = 'border-b';
        
        // Name cell (clickable for folders)
        const nameTd = document.createElement('td');
        nameTd.className = 'px-4 py-2 cursor-pointer';
        
        if (item.type === 'directory') {
            nameTd.classList.add('text-blue-500', 'hover:underline');
            nameTd.addEventListener('click', () => fetchFiles(item.path));
        }
        
        nameTd.textContent = item.name;
        tr.appendChild(nameTd);
        
        // Size cell
        const sizeTd = document.createElement('td');
        sizeTd.className = 'px-4 py-2 text-right';
        sizeTd.textContent = item.type === 'file' ? `${(item.size / 1024).toFixed(2)} KB` : '--';
        tr.appendChild(sizeTd);
        
        // Actions cell (same as before)
        const actionsTd = document.createElement('td');
        actionsTd.className = 'px-4 py-2 space-x-2 text-center';
        
        if (item.type === 'file') {
            const dl = document.createElement('a');
            dl.href = `${API_BASE}/download.php?file=${encodeURIComponent(item.path)}`;
            dl.className = 'px-2 py-1 bg-blue-500 text-white rounded';
            dl.textContent = 'Download';
            actionsTd.appendChild(dl);
        }
        
        const del = document.createElement('button');
        del.className = 'px-2 py-1 bg-red-500 text-white rounded';
        del.textContent = 'Delete';
        del.onclick = () => deleteFile(item.path);
        actionsTd.appendChild(del);
        
        const ren = document.createElement('button');
        ren.className = 'px-2 py-1 bg-yellow-500 text-white rounded';
        ren.textContent = 'Rename';
        ren.onclick = () => showRename(item.path);
        actionsTd.appendChild(ren);
        
        const mv = document.createElement('button');
        mv.className = 'px-2 py-1 bg-green-500 text-white rounded';
        mv.textContent = 'Move';
        mv.onclick = () => showMove(item.path);
        actionsTd.appendChild(mv);
        
        tr.appendChild(actionsTd);
        fileTableBody.appendChild(tr);
    });
}


// public/navigation.js

// Initialize event listeners and set up DOM references
export function initNavigation() {

    const renameForm = document.getElementById('renameForm');
  const moveForm = document.getElementById('moveForm');


    if (renameForm) {
    renameForm.addEventListener('submit', handleRenameSubmit);
  }

  if (moveForm) {
    moveForm.addEventListener('submit', handleMoveSubmit);
  }
  const tableBody = document.getElementById('fileTableBody');
  if (!tableBody) return;

  // Set reference to tbody
  fileTableBody = tableBody;

  // Optionally attach global handlers here later
}