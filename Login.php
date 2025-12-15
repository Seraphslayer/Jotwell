<?php
session_start();
include_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['idLogin']) || !isset($_POST['idPassword'])) {
        echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
        exit;
    }

    $login_email = filter_var($_POST['idLogin'], FILTER_SANITIZE_EMAIL);
    $login_password = htmlspecialchars($_POST['idPassword'], ENT_QUOTES, 'UTF-8');

    try {
        $emailStmt = $conn->prepare("SELECT * FROM tbl_users WHERE email = ?");
        $emailStmt->bind_param("s", $login_email);
        $emailStmt->execute();
        $result = $emailStmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            $firstname = $user['firstname'];
            $lastname = $user['lastname'];
            
            if (password_verify($login_password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['student_email'] = $user['email'];
                echo json_encode([
                    'status' => 'success',
                    'redirect' => 'home.php',
                    'user' => [
                        'firstname' => $firstname,
                        'lastname' => $lastname
                    ]
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Incorrect password']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Wrong email or password']);
        }

        $emailStmt->close();
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'An error occurred. Please try again later.']);
    }
}
?>
