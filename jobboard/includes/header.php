<?php
require_once __DIR__ . '/config.php';

// Get current page name for active menu
$current_page = basename($_SERVER['PHP_SELF']);

// Get theme for body class - FIXED
$theme_class = $_SESSION['theme'] == 'dark' ? 'dark-mode' : 'light-mode';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PartTimeJobs - Find Student Jobs</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        /* Prevent FOUC (Flash of Unstyled Content) */
        body {
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        body.theme-ready {
            visibility: visible;
            opacity: 1;
        }
    </style>
</head>
<body class="<?php echo $theme_class; ?>">
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <!-- Logo/Brand -->
            <a class="navbar-brand text-primary fw-bold d-flex align-items-center" href="home.php">
                <img src="../includes/image/DonBosco-Color_200px.jpg" 
                    alt="Company Logo" 
                    style="width: 50px; height: auto; margin-right: 10px;">
                ADBU PartTimeJobs
            </a>
            
            <!-- Mobile menu button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'home.php' ? 'active' : ''; ?>" 
                           href="home.php">
                           <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'job-listing.php' ? 'active' : ''; ?>" 
                           href="job-listing.php">
                           <i class="fas fa-list"></i> Jobs
                        </a>
                    </li>
                    
                    <?php if(isLoggedIn()): ?>
                        <!-- User is logged in -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo isEmployer() ? 'employer-dashboard.php' : 'user-dashboard.php'; ?>">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <?php if(isEmployer()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="post-job.php">
                                <i class="fas fa-plus-circle"></i> Post Job
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                        <li class="nav-item">
                            <span class="nav-link text-primary">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                            </span>
                        </li>
                    <?php else: ?>
                        <!-- User not logged in -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'login.php' ? 'active' : ''; ?>" 
                               href="login.php">
                               <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'register.php' ? 'active' : ''; ?>" 
                               href="register.php">
                               <i class="fas fa-user-plus"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Admin link -->
                    <li class="nav-item">
                        <a class="nav-link text-muted" href="admin/login.php" title="Admin">
                            <i class="fas fa-user-shield"></i>
                        </a>
                    </li>
                    
                    <!-- Dark/Light mode toggle - FIXED -->
                    <li class="nav-item">
                        <button id="themeToggle" class="btn btn-outline-primary ms-2">
                            <i class="fas <?php echo $_SESSION['theme'] == 'dark' ? 'fa-sun' : 'fa-moon'; ?>"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main content starts -->
    <main class="py-4">
        <!-- Display flash messages -->
        <?php if(isset($_SESSION['success'])): ?>
            <div class="container">
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="container">
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        <?php endif; ?>