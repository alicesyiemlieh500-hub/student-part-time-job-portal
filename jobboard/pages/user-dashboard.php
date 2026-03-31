<?php
require_once '../includes/config.php';
require_once '../includes/header.php';

// Check if user is logged in and is a student
if(!isLoggedIn() || !isStudent()) {
    $_SESSION['error'] = "Please login as a student to access this page";
    redirect("login.php");
}

$user_id = $_SESSION['user_id'];

// Handle profile updates
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $field = $_POST['field'];
    $value = $_POST['value'];
    
    try {
        $stmt = $conn->prepare("UPDATE student_profiles SET $field = ? WHERE user_id = ?");
        $stmt->execute([$value, $user_id]);
        $_SESSION['success'] = "Profile updated successfully!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Update failed. Please try again.";
    }
    
    redirect("user-dashboard.php");
}

// Fetch user profile
$stmt = $conn->prepare("SELECT * FROM student_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch();

// If no profile exists, create one
if(!$profile) {
    $stmt = $conn->prepare("INSERT INTO student_profiles (user_id) VALUES (?)");
    $stmt->execute([$user_id]);
    
    // Fetch again
    $stmt = $conn->prepare("SELECT * FROM student_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch();
}

// Fetch user's job applications
$stmt = $conn->prepare("
    SELECT a.*, j.title, j.company, j.location, j.salary, j.job_type 
    FROM applications a 
    JOIN jobs j ON a.job_id = j.id 
    WHERE a.user_id = ?
    ORDER BY a.applied_at DESC
");
$stmt->execute([$user_id]);
$applications = $stmt->fetchAll();

// Count applications by status
$pending_count = 0;
$accepted_count = 0;
$rejected_count = 0;

foreach($applications as $app) {
    if($app['status'] == 'pending') $pending_count++;
    elseif($app['status'] == 'accepted') $accepted_count++;
    elseif($app['status'] == 'rejected') $rejected_count++;
}
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
                    <p class="text-muted mb-2">@<?php echo htmlspecialchars($_SESSION['username']); ?></p>
                    <span class="badge bg-primary">Student</span>
                </div>
                
                <ul class="dashboard-menu">
                    <li><a href="#" class="active" onclick="showSection('overview')"><i class="fas fa-tachometer-alt"></i> Overview</a></li>
                    <li><a href="#" onclick="showSection('profile')"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="#" onclick="showSection('applications')"><i class="fas fa-briefcase"></i> Applications</a></li>
                    <li><a href="job-listing.php"><i class="fas fa-search"></i> Find Jobs</a></li>
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
                            <h3><?php echo count($applications); ?></h3>
                            <p>Total Applications</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-card" style="background: linear-gradient(135deg, #ffc107, #ff9800);">
                            <i class="fas fa-clock"></i>
                            <h3><?php echo $pending_count; ?></h3>
                            <p>Pending</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-card" style="background: linear-gradient(135deg, #28a745, #20c997);">
                            <i class="fas fa-check-circle"></i>
                            <h3><?php echo $accepted_count; ?></h3>
                            <p>Accepted</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-card" style="background: linear-gradient(135deg, #dc3545, #c82333);">
                            <i class="fas fa-times-circle"></i>
                            <h3><?php echo $rejected_count; ?></h3>
                            <p>Rejected</p>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Applications -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Applications</h5>
                        <a href="#" onclick="showSection('applications')" class="btn btn-sm btn-light">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Company</th>
                                        <th>Applied On</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($applications)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                                <p class="mb-0">No applications yet</p>
                                                <a href="job-listing.php" class="btn btn-primary btn-sm mt-2">Browse Jobs</a>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach(array_slice($applications, 0, 5) as $app): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($app['title']); ?></td>
                                                <td><?php echo htmlspecialchars($app['company']); ?></td>
                                                <td><?php echo formatDate($app['applied_at']); ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = [
                                                        'pending' => 'warning',
                                                        'accepted' => 'success',
                                                        'rejected' => 'danger'
                                                    ][$app['status']];
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class; ?>">
                                                        <?php echo ucfirst($app['status']); ?>
                                                    </span>
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
            
            <!-- Profile Section -->
            <div id="profile-section" style="display: none;">
                <h4 class="mb-4"><i class="fas fa-user me-2 text-primary"></i>My Profile</h4>
                
                <!-- Key Skills -->
                <div class="profile-section" id="skills">
                    <h4>
                        <i class="fas fa-code me-2"></i>Key Skills
                        <button class="btn btn-sm btn-outline-primary edit-btn" onclick="editSection('skills')">
                            <i class="fas fa-edit me-1"></i>Edit
                        </button>
                    </h4>
                    
                    <div id="skillsContent">
                        <?php if($profile['skills']): ?>
                            <?php 
                            $skills = explode(',', $profile['skills']);
                            foreach($skills as $skill): 
                            ?>
                                <span class="badge bg-primary me-2 mb-2 p-2"><?php echo trim($skill); ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No skills added yet. Click edit to add your skills.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div id="skillsForm" class="edit-form">
                        <form method="POST">
                            <input type="hidden" name="field" value="skills">
                            <div class="mb-3">
                                <textarea name="value" class="form-control" rows="3" 
                                          placeholder="Enter your skills (comma separated)"><?php echo htmlspecialchars($profile['skills']); ?></textarea>
                                <small class="text-muted">e.g., PHP, JavaScript, HTML, CSS, Python</small>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary btn-sm">Save</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="cancelEdit('skills')">Cancel</button>
                        </form>
                    </div>
                </div>
                
                <!-- Education -->
                <div class="profile-section" id="education">
                    <h4>
                        <i class="fas fa-graduation-cap me-2"></i>Education
                        <button class="btn btn-sm btn-outline-primary edit-btn" onclick="editSection('education')">
                            <i class="fas fa-edit me-1"></i>Edit
                        </button>
                    </h4>
                    
                    <div id="educationContent">
                        <?php if($profile['education']): ?>
                            <p><?php echo nl2br(htmlspecialchars($profile['education'])); ?></p>
                        <?php else: ?>
                            <p class="text-muted">No education details added yet.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div id="educationForm" class="edit-form">
                        <form method="POST">
                            <input type="hidden" name="field" value="education">
                            <div class="mb-3">
                                <textarea name="value" class="form-control" rows="4" 
                                          placeholder="Enter your education details"><?php echo htmlspecialchars($profile['education']); ?></textarea>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary btn-sm">Save</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="cancelEdit('education')">Cancel</button>
                        </form>
                    </div>
                </div>
                
                <!-- Experience -->
                <div class="profile-section" id="experience">
                    <h4>
                        <i class="fas fa-briefcase me-2"></i>Experience
                        <button class="btn btn-sm btn-outline-primary edit-btn" onclick="editSection('experience')">
                            <i class="fas fa-edit me-1"></i>Edit
                        </button>
                    </h4>
                    
                    <div id="experienceContent">
                        <?php if($profile['experience']): ?>
                            <p><?php echo nl2br(htmlspecialchars($profile['experience'])); ?></p>
                        <?php else: ?>
                            <p class="text-muted">No experience added yet.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div id="experienceForm" class="edit-form">
                        <form method="POST">
                            <input type="hidden" name="field" value="experience">
                            <div class="mb-3">
                                <textarea name="value" class="form-control" rows="4" 
                                          placeholder="Enter your work experience"><?php echo htmlspecialchars($profile['experience']); ?></textarea>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary btn-sm">Save</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="cancelEdit('experience')">Cancel</button>
                        </form>
                    </div>
                </div>
                
                <!-- Certificates -->
                <div class="profile-section" id="certificates">
                    <h4>
                        <i class="fas fa-certificate me-2"></i>Certificates
                        <button class="btn btn-sm btn-outline-primary edit-btn" onclick="editSection('certificates')">
                            <i class="fas fa-edit me-1"></i>Edit
                        </button>
                    </h4>
                    
                    <div id="certificatesContent">
                        <?php if($profile['certificates']): ?>
                            <p><?php echo nl2br(htmlspecialchars($profile['certificates'])); ?></p>
                        <?php else: ?>
                            <p class="text-muted">No certificates added yet.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div id="certificatesForm" class="edit-form">
                        <form method="POST">
                            <input type="hidden" name="field" value="certificates">
                            <div class="mb-3">
                                <textarea name="value" class="form-control" rows="4" 
                                          placeholder="Enter your certificates and qualifications"><?php echo htmlspecialchars($profile['certificates']); ?></textarea>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary btn-sm">Save</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="cancelEdit('certificates')">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Applications Section -->
            <div id="applications-section" style="display: none;">
                <h4 class="mb-4"><i class="fas fa-briefcase me-2 text-primary"></i>My Applications</h4>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Company</th>
                                        <th>Location</th>
                                        <th>Salary</th>
                                        <th>Applied On</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($applications)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                <h5>No applications yet</h5>
                                                <p class="text-muted">Start applying for jobs that match your skills!</p>
                                                <a href="job-listing.php" class="btn btn-primary">
                                                    <i class="fas fa-search me-2"></i>Browse Jobs
                                                </a>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($applications as $app): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($app['title']); ?></td>
                                                <td><?php echo htmlspecialchars($app['company']); ?></td>
                                                <td><?php echo htmlspecialchars($app['location']); ?></td>
                                                <td><?php echo htmlspecialchars($app['salary']); ?></td>
                                                <td><?php echo formatDate($app['applied_at']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $app['status'] == 'accepted' ? 'success' : 
                                                            ($app['status'] == 'rejected' ? 'danger' : 'warning'); 
                                                    ?>">
                                                        <?php echo ucfirst($app['status']); ?>
                                                    </span>
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
        </div>
    </div>
</div>

<script>
function showSection(section) {
    // Hide all sections
    document.getElementById('overview-section').style.display = 'none';
    document.getElementById('profile-section').style.display = 'none';
    document.getElementById('applications-section').style.display = 'none';
    
    // Show selected section
    document.getElementById(section + '-section').style.display = 'block';
    
    // Update active menu
    document.querySelectorAll('.dashboard-menu a').forEach(link => {
        link.classList.remove('active');
    });
    event.target.closest('a').classList.add('active');
}

function editSection(section) {
    document.getElementById(section + 'Content').style.display = 'none';
    document.getElementById(section + 'Form').style.display = 'block';
}

function cancelEdit(section) {
    document.getElementById(section + 'Content').style.display = 'block';
    document.getElementById(section + 'Form').style.display = 'none';
}
</script>

<?php require_once '../includes/footer.php'; ?>