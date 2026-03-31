<?php
require_once '../includes/config.php';
require_once '../includes/header.php';

// Redirect if already logged in
if(isLoggedIn()) {
    redirect(isEmployer() ? 'employer-dashboard.php' : 'user-dashboard.php');
}

// Handle registration form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = $_POST['user_type'];
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone'] ?? '');
    
    // Validate
    $errors = [];
    
    if(strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if(empty($errors)) {
        try {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert into users table
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, user_type, full_name, phone) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password, $user_type, $full_name, $phone]);
            
            $user_id = $conn->lastInsertId();
            
            // Create profile based on user type
            if($user_type == 'student') {
                $stmt = $conn->prepare("INSERT INTO student_profiles (user_id) VALUES (?)");
                $stmt->execute([$user_id]);
            } else {
                $stmt = $conn->prepare("INSERT INTO employer_profiles (user_id, company_name) VALUES (?, ?)");
                $stmt->execute([$user_id, $full_name]);
            }
            
            $_SESSION['success'] = "Registration successful! Please login.";
            redirect("login.php");
            
        } catch(PDOException $e) {
            if($e->errorInfo[1] == 1062) {
                $errors[] = "Username or email already exists!";
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card fade-in">
                <div class="card-header">
                    <h3 class="mb-0"><i class="fas fa-user-plus me-2"></i>Create Your Account</h3>
                </div>
                <div class="card-body">
                    <!-- Show errors -->
                    <?php if(!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Registration Form -->
                    <form method="POST" action="" onsubmit="return validateForm('registerForm')" id="registerForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="full_name" class="form-control" 
                                       value="<?php echo $_POST['full_name'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username *</label>
                                <input type="text" name="username" class="form-control" 
                                       value="<?php echo $_POST['username'] ?? ''; ?>" required>
                                <small class="text-muted">Choose a unique username</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo $_POST['email'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" 
                                       value="<?php echo $_POST['phone'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password *</label>
                                <input type="password" name="password" id="password" class="form-control" 
                                       onkeyup="checkPasswordStrength(this.value)" required>
                                <div class="progress mt-2" style="height: 5px;">
                                    <div id="passwordStrength" class="progress-bar" style="width: 0%"></div>
                                </div>
                                <small id="passwordFeedback" class="text-muted"></small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password *</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">I want to register as *</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check p-3 border rounded <?php echo (!isset($_POST['user_type']) || $_POST['user_type'] == 'student') ? 'bg-light-blue' : ''; ?>">
                                        <input class="form-check-input" type="radio" name="user_type" id="student" value="student" 
                                               <?php echo (!isset($_POST['user_type']) || $_POST['user_type'] == 'student') ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="student">
                                            <i class="fas fa-user-graduate me-2 text-primary"></i>
                                            <strong>Student</strong>
                                            <p class="small text-muted mb-0 mt-1">Looking for part-time jobs</p>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check p-3 border rounded <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'employer') ? 'bg-light-blue' : ''; ?>">
                                        <input class="form-check-input" type="radio" name="user_type" id="employer" value="employer"
                                               <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'employer') ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="employer">
                                            <i class="fas fa-building me-2 text-primary"></i>
                                            <strong>Employer</strong>
                                            <p class="small text-muted mb-0 mt-1">Hiring students for jobs</p>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </div>
                        
                        <div class="text-center mt-3">
                            <p class="mb-0">Already have an account? <a href="login.php" class="text-primary">Login here</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>