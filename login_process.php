<?php

ob_start(); 
session_start();
include 'db_connection.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $name = trim($_POST['name']); 
    $password = $_POST['password'];



    $stmt = $conn->prepare(" SELECT `user_id`,`name`,`password`,`user_type` FROM `users` WHERE `name` = ? ");
    $stmt->bind_param("s", $name); 
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
            $stmt = $conn->prepare("SELECT user_id, name, password, user_type FROM users WHERE name = ?");

        if (password_verify($password, $user['password'])) {
            
            $_SESSION['logged_in'] = true; 
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['user_type'] = $user['user_type'];
            
            if ($user['user_type'] === 'admin') {
                header("Location: /LebanonVibes/web_admin.php");
                exit();
            } elseif ($user['user_type'] === 'business') {
                header("Location: my_business.php");
                exit();
            } else {
                header("Location: profile.php"); 
                exit();
            }
        
        } else {
            $_SESSION['login_error'] = "❌ Invalid password.";
            header("Location: login.php"); 
            exit();
        }
    
    } else {
        $_SESSION['login_error'] = "❌ User not found.";
        header("Location: login.php"); 
        exit();
    }
    
    $stmt->close();
}
$conn->close();
 ?> 