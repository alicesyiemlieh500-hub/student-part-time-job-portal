<?php
require_once '../includes/config.php';
require_once '../includes/header.php';

// Fetch notices from database
$stmt = $conn->query("SELECT * FROM notices ORDER BY posted_at DESC LIMIT 5");
$notices = $stmt->fetchAll();

// Fetch latest jobs
$stmt = $conn->query("SELECT * FROM jobs WHERE status = 'active' ORDER BY posted_at DESC LIMIT 3");
$latest_jobs = $stmt->fetchAll();

// Get statistics
$total_jobs = $conn->query("SELECT COUNT(*) FROM jobs WHERE status = 'active'")->fetchColumn();
$total_students = $conn->query("SELECT COUNT(*) FROM users WHERE user_type = 'student'")->fetchColumn();
$total_employers = $conn->query("SELECT COUNT(*) FROM users WHERE user_type = 'employer'")->fetchColumn();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3">Find Your Perfect Part-Time Job! 🎓</h1>
        <p class="lead mb-4">Browse hundreds of part-time opportunities for students near you</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="job-listing.php" class="btn btn-light btn-lg px-4">
                <i class="fas fa-search me-2"></i>Browse Jobs
            </a>
            <?php if(!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-outline-light btn-lg px-4">
                <i class="fas fa-user-plus me-2"></i>Get Started
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="container">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- About Us Section -->
            <section class="mb-5" id="about">
                <div class="d-flex align-items-center mb-4">
                    <i class="fas fa-info-circle text-primary fa-2x me-3"></i>
                    <h2 class="mb-0">About Us</h2>
                </div>
                <div class="card p-4">
                    <p class="lead">We're on a mission to help students find flexible work that fits their schedule! 🚀</p>
                    <p>PartTimeJobs connects students with local businesses offering part-time positions. Whether you're looking for on-campus work, remote opportunities, or jobs near your college, we've got you covered.</p>
                    
                    <div class="row mt-4 g-4">
                        <div class="col-sm-4 text-center">
                            <div class="stat-card bg-primary text-white p-3">
                                <i class="fas fa-briefcase fa-2x mb-2"></i>
                                <h3 class="mb-0"><?php echo $total_jobs; ?>+</h3>
                                <p class="mb-0">Active Jobs</p>
                            </div>
                        </div>
                        <div class="col-sm-4 text-center">
                            <div class="stat-card bg-primary text-white p-3">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <h3 class="mb-0"><?php echo $total_students; ?>+</h3>
                                <p class="mb-0">Happy Students</p>
                            </div>
                        </div>
                        <div class="col-sm-4 text-center">
                            <div class="stat-card bg-primary text-white p-3">
                                <i class="fas fa-building fa-2x mb-2"></i>
                                <h3 class="mb-0"><?php echo $total_employers; ?>+</h3>
                                <p class="mb-0">Employers</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Latest Jobs Section -->
            <section class="mb-5">
                <div class="d-flex align-items-center mb-4">
                    <i class="fas fa-clock text-primary fa-2x me-3"></i>
                    <h2 class="mb-0">Latest Jobs</h2>
                </div>
                
                <?php if(empty($latest_jobs)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>No jobs posted yet. Check back soon!
                    </div>
                <?php else: ?>
                    <?php foreach($latest_jobs as $job): ?>
                        <div class="job-card">
                            <div class="row">
                                <div class="col-md-8">
                                    <h4 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h4>
                                    <p class="company">
                                        <i class="fas fa-building me-2"></i><?php echo htmlspecialchars($job['company']); ?>
                                    </p>
                                    <div class="job-meta">
                                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?></span>
                                        <span><i class="fas fa-clock"></i> <?php echo ucfirst($job['job_type']); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <p class="salary mb-2">💰 <?php echo htmlspecialchars($job['salary']); ?></p>
                                    <a href="job-details.php?id=<?php echo $job['id']; ?>" class="btn btn-primary btn-sm">
                                        View Details <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="text-center mt-3">
                        <a href="job-listing.php" class="btn btn-outline-primary">
                            View All Jobs <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </section>
            
            <!-- Notices Section -->
            <section class="mb-5" id="notices">
                <div class="d-flex align-items-center mb-4">
                    <i class="fas fa-bullhorn text-primary fa-2x me-3"></i>
                    <h2 class="mb-0">Latest Notices 📢</h2>
                </div>
                
                <?php if(empty($notices)): ?>
                    <div class="alert alert-info">No notices yet.</div>
                <?php else: ?>
                    <?php foreach($notices as $notice): ?>
                        <div class="notice-card">
                            <h5 class="mb-2"><?php echo htmlspecialchars($notice['title']); ?></h5>
                            <p class="mb-2"><?php echo htmlspecialchars($notice['content']); ?></p>
                            <small class="text-muted">
                                <i class="far fa-clock me-1"></i>
                                Posted on <?php echo date('M d, Y', strtotime($notice['posted_at'])); ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Contact Us Section -->
            <section class="mb-4" id="contact">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Contact Us</h5>
                    </div>
                    <div class="card-body">
                        <form id="contactForm" onsubmit="alert('Thank you for contacting us! We will get back to you soon.'); return false;">
                            <div class="mb-3">
                                <input type="text" class="form-control" placeholder="Your Name" required>
                            </div>
                            <div class="mb-3">
                                <input type="email" class="form-control" placeholder="Your Email" required>
                            </div>
                            <div class="mb-3">
                                <textarea class="form-control" rows="4" placeholder="Your Message" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </section>
            
            <!-- Quick Info Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Quick Info</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex mb-3">
                        <i class="fas fa-map-marker-alt text-primary fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-1">Visit Us</h6>
                            <p class="mb-0">123 Campus Road<br>University City, UC 12345</p>
                        </div>
                    </div>
                    
                    <div class="d-flex mb-3">
                        <i class="fas fa-phone text-primary fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-1">Call Us</h6>
                            <p class="mb-0">(555) 123-4567</p>
                        </div>
                    </div>
                    
                    <div class="d-flex mb-3">
                        <i class="fas fa-clock text-primary fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-1">Hours</h6>
                            <p class="mb-0">Mon-Fri: 9am - 5pm<br>Sat-Sun: Closed</p>
                        </div>
                    </div>
                    
                    <div class="d-flex">
                        <i class="fas fa-globe text-primary fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-1">Follow Us</h6>
                            <div class="social-links">
                                <a href="#" class="text-primary me-2"><i class="fab fa-facebook-f"></i></a>
                                <a href="#" class="text-primary me-2"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="text-primary me-2"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#" class="text-primary"><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>