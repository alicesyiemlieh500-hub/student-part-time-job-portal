<?php
require_once '../includes/config.php';
require_once '../includes/header.php';

// Check if user is logged in and is an employer
if(!isLoggedIn() || !isEmployer()) {
    $_SESSION['error'] = "Please login as an employer to access this page";
    redirect("login.php");
}

$employer_id = $_SESSION['user_id'];

// Get filter
$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;

// Get employer's jobs for filter
$jobs = $conn->prepare("SELECT id, title FROM jobs WHERE employer_id = ? ORDER BY posted_at DESC");
$jobs->execute([$employer_id]);
$job_list = $jobs->fetchAll();

// Build query
$query = "
    SELECT a.*, j.title as job_title, j.location, j.job_type,
           u.id as student_id, u.full_name as student_name, u.email, u.phone,
           sp.skills, sp.education, sp.experience
    FROM applications a 
    JOIN jobs j ON a.job_id = j.id 
    JOIN users u ON a.user_id = u.id
    LEFT JOIN student_profiles sp ON u.id = sp.user_id
    WHERE j.employer_id = ?
";
$params = [$employer_id];

if($job_id > 0) {
    $query .= " AND j.id = ?";
    $params[] = $job_id;
}

$query .= " ORDER BY a.applied_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$applications = $stmt->fetchAll();
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-8">
            <h2><i class="fas fa-users"></i> Job Applications</h2>
        </div>
        <div class="col-4 text-end">
            <a href="employer-dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
        </div>
    </div>
    
    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-4">
                    <select name="job_id" class="form-control" onchange="this.form.submit()">
                        <option value="0">All Jobs</option>
                        <?php foreach($job_list as $job): ?>
                            <option value="<?php echo $job['id']; ?>" <?php echo $job_id == $job['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($job['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Applications List -->
    <?php if(empty($applications)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h5>No Applications Found</h5>
                <p class="text-muted">There are no applications matching your criteria.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach($applications as $app): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Application for: <?php echo htmlspecialchars($app['job_title']); ?></h5>
                        <span class="badge bg-<?php 
                            echo $app['status'] == 'accepted' ? 'success' : 
                                ($app['status'] == 'rejected' ? 'danger' : 'warning'); 
                        ?> fs-6"><?php echo ucfirst($app['status']); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="text-primary">Applicant Details</h6>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($app['student_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($app['email']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($app['phone'] ?? 'Not provided'); ?></p>
                        </div>
                        
                        <div class="col-md-4">
                            <h6 class="text-primary">Skills & Education</h6>
                            <p><strong>Skills:</strong><br><?php echo nl2br(htmlspecialchars($app['skills'] ?? 'Not provided')); ?></p>
                            <p><strong>Education:</strong><br><?php echo nl2br(htmlspecialchars($app['education'] ?? 'Not provided')); ?></p>
                        </div>
                        
                        <div class="col-md-4">
                            <h6 class="text-primary">Experience</h6>
                            <p><?php echo nl2br(htmlspecialchars($app['experience'] ?? 'Not provided')); ?></p>
                            
                            <form method="POST" action="update-application.php" class="mt-3">
                                <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                <div class="row">
                                    <div class="col-md-8">
                                        <select name="status" class="form-select">
                                            <option value="pending" <?php echo $app['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="accepted" <?php echo $app['status'] == 'accepted' ? 'selected' : ''; ?>>Accept</option>
                                            <option value="rejected" <?php echo $app['status'] == 'rejected' ? 'selected' : ''; ?>>Reject</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary w-100">Update</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">Applied on: <?php echo formatDate($app['applied_at']); ?></small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>