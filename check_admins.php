<?php
@include 'config.php';

$sql = "SELECT id, email, password, user_type FROM users WHERE user_type = 'admin'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($admins) === 0) {
    echo "No admin users found.";
} else {
    echo "<h2>Admin Users</h2><table border='1'><tr><th>ID</th><th>Email</th><th>Password (MD5 Hash)</th><th>User Type</th></tr>";
    foreach ($admins as $admin) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($admin['id']) . "</td>";
        echo "<td>" . htmlspecialchars($admin['email']) . "</td>";
        echo "<td>" . htmlspecialchars($admin['password']) . "</td>";
        echo "<td>" . htmlspecialchars($admin['user_type']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>
