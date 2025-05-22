// public/app.js
// Entry point: initializes app and wires components

import { fetchFiles, uploadFile, populateFolders, navigateToParent,closeMove, closeRename } from './file_ops.js';
import { initNavigation } from './navigation.js';

// DOM Elements
const fileInput = document.getElementById('fileInput');
//const uploadBtn = document.getElementById('uploadBtn');
const backBtn = document.getElementById('backBtn');
const refreshBtn = document.getElementById('refreshBtn');
const cmove = document.getElementById('cmove');
const cren = document.getElementById('cmove');
const dropZone = document.getElementById('dropZone');

// Handle File Selection
function handleFileSelect(files) {
  if (files.length > 0) {
    Array.from(files).forEach(uploadFile);
  }
}

// Drag & Drop Handlers
function setupDragAndDrop() {
  // Drag Over
  dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-blue-500', 'bg-blue-50');
  });

  // Drag Leave
  dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-blue-500', 'bg-blue-50');
  });

  // Drop
  dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    
    if (e.dataTransfer.files.length > 0) {
      handleFileSelect(e.dataTransfer.files);
    }
  });

  // Click to Browse
  dropZone.addEventListener('click', () => {
    fileInput.click();
  });
}

// File Input Change
fileInput.addEventListener('change', (e) => {
  handleFileSelect(e.target.files);
});


// Initialization
document.addEventListener('DOMContentLoaded', () => {
  setupDragAndDrop();
  fetchFiles();
  initNavigation();
  populateFolders(); // Add this line
  
  /*uploadBtn.addEventListener('click', () => {
    const file = fileInput.files[0];
    if (file) uploadFile(file);
  });
*/
      
    // Back button
    backBtn.addEventListener('click', () => {
        navigateToParent();
    });
    cmove.addEventListener('click', () => {
        closeMove();
    });
    cren.addEventListener('click', () => {
        closeRename();
    });
    // Refresh button
    refreshBtn.addEventListener('click', () => {
        fetchFiles(window.currentPath || '');
    });
  
});
