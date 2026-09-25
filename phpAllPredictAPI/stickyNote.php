<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sticky Notes Board</title>
    <script src="https://cdn.ckeditor.com/4.20.1/standard/ckeditor.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 36px;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .add-note-btn {
            background: #fff;
            color: #667eea;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }
        
        .add-note-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        
        .whiteboard {
            background: white;
            min-height: 600px;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            align-content: start;
        }
        
        .sticky-note {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            min-height: 200px;
            display: flex;
            flex-direction: column;
        }
        
        .sticky-note:hover {
            transform: translateY(-5px) rotate(1deg);
            box-shadow: 0 8px 25px rgba(0,0,0,0.25);
        }
        
        .sticky-note.color-yellow {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        }
        
        .sticky-note.color-pink {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
        }
        
        .sticky-note.color-blue {
            background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);
        }
        
        .sticky-note.color-green {
            background: linear-gradient(135deg, #d4fc79 0%, #96e6a1 100%);
        }
        
        .sticky-note.color-purple {
            background: linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%);
        }
        
        .note-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #333;
            word-wrap: break-word;
        }
        
        .note-subjects {
            font-size: 13px;
            color: #666;
            margin-bottom: 10px;
            flex: 1;
        }
        
        .note-subject-item {
            padding: 4px 0;
            border-left: 3px solid rgba(0,0,0,0.2);
            padding-left: 8px;
            margin-bottom: 5px;
        }
        
        .note-footer {
            font-size: 11px;
            color: #888;
            margin-top: auto;
            padding-top: 10px;
            border-top: 1px solid rgba(0,0,0,0.1);
        }
        
        .note-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 5px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .sticky-note:hover .note-actions {
            opacity: 1;
        }
        
        .action-btn {
            background: rgba(255,255,255,0.9);
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .action-btn:hover {
            background: white;
            transform: scale(1.1);
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 15px;
            width: 100%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 30px;
            box-shadow: 0 10px 50px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-header h2 {
            color: #667eea;
            font-size: 24px;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #999;
            transition: color 0.3s;
        }
        
        .close-btn:hover {
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        
        .form-group input[type="text"]:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .subjects-container {
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .subject-item {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            position: relative;
        }
        
        .subject-item .remove-subject {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff4757;
            color: white;
            border: none;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .add-subject-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .add-subject-btn:hover {
            background: #5568d3;
        }
        
        .color-picker {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .color-option {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .color-option:hover {
            transform: scale(1.1);
        }
        
        .color-option.selected {
            border-color: #333;
            transform: scale(1.15);
        }
        
        .color-option.yellow {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        }
        
        .color-option.pink {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
        }
        
        .color-option.blue {
            background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);
        }
        
        .color-option.green {
            background: linear-gradient(135deg, #d4fc79 0%, #96e6a1 100%);
        }
        
        .color-option.purple {
            background: linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%);
        }
        
        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .btn-danger {
            background: #ff4757;
            color: white;
        }
        
        .btn-danger:hover {
            background: #ee5a6f;
        }
        
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #d0d0d0;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📌 Sticky Notes Board</h1>
        <button class="add-note-btn" onclick="openModal()">+ เพิ่มโน้ตใหม่</button>
    </div>
    
    <div class="whiteboard" id="whiteboard">
        <div class="empty-state">
            <div class="empty-state-icon">📝</div>
            <h3>ยังไม่มีโน้ต</h3>
            <p>คลิกปุ่ม "เพิ่มโน้ตใหม่" เพื่อเริ่มต้น</p>
        </div>
    </div>
    
    <!-- Modal -->
    <div class="modal" id="noteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">สร้างโน้ตใหม่</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            
            <form id="noteForm">
                <input type="hidden" id="noteId">
                
                <div class="form-group">
                    <label>หัวข้อหลัก *</label>
                    <input type="text" id="noteTitle" required placeholder="ระบุหัวข้อหลัก">
                </div>
                
                <div class="form-group">
                    <label>สีโน้ต</label>
                    <div class="color-picker">
                        <div class="color-option yellow selected" data-color="yellow" onclick="selectColor('yellow')"></div>
                        <div class="color-option pink" data-color="pink" onclick="selectColor('pink')"></div>
                        <div class="color-option blue" data-color="blue" onclick="selectColor('blue')"></div>
                        <div class="color-option green" data-color="green" onclick="selectColor('green')"></div>
                        <div class="color-option purple" data-color="purple" onclick="selectColor('purple')"></div>
                    </div>
                    <input type="hidden" id="noteColor" value="yellow">
                </div>
                
                <div class="form-group">
                    <label>หัวข้อย่อย</label>
                    <div class="subjects-container" id="subjectsContainer">
                        <!-- Subject items will be added here -->
                    </div>
                    <button type="button" class="add-subject-btn" onclick="addSubject()">+ เพิ่มหัวข้อย่อย</button>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">ยกเลิก</button>
                    <button type="button" class="btn btn-danger" id="deleteBtn" onclick="deleteNote()" style="display: none;">ลบโน้ต</button>
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        let currentNoteId = null;
        let selectedColor = 'yellow';
        let subjectCount = 0;
        let editors = {};
        
        // Load notes on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadNotes();
        });
        
        // Form submit
        document.getElementById('noteForm').addEventListener('submit', function(e) {
            e.preventDefault();
            saveNote();
        });
        
        function openModal(noteId = null) {
            currentNoteId = noteId;
            const modal = document.getElementById('noteModal');
            const modalTitle = document.getElementById('modalTitle');
            const deleteBtn = document.getElementById('deleteBtn');
            
            if (noteId) {
                modalTitle.textContent = 'แก้ไขโน้ต';
                deleteBtn.style.display = 'block';
                loadNoteData(noteId);
            } else {
                modalTitle.textContent = 'สร้างโน้ตใหม่';
                deleteBtn.style.display = 'none';
                document.getElementById('noteForm').reset();
                document.getElementById('noteId').value = '';
                document.getElementById('subjectsContainer').innerHTML = '';
                selectColor('yellow');
                subjectCount = 0;
                editors = {};
            }
            
            modal.classList.add('active');
        }
        
        function closeModal() {
            const modal = document.getElementById('noteModal');
            modal.classList.remove('active');
            
            // Destroy all CKEditor instances
            for (let key in editors) {
                if (editors[key]) {
                    editors[key].destroy();
                }
            }
            editors = {};
        }
        
        function selectColor(color) {
            selectedColor = color;
            document.getElementById('noteColor').value = color;
            
            document.querySelectorAll('.color-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            document.querySelector(`.color-option.${color}`).classList.add('selected');
        }
        
        function addSubject() {
            subjectCount++;
            const container = document.getElementById('subjectsContainer');
            const subjectId = 'subject_' + subjectCount;
            
            const subjectHtml = `
                <div class="subject-item" data-subject-id="${subjectId}">
                    <button type="button" class="remove-subject" onclick="removeSubject('${subjectId}')">×</button>
                    
                    <div class="form-group">
                        <label>ชื่อหัวข้อย่อย</label>
                        <input type="text" class="subject-name" placeholder="ชื่อหัวข้อย่อย">
                    </div>
                    
                    <div class="form-group">
                        <label>เวอร์ชัน</label>
                        <input type="text" class="subject-version" placeholder="v1.0" value="v1.0">
                    </div>
                    
                    <div class="form-group">
                        <label>รายละเอียด (รองรับ paste รูปภาพ)</label>
                        <textarea class="subject-detail" id="${subjectId}_detail"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Code</label>
                        <textarea class="subject-code" placeholder="วาง code ที่นี่..." rows="5"></textarea>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', subjectHtml);
            
            // Initialize CKEditor for the new subject
            CKEDITOR.replace(subjectId + '_detail', {
                height: 200,
                // Upload configuration
                filebrowserUploadUrl: 'https://thepapers.in/phpAllPredictAPI/upload.php',
                filebrowserUploadMethod: 'form',
                uploadUrl: 'https://thepapers.in/phpAllPredictAPI/upload.php',

				//https://thepapers.in/phpAllPredictAPI/
				//https://thepapers.in/phpAllPredictAPI/
                
                // Image upload adapter
                extraPlugins: 'uploadimage',
                
                // Allow pasting images from clipboard
                clipboard_handleImages: true,
                
                // Image2 plugin configuration
                image2_alignClasses: ['image-align-left', 'image-align-center', 'image-align-right'],
                image2_captionedClass: 'image-captioned',
                
                // Remove Word formatting
                pasteFromWordRemoveStyles: false,
                pasteFromWordRemoveFontStyles: false,
                
                // Allow all content
                allowedContent: true,
                
                // Toolbar configuration
                toolbar: [
                    { name: 'document', items: ['Source'] },
                    { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
                    { name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll'] },
                    '/',
                    { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat'] },
                    { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote'] },
                    { name: 'links', items: ['Link', 'Unlink'] },
                    { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar'] },
                    '/',
                    { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
                    { name: 'colors', items: ['TextColor', 'BGColor'] },
                    { name: 'tools', items: ['Maximize'] }
                ],
                
                // File upload configuration
                fileTools_requestHeaders: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            // Set up paste event handler for images
            CKEDITOR.instances[subjectId + '_detail'].on('paste', function(evt) {
                // Check if clipboard contains files
                if (evt.data.dataTransfer && evt.data.dataTransfer.getFilesCount()) {
                    var files = evt.data.dataTransfer.getFilesCount();
                    console.log('Pasting ' + files + ' file(s)');
                }
            });
            
            editors[subjectId] = CKEDITOR.instances[subjectId + '_detail'];
        }
        
        function removeSubject(subjectId) {
            const element = document.querySelector(`[data-subject-id="${subjectId}"]`);
            if (element) {
                // Destroy CKEditor instance
                if (editors[subjectId]) {
                    editors[subjectId].destroy();
                    delete editors[subjectId];
                }
                element.remove();
            }
        }
        
        async function saveNote() {
            const noteId = document.getElementById('noteId').value;
            const title = document.getElementById('noteTitle').value;
            const color = document.getElementById('noteColor').value;
            
            // Collect subjects
            const subjects = [];
            document.querySelectorAll('.subject-item').forEach(item => {
                const subjectId = item.getAttribute('data-subject-id');
                const name = item.querySelector('.subject-name').value;
                const version = item.querySelector('.subject-version').value;
                const detail = editors[subjectId] ? editors[subjectId].getData() : '';
                const code = item.querySelector('.subject-code').value;
                
                if (name) {
                    subjects.push({ name, version, detail, code });
                }
            });
            
            const data = {
                action: noteId ? 'update' : 'insert',
                id: noteId,
                title: title,
                color: color,
                subjects: JSON.stringify(subjects)
            };
            
            try {
                const response = await fetch('https://thepapers.in/phpAllPredictAPI/managestickNote.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('บันทึกสำเร็จ!');
                    closeModal();
                    loadNotes();
                } else {
                    alert('เกิดข้อผิดพลาด: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการบันทึก');
            }
        }
        
        async function loadNotes() {
            try {
                const response = await fetch('https://thepapers.in/phpAllPredictAPI/managestickNote.php?action=list');
                const result = await response.json();
                
                if (result.success) {
                    displayNotes(result.data);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
        
        function displayNotes(notes) {
            const whiteboard = document.getElementById('whiteboard');
            
            if (notes.length === 0) {
                whiteboard.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">📝</div>
                        <h3>ยังไม่มีโน้ต</h3>
                        <p>คลิกปุ่ม "เพิ่มโน้ตใหม่" เพื่อเริ่มต้น</p>
                    </div>
                `;
                return;
            }
            
            whiteboard.innerHTML = '';
            
            notes.forEach(note => {
                const subjects = note.subjects ? JSON.parse(note.subjects) : [];
                const subjectsHtml = subjects.map(s => `
                    <div class="note-subject-item">
                        📑 ${s.name} <small>(${s.version})</small>
                    </div>
                `).join('');
                
                const noteHtml = `
                    <div class="sticky-note color-${note.color}" onclick="openModal(${note.id})">
                        <div class="note-actions" onclick="event.stopPropagation()">
                            <button class="action-btn" onclick="openModal(${note.id})" title="แก้ไข">✏️</button>
                            <button class="action-btn" onclick="confirmDelete(${note.id})" title="ลบ">🗑️</button>
                        </div>
                        <div class="note-title">${note.title}</div>
                        <div class="note-subjects">${subjectsHtml || '<em>ไม่มีหัวข้อย่อย</em>'}</div>
                        <div class="note-footer">
                            <div>สร้าง: ${new Date(note.create_date).toLocaleDateString('th-TH')}</div>
                            <div>แก้ไข: ${new Date(note.last_update).toLocaleDateString('th-TH')}</div>
                        </div>
                    </div>
                `;
                
                whiteboard.insertAdjacentHTML('beforeend', noteHtml);
            });
        }
        
        async function loadNoteData(noteId) {
            try {
                const response = await fetch(`https://thepapers.in/phpAllPredictAPI/managestickNote.php?action=get&id=${noteId}`);
                const result = await response.json();
                
                if (result.success) {
                    const note = result.data;
                    document.getElementById('noteId').value = note.id;
                    document.getElementById('noteTitle').value = note.title;
                    selectColor(note.color);
                    
                    // Clear and add subjects
                    document.getElementById('subjectsContainer').innerHTML = '';
                    const subjects = note.subjects ? JSON.parse(note.subjects) : [];
                    
                    subjects.forEach(subject => {
                        addSubject();
                        const lastSubject = document.querySelector('.subject-item:last-child');
                        lastSubject.querySelector('.subject-name').value = subject.name;
                        lastSubject.querySelector('.subject-version').value = subject.version;
                        lastSubject.querySelector('.subject-code').value = subject.code || '';
                        
                        const subjectId = lastSubject.getAttribute('data-subject-id');
                        if (editors[subjectId]) {
                            editors[subjectId].setData(subject.detail || '');
                        }
                    });
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
        
        function confirmDelete(noteId) {
            if (confirm('คุณแน่ใจหรือไม่ที่จะลบโน้ตนี้?')) {
                deleteNote(noteId);
            }
        }
        
        async function deleteNote(noteId = null) {
            const id = noteId || document.getElementById('noteId').value;
            
            if (!id) return;
            
            if (!noteId && !confirm('คุณแน่ใจหรือไม่ที่จะลบโน้ตนี้?')) {
                return;
            }
            
            try {
                const response = await fetch('https://thepapers.in/phpAllPredictAPI/managestickNote.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'delete',
                        id: id
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('ลบสำเร็จ!');
                    closeModal();
                    loadNotes();
                } else {
                    alert('เกิดข้อผิดพลาด: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการลบ');
            }
        }
    </script>
</body>
</html>