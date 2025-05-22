# 📁 PHP File Manager with Chunked Uploads

A simple yet powerful file manager built with PHP, HTML, CSS, and JavaScript featuring chunked uploads, file operations, and responsive UI.

## 🔧 Features
- ✅ 9MB chunked file uploads with resumable support
- 🗂 Folder navigation and management
- 📤 Download/Delete/Rename/Move operations
- 🖱 Drag & Drop upload support
- 🔍 File search and filtering
- 📱 Responsive design with Tailwind CSS

## 📦 Project Structure
`
project-root/
├── index.php # Main interface
├── public/
│ ├── app.js # Module loader
│ ├── file_ops.js # File operations logic
│ ├── navigation.js # UI navigation logic
│ └── styles.css # Custom styles
├── api/
│ ├── config.php # Global config & helpers
│ ├── upload_chunk.php # Handles file chunks
│ ├── assemble.php # Reassembles chunks
│ ├── list_files.php # Directory listing
│ ├── download.php # File streaming
│ ├── delete_file.php # File deletion
│ ├── rename_file.php # Rename operations
│ └── move_file.php # Move operations
└── uploads/
├── temp/ # Temporary storage
└── files/ # Final files storage
`
## 🛠️ Installation
1. Clone the repository:
```bash
git clone https://github.com/pgwiz/PhPfileManager.git
```

2. Install dependencies:
```bash
# No npm packages needed - just pure PHP + Tailwind
```

3. Configure database in `api/config.php`:
```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'pov');
define('DB_USER', 'root');
define('DB_PASS', 'root');
```

4. Start PHP server:
```bash
cd project-root
php -S localhost:8000 -t .
```

## 🚀 Usage
### Development Server
```bash
php -S localhost:8000 -t .
```

### Production Setup
1. Upload files to PHP-enabled server
2. Ensure `uploads/files` and `uploads/temp` are writable
3. Configure `.htaccess` for Apache or Nginx rules

## 🧪 API Endpoints
| Endpoint          | Method | Description                  |
|------------------|--------|------------------------------|
| `/api/upload_chunk.php` | POST   | Upload file chunks           |
| `/api/assemble.php`     | POST   | Reassemble chunks            |
| `/api/list_files.php`   | GET    | List directory contents      |
| `/api/delete_file.php`  | POST   | Delete files                 |
| `/api/rename_file.php`  | POST   | Rename files                 |
| `/api/move_file.php`    | POST   | Move files                   |
| `/api/download.php`     | GET    | Stream files for download    |

## 📝 File Operations
### Chunked Upload
```javascript
// Uses 9MB chunks
const CHUNK_SIZE = 9 * 1024 * 1024;
```

### File Management
- Delete: Soft confirmation with `confirm()`
- Rename: Modal UI with validation
- Move: Folder selection with path traversal protection

## 🎨 UI Components
- Tailwind CSS v3.4.17 CDN
- Responsive table layout
- Modal dialogs for operations
- File type indicators
- Size formatting (KB/MB)

## 🔐 Security Considerations
- Filename sanitization with `sanitizeFilename()`
- Path traversal protection via `basename()`
- File existence checks before operations
- Form validation on both client and server
- Session-based chunk storage

## 📈 Roadmap
- [ ] Add folder creation
- [ ] Implement upload progress bars
- [ ] Add file search/filter
- [ ] Implement user authentication
- [ ] Add dark mode toggle
- [ ] Support parallel chunk uploads

## 🤝 Contributing
1. Fork the project
2. Create your feature branch (`git checkout -b feature/new-feature`)
3. Commit changes (`git commit -am 'Add feature'`)
4. Push to branch (`git push origin feature/new-feature`)
5. Open pull request

## 📄 License
MIT License - see LICENSE.md

## 📬 Contact
Your Name - [@yourhandle](https://twitter.com/yourhandle) - email@example.com
```

This documentation includes:
- Project overview and features
- Clear file structure diagram
- Installation instructions
- API endpoint reference
- Security considerations
- Roadmap and contribution guidelines
- License and contact information

You can enhance this further by:
1. Adding screenshots of the UI
2. Including example requests/responses
3. Adding deployment instructions for popular hosts
4. Creating a changelog
5. Adding badges for build status/license
