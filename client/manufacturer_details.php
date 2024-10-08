<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manufacturer Details</title>
    <link rel="stylesheet" href="./css/client.css">
    <link rel="stylesheet" href="./css/manufacturer_details.css"> <!-- New CSS file -->
</head>
<body>
    <!-- Navigation -->
    <?php include 'nav.php'; ?>

    <!-- Manufacturer Details Section -->
    <section class="hero">
        <div class="container">
            <?php
            include "db.php";

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Get manufacturer ID from URL
            if (isset($_GET['manufacturer'])) {
                $manufacturer_id = intval($_GET['manufacturer']); // Ensure it's an integer

                // Fetch manufacturer details
                $sql = "SELECT * FROM manufacturers WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $manufacturer_id);
                $stmt->execute();
                $manufacturer_result = $stmt->get_result();

                if ($manufacturer_result->num_rows > 0) {
                    $manufacturer = $manufacturer_result->fetch_assoc();

                    // Display manufacturer details
                    echo '<h1>' . htmlspecialchars($manufacturer['name']) . '</h1>';

                    echo '<div class="manufacturer-details">';
                    echo '<p><strong>Contact Person:</strong> ' . htmlspecialchars($manufacturer['contact_person']) . '</p>';
                    echo '<p><strong>Email:</strong> <a href="mailto:' . htmlspecialchars($manufacturer['email']) . '">' . htmlspecialchars($manufacturer['email']) . '</a></p>';
                    echo '<p><strong>Phone:</strong> ' . htmlspecialchars($manufacturer['phone']) . '</p>';
                    echo '<p><strong>Address:</strong> ' . nl2br(htmlspecialchars($manufacturer['address'])) . '</p>';
                    echo '<p><strong>Description:</strong> ' . nl2br(htmlspecialchars($manufacturer['description'])) . '</p>';
                    echo '</div>';
                } else {
                    header("Location: index.php");
                    echo "<h2>Manufacturer not found.</h2>";
                }

                // Fetch products for the manufacturer
                $sql_products = "SELECT * FROM products WHERE manufacturer_id = ?";
                $stmt_products = $conn->prepare($sql_products);
                $stmt_products->bind_param("i", $manufacturer_id);
                $stmt_products->execute();
                $products_result = $stmt_products->get_result();

                if ($products_result->num_rows > 0) {
                    echo '<h2>Products Offered</h2>';
                    echo '<div class="designs-grid">';
                    while ($product = $products_result->fetch_assoc()) {
                        $images = json_decode($product['images'], true);
                        $image_url = !empty($images) ? htmlspecialchars($images[0]) : 'manufacturer/img/product/default.jpg'; // Placeholder image if not available
                        $product_name = htmlspecialchars($product['product_name']);
                        $product_description = htmlspecialchars($product['product_description']);
                        $price = number_format($product['price'], 2);
                        $average_rating = $product['average_rating'] ? $product['average_rating'] : 'Not rated';

                        // Product card HTML
                        echo '<div class="category-card">';
                        echo '<img src="../' . $image_url . '" alt="' . $product_name . '">';
                        echo '<h3>' . $product_name . '</h3>';
                        echo '<p>' . $product_description . '</p>';
                        echo '<p>Price: $' . $price . '</p>';
                        echo '<p>Average Rating: ' . $average_rating . ' / 5</p>';
                        echo '</div>';
                    }
                    echo '</div>';
                } else {
                    echo "<h2>No products found for this manufacturer.</h2>";
                }
                ?>

                <!-- Send Request Button -->
                <h2>Request a Quote</h2>
                <?php
                // Assuming you already have $manufacturer_id and $_SESSION['id'] for the user

                // Fetch the latest notification for this manufacturer by this user
                $sql_check = "SELECT status, retry_count FROM notifications WHERE user_id = ? AND manufacturer_id = ? ORDER BY created_at DESC LIMIT 1";
                $stmt_check = $conn->prepare($sql_check);
                $stmt_check->bind_param("ii", $_SESSION['id'], $manufacturer_id);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();
                $notification = $result_check->fetch_assoc();

                if ($notification) {
                    if ($notification['status'] == 'pending') {
                        echo '<p>Your request is pending. The contractor will contact you soon.</p>';
                    } elseif ($notification['status'] == 'accepted') {
                        echo '<p>Your request has been accepted! <a href="contract_page.php">Go to Contract Page</a></p>';
                    } elseif ($notification['status'] == 'rejected') {
                        echo '<p>Your request was rejected.</p>';
                        // Show "Send Again" button
                        echo '
                            <form action="send_request.php" method="POST">
                                <input type="hidden" name="manufacturer_id" value="' . $manufacturer_id . '">
                                <input type="hidden" name="retry" value="1">
                                <button type="submit" class="send-request-button">Send Again</button>
                            </form>
                        ';
                    }
                } else {
                    // Show the initial "Send Request" button
                    echo '
                        <form action="send_request.php" method="POST">
                            <input type="hidden" name="manufacturer_id" value="' . $manufacturer_id . '">
                            <button type="submit" class="send-request-button">Send Request</button>
                        </form>
                    ';
                }

                $stmt_check->close();
                ?>




                <?php

                // Fetch reviews for the manufacturer’s products (limit to 6 reviews)
                echo '<h2>Customer Reviews</h2>';
                $sql_reviews = "SELECT r.review_text, r.rating, u.name AS client_name 
                                FROM reviews r 
                                JOIN users u ON r.client_id = u.id 
                                WHERE r.product_id IN (SELECT id FROM products WHERE manufacturer_id = ?) 
                                LIMIT 6"; // Limit to 6 reviews
                $stmt_reviews = $conn->prepare($sql_reviews);
                $stmt_reviews->bind_param("i", $manufacturer_id);
                $stmt_reviews->execute();
                $reviews_result = $stmt_reviews->get_result();

                if ($reviews_result->num_rows > 0) {
                    while ($review = $reviews_result->fetch_assoc()) {
                        echo '<div class="review">';
                        echo '<p><strong>' . htmlspecialchars($review['client_name']) . '</strong> rated: ' . htmlspecialchars($review['rating']) . ' / 5</p>';
                        echo '<p>' . nl2br(htmlspecialchars($review['review_text'])) . '</p>';
                        echo '</div>';
                    }
                } else {
                    echo "<h2>No reviews found for this manufacturer's products.</h2>";
                }

                $stmt->close();
                $stmt_products->close();
                $stmt_reviews->close();
            } else {
                echo "<h2>No manufacturer selected.</h2>";
            }

            // Close the connection
            $conn->close();
            ?>
        </div>
    </section>

    <?php include "footer.php"; ?>
</body>
</html>
