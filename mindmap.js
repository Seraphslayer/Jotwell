const fileList = document.getElementById('fileList');
        const pdfViewer = document.getElementById('pdfViewer');
        const pdfInfo = document.getElementById('pdfInfo');
        const pdfTitle = document.getElementById('pdfTitle');
        const downloadPdfBtn = document.getElementById('downloadPdfBtn');
        const notesTextarea = document.getElementById('notes');
        const saveNotesBtn = document.getElementById('saveNotesBtn');
        const exportNotesBtn = document.getElementById('exportNotesBtn');
        const addToMindMapBtn = document.getElementById('addToMindMapBtn');
        const alert = document.getElementById('alert');
        
        // Mind Map Elements
        const mindmapContainer = document.getElementById('mindmapContainer');
        const mindmapCanvas = document.getElementById('mindmapCanvas');
        const addNodeBtn = document.getElementById('addNodeBtn');
        const connectNodesBtn = document.getElementById('connectNodesBtn');
        const clearConnectionModeBtn = document.getElementById('clearConnectionModeBtn');
        const saveMapBtn = document.getElementById('saveMapBtn');
        const loadMapBtn = document.getElementById('loadMapBtn');
        const nodeForm = document.getElementById('nodeForm');
        const nodeTitle = document.getElementById('nodeTitle');
        const nodeContent = document.getElementById('nodeContent');
        const nodeType = document.getElementById('nodeType');
        const saveNodeBtn = document.getElementById('saveNodeBtn');
        const cancelNodeBtn = document.getElementById('cancelNodeBtn');
        const colorPicker = document.getElementById('colorPicker');
        const exportImageBtn = document.getElementById('exportImageBtn');
        const exportJSONBtn = document.getElementById('exportJSONBtn');
        const templateBtns = document.querySelectorAll('.template-btn');
        
        // PDF viewer variables
        let pdfUrl = '';
        let currentPdfId = '';
        let currentPdfName = '';
        let notesData = {};
        
        // Mind Map variables
        let nodes = [];
        let connections = [];
        let selectedNode = null;
        let isCreatingNode = false;
        let isConnectingNodes = false;
        let connectionStartNode = null;
        let nodeIdCounter = 0;
        let selectedColor = '#5c6bc0';
        let currentMindMapId = '';
        let mindMaps = {};
        let draggedNode = null;
        let offsetX = 0;
        let offsetY = 0;
        let isDragging = false;
        let isMoving = false;
        let lastX = 0;
        let lastY = 0;
        
        // Initialize the app
        function init() {
            setupEventListeners();
            loadNotes();
            loadMindMaps();
        }
        
        // Set up event listeners
        function setupEventListeners() {
            // Notes
            saveNotesBtn.addEventListener('click', saveNotes);
            if (exportNotesBtn) {
                exportNotesBtn.addEventListener('click', exportNotes);
            }
            
            // Add to Mind Map button
            addToMindMapBtn.addEventListener('click', addNotesToMindMap);
            
            // Mind Map Controls
            addNodeBtn.addEventListener('click', startNodeCreation);
            connectNodesBtn.addEventListener('click', startConnectionMode);
            clearConnectionModeBtn.addEventListener('click', clearConnectionMode);
            saveMapBtn.addEventListener('click', saveMindMap);
            loadMapBtn.addEventListener('click', showLoadMapDialog);
            saveNodeBtn.addEventListener('click', saveNodeProperties);
            cancelNodeBtn.addEventListener('click', hideNodeForm);
            exportImageBtn.addEventListener('click', exportMindMapAsImage);
            exportJSONBtn.addEventListener('click', exportMindMapAsJSON);
            
            // Color picker
            document.querySelectorAll('.color-option').forEach(option => {
                option.addEventListener('click', (e) => {
                    document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
                    e.target.classList.add('selected');
                    selectedColor = e.target.getAttribute('data-color');
                });
            });
            
            // Template buttons
            templateBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const template = e.target.getAttribute('data-template');
                    loadTemplate(template);
                });
            });
            
            // Mind Map Container for node placement
            if (mindmapContainer) {
                mindmapContainer.addEventListener('click', handleContainerClick);
                mindmapContainer.addEventListener('mousedown', handleNodeMouseDown);
                mindmapContainer.addEventListener('mousemove', handleMouseMove);
                mindmapContainer.addEventListener('mouseup', handleMouseUp);
                mindmapContainer.addEventListener('mouseleave', handleMouseUp);
            }
            
            // Mind Map Container for drag and drop
            document.addEventListener('mousemove', handleMouseMove);
            document.addEventListener('mouseup', handleMouseUp);
            
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
            
            // Set current mind map ID based on PDF
            currentMindMapId = 'mindmap_' + pdfId;
            
            // Load mind map for this PDF if exists
            loadMindMapForCurrentPdf();
            
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
        
        // Mind Map Functions
        
        // Add notes to mind map
        function addNotesToMindMap() {
            if (!notesTextarea.value.trim()) {
                showAlert('No notes to add to mind map', 'error');
                return;
            }
            
            // Create a new node with notes content
            const centerX = mindmapContainer.offsetWidth / 2 - 75;
            const centerY = mindmapContainer.offsetHeight / 2 - 75;
            const text = notesTextarea.value.trim();
            
            // If the text is too long, use the first few words as title and rest as content
            let title, content;
            
            if (text.length > 50) {
                const words = text.split(' ');
                title = words.slice(0, 5).join(' ') + '...';
                content = text;
            } else {
                title = text;
                content = '';
            }
            
            createNode(centerX, centerY, title, content, 'default', selectedColor);
            renderMindMap();
            showAlert('Notes added to mind map', 'success');
        }
        
        // Start node creation mode
        function startNodeCreation() {
            isCreatingNode = true;
            isConnectingNodes = false;
            clearConnectionMode();
            showAlert('Click on the mind map to place a new node', 'success');
        }
        
        // Start connection mode
        function startConnectionMode() {
            if (!nodes.length) {
                showAlert('Create some nodes first', 'error');
                return;
            }
            
            isConnectingNodes = true;
            isCreatingNode = false;
            connectionStartNode = null;
            connectNodesBtn.style.display = 'none';
            clearConnectionModeBtn.style.display = 'inline-block';
            showAlert('Select first node to connect', 'success');
        }
        
        // Clear connection mode
        function clearConnectionMode() {
            isConnectingNodes = false;
            connectionStartNode = null;
            connectNodesBtn.style.display = 'inline-block';
            clearConnectionModeBtn.style.display = 'none';
        }
        
        // Handle clicks on the mindmap container
        function handleContainerClick(e) {
            if (isCreatingNode) {
                // Get click position relative to container
                const rect = mindmapContainer.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                // Don't create a node if clicking on another node
                const clickedOnNode = e.target.closest('.node');
                if (clickedOnNode) return;
                
                // Show node form
                nodeTitle.value = '';
                nodeContent.value = '';
                nodeType.value = 'default';
                nodeForm.style.display = 'block';
                
                // Save position for later use
                nodeForm.dataset.x = x;
                nodeForm.dataset.y = y;
                
                isCreatingNode = false;
            }
        }
        
        // Save node properties
function saveNodeProperties() {
    const title = nodeTitle.value.trim() || 'Untitled Node';
    const content = nodeContent.value.trim();
    const type = nodeType.value;
    const x = parseFloat(nodeForm.dataset.x);
    const y = parseFloat(nodeForm.dataset.y);
    
    if (nodeForm.dataset.nodeId) {
        // Update existing node
        const nodeId = parseInt(nodeForm.dataset.nodeId);
        const node = nodes.find(n => n.id === nodeId);
        if (node) {
            node.title = title;
            node.content = content;
            node.type = type;
            node.color = selectedColor;
        }
    } else {
        // Create new node
        createNode(x, y, title, content, type, selectedColor);
    }
    
    hideNodeForm();
    renderMindMap();
}

// Hide node form
function hideNodeForm() {
    nodeForm.style.display = 'none';
    nodeForm.dataset.nodeId = '';
    nodeForm.dataset.x = '';
    nodeForm.dataset.y = '';
}

// Create a new node
function createNode(x, y, title, content, type, color) {
    // Calculate position relative to container size
    const canvasWidth = mindmapCanvas.offsetWidth;
    const canvasHeight = mindmapCanvas.offsetHeight;
    
    const relativeX = Math.max(60, Math.min(x, canvasWidth - 60));
    const relativeY = Math.max(40, Math.min(y, canvasHeight - 40));
    
    const node = {
        id: ++nodeIdCounter,
        x: relativeX,
        y: relativeY,
        title: title || 'New Node',
        content: content || '',
        type: type || 'default',
        color: color || selectedColor
    };
    nodes.push(node);
    return node;
}

// Render the mind map
function renderMindMap() {
    // Clear all existing nodes and connections first
    const existingNodes = mindmapCanvas.querySelectorAll('.node');
    const existingConnections = mindmapCanvas.querySelectorAll('.connection, .connection-dot');
    
    existingNodes.forEach(node => node.remove());
    existingConnections.forEach(conn => conn.remove());
    
    // Render connections first (so they appear behind nodes)
    renderConnections();
    
    // Then render nodes
    nodes.forEach(node => {
        const nodeElement = document.createElement('div');
        nodeElement.className = 'node';
        nodeElement.dataset.id = node.id;
        nodeElement.style.left = `${node.x}px`;
        nodeElement.style.top = `${node.y}px`;
        nodeElement.style.borderColor = node.color;
        nodeElement.style.transform = `translate(-50%, -50%)`; // Center the node
        
        // Add class based on node type
        if (node.type) {
            nodeElement.classList.add(`node-${node.type}`);
        }
        
        // Add content to node
        nodeElement.innerHTML = `
            <div class="node-title">${node.title}</div>
            ${node.content ? `<div class="node-content">${node.content}</div>` : ''}
            <div class="node-delete" data-id="${node.id}">×</div>
        `;
        
        // --- FIX: Add drag and edit event listeners for all nodes ---
        nodeElement.addEventListener('mousedown', handleNodeMouseDown);
        nodeElement.addEventListener('click', (e) => {
            // Ignore clicks on the delete button
            if (e.target.classList.contains('node-delete')) {
                const nodeId = parseInt(e.target.getAttribute('data-id'));
                deleteNode(nodeId);
                e.stopPropagation();
                return;
            }
            
            // Handle connection mode
            if (isConnectingNodes) {
                const nodeId = parseInt(nodeElement.getAttribute('data-id'));
                handleNodeConnectionClick(nodeId);
                e.stopPropagation();
                return;
            }
            
            // Prevent accidental edit after drag
            if (isDragging) {
                e.stopPropagation();
                return;
            }
            
            // Handle regular node selection
            document.querySelectorAll('.node').forEach(n => n.classList.remove('selected'));
            nodeElement.classList.add('selected');
            selectedNode = node;
            
            // Show node properties in form
            nodeForm.dataset.nodeId = node.id;
            nodeTitle.value = node.title;
            nodeContent.value = node.content || '';
            nodeType.value = node.type || 'default';
            
            // Select the correct color
            document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
            const colorOption = document.querySelector(`.color-option[data-color="${node.color}"]`);
            if (colorOption) {
                colorOption.classList.add('selected');
                selectedColor = node.color;
            }
            
            nodeForm.style.display = 'block';
            e.stopPropagation();
        });
        
        mindmapCanvas.appendChild(nodeElement);
    });
    
    // Add event listeners for node deletion
    document.querySelectorAll('.node-delete').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const nodeId = parseInt(e.target.getAttribute('data-id'));
            deleteNode(nodeId);
            e.stopPropagation();
        });
    });
}

// Render connections between nodes
function renderConnections() {
    connections.forEach(conn => {
        const startNode = nodes.find(n => n.id === conn.from);
        const endNode = nodes.find(n => n.id === conn.to);
        
        if (!startNode || !endNode) return;
        
        const x1 = startNode.x;
        const y1 = startNode.y;
        const x2 = endNode.x;
        const y2 = endNode.y;
        
        // Calculate line length and angle
        const dx = x2 - x1;
        const dy = y2 - y1;
        const length = Math.sqrt(dx * dx + dy * dy);
        const angle = Math.atan2(dy, dx) * 180 / Math.PI;
        
        // Create line element
        const line = document.createElement('div');
        line.className = 'connection';
        line.style.width = `${length}px`;
        line.style.left = `${x1}px`;
        line.style.top = `${y1}px`;
        line.style.transform = `rotate(${angle}deg)`;
        
        mindmapCanvas.appendChild(line);
        
        // Add dots at ends
        const startDot = document.createElement('div');
        startDot.className = 'connection-dot';
        startDot.style.left = `${x1 - 4}px`;
        startDot.style.top = `${y1 - 4}px`;
        
        const endDot = document.createElement('div');
        endDot.className = 'connection-dot';
        endDot.style.left = `${x2 - 4}px`;
        endDot.style.top = `${y2 - 4}px`;
        
        mindmapCanvas.appendChild(startDot);
        mindmapCanvas.appendChild(endDot);
    });
}

// Handle node connection clicks
function handleNodeConnectionClick(nodeId) {
    if (!connectionStartNode) {
        // First node clicked
        connectionStartNode = nodeId;
        showAlert('Now select second node to connect', 'success');
    } else {
        // Second node clicked
        if (connectionStartNode === nodeId) {
            showAlert('Cannot connect a node to itself', 'error');
            return;
        }
        
        // Check if connection already exists
        const connectionExists = connections.some(conn => 
            (conn.from === connectionStartNode && conn.to === nodeId) || 
            (conn.from === nodeId && conn.to === connectionStartNode)
        );
        
        if (connectionExists) {
            showAlert('These nodes are already connected', 'error');
        } else {
            // Create the new connection
            connections.push({
                from: connectionStartNode,
                to: nodeId
            });
            
            renderMindMap();
            showAlert('Connection created', 'success');
        }
        
        // Reset connection mode
        clearConnectionMode();
    }
}

// Delete a node and its connections
function deleteNode(nodeId) {
    nodes = nodes.filter(node => node.id !== nodeId);
    connections = connections.filter(conn => conn.from !== nodeId && conn.to !== nodeId);
    
    if (nodeForm.dataset.nodeId == nodeId) {
        hideNodeForm();
    }
    
    renderMindMap();
    showAlert('Node deleted', 'success');
}

// Handle mouse down on node for dragging
function handleNodeMouseDown(e) {
    const nodeElement = e.target.closest('.node');
    if (!nodeElement || isConnectingNodes) return;
    if (e.target.classList.contains('node-delete')) return;

    isDragging = true;
    draggedNode = nodes.find(n => n.id === parseInt(nodeElement.dataset.id));
    
    const rect = mindmapCanvas.getBoundingClientRect();
    const scaleX = rect.width / mindmapCanvas.offsetWidth;
    const scaleY = rect.height / mindmapCanvas.offsetHeight;
    
    offsetX = (e.clientX - rect.left) / scaleX - draggedNode.x;
    offsetY = (e.clientY - rect.top) / scaleY - draggedNode.y;
    
    e.preventDefault();
}

// Handle mouse move for dragging
function handleMouseMove(e) {
    if (!isDragging || !draggedNode) return;

    const rect = mindmapCanvas.getBoundingClientRect();
    const scaleX = rect.width / mindmapCanvas.offsetWidth;
    const scaleY = rect.height / mindmapCanvas.offsetHeight;
    
    const newX = (e.clientX - rect.left) / scaleX - offsetX;
    const newY = (e.clientY - rect.top) / scaleY - offsetY;
    
    // Update node position with bounds checking
    draggedNode.x = Math.max(75, Math.min(newX, mindmapCanvas.offsetWidth - 75));
    draggedNode.y = Math.max(50, Math.min(newY, mindmapCanvas.offsetHeight - 50));
    
    renderMindMap();
}

// Handle mouse up to end dragging
function handleMouseUp() {
    isDragging = false;
    draggedNode = null;
    isMoving = false;
}

// Save mind map to local storage
function saveMindMap() {
    if (!currentPdfId) {
        showAlert('No PDF loaded to save mind map for', 'error');
        return;
    }
    
    const mindMapData = {
        nodes: nodes,
        connections: connections,
        nextId: nodeIdCounter
    };
    
    // Load existing mind maps
    loadMindMaps();
    
    // Save current mind map
    mindMaps[currentMindMapId] = mindMapData;
    localStorage.setItem('mindMaps', JSON.stringify(mindMaps));
    
    showAlert('Mind map saved successfully', 'success');
}

// Load mind maps from local storage
function loadMindMaps() {
    const savedMindMaps = localStorage.getItem('mindMaps');
    if (savedMindMaps) {
        mindMaps = JSON.parse(savedMindMaps);
    }
}

// Load mind map for current PDF
function loadMindMapForCurrentPdf() {
    if (currentMindMapId && mindMaps[currentMindMapId]) {
        const data = mindMaps[currentMindMapId];
        nodes = data.nodes || [];
        connections = data.connections || [];
        nodeIdCounter = nodes.length ? Math.max(...nodes.map(n => parseInt(n.id, 10))) : 0;
        renderMindMap();
        showAlert('Mind map loaded for current PDF', 'success');
    } else {
        // Reset mind map
        nodes = [];
        connections = [];
        nodeIdCounter = 0;
        renderMindMap();
    }
}

// Show load map dialog
function showLoadMapDialog() {
    // For simplicity, just load the current PDF's mind map
    loadMindMapForCurrentPdf();
}

// Export mind map as image (using html2canvas)
function exportMindMapAsImage() {
    // Hide node form if open
    nodeForm.style.display = 'none';
    // Use html2canvas on the mindmap-container (viewport only)
    html2canvas(mindmapContainer, {
        backgroundColor: "#fff",
        useCORS: true,
        scale: 2 // higher quality
    }).then(canvas => {
        // Create a download link
        const link = document.createElement('a');
        link.download = `mindmap_${currentPdfName ? currentPdfName.replace(/\s+/g, '_') : 'export'}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
        showAlert('Mind map exported as image', 'success');
    }).catch(() => {
        showAlert('Failed to export mind map as image', 'error');
    });
}

// Export mind map as JSON
function exportMindMapAsJSON() {
    if (!nodes.length) {
        showAlert('No mind map to export', 'error');
        return;
    }
    
    const data = {
        nodes: nodes,
        connections: connections,
        title: currentPdfName || 'Mind Map',
        date: new Date().toISOString()
    };
    
    const blob = new Blob([JSON.stringify(data, null, 2)], {type: 'application/json'});
    const url = URL.createObjectURL(blob);
    
    const a = document.createElement('a');
    a.href = url;
    a.download = `mindmap_${currentPdfName ? currentPdfName.replace(/\s+/g, '_') : 'export'}.json`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    
    showAlert('Mind map exported as JSON', 'success');
}

// Load a template mind map
function loadTemplate(template) {
    // Reset current mind map
    nodes = [];
    connections = [];
    nodeIdCounter = 0;
    
    const centerX = mindmapContainer.offsetWidth / 2 - 75;
    const centerY = mindmapContainer.offsetHeight / 2 - 75;
    
    // Create template based on type
    switch (template) {
        case 'blank':
            // Just create a central node
            createNode(centerX, centerY, 'Main Idea', 'Central concept of your mind map', 'main', '#5c6bc0');
            break;
            
        case 'chapter':
            // Create a chapter summary template
            const mainNode = createNode(centerX, centerY, 'Chapter Title', 'Main themes and ideas', 'main', '#5c6bc0');
            
            const keyPointsNode = createNode(centerX - 250, centerY - 100, 'Key Points', 'Important concepts from the chapter', 'subtopic', '#66bb6a');
            const charactersNode = createNode(centerX + 250, centerY - 100, 'Characters', 'People mentioned in this chapter', 'subtopic', '#ff7043');
            const questionsNode = createNode(centerX - 250, centerY + 100, 'Questions', 'Things to explore further', 'question', '#ba68c8');
            const summaryNode = createNode(centerX + 250, centerY + 100, 'Summary', 'Brief chapter overview', 'detail', '#4dd0e1');
            
            // Connect nodes
            connections.push({from: mainNode.id, to: keyPointsNode.id});
            connections.push({from: mainNode.id, to: charactersNode.id});
            connections.push({from: mainNode.id, to: questionsNode.id});
            connections.push({from: mainNode.id, to: summaryNode.id});
            break;
            
        case 'concept':
            // Create a concept map template
            const conceptNode = createNode(centerX, centerY, 'Main Concept', 'Central idea or theory', 'main', '#5c6bc0');
            
            const definitionNode = createNode(centerX - 250, centerY - 150, 'Definition', 'What is it?', 'subtopic', '#66bb6a');
            const examplesNode = createNode(centerX + 250, centerY - 150, 'Examples', 'Real-world applications', 'example', '#ff7043');
            
        case 'compare':
            // Create a compare and contrast template
            const compareNode = createNode(centerX, centerY, 'Comparison Topic', 'Items being compared', 'main', '#5c6bc0');
            
            const item1Node = createNode(centerX - 250, centerY - 100, 'Item 1', 'First item to compare', 'subtopic', '#66bb6a');
            const item2Node = createNode(centerX + 250, centerY - 100, 'Item 2', 'Second item to compare', 'subtopic', '#ff7043');
            const similaritiesNode = createNode(centerX, centerY + 150, 'Similarities', 'Common features', 'detail', '#ba68c8');
            
            // Connect nodes
            connections.push({from: compareNode.id, to: item1Node.id});
            connections.push({from: compareNode.id, to: item2Node.id});
            connections.push({from: compareNode.id, to: similaritiesNode.id});
            connections.push({from: item1Node.id, to: similaritiesNode.id});
            connections.push({from: item2Node.id, to: similaritiesNode.id});
            break;
    }
    
    renderMindMap();
    showAlert(`Loaded ${template} template`, 'success');
}
// --- AI Mind Map Generation Feature ---

// 1. Add AI Mind Map Generator button to the Mind Map panel
const aiMindMapBtn = document.createElement('button');
aiMindMapBtn.id = 'openAIMindMapModal';
aiMindMapBtn.className = 'btn btn-primary';
aiMindMapBtn.style.width = '100%';
aiMindMapBtn.style.marginTop = '10px';
aiMindMapBtn.textContent = 'AI Mind Map from PDF';
const mindmapSection = document.querySelector('.mindmap-section');
if (mindmapSection) mindmapSection.appendChild(aiMindMapBtn);

// 2. Create AI Mind Map Modal
const aiMindMapModal = document.createElement('div');
aiMindMapModal.id = 'aiMindMapModal';
aiMindMapModal.style.display = 'none';
aiMindMapModal.style.position = 'fixed';
aiMindMapModal.style.top = '0';
aiMindMapModal.style.left = '0';
aiMindMapModal.style.width = '100vw';
aiMindMapModal.style.height = '100vh';
aiMindMapModal.style.background = 'rgba(0,0,0,0.4)';
aiMindMapModal.style.zIndex = '9999';
aiMindMapModal.innerHTML = `
    <div style="background:#fff;max-width:600px;margin:60px auto;padding:30px;border-radius:8px;position:relative;">
        <h2>AI Mind Map Generator</h2>
        <p>This will use the current PDF to generate a mind map using Gemini AI.</p>
        <div id="aiMindMapLoading" style="display:none;">Generating mind map...</div>
        <div id="aiMindMapResult"></div>
        <input type="text" id="geminiApiKeyInput" placeholder="Enter Gemini API Key" style="width:100%;margin-bottom:10px;">
        <button id="generateAIMindMapBtn" class="btn btn-success">Generate Mind Map</button>
        <button id="saveAIMindMapBtn" class="btn btn-primary" style="display:none;">Save to Mind Map</button>
        <button id="cancelAIMindMapBtn" class="btn btn-danger">Cancel</button>
    </div>
`;
document.body.appendChild(aiMindMapModal);

// 3. Modal open/close logic
document.getElementById('openAIMindMapModal').addEventListener('click', () => {
    if (!currentPdfId || !pdfUrl) {
        showAlert('Please select a PDF first', 'error');
        return;
    }
    document.getElementById('aiMindMapResult').innerHTML = '';
    document.getElementById('aiMindMapLoading').style.display = 'none';
    document.getElementById('saveAIMindMapBtn').style.display = 'none';
    aiMindMapModal.style.display = 'block';
});
document.getElementById('cancelAIMindMapBtn').addEventListener('click', () => {
    aiMindMapModal.style.display = 'none';
});

// 4. Helper: fetch PDF text from backend
async function getPDFTextForMindMap() {
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

// 5. Generate Mind Map with Gemini 
document.getElementById('generateAIMindMapBtn').addEventListener('click', async () => {
    const apiKey = document.getElementById('geminiApiKeyInput').value.trim();
    if (!apiKey) {
        showAlert('Please enter your Gemini API Key', 'error');
        return;
    }
    document.getElementById('aiMindMapLoading').style.display = 'block';
    document.getElementById('aiMindMapResult').innerHTML = '';
    document.getElementById('saveAIMindMapBtn').style.display = 'none';
    try {
        const pdfText = await getPDFTextForMindMap();
        const prompt = `Create a mind map in JSON format from the following PDF text. 
The JSON must have a "nodes" array (each with "id", "title", "content", "type", "color", "x", "y") and a "connections" array (each with "from", "to").
Nodes should represent main ideas, subtopics, and details from the PDF. 
Output only valid JSON. Do not include any explanations or meta content.

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
        let mindMapJson = '';
        if (data.candidates && data.candidates[0] && data.candidates[0].content && data.candidates[0].content.parts) {
            for (const part of data.candidates[0].content.parts) {
                if (part.text) mindMapJson += part.text;
            }
        }
        // Improved: Try to extract JSON block if Gemini returns markdown or explanations
        let mindMap;
        try {
            // Extract JSON from markdown/code block if present
            const jsonMatch = mindMapJson.match(/```(?:json)?\s*([\s\S]*?)\s*```/i);
            let jsonToParse = jsonMatch ? jsonMatch[1] : mindMapJson;
            // Remove any leading/trailing text before/after JSON
            const firstBrace = jsonToParse.indexOf('{');
            const lastBrace = jsonToParse.lastIndexOf('}');
            if (firstBrace !== -1 && lastBrace !== -1) {
                jsonToParse = jsonToParse.substring(firstBrace, lastBrace + 1);
            }
            mindMap = JSON.parse(jsonToParse);
        } catch (e) {
            document.getElementById('aiMindMapResult').innerHTML = `<p style="color:#b00;">Failed to parse mind map JSON. Try again or check your prompt.<br><br><small>Raw output:<br><pre>${mindMapJson.replace(/</g,"&lt;")}</pre></small></p>`;
            document.getElementById('aiMindMapLoading').style.display = 'none';
            return;
        }
        // Preview mind map nodes and connections
        let html = `<h4>Preview:</h4>`;
        if (mindMap.nodes && mindMap.nodes.length) {
            html += `<ul>`;
            mindMap.nodes.slice(0, 5).forEach(node => {
                html += `<li><strong>${node.title}</strong>: ${node.content ? node.content.substring(0, 60) : ''}</li>`;
            });
            if (mindMap.nodes.length > 5) html += `<li>...and ${mindMap.nodes.length - 5} more nodes</li>`;
            html += `</ul>`;
        }
        if (mindMap.connections && mindMap.connections.length) {
            html += `<p>${mindMap.connections.length} connections generated.</p>`;
        }
        document.getElementById('aiMindMapResult').innerHTML = html;
        document.getElementById('saveAIMindMapBtn').style.display = 'inline-block';
        // Store for saving
        aiMindMapModal.dataset.mindMap = JSON.stringify(mindMap);
    } catch (err) {
        document.getElementById('aiMindMapResult').innerHTML = `<p style="color:#b00;">${err.message}</p>`;
    }
    document.getElementById('aiMindMapLoading').style.display = 'none';
});

// 6. Save to Mind Map 
document.getElementById('saveAIMindMapBtn').addEventListener('click', () => {
    const mindMapJson = aiMindMapModal.dataset.mindMap;
    if (!mindMapJson) return;
    let mindMap;
    try {
        mindMap = JSON.parse(mindMapJson);
    } catch (e) {
        showAlert('Failed to parse mind map JSON.', 'error');
        return;
    }
    if (mindMap.nodes && mindMap.connections) {
        // Defensive: Ensure all node ids are numbers and x/y are numbers
        mindMap.nodes.forEach(n => {
            n.id = parseInt(n.id, 10);
            n.x = typeof n.x === 'number' ? n.x : 0;
            n.y = typeof n.y === 'number' ? n.y : 0;
        });
        // Defensive: Ensure all connection from/to are numbers
        mindMap.connections.forEach(c => {
            c.from = parseInt(c.from, 10);
            c.to = parseInt(c.to, 10);
        });
        // --- FIX: Deep copy to break reference and allow editing ---
        nodes = mindMap.nodes.map(n => ({...n}));
        connections = mindMap.connections.map(c => ({...c}));
        nodeIdCounter = nodes.length ? Math.max(...nodes.map(n => n.id)) : 0;
        nodeIdCounter++;
        renderMindMap();
        showAlert('AI-generated mind map loaded!', 'success');
        aiMindMapModal.style.display = 'none';
    } else {
        showAlert('Invalid mind map format from AI.', 'error');
    }
});

// Initialize the application
document.addEventListener('DOMContentLoaded', init);

// Add panning logic:
let panX = 0;
let panY = 0;
let isPanning = false;
let panStartX = 0;
let panStartY = 0;

mindmapContainer.addEventListener('mousedown', (e) => {
    if (e.target === mindmapContainer) {
        isPanning = true;
        mindmapContainer.classList.add('grabbing');
        panStartX = e.clientX - panX;
        panStartY = e.clientY - panY;
    }
});
document.addEventListener('mousemove', (e) => {
    if (isPanning) {
        panX = e.clientX - panStartX;
        panY = e.clientY - panStartY;
        mindmapCanvas.style.transform = `translate(${panX}px, ${panY}px)`;
    }
});
document.addEventListener('mouseup', () => {
    isPanning = false;
    mindmapContainer.classList.remove('grabbing');
});