<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: /auth/login.php');
    exit;
}

$username = htmlspecialchars($_SESSION['username']); // Use the session username
?>

<!DOCTYPE html>
<html>
<head>
    <title>User File Manager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .breadcrumb {
            background-color: #f8f9fa;
            padding: 8px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .breadcrumb-item a {
            text-decoration: none;
            color: #007bff;
        }
        .breadcrumb-item.active {
            color: #6c757d;
        }
        .icon-folder {
            color: #ffc107;
        }
        .icon-file {
            color: #6c757d;
        }
    </style>
</head>
<body class="container mt-5">
    <h2>User File Manager</h2>

    <!-- Hidden input for username -->
    <input type="hidden" id="username" value="<?= $username ?>">

    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb" id="breadcrumb"></ol>
    </nav>

    <!-- Buttons for actions -->
    <div class="mb-3">
        <button onclick="showCreateModal()" class="btn btn-primary">Create File/Folder</button>
        <button onclick="showUploadModal()" class="btn btn-success">Upload File</button>
    </div>

    <!-- Table to display files and folders -->
    <table class="table table-bordered" id="fileTable">
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Size</th>
                <th>Last Modified</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <!-- Storage Usage Bar -->
    <div class="progress mb-3">
        <div id="storageBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    <p id="storageInfo"></p>

    <!-- Modal for creating files/folders -->
    <div class="modal" id="createModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create File/Folder</h5>
                    <button type="button" class="btn-close" onclick="hideCreateModal()"></button>
                </div>
                <div class="modal-body">
                    <form id="createForm">
                        <input type="hidden" id="createUsername" name="username" value="<?= $username ?>">
                        <input type="hidden" id="createPath" name="path">
                        <div class="mb-3">
                            <label for="createName" class="form-label">Name</label>
                            <input type="text" id="createName" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="createType" class="form-label">Type</label>
                            <select id="createType" name="type" class="form-control">
                                <option value="file">File</option>
                                <option value="folder">Folder</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="createItem()">Create</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for uploading files -->
    <div class="modal" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload File</h5>
                    <button type="button" class="btn-close" onclick="hideUploadModal()"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <input type="hidden" id="uploadUsername" name="username" value="<?= $username ?>">
                        <input type="hidden" id="uploadPath" name="path">
                        <div class="mb-3">
                            <label for="fileInput" class="form-label">Select File</label>
                            <input type="file" id="fileInput" name="file" class="form-control">
                        </div>
                        <button type="button" class="btn btn-primary" onclick="uploadFiles()">Upload</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentPath = ''; // Track the current path
        let editingFile = ''; // Track the file being edited

        // Automatically load files when the page loads
        window.onload = function () {
            loadFiles();
        };

        function loadFiles() {
            const username = document.getElementById('username').value;
            fetch(`list_files.php?username=${username}&path=${encodeURIComponent(currentPath)}`)
                .then(res => res.json())
                .then(items => {
                    const tbody = document.querySelector("#fileTable tbody");
                    tbody.innerHTML = "";

                    if (items.error) {
                        alert(items.error);
                        return;
                    }

                    // Update breadcrumb navigation
                    updateBreadcrumb();

                    items.forEach(item => {
                        const row = document.createElement("tr");
                        row.innerHTML = `
                            <td>
                                ${item.type === 'folder' ? `<i class="fas fa-folder icon-folder"></i> <a href="#" onclick="navigateTo('${item.name}')">${item.name}</a>` : `<i class="fas fa-file icon-file"></i> ${item.name}`}
                            </td>
                            <td>${item.type}</td>
                            <td>${item.size}</td>
                            <td>${item.modified}</td>
                            <td>
                                ${item.type === 'file' ? `
                                    <i class="fas fa-edit text-warning" onclick="editFile('${item.name}')"></i>
                                    <i class="fas fa-trash text-danger" onclick="deleteItem('${item.name}')"></i>
                                    <i class="fas fa-download text-primary" onclick="downloadFile('${item.name}')"></i>
                                ` : ''}
                            </td>
                        `;
                        tbody.appendChild(row);
                    });

                    updateStorage(username);
                })
                .catch(err => alert("Error loading files"));
        }

        function updateBreadcrumb() {
            const breadcrumb = document.getElementById('breadcrumb');
            breadcrumb.innerHTML = '';

            const parts = currentPath.split('/');
            let path = '';

            const rootItem = document.createElement('li');
            rootItem.className = 'breadcrumb-item';
            rootItem.innerHTML = `<a href="#" onclick="navigateToRoot()">Root</a>`;
            breadcrumb.appendChild(rootItem);

            parts.forEach((part, index) => {
                path += (index > 0 ? '/' : '') + part;
                const item = document.createElement('li');
                item.className = 'breadcrumb-item' + (index === parts.length - 1 ? ' active' : '');
                item.innerHTML = index === parts.length - 1 ? part : `<a href="#" onclick="navigateToPath('${path}')">${part}</a>`;
                breadcrumb.appendChild(item);
            });
        }

        function navigateTo(folderName) {
            if (folderName === '..') {
                const parts = currentPath.split('/');
                parts.pop();
                currentPath = parts.join('/');
            } else {
                currentPath = currentPath ? `${currentPath}/${folderName}` : folderName;
            }
            loadFiles();
        }

        function navigateToRoot() {
            currentPath = '';
            loadFiles();
        }

        function navigateToPath(path) {
            currentPath = path;
            loadFiles();
        }

        function showCreateModal() {
            const username = document.getElementById('username').value;
            document.getElementById('createUsername').value = username;
            document.getElementById('createPath').value = currentPath;
            document.getElementById('createModal').style.display = 'block';
        }

        function hideCreateModal() {
            document.getElementById('createModal').style.display = 'none';
        }

        function createItem() {
            const form = document.getElementById('createForm');
            const formData = new FormData(form);

            fetch('create_item.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(response => {
                    if (response.success) {
                        alert(response.message || "Item created successfully!");
                        hideCreateModal();
                        loadFiles();
                    } else {
                        alert(response.error || "Failed to create item.");
                    }
                })
                .catch(err => alert("Error creating item"));
        }

        function showUploadModal() {
            const username = document.getElementById('username').value;
            document.getElementById('uploadUsername').value = username;
            document.getElementById('uploadPath').value = currentPath;
            document.getElementById('uploadModal').style.display = 'block';
        }

        function hideUploadModal() {
            document.getElementById('uploadModal').style.display = 'none';
        }

        function uploadFiles() {
            const form = document.getElementById('uploadForm');
            const formData = new FormData(form);

            fetch('upload.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(response => {
                    if (response.success) {
                        alert(response.message || "Upload successful!");
                        hideUploadModal();
                        loadFiles();
                    } else {
                        alert(response.error || "Upload failed.");
                    }
                })
                .catch(err => alert("Error uploading files"));
        }

        function editFile(name) {
            const username = document.getElementById('username').value;
            editingFile = name;

            fetch(`download.php?username=${username}&file=${encodeURIComponent(currentPath + '/' + name)}`)
                .then(res => res.text())
                .then(content => {
                    document.getElementById('fileContent').value = content;
                    document.getElementById('editModal').style.display = 'block';
                });
        }

        function hideEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function saveFile() {
            const username = document.getElementById('username').value;
            const content = document.getElementById('fileContent').value;

            fetch('edit_file.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, file: `${currentPath}/${editingFile}`, content })
            })
                .then(res => res.json())
                .then(response => {
                    if (response.success) {
                        alert("File saved successfully!");
                        hideEditModal();
                        loadFiles();
                    } else {
                        alert(response.error || "Failed to save file.");
                    }
                })
                .catch(err => alert("Error saving file"));
        }

        function deleteItem(name) {
            const username = document.getElementById('username').value;
            if (!confirm(`Are you sure you want to delete this item: ${name}?`)) return;

            fetch('delete_file.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, file: `${currentPath}/${name}` })
            })
                .then(res => res.json())
                .then(response => {
                    if (response.success) {
                        alert("Deleted successfully!");
                        loadFiles();
                    } else {
                        alert(response.error || "Delete failed.");
                    }
                })
                .catch(err => alert("Error deleting item"));
        }

        function downloadFile(name) {
            const username = document.getElementById('username').value;
            window.location.href = `download.php?username=${username}&file=${encodeURIComponent(currentPath + '/' + name)}`;
        }

        function updateStorage(username) {
            fetch(`storage_usage.php?username=${username}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        document.getElementById('storageInfo').innerText = data.error;
                        return;
                    }

                    const usedPercentage = (data.used / data.total) * 100;
                    document.getElementById('storageBar').style.width = `${usedPercentage}%`;
                    document.getElementById('storageBar').setAttribute('aria-valuenow', usedPercentage);
                    document.getElementById('storageInfo').innerText = `Used: ${data.used} KB / Total: ${data.total} KB (${Math.round(usedPercentage)}%)`;
                });
        }
    </script>
</body>
</html>