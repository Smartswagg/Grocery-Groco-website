<?php
@include 'seller_header.php';
@include 'config.php';

$seller_id = $_SESSION['seller_id'] ?? null;
if(!$seller_id){
    header('location:seller_login.php');
    exit;
}

// Fetch current seller info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND user_type = 'seller'");
$stmt->execute([$seller_id]);
$seller = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle profile update
if(isset($_POST['update_profile'])){
    // Handle profile photo upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $img_name = $_FILES['profile_image']['name'];
        $img_tmp = $_FILES['profile_image']['tmp_name'];
        $img_size = $_FILES['profile_image']['size'];
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($img_ext, $allowed_ext) && $img_size <= 2 * 1024 * 1024) { // 2MB limit
            $new_img_name = 'seller_' . $seller_id . '_' . time() . '.' . $img_ext;
            $upload_path = 'uploaded_img/' . $new_img_name;
            if (move_uploaded_file($img_tmp, $upload_path)) {
                // Remove old image if exists and not default
                if (!empty($seller['image']) && file_exists('uploaded_img/' . $seller['image'])) {
                    @unlink('uploaded_img/' . $seller['image']);
                }
                // Update DB
                $stmt_img = $conn->prepare("UPDATE users SET image = ? WHERE id = ? AND user_type = 'seller'");
                $stmt_img->execute([$new_img_name, $seller_id]);
                $message = 'Profile photo updated!';
                // Refresh seller info
                $stmt->execute([$seller_id]);
                $seller = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $message = 'Failed to upload image.';
            }
        } else {
            $message = 'Invalid image file (must be jpg, jpeg, png, gif, <2MB).';
        }
    }

    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $company_name = filter_input(INPUT_POST, 'company_name', FILTER_SANITIZE_STRING);
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $password_change = false;
    $password_error = '';
    if($name && $email) {
        // Check if email already exists for another user
        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_email->execute([$email, $seller_id]);
        if($check_email->rowCount() > 0){
            $message[] = 'Email already exists!';
        } else {
            // Password change logic
            if($old_password || $new_password || $confirm_password) {
                // All password fields must be filled
                if(!$old_password || !$new_password || !$confirm_password) {
                    $password_error = 'Please fill all password fields to change your password.';
                } else {
                    // Check old password
                    $stmt_pw = $conn->prepare("SELECT password FROM users WHERE id = ? AND user_type = 'seller'");
                    $stmt_pw->execute([$seller_id]);
                    $current_hash = $stmt_pw->fetchColumn();
                    if(md5($old_password) !== $current_hash) {
                        $password_error = 'Old password is incorrect.';
                    } elseif($new_password !== $confirm_password) {
                        $password_error = 'New passwords do not match.';
                    } else {
                        // Update password
                        $update_pw = $conn->prepare("UPDATE users SET password = ? WHERE id = ? AND user_type = 'seller'");
                        $update_pw->execute([md5($new_password), $seller_id]);
                        $password_change = true;
                    }
                }
            }
            // Update name/email/company_name
            $update = $conn->prepare("UPDATE users SET name = ?, email = ?, company_name = ? WHERE id = ? AND user_type = 'seller'");
            $update->execute([$name, $email, $company_name, $seller_id]);
            $message = 'Profile updated successfully!';
            if($password_change) $message .= ' Password changed!';
            if($password_error) $message = $password_error;
            // Refresh seller info
            $stmt->execute([$seller_id]);
            $seller = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } else {
        $message = 'Please fill in all fields.';
    }
} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Profile</title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
    <section class="dashboard">
        <h1 class="title">Update Profile</h1>
        <div class="box-container">
            <div class="box">
                <?php if(isset($message)) echo '<p style="color:green;">'.htmlspecialchars($message).'</p>'; ?>
                <form method="post" enctype="multipart/form-data">
                    <?php if (!empty($seller['image'])): ?>
                        <img src="uploaded_img/<?= htmlspecialchars($seller['image']) ?>" alt="Profile Photo" style="width:100px;height:100px;border-radius:50%;object-fit:cover;margin-bottom:10px;">
                    <?php endif; ?>
                    <label>Profile Photo:</label>
                    <input type="file" name="profile_image" accept="image/*"><br><br>
                    <label>Name:</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($seller['name'] ?? '') ?>" required><br><br>
                    <label>Email:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($seller['email'] ?? '') ?>" required><br><br>
                    <label>Company Name:</label>
                    <input type="text" name="company_name" value="<?= htmlspecialchars($seller['company_name'] ?? '') ?>" class="box" required><br><br>
                    <hr>
                    <h4>Change Password (optional)</h4>
                    <label>Old Password:</label>
                    <input type="password" name="old_password" placeholder="Enter old password"><br><br>
                    <label>New Password:</label>
                    <input type="password" name="new_password" placeholder="Enter new password"><br><br>
                    <label>Confirm New Password:</label>
                    <input type="password" name="confirm_password" placeholder="Confirm new password"><br><br>
                    <input type="submit" name="update_profile" value="Update Profile" class="btn">
                </form>
            </div>
        </div>
    </section>
</body>
</html>
