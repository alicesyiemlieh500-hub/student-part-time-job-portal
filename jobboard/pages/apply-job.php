<?php
require_once '../includes/config.php';
require_once '../includes/header.php';

// Check if user is logged in and is a student
if(!isLoggedIn() || !isStudent()) {
    $_SESSION['error'] = "Please login as a student to apply for jobs.";
    redirect("login.php");
}

$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if already applied
$stmt = $conn->prepare("SELECT * FROM applications WHERE job_id = ? AND user_id = ?");
$stmt->execute([$job_id, $_SESSION['user_id']]);
if($stmt->rowCount() > 0) {
    $_SESSION['error'] = "You have already applied for this job.";
    redirect("user-dashboard.php");
}

// Get job details
$stmt = $conn->prepare("SELECT j.*, u.full_name as employer_name FROM jobs j JOIN users u ON j.employer_id = u.id WHERE j.id = ?");
$stmt->execute([$job_id]);
$job = $stmt->fetch();

if(!$job) {
    $_SESSION['error'] = "Job not found.";
    redirect("job-listing.php");
}

// Handle application submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $conn->prepare("INSERT INTO applications (job_id, user_id) VALUES (?, ?)");
    if($stmt->execute([$job_id, $_SESSION['user_id']])) {
        $_SESSION['success'] = "Application submitted successfully!";
        redirect("user-dashboard.php");
    } else {
        $error = "Failed to submit application. Please try again.";
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Apply for Job</h3>
                </div>
                <div class="card-body">
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info">
                        <h5><?php echo htmlspecialchars($job['title']); ?></h5>
                        <p class="mb-0">
                            <strong>Company:</strong> <?php echo htmlspecialchars($job['company']); ?><br>
                            <strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?><br>
                            <strong>Type:</strong> <?php echo ucfirst($job['job_type']); ?>
                        </p>
                    </div>
                    
                    <form method="POST">
                        <p>Are you sure you want to apply for this position?</p>
                        
                        <button type="submit"  class="btn btn-success">
                            <i class="fas fa-check me-2" href="job-details.php?id=<?php echo $job['id']; ?>"></i>Confirm Application
                        </button>
                        <a href="job-details.php?id=<?php echo $job['id']; ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>