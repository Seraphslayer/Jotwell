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
    <link rel="stylesheet" href="css/front.css">
    <title>Jot Well</title>
    <style>
        .sidebar {
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(4px);
            padding-top: 20px;
            padding-left: 20px;
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .sidebar a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: white;
            margin-bottom: 10px;
            border-bottom: 1px solid #444;
        }
        .sidebar a:hover {
            background-color: #575757;
        }

        /* Content Styling */
        .content {
            margin-left: 250px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
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
            <li class="nav-item pt-2"><a class="nav-link text-white" href="upload_pdf.php">LIBRARY</a></li>
            <li class="nav-item pt-2"><a class="nav-link text-white" href="techniques.php">TECHNIQUES</a></li>
            <li class="nav-item pt-2"><a class="nav-link text-white" href="quiz.php">QUIZ</a></li>
    </ul>
            <div class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item dropdown pe-5">
                <a class="nav-link text-black dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle ps-3" style="font-size: 1.5rem; color: #faf6e9;"></i></a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item text-black" href="#">My Account</a></li>
                    <li><hr class="dropdown-divider"></hr></li>
                    <li><button type="button" id="confirmLogoutBtn"class="btn bg-transparent border-0">Logout</button></li>
                </ul>
            </li>
        </div>
</div>
</div>
    </div>

    <!-- Page Content -->
    <div class="content">
        
 

    <script src="jquery-3.7.1.min.js"></script>
    <script src="bootstrap5/js/popper.min.js"></script>
    <script src="bootstrap5/js/bootstrap.bundle.min.js"></script>
    <script src="logout.js"></script>
</body>
</html>
