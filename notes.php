<?php
// Start the session at the beginning
session_start();

// Include database connection
require_once 'config.php';

// Function to fetch PDF files from database
function getPDFFiles() {
    global $conn;
    $files = [];
    
    // Query to fetch all PDFs
    $query = "SELECT pdf_id, title, description, filename, file_path, file_size, upload_date, category 
              FROM tbl_pdfs 
              ORDER BY upload_date DESC";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $files[] = $row;
        }
    }
    
    return $files;
}

// Get all PDF files from database
$pdfFiles = getPDFFiles();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Viewer with Notes</title>
    <style>
        :root {
            --primary: #5c6bc0;
            --primary-dark: #3949ab;
            --secondary: #ff7043;
            --light: #f5f5f5;
            --dark: #263238;
            --danger: #ef5350;
            --success: #66bb6a;
            --warning: #ffb74d;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: var(--primary);
            color: white;
            padding: 20px 0;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        h1, h2, h3 {
            margin-bottom: 20px;
        }
        
        .app-container {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 20px;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .app-container {
                grid-template-columns: 1fr;
            }
        }
        
        .panel {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .file-panel {
            display: flex;
            flex-direction: column;
        }
        
        .file-list {
            list-style: none;
            margin-top: 20px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .file-item:last-child {
            border-bottom: none;
        }
        
        .file-info {
            flex: 1;
        }
        
        .file-title {
            font-weight: bold;
        }
        
        .file-meta {
            font-size: 0.8rem;
            color: #666;
        }
        
        .file-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            background-color: var(--primary);
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-secondary {
            background-color: var(--secondary);
        }
        
        .btn-danger {
            background-color: var(--danger);
        }
        
        .btn-success {
            background-color: var(--success);
        }
        
        .pdf-viewer {
            height: 800px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .pdf-controls {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .notes-section {
            margin-top: 20px;
        }
        
        .notes-textarea {
            width: 100%;
            height: 300px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            font-size: 16px;
        }
        
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: white;
            background-color: var(--primary);
            display: none;
        }
        
        .alert-success {
            background-color: var(--success);
        }
        
        .alert-error {
            background-color: var(--danger);
        }
        
        .side-panel {
            position: sticky;
            top: 20px;
        }
        
        .save-notes-btn {
            margin-top: 10px;
        }
        
        .category-filter {
            margin-bottom: 15px;
        }
        
        .pdf-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>PDF Notes App</h1>
            <p>View PDFs and take notes on your readings</p>
        </div>
    </header>
    
    <div class="container">
        <div class="alert" id="alert"></div>
        
        <div class="app-container">
            <div class="side-panel">
                <div class="panel file-panel">
                    <h2>PDF Files</h2>
                    
                    <div class="category-filter">
                        <label for="categorySelect">Filter by category:</label>
                        <select id="categorySelect" onchange="filterPDFsByCategory(this.value)">
                            <option value="">All Categories</option>
                            <option value="Work">Work</option>
                            <option value="Study">Study</option>
                            <option value="Personal">Personal</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    
                    <ul class="file-list" id="fileList">
                        <?php if (count($pdfFiles) > 0): ?>
                            <?php foreach ($pdfFiles as $pdf): ?>
                                <li class="file-item" data-category="<?php echo htmlspecialchars($pdf['category']); ?>">
                                    <div class="file-info">
                                        <div class="file-title"><?php echo htmlspecialchars($pdf['title']); ?></div>
                                        <div class="file-meta">
                                            Category: <?php echo htmlspecialchars($pdf['category'] ?? 'Uncategorized'); ?> | 
                                            Size: <?php echo round($pdf['file_size'] / 1024, 2); ?> KB | 
                                            Date: <?php echo date('Y-m-d', strtotime($pdf['upload_date'])); ?>
                                        </div>
                                    </div>
                                    <div class="file-actions">
                                        <button class="btn btn-secondary view-btn" 
                                                data-id="<?php echo $pdf['pdf_id']; ?>" 
                                                data-path="<?php echo htmlspecialchars($pdf['file_path']); ?>" 
                                                data-name="<?php echo htmlspecialchars($pdf['title']); ?>">
                                            View
                                        </button>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>No PDF files found in the database.</li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="panel notes-section">
                    <h2>Notes</h2>
                    <textarea class="notes-textarea" id="notes" placeholder="Take notes while you read..."></textarea>
                    <div style="margin-top: 10px; display: flex; gap: 10px;">
                        <button id="saveNotesBtn" class="btn save-notes-btn">Save Notes</button>
                        <button id="exportNotesBtn" class="btn btn-secondary save-notes-btn">Export Notes</button>
                    </div>
                </div>
            </div>
            
            <div class="main-content">
                <div class="panel">
                    <h2>PDF Viewer</h2>
                    <div id="pdfInfo" class="pdf-info" style="display: none;">
                        <h3 id="pdfTitle"></h3>
                        <div class="pdf-controls">
                            <a href="#" class="btn btn-secondary" id="downloadPdfBtn">
                                Download PDF
                            </a>
                        </div>
                    </div>
                    
                    <div id="pdfViewer" class="pdf-viewer">
                        <div style="text-align: center; padding-top: 300px; color: #888;">
                            Select a PDF from the list to view it here.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // DOM Elements
        const fileList = document.getElementById('fileList');
        const pdfViewer = document.getElementById('pdfViewer');
        const pdfInfo = document.getElementById('pdfInfo');
        const pdfTitle = document.getElementById('pdfTitle');
        const downloadPdfBtn = document.getElementById('downloadPdfBtn');
        const notesTextarea = document.getElementById('notes');
        const saveNotesBtn = document.getElementById('saveNotesBtn');
        const exportNotesBtn = document.getElementById('exportNotesBtn');
        const alert = document.getElementById('alert');
        
        // PDF viewer variables
        let pdfUrl = '';
        let currentPdfId = '';
        let currentPdfName = '';
        let notesData = {};
        
        // Initialize the app
        function init() {
            setupEventListeners();
            loadNotes();
        }
        
        // Set up event listeners
        function setupEventListeners() {
            // Notes
            saveNotesBtn.addEventListener('click', saveNotes);
            exportNotesBtn.addEventListener('click', exportNotes);
            
            // Add event listeners to PDF view buttons
            document.querySelectorAll('.view-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const pdfId = btn.getAttribute('data-id');
                    const pdfPath = btn.getAttribute('data-path');
                    const pdfName = btn.getAttribute('data-name');
                    loadPdf(pdfId, pdfPath, pdfName);
                });
            });
        }
        
        // Filter PDFs by category
        function filterPDFsByCategory(category) {
            const items = document.querySelectorAll('#fileList .file-item');
            
            items.forEach(item => {
                if (!category || item.getAttribute('data-category') === category) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }
        
        // Load PDF using iframe
        function loadPdf(pdfId, pdfPath, pdfName) {
            currentPdfId = pdfId;
            currentPdfName = pdfName;
            pdfUrl = pdfPath;
            
            // Update PDF Info
            pdfTitle.textContent = pdfName;
            pdfInfo.style.display = 'block';
            downloadPdfBtn.href = pdfPath;
            downloadPdfBtn.setAttribute('download', pdfName + '.pdf');
            
            // Create iframe for PDF viewing
            pdfViewer.innerHTML = `<iframe src="${pdfPath}" class="pdf-viewer" style="height: 100%; width: 100%; border: none;"></iframe>`;
            
            // Load notes for this PDF
            loadNotesForCurrentPdf();
            
            showAlert(`Loaded ${pdfName}`, 'success');
        }
        
        // Load notes from local storage
        function loadNotes() {
            const savedNotes = localStorage.getItem('pdfNotes');
            if (savedNotes) {
                notesData = JSON.parse(savedNotes);
            }
        }
        
        // Load notes for current PDF
        function loadNotesForCurrentPdf() {
            if (currentPdfId && notesData[currentPdfId]) {
                notesTextarea.value = notesData[currentPdfId];
            } else {
                notesTextarea.value = '';
            }
        }
        
        // Save notes to local storage
        function saveNotes() {
            if (currentPdfId) {
                notesData[currentPdfId] = notesTextarea.value;
                localStorage.setItem('pdfNotes', JSON.stringify(notesData));
                showAlert('Notes saved successfully', 'success');
            } else {
                showAlert('No PDF opened to save notes for', 'error');
            }
        }
        
        // Export notes functionality
        function exportNotes() {
            if (!currentPdfId || !notesData[currentPdfId]) {
                showAlert('No notes to export', 'error');
                return;
            }
            
            const notes = notesData[currentPdfId];
            const blob = new Blob([notes], {type: 'text/plain'});
            const url = URL.createObjectURL(blob);
            
            const a = document.createElement('a');
            a.href = url;
            a.download = `notes_for_${currentPdfName.replace(/\s+/g, '_')}.txt`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            
            showAlert('Notes exported successfully', 'success');
        }
        
        // Show alert messages
        function showAlert(message, type) {
            alert.textContent = message;
            alert.className = `alert ${type === 'success' ? 'alert-success' : 'alert-error'}`;
            alert.style.display = 'block';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 3000);
        }
        
        // Initialize the application
        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>