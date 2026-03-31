<?php
require_once '../includes/config.php';
require_once '../includes/header.php';

$company_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get company details
$stmt = $conn->prepare("SELECT u.*, ep.* FROM users u 
                       LEFT JOIN employer_profiles ep ON u.id = ep.user_id 
                       WHERE u.id = ? AND u.user_type = 'employer'");
$stmt->execute([$company_id]);
$company = $stmt->fetch();

if(!$company) {
    $_SESSION['error'] = "Company not found.";
    redirect("job-listing.php");
}

// Get jobs from this company
$stmt = $conn->prepare("SELECT * FROM jobs WHERE employer_id = ? AND status = 'active' ORDER BY posted_at DESC");
$stmt->execute([$company_id]);
$jobs = $stmt->fetchAll();
?>

<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="company-logo mb-3">
                        <i class="fas fa-building fa-5x text-primary"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($company['company_name'] ?? $company['full_name']); ?></h3>
                    <?php if(!empty($company['location'])): ?>
                        <p class="text-muted"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($company['location']); ?></p>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <?php if(!empty($company['website'])): ?>
                        <p class="mb-2">
                            <i class="fas fa-globe me-2"></i>
                            <a href="<?php echo $company['website']; ?>" target="_blank">Website</a>
                        </p>
                    <?php endif; ?>
                    
                    <p class="mb-2">
                        <i class="fas fa-envelope me-2"></i>
                        <a href="mailto:<?php echo $company['email']; ?>"><?php echo $company['email']; ?></a>
                    </p>
                    
                    <?php if(!empty($company['phone'])): ?>
                        <p class="mb-2">
                            <i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($company['phone']); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">About Company</h5>
                </div>
                <div class="card-body">
                    <?php if(!empty($company['company_description'])): ?>
                        <p><?php echo nl2br(htmlspecialchars($company['company_description'])); ?></p>
                    <?php else: ?>
                        <p class="text-muted">No description provided.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Active Jobs (<?php echo count($jobs); ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if(empty($jobs)): ?>
                        <p class="text-muted">No active jobs at the moment.</p>
                    <?php else: ?>
                        <?php foreach($jobs as $job): ?>
                            <div class="job-card">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h5 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h5>
                                        <div class="job-meta">
                                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?></span>
                                            <span><i class="fas fa-clock"></i> <?php echo ucfirst($job['job_type']); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <p class="salary">💰 <?php echo htmlspecialchars($job['salary']); ?></p>
                                        <a href="job-details.php?id=<?php echo $job['id']; ?>" class="btn btn-primary btn-sm">View Job</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>