<?php
session_start(); // Start the session
include "db.php"; // Include database connection

// Initialize error message variable
$error_message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Input validation
    if (empty($email) || empty($password)) {
        $error_message = "Both fields are required.";
    } else {
        // Prepare the SQL statement
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);

        // Execute the statement
        $stmt->execute();
        $stmt->store_result();

        // Check if user exists
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $name, $hashed_password);
            $stmt->fetch();

            // Verify password
            if (password_verify($password, $hashed_password)) {
                header("Location: session.php?id=$user_id&name=" . urlencode($name) . "&role=client");
                exit;
            } else {
                $error_message = "Invalid email or password.";
            }
        } else {
            $error_message = "No account found with that email.";
        }

        // Close statement
        $stmt->close();
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="./css/login_form.css">
  <script type="text/javascript" src="./js/validation.js" defer></script>
</head>
<body>
  <div class="wrapper">
    <h1>Login</h1>
    <p id="error-message" style="color: red;"><?php echo $error_message; ?></p>
    <form id="form" method="POST" action="">
      <div>
        <label for="email-input">
          <span>@</span>
        </label>
        <input type="email" name="email" id="email-input" placeholder="Email" required>
      </div>
      <div>
        <label for="password-input">
          <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="M240-80q-33 0-56.5-23.5T160-160v-400q0-33 23.5-56.5T240-640h40v-80q0-83 58.5-141.5T480-920q83 0 141.5 58.5T680-720v80h40q33 0 56.5 23.5T800-560v400q0 33-23.5 56.5T720-80H240Zm240-200q33 0 56.5-23.5T560-360q0-33-23.5-56.5T480-440q-33 0-56.5 23.5T400-360q0 33 23.5 56.5T480-280ZM360-640h240v-80q0-50-35-85t-85-35q-50 0-85 35t-35 85v80Z"/></svg>
        </label>
        <input type="password" name="password" id="password-input" placeholder="Password" required>
      </div>
      <button type="submit">Login</button>
    </form>
    <p>New here? <a href="signup_client.php">Create an Account</a></p>
  </div>
</body>
</html>
