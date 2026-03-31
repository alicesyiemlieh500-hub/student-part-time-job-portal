// Dark/Light mode toggle functionality - FIXED VERSION
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('themeToggle');
    
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            // Send request to toggle theme
            fetch('api/toggle-theme.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update body class
                        if (data.theme === 'dark') {
                            document.body.classList.add('dark-mode');
                            document.body.classList.remove('light-mode');
                            themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
                        } else {
                            document.body.classList.remove('dark-mode');
                            document.body.classList.add('light-mode');
                            themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
                        }
                        
                        // Update session theme in background (already done on server)
                        console.log('Theme switched to:', data.theme);
                    }
                })
                .catch(error => {
                    console.error('Error toggling theme:', error);
                    // Fallback - manual toggle
                    if (document.body.classList.contains('dark-mode')) {
                        document.body.classList.remove('dark-mode');
                        document.body.classList.add('light-mode');
                        themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
                    } else {
                        document.body.classList.add('dark-mode');
                        document.body.classList.remove('light-mode');
                        themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
                    }
                });
        });
    }
    
    // Add fade-in animation to cards
    const cards = document.querySelectorAll('.card, .job-card, .notice-card');
    cards.forEach((card, index) => {
        card.style.animation = `fadeIn 0.5s ease forwards ${index * 0.1}s`;
    });
});