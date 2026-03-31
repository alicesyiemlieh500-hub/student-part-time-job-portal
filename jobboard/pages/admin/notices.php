<?php
require_once '../../includes/config.php';
session_start();

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle add notice
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_notice'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    $stmt = $conn->prepare("INSERT INTO notices (title, content) VALUES (?, ?)");
    $stmt->execute([$title, $content]);
    header("Location: notices.php?msg=added");
    exit();
}

// Handle delete notice
if(isset($_GET['delete'])) {
    $notice_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM notices WHERE id = ?");
    $stmt->execute([$notice_id]);
    header("Location: notices.php?msg=deleted");
    exit();
}

// Get all notices
$notices = $conn->query("SELECT * FROM notices ORDER BY posted_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Notices - Admin</title>
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
                        <a href="jobs.php"><i class="fas fa-briefcase"></i> Manage Jobs</a>
                        <a href="notices.php" class="active"><i class="fas fa-bullhorn"></i> Notices</a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <h2 class="mb-4">Manage Notices</h2>
                
                <?php if(isset($_GET['msg']) && $_GET['msg'] == 'added'): ?>
                    <div class="alert alert-success">Notice added successfully!</div>
                <?php endif; ?>
                
                <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                    <div class="alert alert-success">Notice deleted successfully!</div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- Add Notice Form -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Add New Notice</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Title</label>
                                        <input type="text" name="title" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Content</label>
                                        <textarea name="content" rows="4" class="form-control" required></textarea>
                                    </div>
                                    <button type="submit" name="add_notice" class="btn btn-primary">
                                        <i class="fas fa-plus-circle me-2"></i>Add Notice
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notices List -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">All Notices</h5>
                            </div>
                            <div class="card-body">
                                <?php foreach($notices as $notice): ?>
                                <div class="notice-card mb-3 p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5><?php echo htmlspecialchars($notice['title']); ?></h5>
                                            <p><?php echo htmlspecialchars($notice['content']); ?></p>
                                            <small class="text-muted">
                                                <i class="far fa-clock me-1"></i>
                                                <?php echo date('M d, Y h:i A', strtotime($notice['posted_at'])); ?>
                                            </small>
                                        </div>
                                        <a href="?delete=<?php echo $notice['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Delete this notice?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>