<?php
@include 'config.php';
session_start();
if(!isset($_SESSION['seller_id'])){
   header('location:seller_login.php');
   exit();
}

// Fetch all unique categories and product names for filters
$cat_stmt = $conn->prepare("SELECT DISTINCT category FROM products ORDER BY category");
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);

$prod_stmt = $conn->prepare("SELECT DISTINCT name FROM products ORDER BY name");
$prod_stmt->execute();
$product_names = $prod_stmt->fetchAll(PDO::FETCH_COLUMN);

// Get filter/sort values from GET
$filter_category = $_GET['category'] ?? '';
$filter_product = $_GET['product'] ?? '';
$sort_price = $_GET['sort_price'] ?? '';

// Build SQL with filters
$sql = "SELECT p.name AS product_name, p.category, p.price, u.name AS seller_name FROM products p JOIN users u ON p.seller_id = u.id WHERE 1";
$params = [];
if ($filter_category) {
    $sql .= " AND p.category = ?";
    $params[] = $filter_category;
}
if ($filter_product) {
    $sql .= " AND p.name = ?";
    $params[] = $filter_product;
}
$sql .= " ORDER BY p.category, p.name";
if ($sort_price === 'asc') {
    $sql .= ", p.price ASC";
} elseif ($sort_price === 'desc') {
    $sql .= ", p.price DESC";
} else {
    $sql .= ", p.price ASC";
}
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group products by name and category
$grouped = [];
foreach($products as $prod) {
    // Fetch average rating and review count for this product-seller
    $review_stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE product_id = (SELECT id FROM products WHERE name = ? AND seller_id = (SELECT id FROM users WHERE name = ? ) LIMIT 1) AND seller_id = (SELECT id FROM users WHERE name = ? LIMIT 1)");
    $review_stmt->execute([$prod['product_name'], $prod['seller_name'], $prod['seller_name']]);
    $review_data = $review_stmt->fetch(PDO::FETCH_ASSOC);
    $prod['avg_rating'] = $review_data && $review_data['avg_rating'] ? round($review_data['avg_rating'], 2) : 'N/A';
    $prod['total_reviews'] = $review_data ? $review_data['total_reviews'] : 0;
    $key = $prod['category'] . '|' . $prod['product_name'];
    if (!isset($grouped[$key])) $grouped[$key] = [];
    $grouped[$key][] = $prod;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Compare Products</title>
   <link rel="stylesheet" href="css/components.css">
   <style>
      .compare-table { width: 100%; border-collapse: collapse; margin: 2rem 0; }
      .compare-table th, .compare-table td { border: 1px solid #ccc; padding: 1rem; text-align: center; }
      .compare-table th { background: #f6f6f6; }
      .compare-table tr:nth-child(even) { background: #f9f9f9; }
      .compare-title { font-size: 2.2rem; margin: 2rem 0 1rem 0; text-align: center; }
      .filter-form { display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center; margin-bottom: 2rem; }
      .filter-form select, .filter-form button { font-size: 1.5rem; padding: 0.5rem 1rem; border-radius: .5rem; border: 1px solid #ccc; }
      .filter-form button { background: #27ae60; color: #fff; border: none; cursor: pointer; }
      .filter-form button:hover { background: #145a32; }
   </style>
</head>
<body>
<?php include 'seller_header.php'; ?>
<section>
   <h2 class="compare-title">Compare Products & Prices</h2>
   <form class="filter-form" method="get">
      <select name="category" onchange="this.form.submit()">
         <option value="">All Categories</option>
         <?php foreach($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>" <?= $filter_category === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
         <?php endforeach; ?>
      </select>
      <select name="product" onchange="this.form.submit()">
         <option value="">All Products</option>
         <?php foreach($product_names as $prod): ?>
            <option value="<?= htmlspecialchars($prod) ?>" <?= $filter_product === $prod ? 'selected' : '' ?>><?= htmlspecialchars($prod) ?></option>
         <?php endforeach; ?>
      </select>
      <label for="sort_price" style="font-size:1.5rem;align-self:center;">Sort by Price:</label>
      <select name="sort_price" id="sort_price" onchange="this.form.submit()">
         <option value="asc" <?= $sort_price === 'asc' ? 'selected' : '' ?>>Low to High</option>
         <option value="desc" <?= $sort_price === 'desc' ? 'selected' : '' ?>>High to Low</option>
      </select>
   </form>
   <?php if(count($grouped) > 0): ?>
      <?php foreach($grouped as $group_key => $items): ?>
         <?php list($cat, $prod_name) = explode('|', $group_key); ?>
         <h3 style="margin-top:2rem;">Product: <span style="color:#27ae60;"><?= htmlspecialchars($prod_name) ?></span> | Category: <span style="color:#f39c12;"><?= htmlspecialchars($cat) ?></span></h3>
         <table class="compare-table">
            <tr>
               <th>Seller</th>
               <th>Price</th>
               <th>Avg. Rating</th>
               <th>Reviews</th>
            </tr>
            <?php foreach($items as $item): ?>
            <tr>
               <td><?= htmlspecialchars($item['seller_name']) ?></td>
               <td>₹<?= htmlspecialchars($item['price']) ?></td>
               <td><?= $item['avg_rating'] !== 'N/A' ? '★ ' . $item['avg_rating'] . '/5' : 'No ratings' ?></td>
               <td><?= $item['total_reviews'] ?></td>
            </tr>
            <?php endforeach; ?>
         </table>
      <?php endforeach; ?>
   <?php else: ?>
      <p class="empty">No products found for comparison.</p>
   <?php endif; ?>
</section>
<?php include 'footer.php'; ?>
</body>
</html> 