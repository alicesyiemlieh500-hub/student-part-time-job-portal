<?php

// Function to redirect - MAKE SURE THIS EXISTS
function redirect($url) {
    // Make sure no output has been sent
    if (!headers_sent()) {
        header("Location: $url");
    } else {
        // If headers already sent, use JavaScript
        echo "<script>window.location.href='$url';</script>";
    }
    exit();
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$host = 'localhost';
$dbname = 'jobboard';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Set error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative array
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Set default timezone
date_default_timezone_set('Asia/Kolkata');

// THEME HANDLING - FIXED
// Check if theme is set in session, if not check cookie, else default to light
if(!isset($_SESSION['theme'])) {
    if(isset($_COOKIE['theme'])) {
        $_SESSION['theme'] = $_COOKIE['theme'];
    } else {
        $_SESSION['theme'] = 'light';
    }
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is student
function isStudent() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'student';
}

// Function to check if user is employer
function isEmployer() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'employer';
}

// Function to check if admin is logged in
function isAdmin() {
    return isset($_SESSION['admin_id']);
}



// Function to sanitize input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Function to get user by ID
function getUserById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Function to get job by ID
function getJobById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT j.*, u.full_name as employer_name FROM jobs j JOIN users u ON j.employer_id = u.id WHERE j.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Function to format date
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

// Function to time ago
function timeAgo($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);
    $months = round($seconds / 2629440);
    $years = round($seconds / 31553280);
    
    if ($seconds <= 60) {
        return "Just Now";
    } else if ($minutes <= 60) {
        return ($minutes == 1) ? "1 minute ago" : "$minutes minutes ago";
    } else if ($hours <= 24) {
        return ($hours == 1) ? "1 hour ago" : "$hours hours ago";
    } else if ($days <= 7) {
        return ($days == 1) ? "yesterday" : "$days days ago";
    } else if ($weeks <= 4.3) {
        return ($weeks == 1) ? "1 week ago" : "$weeks weeks ago";
    } else if ($months <= 12) {
        return ($months == 1) ? "1 month ago" : "$months months ago";
    } else {
        return ($years == 1) ? "1 year ago" : "$years years ago";
    }
}
?>