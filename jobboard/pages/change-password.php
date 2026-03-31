<?php
require_once '../includes/config.php';

// Check if user is logged in
if(!isLoggedIn()) {
    $_SESSION['error'] = "Please login to access this page";
    redirect("login.php");
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Get current user
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    // Verify current password
    if(!password_verify($current_password, $user['password'])) {
        $_SESSION['error'] = "Current password is incorrect!";
        redirect("employer-dashboard.php");
    }
    
    // Check new password length
    if(strlen($new_password) < 6) {
        $_SESSION['error'] = "New password must be at least 6 characters!";
        redirect("employer-dashboard.php");
    }
    
    // Check if passwords match
    if($new_password !== $confirm_password) {
        $_SESSION['error'] = "New passwords do not match!";
        redirect("employer-dashboard.php");
    }
    
    // Update password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed_password, $_SESSION['user_id']]);
    
    $_SESSION['success'] = "Password changed successfully!";
    
    // Redirect based on user type
    if(isEmployer()) {
        redirect("employer-dashboard.php");
    } else {
        redirect("user-dashboard.php");
    }
} else {
    redirect(isEmployer() ? "employer-dashboard.php" : "user-dashboard.php");
}
?>