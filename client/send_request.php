<?php
session_start();
include "db.php";

$user_id = $_SESSION['id'];
$user_name = $_SESSION['name'];
$manufacturer_id = intval($_POST['manufacturer_id']);
$retry = isset($_POST['retry']) ? intval($_POST['retry']) : 0;

// Function to display message with CSS and auto-redirect
function displayMessage($message, $messageType, $manufacturer_id) {
    echo "
    <style>
        .message {
            padding: 15px;
            margin: 20px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
            font-size: 18px;
        }
        .message-success {
            background-color: #d4edda;
            color: #155724;
        }
        .message-error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>

    <div class='message message-$messageType'>$message</div>
    
    <script>
        setTimeout(function() {
            window.location.href = 'manufacturer_details.php?manufacturer=$manufacturer_id';
        }, 2000); // Redirect after 5 seconds
    </script>
    ";
}

// Check if it's a retry attempt
if ($retry) {
    // Fetch the latest rejected request
    $sql_check = "SELECT id, retry_count FROM notifications WHERE user_id = ? AND manufacturer_id = ? AND status = 'rejected' ORDER BY created_at DESC LIMIT 1";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $user_id, $manufacturer_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $notification = $result_check->fetch_assoc();

    if ($notification) {
        // Insert new notification with incremented retry_count
        $new_retry_count = $notification['retry_count'] + 1;
        $sql_insert_retry = "INSERT INTO notifications (user_id, manufacturer_id, message, status, retry_count) VALUES (?, ?, 'You have received a request again for a quote from user: $user_name (User ID: $user_id)', 'pending', ?)";
        $stmt_retry = $conn->prepare($sql_insert_retry);
        $stmt_retry->bind_param("iii", $user_id, $manufacturer_id, $new_retry_count);
        $stmt_retry->execute();
        $stmt_retry->close();

        displayMessage("Your request has been sent again!", 'success', $manufacturer_id);
    } else {
        displayMessage("No rejected request found to retry.", 'error', $manufacturer_id);
    }

    $stmt_check->close();
} else {
    // Normal request sending process
    $sql_insert = "INSERT INTO notifications (user_id, manufacturer_id, message, status) VALUES (?, ?, 'You have received a request for a quote from user: $user_name (User ID: $user_id)', 'pending')";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("ii", $user_id, $manufacturer_id);
    $stmt->execute();
    $stmt->close();

    displayMessage("Your request has been sent!", 'success', $manufacturer_id);
}

$conn->close();
?>
