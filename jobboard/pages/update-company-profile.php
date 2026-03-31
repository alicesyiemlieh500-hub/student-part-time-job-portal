<?php
require_once '../includes/config.php';

// Check if user is logged in and is an employer
if(!isLoggedIn() || !isEmployer()) {
    $_SESSION['error'] = "Please login as an employer to access this page";
    redirect("login.php");
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company_name = sanitize($_POST['company_name']);
    $company_description = sanitize($_POST['company_description']);
    $website = sanitize($_POST['website']);
    $location = sanitize($_POST['location']);
    
    // Check if profile exists
    $stmt = $conn->prepare("SELECT id FROM employer_profiles WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $profile = $stmt->fetch();
    
    if($profile) {
        // Update existing profile
        $stmt = $conn->prepare("UPDATE employer_profiles SET company_name=?, company_description=?, website=?, location=? WHERE user_id=?");
        $stmt->execute([$company_name, $company_description, $website, $location, $_SESSION['user_id']]);
    } else {
        // Create new profile
        $stmt = $conn->prepare("INSERT INTO employer_profiles (user_id, company_name, company_description, website, location) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $company_name, $company_description, $website, $location]);
    }
    
    $_SESSION['success'] = "Company profile updated successfully!";
    redirect("employer-dashboard.php");
} else {
    redirect("employer-dashboard.php");
}
?>