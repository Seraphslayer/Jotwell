document.addEventListener("DOMContentLoaded", function () {
    let timer;
    let timeLeft = 25 * 60;
    let isRunning = false;
    let sessionCount = 0;
    let currentMode = "pomodoro";

    const timerDisplay = document.getElementById("timerDisplay");
    const progressBar = document.getElementById("progressBar");
    const startBtn = document.getElementById("startTimerBtn");
    const pauseBtn = document.getElementById("pauseTimerBtn");
    const resetBtn = document.getElementById("resetTimerBtn");
    const statusText = document.getElementById("timerStatus");
    const sessionCounter = document.getElementById("sessionCounter");

    const pomodoroBtn = document.getElementById("pomodoroBtn");
    const shortBreakBtn = document.getElementById("shortBreakBtn");
    const longBreakBtn = document.getElementById("longBreakBtn");

    const pomodoroInput = document.getElementById("pomodoroTime");
    const shortBreakInput = document.getElementById("shortBreakTime");
    const longBreakInput = document.getElementById("longBreakTime");
    const sessionsBeforeLongBreakInput = document.getElementById("sessionsBeforeLongBreak");
    
    const saveSettingsBtn = document.getElementById("saveSettingsBtn");

    function updateDisplay() {
        const minutes = Math.floor(timeLeft / 60).toString().padStart(2, '0');
        const seconds = (timeLeft % 60).toString().padStart(2, '0');
        timerDisplay.textContent = `${minutes}:${seconds}`;
        const total = getDuration(currentMode) * 60;
        const percentage = 100 - (timeLeft / total) * 100;
        progressBar.style.width = `${percentage}%`;
    }

    function getDuration(mode) {
        switch (mode) {
            case "short":
                return parseInt(shortBreakInput.value);
            case "long":
                return parseInt(longBreakInput.value);
            default:
                return parseInt(pomodoroInput.value);
        }
    }

    function startTimer() {
        if (!isRunning) {
            isRunning = true;
            startBtn.disabled = true;
            pauseBtn.disabled = false;
            statusText.textContent = "Timer running...";

            timer = setInterval(() => {
                if (timeLeft > 0) {
                    timeLeft--;
                    updateDisplay();
                } else {
                    clearInterval(timer);
                    isRunning = false;
                    startBtn.disabled = false;
                    pauseBtn.disabled = true;

                    if (currentMode === "pomodoro") {
                        sessionCount++;
                        sessionCounter.textContent = `Completed sessions: ${sessionCount}`;
                        const sessionsBeforeLong = parseInt(sessionsBeforeLongBreakInput.value);
                        currentMode = (sessionCount % sessionsBeforeLong === 0) ? "long" : "short";
                    } else {
                        currentMode = "pomodoro";
                    }

                    timeLeft = getDuration(currentMode) * 60;
                    updateDisplay();
                    statusText.textContent = "Time\'s up! Ready for next session.";
                }
            }, 1000);
        }
    }

    function pauseTimer() {
        if (isRunning) {
            clearInterval(timer);
            isRunning = false;
            startBtn.disabled = false;
            pauseBtn.disabled = true;
            statusText.textContent = "Timer paused";
        }
    }

    function resetTimer() {
        clearInterval(timer);
        isRunning = false;
        timeLeft = getDuration(currentMode) * 60;
        updateDisplay();
        startBtn.disabled = false;
        pauseBtn.disabled = true;
        statusText.textContent = "Timer reset";
    }

    function switchMode(mode) {
        currentMode = mode;
        resetTimer();
        document.querySelectorAll(".timer-type-btn").forEach(btn => btn.classList.remove("active"));
        if (mode === "pomodoro") pomodoroBtn.classList.add("active");
        if (mode === "short") shortBreakBtn.classList.add("active");
        if (mode === "long") longBreakBtn.classList.add("active");
    }


    startBtn.addEventListener("click", startTimer);
    pauseBtn.addEventListener("click", pauseTimer);
    resetBtn.addEventListener("click", resetTimer);

    pomodoroBtn.addEventListener("click", () => switchMode("pomodoro"));
    shortBreakBtn.addEventListener("click", () => switchMode("short"));
    longBreakBtn.addEventListener("click", () => switchMode("long"));

    saveSettingsBtn.addEventListener("click", () => {
        resetTimer();
        statusText.textContent = "Settings saved. Ready to start.";
    });

    updateDisplay();
});

