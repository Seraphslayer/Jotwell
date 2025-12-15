 // DOM Elements
        const fileList = document.getElementById('fileList');
        const pdfViewer = document.getElementById('pdfViewer');
        const pdfInfo = document.getElementById('pdfInfo');
        const pdfTitle = document.getElementById('pdfTitle');
        const downloadPdfBtn = document.getElementById('downloadPdfBtn');
        const notesTextarea = document.getElementById('notes');
        const saveNotesBtn = document.getElementById('saveNotesBtn');
        const exportNotesBtn = document.getElementById('exportNotesBtn');
        const createFlashcardsFromNotesBtn = document.getElementById('createFlashcardsFromNotesBtn');
        const alert = document.getElementById('alert');
        
        // Flashcard elements
        const deckSelect = document.getElementById('deckSelect');
        const addDeckBtn = document.getElementById('addDeckBtn');
        const addDeckModal = document.getElementById('addDeckModal');
        const newDeckName = document.getElementById('newDeckName');
        const cancelAddDeckBtn = document.getElementById('cancelAddDeckBtn');
        const confirmAddDeckBtn = document.getElementById('confirmAddDeckBtn');
        const currentFlashcard = document.getElementById('currentFlashcard');
        const cardQuestion = document.getElementById('cardQuestion');
        const cardAnswer = document.getElementById('cardAnswer');
        const startStudyBtn = document.getElementById('startStudyBtn');
        const flipCardBtn = document.getElementById('flipCardBtn');
        const nextCardBtn = document.getElementById('nextCardBtn');
        const shuffleBtn = document.getElementById('shuffleBtn');
        const cardCounter = document.getElementById('cardCounter');
        const studyProgress = document.getElementById('studyProgress');
        const flashcardForm = document.getElementById('flashcardForm');
        const newCardQuestion = document.getElementById('newCardQuestion');
        const newCardAnswer = document.getElementById('newCardAnswer');
        const tagsInput = document.getElementById('cardTags');
        const flashcardList = document.getElementById('flashcardList');
        const difficultyRating = document.getElementById('difficultyRating');
        const difficultyBtns = document.querySelectorAll('.difficulty-btn');
        
        // Notes to flashcards modal elements
        const notesToFlashcardsModal = document.getElementById('notesToFlashcardsModal');
        const notesFormat = document.getElementById('notesFormat');
        const targetDeck = document.getElementById('targetDeck');
        const previewCards = document.getElementById('previewCards');
        const cancelNotesToFlashcardsBtn = document.getElementById('cancelNotesToFlashcardsBtn');
        const confirmNotesToFlashcardsBtn = document.getElementById('confirmNotesToFlashcardsBtn');
        
        // PDF viewer variables
        let pdfUrl = '';
        let currentPdfId = '';
        let currentPdfName = '';
        let notesData = {};
        
        // Flashcard variables
        let flashcards = {};
        let decks = ['default'];
        let currentDeck = 'default';
        let studyCards = [];
        let currentCardIndex = -1;
        let isCardFlipped = false;
        
        // Initialize the app
        function init() {
            setupEventListeners();
            loadNotes();
            loadFlashcards();
            loadDecks();
            updateFlashcardList();
        }
        
        // Set up event listeners
        function setupEventListeners() {
            // Notes
            saveNotesBtn.addEventListener('click', saveNotes);
            if (exportNotesBtn) {
                exportNotesBtn.addEventListener('click', exportNotes);
            }
            
            // Create flashcards from notes
            createFlashcardsFromNotesBtn.addEventListener('click', showNotesToFlashcardsModal);
            notesFormat.addEventListener('change', updatePreview);
            cancelNotesToFlashcardsBtn.addEventListener('click', hideNotesToFlashcardsModal);
            confirmNotesToFlashcardsBtn.addEventListener('click', createFlashcardsFromNotes);
            
            // Flashcard study controls
            startStudyBtn.addEventListener('click', startStudying);
            flipCardBtn.addEventListener('click', flipCard);
            nextCardBtn.addEventListener('click', nextCard);
            shuffleBtn.addEventListener('click', shuffleCards);
            
            // Click to flip card
            currentFlashcard.addEventListener('click', function() {
                if (currentCardIndex >= 0) {
                    flipCard();
                }
            });
            
            // Flashcard form submission
            flashcardForm.addEventListener('submit', function(e) {
                e.preventDefault();
                addFlashcard();
            });
            
            // Tag input
            setupTagsInput();
            
            // Difficulty rating
            difficultyBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    rateCardDifficulty(this.getAttribute('data-value'));
                });
            });
            
            // Deck management
            deckSelect.addEventListener('change', function() {
                switchDeck(this.value);
            });
            
            addDeckBtn.addEventListener('click', showAddDeckModal);
            cancelAddDeckBtn.addEventListener('click', hideAddDeckModal);
            confirmAddDeckBtn.addEventListener('click', addNewDeck);
            
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
            currentPdfPath = pdfPath;  // Track the current PDF path
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
        // Load flashcards from local storage
function loadFlashcards() {
    const savedFlashcards = localStorage.getItem('flashcards');
    if (savedFlashcards) {
        flashcards = JSON.parse(savedFlashcards);
    } else {
        flashcards = {
            default: []
        };
    }
}

// Load decks from local storage
function loadDecks() {
    const savedDecks = localStorage.getItem('flashcardDecks');
    if (savedDecks) {
        decks = JSON.parse(savedDecks);
        updateDeckOptions();
    } else {
        decks = ['default'];
        localStorage.setItem('flashcardDecks', JSON.stringify(decks));
    }
}

// Update deck dropdown options
function updateDeckOptions() {
    deckSelect.innerHTML = '';
    targetDeck.innerHTML = ''; // For the notes to flashcards modal
    
    decks.forEach(deck => {
        const option = document.createElement('option');
        option.value = deck;
        option.textContent = deck;
        deckSelect.appendChild(option);
        
        const targetOption = option.cloneNode(true);
        targetDeck.appendChild(targetOption);
    });
    
    deckSelect.value = currentDeck;
}

// Switch current deck
function switchDeck(deckName) {
    currentDeck = deckName;
    updateFlashcardList();
    resetStudySession();
}

// Show add deck modal
function showAddDeckModal() {
    addDeckModal.style.display = 'block';
    newDeckName.value = '';
    newDeckName.focus();
}

// Hide add deck modal
function hideAddDeckModal() {
    addDeckModal.style.display = 'none';
}

// Add new deck
function addNewDeck() {
    const deckName = newDeckName.value.trim();
    
    if (!deckName) {
        showAlert('Please enter a deck name', 'error');
        return;
    }
    
    if (decks.includes(deckName)) {
        showAlert('Deck with this name already exists', 'error');
        return;
    }
    
    decks.push(deckName);
    localStorage.setItem('flashcardDecks', JSON.stringify(decks));
    
    // Initialize empty array for new deck
    if (!flashcards[deckName]) {
        flashcards[deckName] = [];
        saveFlashcards();
    }
    
    updateDeckOptions();
    hideAddDeckModal();
    
    // Switch to new deck
    deckSelect.value = deckName;
    switchDeck(deckName);
    
    showAlert(`Deck "${deckName}" created successfully`, 'success');
}

// Setup tags input functionality
function setupTagsInput() {
    const input = tagsInput.querySelector('input');
    
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && this.value.trim() !== '') {
            e.preventDefault();
            
            const tag = document.createElement('div');
            tag.className = 'tag';
            tag.innerHTML = `
                ${this.value.trim()}
                <span class="tag-remove">×</span>
            `;
            
            tag.querySelector('.tag-remove').addEventListener('click', function() {
                tag.remove();
            });
            
            tagsInput.insertBefore(tag, this);
            this.value = '';
        }
    });
}

// Get all tags from the tags input
function getTagsFromInput() {
    const tags = [];
    const tagElements = tagsInput.querySelectorAll('.tag');
    
    tagElements.forEach(tag => {
        tags.push(tag.textContent.trim().replace('×', ''));
    });
    
    return tags;
}

// Clear tags input
function clearTagsInput() {
    const tagElements = tagsInput.querySelectorAll('.tag');
    tagElements.forEach(tag => tag.remove());
}

// Add a new flashcard
function addFlashcard() {
    const question = newCardQuestion.value.trim();
    const answer = newCardAnswer.value.trim();
    const tags = getTagsFromInput();
    
    if (!question || !answer) {
        showAlert('Please fill in both question and answer fields', 'error');
        return;
    }
    
    const card = {
        id: Date.now().toString(),
        question: question,
        answer: answer,
        tags: tags,
        difficulty: 'medium',
        lastReviewed: null,
        reviewCount: 0
    };
    
    if (!flashcards[currentDeck]) {
        flashcards[currentDeck] = [];
    }
    
    flashcards[currentDeck].push(card);
    saveFlashcards();
    updateFlashcardList();
    
    // Clear form
    newCardQuestion.value = '';
    newCardAnswer.value = '';
    clearTagsInput();
    
    showAlert('Flashcard added successfully', 'success');
    
    // Reset study session if active
    if (currentCardIndex !== -1) {
        resetStudySession();
    }
}

// Save flashcards to local storage
function saveFlashcards() {
    localStorage.setItem('flashcards', JSON.stringify(flashcards));
}

// Update the list of flashcards in the UI
function updateFlashcardList() {
    flashcardList.innerHTML = '';
    
    if (!flashcards[currentDeck] || flashcards[currentDeck].length === 0) {
        flashcardList.innerHTML = '<p>No flashcards in this deck. Create some!</p>';
        return;
    }
    
    flashcards[currentDeck].forEach((card, index) => {
        const cardItem = document.createElement('div');
        cardItem.className = 'flashcard-item';
        
        const questionPreview = card.question.length > 30 ? 
                               card.question.substring(0, 30) + '...' : 
                               card.question;
        
        cardItem.innerHTML = `
            <div>
                <strong>${questionPreview}</strong>
                ${card.tags.length > 0 ? 
                  `<div style="font-size: 0.8rem; color: #666;">
                    Tags: ${card.tags.join(', ')}
                   </div>` : 
                  ''}
            </div>
            <div class="flashcard-actions">
                <button class="btn btn-secondary btn-sm edit-card-btn" data-index="${index}">Edit</button>
                <button class="btn btn-danger btn-sm delete-card-btn" data-index="${index}">Delete</button>
            </div>
        `;
        
        flashcardList.appendChild(cardItem);
        
        // Add event listeners for edit and delete buttons
        cardItem.querySelector('.edit-card-btn').addEventListener('click', () => editFlashcard(index));
        cardItem.querySelector('.delete-card-btn').addEventListener('click', () => deleteFlashcard(index));
    });
}

// Edit a flashcard
function editFlashcard(index) {
    const card = flashcards[currentDeck][index];
    
    // Fill form with card data
    newCardQuestion.value = card.question;
    newCardAnswer.value = card.answer;
    
    // Clear existing tags and add card tags
    clearTagsInput();
    card.tags.forEach(tag => {
        const tagElement = document.createElement('div');
        tagElement.className = 'tag';
        tagElement.innerHTML = `
            ${tag}
            <span class="tag-remove">×</span>
        `;
        
        tagElement.querySelector('.tag-remove').addEventListener('click', function() {
            tagElement.remove();
        });
        
        tagsInput.insertBefore(tagElement, tagsInput.querySelector('input'));
    });
    
    // Delete original card and let user create updated version
    deleteFlashcard(index);
    
    // Scroll to form
    flashcardForm.scrollIntoView({ behavior: 'smooth' });
}

// Delete a flashcard
function deleteFlashcard(index) {
    if (confirm('Are you sure you want to delete this flashcard?')) {
        flashcards[currentDeck].splice(index, 1);
        saveFlashcards();
        updateFlashcardList();
        
        // Reset study session if active
        if (currentCardIndex !== -1) {
            resetStudySession();
        }
        
        showAlert('Flashcard deleted successfully', 'success');
    }
}

// Start studying flashcards
function startStudying() {
    if (!flashcards[currentDeck] || flashcards[currentDeck].length === 0) {
        showAlert('No flashcards in this deck to study', 'error');
        return;
    }
    
    // Reset study session
    resetStudySession();
    
    // Clone the cards for study session
    studyCards = [...flashcards[currentDeck]];
    
    // Sort by difficulty or last reviewed date if needed
    // For now just shuffle them
    shuffleCards();
    
    // Start with first card
    currentCardIndex = 0;
    displayCurrentCard();
    
    // Enable buttons
    flipCardBtn.disabled = false;
    nextCardBtn.disabled = false;
    shuffleBtn.disabled = false;
    
    showAlert('Study session started', 'success');
}

// Reset the study session
function resetStudySession() {
    studyCards = [];
    currentCardIndex = -1;
    isCardFlipped = false;
    
    currentFlashcard.classList.remove('flipped');
    cardQuestion.textContent = 'Click "Start Studying" to begin';
    cardAnswer.textContent = 'The answer will appear here';
    cardCounter.textContent = '0/0';
    studyProgress.style.width = '0%';
    
    flipCardBtn.disabled = true;
    nextCardBtn.disabled = true;
    shuffleBtn.disabled = true;
    
    difficultyRating.style.display = 'none';
}

// Display the current card
function displayCurrentCard() {
    if (currentCardIndex < 0 || currentCardIndex >= studyCards.length) {
        return;
    }
    
    const card = studyCards[currentCardIndex];
    
    // Update card content
    cardQuestion.textContent = card.question;
    cardAnswer.textContent = card.answer;
    
    // Make sure card is not flipped
    isCardFlipped = false;
    currentFlashcard.classList.remove('flipped');
    
    // Update progress
    cardCounter.textContent = `${currentCardIndex + 1}/${studyCards.length}`;
    studyProgress.style.width = `${((currentCardIndex + 1) / studyCards.length) * 100}%`;
    
    // Hide difficulty rating
    difficultyRating.style.display = 'none';
}

// Flip the current card
function flipCard() {
    if (currentCardIndex < 0) return;
    
    isCardFlipped = !isCardFlipped;
    
    if (isCardFlipped) {
        currentFlashcard.classList.add('flipped');
        // Show difficulty rating when card is flipped to answer side
        difficultyRating.style.display = 'block';
    } else {
        currentFlashcard.classList.remove('flipped');
        difficultyRating.style.display = 'none';
    }
}

// Move to the next card
function nextCard() {
    if (studyCards.length === 0) return;
    
    // Update current card with difficulty if rated
    const currentCard = studyCards[currentCardIndex];
    
    // Move to next card
    currentCardIndex++;
    
    // Check if we've gone through all cards
    if (currentCardIndex >= studyCards.length) {
        showAlert('You have reviewed all cards in this deck!', 'success');
        // Option to restart or end session
        if (confirm('You have reviewed all cards. Start again?')) {
            currentCardIndex = 0;
        } else {
            resetStudySession();
            return;
        }
    }
    
    displayCurrentCard();
}

// Rate card difficulty
function rateCardDifficulty(difficulty) {
    if (currentCardIndex < 0 || currentCardIndex >= studyCards.length) return;
    
    const cardId = studyCards[currentCardIndex].id;
    
    // Find the card in the original deck and update it
    const deckCards = flashcards[currentDeck];
    const cardIndex = deckCards.findIndex(card => card.id === cardId);
    
    if (cardIndex !== -1) {
        deckCards[cardIndex].difficulty = difficulty;
        deckCards[cardIndex].lastReviewed = new Date().toISOString();
        deckCards[cardIndex].reviewCount++;
        
        saveFlashcards();
    }
    
    // Highlight selected difficulty
    difficultyBtns.forEach(btn => {
        btn.classList.remove('selected');
        if (btn.getAttribute('data-value') === difficulty) {
            btn.classList.add('selected');
        }
    });
    
    // Move to next card after a short delay
    setTimeout(nextCard, 500);
}

// Shuffle the study cards
function shuffleCards() {
    if (studyCards.length <= 1) return;
    
    // Fisher-Yates shuffle algorithm
    for (let i = studyCards.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [studyCards[i], studyCards[j]] = [studyCards[j], studyCards[i]];
    }
    
    // Reset to first card
    currentCardIndex = 0;
    displayCurrentCard();
    
    showAlert('Cards shuffled', 'success');
}

// Show modal for creating flashcards from notes
function showNotesToFlashcardsModal() {
    if (!notesTextarea.value.trim()) {
        showAlert('No notes to create flashcards from', 'error');
        return;
    }
    
    notesToFlashcardsModal.style.display = 'block';
    updatePreview();
}

// Hide modal for creating flashcards from notes
function hideNotesToFlashcardsModal() {
    notesToFlashcardsModal.style.display = 'none';
}

// Update preview based on selected format
function updatePreview() {
    const notes = notesTextarea.value.trim();
    const format = notesFormat.value;
    
    if (!notes) {
        previewCards.innerHTML = '<p>No notes to preview</p>';
        return;
    }
    
    let previewHTML = '';
    let cards = [];
    
    switch (format) {
        case 'qa':
            // Split by double line breaks to get Q/A pairs
            const pairs = notes.split(/\n\s*\n/);
            for (let i = 0; i < pairs.length; i += 2) {
                if (pairs[i] && pairs[i+1]) {
                    cards.push({
                        question: pairs[i].trim(),
                        answer: pairs[i+1].trim()
                    });
                }
            }
            break;
            
        case 'bullet':
            // Each bullet point becomes a card
            const bullets = notes.match(/^[\s-]*(.+)$/gm);
            if (bullets) {
                bullets.forEach(bullet => {
                    const cleanBullet = bullet.replace(/^[\s-]*/, '').trim();
                    if (cleanBullet) {
                        cards.push({
                            question: cleanBullet,
                            answer: "Fill in the answer during review"
                        });
                    }
                });
            }
            break;
            
        case 'term':
            // Term:Definition format
            const lines = notes.split('\n');
            lines.forEach(line => {
                const parts = line.split(':');
                if (parts.length >= 2) {
                    const term = parts[0].trim();
                    const definition = parts.slice(1).join(':').trim();
                    if (term && definition) {
                        cards.push({
                            question: term,
                            answer: definition
                        });
                    }
                }
            });
            break;
            
        case 'manual':
            // Show instructions for manual format
            previewHTML = `
                <p>Manual format: You'll need to edit each card after creation.</p>
                <p>Each paragraph will become a separate card with the first sentence as the question and the rest as the answer.</p>
            `;
            
            // Split by paragraphs
            const paragraphs = notes.split(/\n\s*\n/);
            paragraphs.forEach(para => {
                if (para.trim()) {
                    // First sentence as question, rest as answer
                    const firstSentenceMatch = para.match(/^(.*?[.!?])\s/);
                    if (firstSentenceMatch) {
                        const firstSentence = firstSentenceMatch[1];
                        const rest = para.substring(firstSentence.length).trim();
                        cards.push({
                            question: firstSentence,
                            answer: rest || "Fill in the answer during review"
                        });
                    } else {
                        // If no sentence end found, use whole paragraph as question
                        cards.push({
                            question: para.trim(),
                            answer: "Fill in the answer during review"
                        });
                    }
                }
            });
            break;
    }
    
    // Generate preview HTML if not already set
    if (!previewHTML) {
        if (cards.length === 0) {
            previewHTML = '<p>Could not generate any cards with the current format. Try a different format.</p>';
        } else {
            previewHTML = `<p>Generated ${cards.length} cards:</p><ul>`;
            cards.slice(0, 5).forEach(card => {
                previewHTML += `
                    <li>
                        <strong>Front:</strong> ${card.question.length > 50 ? card.question.substring(0, 50) + '...' : card.question}<br>
                        <strong>Back:</strong> ${card.answer.length > 50 ? card.answer.substring(0, 50) + '...' : card.answer}
                    </li>
                `;
            });
            if (cards.length > 5) {
                previewHTML += `<li>...and ${cards.length - 5} more</li>`;
            }
            previewHTML += '</ul>';
        }
    }
    
    previewCards.innerHTML = previewHTML;
    
    // Store cards in the modal for later use
    notesToFlashcardsModal.cards = cards;
}

// Create flashcards from notes
function createFlashcardsFromNotes() {
    const cards = notesToFlashcardsModal.cards || [];
    const selectedDeck = targetDeck.value;
    
    if (cards.length === 0) {
        showAlert('No valid flashcards could be created', 'error');
        return;
    }
    
    // Ensure the deck exists
    if (!flashcards[selectedDeck]) {
        flashcards[selectedDeck] = [];
    }
    
    // Add the cards to the deck
    cards.forEach(card => {
        flashcards[selectedDeck].push({
            id: Date.now().toString() + Math.random().toString(36).substring(2, 9),
            question: card.question,
            answer: card.answer,
            tags: ['auto-generated'],
            difficulty: 'medium',
            lastReviewed: null,
            reviewCount: 0
        });
    });
    
    saveFlashcards();
    
    // Switch to the target deck
    if (currentDeck !== selectedDeck) {
        deckSelect.value = selectedDeck;
        switchDeck(selectedDeck);
    } else {
        updateFlashcardList();
    }
    
    hideNotesToFlashcardsModal();
    showAlert(`Created ${cards.length} flashcards in deck "${selectedDeck}"`, 'success');
}

// Initialize the application when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', init);
// OpenAI Flashcard Generation Variables
        const aiFlashcardModal = document.getElementById('aiFlashcardModal');
        const geminiApiKeyInput = document.getElementById('geminiApiKey');
        const aiPromptType = document.getElementById('aiPromptType');
        const aiDetailLevel = document.getElementById('aiDetailLevel');
        const generateAIFlashcardBtn = document.getElementById('generateAIFlashcardBtn');
        const saveAIFlashcardBtn = document.getElementById('saveAIFlashcardBtn');
        const cancelAIFlashcardBtn = document.getElementById('cancelAIFlashcardBtn');
        const aiGeneratedFlashcards = document.getElementById('aiGeneratedFlashcards');
        const aiLoadingSpinner = document.getElementById('aiLoadingSpinner');
        
        // Add AI Flashcard Generation to the main sidebar
        const aiFlashcardSection = document.createElement('div');
        aiFlashcardSection.className = 'panel';
        aiFlashcardSection.innerHTML = `
            <h2>AI Flashcard Generator</h2>
            <button id="openAIFlashcardModal" class="btn btn-primary" style="width: 100%;">
                Generate AI Flashcards from PDF
            </button>
        `;
        
        // Append to the side panel
        const sidePanel = document.querySelector('.side-panel');
        sidePanel.appendChild(aiFlashcardSection);
        
        // Event Listeners for AI Flashcard Modal
        document.getElementById('openAIFlashcardModal').addEventListener('click', () => {
            if (!currentPdfId) {
                showAlert('Please select a PDF first', 'error');
                return;
            }
            aiFlashcardModal.style.display = 'block';
        });
        
        cancelAIFlashcardBtn.addEventListener('click', () => {
            aiFlashcardModal.style.display = 'none';
        });
        
        // --- PDF TO TEXT FUNCTION (for Gemini Vision input) ---
        async function getPDFText() {
            if (!currentPdfPath) {
                throw new Error('No PDF currently loaded');
            }
            let pdfPath = currentPdfPath;
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
        // --- END PDF TO TEXT FUNCTION ---
        
        // Update AI Flashcard Generation to use text input for Gemini Vision
        // Replace the generateAIFlashcardBtn click event
        
        generateAIFlashcardBtn.addEventListener('click', async () => {
            const apiKey = geminiApiKeyInput.value.trim();
            if (!apiKey) {
                showAlert('Please enter your Gemini API Key', 'error');
                return;
            }
            if (!currentPdfId || !currentPdfPath) {
                showAlert('No PDF selected', 'error');
                return;
            }
            aiLoadingSpinner.style.display = 'block';
            aiGeneratedFlashcards.innerHTML = '';
            saveAIFlashcardBtn.style.display = 'none';
            try {
                const pdfText = await getPDFText();
                const promptType = aiPromptType.value;
                const detailLevel = aiDetailLevel.value;
                const prompt = `Generate flashcards in JSON format from the following PDF text. Focus on ${promptType} at a ${detailLevel} level. Output only valid JSON array of objects with 'question' and 'answer' fields. Do not include any meta or unrelated content.\n\nPDF Text:\n${pdfText}`;
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
                if (!data.candidates || !data.candidates[0] || !data.candidates[0].content || !data.candidates[0].content.parts) {
                    aiGeneratedFlashcards.innerHTML = '<p style="color:#b00;">No flashcards generated. Please try again or check your API key.</p>';
                    return;
                }
                let flashcardsJson = '';
                for (const part of data.candidates[0].content.parts) {
                    if (part.text) {
                        flashcardsJson += part.text;
                    }
                }
                let flashcardsArr = [];
                try {
                    flashcardsArr = JSON.parse(flashcardsJson.match(/\[.*\]/s)[0]);
                } catch (e) {
                    aiGeneratedFlashcards.innerHTML = '<p style="color:#b00;">Failed to parse flashcards. Try again or check the prompt/API key.</p>';
                    return;
                }
                if (!Array.isArray(flashcardsArr) || flashcardsArr.length === 0) {
                    aiGeneratedFlashcards.innerHTML = '<p style="color:#b00;">No flashcards found in the response.</p>';
                    return;
                }
                aiGeneratedFlashcards.setAttribute('data-cards', JSON.stringify(flashcardsArr));
                aiGeneratedFlashcards.innerHTML = '<ul>' + flashcardsArr.map(card => `<li><strong>Q:</strong> ${card.question}<br><strong>A:</strong> ${card.answer}</li>`).join('') + '</ul>';
                saveAIFlashcardBtn.style.display = 'block';
            } catch (error) {
                aiGeneratedFlashcards.innerHTML = '<p style="color:#b00;">Error: ' + error.message + '</p>';
                console.error(error);
            } finally {
                aiLoadingSpinner.style.display = 'none';
            }
        });
        
        // Remove or update getPDFImageBase64() calls if you no longer need image extraction.
        saveAIFlashcardBtn.addEventListener('click', () => {
            // Get the generated flashcards from the data attribute
            let cards = [];
            try {
                cards = JSON.parse(aiGeneratedFlashcards.getAttribute('data-cards'));
            } catch (e) {
                showAlert('Failed to parse generated flashcards. Please generate again.', 'error');
                return;
            }
            if (!Array.isArray(cards) || cards.length === 0) {
                showAlert('No flashcards to save.', 'error');
                return;
            }
            // Save to the currently selected deck
            const selectedDeck = deckSelect ? deckSelect.value : 'default';
            if (!flashcards[selectedDeck]) {
                flashcards[selectedDeck] = [];
            }
            cards.forEach(card => {
                flashcards[selectedDeck].push({
                    id: Date.now().toString() + Math.random().toString(36).substring(2, 9),
                    question: card.question,
                    answer: card.answer,
                    tags: ['AI-generated'],
                    difficulty: 'medium',
                    lastReviewed: null,
                    reviewCount: 0
                });
            });
            saveFlashcards();
            updateFlashcardList();
            aiFlashcardModal.style.display = 'none';
            showAlert(`Saved ${cards.length} AI-generated flashcards to deck "${selectedDeck}"`, 'success');
        });