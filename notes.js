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