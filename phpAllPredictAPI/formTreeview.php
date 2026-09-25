<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- jsTree CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.16/themes/default/style.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
        }

        .top-banner {
            background: linear-gradient(135deg, #0088cc 0%, #00a8e8 100%);
            color: white;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .top-banner .banner-text {
            font-size: 14px;
        }

        .top-banner .banner-text a {
            color: white;
            text-decoration: underline;
            font-weight: 600;
        }

        .top-banner .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
        }

        .header {
            background: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }

        .header .logo img {
            height: 40px;
        }

        .header nav {
            display: flex;
            gap: 30px;
        }

        .header nav a {
            color: #666;
            text-decoration: none;
            font-size: 15px;
            transition: color 0.2s;
        }

        .header nav a:hover {
            color: #0088cc;
        }

        .header .actions {
            display: flex;
            gap: 15px;
        }

        .header .btn {
            padding: 8px 20px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
        }

        .btn-trial {
            background: white;
            border: 2px solid #0088cc;
            color: #0088cc;
        }

        .btn-buy {
            background: #0088cc;
            color: white;
            border: none;
        }

        .main-container {
            display: flex;
            height: calc(100vh - 130px);
        }

        .sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid #e0e0e0;
            overflow-y: auto;
            padding: 20px 10px;
        }

        .sidebar .search-box {
            padding: 0 10px 15px;
            margin-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .sidebar .search-box input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .jstree-default .jstree-icon {
            color: #0088cc;
        }

        .jstree-default .jstree-clicked {
            background: #e3f2fd !important;
            border-radius: 4px;
        }

        .content-area {
            flex: 1;
            overflow-y: auto;
            padding: 30px;
            background: white;
        }

        .breadcrumb {
            background: none;
            padding: 0;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: "→";
            color: #999;
        }

        .content-area h1 {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 25px;
        }

        .form-card {
            background: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
        }

        .form-card h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }

        .form-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px 12px;
            font-size: 14px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #0088cc;
            box-shadow: 0 0 0 3px rgba(0, 136, 204, 0.1);
        }

        .image-upload-area {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
        }

        .image-upload-area:hover {
            border-color: #0088cc;
            background: #f8f9fa;
        }

        .image-upload-area i {
            font-size: 48px;
            color: #0088cc;
            margin-bottom: 15px;
        }

        .image-preview {
            display: none;
            margin-top: 15px;
            position: relative;
        }

        .image-preview img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .image-preview .remove-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-primary {
            background: #0088cc;
            border: none;
            padding: 10px 24px;
            font-weight: 500;
        }

        .btn-primary:hover {
            background: #0077b3;
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
            padding: 10px 24px;
        }

        .data-table {
            width: 100%;
            margin-top: 30px;
        }

        .data-table table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .data-table th {
            background: #f5f5f5;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }

        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
        }

        .data-table tr:hover {
            background: #f9f9f9;
        }

        .action-btn {
            padding: 5px 10px;
            font-size: 12px;
            margin-right: 5px;
        }

        .theme-toggle {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #0088cc;
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }

        .theme-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(0,0,0,0.3);
        }

        .loading {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 20px 40px;
            border-radius: 8px;
            z-index: 9999;
        }

        .loading.show {
            display: block;
        }

        body.dark-mode {
            background: #1a1a1a;
            color: #e0e0e0;
        }

        body.dark-mode .header,
        body.dark-mode .sidebar,
        body.dark-mode .content-area {
            background: #2a2a2a;
            color: #e0e0e0;
        }

        body.dark-mode .form-card {
            background: #333;
            border-color: #444;
        }

        body.dark-mode .form-control,
        body.dark-mode .form-select {
            background: #3a3a3a;
            border-color: #555;
            color: #e0e0e0;
        }

        body.dark-mode .data-table th {
            background: #333;
            color: #e0e0e0;
        }

        body.dark-mode .data-table td {
            border-color: #444;
        }

        body.dark-mode .image-upload-area {
            background: #3a3a3a;
            border-color: #555;
        }
    </style>
</head>
<body>
    <!-- Loading Indicator -->
    <div class="loading" id="loading">
        <i class="fas fa-spinner fa-spin"></i> กำลังโหลด...
    </div>

    <!-- Top Banner -->
    <div class="top-banner">
        <div class="banner-text">
            🎉 <strong>DevExtreme v25.1</strong> is now available. 
            <a href="#">Explore our newest features</a> and share your thoughts with us.
        </div>
        <button class="close-btn" onclick="this.parentElement.style.display='none'">×</button>
    </div>

    <!-- Header -->
    <div class="header">
        <div class="logo">
            <i class="fab fa-dev" style="font-size: 32px; color: #0088cc;"></i>
            <span>DevExtreme</span>
        </div>
        <nav>
            <a href="#">Demos</a>
            <a href="#">Docs</a>
            <a href="#">Releases</a>
            <a href="#">Support</a>
            <a href="#">ThemeBuilder</a>
            <a href="#">Blog</a>
        </nav>
        <div class="actions">
            <button class="btn btn-trial">
                <i class="fas fa-download"></i> Free Trial
            </button>
            <button class="btn btn-buy">
                <i class="fas fa-shopping-cart"></i> Buy
            </button>
        </div>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Sidebar with TreeView -->
        <div class="sidebar">
            <div class="search-box">
                <input type="text" id="tree-search" placeholder="Search by name..." class="form-control">
            </div>
            <div id="tree"></div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                    <li class="breadcrumb-item active">Create Item</li>
                </ol>
            </nav>

            <h1 id="formTitle">สร้างรายการใหม่</h1>

            <!-- Form Card -->
            <div class="form-card">
                <h3><i class="fas fa-file-alt"></i> ข้อมูลพื้นฐาน</h3>
                <form id="mainForm">
                    <input type="hidden" id="itemId" value="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ชื่อรายการ *</label>
                            <input type="text" class="form-control" id="itemName" placeholder="ระบุชื่อรายการ" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">หมวดหมู่</label>
                            <select class="form-select" id="category">
                                <option value="">เลือกหมวดหมู่</option>
                                <option value="overview">Overview</option>
                                <option value="features">Features</option>
                                <option value="documentation">Documentation</option>
                                <option value="examples">Examples</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">สถานะ</label>
                            <select class="form-select" id="status">
                                <option value="active">Active</option>
                                <option value="draft">Draft</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">ลำดับความสำคัญ</label>
                            <select class="form-select" id="priority">
                                <option value="high">สูง</option>
                                <option value="medium" selected>ปานกลาง</option>
                                <option value="low">ต่ำ</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">วันที่</label>
                            <input type="date" class="form-control" id="date">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">คำอธิบาย</label>
                        <textarea class="form-control" id="description" rows="4" placeholder="ระบุรายละเอียด..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tags</label>
                        <input type="text" class="form-control" id="tags" placeholder="เช่น: javascript, web, development">
                    </div>
                </form>
            </div>

            <!-- Image Upload Card -->
            <div class="form-card">
                <h3><i class="fas fa-image"></i> อัพโหลดรูปภาพ</h3>
                <div class="image-upload-area" onclick="document.getElementById('imageInput').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p class="mb-0"><strong>คลิกเพื่ือเลือกไฟล์</strong> หรือลากไฟล์มาวางที่นี่</p>
                    <small class="text-muted">รองรับไฟล์: JPG, PNG, GIF (ขนาดไม่เกิน 5MB)</small>
                </div>
                <input type="file" id="imageInput" name="image" accept="image/*" style="display: none;">
                <div class="image-preview" id="imagePreview">
                    <button class="remove-btn" onclick="removeImage()">×</button>
                    <img id="previewImg" src="" alt="Preview">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-2 mb-4">
                <button class="btn btn-primary" onclick="saveData()">
                    <i class="fas fa-save"></i> <span id="saveButtonText">บันทึก</span>
                </button>
                <button class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-redo"></i> รีเซ็ต
                </button>
            </div>

            <!-- Data Table -->
            <div class="data-table">
                <h3 class="mb-3">รายการที่บันทึก</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ชื่อรายการ</th>
                            <th>หมวดหมู่</th>
                            <th>สถานะ</th>
                            <th>วันที่</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="dataTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Theme Toggle Button -->
    <button class="theme-toggle" onclick="toggleTheme()">
        <i class="fas fa-moon"></i>
    </button>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <!-- jsTree -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.16/jstree.min.js"></script>

    <script>
        // Show/Hide Loading
        function showLoading() {
            document.getElementById('loading').classList.add('show');
        }

        function hideLoading() {
            document.getElementById('loading').classList.remove('show');
        }

        // Load TreeView Data from Database
        function loadTreeView() {
            showLoading();
            $.ajax({
                url: 'getformdata.php',
                type: 'GET',
                data: { action: 'getSubjects' },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#tree').jstree(true).settings.core.data = response.data;
                        $('#tree').jstree(true).refresh();
                    }
                    hideLoading();
                },
                error: function() {
                    console.error('Error loading tree data');
                    hideLoading();
                }
            });
        }

        // Initialize jsTree
        $(document).ready(function() {
            $('#tree').jstree({
                'core': {
                    'data': []
                },
                'plugins': ['search']
            });

            // Load initial data
            loadTreeView();
            loadTableData();

            // Tree node click event
            $('#tree').on('select_node.jstree', function(e, data) {
                if (data.node.original.item_id) {
                    editItem(data.node.original.item_id);
                }
            });

            // Search functionality
            let searchTimeout = false;
            $('#tree-search').keyup(function() {
                if (searchTimeout) clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    const v = $('#tree-search').val();
                    $('#tree').jstree(true).search(v);
                }, 250);
            });
        });

        // Image Upload
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('ไฟล์มีขนาดใหญ่เกิน 5MB');
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImg').src = e.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Drag and drop
        const uploadArea = document.querySelector('.image-upload-area');
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = '#0088cc';
            uploadArea.style.background = '#f0f8ff';
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.style.borderColor = '#ddd';
            uploadArea.style.background = 'white';
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = '#ddd';
            uploadArea.style.background = 'white';
            
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                document.getElementById('imageInput').files = e.dataTransfer.files;
                const event = new Event('change');
                document.getElementById('imageInput').dispatchEvent(event);
            }
        });

        function removeImage() {
            document.getElementById('imageInput').value = '';
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('previewImg').src = '';
        }

        // Save Data (Create or Update)
        function saveData() {
            const itemId = document.getElementById('itemId').value;
            const name = document.getElementById('itemName').value;
            const category = document.getElementById('category').value;
            const status = document.getElementById('status').value;
            const priority = document.getElementById('priority').value;
            const date = document.getElementById('date').value;
            const description = document.getElementById('description').value;
            const tags = document.getElementById('tags').value;

            if (!name) {
                alert('กรุณาระบุชื่อรายการ');
                return;
            }

            const formData = new FormData();
            formData.append('id', itemId);
            formData.append('name', name);
            formData.append('category', category);
            formData.append('status', status);
            formData.append('priority', priority);
            formData.append('date', date);
            formData.append('description', description);
            formData.append('tags', tags);

            const imageFile = document.getElementById('imageInput').files[0];
            if (imageFile) {
                formData.append('image', imageFile);
            }

            const url = itemId ? 'updateform.php' : 'saveform.php';

            showLoading();
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        alert(response.message);
                        resetForm();
                        loadTableData();
                        loadTreeView();
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoading();
                    alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
                    console.error(error);
                }
            });
        }

        // Load Table Data
        function loadTableData() {
            showLoading();
            $.ajax({
                url: 'getformdata.php',
                type: 'GET',
                data: { action: 'getAllItems' },
                dataType: 'json',
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        const tbody = document.getElementById('dataTableBody');
                        tbody.innerHTML = '';

                        response.data.forEach(item => {
                            const statusBadge = item.status === 'active' ? 'success' : 
                                              item.status === 'draft' ? 'warning' : 'secondary';
                            const statusText = item.status === 'active' ? 'Active' : 
                                             item.status === 'draft' ? 'Draft' : 'Archived';

                            const row = `
                                <tr>
                                    <td>${item.id}</td>
                                    <td>${item.name}</td>
                                    <td>${item.category || '-'}</td>
                                    <td><span class="badge bg-${statusBadge}">${statusText}</span></td>
                                    <td>${item.date || '-'}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary action-btn" onclick="editItem(${item.id})">
                                            <i class="fas fa-edit"></i> แก้ไข
                                        </button>
                                        <button class="btn btn-sm btn-danger action-btn" onclick="deleteItem(${item.id})">
                                            <i class="fas fa-trash"></i> ลบ
                                        </button>
                                    </td>
                                </tr>
                            `;
                            tbody.insertAdjacentHTML('beforeend', row);
                        });
                    }
                },
                error: function() {
                    hideLoading();
                    alert('เกิดข้อผิดพลาดในการโหลดข้อมูล');
                }
            });
        }

        // Edit Item
        function editItem(id) {
            showLoading();
            $.ajax({
                url: 'getformdata.php',
                type: 'GET',
                data: { action: 'getItem', id: id },
                dataType: 'json',
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        const item = response.data;
                        document.getElementById('formTitle').textContent = 'แก้ไขรายการ';
                        document.getElementById('saveButtonText').textContent = 'อัปเดต';
                        document.getElementById('itemId').value = item.id;
                        document.getElementById('itemName').value = item.name;
                        document.getElementById('category').value = item.category;
                        document.getElementById('status').value = item.status;
                        document.getElementById('priority').value = item.priority;
                        document.getElementById('date').value = item.date;
                        document.getElementById('description').value = item.description;
                        document.getElementById('tags').value = item.tags;

                        if (item.image_path) {
                            document.getElementById('previewImg').src = item.image_path;
                            document.getElementById('imagePreview').style.display = 'block';
                        }

                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },
                error: function() {
                    hideLoading();
                    alert('เกิดข้อผิดพลาดในการโหลดข้อมูล');
                }
            });
        }

        // Delete Item
        function deleteItem(id) {
            if (!confirm('คุณต้องการลบรายการนี้หรือไม่?')) {
                return;
            }

            showLoading();
            $.ajax({
                url: 'updateform.php',
                type: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        alert(response.message);
                        loadTableData();
                        loadTreeView();
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + response.message);
                    }
                },
                error: function() {
                    hideLoading();
                    alert('เกิดข้อผิดพลาดในการลบข้อมูล');
                }
            });
        }

        // Reset Form
        function resetForm() {
            document.getElementById('formTitle').textContent = 'สร้างรายการใหม่';
            document.getElementById('saveButtonText').textContent = 'บันทึก';
            document.getElementById('itemId').value = '';
            document.getElementById('mainForm').reset();
            removeImage();
            document.getElementById('date').valueAsDate = new Date();
        }

        // Theme Toggle
        function toggleTheme() {
            document.body.classList.toggle('dark-mode');
            const icon = document.querySelector('.theme-toggle i');
            if (document.body.classList.contains('dark-mode')) {
                icon.className = 'fas fa-sun';
            } else {
                icon.className = 'fas fa-moon';
            }
        }

        // Set today's date
        document.getElementById('date').valueAsDate = new Date();
    </script>
</body>
</html>