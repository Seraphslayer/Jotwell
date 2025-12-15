<?php
session_start();
include_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_SESSION['student_email'])) {
        $current_email = $_SESSION['student_email'];
    
        $sql = "SELECT firstname, lastname FROM tbl_users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $current_email);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode([
                'firstname' => $row['firstname'],
                'lastname' => $row['lastname']
            ]);
        } else {
            echo json_encode([
                'error' => 'User not found'
            ]);
        }
    
        $stmt->close();
    } else {
        echo json_encode([
            'error' => 'User is not logged in'
        ]);
    }
    
    $conn->close();
}
?>

