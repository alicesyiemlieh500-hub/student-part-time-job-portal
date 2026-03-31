<?php
require_once '../includes/config.php';
require_once '../includes/header.php';

// Get filter parameters
$search = $_GET['search'] ?? '';
$job_type = $_GET['job_type'] ?? '';
$location = $_GET['location'] ?? '';

// Build query
$query = "SELECT j.*, u.full_name as employer_name FROM jobs j JOIN users u ON j.employer_id = u.id WHERE j.status = 'active'";
$params = [];

if(!empty($search)) {
    $query .= " AND (j.title LIKE ? OR j.company LIKE ? OR j.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if(!empty($job_type)) {
    $query .= " AND j.job_type = ?";
    $params[] = $job_type;
}

if(!empty($location)) {
    $query .= " AND j.location LIKE ?";
    $params[] = "%$location%";
}

$query .= " ORDER BY j.posted_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

// Get unique locations for filter
$locations = $conn->query("SELECT DISTINCT location FROM jobs WHERE status='active'")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="container">
    <!-- Filter Section -->
    <div class="filter-section">
        <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filter Jobs</h5>
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search jobs..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <select name="job_type" class="form-control">
                    <option value="">All Types</option>
                    <option value="remote" <?php echo $job_type == 'remote' ? 'selected' : ''; ?>>Remote</option>
                    <option value="hybrid" <?php echo $job_type == 'hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                    <option value="onsite" <?php echo $job_type == 'onsite' ? 'selected' : ''; ?>>Onsite</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="location" class="form-control">
                    <option value="">All Locations</option>
                    <?php foreach($locations as $loc): ?>
                        <option value="<?php echo htmlspecialchars($loc); ?>" <?php echo $location == $loc ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($loc); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
    
    <!-- Results count -->
    <div class="mb-3">
        <p class="text-muted">Found <strong><?php echo count($jobs); ?></strong> jobs</p>
    </div>
    
    <!-- Job Listings -->
    <?php if(empty($jobs)): ?>
        <div class="alert alert-info">No jobs found matching your criteria.</div>
    <?php else: ?>
        <?php foreach($jobs as $job): ?>
        <div class="job-card" data-job-type="<?php echo $job['job_type']; ?>" data-location="<?php echo $job['location']; ?>">
            <div class="row">
                <div class="col-md-8">
                    <h4 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h4>
                    <p class="company">
                        <i class="fas fa-building me-2"></i><?php echo htmlspecialchars($job['company']); ?>
                    </p>
                    <div class="job-meta">
                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?></span>
                        <span><i class="fas fa-clock"></i> <?php echo ucfirst($job['job_type']); ?></span>
                        <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($job['category']); ?></span>
                    </div>
                    <p class="mt-2"><?php echo substr(htmlspecialchars($job['description']), 0, 150); ?>...</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <p class="salary mb-2">💰 <?php echo htmlspecialchars($job['salary']); ?></p>
                    <p class="text-muted small mb-2">
                        <i class="far fa-clock me-1"></i>Posted <?php echo timeAgo($job['posted_at']); ?>
                    </p>
                    <a href="job-details.php?id=<?php echo $job['id']; ?>" class="btn btn-primary">
                        View Details <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>