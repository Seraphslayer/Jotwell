<?php
session_start();
require_once 'config.php';

// Fetch PDFs from database
function getPDFFiles() {
    global $conn;
    $files = [];
    $query = "SELECT pdf_id, title, file_path, upload_date FROM tbl_pdfs ORDER BY upload_date DESC";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $files[] = $row;
        }
    }
    return $files;
}
$pdfFiles = getPDFFiles();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI PDF Quiz Generator</title>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; }
        .container { max-width: 700px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #0001; padding: 32px; }
        h1 { color: #5c6bc0; }
        .form-group { margin-bottom: 18px; }
        label { font-weight: bold; }
        select, input[type="text"] { width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #bbb; }
        button { padding: 10px 24px; border: none; border-radius: 4px; background: #5c6bc0; color: #fff; font-size: 1rem; cursor: pointer; }
        button:disabled { background: #bbb; }
        .alert { margin: 18px 0; padding: 12px; border-radius: 4px; color: #fff; display: none; }
        .alert-success { background: #66bb6a; }
        .alert-error { background: #ef5350; }
        .quiz-block { margin-top: 32px; }
        .quiz-question { margin-bottom: 18px; padding: 14px; background: #f7f7fa; border-radius: 6px; }
        .quiz-answer { margin-top: 8px; display: none; color: #263238; background: #e3f2fd; padding: 8px; border-radius: 4px; }
        .show-answer-btn { margin-top: 8px; background: #3949ab; color: #fff; border: none; border-radius: 4px; padding: 6px 14px; cursor: pointer; }
    </style>
</head>
<body>
<div class="container">
    <h1>AI PDF Quiz Generator</h1>
    <div class="form-group">
        <label for="pdfSelect">Select PDF:</label>
        <select id="pdfSelect">
            <option value="">-- Choose a PDF --</option>
            <?php foreach ($pdfFiles as $pdf): ?>
                <option value="<?= htmlspecialchars($pdf['file_path']) ?>" data-title="<?= htmlspecialchars($pdf['title']) ?>">
                    <?= htmlspecialchars($pdf['title']) ?> (<?= date('Y-m-d', strtotime($pdf['upload_date'])) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="geminiApiKey">Gemini API Key:</label>
        <input type="text" id="geminiApiKey" placeholder="Enter your Gemini API Key">
    </div>
    <button id="generateQuizBtn">Generate Quiz</button>
    <div class="alert" id="alert"></div>
    <div class="quiz-block" id="quizBlock"></div>
</div>
<script>
const pdfSelect = document.getElementById('pdfSelect');
const geminiApiKey = document.getElementById('geminiApiKey');
const generateQuizBtn = document.getElementById('generateQuizBtn');
const alertBox = document.getElementById('alert');
const quizBlock = document.getElementById('quizBlock');

function showAlert(msg, type='success') {
    alertBox.textContent = msg;
    alertBox.className = 'alert alert-' + (type === 'error' ? 'error' : 'success');
    alertBox.style.display = 'block';
    setTimeout(() => { alertBox.style.display = 'none'; }, 3500);
}

// Fetch PDF text from backend
async function getPDFText(pdfPath) {
    if (!pdfPath.startsWith('uploads/')) {
        pdfPath = pdfPath.replace(/^\/+/, '');
        pdfPath = 'uploads/' + pdfPath;
    }
    const url = `pdf-parser.php?path=${encodeURIComponent(pdfPath)}&t=${Date.now()}`;
    const res = await fetch(url);
    const contentType = res.headers.get('Content-Type');
    if (!res.ok || !contentType || !contentType.includes('application/json')) {
        let errorText = await res.text();
        throw new Error('Failed to fetch PDF text. ' + errorText);
    }
    const data = await res.json();
    if (!data.text) throw new Error('No text extracted from PDF.');
    return data.text;
}

// Generate the quiz with Gemini
generateQuizBtn.addEventListener('click', async () => {
    quizBlock.innerHTML = '';
    const pdfPath = pdfSelect.value;
    const pdfTitle = pdfSelect.options[pdfSelect.selectedIndex]?.getAttribute('data-title') || '';
    const apiKey = geminiApiKey.value.trim();
    if (!pdfPath) { showAlert('Please select a PDF.', 'error'); return; }
    if (!apiKey) { showAlert('Please enter your Gemini API Key.', 'error'); return; }
    showAlert('Generating quiz, please wait...');
    generateQuizBtn.disabled = true;
    try {
        const pdfText = await getPDFText(pdfPath);
        const prompt = `Create a quiz in JSON format based on the following PDF text. 
The JSON must have a "questions" array, each with "question" and "answer" fields.
Questions should be short-answer or multiple-choice, and cover key points from the PDF.
Output only valid JSON. Do not include explanations or meta content.

PDF Title: ${pdfTitle}
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
        let quizJson = '';
        if (data.candidates && data.candidates[0] && data.candidates[0].content && data.candidates[0].content.parts) {
            for (const part of data.candidates[0].content.parts) {
                if (part.text) quizJson += part.text;
            }
        }
        // Try to extract JSON block if Gemini returns markdown or explanations
        let quiz;
        try {
            const jsonMatch = quizJson.match(/```(?:json)?\s*([\s\S]*?)\s*```/i);
            let jsonToParse = jsonMatch ? jsonMatch[1] : quizJson;
            const firstBrace = jsonToParse.indexOf('{');
            const lastBrace = jsonToParse.lastIndexOf('}');
            if (firstBrace !== -1 && lastBrace !== -1) {
                jsonToParse = jsonToParse.substring(firstBrace, lastBrace + 1);
            }
            quiz = JSON.parse(jsonToParse);
        } catch (e) {
            quizBlock.innerHTML = `<p style="color:#b00;">Failed to parse quiz JSON. Try again or check your prompt.<br><br><small>Raw output:<br><pre>${quizJson.replace(/</g,"&lt;")}</pre></small></p>`;
            generateQuizBtn.disabled = false;
            return;
        }
        // Render quiz as answer form
        if (quiz.questions && quiz.questions.length) {
            let html = `<h2>Quiz: ${pdfTitle}</h2>`;
            html += `<form id="quizForm">`;
            quiz.questions.forEach((q, i) => {
                html += `
                    <div class="quiz-question">
                        <strong>Q${i+1}:</strong> ${q.question}
                        <br>
                        <input type="text" name="answer${i}" autocomplete="off" style="width:90%;margin-top:8px;" required>
                        <div class="quiz-answer" id="quiz-answer-${i}" style="display:none;">${marked.parseInline(q.answer || '')}</div>
                    </div>
                `;
            });
            html += `<button type="submit" class="show-answer-btn" style="margin-top:18px;">Submit Quiz</button>`;
            html += `</form>`;
            quizBlock.innerHTML = html;

            // Handle quiz submission
            document.getElementById('quizForm').onsubmit = function(e) {
                e.preventDefault();
                let score = 0;
                const total = quiz.questions.length;
                quiz.questions.forEach((q, i) => {
                    const userInput = this['answer'+i].value.trim().toLowerCase();
                    const correct = (q.answer || '').trim().toLowerCase();
                    // More precise: require at least 70% token overlap or exact match (ignoring punctuation/case)
                    let isCorrect = false;
                    if (userInput && correct) {
                        // Remove punctuation and extra spaces
                        const cleanUser = userInput.replace(/[^\w\s]|_/g, "").replace(/\s+/g, " ").trim();
                        const cleanCorrect = correct.replace(/[^\w\s]|_/g, "").replace(/\s+/g, " ").trim();
                        if (cleanUser === cleanCorrect) {
                            isCorrect = true;
                        } else {
                            // Tokenize and compare overlap
                            const userTokens = cleanUser.split(" ").filter(Boolean);
                            const correctTokens = cleanCorrect.split(" ").filter(Boolean);
                            const intersection = userTokens.filter(token => correctTokens.includes(token));
                            const overlap = intersection.length / Math.max(correctTokens.length, 1);
                            // Accept if at least 70% of the correct answer tokens are present and at least 2 tokens
                            if (overlap >= 0.7 && correctTokens.length > 1) {
                                isCorrect = true;
                            }
                        }
                    }
                    if (isCorrect) score++;
                    // Show answer
                    const ansDiv = document.getElementById('quiz-answer-'+i);
                    ansDiv.style.display = 'block';
                    if (isCorrect) {
                        ansDiv.innerHTML = `<span style="color:#388e3c;font-weight:bold;">Correct!</span> ` + ansDiv.innerHTML;
                    } else {
                        ansDiv.innerHTML = `<span style="color:#b71c1c;font-weight:bold;">Your answer: ${escapeHtml(userInput)}</span><br>` + ansDiv.innerHTML;
                    }
                });
                // Show score
                const result = document.createElement('div');
                result.innerHTML = `<h3 style="color:#3949ab;">Score: ${score} / ${total}</h3>`;
                quizBlock.appendChild(result);
                // Disable all inputs and button
                Array.from(this.elements).forEach(el => el.disabled = true);
            };
        } else {
            quizBlock.innerHTML = `<p>No questions generated. Try again or use a different PDF.</p>`;
        }
        showAlert('Quiz generated!', 'success');
    } catch (err) {
        quizBlock.innerHTML = `<p style="color:#b00;">${err.message}</p>`;
        showAlert('Failed to generate quiz.', 'error');
    }
    generateQuizBtn.disabled = false;
});

// Helper to escape HTML
function escapeHtml(text) {
    return text.replace(/[&<>"']/g, function(m) {
        return ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        })[m];
    });
}
</script>
</body>
</html>
