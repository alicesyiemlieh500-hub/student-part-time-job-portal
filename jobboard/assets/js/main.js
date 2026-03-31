// ===== FORM VALIDATION =====
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    // Email validation
    const emailInputs = form.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (input.value && !emailRegex.test(input.value)) {
            input.classList.add('is-invalid');
            isValid = false;
        }
    });
    
    // Password match validation for registration
    if (formId === 'registerForm') {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        if (password && confirmPassword && password.value !== confirmPassword.value) {
            alert('❌ Passwords do not match!');
            return false;
        }
    }
    
    if (!isValid) {
        alert('⚠️ Please fill in all required fields correctly!');
    }
    
    return isValid;
}

// ===== PASSWORD STRENGTH CHECKER =====
function checkPasswordStrength(password) {
    const strengthBar = document.getElementById('passwordStrength');
    if (!strengthBar) return;
    
    let strength = 0;
    let feedback = '';
    
    // Length check
    if (password.length >= 8) strength += 25;
    
    // Lowercase check
    if (password.match(/[a-z]+/)) strength += 25;
    
    // Uppercase check
    if (password.match(/[A-Z]+/)) strength += 25;
    
    // Number check
    if (password.match(/[0-9]+/)) strength += 25;
    
    // Update progress bar
    strengthBar.style.width = strength + '%';
    
    // Set color based on strength
    if (strength < 50) {
        strengthBar.className = 'progress-bar bg-danger';
    } else if (strength < 75) {
        strengthBar.className = 'progress-bar bg-warning';
    } else {
        strengthBar.className = 'progress-bar bg-success';
    }
    
    // Show feedback
    const feedbackEl = document.getElementById('passwordFeedback');
    if (feedbackEl) {
        if (strength < 50) feedbackEl.textContent = 'Weak password';
        else if (strength < 75) feedbackEl.textContent = 'Medium password';
        else feedbackEl.textContent = 'Strong password';
    }
}

// ===== EDIT PROFILE SECTIONS =====
function editSection(section) {
    document.getElementById(section + 'Content').style.display = 'none';
    document.getElementById(section + 'Form').style.display = 'block';
}

function cancelEdit(section) {
    document.getElementById(section + 'Content').style.display = 'block';
    document.getElementById(section + 'Form').style.display = 'none';
}

// ===== CONFIRM ACTION =====
function confirmAction(message, url) {
    if (confirm(message)) {
        window.location.href = url;
    }
    return false;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add input event listeners for real-time validation
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });
    });
    
    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});