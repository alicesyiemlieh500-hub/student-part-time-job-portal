<?php
require_once '../includes/config.php';
require_once '../includes/header.php';

// Check if user is logged in and is an employer
if (!isLoggedIn() || !isEmployer()) {
    $_SESSION['error'] = "Please login as an employer to post jobs.";
    redirect("login.php");
}

// Handle job posting
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $company = sanitize($_POST['company']);
    $description = sanitize($_POST['description']);
    $requirements = sanitize($_POST['requirements']);
    $location = sanitize($_POST['location']);
    $salary = sanitize($_POST['salary']);  // Fixed the syntax error here
    $job_type = $_POST['job_type'];
    $category = $_POST['category'];
    
    $stmt = $conn->prepare("INSERT INTO jobs (employer_id, title, company, description, requirements, location, salary, job_type, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$_SESSION['user_id'], $title, $company, $description, $requirements, $location, $salary, $job_type, $category])) {
        $_SESSION['success'] = "Job posted successfully!";
        redirect("employer-dashboard.php");
    } else {
        $error = "Error posting job. Please try again.";
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Post a New Job</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Job Title *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Company Name *</label>
                            <input type="text" name="company" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Job Description *</label>
                            <textarea name="description" class="form-control" rows="5" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Requirements</label>
                            <textarea name="requirements" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Location *</label>
                                <input type="text" name="location" class="form-control" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Salary *</label>
                                <input type="text" name="salary" class="form-control" placeholder="e.g., Rs. 500-700/hour" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Job Type *</label>
                                <select name="job_type" class="form-control" required>
                                    <option value="">Select Type</option>
                                    <option value="remote">Remote</option>
                                    <option value="hybrid">Hybrid</option>
                                    <option value="onsite">Onsite</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category *</label>
                                <select name="category" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <option value="Retail">Retail</option>
                                    <option value="Food Service">Food Service</option>
                                    <option value="Tutoring">Tutoring</option>
                                    <option value="Admin">Administrative</option>
                                    <option value="Tech">Technology</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Post Job</button>
                        <a href="employer-dashboard.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>