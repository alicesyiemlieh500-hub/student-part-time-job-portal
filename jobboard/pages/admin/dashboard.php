<?php
require_once '../../includes/config.php';
session_start();

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_students = $conn->query("SELECT COUNT(*) FROM users WHERE user_type='student'")->fetchColumn();
$total_employers = $conn->query("SELECT COUNT(*) FROM users WHERE user_type='employer'")->fetchColumn();
$total_jobs = $conn->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
$total_applications = $conn->query("SELECT COUNT(*) FROM applications")->fetchColumn();

// Get recent users
$recent_users = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Get recent jobs
$recent_jobs = $conn->query("SELECT j.*, u.full_name as employer_name FROM jobs j JOIN users u ON j.employer_id = u.id ORDER BY j.posted_at DESC LIMIT 5")->fetchAll();

// Get monthly statistics for chart
$monthly_stats = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as count 
    FROM users 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month
")->fetchAll();

// Prepare chart data
$months = [];
$counts = [];
foreach($monthly_stats as $stat) {
    $months[] = $stat['month'];
    $counts[] = $stat['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PartTimeJobs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .admin-sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
        }
        .admin-sidebar a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            transition: all 0.3s;
        }
        .admin-sidebar a:hover, .admin-sidebar a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            padding-left: 30px;
        }
        .admin-sidebar i {
            width: 25px;
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            color: #667eea;
        }
        .content-wrapper {
            background: #f8f9fa;
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0">
                <div class="admin-sidebar">
                    <div class="text-center py-4">
                        <i class="fas fa-user-shield fa-3x mb-2"></i>
                        <h5><?php echo htmlspecialchars($_SESSION['admin_name']); ?></h5>
                        <p class="small">Administrator</p>
                    </div>
                    <nav>
                        <a href="dashboard.php" class="active">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="users.php">
                            <i class="fas fa-users"></i> Manage Users
                        </a>
                        <a href="jobs.php">
                            <i class="fas fa-briefcase"></i> Manage Jobs
                        </a>
                        <a href="notices.php">
                            <i class="fas fa-bullhorn"></i> Notices
                        </a>
                        <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4 content-wrapper">
                <h2 class="mb-4">Dashboard Overview</h2>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Total Users</p>
                                    <h3><?php echo $total_users; ?></h3>
                                </div>
                                <i class="fas fa-users stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Students</p>
                                    <h3><?php echo $total_students; ?></h3>
                                </div>
                                <i class="fas fa-user-graduate stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Employers</p>
                                    <h3><?php echo $total_employers; ?></h3>
                                </div>
                                <i class="fas fa-building stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Total Jobs</p>
                                    <h3><?php echo $total_jobs; ?></h3>
                                </div>
                                <i class="fas fa-briefcase stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Applications</p>
                                    <h3><?php echo $total_applications; ?></h3>
                                </div>
                                <i class="fas fa-file-alt stat-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Chart -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">User Growth (Last 6 Months)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="userChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <a href="notices.php" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-plus-circle me-2"></i>Add New Notice
                                </a>
                                <a href="users.php" class="btn btn-outline-primary w-100 mb-2">
                                    <i class="fas fa-user-plus me-2"></i>Add User
                                </a>
                                <a href="../../index.php" target="_blank" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-external-link-alt me-2"></i>View Site
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Users -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Recent Users</h5>
                                <a href="users.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Type</th>
                                                <th>Joined</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($recent_users as $user): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $user['user_type'] == 'student' ? 'info' : 'warning'; ?>">
                                                        <?php echo ucfirst($user['user_type']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo formatDate($user['created_at']); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Jobs -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Recent Jobs</h5>
                                <a href="jobs.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Employer</th>
                                                <th>Posted</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($recent_jobs as $job): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($job['title']); ?></td>
                                                <td><?php echo htmlspecialchars($job['employer_name']); ?></td>
                                                <td><?php echo formatDate($job['posted_at']); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Initialize chart
        const ctx = document.getElementById('userChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'New Users',
                    data: <?php echo json_encode($counts); ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>