<?php
session_start();
require_once 'config.php';

// --- User authentication check based on lol.php session ---
if (!isset($_SESSION['lol_user_id'])) {
    header('Location: profile.php');
    exit;
}
$user_id = $_SESSION['lol_user_id'];

// --- Fetch user info from database ---
$stmt = $conn->prepare("SELECT username, email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile & Progress</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #0001; padding: 32px; }
        h1 { color: #5c6bc0; }
        .profile-info { margin-bottom: 24px; }
        .level-bar-bg { background: #eee; border-radius: 8px; height: 24px; width: 100%; margin: 18px 0; }
        .level-bar { background: #5c6bc0; height: 100%; border-radius: 8px; transition: width 0.5s; }
        .level-label { font-weight: bold; color: #3949ab; }
        .progress-list { margin: 18px 0; }
        .progress-list li { margin-bottom: 8px; }
    </style>
</head>
<body>
<div class="container">
    <h1>User Profile</h1>
    <div class="profile-info">
        <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
    </div>
    <div>
        <span class="level-label" id="levelLabel">Level 1</span>
        <div class="level-bar-bg">
            <div class="level-bar" id="levelBar" style="width:10%"></div>
        </div>
        <div id="levelProgressText"></div>
    </div>
    <h3>Your Progress</h3>
    <ul class="progress-list" id="progressList">
        <!-- Filled by JS -->
    </ul>
</div>
<script>
// --- Progress/Level Calculation ---
// For demo: use localStorage for mnemonics/quizzes/flashcards/notes/mindmap/pomodoro (adapt to server if needed)
function getProgressData() {
    let mnemonics = 0, quizzes = 0, flashcards = 0, notes = 0, mindmaps = 0, pomodoros = 0;
    // Mnemonics
    try {
        const mnemonicsData = JSON.parse(localStorage.getItem('pdfMnemonics') || '{}');
        for (const pdfId in mnemonicsData) {
            mnemonics += Array.isArray(mnemonicsData[pdfId]) ? mnemonicsData[pdfId].length : 0;
        }
    } catch {}
    // Quizzes (count as completed if user has submitted at least once)
    try {
        const quizData = JSON.parse(localStorage.getItem('quizCompletions') || '{}');
        for (const pdfId in quizData) {
            if (quizData[pdfId]) quizzes++;
        }
    } catch {}
    // Flashcards (count total created)
    try {
        const flashcardData = JSON.parse(localStorage.getItem('pdfFlashcards') || '{}');
        for (const pdfId in flashcardData) {
            flashcards += Array.isArray(flashcardData[pdfId]) ? flashcardData[pdfId].length : 0;
        }
    } catch {}
    // Notes (count total notes saved)
    try {
        const notesData = JSON.parse(localStorage.getItem('pdfNotes') || '{}');
        for (const pdfId in notesData) {
            if (notesData[pdfId] && notesData[pdfId].trim().length > 0) notes++;
        }
    } catch {}
    // Mindmaps (count total mindmaps saved)
    try {
        const mindmapData = JSON.parse(localStorage.getItem('mindMaps') || '{}');
        for (const mapId in mindmapData) {
            if (mindmapData[mapId] && mindmapData[mapId].nodes && mindmapData[mapId].nodes.length > 0) mindmaps++;
        }
    } catch {}
    // Pomodoro (count total pomodoro sessions completed)
    try {
        pomodoros = parseInt(localStorage.getItem('pomodoroSessions') || '0', 10);
        if (isNaN(pomodoros)) pomodoros = 0;
    } catch {}
    return { mnemonics, quizzes, flashcards, notes, mindmaps, pomodoros };
}

// Level system: 1-10, each level requires 5 completions (all activities)
function calculateLevel(mnemonics, quizzes, flashcards, notes, mindmaps, pomodoros) {
    const total = mnemonics + quizzes + flashcards + notes + mindmaps + pomodoros;
    const level = Math.min(10, Math.floor(total / 5) + 1);
    const progress = Math.min(1, (total % 5) / 5);
    return { level, progress, total };
}

function renderProfileProgress() {
    const { mnemonics, quizzes, flashcards, notes, mindmaps, pomodoros } = getProgressData();
    const { level, progress, total } = calculateLevel(mnemonics, quizzes, flashcards, notes, mindmaps, pomodoros);
    document.getElementById('levelLabel').textContent = `Level ${level}`;
    document.getElementById('levelBar').style.width = `${10 * level + progress * 10}%`;
    document.getElementById('levelProgressText').textContent =
        `Progress: ${total} completions (${mnemonics} mnemonics, ${quizzes} quizzes, ${flashcards} flashcards, ${notes} notes, ${mindmaps} mindmaps, ${pomodoros} pomodoro sessions)`;
    document.getElementById('progressList').innerHTML = `
        <li>Mnemonics created: <strong>${mnemonics}</strong></li>
        <li>Quizzes completed: <strong>${quizzes}</strong></li>
        <li>Flashcards created: <strong>${flashcards}</strong></li>
        <li>Notes saved: <strong>${notes}</strong></li>
        <li>Mindmaps created: <strong>${mindmaps}</strong></li>
        <li>Pomodoro sessions completed: <strong>${pomodoros}</strong></li>
        <li>Level: <strong>${level}</strong> / 10</li>
    `;
}

// Track completions (call this in your other pages after activity submit)
window.addEventListener('storage', renderProfileProgress);
renderProfileProgress();
</script>
</body>
</html>
