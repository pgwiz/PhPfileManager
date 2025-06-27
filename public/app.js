// public/app.js

import {
    fetchFiles,
    uploadFile,
    navigateToParent,
    closeMove,
    closeRename,
    setViewMode,
    viewMode,
    showCreateModal,
    closeCreateModal,
    handleCreateItem,
    closeEditor,
    showContextMenu,
    hideContextMenu,
    handleRenameSubmit,
    handleMoveSubmit
} from './file_ops.js';

// --- DOM Elements ---
const fileInput = document.getElementById('fileInput');
const backBtn = document.getElementById('backBtn');
const refreshBtn = document.getElementById('refreshBtn');
const createFolderBtn = document.getElementById('createFolderBtn');
const createFileBtn = document.getElementById('createFileBtn');
const dropZone = document.getElementById('dropZone');
const fileContainer = document.getElementById('fileContainer');

// Modal Elements & Forms
const cancelMoveBtn = document.getElementById('cmove');
const cancelRenameBtn = document.getElementById('cren');
const cancelEditorBtn = document.getElementById('editor-cancel');
const createForm = document.getElementById('createForm');
const cancelCreateBtn = document.getElementById('create-cancel');
const renameForm = document.getElementById('renameForm');
const moveForm = document.getElementById('moveForm');


// View Control Buttons
const viewListBtn = document.getElementById('view-list');
const viewGridBtn = document.getElementById('view-grid');
const viewCompactBtn = document.getElementById('view-compact');


// --- Functions ---

function handleFileSelect(files) {
    if (files.length > 0) {
        Array.from(files).forEach(uploadFile);
    }
}

function setupDragAndDrop() {
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-blue-300', 'bg-blue-50');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-blue-300', 'bg-blue-50');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-blue-300', 'bg-blue-50');
        if (e.dataTransfer.files.length > 0) {
            handleFileSelect(e.dataTransfer.files);
        }
    });

    dropZone.addEventListener('click', () => {
        fileInput.click();
    });
}

function updateActiveViewButton() {
    [viewListBtn, viewGridBtn, viewCompactBtn].forEach(btn => btn.classList.remove('active'));
    document.getElementById(`view-${viewMode}`).classList.add('active');
}

// --- Initialize Application ---
document.addEventListener('DOMContentLoaded', () => {
    // Initial Setup
    setupDragAndDrop();
    updateActiveViewButton();
    fetchFiles();

    // --- Event Listeners ---
    fileInput.addEventListener('change', (e) => handleFileSelect(e.target.files));
    backBtn.addEventListener('click', navigateToParent);
    createFolderBtn.addEventListener('click', () => showCreateModal('folder'));
    createFileBtn.addEventListener('click', () => showCreateModal('file'));

    refreshBtn.addEventListener('click', () => {
        const pathSpan = document.getElementById('currentPath').textContent;
        const currentPath = pathSpan.startsWith('/') ? pathSpan.substring(1) : pathSpan;
        fetchFiles(currentPath);
    });
    
    // Modals
    cancelMoveBtn.addEventListener('click', closeMove);
    cancelRenameBtn.addEventListener('click', closeRename);
    cancelEditorBtn.addEventListener('click', closeEditor);
    cancelCreateBtn.addEventListener('click', closeCreateModal);

    // Form Submissions
    createForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const type = document.getElementById('create-item-type').value;
        const name = document.getElementById('create-item-name').value;
        handleCreateItem(type, name);
    });
    renameForm.addEventListener('submit', handleRenameSubmit);
    moveForm.addEventListener('submit', handleMoveSubmit);

    // View switchers
    viewListBtn.addEventListener('click', () => {
        setViewMode('list');
        updateActiveViewButton();
    });
    viewGridBtn.addEventListener('click', () => {
        setViewMode('grid');
        updateActiveViewButton();
    });
    viewCompactBtn.addEventListener('click', () => {
        setViewMode('compact');
        updateActiveViewButton();
    });

    // Context Menu Listeners
    fileContainer.addEventListener('contextmenu', (e) => {
        const targetItem = e.target.closest('[data-path]');
        if (targetItem) {
            e.preventDefault();
            const item = {
                path: targetItem.dataset.path,
                type: targetItem.dataset.type,
                name: targetItem.dataset.name
            };
            showContextMenu(e, item);
        }
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.context-menu')) {
            hideContextMenu();
        }
    });
});
