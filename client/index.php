<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fashion Contractor Services</title>
    <link rel="stylesheet" href="./css/client.css">
</head>
<body>
    <!-- Navigation -->
    <?php include 'nav.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Choose Your Fashion Design</h1>
            <p>Explore a broad range of fashion designs, categorized for your convenience.</p>
            <a href="#designs" class="cta-btn">Explore Designs</a>
        </div>
    </section>

    <!-- Fashion Categories Section -->
    <section id="designs" class="designs">
        <div class="container">
            <h2>Select a Fashion Design</h2>
            <div class="designs-grid">
                <?php
                include "db.php";

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Fetch categories from the database
                $sql = "SELECT * FROM categories";
                $result = $conn->query($sql);

                // Check if there are any categories
                if ($result->num_rows > 0) {
                    // Loop through each category
                    while ($row = $result->fetch_assoc()) {
                        $client_ids = json_decode($row['client_ids'], true); // Decode the JSON array
                        
                        // Only display the category if client_ids is not empty
                        if (!empty($client_ids)) {
                            echo '<div class="category-card">';
                            echo '<img src="' . htmlspecialchars($row['image_path']) . '" alt="' . htmlspecialchars($row['name']) . '">';
                            echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
                            echo '<p>' . htmlspecialchars($row['tagline']) . '</p>';
                            echo '<a href="manufacturer.php?category=' . $row['id'] . '" class="category-btn">Select Manufacturer</a>';
                            echo '</div>';
                        }
                    }
                } else {
                    echo "<p>No categories available.</p>";
                }

                // Close the connection
                $conn->close();
                ?>
            </div>
        </div>
    </section>

    <?php include "footer.php"?>

    <!-- JavaScript can be added for user interactivity if needed -->
</body>
</html>
