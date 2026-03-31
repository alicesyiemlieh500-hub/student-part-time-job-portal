<?php
require_once '../includes/config.php';
require_once '../includes/header.php';

// Redirect if already logged in
if(isLoggedIn()) {
    redirect(isEmployer() ? 'employer-dashboard.php' : 'user-dashboard.php');
}

// Handle login form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if(empty($username) || empty($password)) {
        $error = "Please enter both username/email and password!";
    } else {
        // Check user credentials
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['full_name'] = $user['full_name'];
            
            $_SESSION['success'] = "Welcome back, " . $user['full_name'] . "!";
            
            // Redirect based on user type
            redirect($user['user_type'] == 'employer' ? 'employer-dashboard.php' : 'user-dashboard.php');
        } else {
            $error = "Invalid username/email or password!";
        }
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card fade-in">
                <div class="card-header">
                    <h3 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Welcome Back!</h3>
                </div>
                <div class="card-body">
                    <!-- Show error message -->
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Login Form -->
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Username or Email *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="username" class="form-control" 
                                       value="<?php echo $_POST['username'] ?? ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Password *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </div>
                        
                        <div class="text-center mt-3">
                            <p class="mb-0">Don't have an account? <a href="register.php" class="text-primary">Register here</a></p>
                        </div>
                    </form>
                    
                    <hr>
                    
                    <!-- Demo Accounts -->
                    <div class="alert alert-info mb-0">
                        <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Demo Accounts:</h6>
                        <p class="mb-1 small">
                            <strong>Student:</strong> john_doe / password123<br>
                            <strong>Employer:</strong> techcorp / password123
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>