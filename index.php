<?php

session_start();

$errors = [
    'login' => $_SESSION['login_error']??'',
    'register' => $_SESSION['register_error']?? $_SESSION['match_error']??'',
    'reset' => $_SESSION['reset_error']??''
];
$activeForm = $_SESSION['active_form']??'front';

session_unset();

function showError($error){
    return !empty($error)? "<p class='error-message'>$error</p>" : '';
}

function isActiveForm($forName, $activeForm){
    return $forName==$activeForm ? 'active' : '';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jot Well</title>
    <link rel="stylesheet" href="style.css">

</head>
<body>

    <div class="container">
    
        <div class="form-box <?= isActiveForm('front', $activeForm); ?>" id="front-form">
            <div class="header">
            <li class="content">
                <ul><a href="uploads">UPLOADS</a></ul>
                <ul><a href="techniques">TECHNIQUES</a></ul>
                <ul><a href="quiz">QUIZ</a></ul>
            </li>
        </div>
        <div class="well">
            <h3>WRITE . TEST . GROW</h3>
            <form>
               <a href="#" onclick="showForm('register-form')" class="btn_signup">Get Started >></a></p>
               </div>

            </form>
         </div>
        

        <div class="form-box <?= isActiveForm('login', $activeForm); ?>" id="login-form">
            <form action="login_register.php" method="post">
                <h2 class="h">Log In</h2>
                <?= showError($errors['login']); ?>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
                <p>Don't have any account? <a href="#" onclick="showForm('register-form')">Register</a></p>
                <p>Forgot password? <a href="#" onclick="showForm('reset-form')">Recover</a></p>
            </form>
        </div>

        <div class="form-box <?= isActiveForm('register', $activeForm); ?>" id="register-form">
            <form action="login_register.php" method="post">
                <h2>Register</h2>
                <?= showError($errors['register']); ?>
                <input type="text" name="firstname" placeholder="First Name" required>
                <input type="text" name="lastname" placeholder="Last Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name=" confirmPassword" placeholder="Confirm Password" required>
                <button type="submit" name="register">Register</button>
                <p>Already have an account? <a href="#" onclick="showForm('login-form')">Login</a></p>
            </form>
        </div>

        <div class="form-box <?= isActiveForm('reset', $activeForm); ?>" id="reset-form">
            <form action="login_register.php" method="post">
                <h2 class="h">Recover password</h2>
                <?= showError($errors['reset']); ?> 
                <p>Please enter your email</p>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="new_password" placeholder="New Password" required>
                <input type="password" name="newrepeat_password" placeholder="Repeat Password" required>
                <button type="submit" name="reset">Reset</button>
                <p>Back to <a href="#" onclick="showForm('login-form')">Login</a></p>
            </form>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    <script src="front.js"></script>
</body>
</html>