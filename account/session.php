<?php
session_start(); // Start the session

// Retrieve user data from query parameters
if (isset($_GET['id']) && isset($_GET['name']) && isset($_GET['role'])) {
    // Sanitize input to prevent XSS
    $user_id = intval($_GET['id']);
    $name = htmlspecialchars($_GET['name']);
    $role = htmlspecialchars($_GET['role']);
    
    // Set session variables
    $_SESSION['id'] = $user_id;
    $_SESSION['name'] = $name;
    $_SESSION['role'] = $role;

    if ($role == "client"){
        header("Location: ../client/index.php");
    }
    else if ($role == "contractor"){
        header("Location: ../manufacturer/index.php");
    }
    else{
        header("Location: ../index.php");
    }
    exit;
} else {
    // If the required parameters are not set, redirect back to signup
    if ($role == "client"){
        header("Location: signup_client.php");
    }
    else if ($role == "contractor"){
        header("Location: signup_manufacturer.php");
    }
    else{
        header("Location: ../index.php");
    }
    exit;
}
?>
