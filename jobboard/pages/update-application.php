<?php
require_once '../includes/config.php';

// Check if user is logged in and is an employer
if(!isLoggedIn() || !isEmployer()) {
    $_SESSION['error'] = "Unauthorized access.";
    redirect("login.php");
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $application_id = $_POST['application_id'];
    $status = $_POST['status'];
    
    // Verify that this application belongs to a job posted by this employer
    $stmt = $conn->prepare("
        SELECT a.*, j.employer_id 
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE a.id = ?
    ");
    $stmt->execute([$application_id]);
    $application = $stmt->fetch();
    
    if($application && $application['employer_id'] == $_SESSION['user_id']) {
        $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
        $stmt->execute([$status, $application_id]);
        $_SESSION['success'] = "Application status updated to " . ucfirst($status);
    } else {
        $_SESSION['error'] = "You don't have permission to update this application.";
    }
}

redirect("employer-dashboard.php");
?>