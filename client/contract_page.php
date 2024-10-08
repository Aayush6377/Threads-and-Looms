<?php
session_start();
include "db.php"; // include your database connection

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: ../account/login_form_client.php"); // Redirect to login page if not logged in
    exit;
}

// Get manufacturer_id and contract_id from URL or session (assuming it's passed)
if (isset($_GET['manufacturer_id']) && isset($_GET['contract_id'])) {
    $manufacturer_id = intval($_GET['manufacturer_id']);
    $contract_id = intval($_GET['contract_id']);
} else {
    die("Invalid request.");
}

// Fetch contract details
$sql = "SELECT c.*, m.name AS manufacturer_name 
        FROM contracts c 
        JOIN manufacturers m ON c.manufacturer_id = m.id 
        WHERE c.id = ? AND c.manufacturer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $contract_id, $manufacturer_id);
$stmt->execute();
$contract_result = $stmt->get_result();

if ($contract_result->num_rows > 0) {
    $contract = $contract_result->fetch_assoc();
} else {
    die("Contract not found.");
}

// Handle contract acceptance or rejection if needed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accept_contract'])) {
        // Update contract status to 'Accepted'
        $sql_accept = "UPDATE contracts SET status = 'Accepted' WHERE id = ?";
        $stmt_accept = $conn->prepare($sql_accept);
        $stmt_accept->bind_param("i", $contract_id);
        $stmt_accept->execute();
        $message = "Contract accepted successfully!";
    } elseif (isset($_POST['reject_contract'])) {
        // Update contract status to 'Rejected'
        $sql_reject = "UPDATE contracts SET status = 'Rejected' WHERE id = ?";
        $stmt_reject = $conn->prepare($sql_reject);
        $stmt_reject->bind_param("i", $contract_id);
        $stmt_reject->execute();
        $message = "Contract rejected.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract Details</title>
    <link rel="stylesheet" href="./css/contract_page.css"> <!-- Add custom styles here -->
</head>
<body>
    <!-- Navigation -->
    <?php include 'nav.php'; ?>

    <!-- Contract Details Section -->
    <section class="contract-details">
        <div class="container">
            <h1>Contract with <?php echo htmlspecialchars($contract['manufacturer_name']); ?></h1>

            <!-- Contract Information -->
            <div class="contract-info">
                <p><strong>Contract ID:</strong> <?php echo htmlspecialchars($contract['id']); ?></p>
                <p><strong>Deliverables:</strong> <?php echo nl2br(htmlspecialchars($contract['deliverables'])); ?></p>
                <p><strong>Price:</strong> $<?php echo number_format($contract['price'], 2); ?></p>
                <p><strong>Start Date:</strong> <?php echo htmlspecialchars($contract['start_date']); ?></p>
                <p><strong>End Date:</strong> <?php echo htmlspecialchars($contract['end_date']); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($contract['status']); ?></p>
            </div>

            <!-- Display Message after Accept/Reject Action -->
            <?php if (isset($message)) : ?>
                <p class="message"><?php echo $message; ?></p>
            <?php endif; ?>

            <!-- If contract is pending, show Accept/Reject buttons -->
            <?php if ($contract['status'] === 'Pending') : ?>
                <form method="POST" class="contract-actions">
                    <button type="submit" name="accept_contract" class="accept-btn">Accept Contract</button>
                    <button type="submit" name="reject_contract" class="reject-btn">Reject Contract</button>
                </form>
            <?php elseif ($contract['status'] === 'Accepted') : ?>
                <p>Your contract has been accepted!</p>
            <?php elseif ($contract['status'] === 'Rejected') : ?>
                <p>The contract has been rejected. Please contact the manufacturer for further discussions.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <?php include "footer.php"; ?>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
