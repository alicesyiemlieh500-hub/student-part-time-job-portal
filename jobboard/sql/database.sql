-- Create database
CREATE DATABASE IF NOT EXISTS jobboard;
USE jobboard;

-- Users table (students and employers)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('student', 'employer') NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Student profiles (extra info for students)
CREATE TABLE student_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    skills TEXT,
    education TEXT,
    experience TEXT,
    certificates TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Employer profiles (extra info for employers)
CREATE TABLE employer_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    company_name VARCHAR(100),
    company_description TEXT,
    website VARCHAR(255),
    location VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Jobs table
CREATE TABLE jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    company VARCHAR(100),
    description TEXT,
    requirements TEXT,
    location VARCHAR(100),
    salary VARCHAR(50),
    job_type ENUM('remote', 'hybrid', 'onsite') DEFAULT 'onsite',
    category VARCHAR(50),
    posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deadline DATE,
    status ENUM('active', 'closed') DEFAULT 'active',
    FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Applications table
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Notices table
CREATE TABLE notices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admins table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data

-- Sample admin (password: admin123)
INSERT INTO admins (username, email, password, full_name) VALUES 
('admin', 'admin@jobboard.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator');

-- Sample users (password: password123)
INSERT INTO users (username, email, password, user_type, full_name, phone) VALUES
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'John Doe', '1234567890'),
('jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Jane Smith', '0987654321'),
('techcorp', 'hr@techcorp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer', 'Tech Corp', '5551234567');

-- Sample student profiles
INSERT INTO student_profiles (user_id, skills, education, experience, certificates) VALUES
(1, 'PHP, JavaScript, HTML, CSS', 'BSc Computer Science', '2 years freelance experience', 'PHP Certification'),
(2, 'Graphic Design, Photoshop, Illustrator', 'BFA Digital Arts', 'Internship at Design Studio', 'Adobe Certified');

-- Sample employer profiles
INSERT INTO employer_profiles (user_id, company_name, company_description, website, location) VALUES
(3, 'Tech Corp', 'Leading tech company hiring students for part-time roles', 'https://techcorp.com', 'New York');

-- Sample jobs
INSERT INTO jobs (employer_id, title, company, description, requirements, location, salary, job_type, category) VALUES
(3, 'Web Developer Intern', 'Tech Corp', 'Looking for a passionate web developer intern', 'Knowledge of HTML, CSS, JavaScript', 'New York', '$20/hour', 'hybrid', 'Technology'),
(3, 'Graphic Design Assistant', 'Tech Corp', 'Help with creating marketing materials', 'Experience with Adobe Creative Suite', 'Remote', '$18/hour', 'remote', 'Design'),
(3, 'Campus Ambassador', 'Tech Corp', 'Represent our company on campus', 'Good communication skills', 'On Campus', '$15/hour', 'onsite', 'Marketing');

-- Sample applications
INSERT INTO applications (job_id, user_id, status) VALUES
(1, 1, 'pending'),
(2, 2, 'accepted'),
(1, 2, 'pending');

-- Sample notices
INSERT INTO notices (title, content) VALUES 
('New Part-Time Opportunities', 'Check out latest part-time jobs near campus!'),
('Campus Recruitment Drive', 'Multiple companies hiring part-time workers this weekend!'),
('Remote Work Opportunities', 'Work from home part-time jobs available for students');