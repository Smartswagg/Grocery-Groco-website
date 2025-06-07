<?php
@include 'seller_header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seller Dashboard</title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
    <section class="dashboard">
        <h1 class="title">SELLER DASHBOARD</h1>
        <div class="box-container">
            <?php
            // Get total products
            $total_products = 0;
            $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ?");
            $stmt->execute([$_SESSION['seller_id']]);
            $total_products = $stmt->fetchColumn();

            // Get total orders (robust: check for 'orders' table and 'seller_id' column)
            $total_orders = 'N/A';
            $orders_table_exists = $conn->query("SHOW TABLES LIKE 'orders'")->rowCount() > 0;
            $orders_has_seller_id = false;
            if ($orders_table_exists) {
                $columns = $conn->query("SHOW COLUMNS FROM orders")->fetchAll(PDO::FETCH_COLUMN);
                if (in_array('seller_id', $columns)) {
                    $orders_has_seller_id = true;
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE seller_id = ?");
                    $stmt->execute([$_SESSION['seller_id']]);
                    $total_orders = $stmt->fetchColumn();
                }
            }
            ?>

            <div class="box" style="width:100%;">
                <h3>Total Orders</h3>
                <p class="dashboard-count">
                    <?php
                    // Show count of orders for this seller
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE seller_id = ?");
                    $stmt->execute([$_SESSION['seller_id']]);
                    $total_orders = $stmt->fetchColumn();
                    echo $total_orders;
                    ?>
                </p>
            </div>
        </div>
        <div class="box-container" style="margin-top: 2rem;">

            <a href="seller_update_profile.php" class="btn">Update Profile</a>
        </div>
        <div class="box-container" style="margin-top: 2rem;">
            <div class="box" style="width:100%;">
                <h3>Recent Orders</h3>
                <table style="width:100%; border-collapse:collapse; text-align:left;">
                    <tr>
                        <th>Order ID</th>
                        <th>Product</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                    <?php
                    // Show recent orders for this seller
                    $stmt = $conn->prepare("SELECT * FROM orders WHERE seller_id = ? ORDER BY placed_on DESC LIMIT 5");
                    $stmt->execute([$_SESSION['seller_id']]);
                    while ($order = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($order['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($order['total_products']) . "</td>";
                        echo "<td>" . htmlspecialchars($order['placed_on']) . "</td>";
                        echo "<td>" . htmlspecialchars($order['status']) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </table>
            </div>
        </div>
    </section>
</body>
</html>
