<?php
// Start the session at the beginning
session_start();

// Include database connection
require_once 'config.php';

// Function to fetch PDF files from database
function getPDFFiles() {
    global $conn;
    $files = [];
    
    // Modified query to fetch all PDFs (removed is_public filter)
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
    <title>PDF Viewer with Notes and Mnemonics</title>
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
        
        .btn-warning {
            background-color: var(--warning);
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
            height: 200px;
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
        
        /* Mnemonics Styles */
        .mnemonics-section {
            margin-top: 20px;
        }
        
        .mnemonic-techniques {
            margin-top: 10px;
        }
        
        .mnemonic-item {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
        }
        
        .mnemonic-title {
            font-weight: bold;
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .mnemonic-content {
            border-top: 1px solid #eee;
            padding-top: 10px;
            margin-top: 5px;
        }
        
        .mnemonic-actions {
            display: flex;
            gap: 5px;
            margin-top: 10px;
        }
        
        .mnemonic-type-selector {
            margin-bottom: 15px;
        }
        
        .mnemonic-form {
            margin-top: 15px;
            padding: 15px;
            background-color: #f0f4ff;
            border-radius: 4px;
        }
        
        .form-group {
            margin-bottom: 10px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }
        
        .memory-palace-container {
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            background-color: #f5f5f5;
        }
        
        .location-item {
            padding: 8px;
            margin-bottom: 5px;
            background-color: white;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .location-item-header {
            display: flex;
            justify-content: space-between;
            font-weight: 500;
        }
        
        .location-item-content {
            margin-top: 5px;
            font-size: 0.9rem;
            color: #555;
        }
        
        .mnemonic-quiz {
            margin-top: 15px;
            padding: 15px;
            background-color: #f7f9fc;
            border-radius: 4px;
            border: 1px solid #dde;
        }
        
        .quiz-item {
            margin-bottom: 10px;
            padding: 10px;
            background-color: white;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .quiz-answer {
            margin-top: 10px;
            padding: 10px;
            border-top: 1px solid #eee;
            display: none;
        }
        
        .show-answer-btn {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
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
        
        .ai-mnemonic-modal-bg {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.4);
            z-index: 9999;
        }
        .ai-mnemonic-modal {
            background: #fff;
            max-width: 600px;
            margin: 60px auto;
            padding: 30px;
            border-radius: 8px;
            position: relative;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Enhanced Mnemonics with Database PDFs</h1>
            <p>View database PDFs, take notes, and improve memory with mnemonics techniques</p>
        </div>
    </header>
    
    <div class="container">
        <div class="alert" id="alert"></div>
        
        <div class="app-container">
            <div class="side-panel">
                <div class="panel file-panel">
                    <h2>PDF Files from Database</h2>
                    
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
                
                <div class="panel mnemonics-section">
                    <h2>Mnemonics Techniques</h2>
                    
                    <!-- AI Mnemonic Generator Button -->
                    <button id="openAIMnemonicModal" class="btn btn-primary" style="width:100%;margin-bottom:10px;">AI Mnemonic Generator</button>
                    
                    <div class="mnemonic-type-selector">
                        <label for="mnemonicType">Select technique:</label>
                        <select id="mnemonicType" onchange="changeMnemonicType(this.value)">
                            <option value="acronym">Acronym / Acrostic</option>
                            <option value="loci">Memory Palace (Method of Loci)</option>
                            <option value="peg">Peg System</option>
                            <option value="chunking">Chunking</option>
                            <option value="association">Association</option>
                        </select>
                    </div>
                    
                    <div class="mnemonic-form" id="acronymForm">
                        <h3>Create Acronym / Acrostic</h3>
                        <div class="form-group">
                            <label for="acronymTitle">Title:</label>
                            <input type="text" id="acronymTitle" placeholder="E.g., ROYGBIV for rainbow colors">
                        </div>
                        <div class="form-group">
                            <label for="acronymWord">Acronym/Acrostic Word:</label>
                            <input type="text" id="acronymWord" placeholder="E.g., ROYGBIV">
                        </div>
                        <div class="form-group">
                            <label for="acronymExplanation">What it helps remember:</label>
                            <textarea id="acronymExplanation" placeholder="Red, Orange, Yellow, Green, Blue, Indigo, Violet"></textarea>
                        </div>
                        <button id="saveAcronymBtn" class="btn btn-success">Save Acronym</button>
                    </div>
                    
                    <div class="mnemonic-form" id="lociForm" style="display: none;">
                        <h3>Create Memory Palace</h3>
                        <div class="form-group">
                            <label for="palaceTitle">Palace Name:</label>
                            <input type="text" id="palaceTitle" placeholder="E.g., My House">
                        </div>
                        <div id="locationsContainer" class="memory-palace-container">
                            <h4>Locations</h4>
                            <div id="locationsList"></div>
                            <div class="form-group">
                                <label for="locationName">Location:</label>
                                <input type="text" id="locationName" placeholder="E.g., Front Door">
                            </div>
                            <div class="form-group">
                                <label for="locationItem">Item to remember:</label>
                                <input type="text" id="locationItem" placeholder="E.g., First point of my presentation">
                            </div>
                            <button id="addLocationBtn" class="btn btn-secondary">Add Location</button>
                        </div>
                        <button id="savePalaceBtn" class="btn btn-success">Save Memory Palace</button>
                    </div>
                    
                    <div class="mnemonic-form" id="pegForm" style="display: none;">
                        <h3>Create Peg System</h3>
                        <div class="form-group">
                            <label for="pegTitle">Peg System Title:</label>
                            <input type="text" id="pegTitle" placeholder="E.g., Numbers 1-10">
                        </div>
                        <div class="form-group">
                            <label for="pegSystem">Peg Words (one per line):</label>
                            <textarea id="pegSystem" placeholder="1 - Sun (looks like 1)\n2 - Shoe (looks like 2)\n3 - Tree (sounds like 3)"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="pegItems">Items to Remember (one per line):</label>
                            <textarea id="pegItems" placeholder="1 - First topic\n2 - Second topic\n3 - Third topic"></textarea>
                        </div>
                        <button id="savePegBtn" class="btn btn-success">Save Peg System</button>
                    </div>
                    
                    <div class="mnemonic-form" id="chunkingForm" style="display: none;">
                        <h3>Create Chunking</h3>
                        <div class="form-group">
                            <label for="chunkingTitle">Chunking Title:</label>
                            <input type="text" id="chunkingTitle" placeholder="E.g., Phone Number">
                        </div>
                        <div class="form-group">
                            <label for="originalInfo">Original Information:</label>
                            <input type="text" id="originalInfo" placeholder="E.g., 8005551234">
                        </div>
                        <div class="form-group">
                            <label for="chunkedInfo">Chunked Information:</label>
                            <input type="text" id="chunkedInfo" placeholder="E.g., 800-555-1234">
                        </div>
                        <div class="form-group">
                            <label for="chunkingExplanation">How it helps:</label>
                            <textarea id="chunkingExplanation" placeholder="Breaking the phone number into area code, prefix, and line number makes it easier to remember."></textarea>
                        </div>
                        <button id="saveChunkingBtn" class="btn btn-success">Save Chunking</button>
                    </div>
                    
                    <div class="mnemonic-form" id="associationForm" style="display: none;">
                        <h3>Create Association</h3>
                        <div class="form-group">
                            <label for="associationTitle">Association Title:</label>
                            <input type="text" id="associationTitle" placeholder="E.g., Remembering a Person's Name">
                        </div>
                        <div class="form-group">
                            <label for="itemToRemember">Item to Remember:</label>
                            <input type="text" id="itemToRemember" placeholder="E.g., John Smith">
                        </div>
                        <div class="form-group">
                            <label for="associatedWith">Associated With:</label>
                            <input type="text" id="associatedWith" placeholder="E.g., Blacksmith (Smith) wearing a John Deere hat">
                        </div>
                        <div class="form-group">
                            <label for="associationExplanation">How it helps:</label>
                            <textarea id="associationExplanation" placeholder="Visualizing a blacksmith wearing a John Deere hat creates a vivid image that connects to the name John Smith."></textarea>
                        </div>
                        <button id="saveAssociationBtn" class="btn btn-success">Save Association</button>
                    </div>
                    
                    <h3>Saved Mnemonics</h3>
                    <div class="mnemonic-techniques" id="savedMnemonics">
                        <!-- Saved mnemonics will be displayed here -->
                    </div>
                    
                    <div class="mnemonic-quiz" id="quizSection" style="display: none;">
                        <h3>Practice Your Mnemonics</h3>
                        <button id="startQuizBtn" class="btn btn-secondary">Start Quiz</button>
                        <div id="quizContent" style="display: none;"></div>
                    </div>
                </div>
                
                <div class="panel notes-section">
                    <h2>Notes</h2>
                    <textarea class="notes-textarea" id="notes" placeholder="Take notes while you read..."></textarea>
                    <button id="saveNotesBtn" class="btn save-notes-btn">Save Notes</button>
                    <button id="exportNotesBtn" class="btn btn-secondary save-notes-btn">Export Notes</button>
                </div>
            </div>
            
            <div class="main-content">
                <div class="panel">
                    <h2>PDF Viewer</h2>
                    <div id="pdfInfo" class="pdf-info" style="display: none;">
                        <h3 id="pdfTitle"></h3>
                        <div class="pdf-controls">
                            <a href="#" class="btn btn-secondary" id="downloadPdfBtn">
                                <i class="bi bi-download"></i> Download PDF
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
    
    <!-- AI Mnemonic Modal -->
    <div class="ai-mnemonic-modal-bg" id="aiMnemonicModalBg">
        <div class="ai-mnemonic-modal">
            <h2>AI Mnemonic Generator</h2>
            <p>This will use the current PDF to generate mnemonics using Gemini AI.</p>
            <div id="aiMnemonicLoading" style="display:none;">Generating mnemonics...</div>
            <div id="aiMnemonicResult"></div>
            <input type="text" id="geminiApiKeyInput" placeholder="Enter Gemini API Key" style="width:100%;margin-bottom:10px;">
            <button id="generateAIMnemonicBtn" class="btn btn-success">Generate Mnemonics</button>
            <button id="saveAIMnemonicBtn" class="btn btn-primary" style="display:none;">Save to Mnemonics</button>
            <button id="cancelAIMnemonicBtn" class="btn btn-danger">Cancel</button>
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
        
        // Mnemonic Elements
        const mnemonicTypeSelect = document.getElementById('mnemonicType');
        const acronymForm = document.getElementById('acronymForm');
        const lociForm = document.getElementById('lociForm');
        const pegForm = document.getElementById('pegForm');
        const chunkingForm = document.getElementById('chunkingForm');
        const associationForm = document.getElementById('associationForm');
        const savedMnemonics = document.getElementById('savedMnemonics');
        const quizSection = document.getElementById('quizSection');
        const startQuizBtn = document.getElementById('startQuizBtn');
        const quizContent = document.getElementById('quizContent');
        
        // Acronym Form Elements
        const acronymTitle = document.getElementById('acronymTitle');
        const acronymWord = document.getElementById('acronymWord');
        const acronymExplanation = document.getElementById('acronymExplanation');
        const saveAcronymBtn = document.getElementById('saveAcronymBtn');
        
        // Memory Palace Form Elements
        const palaceTitle = document.getElementById('palaceTitle');
        const locationsList = document.getElementById('locationsList');
        const locationName = document.getElementById('locationName');
        const locationItem = document.getElementById('locationItem');
        const addLocationBtn = document.getElementById('addLocationBtn');
        const savePalaceBtn = document.getElementById('savePalaceBtn');
        
        // Peg System Form Elements
        const pegTitle = document.getElementById('pegTitle');
        const pegSystem = document.getElementById('pegSystem');
        const pegItems = document.getElementById('pegItems');
        const savePegBtn = document.getElementById('savePegBtn');
        
        // Chunking Form Elements
        const chunkingTitle = document.getElementById('chunkingTitle');
        const originalInfo = document.getElementById('originalInfo');
        const chunkedInfo = document.getElementById('chunkedInfo');
        const chunkingExplanation = document.getElementById('chunkingExplanation');
        const saveChunkingBtn = document.getElementById('saveChunkingBtn');
        
        // Association Form Elements
        const associationTitle = document.getElementById('associationTitle');
        const itemToRemember = document.getElementById('itemToRemember');
        const associatedWith = document.getElementById('associatedWith');
        const associationExplanation = document.getElementById('associationExplanation');
        const saveAssociationBtn = document.getElementById('saveAssociationBtn');
        
        // PDF viewer variables
        let pdfUrl = '';
        let currentPdfId = '';
        let currentPdfName = '';
        let notesData = {};
        
        // Mnemonics variables
        let mnemonicsData = {}; // Will store all mnemonics by PDF ID
        let currentMnemonicType = 'acronym';
        let memoryPalaceLocations = []; // For storing memory palace locations temporarily
        
        // Initialize the app
        function init() {
            setupEventListeners();
            loadNotes();
            loadMnemonics();
            displaySavedMnemonics();
        }
        
        // Set up event listeners
        function setupEventListeners() {
            // Notes
            saveNotesBtn.addEventListener('click', saveNotes);
            if (exportNotesBtn) {
                exportNotesBtn.addEventListener('click', exportNotes);
            }
            
            // Mnemonics type selector
            mnemonicTypeSelect.addEventListener('change', function() {
                changeMnemonicType(this.value);
            });
            
            // Acronym form
            saveAcronymBtn.addEventListener('click', saveAcronym);
            
            // Memory Palace form
            addLocationBtn.addEventListener('click', addLocation);
            savePalaceBtn.addEventListener('click', saveMemoryPalace);
            
            // Peg System form
            savePegBtn.addEventListener('click', savePegSystem);
            
            // Chunking form
            saveChunkingBtn.addEventListener('click', saveChunking);
            
            // Association form
            saveAssociationBtn.addEventListener('click', saveAssociation);
            
            // Quiz
            startQuizBtn.addEventListener('click', startQuiz);
            
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
            
            // Display saved mnemonics for this PDF
            displaySavedMnemonics();
            
            // Show quiz section if there are mnemonics for this PDF
            updateQuizSectionVisibility();
            
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
        
        // Load mnemonics from local storage
        function loadMnemonics() {
            const savedMnemonics = localStorage.getItem('pdfMnemonics');
            if (savedMnemonics) {
                mnemonicsData = JSON.parse(savedMnemonics);
            }
        }
        
        // Save mnemonics to local storage
        function saveMnemonics() {
            localStorage.setItem('pdfMnemonics', JSON.stringify(mnemonicsData));
        }
        
        // Change mnemonic type
    function changeMnemonicType(type) {
        currentMnemonicType = type;
        
        // Hide all forms
        acronymForm.style.display = 'none';
        lociForm.style.display = 'none';
        pegForm.style.display = 'none';
        chunkingForm.style.display = 'none';
        associationForm.style.display = 'none';
        
        // Show the selected form
        if (type === 'acronym') {
            acronymForm.style.display = 'block';
        } else if (type === 'loci') {
            lociForm.style.display = 'block';
        } else if (type === 'peg') {
            pegForm.style.display = 'block';
        } else if (type === 'chunking') {
            chunkingForm.style.display = 'block';
        } else if (type === 'association') {
            associationForm.style.display = 'block';
        }
    }
    
    // Save acronym mnemonic
    function saveAcronym() {
        if (!currentPdfId) {
            showAlert('Please open a PDF first', 'error');
            return;
        }
        
        if (!acronymTitle.value || !acronymWord.value || !acronymExplanation.value) {
            showAlert('Please fill in all fields', 'error');
            return;
        }
        
        // Initialize PDF's mnemonics if not exist
        if (!mnemonicsData[currentPdfId]) {
            mnemonicsData[currentPdfId] = [];
        }
        
        // Add new acronym
        mnemonicsData[currentPdfId].push({
            type: 'acronym',
            title: acronymTitle.value,
            acronym: acronymWord.value,
            explanation: acronymExplanation.value,
            id: Date.now() // Use timestamp as unique ID
        });
        
        // Save to local storage
        saveMnemonics();
        
        // Clear form
        acronymTitle.value = '';
        acronymWord.value = '';
        acronymExplanation.value = '';
        
        // Update display
        displaySavedMnemonics();
        updateQuizSectionVisibility();
        
        showAlert('Acronym mnemonic saved', 'success');
    }
    
    // Add location to memory palace
    function addLocation() {
        if (!locationName.value || !locationItem.value) {
            showAlert('Please fill in both location and item fields', 'error');
            return;
        }
        
        // Add to temporary array
        memoryPalaceLocations.push({
            location: locationName.value,
            item: locationItem.value
        });
        
        // Update locations list display
        displayLocations();
        
        // Clear inputs
        locationName.value = '';
        locationItem.value = '';
    }
    
    // Display memory palace locations
    function displayLocations() {
        let html = '';
        
        memoryPalaceLocations.forEach((loc, index) => {
            html += `
                <div class="location-item">
                    <div class="location-item-header">
                        <span>${index + 1}. ${loc.location}</span>
                        <button class="btn btn-danger" onclick="removeLocation(${index})" style="padding: 2px 5px; font-size: 0.8rem;">Remove</button>
                    </div>
                    <div class="location-item-content">${loc.item}</div>
                </div>
            `;
        });
        
        locationsList.innerHTML = html;
    }
    
    // Remove location from memory palace
    function removeLocation(index) {
        memoryPalaceLocations.splice(index, 1);
        displayLocations();
    }
    
    // Save memory palace
    function saveMemoryPalace() {
        if (!currentPdfId) {
            showAlert('Please open a PDF first', 'error');
            return;
        }
        
        if (!palaceTitle.value || memoryPalaceLocations.length === 0) {
            showAlert('Please add a title and at least one location', 'error');
            return;
        }
        
        // Initialize PDF's mnemonics if not exist
        if (!mnemonicsData[currentPdfId]) {
            mnemonicsData[currentPdfId] = [];
        }
        
        // Add new memory palace
        mnemonicsData[currentPdfId].push({
            type: 'loci',
            title: palaceTitle.value,
            locations: [...memoryPalaceLocations], // Create a copy
            id: Date.now() // Use timestamp as unique ID
        });
        
        // Save to local storage
        saveMnemonics();
        
        // Clear form
        palaceTitle.value = '';
        memoryPalaceLocations = [];
        displayLocations();
        
        // Update display
        displaySavedMnemonics();
        updateQuizSectionVisibility();
        
        showAlert('Memory palace saved', 'success');
    }
    
    // Save peg system
    function savePegSystem() {
        if (!currentPdfId) {
            showAlert('Please open a PDF first', 'error');
            return;
        }
        
        if (!pegTitle.value || !pegSystem.value || !pegItems.value) {
            showAlert('Please fill in all fields', 'error');
            return;
        }
        
        // Initialize PDF's mnemonics if not exist
        if (!mnemonicsData[currentPdfId]) {
            mnemonicsData[currentPdfId] = [];
        }
        
        // Parse peg system and items
        const pegs = pegSystem.value.split('\n').map(line => line.trim()).filter(line => line);
        const items = pegItems.value.split('\n').map(line => line.trim()).filter(line => line);
        
        // Add new peg system
        mnemonicsData[currentPdfId].push({
            type: 'peg',
            title: pegTitle.value,
            pegs: pegs,
            items: items,
            id: Date.now() // Use timestamp as unique ID
        });
        
        // Save to local storage
        saveMnemonics();
        
        // Clear form
        pegTitle.value = '';
        pegSystem.value = '';
        pegItems.value = '';
        
        // Update display
        displaySavedMnemonics();
        updateQuizSectionVisibility();
        
        showAlert('Peg system saved', 'success');
    }
    
    // Save chunking
    function saveChunking() {
        if (!currentPdfId) {
            showAlert('Please open a PDF first', 'error');
            return;
        }
        
        if (!chunkingTitle.value || !originalInfo.value || !chunkedInfo.value || !chunkingExplanation.value) {
            showAlert('Please fill in all fields', 'error');
            return;
        }
        
        // Initialize PDF's mnemonics if not exist
        if (!mnemonicsData[currentPdfId]) {
            mnemonicsData[currentPdfId] = [];
        }
        
        // Add new chunking
        mnemonicsData[currentPdfId].push({
            type: 'chunking',
            title: chunkingTitle.value,
            original: originalInfo.value,
            chunked: chunkedInfo.value,
            explanation: chunkingExplanation.value,
            id: Date.now() // Use timestamp as unique ID
        });
        
        // Save to local storage
        saveMnemonics();
        
        // Clear form
        chunkingTitle.value = '';
        originalInfo.value = '';
        chunkedInfo.value = '';
        chunkingExplanation.value = '';
        
        // Update display
        displaySavedMnemonics();
        updateQuizSectionVisibility();
        
        showAlert('Chunking mnemonic saved', 'success');
    }
    
    // Save association
    function saveAssociation() {
        if (!currentPdfId) {
            showAlert('Please open a PDF first', 'error');
            return;
        }
        
        if (!associationTitle.value || !itemToRemember.value || !associatedWith.value || !associationExplanation.value) {
            showAlert('Please fill in all fields', 'error');
            return;
        }
        
        // Initialize PDF's mnemonics if not exist
        if (!mnemonicsData[currentPdfId]) {
            mnemonicsData[currentPdfId] = [];
        }
        
        // Add new association
        mnemonicsData[currentPdfId].push({
            type: 'association',
            title: associationTitle.value,
            item: itemToRemember.value,
            associated: associatedWith.value,
            explanation: associationExplanation.value,
            id: Date.now() // Use timestamp as unique ID
        });
        
        // Save to local storage
        saveMnemonics();
        
        // Clear form
        associationTitle.value = '';
        itemToRemember.value = '';
        associatedWith.value = '';
        associationExplanation.value = '';
        
        // Update display
        displaySavedMnemonics();
        updateQuizSectionVisibility();
        
        showAlert('Association mnemonic saved', 'success');
    }
    
    // Display saved mnemonics for current PDF
    function displaySavedMnemonics() {
        let html = '';
        
        if (currentPdfId && mnemonicsData[currentPdfId] && mnemonicsData[currentPdfId].length > 0) {
            mnemonicsData[currentPdfId].forEach(mnemonic => {
                let content = '';
                
                if (mnemonic.type === 'acronym') {
                    content = `
                        <p><strong>Acronym/Acrostic:</strong> ${mnemonic.acronym}</p>
                        <p><strong>Explanation:</strong> ${mnemonic.explanation}</p>
                    `;
                } else if (mnemonic.type === 'loci') {
                    let locationsHtml = '';
                    mnemonic.locations.forEach((loc, i) => {
                        locationsHtml += `<li>${i + 1}. <strong>${loc.location}:</strong> ${loc.item}</li>`;
                    });
                    
                    content = `
                        <p><strong>Memory Palace:</strong> ${mnemonic.title}</p>
                        <ol>${locationsHtml}</ol>
                    `;
                } else if (mnemonic.type === 'peg') {
                    let pegsHtml = '';
                    for (let i = 0; i < Math.max(mnemonic.pegs.length, mnemonic.items.length); i++) {
                        const peg = mnemonic.pegs[i] || '';
                        const item = mnemonic.items[i] || '';
                        pegsHtml += `<li><strong>${peg}</strong> - ${item}</li>`;
                    }
                    
                    content = `
                        <p><strong>Peg System:</strong> ${mnemonic.title}</p>
                        <ul>${pegsHtml}</ul>
                    `;
                } else if (mnemonic.type === 'chunking') {
                    content = `
                        <p><strong>Original:</strong> ${mnemonic.original}</p>
                        <p><strong>Chunked:</strong> ${mnemonic.chunked}</p>
                        <p><strong>How it helps:</strong> ${mnemonic.explanation}</p>
                    `;
                } else if (mnemonic.type === 'association') {
                    content = `
                        <p><strong>Item to Remember:</strong> ${mnemonic.item}</p>
                        <p><strong>Associated With:</strong> ${mnemonic.associated}</p>
                        <p><strong>How it helps:</strong> ${mnemonic.explanation}</p>
                    `;
                }
                
                html += `
                    <div class="mnemonic-item">
                        <div class="mnemonic-title">
                            <span>${mnemonic.title}</span>
                            <span style="font-weight: normal; font-size: 0.8rem; color: #666;">
                                ${capitalizeFirstLetter(mnemonic.type)}
                            </span>
                        </div>
                        <div class="mnemonic-content">
                            ${content}
                        </div>
                        <div class="mnemonic-actions">
                            <button class="btn btn-danger" onclick="deleteMnemonic('${mnemonic.id}')" style="padding: 3px 8px; font-size: 0.8rem;">Delete</button>
                        </div>
                    </div>
                `;
            });
        } else {
            html = '<p>No mnemonics saved for this PDF yet. Create your first one above!</p>';
        }
        
        savedMnemonics.innerHTML = html;
    }
    
    // Delete mnemonic
    function deleteMnemonic(id) {
        if (confirm('Are you sure you want to delete this mnemonic?')) {
            id = Number(id); // Convert to number since our IDs are timestamps
            
            if (currentPdfId && mnemonicsData[currentPdfId]) {
                mnemonicsData[currentPdfId] = mnemonicsData[currentPdfId].filter(m => m.id !== id);
                saveMnemonics();
                displaySavedMnemonics();
                updateQuizSectionVisibility();
                showAlert('Mnemonic deleted', 'success');
            }
        }
    }
    
    // Update quiz section visibility
    function updateQuizSectionVisibility() {
        if (currentPdfId && mnemonicsData[currentPdfId] && mnemonicsData[currentPdfId].length > 0) {
            quizSection.style.display = 'block';
        } else {
            quizSection.style.display = 'none';
            quizContent.style.display = 'none';
        }
    }
    
    // Start quiz
    function startQuiz() {
        if (!currentPdfId || !mnemonicsData[currentPdfId] || mnemonicsData[currentPdfId].length === 0) {
            showAlert('No mnemonics available for quiz', 'error');
            return;
        }
        
        let html = '<h4>Test Your Knowledge</h4>';
        
        // Create quiz questions based on mnemonic type
        mnemonicsData[currentPdfId].forEach((mnemonic, index) => {
            if (mnemonic.type === 'acronym') {
                html += `
                    <div class="quiz-item">
                        <p><strong>Question ${index + 1}:</strong> What does the acronym "${mnemonic.acronym}" stand for in "${mnemonic.title}"?</p>
                        <button class="show-answer-btn" onclick="toggleAnswer('answer${index}')">Show Answer</button>
                        <div id="answer${index}" class="quiz-answer">
                            <p>${mnemonic.explanation}</p>
                        </div>
                    </div>
                `;
            } else if (mnemonic.type === 'loci') {
                html += `
                    <div class="quiz-item">
                        <p><strong>Question ${index + 1}:</strong> In your memory palace "${mnemonic.title}", what items did you place at these locations?</p>
                        <ol>
                `;
                
                mnemonic.locations.forEach(loc => {
                    html += `<li>${loc.location}?</li>`;
                });
                
                html += `
                        </ol>
                        <button class="show-answer-btn" onclick="toggleAnswer('answer${index}')">Show Answer</button>
                        <div id="answer${index}" class="quiz-answer">
                            <ol>
                `;
                
                mnemonic.locations.forEach(loc => {
                    html += `<li><strong>${loc.location}:</strong> ${loc.item}</li>`;
                });
                
                html += `
                            </ol>
                        </div>
                    </div>
                `;
            } else if (mnemonic.type === 'peg') {
                html += `
                    <div class="quiz-item">
                        <p><strong>Question ${index + 1}:</strong> Using your peg system "${mnemonic.title}", recall what each peg represents:</p>
                        <ul>
                `;
                
                mnemonic.pegs.forEach(peg => {
                    html += `<li>${peg} represents what?</li>`;
                });
                
                html += `
                        </ul>
                        <button class="show-answer-btn" onclick="toggleAnswer('answer${index}')">Show Answer</button>
                        <div id="answer${index}" class="quiz-answer">
                            <ul>
                `;
                
                for (let i = 0; i < Math.min(mnemonic.pegs.length, mnemonic.items.length); i++) {
                    html += `<li><strong>${mnemonic.pegs[i]}:</strong> ${mnemonic.items[i]}</li>`;
                }
                
                html += `
                            </ul>
                        </div>
                    </div>
                `;
            } else if (mnemonic.type === 'chunking') {
                html += `
                    <div class="quiz-item">
                        <p><strong>Question ${index + 1}:</strong> Using your chunking method "${mnemonic.title}", what's the original information for "${mnemonic.chunked}"?</p>
                        <button class="show-answer-btn" onclick="toggleAnswer('answer${index}')">Show Answer</button>
                        <div id="answer${index}" class="quiz-answer">
                            <p>${mnemonic.original}</p>
                        </div>
                    </div>
                `;
            } else if (mnemonic.type === 'association') {
                html += `
                    <div class="quiz-item">
                        <p><strong>Question ${index + 1}:</strong> Using your association "${mnemonic.title}", what is "${mnemonic.item}" associated with?</p>
                        <button class="show-answer-btn" onclick="toggleAnswer('answer${index}')">Show Answer</button>
                        <div id="answer${index}" class="quiz-answer">
                            <p>${mnemonic.associated}</p>
                        </div>
                    </div>
                `;
            }
        });
        
        html += `
            <button class="btn btn-primary" style="margin-top: 15px;" onclick="endQuiz()">End Quiz</button>
        `;
        
        quizContent.innerHTML = html;
        quizContent.style.display = 'block';
        startQuizBtn.style.display = 'none';
    }
    
    // End quiz
    function endQuiz() {
        quizContent.style.display = 'none';
        startQuizBtn.style.display = 'block';
    }
    
    // Toggle quiz answer visibility
    function toggleAnswer(id) {
        const answer = document.getElementById(id);
        if (answer.style.display === 'block') {
            answer.style.display = 'none';
        } else {
            answer.style.display = 'block';
        }
    }
    
    // Show alert message
    function showAlert(message, type = 'info') {
        alert.textContent = message;
        alert.className = 'alert';
        
        if (type === 'success') {
            alert.classList.add('alert-success');
        } else if (type === 'error') {
            alert.classList.add('alert-error');
        }
        
        alert.style.display = 'block';
        
        // Hide after 3 seconds
        setTimeout(() => {
            alert.style.display = 'none';
        }, 3000);
    }
    
    // Helper function to capitalize first letter
    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
    
    // Initialize the app when DOM is loaded
    document.addEventListener('DOMContentLoaded', init);

        // --- AI Mnemonic Generation Feature ---
        // 1. Modal logic
        const aiMnemonicModalBg = document.getElementById('aiMnemonicModalBg');
        const openAIMnemonicModal = document.getElementById('openAIMnemonicModal');
        const generateAIMnemonicBtn = document.getElementById('generateAIMnemonicBtn');
        const saveAIMnemonicBtn = document.getElementById('saveAIMnemonicBtn');
        const cancelAIMnemonicBtn = document.getElementById('cancelAIMnemonicBtn');
        const aiMnemonicResult = document.getElementById('aiMnemonicResult');
        const aiMnemonicLoading = document.getElementById('aiMnemonicLoading');
        const geminiApiKeyInput = document.getElementById('geminiApiKeyInput');

        openAIMnemonicModal.addEventListener('click', () => {
            if (!currentPdfId || !pdfUrl) {
                showAlert('Please select a PDF first', 'error');
                return;
            }
            aiMnemonicResult.innerHTML = '';
            aiMnemonicLoading.style.display = 'none';
            saveAIMnemonicBtn.style.display = 'none';
            aiMnemonicModalBg.style.display = 'block';
        });
        cancelAIMnemonicBtn.addEventListener('click', () => {
            aiMnemonicModalBg.style.display = 'none';
        });

        // 2. Helper: fetch PDF text from backend (reuse pdf-parser.php)
        async function getPDFTextForAIMnemonic() {
            let pdfPath = pdfUrl;
            if (!pdfPath.startsWith('uploads/')) {
                pdfPath = pdfPath.replace(/^\/+/, '');
                pdfPath = 'uploads/' + pdfPath;
            }
            const textUrl = `pdf-parser.php?path=${encodeURIComponent(pdfPath)}&t=${Date.now()}`;
            const res = await fetch(textUrl);
            const contentType = res.headers.get('Content-Type');
            if (!res.ok || !contentType || !contentType.includes('application/json')) {
                let errorText = await res.text();
                throw new Error('Failed to fetch PDF text for Gemini Vision. ' + errorText);
            }
            const data = await res.json();
            if (!data.text) throw new Error('No text extracted from PDF.');
            return data.text;
        }

        // 3. Generate Mnemonics with Gemini
        generateAIMnemonicBtn.addEventListener('click', async () => {
            const apiKey = geminiApiKeyInput.value.trim();
            if (!apiKey) {
                showAlert('Please enter your Gemini API Key', 'error');
                return;
            }
            aiMnemonicLoading.style.display = 'block';
            aiMnemonicResult.innerHTML = '';
            saveAIMnemonicBtn.style.display = 'none';
            try {
                const pdfText = await getPDFTextForAIMnemonic();
                const prompt = `Create a list of mnemonics (acronyms, memory palaces, peg systems, chunking, associations) for the following PDF text. 
Output valid JSON with an array "mnemonics", each with "type" (acronym|loci|peg|chunking|association), "title", and relevant fields:
- For acronym: "acronym", "explanation"
- For loci: "locations" (array of {location, item})
- For peg: "pegs" (array), "items" (array)
- For chunking: "original", "chunked", "explanation"
- For association: "item", "associated", "explanation"
Output only valid JSON. Do not include explanations or meta content.

PDF Text:
${pdfText}`;
                const body = {
                    contents: [
                        {
                            parts: [
                                { text: prompt }
                            ]
                        }
                    ]
                };
                const response = await fetch('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' + encodeURIComponent(apiKey), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body)
                });
                if (!response.ok) {
                    const errorBody = await response.text();
                    throw new Error('Gemini API Error: ' + errorBody);
                }
                const data = await response.json();
                let aiJson = '';
                if (data.candidates && data.candidates[0] && data.candidates[0].content && data.candidates[0].content.parts) {
                    for (const part of data.candidates[0].content.parts) {
                        if (part.text) aiJson += part.text;
                    }
                }
                // Try to extract JSON block if Gemini returns markdown or explanations
                let aiMnemonics;
                try {
                    const jsonMatch = aiJson.match(/```(?:json)?\s*([\s\S]*?)\s*```/i);
                    let jsonToParse = jsonMatch ? jsonMatch[1] : aiJson;
                    const firstBrace = jsonToParse.indexOf('{');
                    const lastBrace = jsonToParse.lastIndexOf('}');
                    if (firstBrace !== -1 && lastBrace !== -1) {
                        jsonToParse = jsonToParse.substring(firstBrace, lastBrace + 1);
                    }
                    aiMnemonics = JSON.parse(jsonToParse);
                } catch (e) {
                    aiMnemonicResult.innerHTML = `<p style="color:#b00;">Failed to parse mnemonics JSON. Try again or check your prompt.<br><br><small>Raw output:<br><pre>${aiJson.replace(/</g,"&lt;")}</pre></small></p>`;
                    aiMnemonicLoading.style.display = 'none';
                    return;
                }
                // Preview mnemonics
                let html = `<h4>Preview:</h4>`;
                if (aiMnemonics.mnemonics && aiMnemonics.mnemonics.length) {
                    html += `<ul>`;
                    aiMnemonics.mnemonics.slice(0, 5).forEach(m => {
                        html += `<li><strong>${m.title}</strong> (${m.type})</li>`;
                    });
                    if (aiMnemonics.mnemonics.length > 5) html += `<li>...and ${aiMnemonics.mnemonics.length - 5} more</li>`;
                    html += `</ul>`;
                }
                aiMnemonicResult.innerHTML = html;
                saveAIMnemonicBtn.style.display = 'inline-block';
                aiMnemonicModalBg.dataset.mnemonics = JSON.stringify(aiMnemonics.mnemonics);
            } catch (err) {
                aiMnemonicResult.innerHTML = `<p style="color:#b00;">${err.message}</p>`;
            }
            aiMnemonicLoading.style.display = 'none';
        });

        // 4. Save AI mnemonics to current PDF
        saveAIMnemonicBtn.addEventListener('click', () => {
            const mnemonicsJson = aiMnemonicModalBg.dataset.mnemonics;
            if (!mnemonicsJson) return;
            let aiMnemonics;
            try {
                aiMnemonics = JSON.parse(mnemonicsJson);
            } catch (e) {
                showAlert('Failed to parse mnemonics JSON.', 'error');
                return;
            }
            if (!currentPdfId) {
                showAlert('Please open a PDF first', 'error');
                return;
            }
            if (!mnemonicsData[currentPdfId]) mnemonicsData[currentPdfId] = [];
            aiMnemonics.forEach(m => {
                m.id = Date.now() + Math.floor(Math.random() * 1000000); // unique id
                mnemonicsData[currentPdfId].push(m);
            });
            saveMnemonics();
            displaySavedMnemonics();
            updateQuizSectionVisibility();
            showAlert('AI-generated mnemonics saved!', 'success');
            aiMnemonicModalBg.style.display = 'none';
        });

        // ...existing code...
    </script>
</body>
</html>