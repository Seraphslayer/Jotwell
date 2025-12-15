<?php
if(isset($_POST['reset'])){
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $newrepeat_password = $_POST['newrepeat_password'];

    if($new_password !== $newrepeat_password){
        $_SESSION['reset_error'] = 'password does not match';
        $_SESSION['active_form'] = 'reset';
    }else{
        $result = $conn->query("SELECT * FROM tbl_users WHERE email = '$email'");
        if($result->num_rows > 0){
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_password = $conn->prepare("UPDATE tbl_users SET password = ? WHERE email = ?");
            $update_password->bind_param("ss", $hashed_password, $email);
            if($update_password->execute()){
                echo"succesful";
            }
        }
    }

    header("Location: lol.php");
    exit();
}


?>