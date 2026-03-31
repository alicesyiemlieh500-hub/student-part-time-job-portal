<?php
require_once '../includes/config.php';

// Check if user is logged in and is an employer
if(!isLoggedIn() || !isEmployer()) {
    $_SESSION['error'] = "Please login as an employer to access this page";
    redirect("login.php");
}

$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verify job belongs to this employer
$stmt = $conn->prepare("SELECT id FROM jobs WHERE id = ? AND employer_id = ?");
$stmt->execute([$job_id, $_SESSION['user_id']]);
$job = $stmt->fetch();

if(!$job) {
    $_SESSION['error'] = "Job not found or you don't have permission to delete it.";
    redirect("employer-dashboard.php");
}

// Start transaction
$conn->beginTransaction();

try {
    // Delete applications first
    $stmt = $conn->prepare("DELETE FROM applications WHERE job_id = ?");
    $stmt->execute([$job_id]);
    
    // Delete the job
    $stmt = $conn->prepare("DELETE FROM jobs WHERE id = ? AND employer_id = ?");
    $stmt->execute([$job_id, $_SESSION['user_id']]);
    
    $conn->commit();
    $_SESSION['success'] = "Job deleted successfully!";
    
} catch(Exception $e) {
    $conn->rollBack();
    $_SESSION['error'] = "Error deleting job. Please try again.";
}

redirect("employer-dashboard.php");
?>