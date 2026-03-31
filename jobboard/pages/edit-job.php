<?php
require_once '../includes/config.php';
require_once '../includes/header.php';

// Check if user is logged in and is an employer
if(!isLoggedIn() || !isEmployer()) {
    $_SESSION['error'] = "Please login as an employer to access this page";
    redirect("login.php");
}

$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get job details
$stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ? AND employer_id = ?");
$stmt->execute([$job_id, $_SESSION['user_id']]);
$job = $stmt->fetch();

if(!$job) {
    $_SESSION['error'] = "Job not found or you don't have permission to edit it.";
    redirect("employer-dashboard.php");
}

// Handle job update
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $company = sanitize($_POST['company']);
    $description = sanitize($_POST['description']);
    $requirements = sanitize($_POST['requirements']);
    $location = sanitize($_POST['location']);
    $salary = sanitize($_POST['salary']);
    $job_type = $_POST['job_type'];
    $category = $_POST['category'];
    
    $stmt = $conn->prepare("UPDATE jobs SET title=?, company=?, description=?, requirements=?, location=?, salary=?, job_type=?, category=? WHERE id=? AND employer_id=?");
    
    if($stmt->execute([$title, $company, $description, $requirements, $location, $salary, $job_type, $category, $job_id, $_SESSION['user_id']])) {
        $_SESSION['success'] = "Job updated successfully!";
        redirect("employer-dashboard.php");
    } else {
        $error = "Error updating job. Please try again.";
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Job</h3>
                </div>
                <div class="card-body">
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Job Title *</label>
                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($job['title']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Company Name *</label>
                            <input type="text" name="company" class="form-control" value="<?php echo htmlspecialchars($job['company']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Job Description *</label>
                            <textarea name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($job['description']); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Requirements</label>
                            <textarea name="requirements" class="form-control" rows="3"><?php echo htmlspecialchars($job['requirements']); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Location *</label>
                                <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($job['location']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Salary *</label>
                                <input type="text" name="salary" class="form-control" value="<?php echo htmlspecialchars($job['salary']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Job Type *</label>
                                <select name="job_type" class="form-control" required>
                                    <option value="remote" <?php echo $job['job_type'] == 'remote' ? 'selected' : ''; ?>>Remote</option>
                                    <option value="hybrid" <?php echo $job['job_type'] == 'hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                                    <option value="onsite" <?php echo $job['job_type'] == 'onsite' ? 'selected' : ''; ?>>Onsite</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category *</label>
                                <select name="category" class="form-control" required>
                                    <option value="Retail" <?php echo $job['category'] == 'Retail' ? 'selected' : ''; ?>>Retail</option>
                                    <option value="Food Service" <?php echo $job['category'] == 'Food Service' ? 'selected' : ''; ?>>Food Service</option>
                                    <option value="Tutoring" <?php echo $job['category'] == 'Tutoring' ? 'selected' : ''; ?>>Tutoring</option>
                                    <option value="Admin" <?php echo $job['category'] == 'Admin' ? 'selected' : ''; ?>>Administrative</option>
                                    <option value="Tech" <?php echo $job['category'] == 'Tech' ? 'selected' : ''; ?>>Technology</option>
                                    <option value="Other" <?php echo $job['category'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Update Job</button>
                        <a href="employer-dashboard.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>