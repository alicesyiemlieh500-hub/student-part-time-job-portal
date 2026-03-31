<?php
require_once '../includes/config.php';
require_once '../includes/header.php';

$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get job details
$stmt = $conn->prepare("SELECT j.*, u.full_name as employer_name, u.email as employer_email 
                       FROM jobs j 
                       JOIN users u ON j.employer_id = u.id 
                       WHERE j.id = ?");
$stmt->execute([$job_id]);
$job = $stmt->fetch();

if(!$job) {
    $_SESSION['error'] = "Job not found.";
    redirect('job-listing.php');
}

// Check if already applied (if logged in as student)
$has_applied = false;
if(isLoggedIn() && isStudent()) {
    $stmt = $conn->prepare("SELECT id FROM applications WHERE job_id = ? AND user_id = ?");
    $stmt->execute([$job_id, $_SESSION['user_id']]);
    $has_applied = $stmt->fetch() ? true : false;
}
?>

<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="home.php">Home</a></li>
            <li class="breadcrumb-item"><a href="job-listing.php">Jobs</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($job['title']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0"><?php echo htmlspecialchars($job['title']); ?></h3>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-building me-2"></i>Company:</strong> <?php echo htmlspecialchars($job['company']); ?></p>
                            <p><strong><i class="fas fa-map-marker-alt me-2"></i>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
                            <p><strong><i class="fas fa-clock me-2"></i>Job Type:</strong> 
                                <span class="badge bg-info"><?php echo ucfirst($job['job_type']); ?></span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-money-bill-alt me-2"></i>Salary:</strong> <?php echo htmlspecialchars($job['salary']); ?></p>
                            <p><strong><i class="fas fa-tag me-2"></i>Category:</strong> <?php echo htmlspecialchars($job['category']); ?></p>
                            <p><strong><i class="fas fa-calendar me-2"></i>Posted:</strong> <?php echo formatDate($job['posted_at']); ?></p>
                        </div>
                    </div>
                    
                    <h5 class="text-primary">Job Description</h5>
                    <p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
                    
                    <?php if(!empty($job['requirements'])): ?>
                        <h5 class="text-primary mt-4">Requirements</h5>
                        <p><?php echo nl2br(htmlspecialchars($job['requirements'])); ?></p>
                    <?php endif; ?>
                    
                    <?php if(isLoggedIn() && isStudent()): ?>
                        <div class="mt-4">
                            <?php if($has_applied): ?>
                                <button class="btn btn-success" disabled>
                                    <i class="fas fa-check me-2"></i>Already Applied
                                </button>
                            <?php else: ?>
                                <a href="apply-job.php?id=<?php echo $job['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Apply Now
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php elseif(!isLoggedIn()): ?>
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <a href="login.php">Login</a> as a student to apply for this job.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Company Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Company:</strong> <?php echo htmlspecialchars($job['company']); ?></p>
                    <p><strong>Posted by:</strong> <?php echo htmlspecialchars($job['employer_name']); ?></p>
                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($job['employer_email']); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>