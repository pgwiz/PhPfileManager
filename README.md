# 📁 Advanced PHP File Manager

A modern, feature-rich file manager built with PHP and vanilla JavaScript. It features a responsive UI, chunked uploads for large files, a text editor, multiple view modes, and a complete suite of file management tools.

## 🔧 Features
- ✅ **Chunked Uploads**: Reliably upload large files with a 9MB chunking mechanism.
- 📂 **Full File & Folder Management**: Create, delete, rename, and move both files and folders.
- ✏️ **Built-in Text Editor**: Edit text-based files (`.txt`, `.html`, `.css`, `.js`, `.php`, etc.) directly in the browser.
- 🖱️ **Modern UI & UX**:
    - Drag & Drop file uploads.
    - Right-click context menu for quick actions.
    - Three persistent view modes: List, Grid, and Compact.
- 📱 **Responsive Design**: A clean, modern interface that works seamlessly on desktop, tablet, and mobile devices.
- 🔒 **Secure**: Built with security in mind, featuring robust path sanitization to prevent directory traversal attacks.
- ⚙️ **Customizable**: Uses vanilla CSS for theming, making it easy to adapt to your own style.

## 📦 Project Structure
The project has been streamlined to use a single, consolidated API endpoint for most operations.

project-root/
├── main_index.php      # The main user interface file
├── api/
│   ├── config.php          # Global configuration & database settings
│   ├── phpfile_operations.php # CONSOLIDATED API for all actions (list, delete, rename, etc.)
│   ├── upload_chunk.php    # Handles individual file chunks during upload
│   └── assemble.php        # Reassembles chunks into the final file
├── public/
│   ├── app.js              # Main application logic and event listeners
│   ├── file_ops.js         # Core frontend logic (API calls, rendering, modals)
│   ├── theme.css           # The main theme and styles
│   └── responsive.css      # CSS for mobile and tablet responsiveness
└── uploads/
├── temp/               # Temporary storage for file chunks
└── files/              # Final destination for all uploaded files


## 🛠️ Installation
1.  **Clone the repository:**
    ```bash
    git clone [https://github.com/pgwiz/PhPfileManager.git](https://github.com/pgwiz/PhPfileManager.git)
    ```

2.  **Configure your web server:**
    - Point your web server (Apache, Nginx, etc.) to the root directory of the project.
    - Ensure the PHP engine is enabled.

3.  **Set Permissions:**
    - The web server must have write permissions for the `uploads/files/` and `uploads/temp/` directories.
    ```bash
    # On a Linux server, you can often do this with:
    sudo chown -R www-data:www-data uploads
    sudo chmod -R 755 uploads
    ```

4.  **Start a development server (optional):**
    ```bash
    # Navigate to the project's root directory
    php -S localhost:8000
    ```

## 🚀 API Endpoints
The backend now uses a single powerful endpoint for most operations, controlled by an `action` parameter.

| Endpoint | Method | Action Parameter | Description |
| :--- | :--- | :--- | :--- |
| `/api/phpfile_operations.php` | GET | `list_files` | Lists contents of a directory. |
| `/api/phpfile_operations.php` | POST | `create_item` | Creates a new file or folder. |
| `/api/phpfile_operations.php` | POST | `delete` | Deletes a file or folder. |
| `/api/phpfile_operations.php` | POST | `rename` | Renames a file or folder. |
| `/api/phpfile_operations.php` | POST | `move` | Moves a file or folder. |
| `/api/phpfile_operations.php` | GET | `get_content` | Retrieves text content for the editor. |
| `/api/phpfile_operations.php` | POST | `save_content`| Saves text content from the editor. |
| `/api/phpfile_operations.php` | GET | `download` | Streams a file for download. |
| `/api/upload_chunk.php` | POST | *(N/A)* | Handles an individual file chunk. |
| `/api/assemble.php` | POST | *(N/A)* | Assembles chunks into the final file. |


## 🔐 Security Considerations
- **Path Traversal Protection**: A robust `sanitizePath()` function on the backend ensures that user-provided paths are decoded and validated, preventing any access outside the designated `uploads/files` directory.
- **Filename Sanitization**: New filenames for creating or renaming items are sanitized to prevent illegal characters or path manipulation.
- **Request Validation**: The backend checks for the correct HTTP method (e.g., `POST` for destructive actions) for all operations.

## 📈 Roadmap
- [x] ~~Add folder creation~~
- [x] ~~Implement upload progress bars~~
- [x] ~~Text Editor for files~~
- [x] ~~Right-click context menu~~
- [ ] Implement user authentication and permissions.
- [ ] Add file search/filter functionality within the current directory.
- [ ] Add dark mode toggle and more themes.
- [ ] Implement parallel chunk uploads for faster performance.
- [ ] Add support for file previews (e.g., image thumbnails).

## 🤝 Contributing
1.  Fork the project.
2.  Create your feature branch (`git checkout -b feature/NewAmazingFeature`).
3.  Commit your changes (`git commit -m 'Add some NewAmazingFeature'`).
4.  Push to the branch (`git push origin feature/NewAmazingFeature`).
5.  Open a Pull Request.

## 📄 License
This project is licensed under the MIT License - see the `LICENSE.md` file for details.

## 📬 Contact
Your Name - [@pgwiz](https://twitter.com/pgwiz) - support@pgwiz.uk
