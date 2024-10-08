<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Manufacturer</title>
    <link rel="stylesheet" href="./css/client.css">
</head>
<body>
    <!-- Navigation -->
    <?php include 'nav.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Select Your Manufacturer</h1>
            <p>Find the perfect manufacturer for your selected fashion design.</p>
        </div>
    </section>

    <!-- Manufacturers Section -->
    <section class="designs">
        <div class="container">
            <h2>Available Manufacturers</h2>
            <div class="designs-grid">
                <?php
                include "db.php";

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Get category ID from URL
                if (isset($_GET['category'])) {
                    $category_id = intval($_GET['category']); // Ensure it's an integer

                    // Fetch client_ids from categories table based on category_id
                    $sql = "SELECT client_ids FROM categories WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $category_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $client_ids = json_decode($row['client_ids'], true); // Decode JSON array

                        if (!empty($client_ids)) {
                            // Convert client_ids array to a comma-separated string
                            $client_ids_str = implode(',', $client_ids);

                            // Fetch manufacturers from users table based on client_ids and role 'contractor'
                            $sql_manufacturers = "SELECT id, name, image FROM users WHERE id IN ($client_ids_str) AND role = 'contractor'";
                            $manufacturers_result = $conn->query($sql_manufacturers);

                            if ($manufacturers_result->num_rows > 0) {
                                while ($manufacturer = $manufacturers_result->fetch_assoc()) {
                                    // Manufacturer details
                                    $image = $manufacturer['image'] ? htmlspecialchars($manufacturer['image']) : 'manufacturer/img/profile/default.jpg'; // Placeholder image if not available
                                    $name = htmlspecialchars($manufacturer['name']);
                                    $id = $manufacturer['id'];

                                    // Manufacturer card HTML
                                    echo '<div class="category-card">';
                                    echo '<img src="../' . $image . '" alt="' . $name . '">';
                                    echo '<h3>' . $name . '</h3>';
                                    echo '<a href="manufacturer_details.php?manufacturer=' . $id . '" class="category-btn">View Details</a>';
                                    echo '</div>';
                                }
                            } else {
                                echo "<h2>No manufacturers available for this category.</h2>";
                            }
                        } else {
                            echo "<h2>No manufacturers are assigned to this category.</h2>";
                        }
                    } else {
                        echo "<h2>Category not found.</h2>";
                    }
                    $stmt->close();
                } else {
                    echo "<h2>No category selected.</h2>";
                }

                // Close the connection
                $conn->close();
                ?>
            </div>
        </div>
    </section>

    <?php include "footer.php"; ?>
</body>
</html>
