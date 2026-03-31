<?php
require_once '../../includes/config.php';
session_start();

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle job deletion
if(isset($_GET['delete'])) {
    $job_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM jobs WHERE id = ?");
    $stmt->execute([$job_id]);
    header("Location: jobs.php?msg=deleted");
    exit();
}

// Get all jobs
$jobs = $conn->query("SELECT j.*, u.full_name as employer_name FROM jobs j JOIN users u ON j.employer_id = u.id ORDER BY j.posted_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Jobs - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        }
        .admin-sidebar a:hover, .admin-sidebar a.active {
            background: rgba(255,255,255,0.1);
            color: white;
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
                        <h5><?php echo $_SESSION['admin_name']; ?></h5>
                    </div>
                    <nav>
                        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                        <a href="users.php"><i class="fas fa-users"></i> Manage Users</a>
                        <a href="jobs.php" class="active"><i class="fas fa-briefcase"></i> Manage Jobs</a>
                        <a href="notices.php"><i class="fas fa-bullhorn"></i> Notices</a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <h2 class="mb-4">Manage Jobs</h2>
                
                <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                    <div class="alert alert-success">Job deleted successfully!</div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Company</th>
                                        <th>Employer</th>
                                        <th>Location</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($jobs as $job): ?>
                                    <tr>
                                        <td>#<?php echo $job['id']; ?></td>
                                        <td><?php echo htmlspecialchars($job['title']); ?></td>
                                        <td><?php echo htmlspecialchars($job['company']); ?></td>
                                        <td><?php echo htmlspecialchars($job['employer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($job['location']); ?></td>
                                        <td><span class="badge bg-info"><?php echo ucfirst($job['job_type']); ?></span></td>
                                        <td><span class="badge bg-<?php echo $job['status'] == 'active' ? 'success' : 'secondary'; ?>"><?php echo $job['status']; ?></span></td>
                                        <td>
                                            <a href="?delete=<?php echo $job['id']; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Delete this job?')">
                                                Delete
                                            </a>
                                        </td>
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
</body>
</html>