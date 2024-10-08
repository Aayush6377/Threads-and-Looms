<?php
session_start(); // Start the session

// Unset all of the session variables
$role = $_SESSION['role'];
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"], $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

if ($role == "client"){
    header("Location: login_form_client.php");
}
else if ($role == "contractor"){
    header("Location: login_form_manufacturer.php");
}
else{
    header("Location: ../index.php");
}
exit;
?>
