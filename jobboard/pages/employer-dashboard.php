<?php
require_once '../includes/config.php';
require_once '../includes/header.php';

// Check if user is logged in and is an employer
if(!isLoggedIn() || !isEmployer()) {
    $_SESSION['error'] = "Please login as an employer to access this page";
    redirect("login.php");
}

$user_id = $_SESSION['user_id'];

// Handle job status updates
if(isset($_GET['action']) && isset($_GET['job_id'])) {
    $job_id = $_GET['job_id'];
    $action = $_GET['action'];
    
    if($action == 'close') {
        $stmt = $conn->prepare("UPDATE jobs SET status = 'closed' WHERE id = ? AND employer_id = ?");
        $stmt->execute([$job_id, $user_id]);
        $_SESSION['success'] = "Job closed successfully";
    } elseif($action == 'activate') {
        $stmt = $conn->prepare("UPDATE jobs SET status = 'active' WHERE id = ? AND employer_id = ?");
        $stmt->execute([$job_id, $user_id]);
        $_SESSION['success'] = "Job activated successfully";
    } elseif($action == 'delete') {
        // First delete applications for this job
        $stmt = $conn->prepare("DELETE FROM applications WHERE job_id = ?");
        $stmt->execute([$job_id]);
        
        // Then delete the job
        $stmt = $conn->prepare("DELETE FROM jobs WHERE id = ? AND employer_id = ?");
        $stmt->execute([$job_id, $user_id]);
        $_SESSION['success'] = "Job deleted successfully";
    }
    
    redirect("employer-dashboard.php");
}

// Fetch employer's jobs
$stmt = $conn->prepare("SELECT * FROM jobs WHERE employer_id = ? ORDER BY posted_at DESC");
$stmt->execute([$user_id]);
$jobs = $stmt->fetchAll();

// Fetch employer profile
$stmt = $conn->prepare("SELECT * FROM employer_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch();

// Get application statistics
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_applications,
        SUM(CASE WHEN a.status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN a.status = 'accepted' THEN 1 ELSE 0 END) as accepted,
        SUM(CASE WHEN a.status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    WHERE j.employer_id = ?
");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

// Get recent applications
$stmt = $conn->prepare("
    SELECT a.*, j.title, u.full_name as applicant_name, u.email 
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN users u ON a.user_id = u.id
    WHERE j.employer_id = ?
    ORDER BY a.applied_at DESC
    LIMIT 10
");
$stmt->execute([$user_id]);
$recent_applications = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="dashboard-sidebar">
                <div class="text-center mb-4">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['full_name']); ?>&size=100&background=0D6EFD&color=fff" 
                         class="rounded-circle mb-3" alt="Profile">
                    <h5 class="mb-1"><?php echo htmlspecialchars($_SESSION['full_name']); ?></h5>
                    <p class="text-muted mb-2"><?php echo htmlspecialchars($profile['company_name'] ?? 'Employer'); ?></p>
                    <span class="badge bg-primary">Employer</span>
                </div>
                
                <ul class="dashboard-menu">
                    <li><a href="#" class="active" onclick="showSection('overview')"><i class="fas fa-tachometer-alt"></i> Overview</a></li>
                    <li><a href="#" onclick="showSection('jobs')"><i class="fas fa-briefcase"></i> My Jobs</a></li>
                    <li><a href="#" onclick="showSection('applications')"><i class="fas fa-users"></i> Applications</a></li>
                    <li><a href="post-job.php"><i class="fas fa-plus-circle"></i> Post New Job</a></li>
                    <li><a href="#" onclick="showSection('profile')"><i class="fas fa-building"></i> Company Profile</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <!-- Overview Section -->
            <div id="overview-section">
                <h4 class="mb-4"><i class="fas fa-tachometer-alt me-2 text-primary"></i>Dashboard Overview</h4>
                
                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3 col-6">
                        <div class="stat-card">
                            <i class="fas fa-briefcase"></i>
                            <h3><?php echo count($jobs); ?></h3>
                            <p>Total Jobs</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-card" style="background: linear-gradient(135deg, #17a2b8, #138496);">
                            <i class="fas fa-users"></i>
                            <h3><?php echo $stats['total_applications'] ?? 0; ?></h3>
                            <p>Applications</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-card" style="background: linear-gradient(135deg, #ffc107, #ff9800);">
                            <i class="fas fa-clock"></i>
                            <h3><?php echo $stats['pending'] ?? 0; ?></h3>
                            <p>Pending</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-card" style="background: linear-gradient(135deg, #28a745, #20c997);">
                            <i class="fas fa-check-circle"></i>
                            <h3><?php echo $stats['accepted'] ?? 0; ?></h3>
                            <p>Accepted</p>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <a href="post-job.php" class="btn btn-primary me-2 mb-2">
                                    <i class="fas fa-plus-circle me-2"></i>Post New Job
                                </a>
                                <a href="#" onclick="showSection('applications')" class="btn btn-outline-primary me-2 mb-2">
                                    <i class="fas fa-users me-2"></i>View Applications
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Applications -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Applications</h5>
                        <a href="#" onclick="showSection('applications')" class="btn btn-sm btn-light">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Applicant</th>
                                        <th>Job Title</th>
                                        <th>Applied On</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($recent_applications)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                                <p class="mb-0">No applications yet</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($recent_applications as $app): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($app['applicant_name']); ?></td>
                                                <td><?php echo htmlspecialchars($app['title']); ?></td>
                                                <td><?php echo formatDate($app['applied_at']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $app['status'] == 'accepted' ? 'success' : 
                                                            ($app['status'] == 'rejected' ? 'danger' : 'warning'); 
                                                    ?>">
                                                        <?php echo ucfirst($app['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="view-applications.php?id=<?php echo $app['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Jobs Section -->
            <div id="jobs-section" style="display: none;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4><i class="fas fa-briefcase me-2 text-primary"></i>My Job Postings</h4>
                    <a href="post-job.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>Post New Job
                    </a>
                </div>
                
                <?php if(empty($jobs)): ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                            <h5>No jobs posted yet</h5>
                            <p class="text-muted">Start by posting your first job opportunity!</p>
                            <a href="post-job.php" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i>Post a Job
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach($jobs as $job): ?>
                        <div class="job-card">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h5 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h5>
                                    <p class="company mb-2">
                                        <i class="fas fa-building me-2"></i><?php echo htmlspecialchars($job['company']); ?>
                                    </p>
                                    <div class="job-meta mb-2">
                                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?></span>
                                        <span><i class="fas fa-clock"></i> <?php echo ucfirst($job['job_type']); ?></span>
                                        <span><i class="fas fa-money-bill-alt"></i> <?php echo htmlspecialchars($job['salary']); ?></span>
                                    </div>
                                    <p class="mb-0">
                                        <small class="text-muted">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            Posted on <?php echo formatDate($job['posted_at']); ?>
                                        </small>
                                    </p>
                                </div>
                                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                    <span class="badge bg-<?php echo $job['status'] == 'active' ? 'success' : 'secondary'; ?> mb-2">
                                        <?php echo ucfirst($job['status']); ?>
                                    </span>
                                    <div class="btn-group">
                                        <a href="view-applications.php?job_id=<?php echo $job['id']; ?>" 
                                           class="btn btn-sm btn-outline-info" title="View Applications">
                                            <i class="fas fa-users"></i> Applications
                                        </a>
                                        <?php if($job['status'] == 'active'): ?>
                                            <a href="?action=close&job_id=<?php echo $job['id']; ?>" 
                                               class="btn btn-sm btn-outline-warning" title="Close Job"
                                               onclick="return confirm('Are you sure you want to close this job?')">
                                                <i class="fas fa-pause"></i> Close
                                            </a>
                                        <?php else: ?>
                                            <a href="?action=activate&job_id=<?php echo $job['id']; ?>" 
                                               class="btn btn-sm btn-outline-success" title="Activate Job"
                                               onclick="return confirm('Activate this job?')">
                                                <i class="fas fa-play"></i> Activate
                                            </a>
                                        <?php endif; ?>
                                        <a href="?action=delete&job_id=<?php echo $job['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this job? This will also delete all applications.')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Applications Section -->
            <div id="applications-section" style="display: none;">
                <h4 class="mb-4"><i class="fas fa-users me-2 text-primary"></i>All Applications</h4>
                
                <?php if(empty($recent_applications)): ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5>No Applications Yet</h5>
                            <p class="text-muted">Applications will appear here when students apply to your jobs.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach($recent_applications as $app): ?>
                        <div class="card mb-3">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Application for: <?php echo htmlspecialchars($app['title']); ?></h5>
                                    <span class="badge bg-<?php 
                                        echo $app['status'] == 'accepted' ? 'success' : 
                                            ($app['status'] == 'rejected' ? 'danger' : 'warning'); 
                                    ?> fs-6">Status: <?php echo ucfirst($app['status']); ?></span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <h6 class="text-primary">Applicant Information</h6>
                                        <p><strong>Name:</strong> <?php echo htmlspecialchars($app['applicant_name']); ?></p>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($app['email']); ?></p>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-primary">Application Details</h6>
                                        <p><strong>Applied on:</strong> <?php echo formatDate($app['applied_at']); ?></p>
                                        
                                        <!-- Update Status Form -->
                                        <form method="POST" action="update-application.php" class="mt-3">
                                            <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <select name="status" class="form-select">
                                                        <option value="pending" <?php echo $app['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="accepted" <?php echo $app['status'] == 'accepted' ? 'selected' : ''; ?>>Accept</option>
                                                        <option value="rejected" <?php echo $app['status'] == 'rejected' ? 'selected' : ''; ?>>Reject</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <button type="submit" class="btn btn-primary">Update Status</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Company Profile Section -->
            <div id="profile-section" style="display: none;">
                <h4 class="mb-4"><i class="fas fa-building me-2 text-primary"></i>Company Profile</h4>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="update-company-profile.php">
                            <div class="mb-3">
                                <label class="form-label">Company Name</label>
                                <input type="text" name="company_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($profile['company_name'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Company Description</label>
                                <textarea name="company_description" class="form-control" rows="4"><?php echo htmlspecialchars($profile['company_description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Website</label>
                                    <input type="url" name="website" class="form-control" 
                                           value="<?php echo htmlspecialchars($profile['website'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Location</label>
                                    <input type="text" name="location" class="form-control" 
                                           value="<?php echo htmlspecialchars($profile['location'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showSection(section) {
    // Hide all sections
    document.getElementById('overview-section').style.display = 'none';
    document.getElementById('jobs-section').style.display = 'none';
    document.getElementById('applications-section').style.display = 'none';
    document.getElementById('profile-section').style.display = 'none';
    
    // Show selected section
    document.getElementById(section + '-section').style.display = 'block';
    
    // Update active menu
    document.querySelectorAll('.dashboard-menu a').forEach(link => {
        link.classList.remove('active');
    });
    event.target.closest('a').classList.add('active');
}
</script>

<?php require_once '../includes/footer.php'; ?>