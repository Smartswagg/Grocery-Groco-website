<?php
@include 'config.php';
session_start();

// Check if admin is logged in (adjust logic as needed for your admin authentication)
if (!isset($_SESSION['admin_id'])) {
    header('location:admin_login.php');
    exit();
}

// Handle delete message request
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_message = $conn->prepare("DELETE FROM `message` WHERE id = ?");
    $delete_message->execute([$delete_id]);
    header('location:admin_messages.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Messages</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'admin_header.php'; ?>
    <section class="messages">
        <h1 class="title">MESSAGES</h1>
        <div class="box-container">
            <?php
            // Fetch all messages
            $select_messages = $conn->prepare("SELECT * FROM `message` ORDER BY id DESC");
            $select_messages->execute();
            $seller_messages = [];
            $user_messages = [];
            if ($select_messages->rowCount() > 0) {
                while ($fetch_message = $select_messages->fetch(PDO::FETCH_ASSOC)) {
                    $user_id = $fetch_message['user_id'];
                    $user_type_stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
                    $user_type_stmt->execute([$user_id]);
                    $user_type = 'user';
                    if($user_type_stmt->rowCount() > 0){
                        $user_row = $user_type_stmt->fetch(PDO::FETCH_ASSOC);
                        $user_type = $user_row['user_type'];
                    }
                    if ($user_type === 'seller') {
                        $seller_messages[] = $fetch_message;
                    } else if ($user_type === 'user') {
                        $user_messages[] = $fetch_message;
                    }
                }
            }
            ?>
        </div>

        <?php
        $show_type = isset($_GET['type']) ? $_GET['type'] : '';
        ?>
        <?php if ($show_type === 'seller' || $show_type === ''): ?>
        <div class="box-container">
            <h2 style="text-align:center;margin-top:40px;font-size:3.2rem;font-weight:bold;">Seller Messages</h2>
            <?php if (count($seller_messages) > 0): ?>
                <?php foreach ($seller_messages as $fetch_message): ?>
                    <div class="box">
                        <span style="display:inline-block;padding:4px 10px;background:#27ae60;color:#fff;font-weight:bold;border-radius:6px;margin-bottom:8px !important; border: 2px solid #145a32 !important;">SELLER</span>
                        <p style="font-size:3rem;color:#111;"><strong style="font-weight:bold;">User ID:</strong> <span style="font-weight:normal;"><?= htmlspecialchars($fetch_message['user_id']); ?></span></p>
                        <p style="font-size:3rem;color:#111;"><strong style="font-weight:bold;">Name:</strong> <span style="font-weight:normal;"><?= htmlspecialchars($fetch_message['name']); ?></span></p>
                        <p style="font-size:3rem;color:#111;"><strong style="font-weight:bold;">Email:</strong> <span style="font-weight:normal;"><?= htmlspecialchars($fetch_message['email']); ?></span></p>
                        <p style="font-size:3rem;color:#111;"><strong style="font-weight:bold;">Number:</strong> <span style="font-weight:normal;"><?= htmlspecialchars($fetch_message['number']); ?></span></p>
                        <p style="font-size:3rem;color:#111;"><strong style="font-weight:bold;">Message:</strong> <span style="font-weight:normal;"><?= nl2br(htmlspecialchars($fetch_message['message'])); ?></span></p>
                        <a href="admin_messages.php?delete=<?= $fetch_message['id']; ?>" class="delete-btn" onclick="return confirm('Delete this message?');">Delete</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="empty">No seller messages found.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($show_type === 'user' || $show_type === ''): ?>
        <div class="box-container">
            <h2 style="text-align:center;margin-top:40px;font-size:3.2rem;font-weight:bold;">Customer Messages</h2>
            <?php if (count($user_messages) > 0): ?>
                <?php foreach ($user_messages as $fetch_message): ?>
                    <div class="box">
                        <span style="display:inline-block;padding:4px 10px;background:#2980b9;color:#fff;font-weight:bold;border-radius:6px;margin-bottom:8px !important; border: 2px solid #154360 !important;">CUSTOMER</span>
                        <p style="font-size:3rem;color:#111;"><strong style="font-weight:bold;">User ID:</strong> <span style="font-weight:normal;"><?= htmlspecialchars($fetch_message['user_id']); ?></span></p>
                        <p style="font-size:3rem;color:#111;"><strong style="font-weight:bold;">Name:</strong> <span style="font-weight:normal;"><?= htmlspecialchars($fetch_message['name']); ?></span></p>
                        <p style="font-size:3rem;color:#111;"><strong style="font-weight:bold;">Email:</strong> <span style="font-weight:normal;"><?= htmlspecialchars($fetch_message['email']); ?></span></p>
                        <p style="font-size:3rem;color:#111;"><strong style="font-weight:bold;">Number:</strong> <span style="font-weight:normal;"><?= htmlspecialchars($fetch_message['number']); ?></span></p>
                        <p style="font-size:3rem;color:#111;"><strong style="font-weight:bold;">Message:</strong> <span style="font-weight:normal;"><?= nl2br(htmlspecialchars($fetch_message['message'])); ?></span></p>
                        <a href="admin_messages.php?delete=<?= $fetch_message['id']; ?>" class="delete-btn" onclick="return confirm('Delete this message?');">Delete</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="empty">No user messages found.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </section>
</body>
</html>
