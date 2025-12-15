    <?php

    session_start();

    $registerSuccess = $_SESSION['register_success'] ?? '';
    $errors = [
    'login' => $_SESSION['login_error']??'',
    'register' => $_SESSION['register_error']?? $_SESSION['match_error']??'',
    'reset' => $_SESSION['reset_error']??''
    ];
    $activeForm = $_SESSION['active_form']??'front';

    session_unset();
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Jot Well</title>
        <link rel="stylesheet" href="css/style.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
        <link rel="stylesheet" href="bootstrap5/css/bootstrap.min.css">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Aleo:ital,wght@1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="bootstrap5/css/sweetalert2.min.css">
    
    </head>
    <body> 
            <!--Navigation Bar-->
            <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
            <img src="pics/new_jot.png" alt="Logo" style="width: 100px;" class="ms-5 mt-1">
                <div class="container px-4">
                    <a class="navbar-brand" href="/"></a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsibleNavbar" aria-controls="collapsibleNavbar" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="collapsibleNavbar">
                    <ul class="navbar-nav">
                        <li class="nav-item pt-2">
                            <a class="nav-link text-white" href="#">UPLOADS</a>
                        </li>
                        <li class="nav-item pt-2">
                            <a class="nav-link text-white" href="#">TECHNIQUES</a>
                        </li>
                        <li class="nav-item pt-2">
                            <a class="nav-link text-white" href="#">QUIZ</a>
                            </li></ul>
                        <div class="navbar-nav ms-auto mb-2 mb-lg-0">
                            <li class="dropdown-item pe-5"><a class="nav-link" href="#secondPopup" id="navbarDropdown" role="button" data-bs-toggle="modal" aria-expanded="false"><i class="bi bi-person-circle ps-3" style="font-size: 1.5rem; color: #faf6e9;"></i></a></li>
                        </div>
                    </div>
                </div>
            </nav>
        <!--Navigation Bar-->      
    <!--Landing Page Content-->
    <div class="container mt-5">
             <div class="well">
                <img src="pics/new_jot.png" alt="home" style="width:60%;" class="img-fluid">
            <h3>WRITE . TEST . GROW</h3>
            <button class="button" data-bs-toggle="modal" data-bs-target="#popup" id="btn">
                Get Started >>
            </button>
        </div>
    </div>

         <!-- About Section -->
         <div class="container" style="background-color:#44576d80;" id="aboutIndex">
            <div class="row">
                <div class="col">
                    <div class="about-section">
                        <h2 class="text-center aboutTitle">ABOUT</h2>
                        <p class="description">
                        We're a resource hub for students, offering effective study techniques, personalized learning strategies, and productivity tips to boost academic performance. Visit us for expert guidance on achieving your academic goals.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="container text-center mt-5" id="rolesIndex">
            <h3 class=" text">FEATURES</h3>
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-6 mb-4">
                    <a href="#" class="text-decoration-none"> 
                        <div class="portal-card">
                            <img src="pics/uploads.png" alt="" style="width:100%;">
                            <h3 class="portal-title m-4">Uploads</h3>
                            <p class="portal-description">Share your notes, resources, and materials with our community. Supported file types: PDF, DOCX, PPTX, JPEG, PNG</p>
                        </div>
                    </a>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <a href="#" class="text-decoration-none"> 
                        <div class="portal-card">
                            <img src="pics/techniques.png" alt="" style="width:75%;">
                            <h3 class="portal-title m-4">Techniques</h3>
                            <p class="portal-description">Learn effective ways to study and retain information. Explore techniques like Pomodoro, Mind Mapping, and more.</p>
                        </div>
                    </a>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <a href="#" class="text-decoration-none"> 
                        <div class="portal-card">
                            <img src="pics/quiz.png" alt="" style="width: 80%;">
                            <h3 class="portal-title">Quiz</h3>
                            <p class="portal-description mt-4">Test your knowledge and track your progress with our interactive quizzes. Challenge yourself and learn with fun!</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer text-white py-5 text-center">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-3">
                        <h5 class="logo">Jot Well</h5>
                        <p>An online reviewer</p>
                    </div>
                    <div class="col-md-2">
                        <h6>Services</h6>
                        <ul class="list-unstyled">
                            <li><a href="#" class="text-white text-decoration-none">Reviewer</a></li>
                        </ul>
                    </div>
                    <div class="col-md-2">
                        <h6>Showcase</h6>
                        <ul class="list-unstyled">
                            <li><a href="#" class="text-white text-decoration-none">WidgetKit</a></li>
                            <li><a href="#" class="text-white text-decoration-none">Support</a></li>
                        </ul>
                    </div>
                    <div class="col-md-2">
                        <h6>About Us</h6>
                        <ul class="list-unstyled">
                            <li><a href="#" class="text-white text-decoration-none">Contact Us</a></li>
                            <li><a href="#" class="text-white text-decoration-none">Affiliates</a></li>
                            <li><a href="#" class="text-white text-decoration-none">Resources</a></li>
                        </ul>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col text-center">
                        <ul class="list-inline">
                            <li class="list-inline-item"><a href="#" class="text-white"><i class="bi bi-facebook"></i></a></li>
                            <li class="list-inline-item"><a href="#" class="text-white"><i class="bi bi-twitter"></i></a></li>
                            <li class="list-inline-item"><a href="#" class="text-white"><i class="bi bi-rss"></i></a></li>
                            <li class="list-inline-item"><a href="#" class="text-white"><i class="bi bi-google"></i></a></li>
                        </ul>
                        <p class="mt-2">&copy; Copyright. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </footer>

       

        
        
        <!-- Register Modal -->
        <div class="modal fade" id="popup" tabindex="-1" aria-labelledby="popupLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="popupLabel">Register</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form class="form" id="register-form" action="loginregister.php" method="post">
                            
                            <p class="message">Unlock exclusive content and features!
                                Log in or Sign up now to get started!</p>
                            
                            <div class="flex">
                                <label>
                                    <input required placeholder="First Name" type="text" class="input" name="firstname" id="firstname"/>
                                    <div id="fnameFeedback"></div>
                                </label>
                                
                                <label>
                                    <input required placeholder="Last Name" type="text" class="input" name="lastname" id="lastname"/>
                                    <div id="lnameFeedback"></div>
                                </label>
                                
                            </div>
                            <label>
                                <input required placeholder="Email" type="email" class="input" name="email" id="email"/>
                                <div id="emailFeedback"></div>
                            </label>
                            
                            <label>
                                <input required placeholder="Password" type="password" class="input" name="password" id="password" autocomplete="new-password"/>
                                <div id="passwordFeedback"></div>
                            </label>
                            
                            <label>
                                <input required placeholder="Confirm Password" type="password" class="input" name="confirmPassword" id="confirmPassword"/>
                                <div id="confirmPasswordFeedback"></div>
                            </label>
                            

                            <button type="submit" class="submit" name="register" id="signupBtn">Submit</button>
                            <p class="logIn">Already have an account? <a href="#secondPopup" data-bs-toggle="modal" data-bs-target="#secondPopup" data-bs-dismiss="modal">Log In</a></p>
                        </form>
                    </div>  
                </div>
            </div>
        </div>

        <!-- Log In Modal -->
        <div class="modal fade" id="secondPopup" tabindex="-1" aria-labelledby="popupLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="popupLabel">Log In</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form class="form " id="loginForm" action="Login.php" method="post">
                            <p class="form-title">Log in to your account</p>
                            <label>
                                <input required placeholder="Email" type="email" class="input" name="idLogin" id="idLogin"/>
                            </label>
                            <label>
                                <input required placeholder="Password" type="password" class="input" name="idPassword" id="idPassword" autocomplete="current-password"/>
                            </label>
                            <button type="submit" class="submit" name="login" id="loginBtn">Log in</button>
                            <p><a href="forgot_password.php" data-bs-dismiss="modal" data-bs-toggle="modal"> Forgot Password?</a></p>
                            <p class="signup-link">
                                No account? <a href="#popup" data-bs-dismiss="modal" data-bs-toggle="modal">Sign up</a>
    </p>
    </form>
    </div>  
    </div>
    </div>
    </div>
        
    <script src="bootstrap5/js/bootstrap.bundle.min.js"></script>
    <script src="bootstrap5/js/bootstrap.min.js"></script>
    <script src="bootstrap5/js/jquery-3.7.1.min.js"></script>
    <script src="register.js"></script>
    <script src="login.js"></script>
    <script src="bootstrap5/js/popper.min.js"></script> 
<script src="bootstrap5/js/sweetalert2.all.min.js"></script>
    </body>
    </html>