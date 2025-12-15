<?php
session_start();
if (!isset($_SESSION['student_email'])) {
    header('Location: lol.php'); 
    exit();
}

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="bootstrap5/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/techniques.css">
    <title>Jot Well</title>
    
</head>
<body>

<div class="main-content">
<div style="text-align:center; margin-top: 20px;">
    <input type="text" id="searchInput" placeholder="Search techniques..." 
           style="padding: 8px 12px; border-radius: 10px; border: 1px solid #ccc; width: 70%;">
</div>



        <h1 class="heading">STUDY TIME!</h1>

<div class="card-container" id="techCards">
    <div class="tech-card" data-title="Pomodoro">
        <div class="tech-icon"><i class="bi bi-alarm"></i></div>
        <div class="tech-title">Pomodoro</div>
        <a href="techniques/pomodoro.php"><button class="start-btn">Start!</button></a>
    </div>

    <div class="tech-card" data-title="Mind Map">
        <div class="tech-icon"><i class="bi bi-diagram-3"></i></div>
        <div class="tech-title">Mind Map</div>
        <a href="techniques/mindmap.php"><button class="start-btn">Start!</button></a>
    </div>

    <div class="tech-card" data-title="Mnemonics">
        <div class="tech-icon"><i class="bi bi-stars"></i></div>
        <div class="tech-title">Mnemonics</div>
        <a href="techniques/mnemonics.php"><button class="start-btn">Start!</button></a>
    </div>

    <div class="tech-card" data-title="Take notes">
        <div class="tech-icon"><i class="bi bi-pencil-square"></i></div>
        <div class="tech-title">Take notes</div>
        <a href="techniques/notes.php"><button class="start-btn">Start!</button></a>
    </div>

    <div class="tech-card" data-title="Flashcards">
        <div class="tech-icon"><i class="bi bi-card-list"></i></div>
        <div class="tech-title">Flashcards</div>
        <a href="techniques/flashcards.php"><button class="start-btn">Start!</button></a>
    </div>
</div>

    <div class="sidebar">
        <h2><img src="pics/new_jot.png" alt="Logo" style="width: 130px;"></h2>
        <div class="container px-4">

<a class="navbar-brand" href="/"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsibleNavbar" aria-controls="collapsibleNavbar">
            <span class="navbar-toggler-icon"></span>
    </button>
<div class="navbar-collapse" id="collapsibleNavbar">
    <ul class="navbar-nav">
            <li class="nav-item pt-2"><a class="nav-link text-white" href="home.php">HOME</a></li>
            <li class="nav-item pt-2"><a class="nav-link text-white" href="upload_pdf.php">LIBRARY </a></li>
            <li class="nav-item pt-2"><a class="nav-link text-white" href="techniques.php">TECHNIQUES</a></li>
            <li class="nav-item pt-2"><a class="nav-link text-white" href="quiz.php">QUIZ</a></li>
    </ul>
            <div class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item dropdown pe-5">
                <a class="nav-link text-black dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle ps-3" style="font-size: 1.5rem; color: #faf6e9;"></i></a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item text-black" href="profile.php">My Account</a></li>
                    <li><hr class="dropdown-divider"></hr></li>
                    <li><button type="button" id="confirmLogoutBtn"class="btn bg-transparent border-0">Logout</button></li>
                </ul>
            </li>
        </div>
</div>
</div>

 

    <script src="jquery-3.7.1.min.js"></script>
    <script src="bootstrap5/js/popper.min.js"></script>
    <script src="bootstrap5/js/bootstrap.bundle.min.js"></script>
    <script src="logout.js"></script>
    <script src="search.js"></script>
</body>
</html>
